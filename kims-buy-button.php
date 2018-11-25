<?php

define('__kbb', 'kims_buy_button');
define('__kbb_action', 'add_shortcode_kims_buy_button');

class KimsBuyButton{
    
    var $translator;

    function __construct(){
        $this->translator = new KimsTranslator();
    }

    function enqueue_styles(){
        $plugin_url = plugin_dir_url( __FILE__ );
        wp_enqueue_style( 'kims-styles', $plugin_url . 'css/kims-styles.css' );
    }

    function enqueue_scripts(){

    }

    function always(){
        $this->activate_shortcode();
        add_action( 'fusion_builder_before_init', array($this, 'add_buy_button_to_fusion_builder'));
    }

    //Активация шорткода "kims_buy_button"
    function activate_shortcode(){
        add_action(__kbb_action, array($this, 'activate_shortcode_handler'));
        do_action(__kbb_action);
    }

    function activate_shortcode_handler(){
        add_shortcode(__kbb, array($this, 'kims_buy_button_handler'));
    }

    //Обработчик шорткода "kims_buy_button"
    function kims_buy_button_handler($atts){

        //Значения по умолчанию
        $a = shortcode_atts(
            array(
                'product_id' => false, //ID товара
            ), 
            $atts
        );

        //Выходим из функции, если не указан ID товара
        if(!$a['product_id'])
        {
            return  KimsFuncitons::hidden('product_id parameter is not defined');
        }
        
        //Получение объекта товара
        $product_id = $a['product_id'];
        $product = wc_get_product($product_id);
        
        //Если не удалось получить объект товара, то выходим из функции
        if(!$product){
            return 'product with id '.$product_id.' is not found';
        }

        if(!($this->current_user_has_access_to($product))){
            //Шорткод для добавления товара в корзину, стандартный шорткод Woocommerce
            $add_to_cart_sc = '[add_to_cart id="'.$product_id.'"]';
            return do_shortcode($add_to_cart_sc);
        }

        $this->make_access_for_subsription_files($product);

        //Отображаем кнопки для загрузки файлов
        $links = $this->kims_downoad_links($product);
        return apply_filters('kims_dowload_links', $links);
    }

    function kims_downoad_links($product){
        $downloads = $product->get_downloads();
        $downloadStr = $this->translator->translate('Скачать');
        $links = '';
        foreach( $downloads as $key => $each_download ) {
            $links .= $links.'<a href="';
            $links .= $each_download['file'];
            $links .= '" class="kims_buy_button">';
            $links .= $downloadStr.' ';
            $links .= $each_download['name'];
            $links .= '</a>';
        }
        return $links;
    }


    function current_user_has_access_to($product){
        
        if(!is_user_logged_in()){ 
            return false; // пользователь не авторизован
        }

        //Определяем пользователя
        $current_user = wp_get_current_user();

        //Определяем, была ли куплена статья/журнал
        $product_is_bought = wc_customer_bought_product($current_user->user_email, $current_user->ID, $product->get_id());
        
        //Определяем, активна ли подписка
        $has_subscription = $this->customer_has_subscription($current_user->ID);

        if(!$product->is_downloadable() // У продукта не бывает файлов для загрузки
            || (!$product_is_bought && !$has_subscription)){ //Пользователь не купил этот товар, или не имеет подписки
           
            return false;
        }
        return true;
    }


    function customer_has_subscription($user_id){
        $query_file = dirname(__FILE__).'/sql/user_has_subscription.sql';
        $query = file_get_contents($query_file);
        global $wpdb;
        $subscr_product_ids = $this->get_subscription_product_ids();
        $subscr_product_ids_str = implode(',', $subscr_product_ids);
        $query = str_replace('{table_prefix}', $wpdb->prefix, $query);
        $query = str_replace('{user_id}', $user_id, $query);
        $query = str_replace('{subscr_product_ids}', $subscr_product_ids_str, $query);
        $exists = $wpdb->get_var($query);

        return $exists <= 0 ? false : true;

    }

    function make_access_for_subsription_files($product){

        $files_added_to_subscription = false;
        foreach ($this->get_subscription_product_ids() as $index => $id) {
            $subscr_product = wc_get_product($id);

            //Добавляем файлы в подписку, если таких файлов нет в подписке
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
        }
        if($files_added_to_subscription){
            $subscr_product->set_downloads($subscr_downloads);
            $subscr_product->save();
        }
        
    }

    function add_buy_button_to_fusion_builder() {

        fusion_builder_map( 
            array(
                'name'            => esc_attr__( 'KIMS BuyButton', 'fusion-builder' ),
                'shortcode'       => __kbb,
                'generator_only'  => true,
                'params'          => array(
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_attr__( 'ID товара', 'kims-imio' ),
                        'description' => esc_attr__( 'ID товара статьи в WooCommerce', 'kims-imio' ),
                        'param_name'  => 'product_id',
                        'value'       => esc_attr__( '', 'kims-imio' ),
                    ),
                ),
            ) 
        );
    }
    

    function get_subscription_product_ids(){
        return array(2703, 3429, 3431);
    }

}