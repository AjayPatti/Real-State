<?php
ob_start();
session_start();

if(!isset($_POST['block_number']) || !is_numeric($_POST['block_number']) || !($_POST['block_number'] > 0)){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$block_number_id = $_POST['block_number'];

$return_array = array();
$block_numbers_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_block_numbers where id = '$block_number_id' limit 0,1 "));
if(isset($block_numbers_details['id'])){
	$return_array['area'] = $block_numbers_details['area'];
	$plcs = mysqli_query($conn,"select * from kc_block_number_plc where block_number_id = '".$block_number_id."' and status = '1' ");
	while($plc_details = mysqli_fetch_assoc($plcs)){
		$return_array['plc'][] = $plc_details['plc_id'];
	}
	echo json_encode($return_array);
}
die;
?>