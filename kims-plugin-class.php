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
    function kims_buy_button_handler($atts){

        //Значения по умолчанию
        $a = shortcode_atts(
            array(
                'product_id' => false, //ID товара
                'spid' => false //ID товара подписки
            ), 
            $atts
        );

        //Выходим из функции, если не указан ID товара
        if(!$a['product_id'])
        {
            return  KimsPlugin::hidden('product_id parameter is not defined');
        }
        
        //Получение объекта товара
        $product_id = $a['product_id'];
        $product = wc_get_product($product_id);
        
        //Если не удалось получить объект товара, то выходим из функции
        if(!$product){
            return 'product with id '.$product_id.' is not found';
        }

        //Получение объекта товара подписки
        $subscr_product = null;
        $subscr_product_id = 0;
        if($a['spid']){
            $subscr_product_id = $a['spid'];
            $subscr_product = wc_get_product($subscr_product_id);
        }

        if(!($this->current_user_has_access_to($product, $subscr_product))){
            //Шорткод для добавления товара в корзину, стандартный шорткод Woocommerce
            $add_to_cart_sc = '[add_to_cart id="'.$product_id.'"]';
            return do_shortcode($add_to_cart_sc);
        }

        $this->make_access_for_subsription_files($product, $subscr_product);

        //Отображаем кнопки для загрузки файлов
        $downloads = $product->get_downloads();
        $links = '';
        foreach( $downloads as $key => $each_download ) {
            $links = $links.'<a href="'.$each_download['file'].'" class="kims_buy_button">'.__('Download', 'КИМС').' '.$each_download['name'].'</a>';
        }
        return $links;
    }


    function current_user_has_access_to($product, $subscr_product){
        
        if(!is_user_logged_in()){ 
            return false; // пользователь не авторизован
        }

        //Определяем пользователя
        $current_user = wp_get_current_user();

        //Определяем, была ли куплена статья/журнал
        $product_is_bought = wc_customer_bought_product($current_user->user_email, $current_user->ID, $product->get_id());
        
        //Определяем, активна ли подписка
        $has_subscription = $this->customer_has_subscription($current_user->ID, $subscr_product->get_id());

        if(!$product->is_downloadable() // У продукта не бывает файлов для загрузки
            || (!$product_is_bought && !$has_subscription)){ //Пользователь не купил этот товар, или не имеет подписки
           
            return false;
        }
        return true;
    }

    function customer_has_subscription($user_id, $subscr_product_id){
        $query_file = dirname(__FILE__).'/sql/user_has_subscription.sql';
        $query = file_get_contents($query_file);
        
        global $wpdb;

        $query = str_replace('{table_prefix}', $wpdb->prefix, $query);
        $query = str_replace('{user_id}', $user_id, $query);
        $query = str_replace('{subscr_product_id}', $subscr_product_id, $query);
        $exists = $wpdb->get_var($query);

        return $exists <= 0 ? false : true;

    }

    function make_access_for_subsription_files($product, $subscr_product){

        //Добавляем файлы в подписку, если таких файлов нет в подписке
        $files_added_to_subscription = false;
        $downloads = $product->get_downloads();
        $subscr_downloads = $subscr_product->get_downloads();
        foreach( $downloads as $key => $each_download ) {
            if(!$subscr_product->has_file($key)){
                $subscr_downloads[$key] = array(
                    'name' => $each_download['name'],
                    'file' => $each_download['file']
                );
                $files_added_to_subscription = true;
            }
        }

        if($files_added_to_subscription){
            $subscr_product->set_downloads($subscr_downloads);
            $subscr_product->save();
        }
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