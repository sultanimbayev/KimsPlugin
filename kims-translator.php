<?php

class KimsTranslator{
    function translate($string, $group = 'KIMS_Plugin'){
        if($this->polylang_is_present()){
            pll_register_string( 'KIMS_Plugin', $string, $group);
            return pll__($string);
        }
        return __($string);
    }

    function polylang_is_present(){
        return in_array('polylang/polylang.php', apply_filters('active_plugins', get_option('active_plugins')));
    }
}