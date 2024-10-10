<?php

add_action('woocommerce_add_order_item_meta', function ($item_id, $values, $key) {
    if (isset($values['dws_files'])) {
        wc_add_order_item_meta($item_id, 'dws_files', $values['dws_files']);
    }
    if (isset($values['dws_directory_id'])) {
        wc_add_order_item_meta($item_id, 'dws_directory_id', $values['dws_directory_id']);
    }
    if (isset($values['dws_svg'])) {
        wc_add_order_item_meta($item_id, 'dws_svg', $values['dws_svg']);
    }
}, 10, 3);
