<?php

use Aws\S3\S3Client;

add_action('woocommerce_thankyou', 'dws_woocommerce_new_order_action', 10, 1);
function dws_woocommerce_new_order_action($order_id)
{
    if (!$order_id) return;
    if (get_post_meta($order_id, 'dws_s3_uploaded', true)) return;

    $order = wc_get_order($order_id);

    $dws_montage_options = get_option('dws_montage_option_name');
    $s3 = new S3Client([
        'version'     => 'latest',
        'region'      => $dws_montage_options['s3_region_3'],
        'credentials' => [
            'key'    => $dws_montage_options['s3_access_key_0'],
            'secret' => $dws_montage_options['s3_secret_1'],
        ],
    ]);

    foreach ($order->get_items() as $item_id => $item) {
        $product_id = $item->get_product_id();

        $directory_id = $item->get_meta('dws_directory_id');

        $svg_url = $item->get_meta('dws_svg');
        $file_names = $item->get_meta('dws_files');

        $upload_path = dws_get_upload_path($directory_id);

        $new_urls = [];

        foreach ($file_names as $id => $url) {

            $old_name = end(explode('/', $url));
            $old_path = $upload_path . $old_name;

            $new_order_id = get_post_meta($order_id, 'dws_id_changed_id', true);
            $new_name = str_replace($product_id, $new_order_id, $old_name);
            // $new_name = str_replace($product_id, $order_id . '-' . $item->get_quantity(), $old_name);

            $new_path = $upload_path . $new_name;

            if (!file_exists($new_path)) {
                if (rename($old_path, $new_path)) {
                    $new_urls[$id] = $new_path;
                    $order->add_order_note("Filename (" . $old_name . ") changed to: " .  $new_name);
                    $result = dws_upload_file_to_s3($s3, $new_name, $new_path, $directory_id);
                    if ($result["result"]) {
                        $new_urls[$id] = $result["url"];
                        $order->add_order_note('File uploaded successfully. URL: ' . $result["url"]);
                    } else {
                        $new_urls[$id] = $new_path;
                        $order->add_order_note('Error uploading file: ' . $result["error"]);
                    }
                } else {
                    $new_urls[$id] = $old_path;
                    $order->add_order_note("Error renaming: " . $old_name);
                }
            } else {
                $order->add_order_note("Already exists: " . $new_path);
            }
        }

        wc_update_order_item_meta($item_id, 'dws_files', $new_urls);

        if ($svg_url) {
            $xml = dws_load_svg_as_xml($svg_url);

            if (!$xml) {
                $order->add_order_note('Error loading svg file: ' . $svg_url);
                return;
            }

            foreach ($new_urls as $id => $url) {
                $matchingElements = $xml->xpath("//*[@id='{$id}']");
                dws_replace_svg_url_at_index($matchingElements, $url);
            }

            $svg_name = end(explode('/', $svg_url));
            $svg_old_path = dws_get_path_from_url($svg_url);

            $new_order_id = get_post_meta($order_id, 'dws_id_changed_id', true);
            $svg_new_name = str_replace($product_id, $new_order_id, $old_name);
            // $svg_new_name = str_replace($product_id, $order_id . '-' . $item->get_quantity(), $svg_name);

            $svg_new_path = $upload_path . $svg_new_name;

            if (!file_exists($svg_new_path)) {
                if (rename($svg_old_path, $svg_new_path)) {
                    $order->add_order_note("Filename (" . $svg_old_path . ") changed to: " .  $svg_new_name);

                    $converted = dws_convert_svg_to_jpg($svg_new_path);

                    if ($converted) {
                        $order->add_order_note("Converted to: " . $converted);
                        $result = dws_upload_file_to_s3($s3, $svg_name, $converted, $directory_id);
                        if ($result["result"]) {
                            $order->add_order_note('File uploaded successfully. URL: ' . $result["url"]);
                            wc_update_order_item_meta($item_id, 'dws_svg', $result["url"]);
                        } else {
                            wc_update_order_item_meta($item_id, 'dws_svg', $converted);
                            $order->add_order_note('Error uploading file: ' . $result["error"]);
                        }
                    } else {
                        wc_update_order_item_meta($item_id, 'dws_svg', $svg_new_path);
                    }
                } else {
                    $order->add_order_note("Error renaming " . $svg_name);
                }
            }
        }
    }

    update_post_meta($order_id, 'dws_s3_uploaded', true);

    return;
}
