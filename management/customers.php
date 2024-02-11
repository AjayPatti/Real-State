<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");
require("../includes/sendMail.php");
require("../includes/sendMessage.php");

 if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_customer'))){ 
 	header("location:/wcc_real_estate/index.php");
 	exit();
 }

$url = 'customers.php?search=Search';

$limit = 50;
if(isset($_GET['page'])){
	$page = $_GET['page'];
}else{
	$page = 1;
}

$page_url = $url.'&page='.$page;

if(isset($_GET['search_customer'])){
	$page_url .= "&search_customer=".$_GET['search_customer'];
}
if(isset($_GET['search_block_no'])){
	$page_url .= "&search_block_no=".$_GET['search_block_no'];
}
if(isset($_GET['search_project'])){
	$page_url .= "&search_project=".$_GET['search_project'];
}
if(isset($_GET['search_employee'])){
	$page_url .= "&search_employee=".$_GET['search_employee'];
}
if(isset($_GET['search_associate'])){
	$page_url .= "&search_associate=".$_GET['search_associate'];
}
if(isset($_GET['search_block'])){
	$page_url .= "&search_block=".$_GET['search_block'];
}

//echo $page_url; die;
if(isset($_POST['save'])){

	$name_title = filter_post($conn,$_POST['name_title']);
	$name = filter_post($conn,$_POST['name']);
	
	$parent_name_relation = isset($_POST['parent_name_title'])?filter_post($conn,$_POST['parent_name_title']):'';
	$parent_name_sub_title = isset($_POST['parent_name_sub_title'])?filter_post($conn,$_POST['parent_name_sub_title']):'';
	$parent_name = filter_post($conn,$_POST['parent_name']);
	$nationality = filter_post($conn,$_POST['nationality']);
	$profession = filter_post($conn,$_POST['profession']);
	$nominee_name = filter_post($conn,$_POST['nominee_name']);
	$nominee_relation = filter_post($conn,$_POST['nominee_relation']);
	
	$residentail_status = filter_post($conn,$_POST['residentail_status']);
	$pan_no = filter_post($conn,$_POST['pan_no']);
	
	$email = filter_post($conn,$_POST['email']);
	$mobile = (float) filter_post($conn,$_POST['mobile']);
	$dob = date("Y-m-d",strtotime(filter_post($conn,$_POST['dob'])));
	$address = addslashes($_POST['address']);
	$office_address = addslashes($_POST['office_address']);
	
	$block = (int) filter_post($conn,$_POST['block']);
	$block_number = (int) filter_post($conn,$_POST['block_number']);

	$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, project_id, name from kc_blocks where id = '".$block."' limit 0,1 "));
	$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, block_number from kc_block_numbers where id = '$block_number' limit 0,1 "));
	
	$rate = (float) filter_post($conn,$_POST['rate']);
	$payable_amount = (float) filter_post($conn,$_POST['payable_amount']);
	
	$payment_type = filter_post($conn,$_POST['payment_type']);
	$bank_name = filter_post($conn,$_POST['bank_name']);
	$cheque_dd_number = filter_post($conn,$_POST['cheque_dd_number']);
	$paid_amount = (float) filter_post($conn,$_POST['paid_amount']);
	$paid_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['paid_date'])));
	$add_transaction_remarks = filter_post($conn,$_POST['transaction_remarks']);
	
	
	$customer_payment_type = filter_post($conn,$_POST['customer_payment_type']);
	
	$next_due_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['next_due_date'])));
	
	$account_id = filter_post($conn,$_POST['account_id']);
	$associate_id = $_POST['associate_id'];
	$send_message = isset($_POST['send_message'])?true:false;

	
	if($customer_payment_type != "EMI"){
		$number_of_installment = 0;
		$installment_amount = 0;
		$emi_payment_date = '1970-01-01';
	}else{
		$number_of_installment = filter_post($conn,$_POST['number_of_installment']);
		$installment_amount = filter_post($conn,$_POST['installment_amount']);
		$emi_payment_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['emi_payment_date'])));
		$next_due_date = date("Y-m-d",strtotime('+1 month',strtotime($emi_payment_date)));
	}
	
	//$registry = filter_post($conn,$_POST['registry']);
	$sales_person_id = (isset($_POST['sales_person']) && $_POST['sales_person'] != '')?filter_post($conn,$_POST['sales_person']):0;
	// $associate_id = (isset($_POST['associate_id']) && $_POST['associate_id'] != '')?filter_post($conn,$_POST['associate_id']):0;
	// $associate_percentage = (isset($_POST['associate_percentage']) && $_POST['associate_percentage'] != '')?filter_post($conn,$_POST['associate_percentage']):0;
	
	
	// print_r($_SESSION['error']);die; 
	
	if($name_title == ''){
		$_SESSION['error'] = 'Name Title was wrong!';
	}else if($name == ''){
		$_SESSION['error'] = 'Name was wrong!';
	}else if ($email != '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
		$_SESSION['error'] = 'Email was wrong!';
	}else if($mobile == '' || strlen($mobile) != 10){
		$_SESSION['error'] = 'Mobile was wrong!';
	}else if($dob == '' || $dob == '1970-01-01'){
		$_SESSION['error'] = 'DOB(Date of Birth) was wrong!';
	}else if($payment_type != '' && $payment_type != 'Cash' && $payment_type != 'DD' && $payment_type != 'Cheque' && $payment_type != 'NEFT' && $payment_type != 'RTGS'){
		$_SESSION['error'] = 'Payment Mode was wrong!';
	}else if(($payment_type == 'DD' || $payment_type == 'Cheque' || $payment_type == 'NEFT' || $payment_type == 'RTGS') && $bank_name == ""){
		$_SESSION['error'] = 'Bank Name was wrong!';
	}else if(($payment_type == 'DD' || $payment_type == 'Cheque' || $payment_type == 'NEFT' || $payment_type == 'RTGS') && $cheque_dd_number == ""){
		$_SESSION['error'] = 'Cheque/DD Number was wrong!';
	}else if(!($rate > 0)){
		$_SESSION['error'] = 'Rate was wrong!';
	}else if($customer_payment_type == "EMI" && (!($number_of_installment > 0) || !is_numeric($number_of_installment))){
		$_SESSION['error'] = 'Number of Installment was wrong!';
	}else if($customer_payment_type == "EMI" && !($installment_amount > 0)){
		$_SESSION['error'] = 'Installment Amount was wrong!';
	}else if($customer_payment_type == "EMI" && $emi_payment_date == "1970-01-01"){
		$_SESSION['error'] = 'Installment Date was wrong!';
	}else if(!($payable_amount > 0)){
		$_SESSION['error'] = 'Total Plot Value was wrong!';
	}else if(empty($associate_id)){
		$_SESSION['error'] = 'Associate was wrong!';
	}else if($account_id ==''){
		$_SESSION['error'] = 'Accounts was wrong!';
	}
	
	/******* comment due to mohit sir ask on 04062020 **********/
	/*else if($paid_amount > 0 && $paid_amount > $payable_amount){
		$_SESSION['error'] = 'Total Plot Value must be greater than Paid Amount!';
	}*/
	/******* comment due to mohit sir ask on 04062020 **********/
	//else if($next_due_date == '' || $next_due_date == '1970-01-01'){
		//$_SESSION['error'] = 'Next Due Date was wrong!';
	/*}else if( !($next_due_date > date("Y-m-d",strtotime("+3 days")))){
		$_SESSION['error'] = 'Next Due Date should be greater than '.date("jS F Y",strtotime("+3 days"));
	}*/else if(!isset($block_details['id'])){
		$_SESSION['error'] = 'Block was wrong!';
	}else if(!isset($block_number_details['id'])){
		$_SESSION['error'] = 'Plot Number was wrong!';
	}/*else if($associate > 0 && (!is_numeric($associate_percentage) || !($associate_percentage > 0))){
		$_SESSION['error'] = 'Associate Percentage was wrong!';
	}*//*else if($paid_amount > 0 && $customer_payment_type == "EMI" && $installment_amount > $paid_amount){
		$_SESSION['error'] = 'Paid Amount was wrong!';
	}*/else{
	
		$already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_customers where name = '".$name."' and dob = '$dob' limit 0,1 "));	// or mobile = '$mobile'
		if(isset($already_exits['id'])){
			$_SESSION['error'] = 'Same Name and DOB Already Exists!';
		}else{
			$error = false;
			mysqli_autocommit($conn,FALSE);
		
			
			if (!mysqli_query($conn,"insert into kc_customers set name_title = '$name_title', name = '$name', parent_name = '$parent_name', parent_name_relation = '$parent_name_relation', parent_name_sub_title = '$parent_name_sub_title', nationality = '$nationality', profession = '$profession', nominee_name = '$nominee_name', nominee_relation = '$nominee_relation', residentail_status = '$residentail_status', pan_no = '$pan_no', office_address = '$office_address', email = '$email', mobile = '$mobile', dob = '$dob', address = '$address', status = '1', addedon ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."' ")){
				$error = true;
				//echo("Error description: " . mysqli_error($conn)); die;
			}else{
				$customer_id = mysqli_insert_id($conn);
			}
			
			if(!$error){
				
				$already_exists = mysqli_fetch_assoc(mysqli_query($conn,"select id, type from kc_contacts where mobile = '$mobile' limit 0,1 "));
				if(!isset($already_exists['id'])){
					$name_with_title = $name_title.' '.$name;
					if (!mysqli_query($conn,"insert into kc_contacts set name = '$name_with_title', mobile = '$mobile', type = 'Customer', customer_id = '$customer_id', status = '1', created ='".date('Y-m-d H:i:s')."', created_by = '".$_SESSION['login_id']."' ")){
						$error = true;
						//echo("Error description: " . mysqli_error($conn)); die;
					}
					
				}else if($already_exists['type'] == "Contact"){
					if (!mysqli_query($conn,"update kc_contacts set type = 'Customer', customer_id = '$customer_id' where id = '".$already_exists['id']."' limit 1")){
						$error = true;
						

						//echo("Error description: " . mysqli_error($conn)); die;
					}
				}
			}
			
			
			//echo $error; die;
			// echo "INSERT INTO kc_customer_follow_ups_hist (customer_id,block_id,block_number_id,pending_amount,next_due_date,next_follow_up_date,created_by) VALUES( '$customer_id' , '$block', '$block_number' , '".$installment_amount."' , '$emi_payment_date' , '$emi_payment_date' ,'".$_SESSION['login_id']."' ) ";
			//die;
			if((!$error && !mysqli_query($conn,"insert into kc_customer_blocks set customer_id = '$customer_id', block_id = '$block', block_number_id = '$block_number', rate_per_sqft = '$rate', final_rate = '$payable_amount', customer_payment_type = '$customer_payment_type', number_of_installment = '$number_of_installment', installment_amount = '$installment_amount', emi_payment_date = '$emi_payment_date', sales_person_id = '$sales_person_id', status = '1', addedon ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."' ")) 
			){
				$error = true;
				//echo("Error description: " . mysqli_error($conn)); die;
			}else{
				$customer_block_id = mysqli_insert_id($conn);
			}

			if((!$error &&  !mysqli_query($conn ,"INSERT INTO kc_customer_follow_ups (customer_id,block_id,block_number_id,pending_amount,next_due_date,next_follow_up_date,created_by) VALUES( '$customer_id' , '$block', '$block_number' , '".$installment_amount."' , '$emi_payment_date' , '$emi_payment_date' ,'".$_SESSION['login_id']."' ) "))
				){
				$error = true;
			}else{
				$customer_block_id = mysqli_insert_id($conn);
			}

			if((!$error &&  !mysqli_query($conn ,"INSERT INTO kc_customer_follow_ups_hist (customer_id,block_id,block_number_id,pending_amount,next_due_date,next_follow_up_date,created_by) VALUES( '$customer_id' , '$block', '$block_number' , '".$installment_amount."' , '$emi_payment_date' , '$emi_payment_date' ,'".$_SESSION['login_id']."' ) "))
				){
				$error = true;
			}else{
				$customer_block_id = mysqli_insert_id($conn);
			}
			
			if(!$error && isset($_POST['plc']) && is_array($_POST['plc']) && sizeof($_POST['plc']) > 0){
				foreach($_POST['plc'] as $plc_id){
					$plc_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, name, plc_percentage from kc_plc where id = '$plc_id' limit 0,1 "));
					
					if(isset($plc_details['id'])){
						if(!mysqli_query($conn,"insert into kc_customer_block_plc set customer_block_id = '$customer_block_id', plc_id = '".$plc_details['id']."', name = '".$plc_details['name']."', plc_percentage = '".$plc_details['plc_percentage']."', status = '1', addedon ='".date('Y-m-d H:i:s')."' ")){
							$error = true;
							//echo("Error description: " . mysqli_error($conn)); die;
						}
					}
				}
			} 

			$data=[];
			if(!$error && is_array($associate_id) && sizeof($associate_id) > 0){
				for($i= 0;$i < count($associate_id);$i++){

					$data['associate_id'] =$associate_id[$i];
					$data['associate_percentage'] =$_POST['associate_percentage'][$i];
				
					if(!mysqli_query($conn,"INSERT INTO `kc_associate_percentage`(`customer_block_id`, `block_id`, `block_number_id`, `customer_id`, `associate`, `associate_percentage`, `status`,`created_at`,  `created_by`) VALUES ('$customer_block_id','$block','$block_number','$customer_id','".$data['associate_id']."','".$data['associate_percentage']."','1','".date('Y-m-d H:i:s')."','".$_SESSION['login_id']."' )")){
						$error = true;	
					}else{
						$associate_percentage_id = mysqli_insert_id($conn);
					}
				}
			}

			$paid = false;
			if(!$error && !mysqli_query($conn,"insert into kc_customer_transactions set customer_id = '$customer_id', block_id = '$block', block_number_id = '$block_number', amount = '$payable_amount', cr_dr = 'cr', paid_date = '".date("Y-m-d")."', next_due_date = '$next_due_date', status = '1', account_id='$account_id', addedon ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."' ")){
				$error = true;
				//echo("Error description: " . mysqli_error($conn)); die;
			}else{
				// $paid_transaction_id = mysqli_insert_id($conn);
				
				// 	receiptNumber($conn,$paid_transaction_id);

				// 	if(!makeAssociateCredit($conn,$paid_transaction_id)){
				// 		$error = true;
				// 	}
				// 	$paid = true;
			}
		
			$paid = false;
			if(!$error && $paid_amount > 0 && $paid_date != '' && $paid_date != '1970-01-01'){
				if(!$error && !mysqli_query($conn,"insert into kc_customer_transactions set customer_id = '$customer_id', block_id = '$block', block_number_id = '$block_number', payment_type = '$payment_type', bank_name = '$bank_name', cheque_dd_number = '$cheque_dd_number', amount = '$paid_amount', cr_dr = 'dr', paid_date = '$paid_date', add_transaction_remarks = '$add_transaction_remarks',account_id='$account_id', next_due_date = '$next_due_date', status = '1', addedon ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."' ")){
					$error = true;
					//echo("Error description: " . mysqli_error($conn)); die;
				}else{
					$paid_transaction_id = mysqli_insert_id($conn);

					receiptNumber($conn,$paid_transaction_id);

					if(!makeAssociateCredit($conn,$paid_transaction_id)){
						$error = true;
					}
					$paid = true;
				}
			}

			if(!$error && $customer_payment_type == "EMI"){
				for($i = 0; $i < $number_of_installment; $i++){
					if($i==0){
						 $emi_payment_date = date("Y-m-d",strtotime($emi_payment_date));
					}else{
						 $emi_payment_date = date("Y-m-d",strtotime('+1 month',strtotime($emi_payment_date)));
					}
					if(!mysqli_query($conn,"insert into kc_customer_emi set customer_id = '$customer_id', block_id = '$block', block_number_id = '$block_number', emi_amount = '$installment_amount', emi_date = '$emi_payment_date', created = '".date('Y-m-d H:i:s')."' ")){
						$error = true;
						//echo("Error description: " . mysqli_error($conn)); die;
					}
				}
				/*if(!makeEMIPaid($conn,$customer_id,$block,$block_number)){
					$error = true;
				}*/
				/*$total_emi_pay = totalEmiPaid($installment_amount,$paid_amount);
				$pay_amount = ($paid_amount/$total_emi_pay);
				for($i = 0; $i < $total_emi_pay; $i++){
					mysqli_query($conn,"update kc_customer_emi set paid_amount = '$pay_amount', paid_date = '$paid_date' where paid_amount = '0' and paid_date IS NULL and customer_id = '$customer_id' and block_id = '$block' and block_number_id = '$block_number' and emi_amount = '$installment_amount'  limit 1 ");
				}*/
			}
		
			
			// echo "<pre>";
			// print_r($_POST['associate_id']);die;
			

			if(!$error){
				
				mysqli_commit($conn);
				//echo "success"; die;
				$_SESSION['success'] = 'Customer Successfully Added! and Transaction Successfully Added!';
				
				$name_with_title = $name_title.' '.$name;
				if($send_message){
					$variables_array = array('variable1' => $name_with_title,'variable2'=>$block_number_details['block_number'],'variable3'=>$block_details['name'],'variable4'=>blockProjectName($conn,$block_details['id']));
					//    print_r($conn);die;
					if(sendMessage($conn,7,$mobile,$variables_array)){
						$_SESSION['success'] .= ' and Welcome Message sent Successfully!';
					}else if(!isset($_SESSION['error'])){
						$_SESSION['error'] = 'Welcome Message not sent!';
					}else if(isset($_SESSION['error'])){
						$_SESSION['error'] .= ' and Welcome Message not sent!';
					}
				}
				// die;
				if($paid){
					if(sendMail($email,$name_with_title,$paid_amount,$block_details['name'],$block_number_details['block_number'],$paid_date,"PaymentReceived")){
						$_SESSION['success'] .= ' and Email Sent Successfully!';
					}else{
						$_SESSION['error'] = 'Email not sent!';
					}
					
					
					if($send_message){
						$variables_array = array('variable1' => $name_with_title,'variable2'=>$paid_amount,'variable3'=>$block_details['name'],'variable4'=>$block_number_details['block_number'],'variable5'=>$paid_date);
						if(sendMessage($conn,8,$mobile,$variables_array)){
							$_SESSION['success'] .= ' and Transaction Message sent Successfully!';
						}else if(!isset($_SESSION['error'])){
							$_SESSION['error'] = 'Transaction Message not sent!';
						}else if(isset($_SESSION['error'])){
							$_SESSION['error'] .= ' and Transaction Message not sent!';
						}
					}
				}
				header("Location:".$page_url);
				exit();
			}else{
				mysqli_rollback($conn);
				$_SESSION['error'] = 'Some Problem Occured during in storing data!';
			}	
		}
	}				
}


if(isset($_POST['ab_save'])){
	//echo "<pre>"; print_r($_POST); die;
	$customer_id = (int) filter_post($conn,$_POST['ab_customer']);
	
	$block = (int) filter_post($conn,$_POST['ab_block']);
	$block_number = (int) filter_post($conn,$_POST['ab_block_number']);
	
	$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, project_id, name from kc_blocks where id = '".$block."' limit 0,1 "));
	$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, block_number from kc_block_numbers where id = '$block_number' limit 0,1 "));
	
	$rate = (float) filter_post($conn,$_POST['ab_rate']);
	$payable_amount = (float) filter_post($conn,$_POST['ab_payable_amount']);
	
	$payment_type = filter_post($conn,$_POST['ab_payment_type']);
	$bank_name = filter_post($conn,$_POST['ab_bank_name']);
	$cheque_dd_number =  filter_post($conn,$_POST['ab_cheque_dd_number']);
	$paid_amount = (float) filter_post($conn,$_POST['ab_paid_amount']);
	$paid_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['ab_paid_date'])));
	$add_transaction_remarks = filter_post($conn,$_POST['ab_transaction_remarks']);
	$next_due_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['ab_next_due_date'])));

	$customer_payment_type = filter_post($conn,$_POST['ab_customer_payment_type']);
	$send_message = isset($_POST['ab_send_message'])?true:false;

	if($customer_payment_type != "EMI"){
		$number_of_installment = 0;
		$installment_amount = 0;
		$emi_payment_date = '1970-01-01';
	}else{
		$number_of_installment = filter_post($conn,$_POST['ab_number_of_installment']);
		$installment_amount = filter_post($conn,$_POST['ab_installment_amount']);
		$emi_payment_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['ab_emi_payment_date'])));
		$next_due_date = date("Y-m-d",strtotime('+1 month',strtotime($emi_payment_date)));
	}


	$sales_person_id = (isset($_POST['ab_sales_person']) && $_POST['ab_sales_person'] != '')?filter_post($conn,$_POST['ab_sales_person']):0;
	$associate = (isset($_POST['associate']) && $_POST['associate'] != '')?filter_post($conn,$_POST['associate']):0;
	$associate_percentage = (isset($_POST['associate_percentage']) && $_POST['associate_percentage'] != '')?filter_post($conn,$_POST['associate_percentage']):0;
	
	$already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_customers where id = '$customer_id' limit 0,1 "));
	// print_r($already_exits['mobile']);die;
	if(!isset($already_exits['id'])){
		$_SESSION['error'] = 'Something Really Wrong!';
	}else if($payment_type != '' && $payment_type != 'Cash' && $payment_type != 'DD' && $payment_type != 'Cheque' && $payment_type != 'NEFT' && $payment_type != 'RTGS'){
		$_SESSION['error'] = 'Payment Mode was wrong!';
	}else if(($payment_type == 'DD' || $payment_type == 'Cheque' || $payment_type == 'NEFT' || $payment_type == 'RTGS') && $bank_name == ""){
		$_SESSION['error'] = 'Bank Name was wrong!';
	}else if(($payment_type == 'DD' || $payment_type == 'Cheque' || $payment_type == 'NEFT' || $payment_type == 'RTGS') && $cheque_dd_number == ""){
		$_SESSION['error'] = 'Cheque/DD Number was wrong!';
	}
	
	else if(!($rate > 0)){
		$_SESSION['error'] = 'Rate was wrong!';
	}else if($customer_payment_type == "EMI" && (!($number_of_installment > 0) || !is_numeric($number_of_installment))){
		$_SESSION['error'] = 'Number of Installment was wrong!';
	}else if($customer_payment_type == "EMI" && !($installment_amount > 0)){
		$_SESSION['error'] = 'Installment Amount was wrong!';
	}else if($customer_payment_type == "EMI" && $emi_payment_date == "1970-01-01"){
		$_SESSION['error'] = 'Installment Amount was wrong!';
	}else if(!($payable_amount > 0)){
		$_SESSION['error'] = 'Total Plot Value was wrong!';
	}
	/******* comment due to mohit sir ask on 04062020 **********/
	/*
	else if($paid_amount > 0 && $paid_amount > $payable_amount){
		$_SESSION['error'] = 'Total Plot Value must be greater than Paid Amount!';
	}*/
	/******* comment due to mohit sir ask on 04062020 **********/
	else if($next_due_date == '' || $next_due_date == '1970-01-01'){
		$_SESSION['error'] = 'Next Due Date was wrong!';
	}/*else if( !($next_due_date > date("Y-m-d",strtotime("+3 days")))){
		$_SESSION['error'] = 'Next Due Date should be greater than '.date("jS F Y",strtotime("+3 days"));
	}*/else if(!isset($block_details['id'])){
		$_SESSION['error'] = 'Block was wrong!';
	}else if(!isset($block_number_details['id'])){
		$_SESSION['error'] = 'Plot Number was wrong!';
	}/*else if($associate > 0 && (!is_numeric($associate_percentage) || !($associate_percentage > 0))){
		$_SESSION['error'] = 'Associate Percentage was wrong!';
	}*/else{
		
		$error = false;
		mysqli_autocommit($conn,FALSE);

		if (!mysqli_query($conn,"insert into kc_customer_blocks set customer_id = '$customer_id', block_id = '$block', block_number_id = '$block_number', rate_per_sqft = '$rate', final_rate = '$payable_amount', customer_payment_type = '$customer_payment_type', number_of_installment = '$number_of_installment', installment_amount = '$installment_amount', emi_payment_date = '$emi_payment_date', sales_person_id = '$sales_person_id', associate = '$associate', associate_percentage = '$associate_percentage', status = '1', addedon ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."' ")){
			$error = true;
			//echo("Error description: " . mysqli_error($conn)); die;
		}
		$customer_block_id = mysqli_insert_id($conn);
		
		
		
		if(isset($_POST['ab_plc']) && is_array($_POST['ab_plc']) && sizeof($_POST['ab_plc']) > 0){
			foreach($_POST['ab_plc'] as $plc_id){
				$plc_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, name, plc_percentage from kc_plc where id = '$plc_id' limit 0,1 "));
				
				if(isset($plc_details['id'])){
					if(!mysqli_query($conn,"insert into kc_customer_block_plc set customer_block_id = '$customer_block_id', plc_id = '".$plc_details['id']."', name = '".$plc_details['name']."', 	plc_percentage = '".$plc_details['plc_percentage']."', status = '1', addedon ='".date('Y-m-d H:i:s')."' ")){
						$error = true;
						//echo("Error description: " . mysqli_error($conn));
					}
				}
			}
		}
		
		if(!mysqli_query($conn,"insert into kc_customer_transactions set customer_id = '$customer_id', block_id = '$block', block_number_id = '$block_number', amount = '$payable_amount', cr_dr = 'cr', next_due_date = '$next_due_date', status = '1', addedon ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."' ")){
			$error = true;
			//echo("Error description: " . mysqli_error($conn)); die;
		}
	//	echo $error;
		if($paid_amount > 0 && $paid_date != '' && $paid_date != '1970-01-01'){
			if(!mysqli_query($conn,"insert into kc_customer_transactions set customer_id = '$customer_id', block_id = '$block', block_number_id = '$block_number', payment_type = '$payment_type', bank_name = '$bank_name', cheque_dd_number = '$cheque_dd_number', amount = '$paid_amount', cr_dr = 'dr', paid_date = '$paid_date', add_transaction_remarks = '$add_transaction_remarks', next_due_date = '$next_due_date', status = '1', addedon ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."' ")){
				$error = true;
				//echo("Error description: " . mysqli_error($conn));
			}else{
				$paid_transaction_id = mysqli_insert_id($conn);

				receiptNumber($conn,$paid_transaction_id);

				if(!makeAssociateCredit($conn,$paid_transaction_id)){
					$error = true;
				}
			}
		}
		//echo $error; die;
		if (!mysqli_query($conn ,"INSERT INTO kc_customer_follow_ups (customer_id,block_id,block_number_id,pending_amount,next_due_date,next_follow_up_date,created_by) VALUES( '$customer_id' , '$block', '$block_number' , '".$installment_amount."' , '$emi_payment_date' , '$emi_payment_date' ,'".$_SESSION['login_id']."' ) ") && !mysqli_query($conn ,"INSERT INTO kc_customer_follow_ups_hist (customer_id,block_id,block_number_id,pending_amount,next_due_date,next_follow_up_date,created_by) VALUES( '$customer_id' , '$block', '$block_number' , '".$installment_amount."' , '$emi_payment_date' , '$emi_payment_date' ,'".$_SESSION['login_id']."' ) ")){
			$error = true;
			//echo("Error description: " . mysqli_error($conn)); die;
		}
		if(!$error && $customer_payment_type == "EMI"){
			for($i = 0; $i < $number_of_installment; $i++){
				if($i==0){
					 $emi_payment_date = date("Y-m-d",strtotime($emi_payment_date));
				}else{
					 $emi_payment_date = date("Y-m-d",strtotime('+1 month',strtotime($emi_payment_date)));
				}
				if(!mysqli_query($conn,"insert into kc_customer_emi set customer_id = '$customer_id', block_id = '$block', block_number_id = '$block_number', emi_amount = '$installment_amount', emi_date = '$emi_payment_date', created = '".date('Y-m-d H:i:s')."' ")){
					$error = true;
					//echo("Error description: " . mysqli_error($conn)); die;
				}
			}
			if(!makeEMIPaid($conn,$customer_id,$block,$block_number)){
				$error = true;
			}
		}

		//var_dump((!$error && $associate > 0 && $associate_percentage > 0)); die;
		//echo $error; die;
		if($error){
			mysqli_rollback($conn);
			$_SESSION['error'] .= ' Something Wrong!';
		}else{
			mysqli_commit($conn);

			$_SESSION['success'] = 'Transaction Successfully Added!';

			$name_with_title = $already_exits['name_title'].' '.$already_exits['name'];
            $mobile = $already_exits['mobile'];
			// print_r($mobile);die;
			if($send_message){
				$variables_array = array('variable1' => $name_with_title,'variable2'=>$block_number_details['block_number'],'variable3'=>$block_details['name'],'variable4'=>blockProjectName($conn,$block_details['project_id']));
				if(sendMessage($conn,7,$mobile,$variables_array)){
					$_SESSION['success'] .= ' and Welcome Message sent Successfully!';
				}else {
					$_SESSION['error'] = 'Welcome Message not sent!';
				}
			}

			if($paid_amount > 0 && $paid_date != '' && $paid_date != '1970-01-01'){	
				if($email!="" && sendMail($email,$name_with_title,$paid_amount,$block_details['name'],$block_number_details['block_number'],$paid_date,"PaymentReceived")){
					$_SESSION['success'] .= ' and Email Sent Successfully!';
				}else if(!isset($_SESSION['error'])){
					$_SESSION['error'] = ' Email not sent!';
				}else if(isset($_SESSION['error'])){
					$_SESSION['error'] .= ' and Email not sent!';
				}
				
				if($send_message){
					$variables_array = array('variable1' => $name_with_title,'variable2'=>$paid_amount,'variable3'=>$block_number_details['block_number'],'variable4'=>$block_details['name'],'variable5'=>$paid_date);
					if(sendMessage($conn,8,$already_exits['mobile'],$variables_array)){
						$_SESSION['success'] .= ' and Transaction Message sent Successfully!';
					}else if(!isset($_SESSION['error'])){
						$_SESSION['error'] = ' Transaction Message not sent!';
					}else if(isset($_SESSION['error'])){
						$_SESSION['error'] .= ' and Transaction Message not sent!';
					}
				}
			}
		}

		// Close connection
		mysqli_close($conn);
		header("Location:".$page_url);
		exit();	
	}
					
}

if(isset($_POST['addTransaction'])){
	
	$customer_id = (int) filter_post($conn,$_POST['customer_id']);
	$block_id = (int) filter_post($conn,$_POST['block_id']);
	$block_number_id = (int) filter_post($conn,$_POST['block_number_id']);
	
	$payment_type = filter_post($conn,$_POST['payment_type']);
	$bank_name = filter_post($conn,$_POST['bank_name']);
	$cheque_dd_number = filter_post($conn,$_POST['cheque_dd_number']);
	//echo"<pre>"; print_r($cheque_dd_number); die;
	$paid_amount = (float) filter_post($conn,$_POST['paid_amount']);
	$paid_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['paid_date'])));
	$add_transaction_remarks = filter_post($conn,$_POST['transaction_remarks']);
	$account_id = filter_post($conn,$_POST['account_id']);
	// var_dump(isLastEmiPayment($conn,$customer_id,$block_id,$block_number_id));die;
	$next_due_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['next_due_date'])));
	
	// 	$next_due_date = date('Y-m-d');
	// 	echo $next_due_date = date('Y-m-d');
	// }else{
	// 	$next_due_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['next_due_date'])));
	// }
	 // print_r($part_amc);die;
	 // echo $pending_amc['installment_amount'];die;
	 // if($pending_amc['installment_amount'] == 0){
		// 			$sql ="UPDATE kc_customer_follow_ups SET status = '1' WHERE customer_id = '$customer_id' AND block_id = '$block_id' AND block_number_id = '$block_number_id' ";
		// 			echo $sql;
		// 			}
		// 			else{
		// 		echo "string";}die;

	//echo $check  = "INSERT INTO kc_customer_follow_ups_hist (customer_id,block_id,block_number_id,pending_amount,next_due_date,next_follow_up_date,created_by) VALUES( '$customer_id' , '$block_id', '$block_number_id' , '".$pending_amc['installment_amount']."' , '$next_due_date' , '$next_due_date' ,'".$_SESSION['login_id']."')"; die;

	$send_message = isset($_POST['at_send_message'])?true:false;

	$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, name from kc_blocks where id = '".$block_id."' limit 0,1 "));
	$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, block_number from kc_block_numbers where id = '".$block_number_id."' limit 0,1 "));
	//echo $check; die;
	if(!($customer_id > 0)){
		$_SESSION['error'] = 'Something was Really wrong!';
	}else if(!isset($block_details['id'])){
		$_SESSION['error'] = 'Opps Something was Really wrong!';
	}else if(!isset($block_number_details['id'])){
		$_SESSION['error'] = 'Oppps! Something was Really wrong!';
	}else if($payment_type != 'Cash' && $payment_type != 'DD' && $payment_type != 'Cheque' && $payment_type != 'NEFT' && $payment_type != 'RTGS'){
		$_SESSION['error'] = 'Payment Mode was wrong!';
	}else if(($payment_type == 'DD' || $payment_type == 'Cheque' || $payment_type == 'NEFT' || $payment_type == 'RTGS') && $bank_name == ""){
		$_SESSION['error'] = 'Bank Name was wrong!';
	}else if(($payment_type == 'DD' || $payment_type == 'Cheque' || $payment_type == 'NEFT' || $payment_type == 'RTGS') && $cheque_dd_number == ""){
		$_SESSION['error'] = 'Cheque/DD Number was wrong!';
	}else if(!($paid_amount > 0)){
		$_SESSION['error'] = 'Paid Amount was wrong!';
	}else if($paid_date == '' || $paid_date == '1970-01-01'){
		$_SESSION['error'] = 'Paid Date was wrong!';
	}else if($next_due_date == '' || ($next_due_date == '1970-01-01' && !isLastEmiPayment($conn,$customer_id,$block_id,$block_number_id))){
		$_SESSION['error'] = 'Next Due Date was wrong!';
	}/*else if( !($next_due_date > date("Y-m-d",strtotime("+3 days")))){
		$_SESSION['error'] = 'Next Due Date should be greater than '.date("jS F Y",strtotime("+3 days"));
	}*/else{
		$total_loan = mysqli_fetch_assoc(mysqli_query($conn,"select sum(amount) as total_loan from kc_customer_transactions where customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id' and cr_dr = 'cr' limit 0,1 "));

		$total_paid = mysqli_fetch_assoc(mysqli_query($conn,"select sum(amount) as total_paid from kc_customer_transactions where customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id' and cr_dr = 'dr' limit 0,1 "));
		

		/******* added false due to mohit sir ask on 04062020 **********/
		if(false && ($total_paid['total_paid']+$paid_amount) > $total_loan['total_loan']){
			$_SESSION['error'] = 'Paid Amount was Greater than total Credited Amount!';
		}else{

			$error = false;
			mysqli_autocommit($conn,FALSE);

			/*$customer_block_emi = mysqli_fetch_assoc(mysqli_query($conn, "select id, installment_amount, emi_payment_date from kc_customer_blocks where customer_id = '".$customer_id."' and block_id = '".$block_id."' and block_number_id = '".$block_number_id."' limit 0,1 "));

			if($customer_block_emi['installment_amount']>0 && $customer_block_emi['emi_payment_date']!=NULL){
				mysqli_query($conn, "update kc_customer_emi set paid_amount = '$paid_amount', paid_date = '$paid_date' where paid_amount = '0' and paid_date IS NULL limit 1 ");
			}*/
			
			if(!mysqli_query($conn,"insert into kc_customer_transactions set customer_id = '$customer_id', block_id = '$block_id', block_number_id = '$block_number_id', payment_type = '$payment_type', bank_name = '$bank_name', cheque_dd_number = '$cheque_dd_number', amount = '$paid_amount', cr_dr = 'dr', paid_date = '$paid_date',account_id='$account_id', add_transaction_remarks = '$add_transaction_remarks', next_due_date = '$next_due_date', status = '1', addedon ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."' ")){
				$error = true;
			}else{
				$paid_transaction_id = mysqli_insert_id($conn);
				receiptNumber($conn,$paid_transaction_id);
				//print_r($paid_transaction_id);die;
				
				if(!makeAssociateCredit($conn,$paid_transaction_id)){
					$error = true;
				}
			}

			if(!mysqli_query($conn,"UPDATE kc_customer_follow_ups SET next_due_date = '$next_due_date', next_follow_up_date = '$next_due_date' , updated_by =  '".$_SESSION['login_id']."' , updated_at = '".date('Y-m-d H:i:s')."' WHERE customer_id = '$customer_id' AND block_id = '$block_id' AND block_number_id = '$block_number_id' ")){
				$error = true;			
				} 
				$part_amc = getPartAmount($conn , $customer_id , $block_id , $block_number_id );
			if(!mysqli_query($conn,"INSERT INTO kc_customer_follow_ups_hist (customer_id,block_id,block_number_id,pending_amount,next_due_date,next_follow_up_date,created_by) VALUES( '$customer_id' , '$block_id', '$block_number_id' , '".$part_amc."' , '$next_due_date' , '$next_due_date' ,'".$_SESSION['login_id']."')")){
				$error = true;			
				} 

				if($part_amc == 0){
				if(!mysqli_query($conn,"UPDATE kc_customer_follow_ups SET status = '1' WHERE customer_id = '$customer_id' AND block_id = '$block_id' AND block_number_id = '$block_number_id' ")){
					$error = true;	
					}
				}
				// if($pending_amc['installment_amount'] == 0){
				// 	if(!mysqli_query($conn,"UPDATE kc_customer_follow_ups SET status = '1' WHERE customer_id = '$customer_id' AND block_id = '$block_id' AND block_number_id = '$block_number_id' "){
				// 		$error = true;
				// 	}
				// }

			if(isEmiTaken($conn,$customer_id,$block_id,$block_number_id)){
				if(!makeEMIPaid($conn,$customer_id,$block_id,$block_number_id)){
					$error = true;
				}
			}
 
			if($error){
				mysqli_rollback($conn);
				$_SESSION['error'] = 'Something Wrong!';
			}else{
				mysqli_commit($conn);
				$_SESSION['success'] = 'Transaction Successfully Added!';

				$customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select name_title, name, email, mobile from kc_customers where id = '".$customer_id."' limit 0,1 "));

				$name_with_title = $customer_details['name_title'].' '.$customer_details['name'];
				

				if(sendMail($customer_details['email'],$name_with_title,$paid_amount,$block_details['name'],$block_number_details['block_number'],$paid_date,"PaymentReceived")){
					$_SESSION['success'] .= ' and Email Sent Successfully!';
				}else{
					$_SESSION['error'] = 'Email not sent!';
				}

				if($send_message){
					
					$variables_array = array('variable1'=>$name_with_title,'variable2'=>$paid_amount,'variable3'=>$block_number_details['block_number'],'variable4'=>$block_details['name'],'variable5'=>$paid_date);
					// print_r($variables_array);die;
					if(sendMessage($conn,9,$customer_details['mobile'],$variables_array)){
						$_SESSION['success'] .= ' and Message sent Successfully!';
					}else if(!isset($_SESSION['error'])){
						$_SESSION['error'] = ' Message not sent!';
					}else if(isset($_SESSION['error'])){
						$_SESSION['error'] .= ' and Message not sent!';
					}
				}
				header("Location:".$page_url);
				exit();
			}				
					
		}
	}
}

