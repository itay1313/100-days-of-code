function wctmw_importing_data_transition_in()
{
	 jQuery('#wctmw_instruction').fadeOut(500, function(){
		 jQuery('#wctmw_loader_container').fadeIn();
	 });
}
function wctmw_importing_data_transition_out()
{
 jQuery('#wctmw_notice_box').append("<p>"+wctmw.upload_complete_message+"</p>");
 jQuery('#wctmw_import_another_button').fadeIn();
}
function wctmw_appent_status_text(text)
{
  if(typeof text == 'object')
  {
	  for(i = 0; i<text.length; i++)
		  jQuery('#wctmw_notice_box').append("<p>"+text[i]+"</p>");
  }
  else
	jQuery('#wctmw_notice_box').append("<p>"+text+"</p>");
}
function wctmw_set_progress_bar_level(perc)
{
 jQuery( "#wctmw_progress_bar" ).animate({'width':perc+"%"});
}