<?php
/*
Plugin Name: WooCommerce TrackingMore
Description: TrackingMore tracking service.
Author: Lagudi Domenico
Version: 3.7
*/


/* Const */
//Domain: woocommerce-trackingmore
define('WCTMW_PLUGIN_PATH', rtrim(plugin_dir_url(__FILE__), "/") ) ;
define('WCTMW_PLUGIN_ABS_PATH', dirname( __FILE__ ) ); ///ex.: "woocommerce/wp-content/plugins/woocommerce-trackingmore"
define('WCTMW_PLUGIN_LANG_PATH', basename( dirname( __FILE__ ) ) . '/languages' ) ;


if ( !defined('WP_CLI') && ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ||
					   (is_multisite() && array_key_exists( 'woocommerce/woocommerce.php', get_site_option('active_sitewide_plugins') ))
					 )	
	)
{
	//For some reasins the theme editor in some installtion won't work. This directive will prevent that.
	if(isset($_POST['action']) && $_POST['action'] == 'edit-theme-plugin-file')
		return;
	
	if(isset($_REQUEST ['context']) && $_REQUEST['context'] == 'edit') //rest api
		return;
		
	if(isset($_POST['action']) && strpos($_POST['action'], 'health-check') !== false) //health check
		return;
	
	if(isset($_REQUEST['is_admin'])) //Fixes and uncompability with Project Manager plugin
		return;
		
	$wctmw_id = 0; //int
	$wctmw_name = "WooCommerce TrackingMore";
	$wctmw_activator_slug = "wctmw-activator";
	
	// Classes Init 
	include_once( "classes/com/WCTMW_Globals.php"); 
	require_once('classes/admin/WCTMW_ActivationPage.php');
	
	add_action('init', 'wctmw_global_init');
	add_action('admin_menu', 'wctmw_init_act');
	if(defined('DOING_AJAX') && DOING_AJAX)
		wctmw_init_act();
	add_action('admin_notices', 'wctmw_admin_notices' );

}
function wctmw_admin_notices()
{
	global $wctmw_notice, $wctmw_name, $wctmw_activator_slug;
	if($wctmw_notice && (!isset($_GET['page']) || $_GET['page'] != $wctmw_activator_slug))
	{
		 ?>
		<div class="notice notice-success">
			<p><?php echo sprintf(__( 'To complete the <span style="color:#96588a; font-weight:bold;">%s</span> plugin activation, you must verify your purchase license. Click <a href="%s">here</a> to verify it.', 'woocommerce-trackingmore' ), $wctmw_name, get_admin_url()."admin.php?page=".$wctmw_activator_slug); ?></p>
		</div>
		<?php
	}
}
function wctmw_setup()
{
	global $wctmw_option_model, $wctwm_trackingmore_model, $wctmw_html_model, $wctmw_order_model, $wctmw_wpml_model, $wctmw_country_model, $wctmw_email_model,
	$wctmw_time_model, $wctmw_shortcode_model;
	
	//com 
	if(!class_exists('WCTMW_TrackingMore'))
	{
		require_once('classes/com/WCTMW_TrackingMore.php');
		$wctwm_trackingmore_model = new WCTMW_TrackingMore();
	}
	if(!class_exists('WCTMW_Wpml'))
	{
		require_once('classes/com/WCTMW_Wpml.php');
		$wctmw_wpml_model = new WCTMW_Wpml();
	}
	if(!class_exists('WCTMW_Option'))
	{
		require_once('classes/com/WCTMW_Option.php');
		$wctmw_option_model = new WCTMW_Option();
	}
	if(!class_exists('WCTMW_HTML'))
	{
		require_once('classes/com/WCTMW_HTML.php');
		$wctmw_html_model = new WCTMW_HTML();
	}
	if(!class_exists('WCTMW_Order'))
	{
		require_once('classes/com/WCTMW_Order.php');
		$wctmw_order_model = new WCTMW_Order();
	}
	if(!class_exists('WCTMW_Country'))
	{
		require_once('classes/com/WCTMW_Country.php');
		$wctmw_country_model = new WCTMW_Country();
	}
	if(!class_exists('WCTMW_Shortcode'))
	{
		require_once('classes/com/WCTMW_Shortcode.php');
		$wctmw_shortcode_model = new WCTMW_Shortcode();
	}
	if(!class_exists('WCTMW_Email'))
	{
		require_once('classes/com/WCTMW_Email.php');
		$wctmw_email_model = new WCTMW_Email();
	}
	if(!class_exists('WCTMW_Time'))
	{
		require_once('classes/com/WCTMW_Time.php');
		$wctmw_time_model = new WCTMW_Time();
	}
	if(!class_exists('Trackingmore'))
	{
		require_once('classes/vendor/trackingmore/track.class.php');	
	}
	
	//admin
	if(!class_exists('WCTMW_SettingsPage'))
		require_once('classes/admin/WCTMW_SettingsPage.php');
	/* if(!class_exists('WCTMW_CourierGenerationPage'))
		require_once('classes/admin/WCTMW_CourierGenerationPage.php'); */
	if(!class_exists('WCTMW_OrderDetailsPage'))
	{
		require_once('classes/admin/WCTMW_OrderDetailsPage.php');
		new WCTMW_OrderDetailsPage();
	}
	if(!class_exists('WCTMW_OrdersListPage'))
	{
		require_once('classes/admin/WCTMW_OrdersListPage.php');
		new WCTMW_OrdersListPage();
	}
	if(!class_exists('WCTMW_EmailNotificationPage'))
	{
		require_once('classes/admin/WCTMW_EmailNotificationPage.php');
	}
	if(!class_exists('WCTMW_ImportPage'))
	{
		require_once('classes/admin/WCTMW_ImportPage.php');
	}
	
	//frontend
	if(!class_exists('Frontend\WCTMW_OrderDetailsPage'))
	{
		require_once('classes/frontend/WCTMW_OrderDetailsPage.php');
		new Frontend\WCTMW_OrderDetailsPage();
	}
	
	add_action('admin_menu', 'wctmw_init_admin_panel');
}
/* Functions */
function wctmw_unregister_css_and_js($enqueue_styles)
{
	
}
function wctmw_global_init()
{
	// Languages 
	load_plugin_textdomain('woocommerce-trackingmore', false, basename( dirname( __FILE__ ) ) . '/languages' );
	/* if(is_admin())
		wctmw_init_act(); */
}
function wctmw_init_act()
{
	global $wctmw_activator_slug, $wctmw_name, $wctmw_id;
	new WCTMW_ActivationPage($wctmw_activator_slug, $wctmw_name, 'woocommerce-trackingmore', $wctmw_id, WCTMW_PLUGIN_PATH);
}
function wctmw_admin_init()
{
	//$remove = remove_submenu_page( 'woocommerce-role-by-amount-spent', 'woocommerce-trackingmore');
}	
function wctmw_init_admin_panel()
{ 
	$place = wctmw_get_free_menu_position(60 , .1);
	$cap = 'manage_woocommerce';
	
	add_menu_page( 'WooCommerce TrackingMore', __('WooCommerce TrackingMore', 'woocommerce-trackingmore'), $cap, 'wctmw-woocommerce-trackingmore', null,   WCTMW_PLUGIN_PATH."/img/menu-icon.png" , (string)$place);
	
	add_submenu_page( 'wctmw-woocommerce-trackingmore', __('TrackingMore - Settings', 'woocommerce-trackingmore'),  __('Settings', 'woocommerce-trackingmore'), $cap, 'woocommerce-trackingmore-settings', 'wctmw_render_admin_page' );	
	add_submenu_page( 'wctmw-woocommerce-trackingmore', __('TrackingMore - Email', 'woocommerce-aftership'),  __('Email', 'woocommerce-aftership'), $cap, 'woocommerce-trackingmore-email-notifications', 'wctmw_render_admin_page' );	
	add_submenu_page( 'wctmw-woocommerce-trackingmore', __('TrackingMore - CSV Import', 'woocommerce-aftership'),  __('Import', 'woocommerce-aftership'), $cap, 'woocommerce-trackingmore-import', 'wctmw_render_admin_page' );	
	//add_submenu_page( 'wctmw-woocommerce-trackingmore', __('TrackingMore - Courier list retriever', 'woocommerce-trackingmore'),  __('Courier list retriever', 'woocommerce-trackingmore'), $cap, 'woocommerce-trackingmore-courier-generation', 'wctmw_render_admin_page' );	
	remove_submenu_page( 'wctmw-woocommerce-trackingmore', 'wctmw-woocommerce-trackingmore');
}
function wctmw_render_admin_page()
{
	if(!isset($_REQUEST['page']))
		return;
	switch($_REQUEST['page'])
	{
		case 'woocommerce-trackingmore-settings':
		
			$settings_page = new WCTMW_SettingsPage();
			$settings_page->render_page();
		break;
		case 'woocommerce-trackingmore-email-notifications':
		
			$settings_page = new WCTMW_EmailNotificationPage();;
			$settings_page->render_page();
		break;
		case 'woocommerce-trackingmore-import':
		
			$settings_page = new WCTMW_ImportPage();;
			$settings_page->render_page();
		break;
		/* case 'woocommerce-trackingmore-courier-generation':
		
			$settings_page = new WCTMW_CourierGenerationPage();
			$settings_page->render_page();
		break; */
	}
}
function wctmw_get_free_menu_position($start, $increment = 0.1)
{
	foreach ($GLOBALS['menu'] as $key => $menu) {
		$menus_positions[] = $key;
	}
	
	if (!in_array($start, $menus_positions)) return $start;

	/* the position is already reserved find the closet one */
	while (in_array($start, $menus_positions)) 
	{
		$start += $increment;
	}
	return (string)$start;
}

function wctmw_var_dump($var)
{
	echo "<pre>";
	var_dump($var);
	echo "</pre>";
}
?>