if(isset($_POST['editInformation'])){
	
	$customer_id = filter_post($conn,$_POST['customer']);
	// echo "<pre>";print_r($customer_id);die;
	$name_title = filter_post($conn,$_POST['name_title']);
	$name = filter_post($conn,$_POST['name']);
	
	$parent_name_relation = filter_post($conn,$_POST['parent_name_title']);
	$parent_name_sub_title = filter_post($conn,$_POST['parent_name_sub_title']);
	$parent_name = filter_post($conn,$_POST['parent_name']);
	$nationality = filter_post($conn,$_POST['nationality']);
	$profession = filter_post($conn,$_POST['profession']);
	$nominee_name = filter_post($conn,$_POST['nominee_name']);
	
	$residentail_status = filter_post($conn,$_POST['residentail_status']);
	$pan_no = filter_post($conn,$_POST['pan_no']);
	
	$email = filter_post($conn,$_POST['email']);
	$mobile = (float) filter_post($conn,$_POST['mobile']);
	$dob = date("Y-m-d",strtotime(filter_post($conn,$_POST['dob'])));
	$address = addslashes($_POST['address']);
	$office_address = addslashes($_POST['office_address']);
	
	if(!($customer_id > 0) || !is_numeric($customer_id)){
		$_SESSION['error'] = 'Something was Really wrong!';
	}else if($name_title == ''){
		$_SESSION['error'] = 'Name Title was wrong!';
	}else if($name == ''){
		$_SESSION['error'] = 'Name was wrong!';
	}else if ($email != '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
	  $_SESSION['error'] = 'Email was wrong!';
	}else if($mobile == '' || strlen($mobile) != 10){
		$_SESSION['error'] = 'Mobile was wrong!';
	}else if($dob == '' || $dob == '1970-01-01'){
		$_SESSION['error'] = 'DOB(Date of Birth) was wrong!';
	}else{
		$already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_customers where id != '$customer_id' and mobile = '$mobile' limit 0,1 "));
		if(isset($already_exits['id'])){
			$_SESSION['error'] = 'Mobile Already Exists!';
		}else{
			mysqli_query($conn,"update kc_customers set name_title = '$name_title', name = '$name', parent_name = '$parent_name', parent_name_relation = '$parent_name_relation', parent_name_sub_title = '$parent_name_sub_title', nationality = '$nationality', profession = '$profession', nominee_name = '$nominee_name', residentail_status = '$residentail_status', pan_no = '$pan_no', office_address = '$office_address', email = '$email', mobile = '$mobile', dob = '$dob', address = '$address' where id = '$customer_id' ");

			$name_with_title = $name_title.' '.$name;
			mysqli_query($conn,"update kc_contacts set name = '$name_with_title', mobile = '$mobile', created_by = '".$_SESSION['login_id']."' where customer_id = '$customer_id' limit 1 ");

			$_SESSION['success'] = 'Information Successfully Updated!';
			header("Location:".$page_url);
			exit();
		}
		
	}
						
}



