<?php 
namespace Frontend;

class WCTMW_OrderDetailsPage
{
	function __construct()
	{
		add_action('init', array( &$this, 'init' ) );
	}
	function init()
	{
		global $wctmw_option_model;
		$options = $wctmw_option_model->get_options();
		$position = wctmw_get_value_if_set($options, array('frontend_order_details_page', 'timeline_position'), 'woocommerce_order_details_after_order_table');
		
		//add_action('woocommerce_order_details_after_order_table', array( &$this, 'render_time_line_area' ) );
		add_action($position, array( &$this, 'render_time_line_area' ) );
	}
	function render_time_line_area($order)
	{
		if(did_action('woocommerce_thankyou'))
			return;
		
		global $wctmw_wpml_model, $wctmw_option_model;
		
		if(!$wctmw_option_model->api_key_has_been_entered())
			return;
		
		$unique_id = rand ( 25684598 , 25684598698547 );
				
		wp_enqueue_style('wctmw-timeline', WCTMW_PLUGIN_PATH.'/css/frontend-timeline.css');
		wp_enqueue_style('wctmw-timeline-com', WCTMW_PLUGIN_PATH.'/css/frontend-timeline-com.css');
		wp_register_script('wctmw-timeline', WCTMW_PLUGIN_PATH.'/js/frontend-timeline.js', array( 'jquery' ));
		wp_localize_script( 'wctmw-timeline', 'wctmw', array( 'ajax_url' => admin_url('admin-ajax.php'),
															  'order_id' => $order->get_id(),
															  'unique_id' => $unique_id,
															  'lang' => $wctmw_wpml_model->get_current_language()
															));
		wp_enqueue_script('wctmw-timeline');
		?>
		<div id="wctmw_loader_<?php echo $unique_id; ?>" class="wctmw_loader" style="background-image: url('<?php echo WCTMW_PLUGIN_PATH;?>/img/loader.gif');"></div>
		<div id="wctmw_timeline_container_<?php echo $unique_id; ?>" class="wctmw_timeline_container"></div>
		<?php 
	}
}
?>