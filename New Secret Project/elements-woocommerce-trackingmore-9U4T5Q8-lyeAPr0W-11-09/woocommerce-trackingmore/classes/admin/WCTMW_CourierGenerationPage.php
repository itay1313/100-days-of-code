<?php 
class WCTMW_CourierGenerationPage
{
	function __construct()
	{
		
	}
	function render_page()
	{
		global $wctwm_trackingmore_model;
		$wctwm_trackingmore_model->retrieve_curriers_list();
	}
}
?>