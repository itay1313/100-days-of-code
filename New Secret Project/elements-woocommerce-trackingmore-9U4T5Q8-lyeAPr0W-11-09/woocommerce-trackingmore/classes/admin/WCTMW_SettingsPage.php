<?php 
class WCTMW_SettingsPage
{
	public function __construct()
	{
		
	}
	
	//rplc: TrackingMore, woocommerce-trackingmore, wctmw, WCTMW
	public function render_page()
	{
		global $wctmw_option_model, $wctwm_trackingmore_model;
		
		//Assets
		wp_enqueue_style( 'wcaf-admin-common', WCTMW_PLUGIN_PATH.'/css/admin-common.css');
		wp_enqueue_style( 'wcaf-admin-settings-page', WCTMW_PLUGIN_PATH.'/css/admin-settings-page.css');
		
		//Save
		$api_key_is_not_valid = false;
		if(isset($_POST['wctmw_options']))
		{
			/* $trackingmore = new WCTMW_TrackingMore(wctmw_get_value_if_set($_POST['wctmw_options'], 'api_key', ""));
			if(!$trackingmore->is_api_key_valid())
			{
				$api_key_is_not_valid = true;
				$_POST['wctmw_options']['api_key'] = "";
			} */
			$wctmw_option_model->save_options($_POST['wctmw_options']);
		}
		
		//Load
		$options = $wctmw_option_model->get_options();
		
		?>
		<div class="wrap white-box">
			<!-- <form action="options.php" method="post" > -->
				<form action="" method="post" >
				<?php //settings_fields('wctmw_options_group'); ?> 
					<h2 class="wctmw_section_title wctmw_no_margin_top"><?php _e('Settings', 'woocommerce-trackingmore');?></h3>
					
					<h3><?php _e('API Key', 'woocommerce-trackingmore');?></h3>
					<div class='wctmw_option_group'>
						<p class="wctmw_general_option_description"><?php _e('Generate a valid API Key by logging (or registering if you have not already done) clicking the following link <a href="https://my.trackingmore.com/get_apikey.php" target="_blank">Generate an Api Key</a>. Once done copy it in the following text area to be able to track order via TrackingMore service.', 'woocommerce-trackingmore'); ?></p>
						<input type="text" size="100" name="wctmw_options[api_key]" placeholder="<?php _e('Type the API Key here', 'woocommerce-trackingmore'); ?>" value="<?php echo wctmw_get_value_if_set($options, 'api_key', ""); ?>"></input>
						<?php if($api_key_is_not_valid): ?>
							<span class="wctmw_error_message"><?php _e('Api Key is not valid!', 'woocommerce-trackingmore'); ?> </span>
						<?php endif; ?>
					</div>
						
					<!--<h3><?php _e('Notification', 'woocommerce-trackingmore');?></h3>
					<p><?php _e("<strong>Note:</strong> for free plan, TrackingMore won't send any notification.", 'woocommerce-trackingmore');?></p>
					<div class='wctmw_option_group'>
						<?php  $selected = wctmw_get_value_if_set($options, 'notification_email', false) ? " checked='checked' " : " "; ?>
						<div class="wctmw_trackingmore_checkbox_container">
							<p class="wctmw_general_option_description"><?php _e('Customer billing email will be used as recipient for TrackingMore notification emails', 'woocommerce-trackingmore');?></p>
							<input type="checkbox" name="wctmw_options[notification_email]" <?php echo $selected; ?> class="wctmw_option_checbox_field" value="true"><?php _e('Send notification emails', 'woocommerce-trackingmore'); ?></input>
						</div>
					</div>
					<div class='wctmw_option_group'>
						<?php  $selected = wctmw_get_value_if_set($options, 'notification_sms', false) ? " checked='checked' " : " "; ?>
						<div class="wctmw_trackingmore_checkbox_container">
							<p class="wctmw_general_option_description"><?php _e('Customer billing phone will be used as recipient for TrackingMore notification smses', 'woocommerce-trackingmore');?></p>
							<input type="checkbox" name="wctmw_options[notification_sms]" <?php echo $selected; ?> class="wctmw_option_checbox_field" value="true"><?php _e('Send notification smses', 'woocommerce-trackingmore'); ?></input>
						</div>
					</div>-->
					
					<h3><?php _e('Admin orders list page', 'woocommerce-trackingmore');?></h3>
					<p><?php _e("Select which element have to be displayed in the admin orders list page", 'woocommerce-trackingmore');?></p>
					<div class="wctmw_inline_block">
						<label><?php _e('Company name', 'woocommerce-trackingmore');?></label>
						<select name="wctmw_options[orders_list_page][carrier_code]">
							<option value ="yes" <?php selected( wctmw_get_value_if_set($options, array('orders_list_page', 'carrier_code'), 'yes'), 'yes' ); ?>><?php _e('Yes', 'woocommerce-trackingmore');?></option>
							<option value ="no"<?php selected( wctmw_get_value_if_set($options, array('orders_list_page', 'carrier_code'), 'yes'), 'no' ); ?>><?php _e('No', 'woocommerce-trackingmore');?></option>
						</select>
					</div>
					<div class="wctmw_inline_block">
						<label><?php _e('Tracking code', 'woocommerce-trackingmore');?></label>
						<select name="wctmw_options[orders_list_page][tracking_number]">
							<option value ="yes" <?php selected( wctmw_get_value_if_set($options, array('orders_list_page', 'tracking_number'), 'yes'), 'yes' ); ?>><?php _e('Yes', 'woocommerce-trackingmore');?></option>
							<option value ="no"<?php selected( wctmw_get_value_if_set($options, array('orders_list_page', 'tracking_number'), 'yes'), 'no' ); ?>><?php _e('No', 'woocommerce-trackingmore');?></option>
						</select>
					</div>
					<div class="wctmw_inline_block">
						<label><?php _e('Status', 'woocommerce-trackingmore');?></label>
						<select name="wctmw_options[orders_list_page][status]">
							<option value ="yes" <?php selected( wctmw_get_value_if_set($options, array('orders_list_page', 'status'), 'yes'), 'yes' ); ?>><?php _e('Yes', 'woocommerce-trackingmore');?></option>
							<option value ="no"<?php selected( wctmw_get_value_if_set($options, array('orders_list_page', 'status'), 'yes'), 'no' ); ?>><?php _e('No', 'woocommerce-trackingmore');?></option>
						</select>
					</div>
					
					<h3><?php _e('Frontend orders details page', 'woocommerce-trackingmore');?></h3>
					<div class="wctmw_inline_block">
						<label><?php _e('Timeline position', 'woocommerce-trackingmore');?></label>
						<select name="wctmw_options[frontend_order_details_page][timeline_position]">
							<option value ="woocommerce_order_details_after_order_table" <?php selected( wctmw_get_value_if_set($options, array('frontend_order_details_page', 'timeline_position'), 'woocommerce_order_details_after_order_table'), 'woocommerce_order_details_after_order_table' ); ?>><?php _e('After order details tabe', 'woocommerce-trackingmore');?></option>
							<option value ="woocommerce_order_details_after_customer_details"<?php selected( wctmw_get_value_if_set($options, array('frontend_order_details_page', 'timeline_position'), 'woocommerce_order_details_after_order_table'), 'woocommerce_order_details_after_customer_details' ); ?>><?php _e('After customr details', 'woocommerce-trackingmore');?></option>
						</select>
					</div>
					<div class="wctmw_inline_block">
						<label><?php _e('Timeline events order', 'woocommerce-trackingmore');?></label>
						<select name="wctmw_options[frontend_order_details_page][timeline_events_order]">
							<option value ="oldest_to_recent" <?php selected( wctmw_get_value_if_set($options, array('frontend_order_details_page', 'timeline_events_order'), 'oldest_to_recent'), 'oldest_to_recent' ); ?>><?php _e('Oldest to most recent', 'woocommerce-trackingmore');?></option>
							<option value ="recent_to_oldest"<?php selected( wctmw_get_value_if_set($options, array('frontend_order_details_page', 'timeline_events_order'), 'oldest_to_recent'), 'recent_to_oldest' ); ?>><?php _e('Most recent to oldest', 'woocommerce-trackingmore');?></option>
						</select>
					</div>
					<div class="wctmw_inline_block">
						<label><?php _e('Show timeline collapsed', 'woocommerce-aftership');?></label>
						<select name="wctmw_options[frontend_order_details_page][timeline_show_collapsed]">
							<option value ="no" <?php selected( wctmw_get_value_if_set($options, array('frontend_order_details_page', 'timeline_show_collapsed'), 'no'), 'no' ); ?>><?php _e('False', 'woocommerce-aftership');?></option>
							<option value ="yes"<?php selected( wctmw_get_value_if_set($options, array('frontend_order_details_page', 'timeline_show_collapsed'), 'no'),  'yes'); ?>><?php _e('True', 'woocommerce-aftership');?></option>
						</select>
					</div>
					<!--<h3><?php _e('Custom tracking URL', 'woocommerce-aftership');?></h3>
					<div class="wctmw_option_group">
						<p class="wctmw_general_option_description"><?php _e('By default the tracking URL used is the <strong>https://track.aftership.com/</strong> address. If you want to use a custom one (for example <strong>http://yourcompany.aftership.com</strong>), type it in the followin text area.', 'woocommerce-trackingmore'); ?></p>
						<input type="text" size="100" name="wctmw_options[custom_tracking_url]" placeholder="<?php _e('Type your custom tracking URL here', 'woocommerce-trackingmore'); ?>" value="<?php echo wctmw_get_value_if_set($options, 'custom_tracking_url', ""); ?>"></input>
					</div>-->
					
					<h2 class="wctmw_section_title "><?php _e('Couriers', 'woocommerce-trackingmore');?></h3>
					<p class="wctmw_company_option_description"><?php _e('Select the couriers that you will use to track shipments', 'woocommerce-trackingmore'); ?></p>
					<?php 
					$trackingmore_companies_list = $wctmw_option_model->get_companies_list();
					foreach($trackingmore_companies_list as $trackingmore_company_data): 
						$selected = wctmw_get_value_if_set($options, array('selected_company', $trackingmore_company_data[1]), false) ? " checked='checked' " : " "; ?>
						<div class="wctmw_trackingmore_checkbox_container">
							<input type="checkbox" name="wctmw_options[selected_company][<?php echo $trackingmore_company_data[1]; ?>]" <?php echo $selected; ?> class="wctmw_option_checbox_field" value="<?php echo $trackingmore_company_data[1]; ?>"><?php echo $trackingmore_company_data[0]; ?></input>
						</div>
					<?php endforeach; ?>
					
				<p class="submit">
					<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'woocommerce-trackingmore'); ?>" />
				</p>
			</form>			
		</div>
		<?php 
	}
}
?>