if(isset($_POST['changeEmployee'])){
	
	//echo "<pre>"; print_r($_POST); die;
	$customer_id = isset($_POST['customer_id'])?(int) $_POST['customer_id']:0;
	$block_id = isset($_POST['block_id'])?(int) $_POST['block_id']:0;
	$block_number_id = isset($_POST['block_number_id'])?(int) $_POST['block_number_id']:0;
	$sales_person_id = isset($_POST['sales_person'])?(int) $_POST['sales_person']:0;

	$customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select sales_person_id from kc_customer_blocks where customer_id = '".$customer_id."' and block_id = '$block_id' and block_number_id = '$block_number_id' limit 0,1 "));

	if(!($sales_person_id > 0)){
		$_SESSION['error'] = 'Please Select Employee!';
	}else if(!isset($customer_details['sales_person_id'])){
		$_SESSION['error'] = 'Block Details not Found!';
	}else{
		$error = false;
		mysqli_autocommit($conn,FALSE);

		if (!mysqli_query($conn,"insert into kc_block_number_employees_hist set customer_id = '$customer_id', block_id = '$block_id', block_number_id = '$block_number_id', sales_person_id = '".$customer_details['sales_person_id']."', added_by = '".$_SESSION['login_id']."' ")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}
		if(!$error && !mysqli_query($conn,"update kc_customer_blocks set sales_person_id = '$sales_person_id' where customer_id = '".$customer_id."' and block_id = '$block_id' and block_number_id = '$block_number_id'")){	//, associate = '$associate', associate_percentage = '$associate_percentage' removed by satyam at 08112019 due to don't understand this is required here
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}
		if($error){
			mysqli_rollback($conn);
			$_SESSION['error'] = 'Something went wrong!';
		}else{
			mysqli_commit($conn);
			$_SESSION['success'] = 'Employee has been Successfully Changed.';
		}
		mysqli_close($conn);
	}
	header("Location:".$page_url);
	exit();
}

if(isset($_POST['changeAssociate'])){
	
	// echo "<pre>"; print_r($_POST); die;
	$customer_id = isset($_POST['customer_id'])?(int) $_POST['customer_id']:0;
	$block_id = isset($_POST['block_id'])?(int) $_POST['block_id']:0;
	$block_number_id = isset($_POST['block_number_id'])?(int) $_POST['block_number_id']:0;
	$associate = $_POST['associate_id'];
	$associate_percentage = $_POST['associate_percentage'];
	
	if(!($associate > 0)){
		$_SESSION['error'] = 'Please Select Associate!';
	}else if(!isset($associate)){
		$_SESSION['error'] = 'Block Details not Found!';
	}else{
		$error = false;
		mysqli_autocommit($conn,FALSE);
		$data=[];
		for($i=0;$i<count($associate);$i++){
			$data['associate_id']=$associate[$i];
			$data['associate_percentage']=$associate_percentage[$i];
			$data['id'] =$_POST['id'][$i];
	
			$customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from `kc_associate_percentage` where id = '".$data['id']."' limit 0,7 "));
			$customer_detail = mysqli_fetch_assoc(mysqli_query($conn,"select * from `kc_block_number_associates_hist` where customer_id = '".$customer_id."' limit 0,7 "));
			if($customer_detail['customer_id']!=$customer_id){
				if (!mysqli_query($conn,"insert into kc_block_number_associates_hist set customer_id = '$customer_id', block_id = '$block_id', block_number_id = '$block_number_id', associate = '".$customer_details['associate']."', added_by = '".$_SESSION['login_id']."' ")){
					$error = true;
				}
			}else{
				if(!$error && !mysqli_query($conn,"update `kc_block_number_associates_hist`set associate = '".$data['associate_id']."' where id='".$customer_detail['id']."'")){
					$error = true;
				}
			}
			if(!$error && !mysqli_query($conn,"update `kc_associate_percentage`set associate = '".$data['associate_id']."',associate_percentage ='".$data['associate_percentage']."' where id='".$data['id']."'")){
				$error = true;
			}
			
		}

		if (!mysqli_query($conn,"insert into kc_associate_transactions_hist (kc_associate_transactions_id, customer_id, associate_id, block_id, block_number_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, status, remarks, cancel_remarks, action_type, addedon, added_by, deleted_by) select  id, customer_id, associate_id, block_id, block_number_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, status, remarks, 'Associate Changed', 'Associate Changed', addedon, added_by, '".$_SESSION['login_id']."' from kc_associates_transactions where  customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id';")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}

		
		if(!$error && !mysqli_query($conn,"update kc_associates_transactions set associate_id = '$associate' where customer_id = '".$customer_id."' and block_id = '$block_id' and block_number_id = '$block_number_id'")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}

		if($error){
			mysqli_rollback($conn);
			$_SESSION['error'] = 'Something went wrong!';
		}else{
			mysqli_commit($conn);
			$_SESSION['success'] = 'Associate has been Successfully Changed.';
		}
		mysqli_close($conn);
	}
	header("Location:".$page_url);
	exit();
}

if(isset($_POST['changeBlockNumber'])){
	
	//echo "<pre>"; print_r($_POST); die;
	$customer_id = isset($_POST['customer_id'])?(int) $_POST['customer_id']:0;
	$block_id = isset($_POST['block_id'])?(int) $_POST['block_id']:0;
	$block_number_id = isset($_POST['block_number_id'])?(int) $_POST['block_number_id']:0;
	$changed_block_id = isset($_POST['changed_block'])?(int) $_POST['changed_block']:0;
	$changed_block_number_id = isset($_POST['changed_block_no'])?(int) $_POST['changed_block_no']:0;

	$new_block_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, area from kc_block_numbers where id = '$changed_block_number_id' and block_id = '$changed_block_id' limit 0,1 "));

	$customer_block_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, rate_per_sqft from kc_customer_blocks where customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id' limit 0,1 "));
	if($block_number_id == $changed_block_number_id){
		$_SESSION['error'] = 'Plot Number was same!';
	}else if(!isset($customer_block_details['id'])){
		$_SESSION['error'] = 'Old Customer Block Details not found!';
	}else if(!isset($new_block_details['id'])){
		$_SESSION['error'] = 'New Block Details not found!';
	}else{

		$total_plot_value = (float) $new_block_details['area'] * (float) $customer_block_details['rate_per_sqft'];

		$error = false;
		mysqli_autocommit($conn,FALSE);

		if (!mysqli_query($conn,"insert into kc_customer_blocks_hist (kc_customer_blocks_id, customer_id, block_id, block_number_id, rate_per_sqft, final_rate, registry, registry_date, registry_by, sales_person_id, associate, associate_percentage, status, action_type, addedon, added_by, deleted_by) select id, customer_id, block_id, block_number_id, rate_per_sqft, final_rate, registry, registry_date, registry_by, sales_person_id, associate, associate_percentage, status, 'Plot Number Changed', addedon, added_by, '".$_SESSION['login_id']."' from kc_customer_blocks where customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id';")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}
		
		if (!$error && !mysqli_query($conn,"insert into kc_customer_block_plc_hist (kc_customer_block_plc_id, customer_block_id, plc_id, name, plc_percentage, status, action_type, addedon, deleted_by) select id, customer_block_id, plc_id, name, plc_percentage, status, 'Plot Number Changed', addedon, '".$_SESSION['login_id']."' from kc_customer_block_plc where customer_block_id = '".$customer_block_details['id']."';")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}

		if (!$error && !mysqli_query($conn,"insert into kc_customer_transactions_hist (kc_customer_transactions_id, customer_id, block_id, block_number_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, next_due_date, late_for_transaction_id, is_affect_sold_amount, status, remarks, add_transaction_remarks, clear_remarks, clear_date, paid_account_no, action_type, addedon, added_by, deleted_by) select id, customer_id, block_id, block_number_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, next_due_date, late_for_transaction_id, is_affect_sold_amount, status, remarks, add_transaction_remarks, clear_remarks, clear_date, paid_account_no, 'Plot Number Changed', addedon, added_by, '".$_SESSION['login_id']."' from kc_customer_transactions where customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id';")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}


		$transactions = mysqli_query($conn,"select id from kc_customer_transactions where customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id' and cr_dr = 'dr'; ");
		if($transaction = mysqli_fetch_assoc($transactions)){
			receiptNumber($conn,$transaction['id']);
		}

		if (!$error && !mysqli_query($conn,"insert into kc_receipt_numbers_hist (kc_receipt_numbers_id, customer_id, block_id, block_number_id, transaction_id, receipt_id, action_type, deleted_by) select id, customer_id, block_id, block_number_id, transaction_id, receipt_id, 'Plot Number Changed', '".$_SESSION['login_id']."' from kc_receipt_numbers where customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id';")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}

		$isEmiTaken = isEmiTaken($conn,$customer_id,$block_id,$block_number_id);
		if ($isEmiTaken && !mysqli_query($conn,"insert into kc_customer_emi_hist (customer_emi_id, customer_id, block_id, block_number_id, emi_amount, paid_amount, emi_date, paid_date, created, action_type, deleted_by) select  id, customer_id, block_id, block_number_id, emi_amount, paid_amount, emi_date, paid_date, created, 'Plot Number Changed', '".$_SESSION['login_id']."' from kc_customer_emi where  customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id';")){
	        $error = true;
	        echo("Error description: " . mysqli_error($conn)); die;
	    }

		if(!$error && !mysqli_query($conn,"update kc_customer_blocks set block_id = '$changed_block_id', block_number_id = '$changed_block_number_id' where customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id';")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}

		if(!$error && !mysqli_query($conn,"delete from kc_customer_block_plc where customer_block_id = '".$customer_block_details['id']."';")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}

		if(!$error && !mysqli_query($conn,"update kc_customer_transactions set block_id = '$changed_block_id', block_number_id = '$changed_block_number_id' where customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id';")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}

		if(!$error && !mysqli_query($conn,"update kc_receipt_numbers set block_id = '$changed_block_id', block_number_id = '$changed_block_number_id' where customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id';")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}

		if(!$error && $isEmiTaken && !mysqli_query($conn,"update kc_customer_emi set block_id = '$changed_block_id', block_number_id = '$changed_block_number_id' where customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id';")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}
		
		$applied_plc = 0;
		if(!$error && isset($_POST['plc']) && is_array($_POST['plc']) && sizeof($_POST['plc']) > 0){
			foreach($_POST['plc'] as $plc_id){
				$plc_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, name, plc_percentage from kc_plc where id = '$plc_id' limit 0,1 "));
				
				if(isset($plc_details['id'])){
					if(!mysqli_query($conn,"insert into kc_customer_block_plc set customer_block_id = '".$customer_block_details['id']."', plc_id = '".$plc_details['id']."', name = '".$plc_details['name']."', 	plc_percentage = '".$plc_details['plc_percentage']."', status = '1', addedon ='".date('Y-m-d H:i:s')."' ")){
						$error = true;
						echo("Error description: " . mysqli_error($conn)); die;
					}else{
						$applied_plc += (int) (($total_plot_value * $plc_details['plc_percentage'])/100);
					}
				}
			}
		}

		$total_plot_value += $applied_plc;

		if(!($total_plot_value > 0)){
			$error = true;
		}

		if(!mysqli_query($conn,"update kc_customer_transactions set amount = '$total_plot_value' where customer_id = '$customer_id' and block_id = '$changed_block_id' and block_number_id = '$changed_block_number_id' and cr_dr = 'cr' and remarks is NULL limit 1; ")){
			$error = true;
			//echo("Error description: " . mysqli_error($conn)); die;
		}

		if(!$error && !mysqli_query($conn,"update kc_customer_blocks set final_rate = '$total_plot_value' where customer_id = '$customer_id' and block_id = '$changed_block_id' and block_number_id = '$changed_block_number_id' limit 1;")){
			$error = true;
			//echo("Error description: " . mysqli_error($conn)); die;
		}

		if($error){
			mysqli_rollback($conn);
		}else{
			mysqli_commit($conn);
		}

		mysqli_close($conn);

		if(!$error){
			$_SESSION['success'] = 'Plot Number has been Successfully Changed!';
		}else{
			$_SESSION['error'] = 'Something Problem Occured!';
		}
		header("Location:".$page_url);
		exit();
	}
	header("Location:".$page_url);
	exit();
}

if(isset($_POST['addLatePayment'])){
	
	//echo "<pre>"; print_r($_POST); die;
	$customer_id = isset($_POST['late_customer_id'])?(int) $_POST['late_customer_id']:0;
	$block_id = isset($_POST['late_block_id'])?(int) $_POST['late_block_id']:0;
	$block_number_id = isset($_POST['late_block_number_id'])?(int) $_POST['late_block_number_id']:0;
	$late_amount = isset($_POST['late_amount'])?(int) $_POST['late_amount']:0;
	$late_remarks = isset($_POST['late_remarks'])?$_POST['late_remarks']:'';
	$next_due_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['late_next_due_date'])));
	//echo "select id from kc_customer_blocks where customer_id = '".$customer_id."' and block_id = '$block_id' and block_number_id = '$block_number_id' limit 0,1 "; die;
	$customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_customer_blocks where customer_id = '".$customer_id."' and block_id = '$block_id' and block_number_id = '$block_number_id' limit 0,1 "));

	if(!isset($customer_details['id'])){
		$_SESSION['error'] = 'Block Details not Found!';
	}else{

		$latestTransactionID = latestTransactionIDWithoutLate($conn,$customer_id,$block_id,$block_number_id);
		//echo $latestTransactionID; die;
		$error = false;
		mysqli_autocommit($conn,FALSE);
		//echo "insert into kc_customer_transactions set customer_id = '$customer_id', block_id = '$block_id', block_number_id = '$block_number_id', amount = '$late_amount', cr_dr = 'cr', paid_date = '".date("Y-m-d")."', next_due_date = '$next_due_date', remarks = '$late_remarks', status = '1', addedon ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."' "; die;
		if(!mysqli_query($conn,"insert into kc_customer_transactions set customer_id = '$customer_id', block_id = '$block_id', block_number_id = '$block_number_id', amount = '$late_amount', cr_dr = 'cr', paid_date = '".date("Y-m-d")."', next_due_date = '$next_due_date', late_for_transaction_id = '$latestTransactionID', remarks = '$late_remarks', status = '1', addedon ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."' ")){
			$error = true;
			//echo("Error description: " . mysqli_error($conn)); die;
		}
		
		if($error){
			mysqli_rollback($conn);
			$_SESSION['error'] = 'Something went wrong!';
		}else{
			mysqli_commit($conn);
			$_SESSION['success'] = 'Late Payment Amount has been Added Successfully.';
		}
		mysqli_close($conn);
	}
	header("Location:".$page_url);
	exit();
}

if(isset($_POST['cancelTransaction'])){
	
	//echo "<pre>"; print_r($_POST); die;
	
	$transaction_id = isset($_POST['cancel_transaction_id'])?(int) $_POST['cancel_transaction_id']:0;
	$cancel_remarks = isset($_POST['cancel_remarks'])?trim($_POST['cancel_remarks']):'';

	$transaction_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, customer_id, block_id, block_number_id from kc_customer_transactions where id = '".$transaction_id."' limit 0,1 "));
	
	$customer_id = $transaction_details['customer_id'];
	$block_id = $transaction_details['block_id'];
	$block_number_id = $transaction_details['block_number_id'];

	if(!isset($transaction_details['id'])){
		$_SESSION['error'] = 'Transaction not Found!';
	}else if($cancel_remarks == ""){
		$_SESSION['error'] = 'Cancel Remarks is required!';
	}else{
		$error = false;
		mysqli_autocommit($conn,FALSE);

		if (!mysqli_query($conn,"insert into kc_customer_transactions_hist (kc_customer_transactions_id, customer_id, block_id, block_number_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, next_due_date, late_for_transaction_id, is_affect_sold_amount, status, remarks, add_transaction_remarks, cancel_remarks, clear_remarks, clear_date, paid_account_no, action_type, addedon, added_by, deleted_by) select id, customer_id, block_id, block_number_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, next_due_date, late_for_transaction_id, is_affect_sold_amount, status, remarks, add_transaction_remarks, '$cancel_remarks', clear_remarks, clear_date, paid_account_no, 'Payment Cancelled', addedon, added_by, '".$_SESSION['login_id']."' from kc_customer_transactions where id = '$transaction_id';")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}

		$extraCharge = mysqli_query($conn,"select * from kc_customer_transactions where id ='$transaction_id' and add_transaction_remarks = 'Extra Charges Applied'");

		if(!$error && !mysqli_query($conn,"delete from kc_customer_transactions where id = '".$transaction_id."';")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}

		if (!$error && !mysqli_query($conn, "insert into kc_customer_emi_hist (customer_emi_id, customer_id, block_id, block_number_id, emi_amount, paid_amount, emi_date, paid_date, created, action_type, deleted_by) select  id, customer_id, block_id, block_number_id, emi_amount, paid_amount, emi_date, paid_date, created, 'EMI Update', '" . $_SESSION['login_id'] . "' from kc_customer_emi where  customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id';")) {
			$error = true;
			echo ("Error description: " . mysqli_error($conn));
			die;
		}

		if (!mysqli_query($conn,"insert into kc_associate_transactions_hist (customer_id, kc_associate_transactions_id, associate_id, block_id, block_number_id, transaction_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, status, remarks, cancel_remarks, action_type, addedon, added_by, deleted_by) select id , customer_id, associate_id, block_id, block_number_id, transaction_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, status, remarks, '$cancel_remarks', 'Payment Cancelled', addedon, added_by, '".$_SESSION['login_id']."' from kc_associates_transactions where transaction_id = '$transaction_id';")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}
		if(!$error && !mysqli_query($conn,"delete from kc_associates_transactions where transaction_id = '".$transaction_id."';")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}

		if(isEmiTaken($conn,$transaction_details['customer_id'],$transaction_details['block_id'],$transaction_details['block_number_id'])){
			if(!makeEMIPaid($conn,$transaction_details['customer_id'],$transaction_details['block_id'],$transaction_details['block_number_id'])){
				$error = true;
			}
		}

		// if($emi_id != 0){

		// 	$created_at = mysqli_fetch_assoc(mysqli_query($conn, "SELECT *  FROM `kc_change_emi` WHERE `customer_id` = '$customer_id' and id='$emi_id';"));
	
		// 	$created = $created_at['created'];
	
		// 	$date = date("Y-m-d H:i:s",strtotime($created));
		// 	// var_dump($date);
	
		// 	if(!$error && !mysqli_query($conn,"delete from kc_customer_emi where customer_id = '$customer_id' and created >= '$date';")){
		// 		$error = true ;
		// 		echo("Error Description: ". mysqli_error($conn));die;
		// 	}
	
		// 	if(!$error && !mysqli_query($conn, "delete from kc_change_emi where id = '$emi_id' and customer_id = '$customer_id' and created='$date'")){
		// 		$error = true ;
		// 		echo("Error Description: ". mysqli_error($conn));die;
		// 	}
		// }

		// echo $emi_id ;die;
		
		if($error){
			mysqli_rollback($conn);
			$_SESSION['error'] = 'Something went wrong!';
		}else{
			if(mysqli_num_rows($extraCharge)>0){
				mysqli_commit($conn);
				$_SESSION['success'] = 'Transaction has been cancelled Successfully, Edit EMI now.';
			}else{
				mysqli_commit($conn);
				$_SESSION['success'] = 'Transaction has been cancelled Successfully.';
			}

		}
		mysqli_close($conn);
	}
	header("Location:".$page_url);
	exit();
}

if(isset($_POST['registrySubmit'])){
	
	$customer_id = (int) filter_post($conn,$_POST['registry_customer_id']);
	$block_id = (int) filter_post($conn,$_POST['registry_block_id']);
	$block_number_id = (int) filter_post($conn,$_POST['registry_block_number_id']);
	
	
	$registry_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['registry_date'])));
	$registry_by = filter_post($conn,$_POST['registry_by']);
	$khasra_no = filter_post($conn,$_POST['khasra_no']);
	$maliyat_value = filter_post($conn,$_POST['maliyat_value']);
	$sale_value = filter_post($conn,$_POST['sale_value']);

	$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, name from kc_blocks where id = '".$block_id."' limit 0,1 "));
	$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, block_number from kc_block_numbers where id = '".$block_number_id."' limit 0,1 "));
	if(!($customer_id > 0)){
		$_SESSION['error'] = 'Something was Really wrong!';
	}else if(!isset($block_details['id'])){
		$_SESSION['error'] = 'Opps Something was Really wrong!';
	}else if(!isset($block_number_details['id'])){
		$_SESSION['error'] = 'Oppps! Something was Really wrong!';
	}else if($registry_date == '' || $registry_date == '1970-01-01'){
		$_SESSION['error'] = 'Registry Date was wrong!';
	}else if($registry_by == ''){
		$_SESSION['error'] = 'Registry By is required!';
	}else if($khasra_no == ''){
		$_SESSION['error'] = 'Khasra No is required!';
	}else if($maliyat_value == ''){
		$_SESSION['error'] = 'Maliyat Value is required!';
	}else if($sale_value == ''){
		$_SESSION['error'] = 'Sale Value is required!';
	}else{
		
		$error = false;
		mysqli_autocommit($conn,FALSE);

		if(!mysqli_query($conn,"update kc_customer_blocks set registry = 'Yes', registry_date = '$registry_date', registry_by = '$registry_by', khasra_no = '$khasra_no', maliyat_value = '$maliyat_value', sale_value = '$sale_value', registry_by_user_id = '".$_SESSION['login_id']."', registry_by_datetime = '".date("Y-m-d H:i:s")."' where customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id' limit 1 ")){
			$error = true;
			// echo("Error description: " . mysqli_error($conn)); die;
		}
		

		if($error){
			mysqli_rollback($conn);
			$_SESSION['error'] = 'Something Wrong!';
		}else{
			mysqli_commit($conn);
			$_SESSION['success'] = 'Registry Successfully Added!';
			header("Location:".$page_url);
			exit();
		}
	}
}

