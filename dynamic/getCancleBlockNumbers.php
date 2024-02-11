<?php
ob_start();
session_start();

$return_str = '<option value="">Select Plot Number</option>';

if(!isset($_POST['block']) || !is_numeric($_POST['block']) || !($_POST['block'] > 0)){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$block_id = $_POST['block'];

$query = "select * from kc_block_numbers where block_id = '$block_id' ";
//print_r($query);die();
if(isset($_POST['type']) && $_POST['type'] == 'booked'){
	$query .= " and id IN (select block_number_id from kc_customer_blocks_hist where status = '1')";
}else if(!isset($_POST['type']) || $_POST['type'] != 'all'){
	$query .= " and id NOT IN (select block_number_id from kc_customer_blocks_hist where status = '1')";
}
$query .= " order by CAST(block_number AS unsigned)";
$block_numbers = mysqli_query($conn,$query);
if(mysqli_num_rows($block_numbers) > 0){
	while($block_number = mysqli_fetch_assoc($block_numbers)){
    	$return_str .= '<option value="'.$block_number['id'].'">'.$block_number['block_number'].'</option>';
	}
}
echo $return_str; die;
?>