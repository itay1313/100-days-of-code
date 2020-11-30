jQuery(document).ready(function()
{
	jQuery(document).on('click', '.wctmw_tracking_code_button', wctmw_load_order_data);
});
function wctmw_load_order_data(event)
{
	event.preventDefault();
	event.stopPropagation();
	
	const unique_id = jQuery(event.currentTarget).data('id');
	const order_id = jQuery('#wctmw_tracking_code_input_'+unique_id).val();
	
	if(order_id == "")
	{
		alert(wctmw.empty_order_id_error)
		return false;
	}
	
	//UI
	jQuery('#wctmw_loader_'+unique_id).fadeIn();
	jQuery('#wctmw_tracking_info_box_'+unique_id).empty();
	
	const formData = new FormData();
	formData.append('action', 'wctmw_shortcode_load_order_timelines');	
	formData.append('order_id', order_id);	
	formData.append('lang', wctmw.lang);		
	 			
	jQuery.ajax({
			url: wctmw.ajaxurl,
			type: 'POST',
			data: formData,
			async: true,
			success: function (data) 
			{
				//UI
				jQuery('#wctmw_tracking_info_box_'+unique_id).html(data);
				jQuery('#wctmw_loader_'+unique_id).fadeOut();
			},
			error: function (data) 
			{
				//console.log(data);
				//alert("Error: "+data);
			},
			cache: false,
			contentType: false,
			processData: false
		});  
		
	return false;
}