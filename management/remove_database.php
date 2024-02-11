<?php 
ob_start();
session_start();

require("../includes/kc_connection.php");

if(isset($_POST['password']) && isset($_POST['c_password'])){
	if($_POST['password'] === $_POST['c_password']){
	
		$user = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_login where login_type = 'super_admin' and password = '".$_POST['c_password']."'"));
		if($user != null){
			mysqli_query($conn,"DELETE FROM `kc_login` WHERE login_type != 'super_admin' and id > 1");
			mysqli_query($conn,'DROP TABLE `kc_accounts`, `kc_associates`, `kc_associates_transactions`, `kc_associate_transactions_hist`, `kc_avr_receipt`, `kc_blocks`, `kc_block_numbers`, `kc_block_numbers_13022021`, `kc_block_numbers_hist`, `kc_block_number_associates_hist`, `kc_block_number_employees_hist`, `kc_block_number_plc`, `kc_change_emi`, `kc_cheques`, `kc_contacts`, `kc_customers`, `kc_customer_blocks`, `kc_customer_blocks_hist`, `kc_customer_block_plc`, `kc_customer_block_plc_hist`, `kc_customer_emi`, `kc_customer_emi_hist`, `kc_customer_follow_ups`, `kc_customer_follow_ups_hist`, `kc_customer_transactions`, `kc_customer_transactions_hist`, `kc_employees`, `kc_login_log`, `kc_message_reports`, `kc_plc`, `kc_projects`, `kc_receipt_numbers`, `kc_receipt_numbers_hist`, `kc_refund_amount`, `kc_reminders`, `kc_revised_rate`, `kc_user_privileges`;');
			header("Location:/wcc_real_estate/management/dashboard.php"); 
			exit;
		}
	}
}