if(isset($_POST['editRegistry'])){
	// print_r($_POST);die;
	$customer_id = (int) filter_post($conn,$_POST['registry_customer_id']);
	$block_id = (int) filter_post($conn,$_POST['registry_block_id']);
	$block_number_id = (int) filter_post($conn,$_POST['registry_block_number_id']);
	
	
	$registry_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['registry_date'])));
	$registry_by = filter_post($conn,$_POST['registry_by']);
	$khasra_no = filter_post($conn,$_POST['khasra_no']);
	$maliyat_value = filter_post($conn,$_POST['maliyat_value']);
	$sale_value = filter_post($conn,$_POST['sale_value']);

	$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, name from kc_blocks where id = '".$block_id."' limit 0,1 "));
	$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, block_number from kc_block_numbers where id = '".$block_number_id."' limit 0,1 "));
	// print_r($customer_id);die;
	if(!($customer_id > 0)){
		$_SESSION['error'] = 'Something was Really wrong!';
	}else if(!isset($block_details['id'])){
		$_SESSION['error'] = 'Opps Something was Really wrong!';
	}else if(!isset($block_number_details['id'])){
		$_SESSION['error'] = 'Oppps! Something was Really wrong!';
	}else if($registry_date == '' || $registry_date == '1970-01-01'){
		$_SESSION['error'] = 'Registry Date was wrong!';
	}else if($registry_by == ''){
		$_SESSION['error'] = 'Registry By is required!';
	}else if($khasra_no == ''){
		$_SESSION['error'] = 'Khasra No is required!';
	}else if($maliyat_value == ''){
		$_SESSION['error'] = 'Maliyat Value is required!';
	}else if($sale_value == ''){
		$_SESSION['error'] = 'Sale Value is required!';
	}else{
		
		$error = false;

		
		$customer_details = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `registry_date`,`registry_by`,`khasra_no`,`maliyat_value`,`sale_value`,`registry_by_user_id`,`registry_by_datetime`,`sales_person_id` from kc_customer_blocks where `customer_id` = '".$customer_id."' AND `block_id` = ".$block_id."  AND block_number_id = ".$block_number_id."  limit 0,1 "));
        
		$pre_registry_date = $customer_details['registry_date'];
		$pre_registry_by = $customer_details['registry_by'];
		$pre_khasra_no = $customer_details['khasra_no'];
		$pre_maliyat_value = $customer_details['maliyat_value'];
		$pre_sale_value = $customer_details['sale_value'];
		$created_by = $_SESSION['login_id'];
		$registry_by_user_id = $customer_details['registry_by_user_id'];
		$registry_by_datetime1 = $customer_details['registry_by_datetime'];

		if(isset($registry_by_datetime1)){

			$registry_by_datetime = "'".$registry_by_datetime1."'";
		}else{
			$registry_by_datetime = 'Null';
		}
		
		
		mysqli_autocommit($conn,FALSE);

		if(!mysqli_query($conn, "INSERT INTO `kc_registry_hist`(`customer_id`, `block_id`, `block_number_id`, `registry_date`, `registry_by`, `khasra_no`, `maliyat_value`, `registry_by_user_id`, `registry_by_datetime`, `sale_value`,`created_by`, `created_at`) VALUES ('$customer_id','$block_id','$block_number_id','$pre_registry_date','$pre_registry_by','$pre_khasra_no','$pre_maliyat_value','$registry_by_user_id',$registry_by_datetime,'$pre_sale_value','$created_by','".date("Y-m-d H:i:s")."') ")){
			$error = true;
		
			echo("INSERT INTO `kc_registry_hist`(`customer_id`, `block_id`, `block_number_id`, `registry_date`, `registry_by`, `khasra_no`, `maliyat_value`, `registry_by_user_id`, `registry_by_datetime`, `sale_value`,`created_by`, `created_at`) VALUES ('$customer_id','$block_id','$block_number_id','$pre_registry_date','$pre_registry_by','$pre_khasra_no','$pre_maliyat_value','$registry_by_user_id',$registry_by_datetime,'$pre_sale_value','$created_by','".date("Y-m-d H:i:s")."')");
		}


		if(!mysqli_query($conn,"UPDATE kc_customer_blocks set  registry_date = '$registry_date', registry_by = '$registry_by', khasra_no = '$khasra_no', maliyat_value = '$maliyat_value', sale_value = '$sale_value' where customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id' limit 1 ")){
			$error = true;
			// echo("Error description: " . mysqli_error($conn)); die;
		}
		

		if($error){
			mysqli_rollback($conn);
			$_SESSION['error'] = 'Something Wrong!';
		}else{
			mysqli_commit($conn);
			$_SESSION['success'] = 'Registry Successfully Added!';
			header("Location:".$page_url);
			exit();
		}
	}
}

// DELETE REGISTRY CODE
// print_r($page_url);
if((isset($_GET['customer']) && isset($_GET["block"]) && isset($_GET["block_number_id"])&& $_GET['action'] == "delete")){

	$error = false;
    // print_r($page_url);die;
	// $page = $_GET['page'];
	$customer_id = $_GET["customer"];
	$block_id = $_GET["block"];
	$block_number_id = $_GET["block_number_id"];

	$customer_details = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `registry_date`,`registry_by`,`khasra_no`,`maliyat_value`,`sale_value`,`registry_by_user_id`,`registry_by_datetime`,`sales_person_id` from kc_customer_blocks where `customer_id` = '".$customer_id."' AND `block_id` = ".$block_id."  AND block_number_id = ".$block_number_id."  limit 0,1 "));
	
	$pre_registry_date = $customer_details['registry_date'];
	$pre_registry_by =  $customer_details['registry_by']  ;
	$pre_khasra_no =  $customer_details['khasra_no']  ;
	$pre_maliyat_value = $customer_details['maliyat_value']  ;
	$pre_sale_value =  $customer_details['sale_value']  ;
	$created_by = $_SESSION['login_id'];
	$registry_by_user_id =  $customer_details['registry_by_user_id']  ;
	
	// $registry_by_datetime = $customer_details['registry_by_datetime'];
	if(isset($customer_details['registry_by_datetime'])){
		$registry_by_datetime = "'".$customer_details['registry_by_datetime']."'";
	}else{
		$registry_by_datetime = 'Null';
	}
	
	
	// echo $registry_by_datetime;
	//echo ($registry_by_datetime);
	mysqli_autocommit($conn,FALSE);

	if(!mysqli_query($conn, "INSERT INTO `kc_registry_hist`(`customer_id`, `block_id`, `block_number_id`, `registry_date`, `registry_by`, `khasra_no`, `maliyat_value`, `registry_by_user_id`, `registry_by_datetime`, `sale_value`,`created_by`, `created_at`) VALUES ('$customer_id','$block_id','$block_number_id','$pre_registry_date','$pre_registry_by','$pre_khasra_no','$pre_maliyat_value','$registry_by_user_id',$registry_by_datetime,'$pre_sale_value','$created_by','".date("Y-m-d H:i:s")."') ")){
		$error = true;
		
		
		
	}

	if(!mysqli_query($conn,"UPDATE `kc_customer_blocks` SET `registry`='no',`registry_date`=null,`registry_by`=null,`khasra_no`=null,`maliyat_value`=null,`registry_by_user_id`='0',`registry_by_datetime`=null,`sale_value`= null  where `customer_id` = '".$customer_id."' AND `block_number_id` = '".$block_number_id."' AND `block_id` = '".$block_id."' ")){
		$error = true;
	}
	
	if($error){
		mysqli_rollback($conn);
		$_SESSION['error'] = 'Something Wrong!';
		
	}else{
		mysqli_commit($conn);
		$_SESSION['success'] = 'Registry  deleted!';
		header("Location:".$page_url);
		exit();
	}
			
}

