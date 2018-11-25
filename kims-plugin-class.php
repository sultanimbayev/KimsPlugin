<?php

class KimsPlugin{

    public $kims_buy_button;
    public $kims_exchange_rates;
    public $kims_translator;

    function __construct(){
        $this->kims_buy_button = new KimsBuyButton();
        $this->kims_exchange_rates = new KimsExchangeRates();
        $this->kims_translator = new KimsTranslator();
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

    function add_to_cart_text_filter($text){
        if(strcasecmp($text, "Sign up now") == 0) {
            return $this->kims_translator->translate($text);
        }
        return $text;
    }

    //данная функция показывает готовность активации данного плагина
    function ready(){
        return $this->woocommerce_is_present();
    }

    //Функция активации плагина
    function activate(){
        $this->kims_exchange_rates->activate();
    }

    //Функция деактивации плагина
    function deactivate(){
        $this->kims_exchange_rates->deactivate();
    }

    //Функция удаления плагина
    static function uninstall(){
        
    }

    //Функция вызывается при каждом обновлении страницы
    function always(){
        $this->load_kims_plugin_scripts();
        $this->kims_buy_button->always();
        $this->kims_exchange_rates->always();
        add_filter('woocommerce_product_add_to_cart_text', array($this, 'add_to_cart_text_filter'));
        //add_filter('woocommerce_pay_order_button_text', array($this, 'add_to_cart_text_filter'));
    }

}