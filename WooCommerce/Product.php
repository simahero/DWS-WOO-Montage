<?php

// add_action('woocommerce_before_variations_form', function () {
//     echo '<div id="dws-woo-montage"></div>';
// });

add_action('woocommerce_before_add_to_cart_quantity', function () {
    echo '<div id="dws-woo-cropper" ></div>';
}, 10, 0);

add_filter('woocommerce_single_product_image_thumbnail_html', 'new_product_image', 10, 2);
function new_product_image($html, $post_thumbnail_id)
{
    global $post;
    $svg_url = get_post_meta($post->ID, '_dws_svg_url', true);
    $placeholder_url = get_post_meta($post->ID, '_dws_placeholder_url', true);
    $svg_css = get_post_meta($post->ID, '_dws_svg_css', true);

    $svg_url = dws_get_path_from_url($svg_url);

    if ($svg_url) {
        $svg_content = file_get_contents($svg_url);

        if ((bool)$placeholder_url) {
            $html = '<div id="dws-woo-cropper-markup"><img src="' . $placeholder_url . '" /><div style="position: absolute;' . $svg_css . '">' . $svg_content . '</div></div>';
        } else {
            $html .= '<div id="dws-woo-cropper-markup" style="display: none !important;">' . $svg_content . '</div>';
        }
    }
    return $html;
}

add_action('woocommerce_product_options_advanced', 'dws_woocommerce_product_custom_fields');
function dws_woocommerce_product_custom_fields()
{
    global $woocommerce, $post;
    echo '<div class="product_custom_field">';

    woocommerce_wp_checkbox(
        array(
            'id'            => '_dws_is_dws_product',
            'style'         => 'display: block!important;',
            'label'         => __('DWS Termek', 'woocommerce'),
            'description'   => __('DWS Termek', 'woocommerce')
        )
    );

    woocommerce_wp_checkbox(
        array(
            'id'            => '_dws_use_aspect_ratio',
            'style'         => 'display: block!important;',
            'label'         => __('Keparany hasznalata', 'woocommerce'),
            'description'   => __('Keparany hasznalata', 'woocommerce')
        )
    );

    woocommerce_wp_text_input(
        array(
            'id' => '_dws_placeholder_url',
            'placeholder' => 'https://',
            'label' => __('PLACEHOLDER URL', "dws"),
            'desc_tip' => 'true'
        )
    );
    woocommerce_wp_text_input(
        array(
            'id' => '_dws_svg_url',
            'placeholder' => 'https://',
            'label' => __('SVG URL', "dws"),
            'desc_tip' => 'true'
        )
    );

    woocommerce_wp_textarea_input(
        array(
            'id'          => '_dws_svg_css',
            'label'       => __('CSS', 'dws'),
            'desc_tip'    => true,
            'description' => __('SVG CSS', 'dws'),
        )
    );

    echo '</div>';
}

add_action('woocommerce_process_product_meta', 'dws_woocommerce_product_custom_fields_save');
function dws_woocommerce_product_custom_fields_save($post_id)
{

    $woocommerce__dws_is_dws_product = $_POST['_dws_is_dws_product'];
    update_post_meta($post_id, '_dws_is_dws_product', esc_attr($woocommerce__dws_is_dws_product));

    $woocommerce_dws_svg_url = $_POST['_dws_svg_url'];
    update_post_meta($post_id, '_dws_svg_url', esc_attr($woocommerce_dws_svg_url));

    $woocommerce_dws_placeholder_url = $_POST['_dws_placeholder_url'];
    update_post_meta($post_id, '_dws_placeholder_url', esc_attr($woocommerce_dws_placeholder_url));

    $woocommerce_dws_svg_css = $_POST['_dws_svg_css'];
    update_post_meta($post_id, '_dws_svg_css', esc_attr($woocommerce_dws_svg_css));

    $woocommerce_dws_use_aspect_ratio = $_POST['_dws_use_aspect_ratio'];
    update_post_meta($post_id, '_dws_use_aspect_ratio', esc_attr($woocommerce_dws_use_aspect_ratio));
}

add_action('woocommerce_product_after_variable_attributes', 'dws_woocommerce_product_options_advanced', 10, 3);
function dws_woocommerce_product_options_advanced($loop, $variation_data, $variation)
{
    echo '<div class="product_custom_field">';
    woocommerce_wp_text_input(
        array(
            'id' => '_dws_aspect_ratio[' . $variation->ID . ']',
            'placeholder' => '1',
            'value' => get_post_meta($variation->ID, '_dws_aspect_ratio', true),
            'label' => __('Képarány', "dws"),
            'type' => 'number',
            'desc_tip' => 'true'
        )
    );
    echo '</div>';
}

add_action('woocommerce_save_product_variation', 'dws_save_custom_field_for_variation', 10, 2);
function dws_save_custom_field_for_variation($variation_id, $i)
{
    $dws_aspect_ratio = $_POST['_dws_aspect_ratio'][$variation_id];
    update_post_meta($variation_id, '_dws_aspect_ratio', esc_attr($dws_aspect_ratio));
}

add_filter('woocommerce_loop_add_to_cart_link', 'dws_custom_replace_add_to_cart_button', 10, 2);
function dws_custom_replace_add_to_cart_button($html, $product)
{
    global $post;
    $svg_url = get_post_meta($post->ID, '_dws_svg_url', true);

    if ($svg_url) {
        return '';
    }
    return $html;
}

add_filter('woocommerce_cart_item_thumbnail', 'dws_change_cart_product_image', 99, 3);
function dws_change_cart_product_image($image, $cart_item, $cart_item_key)
{
    if (isset($cart_item['dws_svg'])) {
        $svg_path = dws_get_path_from_url($cart_item['dws_svg']);
        $svg_content = file_get_contents($svg_path);

        return $svg_content;
    }

    if (!isset($cart_item['dws_svg']) && $cart_item['dws_files']) {
        return '<img loading="lazy" decoding="async" width="300" height="300" src="' . end($cart_item['dws_files']) . '" class="woocommerce-placeholder wp-post-image" alt="Helytartó" >';
    }

    return $image;
}

add_filter('woocommerce_order_item_get_formatted_meta_data', 'dws_change_formatted_meta_data', 99, 2);
function dws_change_formatted_meta_data($formatted_meta, $item)
{
    if (!(is_admin())) return;
    return $formatted_meta;
}
