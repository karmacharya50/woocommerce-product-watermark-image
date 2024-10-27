<?php
/*
Plugin Name: WooCommerce product Watermark Image
Plugin URI: https://wpfactory.com/
Description: Image Watermark for WooCommerce product, Product Gallery and Variable product. Image watermark for previously uploaded product images.
Version: 1.0.0
Author: WPFactory
Author URI: https://wpfactory.com/
Text Domain: wf-watermark
Copyright: © 2024 WPFactory
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

//Include
require_once plugin_dir_path( __FILE__ ) . 'includes/class-admin-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-product-watermark-image.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

// Check for active plugins
add_action( 'admin_init', 'check_woocommerce_plugin_activate' );
add_action( 'admin_enqueue_scripts', 'wf_enqueue_scripts' );

function wf_enqueue_scripts(){
    wp_enqueue_media();
    wp_enqueue_script( 'watermark_main',plugins_url( 'assets/js/main.js', __FILE__ ), array('jquery'), null, true );
    wp_register_style( 'watermark_admin_css', plugins_url( 'assets/css/main.css', __FILE__ ), false, '1.0.0' );
    wp_enqueue_style( 'watermark_admin_css' );
}

function check_woocommerce_plugin_activate() {
    // Check if WooCommerce is active
    if ( ! is_plugin_active( 'woocommerce/woocommerce.php' )) {
        deactivate_plugins(plugin_basename(__FILE__));
        add_action( 'admin_notices', 'show_woocommerce_required_notice' );       
    }
}

function show_woocommerce_required_notice() {
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php _e( '"WooCommerce product watermark image" plugin requires WooCommerce to be installed and active. Please install or activate WooCommerce.', 'wf-watermark' ); ?></p>
    </div>
    <?php
}