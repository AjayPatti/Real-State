
<?php
ob_start();
session_start();


require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
if(isset($_POST['CancelledTransactionDetails']) && isset($_POST['id']) && isset($_POST['customer']) && isset($_POST['block']) && isset($_POST['block_number'])){
//,remarks
	$details = mysqli_fetch_assoc(mysqli_query($conn,"SELECT amount from kc_customer_transactions where customer_id = '".$_POST['customer']."' and block_id = '".$_POST['block']."' and block_number_id = '".$_POST['block_number']."' and cr_dr = 'cr' and remarks is not null"));

	$data = array();
	$data['amount'] = $details['amount'];
	// $data['remarks'] = $details['remarks']?$details['remarks']:'N/A';
	echo json_encode($data);die;

}