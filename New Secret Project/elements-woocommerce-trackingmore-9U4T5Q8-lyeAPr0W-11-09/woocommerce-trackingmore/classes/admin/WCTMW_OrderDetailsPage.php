<?php 
class WCTMW_OrderDetailsPage
{
	//rplc: TrackingMore, woocommerce-trackingmore, wctmw, WCTMW
	function __construct()
	{
		add_action( 'add_meta_boxes', array( &$this, 'woocommerce_metaboxes' ) );
		//Now managed via ajax, see Order model
		//add_action( 'woocommerce_process_shop_order_meta', array( &$this, 'woocommerce_process_shop_ordermeta' ), 99, 2 ); //99: using 99 info are not embedded into emails
	}
	
	public function woocommerce_process_shop_ordermeta( $order_id, $post_obj ) 
	{
		global $wctmw_order_model;
		
		if(wctmw_get_value_if_set($_POST, 'wctmw_tracking_data', false))
		{
			 $wctmw_order_model->save_tracking_data($order_id, $_POST['wctmw_tracking_data']);
		}
			
	}
	public function woocommerce_metaboxes() 
	{
		add_meta_box( 'wctmw-woocommerce-trackingmore', __('TrackingMore', 'woocommerce-trackingmore'), array( &$this, 'render_metabox' ), 'shop_order', 'side', 'high');
	}
	public function render_metabox($post = null)
	{
		global $wctmw_html_model, $wctmw_option_model, $wctmw_order_model;
		
		wp_enqueue_style( 'wctmw-add-new-company-box', WCTMW_PLUGIN_PATH.'/css/admin-add-new-company-box.css');	
		wp_enqueue_script( 'wctmw-order-details', WCTMW_PLUGIN_PATH.'/js/admin-order-details-page.js', array('jquery'));	
		wp_register_script( 'wctmw-order-details', WCTMW_PLUGIN_PATH.'/js/admin-order-details-page.js', array('jquery'));	
		wp_localize_script( 'wctmw-order-details', 'wctmw', array(
																'order_id' => $post->ID
																) );
		wp_enqueue_script( 'wctmw-order-details' );
		//Existing data -> done via js to avoid to block the UI -> Handerl defined in WCAF_HTML
		//$data = $wctmw_order_model->get_tracking_data($post->ID);
		//wctmw_var_dump($data);
		/* if(!empty($data))
			$wctmw_html_model->render_tracking_company_widget($data); */
		
		?>
		<div id="wctmw_data_form"> 
			<div id="wctmw_new_companies_container"></div>
			<div id="wctmw_loading"><?php _e('Please wait...', 'woocommerce-trackingmore'); ?></div>
			<?php if(!$wctmw_option_model->api_key_has_been_entered()): ?>
				<span class="wctmw_no_api_key_warning"><?php _e('No valid API Key has been entered thorugh the Options menu.', 'woocommerce-trackingmore'); ?></span>
			<?php else: ?>
				<button id="wctmw_add_new_company_button" class="button button-primary wctmw_button_to_disable"><?php _e('Add tracking', 'woocommerce-trackingmore'); ?></button>
				<button id="wctmw_save" class="button wctmw_button_to_disable"><?php _e('Save', 'woocommerce-trackingmore'); ?></button>
			<?php endif; ?>
		</div>
		<?php 
	}
}
?>