<?php 
class WCTMW_Time
{
	//rplc: WCTMW
	public function __construct()
	{
	}
	
	public function format_data_according_wordpress_settings($date, $display_hour = true)
	{
			try{
			$date = new DateTime($date); //yyyy-mm-dd
		}catch(Exception $e){return $date;}
		
		return $display_hour ? $date->format(get_option('date_format')." ".get_option('time_format')) : $date->format(get_option('date_format'));
	}
	public function is_valid_date_format($date)
	{
		
		if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date)) {
			return true;
		} else {
			return false;
}
	}
}
?>