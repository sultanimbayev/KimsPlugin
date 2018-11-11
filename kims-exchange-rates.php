<?

class KimsExchangeRates{

    function always(){
        add_action('kims_update_exchange_rates', array($this, 'retreive_and_update_rates'));
    }

    function activate(){
        $this->retreive_and_update_rates();
        $this->schedule_updates();
    }

    function deactivate(){
        $this->clear_schedules();
    }

    function schedule_updates($interval = 'hourly'){
        if(!wp_next_scheduled('kims_update_exchange_rates')){
            wp_schedule_event(time(), $interval, 'kims_update_exchange_rates');
        }
    }

    function clear_schedules(){
        if(wp_next_scheduled('kims_update_exchange_rates')){ 
            wp_clear_scheduled_hook('kims_update_exchange_rates'); 
        }
    }

    function retreive_and_update_rates(){
        $rates = $this->retreive_rates();
        foreach ($rates as $key => $value) {
            $rate = floatval($value->quant) / floatval($value->description);
            $code = $value->title;
            $settings = $this->update_rate($code, $rate);
        }
    }

    function retreive_rates(){
        $date_str = date('d.m.Y');
        $contents = file_get_contents('http://www.nationalbank.kz/rss/get_rates.cfm?fdate='.$date_str);
        $xml = new SimpleXMLElement($contents);
        $rates = array();
        foreach ($xml as $key => $value) {
            if(!property_exists($value, 'title') 
                || !property_exists($value, 'quant') 
                || !property_exists($value, 'description'))
            {
                continue;
            }
            array_push($rates, $value);
        }
        return $rates;
    }

    function update_rate($currency_code, $rate){
        $wmc_settings = get_option( 'woo_multi_currency_params', array() );
        
        $currency_idx = array_search($currency_code, $wmc_settings['currency']);
        if($currency_idx !== false){
            $wmc_settings['currency_rate'][$currency_idx] = $rate;
            update_option('woo_multi_currency_params', $wmc_settings);
        }
        return $wmc_settings;
    }
}