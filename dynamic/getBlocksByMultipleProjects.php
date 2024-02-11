<?php
ob_start();
session_start();

$return_str = '<option value="">Select Block</option>';

if(!isset($_POST['projects']) || !is_array($_POST['projects']) || !(sizeof($_POST['projects']) > 0)){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$projects_id = $_POST['projects'];

$query = "select * from kc_blocks where project_id IN ('".implode("','",$projects_id)."') ";

$blocks = mysqli_query($conn,$query);
if(mysqli_num_rows($blocks) > 0){
	while($block = mysqli_fetch_assoc($blocks)){
    	$return_str .= '<option value="'.$block['id'].'">'.$block['name'].'</option>';
	}
}
echo $return_str; die;
?>