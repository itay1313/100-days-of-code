<?php 
class WCTMW_TrackingMore
{
	var $trackingmore_api;
	var $api_request_errors = array('Unauthorized','Bad Request', 'No Content', 'Payment Required', 'Forbidden', 'Not Found', 'Method Not Allowed', 'Conflict', 'Too Many Requests', 'Server error' ,'Service Unavailable');
	var $shipping_statuses = array();
	public function __construct()
	{
		$this->shipping_statuses = array("pending" => __('Pending', 'wwoocommerce-trackingmore'),
								   "notfound" => __('Not found', 'wwoocommerce-trackingmore'),
								   "transit" => __('In transit', 'wwoocommerce-trackingmore'),
								   "pickup" => __('Picked up', 'wwoocommerce-trackingmore'),
								   "delivered" => __('Delivered', 'wwoocommerce-trackingmore'),
								   "undelivered" => __('Undelivered', 'wwoocommerce-trackingmore'),
								   "exception" => __('Exception', 'wwoocommerce-trackingmore'),
								   "expired" => __('Expired', 'wwoocommerce-trackingmore')
		);
	}
	
	private function init($print_message = true)
	{
		if(isset($this->trackingmore_api))
			return $this->trackingmore_api;
		
		global $wctmw_option_model;
		$api_key = $wctmw_option_model->get_options('api_key');
		if($api_key == "")
		{
			if($print_message)
				echo "<span class='wctmw_error' style='color:#e74c3c; font-weight:bold;'>".__('Insert a valid API Key', 'oocommerce-trackingmore')."</span>";
			return false;
		}
		$this->trackingmore_api = new Trackingmore($api_key);
		return true;
	}
	private function api_request_error($data, $print_message = true)
	{
		$result = in_array($data['meta']["type"], $this->api_request_errors);
		if($result && $print_message)
			echo "<span class='wctmw_error' style='color:#e74c3c; font-weight:bold;'>(".$data['meta']["code"].") ".$data['meta']["message"]."</span>";
		
		return $result;
	}
	public function retrieve_and_create_curriers_list()
	{
		if(!$this->init())
			return false;
		
		global $wctmw_option_model;
		$tracks_list = $this->trackingmore_api->getCarrierList();
		
		//wctmw_var_dump($tracks_list);
		
		//if(/* $tracks_list['meta']["code"] == 4021 */ in_array($tracks_list['meta']["type"], $this->api_request_errors))
		if($this->api_request_error($tracks_list))
			return false;
		
		else if($tracks_list['meta']["code"] == 200)
			$wctmw_option_model->create_companies_list($tracks_list['data']);
		
		return true;
	}
	//updates (or cretes if not existing) the tracking info
	public function update_tracking($data)
	{
		$courier_slug = $data['carrier_code'];
		$tracking_code = $data['tracking_number'];
		
		if(!$this->init())
			return false;
		
		$response;
		
		foreach($data['optional_parameters'] as $param_key => $param_value)
			$tracking_info[$param_key] = $param_value;
			
		try
		{
			$response = $this->trackingmore_api->createTracking($courier_slug, $tracking_code, $tracking_info);
			/* wctmw_var_dump($response); */
			
		}
		catch(Exception $e) 
		{
			/* wctmw_var_dump("error");
			wctmw_var_dump($e); */
		}
			
		//Updating because already existing	
		if($response["meta"]["code"] == 4016)
		{
			$response = $this->trackingmore_api->updateTrackingItem($courier_slug, $tracking_code, $data['optional_parameters']);
			/* wctmw_var_dump("Exists");
			wctmw_var_dump($response); */
			return array('result'=> true, 'response' => $response); 
		}
		else if($response["meta"]["code"] == 4002 || $response["meta"]["code"] == 4013)//No valid API key or no valid number
			return array('result'=> false, 'response' => $response);
		
		return array('result'=> true, 'response' => $response);
	}
	public function get_tracking_data($slug, $tracking_code, $print_error = true)
	{
		if(!$this->init($print_error))
			return false;
		
		//To  avoid Exceeded API limits error
		sleep(1);
		
		return $this->trackingmore_api->getRealtimeTrackingResults($slug,$tracking_code);
	}
	public function delete_tracking($data)
	{
		if(!$this->init())
			return false;
		return $this->trackingmore_api->deleteTrackingItem($data['carrier_code'],$data['tracking_number']);
	}
	
