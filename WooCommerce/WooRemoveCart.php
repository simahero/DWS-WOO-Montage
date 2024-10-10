<?php

add_action('woocommerce_remove_cart_item', 'dws_cart_updated', 10, 2);

function dws_cart_updated($cart_item_key, $cart)
{
    $cart_items = $cart->get_cart();

    foreach ($cart_items as $key => $item) {
        if ($cart_item_key === $key) {

            if (isset($item['dws_directory_id'])) {
                $path = dws_get_upload_path($item['dws_directory_id']);
                dws_remove_dir($path);
            }
            break;
        }
    }
}

add_action('woocommerce_before_shop_loop', 'dws_delete_remove_product_notice', 5);
add_action('woocommerce_shortcode_before_product_cat_loop', 'dws_delete_remove_product_notice', 5);
add_action('woocommerce_before_single_product', 'dws_delete_remove_product_notice', 5);

function dws_delete_remove_product_notice()
{
    $notices = WC()->session->get('wc_notices', array());
    if (isset($notices['success'])) {
        for ($i = 0; $i < count($notices['success']); $i++) {
            if (strpos($notices['success'][$i], __('removed', 'woocommerce')) !== false) {
                array_splice($notices['success'], $i, 1);
            }
        }
        WC()->session->set('wc_notices', $notices['success']);
    }
}
