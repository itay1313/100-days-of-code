<?php 
class WCTMW_Order
{
	//rplc: TrackingMore, woocommerce-trackingmore, wctmw, WCTMW
	var $status_that_can_be_cached = array('delivered', /* 'exception', */ 'expired');
	function __construct()
	{
		add_action( 'wp_ajax_wctmw_save_data', array( &$this, 'ajax_save_data' ) );
		add_action( 'wp_ajax_wctmw_load_order_timelines', array( &$this, 'ajax_load_order_timelines' ) );
		add_action( 'wp_ajax_wctmw_shortcode_load_order_timelines', array( &$this, 'ajax_shortcode_load_order_timelines' ) );
		add_action( 'wp_ajax_nopriv_wctmw_shortcode_load_order_timelines', array( &$this, 'ajax_shortcode_load_order_timelines' ) );
		add_action('wp_ajax_wctmw_ajax_get_order_tracking_status',array(&$this, 'ajax_get_order_tracking_status'));
		//csv
		add_action('wp_ajax_wctmw_csv_import',array(&$this, 'ajax_import_csv_data'));
	}
	
	public function ajax_import_csv_data()
	{
		$csv = json_decode(stripslashes($_POST['csv']));
		$merge_data = wctmw_get_value_if_set($_POST, 'merge_data', 'no') == 'yes';
		//wctmw_var_dump($csv);
		if($csv)
		{
			$this->import_from_csv($csv, $merge_data);
		}
		wp_die();
	}
	public function ajax_get_order_tracking_status()
	{
		$status = $this->get_tracking_status($_POST['carrier_code'], $_POST['tracking_number'], $_POST['order_id']);
		echo $status['status_icon'];
		echo $status['status'];
		wp_die();
	}
	public function ajax_save_data()
	{
		global $wctmw_html_model;
		$data = array();
		parse_str($_POST['serialized_data'], $data);
		
		$this->save_tracking_data($_POST['order_id'], $data['wctmw_tracking_data']);
		
		//render new widges
		$data = $this->get_tracking_data($_POST['order_id']);
		$wctmw_html_model->render_tracking_company_widget($data, $_POST['order_id']);
		wp_die();
	}
	public function get_real_order_id($order_id)
	{
		//Sequential Order Numbers Pro
		if(function_exists('wc_seq_order_number_pro'))
			$order_id = wc_seq_order_number_pro()->find_order_by_order_number( $order_id );
		else if(function_exists('wc_sequential_order_numbers'))
			$order_id = wc_sequential_order_numbers()->find_order_by_order_number( $order_id );
		
		return $order_id;
	}
	public function ajax_shortcode_load_order_timelines()
	{
		global $wctwm_trackingmore_model;
		
		if(!wctmw_get_value_if_set($_POST, 'order_id',false))
		{
			_e('Please enter a valid order id', 'woocommerce-trackingmore');
			wp_die();
		}
		$order_id = trim($_POST['order_id']);
		$order_id = $this->get_real_order_id($order_id);
		$tracking_data = $this->get_tracking_data($order_id);
	
		if(empty($tracking_data))
			echo "<p class='wctmw_message'>".__("Order hasn't shipped yet!", 'woocommerce-trackingmore')."</p>";
		else
			$wctwm_trackingmore_model->render_tracking_info_box($tracking_data, $_POST['lang'], $order_id);
			wp_die();
	}
	public function ajax_load_order_timelines()
	{
		global $wctwm_trackingmore_model;
		
		if(!wctmw_get_value_if_set($_POST, 'order_id',false))
			wp_die();
		
		$tracking_data = $this->get_tracking_data($_POST['order_id']);
		$wctwm_trackingmore_model->render_tracking_info_box($tracking_data, $_POST['lang'], $_POST['order_id']);
		wp_die();
	}
	
	
	public function get_order_statuses($remove_prefix = true)
	{ 
		$statuses = wc_get_order_statuses();
		if($remove_prefix)
		{
			$new_result = array();
			foreach($statuses as $code => $name)
				$new_result[str_replace("wc-","",$code)] = $name;
				
			$statuses = $new_result;
		}
		return $statuses;
	}
	public function get_shipping_post_code($order_id)
	{
		$shipping_postcode = "";
		$wc_order = wc_get_order($order_id);		
		if(isset($wc_order) && $wc_order != false)
		{
			$shipping_postcode = str_replace(" ","", $wc_order->get_shipping_postcode());
		}
		
		return $shipping_postcode;
	}
	public function get_lang($order)
	{
		global $wctmw_wpml_model;
		
		$wc_order = is_object($order) ? $order : wc_get_order($order);
		$default = $wctmw_wpml_model->get_current_language(); //exameple en;

		if(!isset($order) || $order == false)
			return $default;
		
		$result = $wc_order->get_meta('wpml_language');
		$result = !$result || !is_string($result) || $result == "" ? $default : $result;
		
		return strtolower($result);
	}
	public function import_from_csv($csv, $merge_data)
	{
		if(!isset($csv))
			return;
		
		$output_messages = array();
		$order_statuses = wc_get_order_statuses();
		$allowed_email_notification_statuses = array("send_email_new_order,send_email_cancelled_order",
													 "send_email_customer_processing_order",
													 "send_email_customer_completed_order",
													 "send_email_customer_refunded_order",
													 "send_email_customer_invoice" );
		$columns_names = array("order_id",
								"order_status",
								"force_email_notification",
								"carrier_code",
								"tracking_number",
								"note");
		
		$colum_index_to_name = $tracking_data = array();
		
		$row = 0;
		foreach($csv as $current_row)
		{
			if(empty($current_row))
					continue;
			
			//setup
			for ($i=0; $i < count($current_row); $i++) 
			{	
				//headers
				if($row == 0)
				{
					foreach($columns_names as $column_name)
						if($column_name == $current_row[$i])
							$colum_index_to_name[$i] = strtolower(trim($column_name));
				}
				//data
				else 
				{
					if(!isset($tracking_data[$row]))
						$tracking_data[$row] = array();
					if(isset($colum_index_to_name[$i]))
					{
						$tracking_data[$row][$colum_index_to_name[$i]] = $current_row[$i];
					}
				}
			}
			$row++;
		}
		
		//save data process
		if(empty($colum_index_to_name))
		{
			echo json_encode(array( 'error_code' => 1,
									'message' => array(__("The file hasn't a valid header row, import process stopped. Please check the csv file structure.", 'woocommerce-trackingmore'))
									)
							);
			return;
		}
		$row = 0;
		foreach($tracking_data as $row_data)
		{
			$row++;
			$order_id_is_valid = wctmw_get_value_if_set($row_data, 'order_id', false) != false;
			$order_id = wctmw_get_value_if_set($row_data, 'order_id', "");
			
			//Sequential Order Numbers Pro
			$order_id = $order_id_is_valid ? $this->get_real_order_id($order_id) : $order_id;
			
			$wc_order = $order_id_is_valid ? wc_get_order($order_id) : false;
			$error = false;
			
			//Error check
			if(!isset($wc_order) || $wc_order == false)
			{
				$output_messages[] =  sprintf(__("Invalid order id for row %d", 'woocommerce-trackingmore'), $row);
				$error = true;
			}
			else if(wctmw_get_value_if_set($row_data, 'carrier_code', false) == false)
			{
				$output_messages[] =  sprintf(__("Missing carrier_code row %d", 'woocommerce-trackingmore'), $row);
				$error = true;
			}
			else if(wctmw_get_value_if_set($row_data, 'tracking_number', false) == false)
			{
				$output_messages[] =  sprintf(__("Missing tracking_number row %d", 'woocommerce-trackingmore'), $row);
				$error = true;
			}					
			if($error)
				continue;
			
			
			if(!$merge_data)
			{
				$this->delete_all_tracking_data($order_id);
				$old_tracking_data = array();
			}
			else
				$old_tracking_data = $this->get_tracking_data($wc_order);
			
			$unique_id = rand (12312, 9999999);
			$new_data = array(
						$unique_id = array(
							'carrier_code' => $row_data['carrier_code'],
							'tracking_number' => $row_data['tracking_number'],
							'comment' => wctmw_get_value_if_set($row_data, 'note', "")
						)		
			);
			
			//unique check 
			$skip = false;
			foreach((array)$old_tracking_data as /* $unique_id => */ $tracking_data)
			{
				if($tracking_data['tracking_number'] == $row_data['tracking_number'])
					$skip = true;
			}
			if(!$skip)
				$this->save_tracking_data($order_id, array_merge($old_tracking_data, $new_data));
			
			//order_status
			if(wctmw_get_value_if_set($row_data, 'order_status', false) != false)
			{
				$wc_order->set_status(str_replace('wc-', '', $row_data['order_status']));
				$wc_order->save();
			}
		}
		
		echo json_encode(array( 'error_code' => 0,
										'message' => $output_messages
										)
								);
	}
	public function get_tracking_status($slug, $tracking_number, $order_id)
	{
		global $wctmw_option_model, $wctwm_trackingmore_model;
		
		//Get from cache
		$cached_data = $this->get_cached_tracking_data($slug, $tracking_number, $order_id);
		
		if($cached_data == false)
		{
			$checkpoint = $wctwm_trackingmore_model->get_tracking_data($slug, $tracking_number);
			//wctmw_var_dump($checkpoint);
			if($checkpoint == false)
				return array('status' => "", 'last_event'=>"", 'status_icon' =>"", 'status_code' => "");
			
			
			$data = wctmw_get_value_if_set($checkpoint, array('data', 'items', 0), false);
			if($data === false )
				return array('status' => "", 'last_event'=>"", 'status_icon' =>"");
			
			$status_code = wctmw_get_value_if_set($data, array('status'), __('N/A', 'woocommerce-trackingmore'));
			$status = wctmw_get_value_if_set($wctwm_trackingmore_model->shipping_statuses, $status_code, __('N/A', 'woocommerce-trackingmore'));
			$last_event = wctmw_get_value_if_set($data, array('lastEvent'), __('N/A', 'woocommerce-trackingmore'));
				
			//Save cache
			if(in_array($status_code, $this->status_that_can_be_cached))
				$this->set_cached_tracking_data($slug, $tracking_number, $order_id, array('status_code'=> $status_code,
																						  'status' => $status,
																						  'last_event' => $last_event));
		}
		else 
		{
			$status_code = $cached_data['status_code'];
			$status = $cached_data['status'];
			$last_event = $cached_data['last_event'];
		}
		
		$status_icon = $status != 'N/A' ? '<img class="wctmw_shipping_badge" width="24" style="vertical-align: top;" src="'.WCTMW_PLUGIN_PATH.'/img/trackingmore/'.strtolower($status_code).'.png"></img>' : '';
		 
		return array('status' => $status, 'last_event'=>$last_event, 'status_icon' =>$status_icon, 'status_code' => $status_code);
	}
	public function set_cached_tracking_data($slug, $tracking_number, $order_id, $data)
	{
		$wc_order = wc_get_order($order_id);
		if(!isset($wc_order) || $wc_order == false)
			return false;
		
		$order_tracking_data = $this->get_tracking_data($wc_order);
		if(empty($order_tracking_data))
			return false;
		
		foreach($order_tracking_data as $unique_id => $tracking_data)
			if($tracking_data['carrier_code'] == $slug && $tracking_data['tracking_number'] == $tracking_number)
			{
				$order_tracking_data[$unique_id]['cache_status_code'] = $data['status_code'];
				$order_tracking_data[$unique_id]['cache_status'] = $data['status'];
				$order_tracking_data[$unique_id]['cache_last_event'] = $data['last_event'];
			}
		
		$wc_order->update_meta_data('_wctmw_tracking', $order_tracking_data);
		$wc_order->save();
	}
	public function get_cached_tracking_data($slug, $tracking_number, $order_id)
	{
		$wc_order = wc_get_order($order_id);
		if(!isset($wc_order) || $wc_order == false)
			return false;
		
		$order_tracking_data = $this->get_tracking_data($wc_order);
		if(empty($order_tracking_data))
			return false;
		
		foreach($order_tracking_data as $unique_id => $tracking_data)
			if($tracking_data['carrier_code'] == $slug && $tracking_data['tracking_number'] == $tracking_number)
			{
				if(isset($tracking_data['cache_status_code']) &&
					isset($tracking_data['cache_status']) &&
					isset($tracking_data['cache_last_event'])
				   )
				   return array('status' => $tracking_data['cache_status'], 'last_event'=>$tracking_data['cache_last_event'], 'status_code' => $tracking_data['cache_status_code']);
			}
		
		return false;
	}
	public function save_tracking_data($order_id, $data)
	{
		global $wctmw_option_model, $wctmw_country_model, $wctmw_email_model, $wctwm_trackingmore_model;
		$wc_order = wc_get_order($order_id);
		
		if(!$wctmw_option_model->api_key_has_been_entered())
			return;
		
		$old_tracking_data = $this->get_tracking_data($wc_order);
		$active_notification_data = array();
		$shipping_postcode = str_replace(" ","", $wc_order->get_shipping_postcode());
		
		foreach($data as $tracking_unique_id => $shipping_data)
		{
			$is_update = wctmw_get_value_if_set($shipping_data, 'type', "creation") == 'update';
			//remove spaces
			if(wctmw_get_value_if_set($shipping_data, 'tracking_number', "") != "")
				$shipping_data['tracking_number'] = trim($shipping_data['tracking_number']);
			
			//empty
			if((wctmw_get_value_if_set($shipping_data, 'tracking_number', "") == "" || wctmw_get_value_if_set($shipping_data, 'carrier_code', "") == "") && !$is_update )
			{
					unset($data[$tracking_unique_id]);
					continue;
			}
			//delete
			if(wctmw_get_value_if_set($shipping_data, 'to_delete', 'no') == 'yes')
			{
				$old_data = wctmw_get_value_if_set($old_tracking_data, $tracking_unique_id, false);
				if($old_data != false)
				{
					try
					{
						//TrackingMore hasn't any method to delete via ID
						/* if(wctmw_get_value_if_set($old_data, 'tracking_unique_id', false ) != false )
						{
							$wctwm_trackingmore_model->delete_shipping_tracking_by_id(wctmw_get_value_if_set($old_data, 'tracking_unique_id', false )); 
						}
						else */
							$wctwm_trackingmore_model->delete_tracking($old_data);
						
					}catch(Exception $e){/* wctmw_var_dump($e); */};
					unset($data[$tracking_unique_id]);
					//skips the current loop
					continue;
				}
			}
			
			if($is_update)
			{
				//Updata temp metadata. DUE TO SECURITY is prefereable to retrieved from server the already saved data
				$data[$tracking_unique_id]['tracking_number'] = $shipping_data['tracking_number'] = $old_tracking_data[$tracking_unique_id]['tracking_number'];
				$data[$tracking_unique_id]['carrier_code'] = $shipping_data['carrier_code'] = $old_tracking_data[$tracking_unique_id]['carrier_code'];
				$data[$tracking_unique_id]['comment'] = $shipping_data['comment'] = $old_tracking_data[$tracking_unique_id]['comment'];
				
				//clear unuseful index
				unset($data[$tracking_unique_id]['type']);
				unset($data[$tracking_unique_id]['to_delete']);
			}
			
			//$data[$tracking_unique_id]['tracking_postal_code'] =  $shipping_postcode;
			$shipping_data['optional_parameters'] = array('tracking_postal_code' => $shipping_postcode,
												//'order_create_time ' => ,
												//'tracking_ship_date' => ,
												'destination_code' => $wc_order->get_shipping_country(),
												'tracking_destination_country' => $wc_order->get_shipping_country(),
												'tracking_origin_country' => $wctmw_country_model->get_store_base_country(),
												'customer_name' => $wc_order->get_shipping_first_name()." ".$wc_order->get_shipping_last_name(),
												'order_id' => $wc_order->get_order_number(),
												'comment' => $shipping_data["comment"],
												'title' => apply_filters('wctmw_aftership_title', $wc_order->get_order_number(), $wc_order),
												'lang' =>  $this->get_lang($wc_order)
												
			);
			
			//if($wctmw_option_model->get_options('notification_email', false))
				$shipping_data['optional_parameters']['customer_email'] = $wc_order->get_billing_email();
			//if($wctmw_option_model->get_options('notification_sms', false))
				$shipping_data['optional_parameters']['customer_phone'] = $wctmw_country_model->get_area_code_by_country($wc_order->get_shipping_country())." ".$wc_order->get_billing_phone();
	
			$has_been_created = false;
			$exception = false;
			try{
				$update_tracking_result = $wctwm_trackingmore_model->update_tracking($shipping_data);
				/* wctmw_var_dump("Order");
				wctmw_var_dump($update_tracking_result);  */
				
				$has_been_created = $update_tracking_result != false && $update_tracking_result['response']['meta']["code"] == 200;
				if(isset($update_tracking_result['data']))
					$data[$tracking_unique_id]['tracking_unique_id'] = $update_tracking_result['data']['id'];
				
			}
			catch(Exception $e){$has_been_created = false; $exception = true;}
			if(!$has_been_created && !$exception)
			{
				unset($data[$tracking_unique_id]);
				if(!isset($update_tracking_result["response"]["meta"]["message"]))
				{
					echo sprintf(__('<strong>Tracking %s cannot be created, due to the following reason:</strong> %s ', 'woocommerce-trackingmore'), $shipping_data['tracking_number'], $update_tracking_result["response"]["meta"]["type"]);
				}
				else 
					echo sprintf(__('<strong>Tracking %s cannot be created, due to the following reason:</strong> %s ', 'woocommerce-trackingmore'), $shipping_data['tracking_number'], $update_tracking_result["response"]["meta"]["message"]);	
			} 
			else if($exception)
			{
				_e('<strong>Error</strong>', 'woocommerce-trackingmore');
				wctmw_var_dump($exception);
			}
			else 
			{
				if(isset($shipping_data['active_notification'])) //Active notification data
				{
					$active_notification_data[$tracking_unique_id] = $shipping_data;
				}
			}
		}
		
		//Save
		//if($has_been_created)
		{
			$wc_order->update_meta_data('_wctmw_tracking', $data);
			$wc_order->save(); 
			
			//Active notification: send 
			//wctmw_var_dump($active_notification_data);
			if(!empty($active_notification_data))
				$wctmw_email_model->send_active_notification_email_with_tracking_codes($wc_order, $active_notification_data);
		}
	}
	public function delete_all_tracking_data($order_id)
	{
		global $wctwm_trackingmore_model;
		
		$data = $this->get_tracking_data($order_id);
		foreach((array)$data as $single_data)
		{
			try{
					$result = $wctwm_trackingmore_model->delete_tracking($single_data);
					
				}catch(Exception $e){};
		} 
		$wc_order = wc_get_order($order_id);
		if(!isset($wc_order) || $wc_order == false)
			return;
		$wc_order->update_meta_data('_wctmw_tracking', array());
		$wc_order->save();
	}
	public function get_tracking_data($order_id)
	{
		$wc_order = is_object($order_id) ? $order_id : wc_get_order(trim($order_id));
		if(!isset($wc_order) || $wc_order == false)
			return array();
		
		$result = $wc_order->get_meta('_wctmw_tracking');
		
		return isset($result) && is_array($result) ? $result : array();
	}
}
?>