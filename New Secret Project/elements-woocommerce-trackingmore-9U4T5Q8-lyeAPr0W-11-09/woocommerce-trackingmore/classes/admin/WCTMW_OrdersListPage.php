<?php 
class WCTMW_OrdersListPage
{
	public function __construct()
	{
		add_action( 'manage_edit-shop_order_columns', array( &$this, 'add_tracking_column'), 20, 1 );
		add_action( 'manage_shop_order_posts_custom_column', array( &$this, 'add_tacking_info_to_column'), 10,2);
	}
	//Order list columns
	function add_tracking_column($columns){ 
	
		wp_enqueue_style('wctmw-info-tracking-box', WCTMW_PLUGIN_PATH.'/css/admin-orders-list-page.css');
		wp_enqueue_script('wctmw-info-tracking-box', WCTMW_PLUGIN_PATH.'/js/admin-orders-list-page.js', array('jquery'));
		
		$columns["wctmw_tracking_data"] = __('Tracking', 'woocommerce-trackingmore');
		
		return $columns;
		
	}
	//Order list columns
	function add_tacking_info_to_column($column, $order_id)
	{ 
		global $wctmw_option_model,  $wctmw_order_model;	
	
		switch ( $column ) 
		{
			case "wctmw_tracking_data" :
				/* $order = wc_get_order( $order_id );
				if($order == false || $order == null)
					return; */
				$data = $wctmw_order_model->get_tracking_data( $order_id );
				$companies_list = $wctmw_option_model->get_selected_companies_list();
				$companies_complete_list = $wctmw_option_model->get_complete_companies_list();
				$options = $wctmw_option_model->get_options();
				$counter = 0;
				foreach((array)$data as $unique_id => $tracking_data):
				$tracking_url = $wctmw_option_model->get_options('custom_tracking_url', "https://www.trackingmore.com/".$tracking_data['carrier_code']."-tracking.html?number="); //https://www.trackingmore.com/choose-en-.html // http://track.trackingmore.com/
				?>
					<div class="wctmw_tracking_data_container <?php echo ($counter++)%2==0 ? 'wctmw_tracking_data_container_even' : 'wctmw_tracking_data_container_odd'; ?>">
						
						<?php if(wctmw_get_value_if_set($options, array('orders_list_page', 'carrier_code'), 'yes') == 'yes'): ?>
							<label class="wctmw_label wctmw_company_code_label"><?php _e('Company', 'woocommerce-trackingmore'); ?></label>
							<?php echo wctmw_get_value_if_set($companies_complete_list, $tracking_data['carrier_code'], $tracking_data['carrier_code']); ?>
							<img class="wctmw_courier_logo" width="24" src="<?php echo $wctmw_option_model->get_company_logo($tracking_data['carrier_code']); ?>"></img>
						<?php endif; ?>
						
						<?php if(wctmw_get_value_if_set($options, array('orders_list_page', 'tracking_number'), 'yes') == 'yes'): ?>
							<label class="wctmw_label wctmw_tracking_code_label"><?php _e('Tracking code', 'woocommerce-trackingmore'); ?></label>
							<span class="wctmw_tracking_code"><a target="_blank" href="<?php echo $tracking_url.$tracking_data['tracking_number']; ?>"><?php echo $tracking_data['tracking_number']; ?></a></span>
						<?php endif; ?>
						
						<?php if(wctmw_get_value_if_set($options, array('orders_list_page', 'status'), 'yes') == 'yes'): ?>
							<label class="wctmw_label wctmw_tracking_code_label"><?php _e('Status', 'woocommerce-trackingmore'); ?></label>
							<span class="wctmw_tracking_status" data-order-id="<?php echo $order_id;?>" data-tracking="<?php echo $tracking_data['tracking_number'];?>" data-company="<?php echo $tracking_data['carrier_code'];?>"><img src="<?php echo WCTMW_PLUGIN_PATH.'/img/loader.gif'; ?>" width="24"></img></span>
						<?php endif; ?>
					</div>
				<?php	

				endforeach;
			break; 
			
			
		}
			
	}
}
?>