<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");

$original_block_id = isset($_POST['original_block'])?(int) $_POST['original_block']:0;
$block_id = isset($_POST['block'])?(int) $_POST['block']:0;
$block_number_id = isset($_POST['block_number'])?(int) $_POST['block_number']:0;

/*$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select area from kc_block_numbers where id = '".$block_number_id."' and block_id = '$block_id' limit 0,1 "));

if(!isset($block_number_details['area'])){
  die;
}*/

//area = '".$block_number_details['area']."' and 

$return_str = '<option value="">Select Plot Number</option>';
$query = "select id, block_number from kc_block_numbers where (id NOT IN (select block_number_id from kc_customer_blocks where status = '1') and block_id = '$block_id')";
if($original_block_id == $block_id){
	$query .= " or id = '$block_number_id'";
}
$query .= " order by CAST(block_number AS unsigned)";

$block_numbers = mysqli_query($conn,$query);
if(mysqli_num_rows($block_numbers) > 0){
  while($block_number = mysqli_fetch_assoc($block_numbers)){
      $return_str .= '<option value="'.$block_number['id'].'"'; 
      if($block_number['id'] == $block_number_id){
      	$return_str .= ' selected="selected"';
      }
      $return_str .= '>'.$block_number['block_number'].'</option>';
  }
}
echo $return_str; die;
?>