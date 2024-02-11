<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/sendMail.php");
require("../includes/sendMessage.php");


/******** Birthday Concept *************/
$last_hundred_years = range(date('Y')-100,date('Y'));
$today_date_month = date('-m-d');
$last_hundred_years_dates_str = "'".implode($today_date_month."','",$last_hundred_years)."'";
$birthday_customers = mysqli_query($conn,"select name_title, name, email, mobile from kc_customers where dob IN ($last_hundred_years_dates_str) ");
while($birthday_customer = mysqli_fetch_assoc($birthday_customers)){
	$name_with_title = $birthday_customer['name_title'].$birthday_customer['name'];
	sendMail($birthday_customer['email'],$name_with_title,'Birthday','','','',"Wishes");
	$variables_array['variable1'] = $name_with_title;
	$variables_array['variable2'] = 'Birthday';
	sendMessage($conn,1,$birthday_customer['mobile'],$variables_array);
}
/******** Birthday Concept *************/


$pendingPayments = outStandingPayments($conn);
if(sizeof($pendingPayments) > 0){
	foreach($pendingPayments as $pendingPayment){
		if( $pendingPayment['next_due_date'] == date("Y-m-d",strtotime("+7 days")) || $pendingPayment['next_due_date'] == date("Y-m-d",strtotime("+3 days"))){
			$customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select name_title, name, email, mobile from kc_customers where id = '".$pendingPayment['customer_id']."' limit 0,1 "));
			$name_with_title = $customer_details['name_title'].$customer_details['name'];
			$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, name from kc_blocks where id = '".$pendingPayment['block_id']."' limit 0,1 "));
			$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, block_number from kc_block_numbers where id = '".$pendingPayment['block_number_id']."' limit 0,1 "));
			//sendMail($customer_details['email'],$name_with_title,'',$block_details['name'],$block_number_details['block_number'],$pendingPayment['next_due_date'],"NextDueDate");
			$variables_array['variable1'] = $name_with_title;
			$variables_array['variable2'] = $pendingPayment['next_due_date'];
			/*$variables_array['variable3'] = $block_details['name'];
			$variables_array['variable4'] = $block_number_details['block_number'];*/
			//sendMessage($conn,2,$customer_details['name_title'],$variables_array);
		}
	}
}


?>