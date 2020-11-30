<?php 
class WCTMW_Email
{
	public function __construct()
	{
		add_action('init', array(&$this, 'setup'));
	}
	public function setup()
	{
		global $wctmw_option_model, $wctmw_order_model;
		
		$options = $wctmw_option_model->get_email_options();
		//woocommerce_email_before_order_table ($order, $sent_to_admin, $plain_text, $email)
		//woocommerce_email_after_order_table  ($order, $sent_to_admin, $plain_text, $email)
		add_action($options['position'], array(&$this, 'render_tracking_data'), 10, 4);	
	}
	
	public function render_tracking_data($order, $sent_to_admin, $plain_text, $email = null)
	{
		global $wctmw_option_model, $wctmw_order_model, $wctmw_shortcode_model;
		
		
		$reflect = new ReflectionClass($order);
		//WC_order || WC_Admin_Order
		if($sent_to_admin || !isset($order) || !is_object($order) || ($reflect->getShortName() !== "Order" && $reflect->getShortName() !== "Admin_Order"))
			return;
		
		$email_settings = $wctmw_option_model->get_email_options();
		
		
		//1 - Check order status (remove wc- prefix)
		if(wctmw_get_value_if_set($email_settings,array('order_statuses', $order->get_status()), false) == false)
				return;
			
		$message = wctmw_get_value_if_set($email_settings , array('message', $wctmw_order_model->get_lang($order)), "");
		
		//2 - Shortcode processing (for each tracking data)
		$final_messages = $wctmw_shortcode_model->replace_shortcodes($message, $order);
		
		include WCTMW_PLUGIN_ABS_PATH.'/templates/wc_email.php';
		
	}
	public function send_active_notification_email_with_tracking_codes($wc_order, $tracking_data)
	{
		global $wctmw_option_model, $wctmw_order_model, $wctmw_shortcode_model;
		
		$email_options = $wctmw_option_model->get_email_options();
		$order_lang = $wctmw_order_model->get_lang($wc_order);
		$recipients = $wc_order->get_billing_email();
		$subject = wctmw_get_value_if_set($email_options , array('active_notification', 'subject', $order_lang), "");
		$template = wctmw_get_value_if_set($email_options , array('active_notification', 'template', $order_lang), "");
		$tracking_message = wctmw_get_value_if_set($email_options , array('active_notification', 'tracking_message', $order_lang), "");
		$message = "";
		
		// 1 - remove from subject and tracking info
		$subject = str_replace("[tracking_message]", "", $subject);
		$tracking_message = str_replace("[tracking_message]", "", $tracking_message);
		
		// 2 - replace the [tracking_message] from template with "", or [tracking_message_1], ... , [tracking_messag_n] according tracking info number 
		$template = $wctmw_shortcode_model->remove_tracking_message_shortcode($template, $tracking_data);
		
		// 3 - replace shortcodes from tracking message and insert in into the tempalte
		//$new_tracking_messages = $wctmw_shortcode_model->replace_shortcodes($tracking_message, $wc_order);
		//$template = str_replace("[tracking_message]", implode("", $new_tracking_messages), $template);
		$template = $wctmw_shortcode_model->insert_tracking_info($wc_order, $tracking_data, $template, $tracking_message);
	
		//4 - replace shortcodes from subject and template
		$template = $wctmw_shortcode_model->shortcode_to_text($template,  $wc_order);
		$subject = $wctmw_shortcode_model->shortcode_to_text($subject,  $wc_order);
		
		$mail = WC()->mailer();
		ob_start();
		$mail->email_header(get_bloginfo('name'));
		echo stripcslashes($template);
		$mail->email_footer();
		$message =  ob_get_contents();
		ob_end_clean(); 
		
		
		do_action('wctmw_before_active_notification_email', $tracking_data, $wc_order);
		
		add_filter('wp_mail_from_name',array(&$this, 'wp_mail_from_name'), 99, 1);
		add_filter('wp_mail_from', array(&$this, 'wp_mail_from')/* , 99, 1 */);
		$attachments = /* isset($attachment[$recipients]) ? $attachment[$recipients] : */ array();
		if(!$mail->send( $recipients, $subject, $message, "Content-Type: text/html\r\n", $attachments)) //$mail->send || wp_mail
			wp_mail( $recipients, $subject, $message, "Content-Type: text/html\r\n", $attachments);
		remove_filter('wp_mail_from_name',array(&$this, 'wp_mail_from_name'));
		remove_filter('wp_mail_from',array(&$this, 'wp_mail_from'));
		
		do_action('wctmw_after_active_notification_email', $tracking_data, $wc_order);
	}
	public function wp_mail_from_name($name) 
	{
		/* global $wcsts_text_helper;
		$text = $wcsts_text_helper->get_email_sender_name(); */
		return get_bloginfo('name');
	}
	public function wp_mail_from($content_type) 
	{
		$server_headers = function_exists('apache_request_headers') ? apache_request_headers() : wctmw_apache_request_headers();
		$domain = isset($server_headers['Host']) ? $server_headers['Host'] : null ;
		if(!isset($domain) && isset($_SERVER['HTTP_HOST']))
			$domain = str_replace("www.", "", $_SERVER['HTTP_HOST'] );
		
		return isset($domain) ? 'noprely@'.$domain : $content_type;
	}
}
?>