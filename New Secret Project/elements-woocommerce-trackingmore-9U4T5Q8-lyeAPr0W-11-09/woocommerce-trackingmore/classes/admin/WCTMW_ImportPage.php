<?php 
class WCTMW_ImportPage
{
	public function __construct()
	{
		
	}
	public function render_page()
	{
		global $wctmw_option_model;
		
		//Assets
		$js_data =  array(
							 'csv_file_format_error' => __('Invalid file format. Please select a valid CSV file.','wctmw-woocommerce-trackingmore'),
							 'file_selection_error' => __('Select a file first!','wctmw-woocommerce-trackingmore'),
							 'upload_complete_message' => __('100% done!','wctmw-woocommerce-trackingmore'),
							 'not_compliant_browser_error' => __('Please use a fully HTML5 compliant browser. The one you are using does not allow file reading.','wctmw-woocommerce-trackingmore')
							);
							
		wp_enqueue_style( 'wctmw-admin-settings-page', WCTMW_PLUGIN_PATH.'/css/admin-common.css');
		wp_enqueue_style( 'wctmw-admin-import-page', WCTMW_PLUGIN_PATH.'/css/admin-import-page.css');
		
		wp_enqueue_script('wctmw-admin-paperparse', WCTMW_PLUGIN_PATH.'/js/vendor/paperparse/papaparse.js', array('jquery'));
		wp_register_script('wctmw-admin-import-page', WCTMW_PLUGIN_PATH.'/js/admin-import-page.js', array('jquery'));
		wp_register_script('wctmw-admin-import-page-ui', WCTMW_PLUGIN_PATH.'/js/admin-import-page-ui.js', array('jquery'));
		wp_localize_script('wctmw-admin-import-page', 'wctmw', $js_data);
		wp_localize_script('wctmw-admin-import-page-ui', 'wctmw', $js_data);
		wp_enqueue_script('wctmw-admin-import-page' );
		wp_enqueue_script('wctmw-admin-import-page-ui' );
		
		?>
		<div class="wrap white-box">
			<?php if(!$wctmw_option_model->api_key_has_been_entered()): ?>
				<span class="wctmw_no_api_key_warning"><?php _e('No valid API Key has been entered thorugh the Options menu.', 'wctmw-woocommerce-trackingmore'); ?></span>
			<?php else: ?>
				<div id="wctmw_inner_container">
					<div id="wctmw_instruction">
						<h2 class="wctmw_section_title wctmw_no_margin_top"><?php _e('CSV data import', 'wctmw-woocommerce-trackingmore');?></h3>
						<div id="instruction">
							<h3 class="wctmw_no_margin_top"><?php _e('Instruction','wctmw-woocommerce-trackingmore');?></h3>
							<p id="instruction_description"><?php _e('The CSV file must have the following colums:','wctmw-woocommerce-trackingmore');?></p>
							<ul id="field_list">
									<li>order_id</li>
									<li>order_status <span class="normal">(<?php _e('Leave empty to leave order status unchanged, otherwise use the follwing codes to set order status: ', 'wctmw-woocommerce-trackingmore');  
														$counter = 0;
														foreach(wc_get_order_statuses() as $code => $status):
															if($counter > 0)
																echo ", ";
															echo "<strong>".$code."</strong>";
															$counter++;
														endforeach; ?>)</li>
									<!-- <li>force_email_notification <span class="normal">(<?php _e('Leave empty for no notification, otherwise choose one of the following values to resend a notification email: <strong>send_email_new_order</strong>, <strong>send_email_cancelled_order</strong>, <strong>send_email_customer_processing_order</strong>, <strong>send_email_customer_completed_order</strong>, <strong>send_email_customer_refunded_order</strong>, <strong>send_email_customer_invoice</strong>', 'wctmw-woocommerce-trackingmore'); ?>)</li>-->
									<li>carrier_code <span class="normal">(<?php _e('Example: italy-sda, dhl, etc. The company id, named <strong>Courier Slug</strong>, can be found by download this <a target="_blank" href="https://docs.aftership.com/couriers/download"> file</a>', 'wctmw-woocommerce-trackingmore'); ?>)</span></li>
									<li>tracking_number <span class="normal">(<?php _e('Example: 1231R21FT', 'wctmw-woocommerce-trackingmore'); ?>)</span></li>
									<!-- <li>merge_data <span class="normal">(<?php _e('Possible values: <strong>yes</strong>, <strong>no</strong>. If the value is <strong>no</strong>, all the previous tracking info associated to the order will be deleted', 'wctmw-woocommerce-trackingmore'); ?>)</span></li>-->
									<li>note <span class="normal">(<?php _e('<strong>REMOVE ALL "," CHARACTERS</strong> eventually present in this field otherwise import <strong>WILL FAIL</strong>', 'wctmw-woocommerce-trackingmore'); ?>)</span></li>
							</ul>
						</div>	
						<div class="wctmw_option_selector_container">
							<label><?php _e('In case an order already has some tracking info associated, merge the existing info with imported data?', 'wctmw-woocommerce-trackingmore');?></label>
							<p><?php _e('In case of merging, if you try to import an already existing tracking code for an order, it will be ignored.', 'woocommerce-aftership');?></p>
							<select name="merge_data" id="merge_data_selector">
								<option value="yes"><?php _e('Yes', 'wctmw-woocommerce-trackingmore');?></option>
								<option value="no"><?php _e('No', 'wctmw-woocommerce-trackingmore');?></option>
							</select>
						</div>
						<div class="wctmw_option_selector_container">
							<label><?php _e('Select a file', 'wctmw-woocommerce-trackingmore');?></label>
							<input type="file" name="csv_file" id="csv_file_input" accept=".csv"></input>
						</div>				
						<p class="submit">
							<button class="button-primary" id="wctmw_import_button"><?php esc_attr_e('Import', 'wctmw-woocommerce-trackingmore'); ?></button>
						</p>
					</div>
					<div id="wctmw_loader_container">
						<div id="wctmw_progress_bar_container">
							<div id="wctmw_progress_bar_background"><div id="wctmw_progress_bar"></div></div>
							<div id="wctmw_notice_box"></div>				
						</div>		
						
						<p class="submit">
							<button class="button-primary" id="wctmw_import_another_button"><?php esc_attr_e('Import another', 'wctmw-woocommerce-trackingmore'); ?></button>
						</p>
					</div>
				</div>	
			<?php endif; ?>
		</div>
		<?php
	}
}
?>