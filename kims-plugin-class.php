<?php

class KimsPlugin{

    public $kims_buy_button;

    function __construct(){
        $this->kims_buy_button = new KimsBuyButton();
    }

    //Инициализация стилей css
    function load_kims_plugin_scripts(){
        add_action( 'wp_enqueue_scripts', array($this, 'load_kims_plugin_css_handler') );
        add_action( 'wp_enqueue_scripts', array($this, 'load_kims_plugin_js_handler') );
    }

    //Обработчик иницализации стилей css
    function load_kims_plugin_css_handler() {
        $this->kims_buy_button->enqueue_styles();
    }

    function load_kims_plugin_js_handler(){
        $this->kims_buy_button->enqueue_scripts();
    }

    //Проверка установлен ли плагин woocommerce
    function woocommerce_is_present(){
        return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
    }

    //данная функция показывает готовность активации данного плагина
    function ready(){
        return $this->woocommerce_is_present();
    }

    //Функция активации плагина
    static function activate(){
        if(!wp_next_scheduled('kims_update_rates')){
            add_action('kims_update_rates', array('KimsPlugin', 'retreive_and_update_rates'));
            wp_schedule_event( time(), 'hourly', 'kims_update_rates');
        }
    }

    static function deactivate(){
        if(wp_next_scheduled('kims_update_rates')){ wp_clear_scheduled_hook('kims_update_rates'); }
    }

    static function retreive_and_update_rates(){
        $rates = new KimsExchangeRates();
        $rates->retreive_and_update_rates();
    }

    //Функция удаления плагина
    static function uninstall(){
        if(wp_next_scheduled('kims_update_rates')){ wp_clear_scheduled_hook('kims_update_rates'); }
    }

    //Функция вызывается при каждом обновлении страницы
    function always(){
        $this->load_kims_plugin_scripts();
        $this->kims_buy_button->activate();
    }

}