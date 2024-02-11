<?php
ob_start();
session_start();

// if(!isset($_POST['customer']) || !is_numeric($_POST['customer']) || !($_POST['customer'] > 0)){
//   exit();
// }

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$id = $_POST['id'];
$customer_id = $_POST['cid'];
$remarks = $_POST['remark'];
$date = date("Y-m-d", strtotime($_POST['date']));
//print_r($_POST);die;
$customer_followup_detail = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_customer_follow_ups where id = '".$id."' AND customer_id = '".$customer_id."' limit 0,1 "));
//print_r($customer_followup_detail);die;
$insert_query = "INSERT INTO kc_customer_follow_ups_hist (customer_id, block_id, block_number_id, pending_amount, next_due_date, next_follow_up_date, remarks, created_by, created_at) VALUES ( '".$customer_id."' , '".$customer_followup_detail['block_id']."', '".$customer_followup_detail['block_number_id']."', '".$customer_followup_detail['pending_amount']."', '".$customer_followup_detail['next_due_date']."', '".$date."', '".$remarks."', '".$_SESSION['login_id']."', '".date("Y-m-d H:i:s")."' )";

$update_query = "UPDATE kc_customer_follow_ups SET next_follow_up_date = '".$date."', remarks = '".$remarks."', updated_by = '".$_SESSION['login_id']."' WHERE  id = '".$id."'  ";
//echo $insert_query;die;
	$res1 = mysqli_query($conn , $insert_query);
	$res2 = mysqli_query($conn, $update_query);
	if($res1 == '1' && $res2 == '1')
	{
		echo json_encode("success");
	}
?>






