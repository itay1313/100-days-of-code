<?php 
class WCTMW_HTML
{
	//rplc: TrackingMore, woocommerce-trackingmore, wctmw, WCTMW
	function __construct()
	{
		add_action('wp_ajax_wctmw_load_new_company_widget', array($this, 'ajax_wctmw_load_new_company_widget'));
		add_action('wp_ajax_wctmw_load_existing_company_widgets', array($this, 'ajax_wctmw_load_existing_company_widgets'));
	}
	public function ajax_wctmw_load_new_company_widget()
	{
		$this->render_tracking_company_widget();
		
		wp_die();
	}
	public function ajax_wctmw_load_existing_company_widgets()
	{
		global $wctmw_order_model;
		
		$data = $wctmw_order_model->get_tracking_data($_POST['order_id']);
		$this->render_tracking_company_widget($data, $_POST['order_id']);
		
		wp_die();
	}
	public function render_tracking_company_widget($data = null, $order_id = null)
	{
		global $wctmw_option_model,  $wctmw_order_model;
		
		
		$companies_list = $wctmw_option_model->get_selected_companies_list();
		$companies_complete_list = $wctmw_option_model->get_complete_companies_list();
		$unique_id = rand (12312, 9999999);
		
		?>
		<?php 
			if(!isset($data)):
			?>
			<div class="wctmw_inside" id="wctmw_company_container_<?php echo $unique_id; ?>">
				<input type="hidden" id="wctmw_delete_field_<?php echo $unique_id; ?>" name="wctmw_tracking_data[<?php echo $unique_id; ?>][type]" value="creation"></input>
				<label class="wctmw_label"><?php _e('Select company', 'woocommerce-trackingmore'); ?></label>
				<select name="wctmw_tracking_data[<?php echo $unique_id; ?>][carrier_code]">
					<?php foreach($companies_list as $company_id => $company_name): ?>
					<option value="<?php echo $company_id;?>"><?php echo $company_name;?></option>
					<?php endforeach; ?>
				</select>
				
				<label class="wctmw_label"><?php _e('Tracking code', 'woocommerce-trackingmore'); ?></label>
				<span id="wctmw_tracking_code_error_message_<?php echo $unique_id; ?>" class="wctmw_tracking_code_error_message"><?php _e('Cannot be empty', 'woocommerce-trackingmore'); ?></span>
				<input type="text" class="wctmw_tracking_code_input" name="wctmw_tracking_data[<?php echo $unique_id; ?>][tracking_number]"></input>
				
				<label class="wctmw_label"><?php _e('Note', 'woocommerce-trackingmore'); ?></label>
				<textarea name="wctmw_tracking_data[<?php echo $unique_id; ?>][comment]"></textarea>
				<button data-id="<?php echo $unique_id; ?>" class=" button wctmw_delete_button" id="wctmw_delete_button" data-is-temp="true"><?php _e('Delete', 'woocommerce-trackingmore'); ?></button>
			</div>
			<?php
			//Renders existing companies data
			else:
				foreach((array)$data as $unique_id => $tracking_data):
				
				$status = $wctmw_order_model->get_tracking_status($tracking_data['carrier_code'], $tracking_data['tracking_number'], $order_id);
				$tracking_url = $wctmw_option_model->get_options('custom_tracking_url', "https://www.trackingmore.com/".$tracking_data['carrier_code']."-tracking.html?number="); //https://www.trackingmore.com/choose-en-.html // http://track.trackingmore.com/
			?>
				<div class="wctmw_inside" id="wctmw_company_container_<?php echo $unique_id; ?>">
					<input type="hidden" id="wctmw_delete_field_<?php echo $unique_id; ?>" name="wctmw_tracking_data[<?php echo $unique_id; ?>][to_delete]" value="no"></input>
					<input type="hidden" id="wctmw_delete_field_<?php echo $unique_id; ?>" name="wctmw_tracking_data[<?php echo $unique_id; ?>][type]" value="update"></input>
					
					<label class="wctmw_label"><?php echo wctmw_get_value_if_set($companies_complete_list, $tracking_data['carrier_code'], $tracking_data['carrier_code']); ?></label>
					<img class="wctmw_courier_logo" width="64" src="<?php echo $wctmw_option_model->get_company_logo($tracking_data['carrier_code']); ?> "></img>
										
					<label class="wctmw_label"><?php _e('Tracking code', 'woocommerce-trackingmore'); ?></label>
					<!-- <input type="text" name="wctmw_tracking_data[<?php echo $unique_id; ?>][tracking_number]" value="<?php echo $tracking_data['tracking_number']; ?>" disabled="disabled"></input>-->
					<span class="wctmw_tracking_code"><a target="_blank" href="<?php echo $tracking_url .$tracking_data['tracking_number']; ?>"><?php echo $tracking_data['tracking_number']; ?></a></span>
					
					<label class="wctmw_label"><?php _e('Status', 'woocommerce-trackingmore'); ?></label>
					<?php echo $status['status_icon']; ?>
					<span class="wctmw_status_message"><?php echo $status['status'];  ?></span>
					
					<label class="wctmw_label"><?php _e('Last event', 'woocommerce-trackingmore'); ?></label>
					<span class="wctmw_status_message"><?php echo $status['last_event'];  ?></span>
					
					<label class="wctmw_label"><?php _e('Note', 'woocommerce-trackingmore'); ?></label>
					<textarea name="wctmw_tracking_data[<?php echo $unique_id; ?>][comment]" disabled="disabled"><?php echo wctmw_get_value_if_set($tracking_data, 'comment', ''); ?></textarea>
					<small><?php _e('TrackingMore doesn\'t allow updating this field once created.', 'woocommerce-trackingmore'); ?></small>
					
					<label class="wctmw_label"><?php _e('Active notification', 'woocommerce-trackingmore'); ?></label>
					<p class="wctmw_small_description"><?php _e('Send an email containing the tracking info. Email template can be configured through the Email menu. In case of multiple tracking info, the plugin will send only one email containing all the data. Click the <strong>save</strong> button to send the notification.', 'woocommerce-trackingmore'); ?></p>
					<input type="checkbox" value="true" name="wctmw_tracking_data[<?php echo $unique_id; ?>][active_notification]"></input>
					
					<button data-id="<?php echo $unique_id; ?>" class="button wctmw_delete_button" id="wctmw_delete_button" data-is-temp="false"><?php _e('Delete', 'woocommerce-trackingmore'); ?></button>
				</div>
			<?php
				endforeach;
			endif;
		
	}
}
?>