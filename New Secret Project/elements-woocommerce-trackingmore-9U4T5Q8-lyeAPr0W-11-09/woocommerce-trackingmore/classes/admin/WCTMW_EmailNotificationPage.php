<?php 
class WCTMW_EmailNotificationPage
{
	public function __construct()
	{
		
	}
	//rplc: TrackingMore, woocommerce-trackingmore, wctmw, WCTMW
	public function render_page()
	{
		global $wctmw_order_model, $wctmw_option_model, $wctmw_wpml_model;
		//Assets
		wp_enqueue_style( 'wctmw-admin-common', WCTMW_PLUGIN_PATH.'/css/admin-common.css');
		wp_enqueue_style( 'wctmw-admin-email-settings', WCTMW_PLUGIN_PATH.'/css/admin-email-settings-page.css');
		
		if(isset($_POST['submit']))
		{
			if(!wctmw_get_value_if_set($_POST, 'wctmw_email_options', false))
				$_POST['wctmw_email_options'] = array();
			
			if(!wctmw_get_value_if_set($_POST, array('wctmw_email_options', 'order_statuses'), false))
				$_POST['wctmw_email_options']['order_statuses'] = array();
			
			$wctmw_option_model->save_email_options($_POST['wctmw_email_options']);
		}
		
		$options = $wctmw_option_model->get_email_options(); //false at first run
		
		?>
		<div class="wrap white-box">
			<!-- <form action="options.php" method="post" > -->
				<form action="" method="post" >
				<?php //settings_fields('wctmw_options_group'); ?> 
					<h2 class="wctmw_section_title wctmw_no_margin_top"><?php _e('WooCommerce notification emails', 'woocommerce-trackingmore');?></h3>
						
					<h3 class="wctmw_no_margin_top"><?php _e('Order statuses', 'woocommerce-trackingmore');?></h3>	
					<div class="wctmw_option_group">
						<p><?php _e('Tracking info are included in <strong>every</strong> WooCommerce order notification email according to the current order status. Select for which status tracking info will be embedded into emails.', 'woocommerce-trackingmore');?></p>
						<?php 
							$statuses = $wctmw_order_model->get_order_statuses();
							foreach ($statuses as $status_code => $status_name):
								 $selected =/*  $options != false && */ wctmw_get_value_if_set($options, array('order_statuses', $status_code), false) ? " checked='checked' " : " "; 
								 //$selected = $options == false && $status_code == 'wc-completed' ? " checked='checked' " : $selected; //first run
							?>
							<div class="wctmw_checkbox_container_auto_width">
								<input type="checkbox" class="wctmw_option_checbox_field" name="wctmw_email_options[order_statuses][<?php echo $status_code; ?>]" value="true" <?php echo  $selected; ?> ><?php echo $status_name; ?></input>
							</div>
						<?php endforeach; ?>
					</div>
					
					<h3><?php _e('Position', 'woocommerce-trackingmore');?></h3>
					<div class="wctmw_option_group">
						<p><?php _e('Tracking info can be displayed before or after the <strong>order table</strong>.', 'woocommerce-trackingmore');?></p>
						<select name="wctmw_email_options[position]">
							<option value ="woocommerce_email_before_order_table" <?php selected( wctmw_get_value_if_set($options, array('position'), 'woocommerce_email_before_order_table'), 'woocommerce_email_before_order_table' ); ?>><?php _e('Before', 'woocommerce-trackingmore');?></option>
							<option value ="woocommerce_email_after_order_table"<?php selected( wctmw_get_value_if_set($options, array('position'), 'woocommerce_email_before_order_table'), 'woocommerce_email_after_order_table' ); ?>><?php _e('After', 'woocommerce-trackingmore');?></option>
						</select>
					</div>
						
					<h3><a id="messages"></a><?php _e('Message', 'woocommerce-trackingmore');?></h3>
					<div class="wctmw_option_group">
						<p><?php _e('In case of multiple shipping, the plugin will print a message for each tracking info. You can use the following shortcodes: ', 'woocommerce-trackingmore');?></p>
						<ol id="wctmw_shortcode_list">
							<li><?php _e('<strong>[tracking_code]</strong>', 'woocommerce-trackingmore');?></li>
							<li><?php _e('<strong>[tracking_company_name]</strong>', 'woocommerce-trackingmore');?></li>
							<li><?php _e('<strong>[tracking_note]</strong>', 'woocommerce-trackingmore');?></li>
							<li><?php _e('<strong>[order_url]</strong>', 'woocommerce-trackingmore');?></li>
							<li><?php _e('<strong>[tracking_url]</strong> (this will redirect to the TrackingMore site)', 'woocommerce-trackingmore');?></li>
						</ol>
						<div id="wctmw_messages_container">
						<?php	
							
							$langs =  $wctmw_wpml_model->get_langauges_list();
							foreach($langs as $lang_data): ?>
									<div class="wctmw_message_container">
										<?php if($lang_data['country_flag_url'] != "none"): ?>
											<img src=<?php echo $lang_data['country_flag_url']; ?> /><label class="wctmw_label"> <?php echo ucwords($lang_data['language_code']); ?></label>
										<?php endif; 
										
										 $content = wctmw_get_value_if_set($options , array('message', $lang_data['language_code']), "");
										 wp_editor( $content, "wctmw_message_editor_".$lang_data['language_code'], array( 'media_buttons' => false,
																														   'textarea_rows' => 8,
																														   'tinymce' => true,
																														   "wpautop" => false,
																														   'textarea_name'=>"wctmw_email_options[message][".$lang_data['language_code']."]")); ?>
									</div>
							<?php endforeach; ?>
						</div>	
					</div>
										
					<h2 class="wctmw_section_title wctmw_no_margin_top"><?php _e('Active notification', 'woocommerce-trackingmore');?></h3>
					
					<div id="wctmw_shortcode_container">
						<p><?php _e('Active notfications can be sent through the admin order details page to actively notify the customer about one or more shipping. You can use the following shortcodes, the <strong>[tracking_message]</strong> (that can be used only in the Tempalte message) will render the tracking messages that will containing the tracking data:', 'woocommerce-trackingmore');?></p>
					
						<label><?php _e('Order data', 'woocommerce-support-ticket-system');?></label>
						[order_id], [order_total], [order_date], [order_url]
						<br><br>
						<label><?php _e('Billing data', 'woocommerce-support-ticket-system');?></label>
						[billing_first_name], [billing_last_name], [billing_email], [billing_company], [billing_company], [billing_phone], [billing_country], [billing_state], [billing_city], [billing_post_code], [billing_address_1], [billing_address_2], [formatted_billing_address]
						<br><br>
						<label><?php _e('Shipping data', 'woocommerce-support-ticket-system');?></label>
						[shipping_first_name], [shipping_last_name], [shipping_company], [shipping_phone], [shipping_country], [shipping_state], [shipping_city], [shipping_post_code], [shipping_address_1], [shipping_address_2], [formatted_shipping_address]
						<br><br>
						<label><?php _e('Tracking data (can be used only for the Template)', 'woocommerce-support-ticket-system');?></label>
						[tracking_message]
					</div>
					
					<h3><?php _e('1. Subject', 'woocommerce-trackingmore');?></h3> 
						<div class="wctmw_option_group">
						<?php foreach($langs as $lang_data): ?>
									<div class="wctmw_message_container">
										<?php if($lang_data['country_flag_url'] != "none"): ?>
											<img src=<?php echo $lang_data['country_flag_url']; ?> /><label class="wctmw_label"> <?php echo ucwords($lang_data['language_code']); ?></label>
										<?php endif; 
										
								
										$content = wctmw_get_value_if_set($options , array('active_notification', 'subject', $lang_data['language_code']), "");
										?>
										<input type="text" class="wctmw_active_notification_subject" value="<?php echo $content;?>" name="wctmw_email_options[active_notification][subject][<?php echo $lang_data['language_code']; ?>]"></input>
									</div>
							<?php endforeach; ?>
					</div>	
					
					<h3><?php _e('2. Template', 'woocommerce-trackingmore');?></h3> 
					<div class="wctmw_option_group">
						<div id="wctmw_messages_container">
						<?php	
							
							foreach($langs as $lang_data): ?>
									<div class="wctmw_message_container">
										<?php if($lang_data['country_flag_url'] != "none"): ?>
											<img src=<?php echo $lang_data['country_flag_url']; ?> /><label class="wctmw_label"> <?php echo ucwords($lang_data['language_code']); ?></label>
										<?php endif; 
										
										$content = wctmw_get_value_if_set($options , array('active_notification', 'template',  $lang_data['language_code']), "");
										wp_editor( $content, "wctmw_message_active_notification_template_editor_".$lang_data['language_code'], array( 'media_buttons' => false,
																														   'textarea_rows' => 8,
																														   'tinymce' => true,
																														   "wpautop" => false,
																														   'textarea_name'=>"wctmw_email_options[active_notification][template][".$lang_data['language_code']."]")); ?>
									</div>
							<?php endforeach; ?>
						</div>	
					</div>
					
					<h3><?php _e('3. Tracking info', 'woocommerce-trackingmore');?></h3> 
					<div class="wctmw_option_group">
						<p><?php _e('This is the message printed when using the <strong>[tracking_message]</strong> shortcode inside the <strong>Template</strong> configured in the previous step. The plugin will print a message for each shipping associated to the order. The following shortcode can be used: ', 'woocommerce-support-ticket-system');?></p>
						<ol id="wctmw_shortcode_list">
							<li><?php _e('<strong>[tracking_code]</strong>', 'woocommerce-trackingmore');?></li>
							<li><?php _e('<strong>[tracking_company_name]</strong>', 'woocommerce-trackingmore');?></li>
							<li><?php _e('<strong>[tracking_note]</strong>', 'woocommerce-trackingmore');?></li>
							<li><?php _e('<strong>[order_url]</strong>', 'woocommerce-trackingmore');?></li>
							<li><?php _e('<strong>[tracking_url]</strong> (this will redirect to the TrackingMore site)', 'woocommerce-trackingmore');?></li>
						</ol>
						<div id="wctmw_messages_container">
						<?php	
							
							foreach($langs as $lang_data): ?>
									<div class="wctmw_message_container">
										<?php if($lang_data['country_flag_url'] != "none"): ?>
											<img src=<?php echo $lang_data['country_flag_url']; ?> /><label class="wctmw_label"> <?php echo ucwords($lang_data['language_code']); ?></label>
										<?php endif; 
										
										//Heading
										
										//Subject
										
										$content = wctmw_get_value_if_set($options , array('active_notification', 'tracking_message',  $lang_data['language_code']), "");
										wp_editor( $content, "wctmw_message_active_notification_message_editor_".$lang_data['language_code'], array( 'media_buttons' => false,
																														   'textarea_rows' => 8,
																														   'tinymce' => true,
																														   "wpautop" => false,
																														   'textarea_name'=>"wctmw_email_options[active_notification][tracking_message][".$lang_data['language_code']."]")); ?>
									</div>
							<?php endforeach; ?>
						</div>	
					</div>
					<p class="submit">
						<input name="submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save', 'woocommerce-trackingmore'); ?>" />
					</p>
			</form>			
		</div>
		<?php 
	}
}
?>