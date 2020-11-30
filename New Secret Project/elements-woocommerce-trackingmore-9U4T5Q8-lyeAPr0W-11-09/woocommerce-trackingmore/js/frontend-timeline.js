jQuery(document).ready(function()
{
	wctmw_timeline_init();
	jQuery(document).on('click','.wctmw_collapse_button', wcuf_manage_collapse_action);
});
function wcaf_hide_loader()
{
	jQuery('#wctmw_loader_'+wctmw.unique_id).fadeOut();
}
function wctmw_timeline_init()
{
	const formData = new FormData();
	formData.append('action', 'wctmw_load_order_timelines');	
	formData.append('order_id', wctmw.order_id);	
	formData.append('lang', wctmw.lang);	
	 			
	jQuery.ajax({
			url: wctmw.ajax_url,
			type: 'POST',
			data: formData, 
			async: true,
			success: function (data) 
			{
				//UI	
				//console.log(wctmw.unique_id);
				wcaf_hide_loader();
				jQuery('#wctmw_timeline_container_'+wctmw.unique_id).html(data);
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
}
function wcuf_manage_collapse_action(event)
{
	event.stopPropagation();
	const status = jQuery(event.currentTarget).data('status');
	const id = jQuery(event.currentTarget).data('id');
	if(status == 'show')
	{
		jQuery('.wctmw_collapse_'+id).fadeIn();
		jQuery(event.currentTarget).data('status', 'hide');
	}
	else
	{
		jQuery('.wctmw_collapse_'+id).fadeOut();
		jQuery(event.currentTarget).data('status', 'show');
	}
	
	return false;
}