if(isset($_POST['revisedRate'])){
	//echo "<pre>"; print_r($_POST); die;
	$customer_id = isset($_POST['rr_customer_id'])?(int) $_POST['rr_customer_id']:0;
	$block_id = isset($_POST['rr_block'])?(int) $_POST['rr_block']:0;
	$block_number_id = isset($_POST['rr_block_no'])?(int) $_POST['rr_block_no']:0;
	$rate = (float) filter_post($conn,$_POST['rr_revised_rate']);
	$payable_amount = (float) filter_post($conn,$_POST['rr_payable_amount']);
	$is_affect_sold_amount = isset($_POST['affect_sold'])?1:0;

	$customer_block_details = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_customer_blocks where customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id' limit 0,1 "));
	if(!isset($customer_block_details['id'])){
		$_SESSION['error'] = 'Customer Block Details not found!';
	}else if(!($rate > 0)){
		$_SESSION['error'] = 'Rate was wrong!';
	}else if(!($payable_amount > 0)){
		$_SESSION['error'] = 'Total Plot Value was wrong!';
	}else{
		$error = false;
		mysqli_autocommit($conn,FALSE);

		$next_due_date = nextDueDate($conn,$customer_id,$block_id,$block_number_id);

		if(!$error && !mysqli_query($conn,"insert into kc_customer_transactions set customer_id = '$customer_id', block_id = '$block_id', block_number_id = '$block_number_id', amount = '$payable_amount', cr_dr = 'cr', paid_date = '".date("Y-m-d")."', next_due_date = '$next_due_date', status = '1', remarks = 'Revised Rate', is_affect_sold_amount = '$is_affect_sold_amount', addedon ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."' ")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}
		if(!$error && !mysqli_query($conn,"insert into kc_revised_rate set customer_id = '$customer_id', block_id = '$block_id', block_number_id = '$block_number_id', rate_per_sqft = '$rate', final_rate = '$payable_amount', addedon ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."' ")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}
		if($error){
			mysqli_rollback($conn);
			$_SESSION['error'] = 'Something went wrong!';
		}else{
			mysqli_commit($conn);
			$_SESSION['success'] = 'Revised Rate has been Successfully Updated.';
		}
		mysqli_close($conn);
		header("Location: ".$page_url);
		exit();
	}
}

if(isset($_POST['applyDiscount'])){
	// echo "<pre>"; print_r($_POST); die();
	$remark = isset($_POST['remark'])?$_POST['remark']:'';
	if($remark == ""){
		$_SESSION['error'] = 'Remarks is required!';
	}
	$customer_id = isset($_POST['dr_customer_id'])?(int) $_POST['dr_customer_id']:0;
	$block_id = isset($_POST['dr_block'])?(int) $_POST['dr_block']:0;
	$block_number_id = isset($_POST['dr_block_no'])?(int) $_POST['dr_block_no']:0;
	$rate = (float) filter_post($conn,$_POST['dr_discount_rate']);
	$payable_amount = (float) filter_post($conn,$_POST['dr_payable_amount']);
	$is_affect_sold_amount = isset($_POST['affect_sold'])?1:0;
	

	$customer_block_details = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_customer_blocks where customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id' limit 0,1 "));
	if(!isset($customer_block_details['id'])){
		$_SESSION['error'] = 'Customer Block Details not found!';
	}else if(!($rate > 0)){
		$_SESSION['error'] = 'Rate was wrong!';
	}else if(!($payable_amount > 0)){
		$_SESSION['error'] = 'Total Plot Value was wrong!';
	}else{
		$error = false;
		mysqli_autocommit($conn,FALSE);

		$next_due_date = nextDueDate($conn,$customer_id,$block_id,$block_number_id);

		if(!$error && !mysqli_query($conn,"insert into kc_customer_transactions set customer_id = '$customer_id', block_id = '$block_id', block_number_id = '$block_number_id', amount = '$payable_amount', cr_dr = 'dr', paid_date = '".date("Y-m-d")."', next_due_date = '$next_due_date', is_affect_sold_amount = '$is_affect_sold_amount', status = '1', remarks = '$remark', addedon ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."' ")){
			$error = true;
		}

		if(!makeEMIPaid($conn,$customer_id,$block_id,$block_number_id)){
			$error = true;
		}

	



		if($error){
			mysqli_rollback($conn);
			$_SESSION['error'] = 'Something went wrong!';
		}else{
			mysqli_commit($conn);
			$_SESSION['success'] = 'Discount Rate has been Successfully Updated.';
		}
		mysqli_close($conn);
		header("Location: ".$page_url);
		exit();
	}
}

if(isset($_POST['addExtraCharges'])){
	// echo "<pre>"; print_r($_POST['send_message']?$_POST['send_message']:'sdfg'); die();
	$cr_dr = isset($_POST['cr_dr'])?$_POST['cr_dr']:'';
	$customer_id = isset($_POST['customer_id'])?(int)$_POST['customer_id']:0;
	//  print_r($customer_id); die();
	$block_id = isset($_POST['block_id'])?(int)$_POST['block_id']:0;
	$block_number_id = isset($_POST['block_number_id'])?(int) $_POST['block_number_id']:0;
	$amount = $_POST['amount'];
	$remarks = $_POST['remarks'];
	$send_message = isset($_POST['send_message'])?true:false;
	 // print_r($remarks); die();
	if(($customer_id == null)){
		$_SESSION['error'] = 'Customer not found!';
	}else if(($block_id == null)){
		$_SESSION['error'] = 'Block not found!';
	}else if(($block_number_id == null)){
		$_SESSION['error'] = 'Plot not found!';
	}else if(($cr_dr == null)){
		$_SESSION['error'] = 'Cr_dr not found!';
	}else if(($remarks == null)){
		$_SESSION['error'] = 'Remarks not found!';
	}else if(($amount < 0)){
		$_SESSION['error'] = 'Amount was wrong!';
	}else{
		$error = false;
		mysqli_autocommit($conn,FALSE);
	
		$extra_amount = (int)abs(extraPaidAmount($conn,$customer_id,$block_id,$block_number_id));		
		
		
		
		if (!$error && !mysqli_query($conn, "insert into kc_customer_emi_hist (customer_emi_id, customer_id, block_id, block_number_id, emi_amount, paid_amount, emi_date, paid_date, created, action_type, deleted_by) select  id, customer_id, block_id, block_number_id, emi_amount, paid_amount, emi_date, paid_date, created, 'EMI Update', '" . $_SESSION['login_id'] . "' from kc_customer_emi where  customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id';")) {
			$error = true;
			echo ("Error description: " . mysqli_error($conn));
			die;
		}
		
		

		
		
		if(!$error && !mysqli_query($conn,"insert into kc_customer_transactions set customer_id = '$customer_id', block_id = '$block_id', block_number_id = '$block_number_id', amount = '$amount',next_due_date = '2000-01-01', cr_dr = 'cr', status = '1', remarks = '$remarks', add_transaction_remarks = 'Extra Charges Applied', addedon ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."'")){
			$error = true;
		}
		
		
		
		
		if($error){
			mysqli_rollback($conn);
			$_SESSION['error'] = 'Something went wrong!';
		}else{
			if($send_message){
				
				
				$customer_mobile = mysqli_fetch_assoc(mysqli_query($conn,"SELECT mobile from kc_customers where id = '".$customer_id."'"));
				$mobile = $customer_mobile['mobile'];
				$variables_array = array('variable1'=>$amount = $_POST['amount']);
				// if(sendMessage($conn,26,$mobile,$variables_array)){
				// 	$_SESSION['success'] .= ' and Welcome Message sent Successfully!';
				// }else if(!isset($_SESSION['error'])){
				// 	$_SESSION['error'] = 'Welcome Message not sent!';
				// }else if(isset($_SESSION['error'])){
				// 	$_SESSION['error'] .= ' and Welcome Message not sent!';
				// }
			}
			mysqli_commit($conn);
		
			$_SESSION['success'] = 'Extra Charges Added Edit EMI to Take Effect !';
		}
		mysqli_close($conn);
		
		header("Location: ".$page_url);
		exit();
	}
}

// echo "<pre>"; print_r($_GET); die;
 if(isset($_GET['action']) && $_GET['action'] == "blacklist" && isset($_GET['customer']) && is_numeric($_GET['customer'])){
	$customer_id= $_GET['customer'];
    // echo "<pre>"; print_r($customer_id); die;
	$customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select blacklisted from kc_customers where id = '".$customer_id."' limit 0,1 "));
	// echo "<pre>"; print_r($customer_details); die;
	if(isset($customer_details['blacklisted'])){
		$current_blacklisted = $customer_details['blacklisted'];
		// echo "<pre>"; print_r($current_blacklisted);die;
		if($current_blacklisted == 0){
			$new_blacklisted = 1;
		}else{
			$new_blacklisted= 0;
		}
		mysqli_query($conn,"update kc_customers set blacklisted = '$new_blacklisted' where id = '".$customer_id."' limit 1 ");
		//  echo "<pre>"; print_r($sql);die;
		if($new_blacklisted == 1){
		$_SESSION['success'] = 'customer has been  successfully marked blacklist';
        }else{
            $_SESSION['success']='customer has been removed from blacklist ';
        }
		// header("Location:customers.php");
        header("Location:".$page_url);
		exit();
    }
	$_SESSION['error'] = 'Something Wrong!';
	header("Location:".$page_url);
    
	exit();
}
//  echo "pre";print_r($_GET);die;
if(isset($_GET['action']) && $_GET['action'] == "message"  && isset($_GET['customerBlock']) && is_numeric($_GET['customerBlock'])){	
	$customer_block_id= $_GET['customerBlock'];

	$blocks = mysqli_fetch_assoc(mysqli_query($conn,"select id, customer_id, block_id, block_number_id,customer_payment_type, installment_amount from kc_customer_blocks where id = '".$customer_block_id."' and status = '1' "));
	// echo "<pre>"; print_r($blocks); die;

	$customer_mobile = mysqli_fetch_assoc(mysqli_query($conn,"SELECT mobile from kc_customers where id = '".$blocks['customer_id']."'"));
	// echo "<pre>";print_r($customer_mobile);die;
	$mobile=$customer_mobile['mobile'];

	if($blocks['customer_payment_type']=="EMI"){
		$pending_amount=$blocks['installment_amount'];
	}
	else{
		$pending_amount=getPartAmount($conn,$blocks['customer_id'],$blocks['block_id'],$blocks['block_number_id']);
	}
								
	$variables_array = array('variable2'=>$pending_amount,'variable3'=>nextDueDate($conn,$blocks['customer_id'],$blocks['block_id'],$blocks['block_number_id']),'variable4'=>blockProjectName($conn,$blocks['block_id']).blockName($conn,$blocks['block_id']).'('.blockNumberName($conn,$blocks['block_number_id']).')' );
	print_r($variables_array);die; 
	if(sendMessage($conn,31,$mobile,$variables_array)){

		$_SESSION['success'] = 'Message sent Successfully!';
	
    }
	else{
	  $_SESSION['error'] =   'Message not send!';	
    }
 	header("Location:".$page_url);
	exit();

}

if (isset($_POST['dd_number'])){
	$cheque_dd_number= $_POST['dd_number'];
	$result= mysqli_fetch_assoc(mysqli_query($conn,"SELECT cheque_dd_number FROM `kc_customer_transactions` WHERE `status`= '1' AND `cheque_dd_number`= '$cheque_dd_number' ")) ;
	 
	if($cheque_dd_number== ($result['cheque_dd_number'])){ 
		echo (json_encode(['exists'=> $result['cheque_dd_number'] ]));die;
	}else{ 
		echo (json_encode(['exists'=>0 ]));die;
	}
    
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>WCC | Admin Panel</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- jQuery UI 1.11.4 -->
    <link href="/<?php echo $host_name; ?>/plugins/jQueryUI/jquery-ui.css" rel="stylesheet" type="text/css" />
    <!-- Bootstrap 3.3.4 -->
    <link href="/<?php echo $host_name; ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- FontAwesome 4.3.0 -->
    <link href="/<?php echo $host_name; ?>/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Ionicons 2.0.0 -->
    <link href="/<?php echo $host_name; ?>/css/ionicons.min.css" rel="stylesheet" type="text/css" />
	<link rel="icon" type="image/x-icon" href="/<?php echo $host_name; ?>img/logo.png">
	<!-- Select2 -->
    <link href="/<?php echo $host_name; ?>/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
	
    <!-- Theme style -->
    <link href="/<?php echo $host_name; ?>/dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link href="/<?php echo $host_name; ?>/dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />
    <!-- iCheck -->
    <link href="/<?php echo $host_name; ?>/plugins/iCheck/square/blue.css" rel="stylesheet" type="text/css" />
    <!-- Morris chart -->
    <link href="/<?php echo $host_name; ?>/plugins/morris/morris.css" rel="stylesheet" type="text/css" />
    <!-- jvectormap -->
    <link href="/<?php echo $host_name; ?>/plugins/jvectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- Date Picker -->
    <link href="/<?php echo $host_name; ?>/plugins/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker -->
    <link href="/<?php echo $host_name; ?>/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
    <!-- bootstrap wysihtml5 - text editor -->
    <link href="/<?php echo $host_name; ?>/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css" />
	
	<!-- Developer Css -->
    <link href="/<?php echo $host_name; ?>/css/style.css" rel="stylesheet" type="text/css" />

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="/<?php echo $host_name; ?>/js/html5shiv.min.js"></script>
        <script src="/<?php echo $host_name; ?>/js/respond.min.js"></script>
    <![endif]-->

    <style type="text/css">
    	.iradio_square-blue.has-error > .form-error{
    		margin-top: 25px;
    	}
    	.search-container{
    		margin-top:10px;
    	}
    	.dropdown.dropdown-lg .dropdown-menu {
    margin-top: -1px;
    padding: 6px 20px;
}
.input-group-btn .btn-group {
    display: flex !important;
}
.btn-group .btn {
    border-radius: 0;
    margin-left: -1px;
}
.btn-group .btn:last-child {
    border-top-right-radius: 4px;
    border-bottom-right-radius: 4px;
}
.btn-group .form-horizontal .btn[type="submit"] {
  border-top-left-radius: 4px;
  border-bottom-left-radius: 4px;
}
.form-horizontal .form-group {
    margin-left: 0;
    margin-right: 0;
}
.form-group .form-control:last-child {
    border-top-left-radius: 4px;
    border-bottom-left-radius: 4px;
}

@media screen and (min-width: 768px) {
    #adv-search {
        width: 500px;
        margin: 0 auto;
    }
    .dropdown.dropdown-lg {
        position: static !important;
    }
    .dropdown.dropdown-lg .dropdown-menu {
        min-width: 500px;
    }
}
/* .modal{
	overflow:auto!important;
} */
    </style>
  </head>
  <body class="skin-blue sidebar-mini">
    <div class="wrapper">

      <?php require('../includes/header.php'); ?>
      <!-- Left side column. contains the logo and sidebar -->
      <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <?php echo require('../includes/left_sidebar.php'); ?>
        <!-- /.sidebar -->
      </aside>

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Masters
            <small>Control panel</small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Customers</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
			<div class="box">
                <div class="box-header">
					<?php 
					include("../includes/notification.php"); ?>
					<div class="row">
						<div class="col-sm-8">
							<h3 class="box-title">All Customers</h3>
						</div>
					<?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'add_customer')){ ?>
	                    <div class="col-sm-4">
							<button class="btn btn-sm btn-success pull-right" data-toggle="modal" data-target="#addCustomer">Add Customer</button>
						</div>
						<input type ="hidden" id="carries">
					<?php } ?>
					</div>
					<hr />

					<form class="" action="customers.php" name="search_frm" id="search_frm" method="get">
						<div class="form-group col-sm-3">
							<label for="search_customer">Customer <a href="javascript:void(0);" class="text-primary" data-toggle="popover" title="Customer Search Hint" data-content="'c-' for Code Search<br>'n-' for Name Search<br>'m-' for Mobile Search<br><b>Eg:</b> If want to search 00942 in only code then Search for 'c-00942' <br><br> <b>OR</b> <br> <br> You can search similar name by Pressing 'Enter Key'"><i class="fa fa-info-circle"></i></a></label>
						  	
						  	<input type="text" class="form-control customer-autocomplete" placeholder="Name or Code or Mobile" data-for-id="search_customer">
							<input type="hidden" name="search_customer" id="search_customer">

							<?php /*<input type="text" class="form-control" placeholder="Search Name" name="search_customer" id="search_customer">*/ ?>

						</div>
						<div class="form-group col-sm-3">
							<label for="search_project">Project </label>
						  	<select class="form-control" id="search_project" name="search_project" onChange="search_getBlocks(this.value);">
						    	<option value="">Select Project</option>
						        <?php
								$projects = mysqli_query($conn,"select * from kc_projects where status = '1' ");
								while($project = mysqli_fetch_assoc($projects)){ ?>
						        	<option value="<?php echo $project['id']; ?>"><?php echo $project['name']; ?></option>
						        <?php } ?>
							</select>
						</div>
						<div class="form-group col-sm-3">
							<label for="search_block">Block</label>
							<select class="form-control" id="search_block" name="search_block" onChange="search_getBlockNumbers(this.value);">
						        <option value="">Select Block</option>
						        <?php
								/*$blocks = mysqli_query($conn,"select * from kc_blocks where status = '1' ");
								while($block = mysqli_fetch_assoc($blocks)){ ?>
						        	<option value="<?php echo $block['id']; ?>"><?php echo $block['name']; ?></option>
						        <?php }*/ ?>
						    </select>
						</div>

						<div class="form-group col-sm-3">
							<label for="search_block_no">Plot Number</label>
							<select class="form-control" id="search_block_no" name="search_block_no">
						        <option value="">Select Plot Number</option>
						    </select>
						</div>
						<div class="form-group col-sm-3">
							<label for="search_associate">Associate <a href="javascript:void(0);" class="text-primary" data-toggle="popover" title="Associate Search Hint" data-content="'c-' for Code Search<br>'n-' for Name Search<br>'m-' for Mobile Search<br><b>Eg:</b> If want to search 9651 in only code then Search for 'c-9651' "><i class="fa fa-info-circle"></i></a></label>
							<input type="text" class="form-control associate-autocomplete" data-for-id="search_associate" placeholder="Name or Code or Mobile">
							<input type="hidden" name="search_associate" id="search_associate">
							<?php
								/*<select class="form-control" id="search_associate" name="search_associate">
						        <option value="">Select Associate</option>
						        $associates = mysqli_query($conn,"select * from kc_associates where status = '1' ");
								while($associate = mysqli_fetch_assoc($associates)){ ?>
						        	<option value="<?php echo $associate['id']; ?>"><?php echo $associate['name']; ?></option>
						        <?php }
						    </select>*/ ?>
						</div>

						<div class="form-group col-sm-3">
							<label for="search_employee">Employee</label>
							<select class="form-control" id="search_employee" name="search_employee">
						        <option value="">Select Employee</option>
						        <?php
								$employees = mysqli_query($conn,"select * from kc_employees where status = '1' ");
								while($employee = mysqli_fetch_assoc($employees)){ ?>
						        	<option value="<?php echo $employee['id']; ?>"><?php echo $employee['name']; ?></option>
						        <?php } ?>
						    </select>
						</div>
						<button type="submit" name="search" value="Search" class="btn btn-primary" style="margin-top: 24px;"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
					</form>
				</div>
                <div class="box-body no-padding">
                	<div class="table-responsive">
					 <table class="table table-striped table-hover table-bordered">
	                    <tr>
	                      <th>Sl No.</th>
						  <th>Details</th>
						  <th>Other Details</th>
						  <th>Block Transactions Details</th>
						  <?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'view_customer')) {?>
	                      <th>Action</th>
	                  <?php } ?>
						</tr>
						<?php
                        
						$query = "select * from kc_customers where  status = 1  ";
						// $query = "select * from kc_customers ";


						if(isset($_GET['search_customer']) && $_GET['search_customer'] != ''){
							//$query .= " and name LIKE '%".$_GET['search_customer']."%'";
							if(!ctype_digit($_GET['search_customer'])){
								$query .= " and name LIKE '%".$_GET['search_customer']."%'";
							}else{
								$query .= " and id = '".$_GET['search_customer']."'";
							}
							$url .= '&search_customer='.$_GET['search_customer'];
						}
						if(isset($_GET['search_project']) && (int) $_GET['search_project'] > 0){
							$project_id = (int) $_GET['search_project'];
							$query .= " and id IN (SELECT customer_id from kc_customer_blocks WHERE block_id IN (SELECT id FROM kc_blocks WHERE project_id = '$project_id') )";
							// echo "$query";die;
							$url .= '&search_project='.$_GET['search_project'];
						}
						if(isset($_GET['search_block']) && (int) $_GET['search_block'] > 0){
							$block_id = (int) $_GET['search_block'];
							$query .= " and id IN (select customer_id from kc_customer_blocks where status = '1' and block_id = '$block_id' )";
							$url .= '&search_block='.$_GET['search_block'];
						}
						if(isset($_GET['search_block_no']) && (int) $_GET['search_block_no'] > 0){
							$block_number_id = (int) $_GET['search_block_no'];
							$query .= " and id IN (select customer_id from kc_customer_blocks where status = '1' and block_number_id = '$block_number_id' )";
							$url .= '&search_block_no='.$_GET['search_block_no'];
						}

						if(isset($_GET['search_employee']) && (int) $_GET['search_employee'] > 0){
							$sales_person_id = (int) $_GET['search_employee'];
							$query .= " and id IN (select customer_id from kc_customer_blocks where status = '1' and sales_person_id = '$sales_person_id' )";
							$url .= '&search_employee='.$_GET['search_employee'];
						}

						if(isset($_GET['search_associate']) && (int) $_GET['search_associate'] > 0){
							$associate_id = (int) $_GET['search_associate'];
							$query .= " and (id IN (select customer_id from kc_customer_blocks where status = '1' and associate = '$associate_id' ) or id IN (select customer_id from kc_customer_blocks_hist where associate = '$associate_id' ))";
							$url .= '&search_associate='.$_GET['search_associate'];
						}
						
						$total_records = mysqli_num_rows(mysqli_query($conn,$query));
						$total_pages = ceil($total_records/$limit);
						
						if($page == 1){
							$start = 0;
						}else{
							$start = ($page-1)*$limit;
						}
						$query .= " limit $start,$limit";
						// echo $query;die;
						$customers = mysqli_query($conn,$query);
						if(mysqli_num_rows($customers) > 0){
							$counter = $start + 1;
                            // echo"<pre>";print_r($counter);die;
							while($customer = mysqli_fetch_assoc($customers)){
								$blocks = mysqli_query($conn,"select id, block_id, block_number_id, installment_amount, registry, registry_date, registry_by, sales_person_id from kc_customer_blocks where customer_id = '".$customer['id']."' and status = '1' ");
								
								?>
								<tr style=" <?php if($customer['blacklisted']== '1') {echo 'background:#ff7979'; }?>">

									<td><?php echo $counter; ?></td>
									<td nowrap="nowrap">
									<a href="javascript:void(0)" data-toggle="tooltip" title="View Customer's Information" onclick = "viewInformation(<?php echo $customer['id']; ?>);"><strong><?php echo $customer['name_title']; ?> <?php echo $customer['name'].' ('.customerID($customer['id']).')'; ?></strong></a><br>
	                                    <strong><?php echo $customer['parent_name_relation']; ?></strong> <?php if($customer['parent_name'] != ''){ ?>of <strong><?php echo isset($customer['parent_name_sub_title'])?$customer['parent_name_sub_title']:''; ?> <?php echo $customer['parent_name']; } ?></strong><br>
	                                    <?php if($customer['nominee_name'] != ''){ ?>
	                                    	Co-owner: <strong class="text-danger"><?php echo $customer['nominee_name']; ?></strong>
	                                    	<?php if($customer['nominee_relation'] != ''){ ?>
		                                    	<strong class="text-danger">(<?php echo $customer['nominee_relation']; ?>)</strong>
		                                    <?php } ?>
		                                    <br>
	                                    <?php } ?>
	                                </td>
	                                <td>
										Email: <strong><?php echo $customer['email']; ?></strong><br>
	                                    Mobile: <strong><?php echo $customer['mobile']; ?></strong>
	                                </td>
	                                <td nowrap="nowrap">
	                                	<?php
	                                    $block_names = array();
	                                    if(mysqli_num_rows($blocks) > 0){
											while($block = mysqli_fetch_assoc($blocks)){
												$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_blocks where id = '".$block['block_id']."' limit 0,1 "));
												$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select block_number from kc_block_numbers where id = '".$block['block_number_id']."' limit 0,1 "));
												$sales_person_detials = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_employees where id = '".$block['sales_person_id']."' and status = '1' limit 0,1 "));
												$emi = ($block['installment_amount']>0)?true:false;
												if($emi){
													$second_next_emi_details = nextEMIDetails($conn,$customer['id'],$block['block_id'],$block['block_number_id'],2);
												}
												else{
														$nextduedate = '';
												    }
												
												?>

												<?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'add_transactions_customer')){  
													
													if($emi){

														if(isLastEmiPayment($conn,$customer['id'],$block['block_id'],$block['block_number_id'])){
															$nextduedate = '';
														}else{
															if($second_next_emi_details){
																$nextduedate =  $second_next_emi_details['emi_date']? date("d-m-Y",strtotime($second_next_emi_details['emi_date'])):'';
															}else{
																$nextduedate = '';
															}
															
														}
													}else{
														$nextduedate = false;
													}

												?>
													 
		                                        <button class="btn btn-xs btn-success" onClick="addTransaction(<?php echo $customer['id']; ?>,<?php echo $block['block_id']; ?>,<?php echo $block['block_number_id']; ?>,'<?php echo $nextduedate ?>','<?php echo $emi; ?>');" data-toggle="tooltip" title="Add Transaction"><i class="fa fa-money"></i></button>
		                                    <?php }if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'view_transactions_customer')){?>
		                                		<button class="btn btn-xs btn-warning" onClick="getTransactions(<?php echo $customer['id']; ?>,<?php echo $block['block_id']; ?>,<?php echo $block['block_number_id']; ?>);" data-toggle="tooltip" title="View Transactions"><i class="fa fa-money"></i></button>
		                                	<?php } ?>
		                                        <?php
		                                        if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'print_allotment_customer')){
												$total_credited = totalCredited($conn,$customer['id'],$block['block_id'],$block['block_number_id']);
												$total_debited = totalDebited($conn,$customer['id'],$block['block_id'],$block['block_number_id']);
												$one_fourth = ($total_credited*25)/100;
												//if($total_debited >= $one_fourth){ ?>
													<a class="btn btn-xs btn-danger" target="_blank" href="allotment_letter.php?cb=<?php echo $block['id']; ?>" data-toggle="tooltip" title="Print Allotment"><i class="fa fa-print"></i></a>

													<?php } /* ?><a class="btn btn-xs btn-info" target="_blank" href="reminder_letter.php?cb=<?php echo $block['id']; ?>" data-toggle="tooltip" title="Print Reminder"><i class="fa fa-print"></i></a>

													<a class="btn btn-xs btn-default" target="_blank" href="statement.php?cb=<?php echo $block['id']; ?>" data-toggle="tooltip" title="Print Statement"><i class="fa fa-print"></i></a><?php */ ?>
		                                         <?php
												//}
												
		                                        ?>
		                                        <?php /* Comment on 18-11-2019 due to not in use
		                                        <button class="btn btn-xs btn-primary" onClick="changeEmployee(<?php echo $customer['id']; ?>,<?php echo $block['block_id']; ?>,<?php echo $block['block_number_id']; ?>);" data-toggle="tooltip" title="Change Employee"><i class="fa fa-pencil"></i></button>*/ ?>
		                                        <?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'change_associate_customer')){ ?>
		                                        <button class="btn btn-xs btn-success" onClick="changeAssociate(<?php echo $customer['id']; ?>,<?php echo $block['block_id']; ?>,<?php echo $block['block_number_id']; ?>);" data-toggle="tooltip" title="Change Associate"><i class="fa fa-pencil"></i></button>
		                                    <?php } if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'change_plot_number_customer')){?>
		                                        <button class="btn btn-xs btn-warning" onClick="changeBlockNumber(<?php echo $customer['id']; ?>,<?php echo $block['block_id']; ?>,<?php echo $block['block_number_id']; ?>);" data-toggle="tooltip" title="Change Plot Number"><i class="fa fa-pencil"></i></button>
		                                    <?php } ?>
		                                        <?php 
		                                        if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'registry_customer')){
		                                        if($block['registry'] != 'yes'){ ?>
		                                        	<button class="btn btn-xs btn-danger" onClick="registry(<?php echo $customer['id']; ?>,<?php echo $block['block_id']; ?>,<?php echo $block['block_number_id']; ?>);" data-toggle="tooltip" title="Registry"><i class="fa fa-plus"></i></button>
		                                        <?php } ?>
		                                        <?php if($emi){ ?>
		                                        	<a class="btn btn-xs btn-info" target="_blank" href="renewal.php?customer=<?php echo $customer['id']; ?>&block=<?php echo $block['block_id']; ?>&cbn=<?php echo $block['block_number_id']; ?>&action=true" data-toggle="tooltip" title="EMI Payment"><i class="fa fa-money"></i></a>
		                                       	<?php } }?>

												<?php 
                                                  if($_SESSION['login_type'] == 'super2admin'){ 
                                                   
													 if($block['registry'] == 'yes'){ ?>
													   <a href="<?php echo $page_url; ?>&customer=<?php echo $customer['id']; ?>&block=<?php echo $block['block_id']; ?>&block_number_id=<?php echo $block['block_number_id'];?>&page=<?php echo $page;?>&action=delete" class="btn btn-xs btn-danger"   onclick="return confirm('Are You Sure?')" data-toggle="tooltip" title="Delete registry"><i class="fa fa-trash"></i></a>

													   <button class="btn btn-xs btn-warning" onClick="edit_registry(<?php echo $customer['id']; ?>,<?php echo $block['block_id']; ?>,<?php echo $block['block_number_id']; ?>);" data-toggle="tooltip" title="Edit registry"><i class="fa fa-pencil"></i></button>

												 <?php } }?>
                                                
												



                                                 <!--      
												/*if($block['registry'] == "yes"){
													$registry_status = '<strong class="text-success">Registry Done</strong>';
												}else{
													$registry_status = '<strong class="text-danger">Registry Not Done</strong>';
												}*/ -->
												<?php
												if($block['registry'] == 'yes'){
													echo '<br>Registry Date: '.date("d-m-Y",strtotime($block['registry_date']));
													echo '<br>Registry By: '.$block['registry_by'];
												}
												// print_r("<br/>".$block_number_details['block_number']);
												echo '<br/><h5 class="text-success">'.blockProjectName($conn,$block['block_id']).'<br>'.	$block_details['name'].'('.$block_number_details['block_number'].')'."</h5>";//."($registry_status)"
												if($emi){
													echo '<strong class="text-warning">EMI Amount: '.$block['installment_amount'].' ₹</strong><br>';
												}
												
												if(isOutStandingPayment($conn,$customer['id'],$block['block_id'],$block['block_number_id'])){
													$next_due_date = nextDueDate($conn,$customer['id'],$block['block_id'],$block['block_number_id']);
													if($next_due_date < date("Y-m-d") && !isLatePaymentApplied($conn,$customer['id'],$block['block_id'],$block['block_number_id'],$next_due_date)){
														echo '<strong class="text-danger">Next Due: '.formatDate($next_due_date).'</strong><hr>';
														//echo '<strong class="text-danger">Next Due: '.formatDate($next_due_date).'</strong><br><button class="btn btn-xs btn-danger" onClick="addLatePayment('.$customer['id'].','.$block['block_id'].','.$block['block_number_id'].',\''.$next_due_date.'\');" data-toggle="tooltip" title="Add Late Payment"><i class="fa fa-rupee"></i></button><br><hr>';
													}else{
														echo '<strong class="text-info">Next Due: '.formatDate($next_due_date).'</strong><br>';
													}
													if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'add_revised_rate_customer')){
													?>
													<button class="btn btn-xs btn-success" onClick="addRevisedRate(<?php echo $customer['id']; ?>,<?php echo $block['block_id']; ?>,<?php echo $block['block_number_id']; ?>);" data-toggle="tooltip" title="Add Revised Rate"><i class="fa fa-plus"></i></button>
												<?php } if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'apply_discount_customer')){ ?>
													<button class="btn btn-xs btn-warning" onClick="applyDiscount(<?php echo $customer['id']; ?>,<?php echo $block['block_id']; ?>,<?php echo $block['block_number_id']; ?>);" data-toggle="tooltip" title="Apply Discount"><i class="fa fa-plus"></i></button>
													
											<?php
											} if(userCan($conn,$_SESSION['login_id'],$privilegeName = '')){  ?>
												<button class="btn btn-xs btn-primary" onClick="addExtraCharges(<?php echo $customer['id']; ?>,<?php echo $block['block_id']; ?>,<?php echo $block['block_number_id']; ?>);" data-toggle="tooltip" title="Add Extra Charges"><i class="fa fa-plus"></i></button>
											<?php }
												
												}else{
													   if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'add_revised_rate_customer')){
														?>
															<button class="btn btn-xs btn-success" onClick="addRevisedRate(<?php echo $customer['id']; ?>,<?php echo $block['block_id']; ?>,<?php echo $block['block_number_id']; ?>);" data-toggle="tooltip" title="Add Revised Rate"><i class="fa fa-plus"></i></button>
														<?php }
														if(userCan($conn,$_SESSION['login_id'],$privilegeName = '')){  ?>
															<button class="btn btn-xs btn-primary" onClick="addExtraCharges(<?php echo $customer['id']; ?>,<?php echo $block['block_id']; ?>,<?php echo $block['block_number_id']; ?>);" data-toggle="tooltip" title="Add Extra Charges"><i class="fa fa-plus"></i></button>
														<?php }
														if(userCan($conn,$_SESSION['login_id'],$privilegeName = '')){ ?>
															<a href="<?php echo $page_url;?>&customerBlock=<?php echo $block['id']; ?>&action=message" class="btn btn-xs btn-warning" data-toggle="tooltip" title="Send message" onclick = "return confirm('Are you sure you want to send message ')"><i class="fa fa-send"></i></a>
                                  
													    <?php }
													 	 
									     		    }				
										    	 }
										     }
									    
									
								            	


										$deleted_blocks = mysqli_query($conn,"select id, block_id, block_number_id, registry, registry_date, registry_by, sales_person_id from kc_customer_blocks_hist where customer_id = '".$customer['id']."' and action_type = 'Cancel Booking' ");
										while($block = mysqli_fetch_assoc($deleted_blocks)){
											$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_blocks where id = '".$block['block_id']."' limit 0,1 "));
											$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select block_number from kc_block_numbers where id = '".$block['block_number_id']."' limit 0,1 "));
											if(!isset($block_number_details['block_number'])){
												$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select block_number from kc_block_numbers_hist where block_number_table_id = '".$block['block_number_id']."' limit 0,1 "));
											}
											// echo "<pre>";print_r($block_number_details);die;
											echo '<a class="text-danger" href="javascript:void(0);" onClick="getArchivedTransactions('.$block['id'].');"><h5 class="text-danger" data-toggle="tooltip" title="View Transactions">'.blockProjectName($conn,$block['block_id']).'<br>'.$block_details['name'].'('.$block_number_details['block_number'].')'."</h5></a>";
											?>
											<hr>
											<?php
										} ?>
	                                </td>
   

	                                <td nowrap="nowrap">
	                                	<?php $blocks = mysqli_query($conn,"select id, block_id, block_number_id, installment_amount, registry, registry_date, registry_by, sales_person_id from kc_customer_blocks where customer_id = '".$customer['id']."' and status = '1' ");
	                                	while($block = mysqli_fetch_assoc($blocks)){
	                                	?>

	                                	<a href="../management/viewcustomerfollowups.php?customer_id=<?= $customer['id'];?>&block_id=<?= $block['block_id'];?>&block_number_id=<?= $block['block_number_id'];?>"><button class="btn btn-xs btn-primary" type="button" data-toggle="tooltip" title="View Followups History" ><i class="fa fa-history"></i></button></a>
	                                	<?php  }if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'view_customer')){ ?>
	                                	<button class="btn btn-xs btn-info" type="button" data-toggle="tooltip" title="View Customer's Information" onclick = "viewInformation(<?php echo $customer['id']; ?>);"><i class="fa fa-eye"></i></button>
	                                <?php } if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'edit_customer_customer')){ ?>
	                                	<button class="btn btn-xs btn-warning" type="button" data-toggle="tooltip" title="Edit Customer's Information" onclick = "editInformation(<?php echo $customer['id']; ?>);"><i class="fa fa-pencil"></i></button>
	                                <?php } if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'add_block_customer')){ ?>
	                                    <button class="btn btn-xs btn-primary" type="button" data-toggle="tooltip" title="Add Block" onclick = "addBlock(<?php echo $customer['id']; ?>);"><i class="fa fa-cubes"></i></button>								
	                                  <?php } 
										 
                                    if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'mark_blacklist')) {
										// echo "<pre>";print_r($customer);die;
										if($customer['blacklisted']=='0'){
											$button_class = 'btn-success';
											$icon_class = 'fa fa-ban';
											$btn_title = "Mark blacklist ";
										
                                        } else{

											$button_class = 'btn-danger';
											$icon_class = 'fa fa-ban';
											$btn_title = "Remove from blacklist ";
										
										}

									}
                                     
                                      
	                                   ?> 
                        				<a href="<?php echo $page_url;?>&customer=<?php echo $customer['id']; ?>&action=blacklist" data-toggle="tooltip" title="<?php echo $btn_title; ?>">
			         					<button class="btn btn-xs<?php echo $button_class; ?>" type="button"><i class="fa fa <?php echo $icon_class; ?>"></i></button>
										</a>
                                       
							
							        	
										
									                                      
													 
									</td>
								</tr>
								<?php
								$counter++;
							
						}
					}else{
							?>
							<tr>
								<td colspan="9" align="center"><h4 class="text-red">No Record Found</h4></td>
							</tr>
							<?php
						}
					
						?>
	                  </table>
	                </div>
                </div><!-- /.box-body -->
				
				<?php if($total_pages > 1){ ?>
					<div class="box-footer clearfix">
					  <ul class="pagination pagination-sm no-margin pull-right">
					   
						<?php
							for($i = 1; $i <= $total_pages; $i++){
								?>
								 <li <?php if((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)){ ?>class="active"<?php } ?>><a href="<?php echo $url ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
								<?php
							}
						?>
						
					  </ul>
					</div>
				<?php } ?>
				
              </div><!-- /.box -->
        </section> 
          
      </div><!-- /.content-wrapper -->    
      <?php require('../includes/footer.php'); ?>
    </div><!-- ./wrapper -->
	
	
	
	<div class="modal" id="addCustomer">
	  <div class="modal-dialog">
		<div class="modal-content" id="addCustomer_tb">
			<form action="<?php echo $page_url; ?>" name="add_customer_frm" id="add_customer_frm" method="post" class="form-horizontal">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add Customer</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-header with-border">
						<div class="col-md-12">
							<h3 class="box-title">Add Customer Panel</h3>
						</div>
					</div><!-- /.box-header -->
					<!-- form start -->
					<div class="box-body">
						
						<div class="form-group">
						  <label for="name" class="col-sm-3 control-label">Name <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<select class="form-control" name="name_title" id="name_title" style="width:15%;float:left;" data-validation="required">
                            	<option value="Mr.">Mr.</option>
                                <option value="Mrs.">Mrs.</option>
                                <option value="Ms.">Ms.</option>
                                <option value="Dr.">Dr.</option>
                                <option value="M/s.">M/s.</option>
                            </select>
                            <input type="text" class="form-control col-sm-8" id="name" name="name" style="width:85%;"  data-validation="required">
						  </div>
						</div>
                        
                        <div class="form-group">
						  <label for="parent_name_title" class="col-sm-3 control-label"><input type="radio" value="S" name="parent_name_title" data-validation="required" data-validation-error-msg="required">S/<input type="radio" value="C" name="parent_name_title" data-validation="required" data-validation-error-msg="required">C/<input type="radio" value="W" name="parent_name_title" data-validation="required" data-validation-error-msg="required">W/<input type="radio" value="D" name="parent_name_title" data-validation="required" data-validation-error-msg="required">D of</label>
						  <div class="col-sm-8">
						  	<select class="form-control" name="parent_name_sub_title" id="parent_name_sub_title" style="width:15%;float:left;" data-validation="required">
                            	<option value="Mr.">Mr.</option>
                                <option value="Mrs.">Mrs.</option>
                                <option value="Ms.">Ms.</option>
                                <option value="Dr.">Dr.</option>
                                <option value="M/s.">M/s.</option>
                            </select>
							<input type="text" class="form-control col-sm-8" id="parent_name" name="parent_name" style="width:85%;" />
						  </div>
						</div>
                        
                        <div class="form-group">
						  <label for="nationality" class="col-sm-3 control-label">Nationality</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="nationality" name="nationality">
						  </div>
						</div>
                        
                        <div class="form-group">
						  <label for="profession" class="col-sm-3 control-label">Profession </label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="profession" name="profession">
						  </div>
						</div>
                        
                        <div class="form-group">
						  <label for="dob" class="col-sm-3 control-label">DOB <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="dob" name="dob" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" data-validation="birthdate" data-validation-format="dd-mm-yyyy">
						  </div>
						</div>
                        
                        <div class="form-group">
						  <label for="nominee_name" class="col-sm-3 control-label">Co-owner Name</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="nominee_name" name="nominee_name">
						  </div>
						</div>

						<div class="form-group">
						  <label for="nominee_relation" class="col-sm-3 control-label">Co-owner Relation</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="nominee_relation" name="nominee_relation">
						  </div>
						</div>
                        
                        <div class="form-group">
						  <label for="residentail_status" class="col-sm-3 control-label">Residential Status</label>
						  <div class="col-sm-8">
							<select class="form-control" name="residentail_status" id="residentail_status">
                            	<option value="">Select Status</option>
                            	<option value="Resident">Resident</option>
                                <option value="Non-Resident">Non-Resident</option>
                                <option value="Foreign National of India Origin">Foreign National of India Origin</option>
                            </select>
                          </div>
						</div>
                        
                        <div class="form-group">
						  <label for="pan_no" class="col-sm-3 control-label">PAN No./Aadhar No.</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="pan_no" name="pan_no">
						  </div>
						</div>
                        
                        
                        <div class="form-group">
						  <label for="address" class="col-sm-3 control-label">Address</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="address" name="address">
						  </div>
						</div>
                        
                        
                        <div class="form-group">
						  <label for="mobile" class="col-sm-3 control-label">Mobile <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="mobile" name="mobile" data-validation="number length" data-validation-length="10-10">
						  </div>
						</div>
                        
                        <div class="form-group">
						  <label for="email" class="col-sm-3 control-label">Email</label>
						  <div class="col-sm-8">
							<input type="email" class="form-control" id="email" name="email" data-validation="email" data-validation-optional="true">
						  </div>
						</div>
                        
                        
                        <div class="form-group">
						  <label for="office_address" class="col-sm-3 control-label">Residence/Office</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="office_address" name="office_address">
						  </div>
						</div>
                       
						<div class="form-group">
						  <label for="project" class="col-sm-3 control-label">Project <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<select class="form-control" id="project" name="project" onChange="getBlocks(this.value);" data-validation="required">
                            	<option value="">Select Project</option>
                                <?php
								$projects = mysqli_query($conn,"select * from kc_projects where status = '1' ");
								while($project = mysqli_fetch_assoc($projects)){ ?>
                                	<option value="<?php echo $project['id']; ?>"><?php echo $project['name']; ?></option>
                                <?php } ?>
                            </select>
						  </div>
						</div>

                        <div class="form-group">
						  <label for="block" class="col-sm-3 control-label">Block <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<select class="form-control" id="block" name="block" onChange="getBlockNumbers(this.value);" data-validation="required">
                            	<option value="">Select Block</option>
                                <?php
								/*$blocks = mysqli_query($conn,"select * from kc_blocks where status = '1' ");
								while($block = mysqli_fetch_assoc($blocks)){ ?>
                                	<option value="<?php echo $block['id']; ?>"><?php echo $block['name']; ?></option>
                                <?php }*/ ?>
                            </select>
						  </div>
						</div>
                        
                        <div class="form-group">
						  <label for="block_number" class="col-sm-3 control-label">Plot Number <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<select class="form-control" id="block_number" name="block_number" onChange="blockNumberChanged(this);" data-validation="required">
                            	<option value="">Select Plot Number</option>
                            </select>
						  </div>
						</div>
                        
                        <div class="form-group">
						  <label for="area" class="col-sm-3 control-label">Total Area <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="area" disabled data-validation="required">
						  </div>
						</div>
                        
                        <div class="form-group">
						  <label for="plc" class="col-sm-3 control-label">PLC</label>
						  <div class="col-sm-8">
							<select class="form-control select2" name="plc[]" id="plc" multiple  style="width: 100%;" readonly>
                            	<?php
								$plcs = mysqli_query($conn,"select * from kc_plc where status = '1' ");
								while($plc = mysqli_fetch_assoc($plcs)){ ?>
                                	<option value="<?php echo $plc['id']; ?>" data-percentage="<?php echo $plc['plc_percentage']; ?>"><?php echo $plc['name']; ?>(<?php echo $plc['plc_percentage']; ?> %)</option>
                                <?php } ?>
                            </select>
						  </div>
						</div>
                        
                        
                        <div class="form-group">
						  <label for="rate" class="col-sm-3 control-label">Rate per sq. ft. <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" name="rate" id="rate" autocomplete="off" data-validation="required" data-validation-allowing="range[1;10000]">
						  </div>
						</div>

                        <div class="form-group">
						  <label for="payable_amount" class="col-sm-3 control-label">Total Plot Value(INR) <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="payable_amount" readonly name="payable_amount" data-validation="number" data-validation-allowing="range[1;1000000000]">
						  </div>
						</div>

						<div class="form-group">
						  <label for="payable_amount" class="col-sm-3 control-label">Payment Type <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<select name="customer_payment_type" id="customer_payment_type" class="form-control" data-validation="required" onChange="customerPaymentEMIPartChanged(this);">
								<option value="" selected="selected">Select Payment Type</option>
								<option value="EMI">EMI Payment</option>
								<option value="Part">Part Payment</option>
								<option value="Full">Full Payment</option>
							</select>
						  </div>
						</div>

                        
                        <div class="form-group">
						  <label for="customer_payment_mode" class="col-sm-3 control-label">Payment Mode</label>
						  <div class="col-sm-8">
							<select class="form-control" id="customer_payment_mode" name="payment_type" onChange="customerPaymentTypeChanged(this);">
                            	<option value="">Select Payment Mode</option>
                                <option value="Cash">Cash</option>
                                <option value="DD">DD</option>
                                <option value="Cheque">Cheque</option>
                                <option value="NEFT">NEFT</option>
                                <option value="RTGS">RTGS</option>
                            </select>
						  </div>
						</div>		
						
                        <div class="form-group customer_cheque_dd" style="display:none;">
						  <label for="customer_bank_name" class="col-sm-3 control-label">Bank Name</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="customer_bank_name" name="bank_name" data-validation="required" data-validation-depends-on="payment_type" data-validation-depends-on-value="DD, Cheque, NEFT, RTGS">
						  </div>
						</div>

                        <div class="form-group customer_cheque_dd" style="display:none;">
						  <label for="customer_cheque_dd_number" class="col-sm-3 control-label"><span class="cheque_dd_label">&nbsp;</span> Number</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="customer_cheque_dd_number" name="cheque_dd_number" data-validation="required" onblur="checkddnumber(this)" data-validation-depends-on="payment_type" data-validation-depends-on-value="DD, Cheque, NEFT, RTGS">
							<span  class="text-danger" style="display:none" >This <span class="cheque_dd_label"> </span> Number is already exists</span>
							<a href="javascript:void(0)"   style="display:none" onclick="openSecondModal(this)">Click here!</a>
							<span  class="text-danger" style="display:none" >for more info.</span>
						  </div>
						</div>
						
						
                        
                        <div class="form-group">
						  <label for="customer_paid_amount" class="col-sm-3 control-label">Paid Amount(INR)</label>
						  <div class="col-sm-8">
						  	<input type="text" class="form-control" id="customer_paid_amount" name="paid_amount" data-validation="number" data-validation-depends-on="payment_type" data-validation-allowing="range[1;100000000]">
						  </div>
						</div>
                        <div class="form-group">
						  <label for="customer_paid_date" class="col-sm-3 control-label"><span class="cheque_dd_label">Paid</span> Date</label>
						  <div class="col-sm-8">
							<input type="date" class="form-control" id="customer_paid_date" name="paid_date"  data-validation-format="dd-mm-yyyy" data-validation-depends-on="payment_type">
						  </div>
						</div>
                        

                        <div class="form-group">
						  <label for="transaction_remarks" class="col-sm-3 control-label">Remarks</label>
						  <div class="col-sm-8">
							<textarea class="form-control" id="transaction_remarks" name="transaction_remarks"></textarea>
						  </div>
						</div>

                        <div class="form-group payment_type_part">
						  <label for="next_due_date" class="col-sm-3 control-label">Next Due Date <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="next_due_date" name="next_due_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask=""  data-validation="" data-validation-format="dd-mm-yyyy">
						  </div>
						</div>

						<div class="form-group payment_type_emi" style="display:none;">
                          <label class="col-sm-3 control-label" for="number_of_installment">Number of Installment <small class="text-danger">*</small></label>
                          <div class="col-sm-8">
                          	<input class="form-control number cut copy paste" maxlength="3" name="number_of_installment" placeholder="Enter Number of Installment" type="text" id="number_of_installment" autocomplete="off" data-validation="required number" data-validation-allowing="range[1;10000]" onkeyup="calculateInstallmentAmount(this);" data-validation-allowing="range[1;200]" />
                          </div>
                        </div>

                        <div class="form-group payment_type_emi" style="display:none;">
                          <label class="col-sm-3 control-label" for="installment_year">Installment Amount <small class="text-danger">*</small></label>
                          <div class="col-sm-8">
	                          <input type="text" class="form-control number cut copy paste" id="installmentAmt" maxlength="6" name="installment_amount" placeholder="Enter Installment Amount" autocomplete="off" data-validation="required number" readonly="readonly" data-validation-allowing="range[1;500000], float" />
	                      </div>
                        </div>

                        <div class="form-group payment_type_emi" style="display:none;">
						  <label for="emi_payment_date" class="col-sm-3 control-label">EMI Payment Date <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="emi_payment_date" name="emi_payment_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask=""  data-validation="date" data-validation-format="dd-mm-yyyy">
						  </div>
						</div>
                        
                        
                        <div class="form-group">
						  <label for="project" class="col-sm-3 control-label">Account Details<span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<select class="form-control" id="project" name="account_id"  data-validation="required">
                            	<option value="">Select Account datils</option>
                                <?php
								$accounts = mysqli_query($conn,"select * from `kc_accounts` where status = '1' ");
								while($account = mysqli_fetch_assoc($accounts)){ ?>
                                	<option value="<?php echo $account['id']; ?>"><?php echo $account['name'].'-'.$account['bank_name'].'-('.$account['account_no'].')-'.$account['branch_name']; ?></option>
                                <?php } ?>
                            </select>
						  </div>
						</div>

                        <div class="form-group">
						  <label for="sales_person" class="col-sm-3 control-label">Sales Person</label>
						  <div class="col-sm-8">
							<select class="form-control" id="sales_person" name="sales_person">
                            	<option value="">Select Employee</option>
                                <?php
								$employees = mysqli_query($conn,"select * from kc_employees where status = '1' ");
								while($employee = mysqli_fetch_assoc($employees)){ ?>
                                	<option value="<?php echo $employee['id']; ?>"><?php echo $employee['name']; ?></option>
                                <?php } ?>
                            </select>
						  </div>
						</div>

						<div class="form-group">
						  <label for="sales_person" class="col-sm-3 control-label">Associate<span class="text-danger">*</span></label>
						  <div class="col-sm-8">
						  		<input type="text" class="form-control associate-autocomplete" data-for-id="associate" placeholder="Name or Code or Mobile" data-validation="required">
								<input type="hidden" name="associate_id[]" id="associate">
								<?php /*<select class="form-control" id="associate" name="associate">
	                            	<option value="">Select Associate</option>
	                                <?php
									$associates = mysqli_query($conn,"select * from kc_associates where status = '1' ");
									while($associate = mysqli_fetch_assoc($associates)){ ?>
	                                	<option value="<?php echo $associate['id']; ?>"><?php echo $associate['name']; ?></option>
	                                <?php } ?>
	                            </select>*/ ?>
						  </div>
						</div>
						<div class="associate_list"></div>
						<div class="form-group">
						  <label for="sales_person" class="col-sm-3 control-label">Associate Percentage (%)</label>
						  <div class="col-sm-8">
						  	<input type="text" name="associate_percentage[]" id="associate_percentage" data-validation="number" data-validation-allowing="range[0;50],float" data-validation-depends-on="associate" class="form-control" maxlength="5" />
						  </div>
						</div>

						<div class="form-group">
						  <label for="send_message" class="col-sm-3 control-label">Send Message</label>
						  <div class="col-sm-8">
						  	<input type="checkbox" name="send_message" id="send_message" class="form-control" />
						  </div>
						</div>
                        
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="save">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
    
    
    <div class="modal" id="addBlock">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="<?php echo $page_url; ?>" name="add_block_frm" id="add_block_frm" method="post" class="form-horizontal dropzone" onSubmit="return confirm('Are you sure All Details are correctly Filled?');">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add Block</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-header with-border">
						<div class="col-md-12">
							<h3 class="box-title">Add Block Panel</h3>
						</div>
					</div><!-- /.box-header -->
					<!-- form start -->
					<div class="box-body">
						
						<div class="form-group">
						  <label for="ab_project" class="col-sm-3 control-label">Project <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<select class="form-control" id="ab_project" name="ab_project" onChange="ab_getBlocks(this.value);" data-validation="required">
                            	<option value="">Select Project</option>
                                <?php
								$projects = mysqli_query($conn,"select * from kc_projects where status = '1' ");
								while($project = mysqli_fetch_assoc($projects)){ ?>
                                	<option value="<?php echo $project['id']; ?>"><?php echo $project['name']; ?></option>
                                <?php } ?>
                            </select>
						  </div>
						</div>


						<div class="form-group">
						  <label for="ab_customer" class="col-sm-3 control-label">Block <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<input type="hidden" name="ab_customer" id="ab_customer">
                            <select class="form-control" data-validation="required" id="ab_block" name="ab_block" onChange="ab_getBlockNumbers(this.value);">
                            	<option value="">Select Block</option>
                                <?php
								/*$blocks = mysqli_query($conn,"select * from kc_blocks where status = '1' ");
								while($block = mysqli_fetch_assoc($blocks)){ ?>
                                	<option value="<?php echo $block['id']; ?>"><?php echo $block['name']; ?></option>
                                <?php }*/ ?>
                            </select>
						  </div>
						</div>
                        
                        <div class="form-group">
						  <label for="ab_block_number" class="col-sm-3 control-label">Plot Number <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<select class="form-control" data-validation="required" id="ab_block_number" name="ab_block_number" onChange="ab_blockNumberChanged(this);">
                            	<option value="">Select Plot Number</option>
                            </select>
						  </div>
						</div>
                        
                        <div class="form-group">
						  <label for="ab_area" class="col-sm-3 control-label">Total Area <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="ab_area" disabled>
						  </div>
						</div>
                        
                        <div class="form-group">
						  <label for="ab_plc" class="col-sm-3 control-label">PLC</label>
						  <div class="col-sm-8">
							<select class="form-control select2" name="ab_plc[]" id="ab_plc" multiple  style="width: 100%;" readonly>
                            	<?php
								$plcs = mysqli_query($conn,"select * from kc_plc where status = '1' ");
								while($plc = mysqli_fetch_assoc($plcs)){ ?>
                                	<option value="<?php echo $plc['id']; ?>" data-percentage="<?php echo $plc['plc_percentage']; ?>"><?php echo $plc['name']; ?>(<?php echo $plc['plc_percentage']; ?> %)</option>
                                <?php } ?>
                            </select>
						  </div>
						</div>
                        
                        
                        <div class="form-group">
						  <label for="ab_rate" class="col-sm-3 control-label">Rate per sq. ft. <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" data-validation="required number" name="ab_rate" id="ab_rate" autocomplete="off">
						  </div>
						</div>
                        
                        
                        
                        <div class="form-group">
						  <label for="ab_payable_amount" class="col-sm-3 control-label">Total Plot Value(INR) <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" data-validation="required number" id="ab_payable_amount" readonly name="ab_payable_amount">
						  </div>
						</div>
                        
						<div class="form-group">
						  <label for="ab_customer_payment_type" class="col-sm-3 control-label">Payment Type <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<select name="ab_customer_payment_type" id="ab_customer_payment_type" class="form-control" data-validation="required" onChange="ab_customerPaymentEMIPartChanged(this);">
								<option value="" selected="selected">Select Payment Type</option>
								<option value="EMI">EMI Payment</option>
								<option value="Part">Part Payment</option>
								<option value="Full">Part Payment</option>
							</select>
						  </div>
						</div>



                        <div class="form-group">
						  <label for="ab_customer_payment_mode" class="col-sm-3 control-label">Payment Mode</label>
						  <div class="col-sm-8">
							<select class="form-control" id="ab_customer_payment_mode" name="ab_payment_type" onChange="ab_customerPaymentTypeChanged(this);">
                            	<option value="">Select Payment Mode</option>
                                <option value="Cash">Cash</option>
                                <option value="DD">DD</option>
                                <option value="Cheque">Cheque</option>
                                <option value="NEFT">NEFT</option>
                                <option value="RTGS">RTGS</option>
                            </select>
						  </div>
						</div>
						
                        <div class="form-group ab_customer_cheque_dd" style="display:none;">
						  <label for="ab_customer_bank_name" class="col-sm-3 control-label">Bank Name</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="ab_customer_bank_name" name="ab_bank_name">
						  </div>
						</div>
                        <div class="form-group ab_customer_cheque_dd" style="display:none;">
						  <label for="ab_customer_cheque_dd_number" class="col-sm-3 control-label"><span class="cheque_dd_label">&nbsp;</span> Number</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="ab_customer_cheque_dd_number" name="ab_cheque_dd_number">
						  </div>
						</div>
                        
                        <div class="form-group">
						  <label for="ab_customer_paid_amount" class="col-sm-3 control-label">Paid Amount(INR)</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="ab_customer_paid_amount" name="ab_paid_amount" data-validation="number" data-validation-depends-on="ab_payment_type" data-validation-allowing="range[1;100000000]">
						  </div>
						</div>
                        <div class="form-group">
						  <label for="ab_customer_paid_date" class="col-sm-3 control-label"><span class="cheque_dd_label">Paid</span> Date</label>
						  <div class="col-sm-8">
							<input type="date" class="form-control" id="ab_customer_paid_date" name="ab_paid_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" data-validation="date" data-validation-format="dd-mm-yyyy" data-validation-depends-on="ab_payment_type">
						  </div>
						</div>

						<div class="form-group">
						  <label for="transaction_remarks" class="col-sm-3 control-label">Remarks</label>
						  <div class="col-sm-8">
							<textarea class="form-control" id="ab_transaction_remarks" name="ab_transaction_remarks"></textarea>
						  </div>
						</div>
                        
                        <div class="form-group ab_payment_type_part">
						  <label for="ab_next_due_date" class="col-sm-3 control-label">Next Due Date <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="ab_next_due_date" data-validation="required" name="ab_next_due_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" >
						  </div>
						</div>


                        <div class="form-group ab_payment_type_emi" style="display:none;">
                          <label class="col-sm-3 control-label" for="ab_number_of_installment">Number of Installment <small class="text-danger">*</small></label>
                          <div class="col-sm-8">
                          	<input class="form-control number cut copy paste" maxlength="3" name="ab_number_of_installment" placeholder="Enter Number of Installment" type="text" id="ab_number_of_installment" autocomplete="off" data-validation="required number" data-validation-allowing="range[1;200]" onkeyup="ab_calculateInstallmentAmount(this);" />
                          </div>
                        </div>

                        <div class="form-group ab_payment_type_emi" style="display:none;">
                          <label class="col-sm-3 control-label" for="ab_installment_amount">Installment Amount <small class="text-danger">*</small></label>
                          <div class="col-sm-8">
	                          <input type="text" class="form-control number cut copy paste" id="ab_installmentAmt" maxlength="6" name="ab_installment_amount" placeholder="Enter Installment Amount" autocomplete="off" data-validation="required number" readonly="readonly" data-validation-allowing="range[1;500000], float" />
	                      </div>
                        </div>

                        <div class="form-group ab_payment_type_emi" style="display:none;">
						  <label for="ab_emi_payment_date" class="col-sm-3 control-label">EMI Payment Date <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" data-validation="required" id="ab_emi_payment_date" name="ab_emi_payment_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask=""  data-validation="date" data-validation-format="dd-mm-yyyy">
						  </div>
						</div>
                        
                        <div class="form-group">
						  <label for="ab_sales_person" class="col-sm-3 control-label">Sales Person</label>
						  <div class="col-sm-8">
							<select class="form-control" id="ab_sales_person" name="ab_sales_person">
                            	<option value="">Select Employee</option>
                                <?php
								$employees = mysqli_query($conn,"select * from kc_employees where status = '1' ");
								while($employee = mysqli_fetch_assoc($employees)){ ?>
                                	<option value="<?php echo $employee['id']; ?>"><?php echo $employee['name']; ?></option>
                                <?php } ?>
                            </select>
						  </div>
						</div>

						<div class="form-group">
						  <label for="ab_associate" class="col-sm-3 control-label">Associate <small class="text-danger">*</small></label>
						  <div class="col-sm-8">
						  	<input type="text" class="form-control associate-autocomplete" data-for-id="ab_associate" placeholder="Name or Code or Mobile" data-validation="required">
							<input type="hidden" name="associate" id="ab_associate">
							<?php /*<select class="form-control" id="ab_associate" name="associate">
                            	<option value="">Select Associate</option>
                                <?php
								$associates = mysqli_query($conn,"select * from kc_associates where status = '1' ");
								while($associate = mysqli_fetch_assoc($associates)){ ?>
                                	<option value="<?php echo $associate['id']; ?>"><?php echo $associate['name']; ?></option>
                                <?php } ?>
                            </select>*/ ?>
						  </div>
						</div>

						<div class="form-group">
						  <label for="ab_associate_percentage" class="col-sm-3 control-label">Associate Percentage (%)</label>
						  <div class="col-sm-8">
						  	<input type="text" name="associate_percentage" id="ab_associate_percentage" data-validation="number" data-validation-allowing="range[0;50],float" data-validation-depends-on="associate" class="form-control" maxlength="5" />
						  </div>
						</div>

						<div class="form-group">
						  <label for="ab_send_message" class="col-sm-3 control-label">Send Message</label>
						  <div class="col-sm-8">
						  	<input type="checkbox" name="ab_send_message" id="ab_send_message" class="form-control" />
						  </div>
						</div>
                        
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="ab_save" name="ab_save">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
    
	
	
	<div class="modal" id="addTransaction">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="<?php echo $page_url; ?>" name="add_transaction_frm" id="add_transaction_frm" method="post" class="form-horizontal dropzone" onSubmit="return confirm('Are you sure All Details are correctly Filled?');">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add Transaction</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-header with-border">
						<div class="col-md-12">
							<h3 class="box-title">Add Transaction Panel</h3>
						</div>
					</div><!-- /.box-header -->
					<!-- form start -->
					<div class="box-body">
						<div class="form-group">
						  <label for="payment_type" class="col-sm-3 control-label">Payment Mode</label>
						  <div class="col-sm-8">
							<select class="form-control" id="payment_type" name="payment_type" onChange="paymentTypeChanged(this);">
                            	<option value="">Select Payment Mode</option>
                                <option value="Cash">Cash</option>
                                <option value="DD">DD</option>
                                <option value="Cheque">Cheque</option>
                                <option value="NEFT">NEFT</option>
                                <option value="RTGS">RTGS</option>
                            </select>
						  </div>
						</div>
						
                        <div class="form-group cheque_dd" style="display:none;">
						  <label for="excel_file" class="col-sm-3 control-label">Bank Name</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="bank_name" name="bank_name">
						  </div>
						</div>
                        <div class="form-group cheque_dd" style="display:none;">
						  <label for="excel_file" class="col-sm-3 control-label"><span class="cheque_dd_label">&nbsp;</span> Number</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="cheque_dd_number" name="cheque_dd_number" onblur="checkddnumber(this)">
							<span  class="text-danger" style="display:none" >This <span class="cheque_dd_label"> </span> Number is already exists</span>
							<a href="javascript:void(0)"   style="display:none" onclick="openSecondModal(this)">Click here!</a>
							<span  class="text-danger" style="display:none" >for more info.</span>
						  </div>
						</div>
                        
                        <div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">Paid Amount(INR)</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="paid_amount" name="paid_amount">
						  </div>	
						</div>
                        <div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label"><span class="cheque_dd_label">Paid</span> Date</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="paid_date" name="paid_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="">
						  </div>
						</div>
						<div class="form-group">
						  <label for="transaction_remarks" class="col-sm-3 control-label">Remarks</label>
						  <div class="col-sm-8">
							<textarea class="form-control" id="at_transaction_remarks" name="transaction_remarks"></textarea>
						  </div>
						</div>
                        <div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">Next Due Date (EMI)</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="at_next_due_date" name="next_due_date" data-inputmask="'alias': 'dd-mm-yyyy'" >
                            <input type="hidden" name="customer_id" id="customer_id">
                            <input type="hidden" name="block_id" id="block_id">
                            <input type="hidden" name="block_number_id" id="block_number_id">
						  </div>
						</div>
						<div class="form-group">
						  <label for="project" class="col-sm-3 control-label">Account Details<span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<select class="form-control" id="project" name="account_id"  data-validation="required">
                            	<option value="">Select Account datils</option>
                                <?php
								$accounts = mysqli_query($conn,"select * from `kc_accounts` where status = '1' ");
								while($account = mysqli_fetch_assoc($accounts)){ ?>
                                	<option value="<?php echo $account['id']; ?>"><?php echo $account['name'].'-'.$account['bank_name'].'-('.$account['account_no'].')-'.$account['branch_name']; ?></option>
                                <?php } ?>
                            </select>
						  </div>
						</div>			
						<div class="form-group">
						  <label for="at_send_message" class="col-sm-3 control-label">Send Message</label>
						  <div class="col-sm-8">
						  	<input type="checkbox" name="at_send_message" id="at_send_message" class="form-control" />
						  </div>
						</div>
					</div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="addTransaction">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

    
    
    <div class="modal" id="viewTransaction">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="<?php echo $page_url; ?>" name="view_transaction_frm" id="view_transaction_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">All Transactions</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-body">
						
                        <table class="table table-bordered" id="view-transaction-container">
                        </table>
                         
					</div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	<div class="modal" id="viewArchivedTransaction">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="<?php echo $page_url; ?>" name="view_archived_transaction_frm" id="view_archived_transaction_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">All Transactions</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-body">
						
                        <table class="table table-bordered" id="view-archived-transaction-container">
                        </table>
                        
                        
                        
                        
						
					</div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
    
    
    <div class="modal" id="editInformation">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="<?php echo $page_url; ?>" name="edit_frm" id="edit_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Edit Customer Information</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-body" id="edit-information-container">
					
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="editInformation">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	<div class="modal" id="viewInformation">
	  <div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Customer Information</h4>
			</div>
			<div class="modal-body">
				<div class="box box-info">
					<div class="box-body" id="view-information-container">
					
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
			</div>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->


	<div class="modal" id="changeEmployee">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="<?php echo $page_url; ?>" name="edit_frm" id="edit_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Change Employee</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-body" id="change-employee-container">
					
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="changeEmployee">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	<div class="modal" id="changeAssociate">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="<?php echo $page_url; ?>" name="edit_frm" id="edit_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Change Associate</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-body" id="change-associate-container">
					
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="changeAssociate">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->


	<div class="modal" id="changeBlockNumber">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="<?php echo $page_url; ?>" name="edit_frm" id="edit_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Change Plot Number</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-body" id="change-blockno-container">
					
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="changeBlockNumber">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	

	<div class="modal" id="addLatePayment">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="<?php echo $page_url; ?>" name="add_late_payment_frm" id="add_late_payment_frm" method="post" class="form-horizontal dropzone" onSubmit="return confirm('Are you sure All Details are correctly Filled?');">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add Late Payment</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-header with-border">
						<div class="col-md-12">
							<h3 class="box-title">Add Late Payment Panel</h3>
						</div>
					</div><!-- /.box-header -->
					<!-- form start -->
					<div class="box-body">
						
                        
                        <div class="form-group">
						  <label for="late_amount" class="col-sm-3 control-label">Amount(INR)</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="late_amount" name="late_amount">
						  </div>
						</div>
                        <div class="form-group">
						  <label for="late_remarks" class="col-sm-3 control-label">Remarks</label>
						  <div class="col-sm-8">
							<textarea class="form-control" id="late_remarks" name="late_remarks"></textarea>
							<input type="hidden" name="late_customer_id" id="late_customer_id">
                            <input type="hidden" name="late_block_id" id="late_block_id">
                            <input type="hidden" name="late_block_number_id" id="late_block_number_id">
                            <input type="hidden" name="late_next_due_date" id="late_next_due_date">
						  </div>
						</div>
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="addLatePaymentBtn" name="addLatePayment">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	<div class="modal" id="cancelTransaction">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="<?php echo $page_url; ?>" name="add_late_payment_frm" id="add_late_payment_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Cancel Transaction</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-header with-border">
						<div class="col-md-12">
							<h3 class="box-title">Cancel Transaction Panel</h3>
						</div>
					</div><!-- /.box-header -->
					<!-- form start -->
					<div class="box-body">
						<div class="form-group">
						  <label for="cancel_remarks" class="col-sm-3 control-label">Remarks</label>
						  <div class="col-sm-8">
							<textarea class="form-control" id="cancel_remarks" name="cancel_remarks"></textarea>
							<input type="hidden" name="cancel_transaction_id" id="cancel_transaction_id">
							<input type="hidden" name="cancel_customer_id" id="cancel_customer_id">
                            <input type="hidden" name="cancel_block_id" id="cancel_block_id">
                            <input type="hidden" name="cancel_block_number_id" id="cancel_block_number_id">
                            <!-- <input type="hidden" name="emi_id" id="emi_id"> -->
						  </div>
						</div>
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="button" id="cancelTransactionBack" class="btn btn-info">Back</button>
				<button type="submit" class="btn btn-primary" id="cancelTransactionBtn" name="cancelTransaction">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->


	<div class="modal" id="registryModal">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="<?php echo $page_url; ?>" name="registry_frm" id="registry_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Registry Information</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-header with-border">
						<div class="col-md-12">
							<h3 class="box-title">Registry Panel</h3>
						</div>
					</div><!-- /.box-header -->
					<!-- form start -->
					<div class="box-body">
						<div class="form-group">
						  <label for="registry_date" class="col-sm-3 control-label">Registry Date</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="registry_date" name="registry_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" data-validation="date" data-validation-format="dd-mm-yyyy">
							<input type="hidden" name="registry_customer_id" id="registry_customer_id">
                            <input type="hidden" name="registry_block_id" id="registry_block_id">
                            <input type="hidden" name="registry_block_number_id" id="registry_block_number_id">
						  </div>
						</div>

						<div class="form-group">
						  <label for="registry_by" class="col-sm-3 control-label">Registry By</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="registry_by" name="registry_by" data-validation="required">
						  </div>
						</div>

						<div class="form-group">
						  <label for="khasra_no" class="col-sm-3 control-label">Khasra Number</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="khasra_no" name="khasra_no" data-validation="required">
						  </div>
						</div>

						<div class="form-group">
						  <label for="maliyat_value" class="col-sm-3 control-label">Maliyat Value</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="maliyat_value" name="maliyat_value" data-validation="required">
						  </div>
						</div>

						<div class="form-group">
						  <label for="sale_value" class="col-sm-3 control-label">Sale Value</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="sale_value" name="sale_value" data-validation="required">
						  </div>
						</div>
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="registrySubmit" name="registrySubmit">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
    
    <div class="modal" id="editRegistryModal">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="" name="edit_registry_frm" id="edit_registry_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Edit Registry Information</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<!-- <div class="box-header with-border">
						<div class="col-md-12">
							<h3 class="box-title">Registry Panel</h3>
						</div>
					</div>/.box-header -->
					<!-- form start -->
					<div class="box-body" id="edit-registry-container">
						
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="editRegistry" name="editRegistry">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	<div class="modal" id="addRevisedRate">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="" name="revised_rate_frm" id="revised_rate_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add Revised Rate</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-body" id="add-revisedrate-container">
					
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" name="revisedRate">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	<div class="modal" id="applyDiscount">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="" name="apply_discount_frm" id="apply_discount_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Discount Rate</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-body" id="apply-discount-container">
					
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" name="applyDiscount" id="applyDiscountButton">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	<div class="modal" id="addExtraCharges">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="" name="apply_discount_frm" id="apply_discount_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add Extra Charges</h4>
			  </div>
			  <div class="modal-body">
				<div class="box-body" id="addExtraCharges-container">
				
                </div><!-- /.box-body -->
					
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" name="addExtraCharges">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->


	<div class="modal" id="cheque_info">
	  <div class="modal-dialog">
		<div class="modal-content">
		    <div class="modal-header">
				<button type="button" class="close" onclick="openmodel(this)" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Cheque Info</h4>
			</div>
			<div class="modal-body">
				<div class="box box-info">
					<div class="box-body">
						
                        <table class="table table-bordered" id="view-cheque-info">
                        </table>
                         
					</div><!-- /.box-body -->
					
				</div><!-- /.box -->
			</div>
			<div class="modal-footer">
			   <input type="hidden" id="custId" name="custId" value="" >
				<button type="button" class="btn btn-default pull-left" onclick="openmodel(this)" >Back</button>
			</div>		
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	<?php require('../includes/common-js.php'); ?>
	
    <script type="text/javascript">
    $(function(){
    	$("#cancelTransactionBack").click(function(){
    		$("#cancelTransaction").modal('hide');
    		getTransactions($("#cancel_customer_id").val(),$("#cancel_block_id").val(),$("#emi_id").val(),$("#cancel_block_number_id").val());
    	});
    });
    function cancelTransaction(transaction,customer,block,block_number){
    	if(confirm('Are you sure you want to cancel this transaction?')){
    		$("#cancel_transaction_id").val(transaction);
    		$("#cancel_customer_id").val(customer);
			$("#cancel_block_id").val(block);
			$("#cancel_block_number_id").val(block_number);
    		$("#viewTransaction").modal('hide');
    		$("#cancelTransaction").modal('show');
    	}
    }

    function registry(customer,block,block_number){
    	$("#registry_customer_id").val(customer);
		$("#registry_block_id").val(block);
		$("#registry_block_number_id").val(block_number);
		$("#registryModal").modal('show');
    }

	    //   <?php if(isset($_GET['focus'])){ ?>
        //         $("#row_<?php echo $_GET['focus']; ?>").focus();
        //     <?php } ?>

	function edit_registry(customer,block,block_number){
		// console.log(customer,block);
		// $("#registry_customer_id").val(customer);
		// $("#registry_block_id").val(block);
		// $("#registry_block_number_id").val(block_number);
		$.ajax({
			url: '../dynamic/getEditRegistryinfo.php',
			type:'post',
			data:{customer:customer,block:block,block_number:block_number},
			success: function(resp){
				$("#edit-registry-container").html(resp);
    		  $("#editRegistryModal").modal('show');
			}
	   });
    }

	function addLatePayment(customer,block,block_number,next_due_date){
		$("#late_customer_id").val(customer);
		$("#late_block_id").val(block);
		$("#late_block_number_id").val(block_number);
		$("#late_next_due_date").val(next_due_date);
		$("#addLatePayment").modal('show');
	}

	function addTransaction(customer,block,block_number,emi_date,emi){
		// alert(emi_date);
	
		$("#customer_id").val(customer);
		$("#block_id").val(block);
		$("#block_number_id").val(block_number);
		if(emi_date != false && emi_date!='' ){
			$("#at_next_due_date").val(emi_date).attr('readonly','readonly');
		}else if(emi_date==''){
			$("#at_next_due_date").val("No-Next-EMI").attr('readonly','readonly');
		}else{
			$("#at_next_due_date").val('').removeAttr('readonly','readonly');
		}
		if(!emi){
			$("#at_next_due_date").val('').removeAttr('readonly','readonly');
		}
		$("#addTransaction").modal('show');
	}
	
	function getTransactions(customer,block,block_number){
		$.ajax({
			url: '../dynamic/getTransactions.php',
			type:'post',
			data:{customer:customer,block:block,block_number:block_number},
			success: function(resp){
				$("#view-transaction-container").html(resp);
				$("#viewTransaction").modal('show');
			}
		});
	}

	function getArchivedTransactions(archive_customer_block_id){
		$.ajax({
			url: '../dynamic/getArchivedTransactions.php',
			type:'post',
			data:{archive_customer_block_id:archive_customer_block_id},
			success: function(resp){
				$("#view-archived-transaction-container").html(resp);
				$("#viewArchivedTransaction").modal('show');
			}
		});
	}
	
	function getBlocks(project){
		$("#area").val('');
		$("#rate").val('');
		$("#plc").val('');
		$("#block_number").val('');
		$.ajax({
			url: '../dynamic/getBlocks.php',
			type:'post',
			data:{project:project},
			success: function(resp){
				$("#block").html(resp);
			}
		});
	}

	function ab_getBlocks(project){
		$("#ab_area").val('');
		$("#ab_rate").val('');
		$("#ab_plc").val('');
		$("#ab_block_number").val('');
		$.ajax({
			url: '../dynamic/getBlocks.php',
			type:'post',
			data:{project:project},
			success: function(resp){
				$("#ab_block").html(resp);
			}
		});
	}

	function search_getBlocks(project){
		$("#search_block_no").val('');
		$.ajax({
			url: '../dynamic/getBlocks.php',
			type:'post',
			data:{project:project},
			success: function(resp){
				$("#search_block").html(resp);
			}
		});
	}

	function getBlockNumbers(block){
		$("#area").val('');
		$("#rate").val('');
		$("#plc").val('');
		$("#payable_amount").val('');
		$.ajax({
			url: '../dynamic/getBlockNumbers.php',
			type:'post',
			data:{block:block},
			success: function(resp){
				$("#block_number").html(resp);
			}
		});
	}
	
	function addBlock(customer){
		$("#ab_customer").val(customer);
		$("#addBlock").modal('show');
	}
	
	function ab_getBlockNumbers(block){
		$("#ab_area").val('');
		$("#ab_rate").val('');
		$("#ab_plc").val('');
		$("#ab_payable_amount").val('');
		$.ajax({
			url: '../dynamic/getBlockNumbers.php',
			type:'post',
			data:{block:block},
			success: function(resp){
				$("#ab_block_number").html(resp);
			}
		});
	}

	function search_getBlockNumbers(block){
		$.ajax({
			url: '../dynamic/getBlockNumbers.php',
			type:'post',
			data:{block:block, type: 'booked'},
			success: function(resp){
				if(resp.trim() != ''){
					$("#search_block_no").html(resp);
				}else{
					$("#search_block_no").html('<option value="">Select Plot Number</option>');
				}
			}
		});
	}

	function changed_getBlockNumbers(block,block_number,original_block){
		$.ajax({
			url: '../dynamic/getMatchingBlockNumbers.php',
			type:'post',
			data:{block:block, block_number: block_number, type: 'matched',original_block: original_block},
			success: function(resp){
				if(resp.trim() != ''){
					$("#changed_block_no").html(resp);
				}else{
					$("#changed_block_no").html('<option value="">Select Plot Number</option>');
				}
			}
		});
	}


	function customerPaymentEMIPartChanged(elem){
		if($(elem).val() == "EMI"){
			$("#customer_payment_mode").attr('data-validation','required');
			$(".payment_type_emi").show();
			$(".payment_type_part").hide();
			
		}else if($(elem).val()=='Full'){
				$(".payment_type_emi").hide();
				$(".payment_type_part").hide();
		}else{
			$("#customer_payment_mode").removeAttr('data-validation');
			$(".payment_type_emi").hide();
			$(".payment_type_part").show();
		}
	}
	function ab_customerPaymentEMIPartChanged(elem){
		if($(elem).val() == "EMI"){
			$("#ab_customer_payment_mode").attr('data-validation','required');
			$(".ab_payment_type_emi").show();
			$(".ab_payment_type_part").hide();

		}else if($(elem).val()=='Full'){
			$(".ab_payment_type_emi").hide();
			$(".ab_payment_type_part").hide();
		}else{
			$("#ab_customer_payment_mode").removeAttr('data-validation');
			$(".ab_payment_type_emi").hide();
			$(".ab_payment_type_part").show();
		}
	}

	function customerPaymentTypeChanged(elem){
		if($(elem).val() == "Cheque" || $(elem).val() == "DD" || $(elem).val() == "NEFT" || $(elem).val() == "RTGS"){
			$(".customer_cheque_dd").show();
			$(elem).parent().parent().parent().find('.cheque_dd_label').text($(elem).val());
		}else{
			$(".customer_cheque_dd").hide();
			$(elem).parent().parent().parent().find('.cheque_dd_label').text('Paid');
		}
	}
	function ab_customerPaymentTypeChanged(elem){
		if($(elem).val() == "Cheque" || $(elem).val() == "DD" || $(elem).val() == "NEFT" || $(elem).val() == "RTGS"){
			$(".ab_customer_cheque_dd").show();
			$(elem).parent().parent().parent().find('.ab_cheque_dd_label').text($(elem).val());
		}else{
			$(".ab_customer_cheque_dd").hide();
			$(elem).parent().parent().parent().find('.ab_cheque_dd_label').text('Paid');
		}
	}
	function paymentTypeChanged(elem){
		if($(elem).val() == "Cheque" || $(elem).val() == "DD" || $(elem).val() == "NEFT" || $(elem).val() == "RTGS"){
			$(".cheque_dd").show();
			$(elem).parent().parent().parent().find('.cheque_dd_label').text($(elem).val());
		}else{
			$(".cheque_dd").hide();
			$(elem).parent().parent().parent().find('.cheque_dd_label').text('Paid');
		}
	}
	function calculateInstallmentAmount(elem){
		var number_of_installment = $(elem).val();
		var payable_amount = parseFloat($("#payable_amount").val());
		if(parseFloat($("#customer_paid_amount").val()) > 0){
			payable_amount -= parseFloat($("#customer_paid_amount").val());
		}
		if(payable_amount>0 && number_of_installment>0){
			var installment_amount = (payable_amount/number_of_installment);
			$('#installmentAmt').val(installment_amount.toFixed(2));
		}else{
			$('#installmentAmt').val();
		}
	}

	function ab_calculateInstallmentAmount(elem){
		var number_of_installment = $(elem).val();
		var payable_amount = parseFloat($("#ab_payable_amount").val());
		if(parseFloat($("#ab_customer_paid_amount").val()) > 0){
			payable_amount -= parseFloat($("#ab_customer_paid_amount").val());
		}
		if(payable_amount>0 && number_of_installment>0){
			var installment_amount = (payable_amount/number_of_installment);
			$('#ab_installmentAmt').val(installment_amount.toFixed(2));
		}else{
			$('#ab_installmentAmt').val();
		}
	}
	
	function editInformation(customer){
		$.ajax({
			url: '../dynamic/getCustomerInformation.php',
			type:'post',
			data:{customer:customer},
			success: function(resp){
				$("#edit-information-container").html(resp);
				$("[data-mask]").inputmask();
				$('input').iCheck({
					  checkboxClass: 'icheckbox_square-blue',
					  radioClass: 'iradio_square-blue',
					  click: function(){
						}
					});
				$("#editInformation").modal('show');
			}
		});
	}

	function changeEmployee(customer,block,block_number){
		$.ajax({
			url: '../dynamic/changeEmployee.php',
			type:'post',
			data:{customer:customer, block:block, block_number:block_number},
			success: function(resp){
				$("#change-employee-container").html(resp);
				$("#changeEmployee").modal('show');
			}
		});
	}

	function changeAssociate(customer,block,block_number){
		$.ajax({
			url: '../dynamic/changeAssociate.php',
			type:'post',
			data:{customer:customer, block:block, block_number:block_number},
			success: function(resp){
				$("#change-associate-container").html(resp);
				$("#changeAssociate").modal('show');
			}
		});
	}

	function changeBlockNumber(customer,block,block_number){
		$.ajax({
			url: '../dynamic/changeBlockNumber.php',
			type:'post',
			data:{customer:customer, block:block, block_number:block_number},
			success: function(resp){
				$("#change-blockno-container").html(resp);
				$("#change-blockno-container .select2").select2();
				$("#changeBlockNumber").modal('show');
			}
		});
	}
	function viewInformation(customer){
		$.ajax({
			url: '../dynamic/viewCustomer.php',
			type:'post',
			data:{customer:customer},
			success: function(resp){
				$("#view-information-container").html(resp);
				$("#viewInformation").modal('show');
			}
		});
	}
	function iCheckClicked(elem){
		 var for_attr = $(elem).attr('for');
	}
	function blockNumberChanged(elem){
		var block_number = $(elem).val();
		$("#area").val('');
		$("#plc").select2("val", []);
		if(block_number != '' && !isNaN(block_number)){
			$.ajax({
				url: '../dynamic/getBlockNumberDetailsJson.php',
				type:'post',
				data:{block_number:block_number},
				dataType:"json",
				success: function(resp){
					//alert(resp.area);
					$("#area").val(resp.area);
					if(jQuery.isArray(resp.plc)){
						$("#plc").select2("val", resp.plc);
					}
				}
			});
		}
	}

	function getPLC(elem){
		var block_number = $(elem).val();
		$("#changed_plc").select2("val", []);
		if(block_number != '' && !isNaN(block_number)){
			$.ajax({
				url: '../dynamic/getBlockNumberDetailsJson.php',
				type:'post',
				data:{block_number:block_number},
				dataType:"json",
				success: function(resp){
					if(jQuery.isArray(resp.plc)){
						$("#changed_plc").select2("val", resp.plc);
					}
				}
			});
		}
	}
	
	function ab_blockNumberChanged(elem){
		var block_number = $(elem).val();
		$("#ab_area").val('');
		$("#ab_plc").select2("val", []);
		if(block_number != '' && !isNaN(block_number)){
			$.ajax({
				url: '../dynamic/getBlockNumberDetailsJson.php',
				type:'post',
				data:{block_number:block_number},
				dataType:"json",
				success: function(resp){
					//alert(resp.area);
					$("#ab_area").val(resp.area);
					if(jQuery.isArray(resp.plc)){
						$("#ab_plc").select2("val", resp.plc);
					}
				}
			});
		}
	}
	
	function calculateAmount(){
		if(!isNaN($("#rate").val())){
			var rate_per_sq = $("#rate").val();
			var total_area = $("#area").val();
			var total_plot_value = parseFloat(rate_per_sq)*parseFloat(total_area);
			
			var applied_plc = [];
			var plc = $("#plc").val();
			
			$( "#plc option:selected" ).each(function(index, element) {
				if(!isNaN($(this).attr('data-percentage')) && $(this).attr('data-percentage') > 0){
					var applied_perc = $(this).attr('data-percentage');
					applied_plc.push(parseInt((total_plot_value*applied_perc)/100));
				}
			});
			if(jQuery.isArray(applied_plc)){
				$( applied_plc ).each(function(index, amount) {
					total_plot_value += parseInt(amount);
				});
			}
			
			return total_plot_value;
		}
	}
	
	function ab_calculateAmount(){
		if(!isNaN($("#ab_rate").val())){
			var rate_per_sq = $("#ab_rate").val();
			var total_area = $("#ab_area").val();
			var total_plot_value = parseFloat(rate_per_sq)*parseFloat(total_area);
			
			var applied_plc = [];
			var plc = $("#ab_plc").val();
			
			$( "#ab_plc option:selected" ).each(function(index, element) {
				if(!isNaN($(this).attr('data-percentage')) && $(this).attr('data-percentage') > 0){
					var applied_perc = $(this).attr('data-percentage');
					applied_plc.push(parseInt((total_plot_value*applied_perc)/100));
				}
			});
			if(jQuery.isArray(applied_plc)){
				$( applied_plc ).each(function(index, amount) {
					total_plot_value += parseInt(amount);
				});
			}
			
			return total_plot_value;
		}
	}
	function rr_calculateAmount(){
		if(!isNaN($("#rr_revised_rate").val())){
			var rate_per_sq = $("#rr_revised_rate").val();
			var total_area = $("#rr_area").val();
			var total_plot_value = parseFloat(rate_per_sq)*parseFloat(total_area);
			
			var applied_plc = [];
			var plc = $("#rr_plc").val();
			
			$( "#rr_plc option:selected" ).each(function(index, element) {
				if(!isNaN($(this).attr('data-percentage')) && $(this).attr('data-percentage') > 0){
					var applied_perc = $(this).attr('data-percentage');
					applied_plc.push(parseInt((total_plot_value*applied_perc)/100));
				}
			});
			if(jQuery.isArray(applied_plc)){
				$( applied_plc ).each(function(index, amount) {
					total_plot_value += parseInt(amount);
				});
			}
			
			$("#rr_payable_amount").val(total_plot_value);
		}
	}
	function dr_calculateAmount(){
		if(!isNaN($("#dr_discount_rate").val())){
			var rate_per_sq = $("#dr_discount_rate").val();
			var total_area = $("#dr_area").val();
			var total_plot_value = parseFloat(rate_per_sq)*parseFloat(total_area);
			
			var applied_plc = [];
			var plc = $("#dr_plc").val();
			
			$( "#dr_plc option:selected" ).each(function(index, element) {
				if(!isNaN($(this).attr('data-percentage')) && $(this).attr('data-percentage') > 0){
					var applied_perc = $(this).attr('data-percentage');
					applied_plc.push(parseInt((total_plot_value*applied_perc)/100));
				}
			});
			if(jQuery.isArray(applied_plc)){
				$( applied_plc ).each(function(index, amount) {
					total_plot_value += parseInt(amount);
				});
			}
			
			$("#dr_payable_amount").val(total_plot_value);
		}
	}
	function addRevisedRate(customer,block,block_number){
		$.ajax({
			url: '../dynamic/addRevisedRate.php',
			type:'post',
			data:{customer:customer, block:block, block_number:block_number},
			success: function(resp){
				$("#add-revisedrate-container").html(resp);
				$("#add-revisedrate-container .select2").select2();
				$.validate({
	              modules : 'date, security, location, logic'
	            });
	            $('input').iCheck({
				  checkboxClass: 'icheckbox_square-blue',
				  radioClass: 'iradio_square-blue',
				  /*increaseArea: '20%' // optional*/
				  click: function(){
					  //alert('sdfsdf');
				  }
				});
				$("#addRevisedRate").modal('show');
			}
		});
	}
	function applyDiscount(customer,block,block_number){
		$.ajax({
			url: '../dynamic/applyDiscountRate.php',
			type:'post',
			data:{customer:customer, block:block, block_number:block_number},
			success: function(resp){
				$("#apply-discount-container").html(resp);
				$("#apply-discount-container .select2").select2();
				$.validate({
	              modules : 'date, security, location, logic'
	            });
	            $('input').iCheck({
				  checkboxClass: 'icheckbox_square-blue',
				  radioClass: 'iradio_square-blue',
				  click: function(){
					}
				});
				$("#applyDiscount").modal('show');
			}
		});
	}
	$(function(){
		$("#rate").keyup(function(){
			$("#payable_amount").val(calculateAmount());
			calculateInstallmentAmount($("#number_of_installment"));
		});
		$("#plc").change(function(){
			$("#payable_amount").val(calculateAmount());
			calculateInstallmentAmount($("#number_of_installment"));
		});
		
		$("#ab_rate").keyup(function(){
			$("#ab_payable_amount").val(ab_calculateAmount());
			ab_calculateInstallmentAmount($("#ab_number_of_installment"));
		});
		$("#ab_plc").change(function(){
			$("#ab_payable_amount").val(ab_calculateAmount());
			ab_calculateInstallmentAmount($("#ab_number_of_installment"));
		});

		$("#customer_paid_amount").keyup(function(){
			if( parseInt($(this).val()) > parseInt($("#payable_amount").val()) ){
				alert("This value can not be greater than "+$("#payable_amount").val())
				$(this).val('');
			}
			calculateInstallmentAmount($("#number_of_installment"));
		});
		$("#ab_customer_paid_amount").keyup(function(){
			ab_calculateInstallmentAmount($("#ab_number_of_installment"));
		});

		$("#name, #dob").blur(function(){
			checkCustomerExistance();
		});

		/*$("#next_due_date, #ab_next_due_date").blur(function(){
			var date = moment($(this).val(),"DD-MM-YYYY").format("YYYY-MM-DD");
			var minDate = moment().add('days', 3).format("YYYY-MM-DD");
			if(!(date > minDate)){
            	$(this).val('');
            	alert('Next Due Date should be greater than '+minDate.toString("dd-mm-yyyy"));
	        }
		});*/
	});

	$(function(){
		$(document).on("cut copy paste",".do_not_copy_paste",function(e) {
			e.preventDefault();
		});
	});

	$(document).on('keyup','.number' , function() {
      $(this).val($(this).val().replace(/\D/g, ''));
    });

	function checkCustomerExistance(){
		var name = $("#name").val();
		var dob = $("#dob").val();
		if(name != '' && dob != ''){
			$.ajax({
				url: '../dynamic/checkCustomerExistance.php',
				type:'post',
				data:{name:name, dob:dob},
				success: function(resp){
					if(resp.trim() != "0"){
						$("#dob").val('');
						alert('Same Name and DOB Already Exists!');
					}
				}
			});
		}
	}


	function addExtraCharges(customer,block,block_number){
		$.ajax({
			url: '../dynamic/addExtraCharges.php',
			type:'post',
			data:{customer:customer, block:block, block_number:block_number},
			success: function(resp){
				$("#addExtraCharges-container").html(resp);
				$("#addExtraCharges").modal('show');
			}
		});
	}

	$(document).ready(function(){
		$('#applyDiscountButton').click(function(){
			$("#applyDiscountButton").hide();
		});
	});

	function checkddnumber(elem){
	
		var dd_number = $("#customer_cheque_dd_number").val();
		var cheque_dd_number = $("#cheque_dd_number").val();
		var data =cheque_dd_number?cheque_dd_number:dd_number
			if(data !='' ){
				$.ajax({
					url: 'customers.php',
					type:'post',
					data:{dd_number: data}, 
					success: function(resp){
						obj = JSON.parse(resp)
						if(obj.exists){
							 $(elem).siblings('span').css('display','');
							 $(elem).siblings('a').css('display',''); 
						}				
					}
				});
			}
	}

	function openSecondModal(elem){ 
		var val = $(elem).parents('.modal').modal('hide').attr('id');
		 $("#custId").val(val);

		 var dd_number = $("#customer_cheque_dd_number").val();
		var cheque_dd_number = $("#cheque_dd_number").val();
		var data =cheque_dd_number?cheque_dd_number:dd_number
		if(data !='' ){
				$.ajax({
					url: '../dynamic/getchequeinfo.php',
					type:'post',
					data:{dd_number: data},
					success: function(resp){
						$("#view-cheque-info").html(resp);
						$("#cheque_info").modal('show');
					}
				});
		
	        }
    }

	function openmodel(elem){
		var getId = $("#custId").val();
		$('#cheque_info').modal('hide');
		$('#'+getId).modal('show');
			
	 }
	
	
	</script>
  </body>
</html>