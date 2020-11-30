jQuery(document).ready(function()
{
	jQuery(document).on('click', '#wctmw_add_new_company_button', wctmw_add_new_company);
	jQuery(document).on('click', '.wctmw_delete_button', wctmw_delete_company);
	jQuery(document).on('click', '#wctmw_save', wctmw_save_data);
	jQuery(document).on('keypress', '.wctmw_tracking_code_input', wctmw_on_tracking_code_input_enter);
	
	wctmw_init();
});

function wctmw_on_loading()
{
	jQuery('#wctmw_loading').fadeIn();
	jQuery('.wctmw_button_to_disable').prop('disabled', true);
}
function wcaf_on_save()
{
	wctmw_on_loading();
	//jQuery('#wctmw_new_companies_container').fadeOut();
	jQuery('#wctmw_new_companies_container').append("<div class='wctmw_loading_layer' />");
}
function wcaf_on_save_competed()
{
	wctmw_completed_loading();
	jQuery('#wctmw_new_companies_container').fadeIn();
}
function wctmw_completed_loading()
{
	jQuery('#wctmw_loading').fadeOut();
	jQuery('.wctmw_button_to_disable').prop('disabled', false);
}
function wctmw_on_tracking_code_input_enter(event)
{
	var keycode = (event.keyCode ? event.keyCode : event.which);
    if(keycode == '13')
	{
	   event.preventDefault();
	   event.stopPropagation();
       jQuery('#wctmw_save').trigger('click');
	   return false;
    }
}
function wctmw_init()
{
	//UI
	wctmw_on_loading();
	
	const formData = new FormData();
	formData.append('action', 'wctmw_load_existing_company_widgets');	
	formData.append('order_id', wctmw.order_id);	
	 			
	jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			data: formData, 
			async: true,
			success: function (data) 
			{
				//UI				
				wctmw_completed_loading();
				jQuery('#wctmw_new_companies_container').html(data);
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
function wctmw_save_data(event)
{
	event.preventDefault();
	event.stopPropagation();
	
	//UI
	wcaf_on_save();
	
	const serialized_data = jQuery('#wctmw_data_form *').serialize();
	const formData = new FormData();
	formData.append('action', 'wctmw_save_data');	
	formData.append('order_id', wctmw.order_id);
	formData.append('serialized_data', serialized_data);	
	 			
	jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			data: formData,
			async: true,
			success: function (data) 
			{
				//UI
				jQuery('#wctmw_new_companies_container').html(data);
				wcaf_on_save_competed();
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
function wctmw_add_new_company(event) 
{
	event.preventDefault();
	event.stopPropagation();
		
	//UI
	wctmw_on_loading();
	
	const formData = new FormData();
	formData.append('action', 'wctmw_load_new_company_widget');	
	 			
	jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			data: formData,
			async: true,
			success: function (data) 
			{
				//UI
				jQuery('#wctmw_new_companies_container').append(data);
				wctmw_completed_loading();
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
function wctmw_delete_company(event)
{
	event.preventDefault();
	event.stopPropagation();
	
	const id = jQuery(event.currentTarget).data('id');
	const is_temp = jQuery(event.currentTarget).data('is-temp');
	
	if(is_temp)
	{
		jQuery('#wctmw_company_container_'+id).remove();
	}
	else 
	{
		jQuery('#wctmw_company_container_'+id).hide();
		jQuery('#wctmw_delete_field_'+id).val("yes");
	}
	
	return false;
}