<?php

class KimsPlugin{

    //Инициализация стилей css
    function load_kims_plugin_css(){
        add_action( 'wp_enqueue_scripts', array($this, 'load_kims_plugin_css_handler') );
    }

    //Обработчик иницализации стилей css
    function load_kims_plugin_css_handler() {
        $plugin_url = plugin_dir_url( __FILE__ );
        wp_enqueue_style( 'kims-styles', $plugin_url . 'css/kims-styles.css' );
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

    }


    //Функция удаления плагина
    static function uninstall(){

    }

    //Активация шорткода "kims_buy_button"
    function kims_buy_button_shortcode_activation(){
        add_shortcode('kims_buy_button', array($this, 'kims_buy_button_handler'));
    }

    //Обработчик шорткода "kims_buy_button"
    function kims_buy_button_hadler($atts){

        $a = shortcode_atts(
            array(
                'product_id' => false
            ), 
            $atts
        );

        if(!$a['product_id'])
        {
            return  KimsPlugin::hidden('product_id parameter is not defined');
        }

        $product_id = $a['product_id'];
        $product = wc_get_product($product_id);

        if(!$product){
            return 'product with id '.$product_id.' is not found';
        }

        $current_user = wp_get_current_user();

        if (is_user_logged_in() and $product->is_downloadable() and wc_customer_bought_product($current_user->user_email, $current_user->ID, $product->get_id())){
            $downloads = $product->get_downloads();
            $links = '';
            foreach( $downloads as $key => $each_download ) {
                $links = $links.'<a href="'.$each_download['file'].'" class="kims_buy_button">'.__('Download', 'КИМС').' '.$each_download['name'].'</a>';
            }
            return $links;
        }

        $content = '[add_to_cart id="'.$product_id.'"]';

        return do_shortcode($content);
    }

    //Скрытие текта с помощью тэга <pre>
    static function hidden($text){
        return '<pre style="display: none;">'.$text.'</pre>';
    }

    //Данный метод возвращает необходимость указания адреса доставки
    function is_checkout_address_required(){
        return false;
    }
}