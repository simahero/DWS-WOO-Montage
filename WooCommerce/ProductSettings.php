<?php

add_action('woocommerce_product_options_advanced', 'add_custom_checkbox_to_advanced_tab');
function add_custom_checkbox_to_advanced_tab()
{
    woocommerce_wp_checkbox(
        array(
            'id'          => '_dws_order_change_exclude',
            'label'       => __('Rendelés szám kihagyás', 'your-text-domain'),
        )
    );
}

add_action('woocommerce_process_product_meta', 'save_custom_checkbox');
function save_custom_checkbox($post_id)
{
    $custom_checkbox_value = isset($_POST['_dws_order_change_exclude']) ? 'yes' : 'no';
    update_post_meta($post_id, '_dws_order_change_exclude', $custom_checkbox_value);
}
