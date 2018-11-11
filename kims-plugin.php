<?php
/*
Plugin Name: KIMS Plugin
Plugin URI: https://github.com/sultanimbayev/KimsPlugin
Description: Плагин кастомизации сайта КИМС
Version: 1.3.10
Author: Sultan Imbayev
Author URI: https://github.com/sultanimbayev
*/

if(!defined('ABSPATH'))
{
    die('direct access detected!');
}

require_once(dirname(__FILE__)."/kims-plugin-class.php");
require_once(dirname(__FILE__)."/kims-buy-button.php");
require_once(dirname(__FILE__)."/kims-exchange-rates.php");
require_once(dirname(__FILE__)."/kims-functions.php");
require_once(dirname(__FILE__)."/kims-translator.php");

register_activation_hook(__FILE__, array('KimsPlugin', 'activate'));
register_deactivation_hook(__FILE__, array('KimsPlugin', 'deactivate'));
register_uninstall_hook(__FILE__, array('KimsPlugin', 'uninstall'));

$KimsPlugin = new KimsPlugin();

if($KimsPlugin->ready()){
    $KimsPlugin->always();
}

