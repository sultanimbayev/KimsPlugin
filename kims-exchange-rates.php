<?

class KimsExchangeRates{

function retreive_and_update_rates(){
    $rates = $this->retreive_rates();
    foreach ($rates as $key => $value) {
        $code = $value->title;
        $rate = $value->quant / $value->description;
        $settings = $this->update_currency($code, $rate);
    }
	return $settings;
}

function retreive_rates(){
    $date_str = date('d.m.Y');
    $contents = file_get_contents('http://www.nationalbank.kz/rss/get_rates.cfm?fdate='.$date_str);
    $xml = new SimpleXMLElement($contents);
    $rates = array();
    foreach ($xml as $key => $value) {
        if(!property_exists($value, 'title') 
            && !property_exists($value, 'quant') 
            && !property_exists($value, 'description'))
        {
            continue;
        }
        array_push($rates, $value);
    }
    return $rates;
}

function update_currency($currency_code, $rate){
    $wmc_settings = get_option( 'woo_multi_currency_params', array() );
    
    $currency_idx = array_search($currency_code, $wmc_settings['currency']);
    if($currency_idx !== false){
        $wmc_settings['currency_rate'][$currency_idx] = $rate;
        update_option('woo_multi_currency_params', $wmc_settings);
    }
    return $wmc_settings;
}
}