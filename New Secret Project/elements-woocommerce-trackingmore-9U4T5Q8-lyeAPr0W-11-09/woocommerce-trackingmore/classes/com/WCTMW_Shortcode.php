<?php 
class WCTMW_Shortcode
{
	public function __construct()
	{
		add_shortcode( 'wctmw_order_status', array(&$this, 'display_order_timeline' ));
	}
	public function display_order_timeline($param)
	{
		/* if(!isset($params['tacking_code']) || $params['tacking_code'] == "")
			return ""; */
		
		global $wctmw_wpml_model;
		$unique_id = rand ( 25684598 , 25684598698547 );
		
		wp_enqueue_style('wctmw-timeline', WCTMW_PLUGIN_PATH.'/css/frontend-timeline.css');
		wp_enqueue_style('wctmw-timeline-com', WCTMW_PLUGIN_PATH.'/css/frontend-timeline-com.css');
		wp_enqueue_style('wctmw-shortcode-style', WCTMW_PLUGIN_PATH.'/css/frontend-shortcode.css');
		
		wp_enqueue_script('wctmw-shortcode-tracking-input', WCTMW_PLUGIN_PATH.'/js/frontend-shortcode.js', array( 'jquery' ));
		wp_localize_script( 'wctmw-shortcode-tracking-input', 'wctmw', array(
																'ajaxurl' => admin_url('admin-ajax.php'),
																'empty_order_id_error' => __('Please enter a valid order id', 'woocommerce-aftership'),
																'lang' => $wctmw_wpml_model->get_current_language()
																) );
		wp_enqueue_script( 'wctmw-shortcode-tracking-input' );
		
		ob_start();
		?>
		<div id="wctmw_tracking_form_<?php echo $unique_id; ?>" class="wctmw_tracking_form">
			<!--<label class="wctmw_tracking_code_input_label"><?php _e('Type the order id','woocommerce-trackingmore');?></label> -->
			<input type="text" class="wctmw_tracking_code_input" id="wctmw_tracking_code_input_<?php echo $unique_id; ?>" value=""></input>
			<button class="wctmw_tracking_code_button button " id="wctmw_tracking_code_button_<?php echo $unique_id; ?>" data-id="<?php echo $unique_id; ?>"><?php _e('Track','woocommerce-trackingmore');?></button>
		</div>
		<div id="wctmw_loader_<?php echo $unique_id; ?>" class="wctmw_loader" style="background-image: url('<?php echo WCTMW_PLUGIN_PATH;?>/img/loader.gif');"></div>
		<div id="wctmw_tracking_info_box_<?php echo $unique_id; ?>" class="wctmw_tracking_info_box_in_site"></div>
		<?php
		return ob_get_clean();
	}
	public function replace_shortcodes($message, $order)
	{
		global $wctmw_order_model, $wctmw_option_model;
		
		$result = array();
		$order_tracking_data = $wctmw_order_model->get_tracking_data($order);
		$companies_complete_list = $wctmw_option_model->get_complete_companies_list();
		
		foreach($order_tracking_data as $tracking_data)
		{
			$tracking_url = $wctmw_option_model->get_options('custom_tracking_url', "https://www.trackingmore.com/".$tracking_data['carrier_code']."-tracking.html?number=");
			$tracking_code = trim($tracking_data['tracking_number']);
			$carrier_img = '<img class="wctmw_courier_logo" width="32" src="https:'.$wctmw_option_model->get_company_logo($tracking_data['carrier_code']).'" alt="img" />';
			
			$tmp_result = str_replace('[tracking_code]', $tracking_code, $message);
			$tmp_result = str_replace('[tracking_company_name]', $carrier_img.wctmw_get_value_if_set($companies_complete_list, $tracking_data['carrier_code'], $tracking_data['carrier_code']), $tmp_result);
			$tmp_result = str_replace('[tracking_note]', $tracking_data['comment'], $tmp_result);
			$tmp_result = str_replace('[tracking_url]', $tracking_url.$tracking_code, $tmp_result);
			$tmp_result = str_replace('[order_url]', add_query_arg('view-order', $order->get_id(), get_permalink( get_option( 'woocommerce_myaccount_page_id' ) )  ), $tmp_result);
			
			$result[] = $tmp_result;
		}
		
		return $result;
	}
	function insert_tracking_info($order, $order_tracking_data, $template, $tracking_message)
	{
		global $wctmw_option_model;
		
		$companies_complete_list = $wctmw_option_model->get_complete_companies_list();
		$counter = 0;
		
		foreach($order_tracking_data as $tracking_data)
		{
			$tracking_url = $wctmw_option_model->get_options('custom_tracking_url', "https://www.trackingmore.com/".$tracking_data['carrier_code']."-tracking.html?number=");
			$tracking_code = trim($tracking_data['tracking_number']);
			$carrier_img = '<img class="wctmw_courier_logo" width="32" src="https:'.$wctmw_option_model->get_company_logo($tracking_data['carrier_code']).'" alt="img" />';
			
			$tmp_result = str_replace('[tracking_code]', $tracking_code, $tracking_message);
			$tmp_result = str_replace('[tracking_company_name]', $carrier_img.wctmw_get_value_if_set($companies_complete_list, $tracking_data['carrier_code'], $tracking_data['carrier_code']), $tmp_result);
			$tmp_result = str_replace('[tracking_note]', $tracking_data['comment'], $tmp_result);
			$tmp_result = str_replace('[tracking_url]', $tracking_url.$tracking_code, $tmp_result);
			$tmp_result = str_replace('[order_url]', add_query_arg('view-order', $order->get_id(), get_permalink( get_option( 'woocommerce_myaccount_page_id' ) )  ), $tmp_result);
			
			$template = str_replace("[tracking_message_{$counter}]", $tmp_result, $template);
			
			$counter++;
		}
		
		return $template;
	}
	function remove_tracking_message_shortcode($template, $tracking_data)
	{
		if(empty($tracking_data))
			return str_replace("[tracking_message]", "", $template);
		
		$result = "";
		for($i = 0; $i<count($tracking_data); $i++)
		{
			$result .= "[tracking_message_{$i}]";
		}
	
		return str_replace("[tracking_message]", $result, $template);
	}
	function shortcode_to_text($message,  $order = null, $user_id = null)
	{
		$is_order = isset($order);
		$account_shortcodes = array('[account_first_name]', '[account_last_name]', '[account_email]');
		$order_shortcodes = array('[order_id]', '[order_total]', '[order_date]');
		$billing_shortcodes = array('[billing_first_name]', '[billing_last_name]', '[billing_email]', '[billing_company]', '[billing_company]', '[billing_phone]', '[billing_country]', '[billing_state]', '[billing_city]', '[billing_post_code]', '[billing_address_1]', '[billing_address_2]', '[formatted_billing_address]');
		$shipping_shortcodes = array('[shipping_first_name]', '[shipping_last_name]', '[shipping_company]', '[shipping_phone]', '[shipping_country]', '[shipping_state]', '[shipping_city]', '[shipping_post_code]', '[shipping_address_1]', '[shipping_address_2]', '[formatted_shipping_address]');
		
		//list: 
		// - account: 			[account_first_name], [account_last_name], [account_email]
		// - order:   			[order_id], [order_total], [order_date]
		// - billing/shipping:  [billing_first_name], [billing_last_name], [billing_email], [billing_company], [billing_company], [billing_phone], [billing_country], [billing_state], [billing_city], [billing_post_code], [billing_address_1], [billing_address_2], [formatted_billing_address]
		
		//Customer
		/* $customer = new WC_Customer( $user_id );
		foreach($account_shortcodes as $current_shortcode)
			if (strpos($message, $current_shortcode) !== false)
			{
				$method_name = "get_".str_replace(array('[',']', 'account_'), "", $current_shortcode);
				$value = is_callable ( array($customer , $method_name) ) ? $customer->$method_name() : "";
				$message = str_replace($current_shortcode, $value, $message);
			} */
		
		//Order		
		$message = str_replace('[order_url]', add_query_arg('view-order', $order->get_id(), get_permalink( get_option( 'woocommerce_myaccount_page_id' ) )  ), $message);
		foreach($order_shortcodes as $current_shortcode)
			if (strpos($message, $current_shortcode) !== false)
			{
				$original_method_name = $method_name = str_replace(array('[',']'), "", $current_shortcode);
				switch($method_name)
				{
					case 'order_id': $method_name = 'get_order_number'; break;
					case 'order_total': $method_name = 'get_formatted_order_total'; break;
					case 'order_date': $method_name = 'get_date_created'; break;
				}
				$value = $order != null && is_callable ( array($order , $method_name) ) ? $order->$method_name() : "";
				if(is_object($value) && get_class($value) == 'WC_DateTime')
				{
					$value = $value->date_i18n(get_option('date_format')." ".get_option('time_format'));
					//wcsts_var_dump($value);
				}
				$message = str_replace($current_shortcode, $value, $message);
			}
		foreach($billing_shortcodes as $current_shortcode)
			if (strpos($message, $current_shortcode) !== false)
			{
				$method_name = "get_".str_replace(array('[',']'), "", $current_shortcode);
				$value = $order != null && is_callable ( array($order , $method_name) ) ? $order->$method_name() : "";
				$message = str_replace($current_shortcode, $value, $message);
			}
		foreach($shipping_shortcodes as $current_shortcode)
			if (strpos($message, $current_shortcode) !== false)
			{
				$method_name = "get_".str_replace(array('[',']'), "", $current_shortcode);
				$value = $order != null && is_callable ( array($order , $method_name) ) ? $order->$method_name() : "";
				$message = str_replace($current_shortcode, $value, $message);
			}
		//wp_die();
		return $message;
	}
}
?>