<?php

add_action('init', function(){
    if(!function_exists('pll_register_string')){ return; }
    pll_register_string('_kims_buy_button__download_text', 'Скачать', 'КИМС');
    pll_register_string('_wcs_sign_up_now', 'Sign Up Now', 'КИМС');
});

class KimsTranslator{

    function translate($string){
        if(function_exists('pll__')){
            return pll__($string);
        }
        if(function_exist('__')){
            return __($string);
        }
        return $string;
    }
}