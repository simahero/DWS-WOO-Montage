<?php

/**
 * Plugin Name:       _DWS-WOO-MONTAGE
 * Description:       
 * Version:           3.1.5
 * Author:            Ront車 Zolt芍n
 * Author URI:        simahero.github.io
 * Text Domain:       
 */

/* 
  01001001 00100000 01001100 01001111
  01010110 01000101 00100000 01011001
  01001111 01010101 00100000 01001100
  01001111 01010100 01010100 01001001
  00100000 00111100 00110011 00000000
*/

include_once __DIR__ . "/Utils/Settings.php";
include_once __DIR__ . "/Utils/Helpers.php";

include_once __DIR__ . "/WooCommerce/AllowTTF.php";
include_once __DIR__ . "/WooCommerce/AllowSVG.php";
include_once __DIR__ . "/WooCommerce/Product.php";
include_once __DIR__ . "/WooCommerce/WooAddOrderMeta.php";
include_once __DIR__ . "/WooCommerce/WooAdmin.php";
include_once __DIR__ . "/WooCommerce/WooAjax.php";
include_once __DIR__ . "/WooCommerce/ProductSettings.php";
include_once __DIR__ . "/WooCommerce/WooOrderNumber.php";
// include_once __DIR__ . "/WooCommerce/WooRemoveCart.php";
include_once __DIR__ . "/WooCommerce/WooUploadS3.php";

add_action('init', 'dws_autoload');
function dws_autoload()
{
	require 'vendor/autoload.php';
}

add_action('activated_plugin', 'dws_load_first');
function dws_load_first()
{
	$path = str_replace(WP_PLUGIN_DIR . '/', '', __FILE__);
	if ($plugins = get_option('active_plugins')) {
		if ($key = array_search($path, $plugins)) {
			array_splice($plugins, $key, 1);
			array_unshift($plugins, $path);
			update_option('active_plugins', $plugins);
		}
	}
}

add_action('wp_enqueue_scripts', function ($a) {

	global $post;
	$is_dws_product = get_post_meta($post->ID, '_dws_is_dws_product', true);

	if (!$is_dws_product) return;

	$use_aspect_ratio = get_post_meta($post->ID, '_dws_use_aspect_ratio', true);
	$aspect_ratios = array();

	if ($use_aspect_ratio) {
		$product = wc_get_product($post->ID);
		$variations = $product->get_available_variations();

		foreach ($variations as $variation) {
			$variation_id = $variation['variation_id'];
			$aspect_ratio = get_post_meta($variation_id, '_dws_aspect_ratio', true);
			$aspect_ratios[$variation_id] = $aspect_ratio;
		}
	}

	$dws_montage_options = get_option('dws_montage_option_name');

	wp_enqueue_style('dws-woo-montage-cropper-style', plugin_dir_url(__FILE__) . 'CSS/style.css');

	wp_enqueue_style('dws-woo-montage-style', plugin_dir_url(__FILE__) . 'js/build/static/css/styles.css');

	wp_enqueue_script('dws-woo-montage-script', plugin_dir_url(__FILE__) . 'js/build/static/js/bundle.js', array('jquery'), false, true);
	wp_localize_script(
		'dws-woo-montage-script',
		'ajax_object',
		array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'dws_nonce' => wp_create_nonce('dws_image_nonce'),
			'product_id' => $post->ID,
			'aspect_ratios' => $aspect_ratios,
			'filetype' => $dws_montage_options['filetype'] ?: 'png',
			'quality' => $dws_montage_options['quality'] ?: 90
		)
	);
}, 10, 1);
