<?php

add_action('woocommerce_after_order_itemmeta', 'dws_display_admin_order_item_custom_button', 10, 3);
function dws_display_admin_order_item_custom_button($item_id, $item, $product)
{

    if (!(is_admin() && $item->is_type('line_item'))) return;

    $svg_url = $item->get_meta('dws_svg');
    $file_urls = $item->get_meta('dws_files');

    if (!empty($svg_url)) {
        echo '<a target="_blank" style="margin-right: 5px;" href="' . $svg_url . '" class="button download">SVG</a>';
    }

    if (!empty($file_urls)) {
        foreach ($file_urls as $index => $url) {
            $url = dws_get_url_from_path($url);
            echo '<a target="_blank" style="margin-right: 5px;" href="' . $url . '" class="button download">' . $index . '. Kép</a>';
        }
    }
}
