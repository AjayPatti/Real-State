<?php
date_default_timezone_set('Asia/Kolkata');

$conn = mysqli_connect('localhost','root','');
if(!$conn){
	die('Not Connected');
}

mysqli_select_db($conn,'real_state');



// mysqli_select_db($conn,'wcc_realestate_db');
?>