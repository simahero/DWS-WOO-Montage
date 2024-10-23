<?php

add_action('wp_ajax_add_to_cart_with_images', 'dws_add_to_cart_with_images');
add_action('wp_ajax_nopriv_add_to_cart_with_images', 'dws_add_to_cart_with_images'); // For non-logged-in users

function dws_add_to_cart_with_images()
{

    $product_id = isset($_POST['product_id']) ? sanitize_text_field($_POST['product_id']) : '';
    $quantity = isset($_POST['quantity']) ? sanitize_text_field($_POST['quantity']) : 1;
    $variation_id  = isset($_POST['variation_id']) ? sanitize_text_field($_POST['variation_id']) : 0;

    unset($_POST['action']);
    unset($_POST['dws_nonce']);
    unset($_POST['product_id']);
    unset($_POST['quantity']);
    unset($_POST['variation_id']);

    $file_urls = [];

    $directory_id     = uniqid();
    $upload_path      = dws_get_upload_path($directory_id);
    $upload_url       = dws_get_upload_url($directory_id);

    $xml = dws_load_svg_as_xml(get_post_meta($product_id, '_dws_svg_url', true));

    if (!$xml) {
        wp_send_json_error('Failed to add item to cart');
        return;
    }

    $old_upload_filter = [];

    add_filter('upload_dir', function ($uploads) use ($upload_path, $upload_url) {
        $old_upload_filter = $uploads;
        $uploads['path'] = $upload_path;
        $uploads['url'] = $upload_url;
        return $uploads;
    });

    foreach ($_FILES as $id => $data) {

        $matchingElements = $xml->xpath("//*[@id='{$id}']");
        $filename = $id ? $product_id . '-' . $id . '.webp' : $product_id . '.webp';
        // $output_file = $upload_path . $filename;
        // $file_url = $upload_url . $filename;

        // if (move_uploaded_file($data['tmp_name'], $output_file)) {
        //     dws_replace_svg_url_at_index($matchingElements, $file_url);
        //     $file_urls[$id] = $file_url;
        // }

        $upload = wp_upload_bits($filename, null, file_get_contents($data['tmp_name']));

        if (empty($upload['error'])) {
            // Add the file URL to the array
            $file_urls[$id] = $upload['url'];
        } else {
            // Handle the error as needed
            // For example, you can log the error or display a message
            error_log('File upload error: ' . $upload['error']);
        }
    }

    add_filter('upload_dir', function ($uploads) use ($old_upload_filter) {
        return $old_upload_filter;
    });

    foreach ($_POST as $id => $data) {
        $matchingElements = $xml->xpath("//*[@id='{$id}']");

        dws_replace_svg_text_at_index($matchingElements, $data);
    }

    $svg_url = $upload_url .  dws_save_updated_svg($xml, $product_id, $upload_path);

    $success = WC()->cart->add_to_cart(
        $product_id,
        $quantity,
        $variation_id,
        array(),
        array(
            'dws_files' => $file_urls,
            'dws_directory_id' => $directory_id,
            'dws_svg' => $svg_url,
        )
    );

    if (!$success) {
        wp_send_json_error('Failed to add item to cart');
    }

    wp_send_json_success();
}

add_action('wp_ajax_add_to_cart_with_single_image', 'dws_add_to_cart_with_single_image');
add_action('wp_ajax_nopriv_add_to_cart_with_single_image', 'dws_add_to_cart_with_single_image'); // For non-logged-in users

function dws_add_to_cart_with_single_image()
{

    $product_id = isset($_POST['product_id']) ? sanitize_text_field($_POST['product_id']) : '';
    $quantity = isset($_POST['quantity']) ? sanitize_text_field($_POST['quantity']) : 1;
    $variation_id  = isset($_POST['variation_id']) ? sanitize_text_field($_POST['variation_id']) : 0;

    unset($_POST['action']);
    unset($_POST['dws_nonce']);
    unset($_POST['product_id']);
    unset($_POST['quantity']);
    unset($_POST['variation_id']);

    $file_urls = [];

    $directory_id     = uniqid();
    $upload_path      = dws_get_upload_path($directory_id);
    $upload_url       = dws_get_upload_url($directory_id);

    $old_upload_filter = [];

    add_filter('upload_dir', function ($uploads) use ($upload_path, $upload_url) {
        $old_upload_filter = $uploads;
        $uploads['path'] = $upload_path;
        $uploads['url'] = $upload_url;
        return $uploads;
    });

    foreach ($_FILES as $id => $data) {

        $filename = $id ? $product_id . '-' . $id . '.webp' : $product_id . '.webp';
        // $output_file = $upload_path . $filename;
        // $file_url = $upload_url . $filename;

        // if (move_uploaded_file($data['tmp_name'], $output_file)) {
        //     $file_urls[$id] = $file_url;
        // }

        $upload = wp_upload_bits($filename, null, file_get_contents($data['tmp_name']));

        if (empty($upload['error'])) {
            // Add the file URL to the array
            $file_urls[$id] = $upload['url'];
        } else {
            // Handle the error as needed
            // For example, you can log the error or display a message
            error_log('File upload error: ' . $upload['error']);
        }
    }

    add_filter('upload_dir', function ($uploads) use ($old_upload_filter) {
        return $old_upload_filter;
    });


    $success = WC()->cart->add_to_cart(
        $product_id,
        $quantity,
        $variation_id,
        array(),
        array(
            'dws_files' => $file_urls,
            'dws_directory_id' => $directory_id,
        )
    );

    if (!$success) {
        wp_send_json_error('Failed to add item to cart');
    }

    wp_send_json_success();
}
