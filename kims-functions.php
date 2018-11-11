<?php

class KimsFunctions{
    //Скрытие текта с помощью тэга <pre>
    static function hidden($text){
        return '<pre style="display: none;">'.$text.'</pre>';
    }
}