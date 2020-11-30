<?php 
foreach($final_messages as $email_message)
{
	if($email_message == "")
		continue;
	
	echo "<div style='display:block; margin-top:10px; margin-bottom:10px; border:1px #dfdfdf solid; padding:10px 10px 10px 20px;'>";
	echo $email_message;
	echo "</div>";
}
?>