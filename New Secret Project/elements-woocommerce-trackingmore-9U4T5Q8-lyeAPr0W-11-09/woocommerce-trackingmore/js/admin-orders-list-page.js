let wctmw_tracking_queue = new Array();
let wctmw_tracking_queue_index = 0;
jQuery(document).ready(function()
{
	wctmw_load_statuses();
});
function wctmw_load_statuses()
{
	jQuery('.wctmw_tracking_status').each(function(index, elem)
	{
		wctmw_tracking_queue.push(elem);
	});
	
	wctmw_load_next_status(wctmw_tracking_queue[wctmw_tracking_queue_index]);
}
function wctmw_load_next_status(elem)
{
	const formData = new FormData();
	formData.append('action', 'wctmw_ajax_get_order_tracking_status');	
	formData.append('carrier_code', jQuery(elem).data('company'));	
	formData.append('tracking_number', jQuery(elem).data('tracking'))	
	formData.append('order_id', jQuery(elem).data('order-id'))	
				
	jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			data: formData, 
			async: true,
			success: function (data) 
			{
				jQuery(elem).html(data);
				//load next element
				setTimeout(function()
				{
					if(++wctmw_tracking_queue_index < wctmw_tracking_queue.length)
						wctmw_load_next_status(wctmw_tracking_queue[wctmw_tracking_queue_index]);
				}, 200);
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