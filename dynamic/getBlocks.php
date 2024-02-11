<?php
ob_start();
session_start();

$return_str = '<option value="">Select Block</option>';

if(!isset($_POST['project']) || !is_numeric($_POST['project']) || !($_POST['project'] > 0)){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$project_id = $_POST['project'];

$query = "select * from kc_blocks where project_id = '$project_id' ";
// print_r($query);
$blocks = mysqli_query($conn,$query);
if(mysqli_num_rows($blocks) > 0){
	while($block = mysqli_fetch_assoc($blocks)){
    	$return_str .= '<option value="'.$block['id'].'">'.$block['name'].'</option>';
	}
}
echo $return_str; die;
?>