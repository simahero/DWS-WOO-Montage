<?php

add_filter('woocommerce_order_number', 'dws_change_woocommerce_order_number');

function dws_change_woocommerce_order_number($order_id)
{
    global $new_order_id;
    $dws_woo_custom_id_options = get_option('dws_montage_option_name');

    if (!$dws_woo_custom_id_options) {
        $dws_woo_custom_id_options = array(
            "jelenlegi_id_0" => 0,
            "kategoria_id_k_1" => "",
        );
    }

    $change = false;
    $total_quantity = 0;
    $current = $dws_woo_custom_id_options['jelenlegi_id_0'] ?? 0;
    $categories_to_check = $dws_woo_custom_id_options['kategoria_id_k_1'] ? array_map('trim', explode(",",  trim($dws_woo_custom_id_options['kategoria_id_k_1']))) : array();

    $order = wc_get_order($order_id);

    if (!$order) return;

    foreach ($order->get_items() as $item_key => $item) {
        $product = $item->get_product();
        $quantity = $item->get_quantity();

        $dws_order_change_exclude = get_post_meta($item->get_product_id(), '_dws_order_change_exclude', true);
        if ($dws_order_change_exclude !== 'yes') {
            $total_quantity += $quantity;
        }

        $categories = $product->get_category_ids();

        foreach ($categories as $category_id) {
            if (in_array($category_id, $categories_to_check)) {
                $change = true;
            }
        }
    }

    $new_id = intval($current);

    if ($change) {
        $new_order_id = "P-" . str_pad($new_id++, 5, "0", STR_PAD_LEFT) . "-" . $total_quantity;
    } else {
        $new_order_id = $order_id . "-" . $total_quantity;
    }

    if (!get_post_meta($order_id, "dws_id_changed")) {
        if ($change) {
            $dws_woo_custom_id_options['jelenlegi_id_0'] = $new_id;
            update_option("dws_woo_custom_id_option_name", $dws_woo_custom_id_options);
        }
        update_post_meta($order_id, "dws_id_changed", true);
        update_post_meta($order_id, "dws_id_changed_id", $new_order_id);
    }

    add_filter('wcuf_file_name', function ($file_name, $file_data, $index, $upload_field_ids, $order_id) {
        global $new_order_id;
        $splitted_file_name = explode(".", $file_name);
        $extension = end($splitted_file_name);
        return $new_order_id . "." . $extension;
    }, 9999, 5);

    return $new_order_id;
}

add_filter('wcuf_file_name', function ($file_name, $file_data, $index, $upload_field_ids, $order_id) {
    global $order_id;

    $new_order_id = get_post_meta($order_id, 'dws_id_changed_id', true);

    $splitted_file_name = explode(".", $file_name);
    $extension = end($splitted_file_name);
    return $new_order_id . "." . $extension;
}, 9999, 5);