	public function render_tracking_info_box($data, $lang, $order_id)
	{
		global $wctmw_time_model, $wctmw_option_model;
		
		$options = $wctmw_option_model->get_options();
		$display_collapse_button =  wctmw_get_value_if_set($options, array('frontend_order_details_page', 'timeline_show_collapsed'), 'no') == 'yes';
		$counter = 0;
		foreach((array)$data as $unique_id => $tracking_data)
		{
			
			$tracking_info = $this->get_tracking_data($tracking_data['carrier_code'], $tracking_data['tracking_number'], false );
			
			//wctmw_var_dump($tracking_data);
			//wctmw_var_dump($tracking_info);
			
			//Errors
			if(wctmw_get_value_if_set($tracking_info, array('meta', 'code'), false) != 200 && wctmw_get_value_if_set($tracking_info, array('meta', 'message'), '') != '')
			{
				echo "<h3><img class='wctmw_courier_logo' width='32' src='".$wctmw_option_model->get_company_logo($tracking_data['carrier_code'])."'></img>".$wctmw_option_model->get_company_by_name($tracking_data['carrier_code']).": ".$tracking_data['tracking_number']."</h3>";
				echo "<p class='wctmw_error_description'>".__( 'Error: ', 'woocommerce-trackingmore' ).$tracking_info['meta']['message']."</p>";
				continue;
			}
			
			$expected_delivery = /* $tracking_info['data']['tracking']['expected_delivery'] != null ? $wctmw_time_model->format_data_according_wordpress_settings($tracking_info['data']['tracking']['expected_delivery'], false) : */ "";
			$status_message = ucwords($tracking_info['data']['items'][0]['status']);
			$tracking_number = $tracking_info['data']['items'][0]['tracking_number'];
			$company_name = $wctmw_option_model->get_company_by_name($tracking_info['data']['items'][0]['carrier_code']);
			$company_slug = $tracking_info['data']['items'][0]['carrier_code'];
			$status =  strtolower( $tracking_info['data']['items'][0]['status']); 
			$tracking_checkpoints =  $tracking_info["data"]["items"][0]["origin_info"]['trackinfo'];
			$collapse_id = rand(1312, 999999);
			$collapse_elem_to_show = 0;
			$collapse_counter = 0;
			
			if(empty($tracking_checkpoints))
			{
				echo "<h3><img class='wctmw_courier_logo' width='32' src='".$wctmw_option_model->get_company_logo($tracking_data['carrier_code'])."'></img>".$wctmw_option_model->get_company_by_name($tracking_data['carrier_code']).": ".$tracking_data['tracking_number']."</h3>";
				echo "<p class='wctmw_error_description'>".__( 'No tracking info avaliable at the moment. Please try again later', 'woocommerce-trackingmore' )."</p>";
				continue;
			}
			
			if(wctmw_get_value_if_set($options, array('frontend_order_details_page', 'timeline_events_order'), 'oldest_to_recent') == 'oldest_to_recent')
			{
				$tracking_checkpoints = array_reverse($tracking_checkpoints);
				$collapse_elem_to_show = count($tracking_checkpoints) - 1;
			}
			
			$counter = 1;
			if($status == 'pending'):
				echo "<h3><img class='wctmw_courier_logo' width='32' src='".$wctmw_option_model->get_company_logo($tracking_data['carrier_code'])."'></img>".$wctmw_option_model->get_company_by_name($tracking_data['carrier_code']).": ".$tracking_data['tracking_number']."</h3>";
				echo "<p class='wctmw_error_description'>".__( 'Please try again later, we are awaiting tracking info from the carrier.', 'woocommerce-trackingmore' )."</p>";
			else:
				?>
				<h2 class="wctmw_shipping_company_name"><img class="wctmw_courier_logo" width="32" src="<?php echo $wctmw_option_model->get_company_logo($tracking_data['carrier_code']); ?>"></img><?php echo $company_name.": ".$tracking_number;?> <?php if($display_collapse_button):?><a href="#" data-id="<?php echo $collapse_id;?>" class="wctmw_collapse_button" data-status="show"><?php _e( '(click for details)', 'woocommerce-aftership' );?></a><?php endif;?></h2>
				<div class="wctmw_staus_container <?php echo "wctmw_staus_container_".$status; ?>">
					<span class="<?php echo $expected_delivery == "" ? 'wctmw_status_full_text' : 'wctmw_status_left_text'?>"><?php echo $status_message; ?></span>
					<?php if($expected_delivery != ""): ?>
						<span class="wctmw_status_right_text"><?php echo sprintf(__( 'scheduled: %s', 'woocommerce-trackingmore' ), $expected_delivery); ; ?></span>
					<?php endif;?>
				</div>
				<ul class="timeline">
				<?php 
				foreach($tracking_checkpoints as $tracking_checkpoint):
						$current_status = strtolower($tracking_checkpoint["checkpoint_status"]);
						$collapse_hide_current_elem = $display_collapse_button && $collapse_counter != $collapse_elem_to_show;
						$collapse_counter++;
				?>
					
					<li class="<?php if($counter++ % 2 == 0) echo 'timeline-inverted'; if($collapse_hide_current_elem) echo ' wctmw_hide_elem wctmw_collapse_'.$collapse_id ?> ">
					  <div class="timeline-badge wctmw_badge"><img class="wctmw_shipping_badge" src="<?php echo WCTMW_PLUGIN_PATH; ?>/img/trackingmore/<?php echo $current_status;?>.png"></img></div>
					  <div class="timeline-panel">
						<div class="timeline-heading">
						  <h4 class="timeline-title"><?php echo $counter - 1; ?>. <?php echo ucwords(strtolower($tracking_checkpoint["StatusDescription"])); ?></h4>
						  <p>
							<span class="timeline-location"><?php echo $tracking_checkpoint["Details"]; ?></span>
							<span class="text-muted"><?php echo $wctmw_time_model->format_data_according_wordpress_settings ( $tracking_checkpoint["Date"] ); //2018-02-24T18:11:10 ?></span>
						  </p>
						</div>
						<!-- <div class="timeline-body">
						  <p>Mussum ipsum cacilds, vidis litro abertis.</p>
						</div> -->
					  </div>
					</li>
				<?php endforeach; ?>
				</ul>
				<?php 
			endif;
		}
		/* return ob_get_clean(); */
	}
}
?>