let wctmw_csv_data;
let wctmw_chunk_size = 15; //min must be 2
let wctmw_last_row_chunk;
let wctmw_current_row_chunk;
let wctmw_total_data_to_send;
jQuery(document).ready(function()
{
	jQuery(document).on('change', '#csv_file_input', wctmw_on_file_selection);
	jQuery(document).on('click', '#wctmw_import_button', wctmw_start_import_process);
	jQuery(document).on('click', '#wctmw_import_another_button', wctmw_reload_page);
});
function wctmw_reload_page(event)
{
	location.reload();
}
function wctmw_on_file_selection(event)
{
	const files = event.target.files;
	if(!wctmw_browserSupportFileUpload())
	{
		alert(wctmw.not_compliant_browser_error);
		return;
	}
	Papa.parse(files[0], 
	{
		worker: true,
		complete: function(results) 
		{
			/* console.log(results.data);
			console.log(results); */
			if(results.errors.length > 0)
			{
				alert(wctmw.csv_file_format_error);
				return;
			}
			wctmw_total_data_to_send = results.data.length;
			wctmw_csv_data = results.data; //wctmw_csv_data[0] -> titles
			
		}
	});
	return false;
}
//1
function wctmw_start_import_process(event)
{
	if(wctmw_csv_data == null)
	{
		alert(wctmw.file_selection_error);
		return;
	}
	wctmw_setup_csv_data_to_send({first_run: true});
	return false;
}
//2
function wctmw_setup_csv_data_to_send(options)
{
	if(options != null && options.first_run)
	{
		wctmw_last_row_chunk = 1;
		wctmw_current_row_chunk = wctmw_chunk_size < wctmw_total_data_to_send ? wctmw_chunk_size : wctmw_total_data_to_send;
	}	
	
	var dataToSend =  [];
	dataToSend.push(wctmw_csv_data[0]);
	for(var i = wctmw_last_row_chunk;  i < wctmw_current_row_chunk; i++)
	{
		//console.log("Row: "+i);
		dataToSend.push(wctmw_csv_data[i]);
	}
	
	//UI
	wctmw_importing_data_transition_in();
	
	setTimeout(function(){wctmw_upload_csv(dataToSend)}, 1000);;
}
//3
function wctmw_upload_csv(dataToSend)
{
	var formData = new FormData();
	formData.append('action', 'wctmw_csv_import');  
	formData.append('merge_data', jQuery('#merge_data_selector').val());  
	formData.append('csv', JSON.stringify(dataToSend)); 
	var perc_num = ((wctmw_current_row_chunk/wctmw_total_data_to_send)*100);
	perc_num = perc_num > 100 ? 100:perc_num;
	
	var perc = Math.floor(perc_num);
	jQuery('#ajax-progress').html("<p>computing data, please wait...<strong>"+perc+"% done</strong></p>");
	//UI
	wctmw_set_progress_bar_level(perc);
				
	jQuery.ajax({
		url: ajaxurl, //defined in php
		type: 'POST',
		data: formData,//{action: 'upload_csv', csv: data_to_send},
		async: true,
		success: function (data) {
			//alert(data);
			//console.log(data);
			try {
				wctmw_check_response(JSON.parse(data));
			} catch (e) 
			{
				wctmw_import_process_ended();
				wctmw_appent_status_text(data);
			}
		},
		error: function (data) {
			//alert("error: "+data);
			wctmw_check_response(JSON.parse(data));
		},
		cache: false,
		contentType: false,
		processData: false
	});
		
}
//4
function wctmw_check_response(data)
{
	//UI
	wctmw_appent_status_text(data.message);
	
	if(data.error_code == 0 && wctmw_current_row_chunk < wctmw_total_data_to_send)
	{
		wctmw_last_row_chunk = wctmw_current_row_chunk;
		wctmw_current_row_chunk += wctmw_chunk_size;
		if(wctmw_current_row_chunk > wctmw_total_data_to_send)
			wctmw_current_row_chunk = wctmw_total_data_to_send;
		
		setTimeout(wctmw_setup_csv_data_to_send, 1000);
	}
	else
	{
		wctmw_import_process_ended();
	}
}
function wctmw_import_process_ended()
{
	wctmw_set_progress_bar_level(100);
	wctmw_importing_data_transition_out();
}
function wctmw_browserSupportFileUpload() 
{
	var isCompatible = false;
	if (window.File && window.FileReader && window.FileList && window.Blob) {
	isCompatible = true;
	}
	return isCompatible;
}