<?php
/*
Plugin Name: KIMS Plugin
Plugin URI: https://github.com/sultanimbayev/KimsPlugin
Description: Плагин кастомизации сайта КИМС
Version: 1.1.7
Author: Sultan Imbayev
Author URI: https://github.com/sultanimbayev
*/

if(!defined('ABSPATH'))
{
    die('direct access detected!');
}

include(dirname(__FILE__)."/kims-plugin-class.php");

register_activation_hook(__FILE__, array('KimsPlugin','activate'));
register_uninstall_hook(__FILE__, array('KimsPlugin','uninstall'));


$KimsPlugin = new KimsPlugin();

if($KimsPlugin->ready()){

    $KimsPlugin->load_kims_plugin_css();
    $KimsPlugin->kims_buy_button_shortcode_activation();
    
    /*
    * Snippet to remove the 'Proceed to Checkout' link on the cart page.
    * Code goes in the functions.php file in your theme.
    */
    remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );

    if(!$KimsPlugin->is_checkout_address_required()){
        add_filter('woocommerce_paypal_express_checkout_address_not_required', function(){ return true; });
    }

}

