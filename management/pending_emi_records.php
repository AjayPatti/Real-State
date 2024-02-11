<?php 
	ob_start();
	session_start();
	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/sendMessage.php");
	//$query = mysqli_query($conn,"SELECT customer_id, block_id, block_number_id, emi_amount, paid_amount, emi_date, paid_date FROM kc_customer_emi INNER JOIN kc_blocks ON kc_customer_emi.block_id = kc_blocks.id where kc_customer_emi.emi_amount > kc_customer_emi.paid_amount AND kc_customer_emi.emi_date BETWEEN '".date('Y-m-01')."' AND '".date('Y-m-t')."' and kc_blocks.project_id IN (select id from kc_projects where is_reminder = '1' and status = '1') order by kc_customer_emi.emi_date ");
	$variables_array = array('variable1' => 'Kuldeep Tiwari', 'variable2' => '99.00', 'variable3' => '123', 'variable4' => '14-07-2020');
	sendMessage($conn,19,'9455140710',$variables_array);

	/* while($customer = mysqli_fetch_array($query)){
		$block_number_name = (blockName($conn,$customer['block_id']).'('.blockNumberName($conn,$customer['block_number_id']).')');
		$customer_name = customerName($conn, $customer['customer_id']);
		$amt = ($customer['emi_amount']-$customer['paid_amount']);
		$date = date('d-m-Y',strtotime($customer['emi_date']));
		$variables_array = array('variable1' => $customer_name, 'variable2' => $amt, 'variable3' => $block_number_name, 'variable4' => $date);
		$detail = customerDetail($conn,$customer['customer_id']);
		if(isset($detail['mobile']) && strlen($detail['mobile']) == 10){
			sendMessage($conn,19,$detail['mobile'],$variables_array);
		}
	} */
 die;
?>