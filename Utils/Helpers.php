<?php

use Aws\Exception\AwsException;

function dws_get_upload_path($directory_id)
{
    $upload_path = str_replace('/', DIRECTORY_SEPARATOR, wp_upload_dir()['basedir']) . DIRECTORY_SEPARATOR . 'DWS' . DIRECTORY_SEPARATOR . $directory_id . DIRECTORY_SEPARATOR;
    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0755, true);
    }
    return $upload_path;
}

function dws_get_upload_url($directory_id)
{
    return wp_upload_dir()['baseurl'] . '/DWS/' . $directory_id . '/';
}

function dws_get_path_from_url($url)
{
    return str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $url);
}

function dws_get_url_from_path($url)
{
    return str_replace(wp_upload_dir()['basedir'], wp_upload_dir()['baseurl'], $url);
}

function dws_load_svg_as_xml($svg_sring_url)
{
    if (!$svg_sring_url) return null;
    $svg_sring_url = dws_get_path_from_url($svg_sring_url);
    $svg_string = file_get_contents($svg_sring_url);
    if ($svg_string !== false) {
        return simplexml_load_string(urldecode($svg_string));
    }
    return null;
}

function dws_upload_image($data, $product_id, $id, $upload_path, $upload_url)
{

    $output_file = $upload_path . $data['name'];
    $file_url = $upload_url . $data['name'];

    if (move_uploaded_file($_FILES['image']['tmp_name'], $output_file)) {
        return $file_url;
    } else {
        return false;
    }

    // $base64_string = preg_replace('/data:image\/.*;base64,/', '', $data);
    // $decoded = base64_decode($base64_string);

    // $filename = $id ? $product_id . '-' . $id . '.png' : $product_id . '.png';
    // $output_file = $upload_path . $filename;
    // $file_url = $upload_url . $filename;
    // file_put_contents($output_file, $decoded);

}

function dws_replace_svg_url_at_index($matchingElements, $url)
{
    if (!empty($matchingElements)) {
        $matchingElements[0]->attributes('xlink', true)->href = $url;
        $matchingElements[0]['href'] = $url;
    }
}

function dws_replace_svg_text_at_index($matchingElements, $text)
{

    if (!empty($matchingElements)) {
        $matchingElements[0][0] =  $text;
    }
}

function dws_save_updated_svg($xml, $name, $upload_path)
{
    $svgfilename = $name . '.svg';
    $svg_file = $upload_path . $svgfilename;
    file_put_contents($svg_file, str_replace('<?xml version="1.0"?>', '', $xml->asXML()));

    return $svgfilename;
}

function dws_upload_file_to_s3($s3, $name, $path, $directory_id)
{
    $dws_montage_options = get_option('dws_montage_option_name');
    try {
        $result = $s3->putObject([
            'Bucket' => $dws_montage_options['s3_bucket_2'],
            'Key'    => $directory_id . '/' . $name,
            'Body'   => fopen($path, 'rb'),
            'ACL'    => 'public-read',
        ]);

        dws_remove_dir($path);

        return array(
            "result" => true,
            "url" => $result['ObjectURL']
        );
    } catch (AwsException $e) {
        return array(
            "result" => false,
            "error" => $e->getMessage()
        );
    } catch (Exception $e) {
        return array(
            "result" => false,
            "error" => $e->getMessage()
        );
    }
}

function dws_convert_svg_to_jpg($path)
{

    try {
        $im = new Imagick();
        $svg = file_get_contents($path);
        $im->readImageBlob($svg);

        $im->setImageFormat("jpeg");

        $new_path = str_replace('svg', 'jpg', $path);
        $im->writeImage($new_path);
        $im->clear();
        $im->destroy();
        return $new_path;
    } catch (Exception $e) {
        return null;
    }
}

function dws_remove_dir($path)
{
    if (!is_dir($path)) {
        throw new InvalidArgumentException("$path must be a directory");
    }

    $path = rtrim($path, '/') . '/';

    $files = scandir($path);
    if ($files === false) {
        throw new RuntimeException("Unable to scan directory $path");
    }

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $filePath = $path . $file;

        if (is_dir($filePath)) {
            dws_remove_dir($filePath);
        } else {
            if (!unlink($filePath)) {
                throw new RuntimeException("Unable to delete file $filePath");
            }
        }
    }

    if (!rmdir($path)) {
        throw new RuntimeException("Unable to delete directory $path");
    }
}
