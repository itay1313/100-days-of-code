<?php 
function wctmw_url_exists($url) 
{
    $headers = @get_headers($url);
	if(strpos($headers[0],'200')===false) return false;
	
	return true;
}
function wctmw_get_value_if_set($data, $nested_indexes, $default = false)
{
	if(!isset($data))
		return $default;
	
	$nested_indexes = is_array($nested_indexes) ? $nested_indexes : array($nested_indexes);
	//$current_value = null;
	foreach($nested_indexes as $index)
	{
		if(!isset($data[$index]))
			return $default;
		
		$data = $data[$index];
		//$current_value = $data[$index];
	}
	
	return $data;
}
//$wctmw_result = get_option("_".$wctmw_id);
//$wctmw_notice = !$wctmw_result || $wctmw_result != md5($_SERVER['SERVER_NAME']);
//if(!$wctmw_notice)
	wctmw_setup();
function wctmw_write_log ( $log )  
{
  if ( is_array( $log ) || is_object( $log ) ) {
	 error_log( print_r( $log, true ) );
  } else {
	 error_log( $log );
  }
}
function wctmw_start_with($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}
if( !function_exists('apache_request_headers') ) 
{
    function wctmw_apache_request_headers() 
	{
        $arh = array();
        $rx_http = '/\AHTTP_/';

        foreach($_SERVER as $key => $val) {
            if( preg_match($rx_http, $key) ) {
                $arh_key = preg_replace($rx_http, '', $key);
                $rx_matches = array();
           // do some nasty string manipulations to restore the original letter case
           // this should work in most cases
                $rx_matches = explode('_', $arh_key);

                if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
                    foreach($rx_matches as $ak_key => $ak_val) {
                        $rx_matches[$ak_key] = ucfirst($ak_val);
                    }

                    $arh_key = implode('-', $rx_matches);
                }

                $arh[$arh_key] = $val;
            }
        }

        return( $arh );
    }
}
?>