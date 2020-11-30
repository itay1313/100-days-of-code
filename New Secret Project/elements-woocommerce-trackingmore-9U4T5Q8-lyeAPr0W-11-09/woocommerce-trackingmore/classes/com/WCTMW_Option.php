<?php 
class WCTMW_Option
{
	//rplc: TrackingMore, woocommerce-trackingmore, wctmw, WCTMW
	
	var $company_list;
	var $companies_complete_data;
	var $company_list_by_slug;
	public function __construct()
	{
		
	}
	public function save_options($data)
	{
		update_option('wctmw_options', $data);
	}
	public function save_email_options($data) 
	{
		/* if(isset($data['message']))
			foreach($data['message'] as $index => $content)
			{
				$data['message'][$index] = stripcslashes($content);
			} */
		if(isset($data))
			$data = $this->escape_text($data);
		
		update_option('wctmw_email_options', $data);
	}
	private function escape_text($data)
	{
		foreach($data as $index => $content)
		{
			if(is_string($content))
				$data[$index] = stripcslashes($content);
			else if(is_array($content))
				$data[$index] = $this->escape_text($content);
		}
		return $data;
	}
	public function api_key_has_been_entered()
	{
		$api_key = $this->get_options('api_key');
		if(!isset($api_key) || empty($api_key))
			return false;
		
		return true;
	}
	public function get_options($option_name = null, $default_value = null)
	{
		$result = null;
		
		$options = get_option('wctmw_options');
		if($option_name != null)
		{
			$result = wctmw_get_value_if_set($options, $option_name ,$default_value);
		}
		else 
			$result = $options;
		
		return $result;
	}
	public function get_company_logo($carrier_code)
	{
		//https://s.trackingmore.com/images/icons/express/<?php echo $tracking_data['carrier_code']; .png
		
		if(!file_exists (WCTMW_PLUGIN_ABS_PATH.'/assets/couriers.csv'))
			return "";
		if(!isset($this->companies_complete_data))
		{
			$this->companies_complete_data = array();
			$csv = array_map('str_getcsv', file(WCTMW_PLUGIN_ABS_PATH.'/assets/couriers.csv'));
			foreach($csv as $single_data)
				$this->companies_complete_data[$single_data[1]] = $single_data; //5 -> flag
		}
		
		return wctmw_get_value_if_set($this->companies_complete_data, array($carrier_code, 5), '');
		
	}
	public function create_companies_list($data)
	{
		$fp = fopen(WCTMW_PLUGIN_ABS_PATH.'/assets/couriers.csv', 'w');
		foreach($data as $currier_data)
			{
				 fputcsv($fp, $currier_data);
			}
		fclose($fp);
	}
	public function get_companies_list()
	{
		global $wctwm_trackingmore_model;
		if(!isset($this->company_list))
		{
			if(!file_exists (WCTMW_PLUGIN_ABS_PATH.'/assets/couriers.csv'))
			{
				$result = $wctwm_trackingmore_model->retrieve_and_create_curriers_list();
				if(!$result)
					return array();
			}
			
			$company_list = array_map('str_getcsv', file(WCTMW_PLUGIN_ABS_PATH.'/assets/couriers.csv'));
			$this->company_list = array();
			$this->company_list_by_slug = array();
			foreach($company_list as $company_data)
			{
				$this->company_list_by_slug[$company_data[0]] = $company_data[1];
			}
			asort($this->company_list_by_slug);
			foreach($this->company_list_by_slug as $company_slug => $company_name)
				$this->company_list[] = array(0 => $company_slug, 1 => $company_name);
		}
		
		return $this->company_list;
	}
	public function get_complete_companies_list()
	{
		$result = array();
		$company_list = $this->get_companies_list();
		
		foreach($company_list as $company_data)
				$result[$company_data[1]] = $company_data[0];
		
		return $result;
	}
	public function get_selected_companies_list()
	{
		$result = array();
		$company_list = $this->get_companies_list();
		$options = $this->get_options();
		
		foreach($company_list as $company_data)
			if( wctmw_get_value_if_set($options, array('selected_company', $company_data[1]), false))
				$result[$company_data[1]] = $company_data[0];
		
		return $result;
	}
	public function get_company_by_name($copany_slug)
	{
		$company_list = $this->get_companies_list();
		return  wctmw_get_value_if_set($this->company_list_by_slug, $copany_slug, $copany_slug);
	}
	public function get_email_options($option_name = null, $default_value = null)
	{
		global  $wctmw_wpml_model, $wctmw_order_model;
		$result = null;
		
		$options = get_option('wctmw_email_options');
		
		//default values 
		$is_first_run = $options == false || !isset($options) || empty($options);
		$options = $is_first_run ? array() : $options;
		if($is_first_run)
		{
			// -- Email message
			$default_message = __('<h2>Your order has been shipped with [tracking_company_name]</h2>'.
											  '<strong>Tracking code: </strong>[tracking_code]<br/>'.
											  '<a href="[order_url]" target="_blank">Click here</a> to monitor your order status', 'woocommerce-trackingmore');
											  
			$langs =  $wctmw_wpml_model->get_langauges_list();
			$options['message'] = array();
			foreach($langs as $lang_data)
			{		
				$options['message'][$lang_data['language_code']] = $default_message;
			}
			
			// -- Active notification
			$default_subject = __('Order #[order_id] has been shipped!', 'woocommerce-trackingmore');
			$default_template = __('Hello [billing_first_name] [billing_first_name],<br>'.
							   'The order number [order_id] will be shipped to the following address: [formatted_shipping_address]<br><br>'.
							   '[tracking_message]<br><br>'.
							   '<a href="[order_url]" target="_blank">Click here</a> to monitor your order status.', 'woocommerce-trackingmore');
			$default_message = __('<strong>Tracking code: </strong>[tracking_code]<br/>'.
								   'shipped with [tracking_company_name]', 'woocommerce-trackingmore');
			
			$options['active_notification'] = array('subject' => array(), 'template'=> array(), 'tracking_message'=> array());
			foreach($langs as $lang_data)
			{					
				$options['active_notification']['subject'][$lang_data['language_code']] = $default_subject;
				$options['active_notification']['template'][$lang_data['language_code']] = $default_template;
				$options['active_notification']['tracking_message'][$lang_data['language_code']] = $default_message;
			}
			
			// -- Position
			$options['position'] = 'woocommerce_email_before_order_table';
			
			// -- Order statuses 
			$statuses = $wctmw_order_model->get_order_statuses();
			$options['order_statuses'] = array();
			foreach ($statuses as $status_code => $status_name)
			{
				$options['order_statuses'][$status_code] = $status_code == 'completed' ? true : false;
			}
		}
		//end default values
		
		//load values
		if($option_name != null)
		{
			$result = wctmw_get_value_if_set($options, $option_name ,$default_value);
		}
		else 
			$result = $options;
		
		return $result;
	}
}
?>