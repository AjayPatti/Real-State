<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");
require("../includes/sendMail.php");
require("../includes/sendMessage.php");
if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_avr_receipt'))){ 
 	header("location:/wcc_real_estate/index.php");
 	exit();
 }
$url = 'avr_receipt.php?search=Search';

$limit = 50;
if(isset($_GET['page'])){
	$page = $_GET['page'];
}else{
	$page = 1;
}

$page_url = $url.'&page='.$page;

$query = "select * from kc_avr_receipt where status = '1' and deleted is null ";



if(isset($_GET['search']) && isset($_GET['from_date']) && isset($_GET['to_date'])){
	$to_date = date("Y-m-d", strtotime($_GET['to_date']));
	$from_date = date("Y-m-d", strtotime($_GET['from_date']));
	$query .= " And  paid_date between '".$from_date."' AND '".$to_date."' ";
	$uri = explode('?', $_SERVER['REQUEST_URI'])[1];
}

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
	//echo "<pre>"; print_r($_POST); die;
	$name_title = filter_post($conn,$_POST['name_title']);
	$name = filter_post($conn,$_POST['name']);
	
	$parent_name_relation = isset($_POST['parent_name_title'])?filter_post($conn,$_POST['parent_name_title']):'';
	
	$parent_sub_title = filter_post($conn,$_POST['parent_sub_title']);
	$parent_name = filter_post($conn,$_POST['parent_name']);
	$nationality = filter_post($conn,$_POST['nationality']);
	
	$mobile = (float) filter_post($conn,$_POST['mobile']);
	$dob = date("Y-m-d",strtotime(filter_post($conn,$_POST['dob'])));
	$address = addslashes($_POST['address']);
	
	$project = filter_post($conn,$_POST['project_block_plotnumber_totalarea']);
	

	$payment_type = filter_post($conn,$_POST['payment_type']);
	$bank_name = filter_post($conn,$_POST['bank_name']);
	$cheque_dd_number = filter_post($conn,$_POST['cheque_dd_number']);
	$paid_amount = (float) filter_post($conn,$_POST['paid_amount']);
	$paid_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['paid_date'])));
	$remarks = filter_post($conn,$_POST['transaction_remarks']);
	$send_message = isset($_POST['send_message'])?true:false;

	if($name_title == ''){
		$_SESSION['error'] = 'Name Title was wrong!';
	}else if($name == ''){
		$_SESSION['error'] = 'Name was wrong!';
	}else if($payment_type != '' && $payment_type != 'Cash' && $payment_type != 'DD' && $payment_type != 'Cheque' && $payment_type != 'NEFT' && $payment_type != 'RTGS'){
		$_SESSION['error'] = 'Payment Mode was wrong!';
	}else if(($payment_type == 'DD' || $payment_type == 'Cheque' || $payment_type == 'NEFT' || $payment_type == 'RTGS') && $bank_name == ""){
		$_SESSION['error'] = 'Bank Name was wrong!';
	}else if(($payment_type == 'DD' || $payment_type == 'Cheque' || $payment_type == 'NEFT' || $payment_type == 'RTGS') && $cheque_dd_number == ""){
		$_SESSION['error'] = 'Cheque/DD Number was wrong!';
	}
	else{
		
		$already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_avr_receipt where name = '".$name."' and dob = '$dob' limit 0,1 "));	// or mobile = '$mobile'
		if(isset($already_exits['id'])){
			$_SESSION['error'] = 'Same Name and DOB Already Exists!';
		}else{
			
			mysqli_query($conn,"insert into kc_avr_receipt set name_title = '$name_title', name = '$name', parent_name = '$parent_name', parent_sub_title = '$parent_sub_title', parent_name_relation = '$parent_name_relation', nationality = '$nationality', mobile = '$mobile', dob = '$dob', address = '$address', payment_type = '$payment_type',bank_name='$bank_name',cheque_dd_number='$cheque_dd_number', paid_amount = '$paid_amount',paid_date = '$paid_date',remarks = '$remarks',project_block_plotnumber_totalarea = '$project',send_message = '$send_message',status = '1', addedon ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."' ");
		}
			$add = mysqli_insert_id($conn);
			if($add > 0){
				$_SESSION['success'] = 'Information Successfully Added!';
				header("location:avr_receipt.php");
				exit();
			}else{
				$_SESSION['error'] = 'Something Went Wrong.Try Again!';
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
		$_SESSION['error'] = 'Block was wrong!';
	}*/else if(!isset($block_details['id'])){
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
	// echo "<pre>"; print_r($_POST); die();
	$id = (int) filter_post($conn,$_POST['name']);
	$payment_type = filter_post($conn,$_POST['payment_type']);
	$bank_name = filter_post($conn,$_POST['bank_name']);
	$cheque_dd_number = filter_post($conn,$_POST['cheque_dd_number']);
	$paid_amount = (float) filter_post($conn,$_POST['paid_amount']);
	$paid_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['paid_date'])));
	$remarks = filter_post($conn,$_POST['remarks']);
	$next_due_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['next_due_date'])));

	$send_message = isset($_POST['at_send_message'])?true:false;

	
	if(!($id > 0)){
		$_SESSION['error'] = 'Something was Really wrong!';
	}else if($payment_type != 'Cash' && $payment_type != 'DD' && $payment_type != 'Cheque' && $payment_type != 'NEFT' && $payment_type != 'RTGS'){
		$_SESSION['error'] = 'Payment Mode was wrong!';
	}else if(!($paid_amount > 0)){
		$_SESSION['error'] = 'Paid Amount was wrong!';
	}else if($paid_date == '' || $paid_date == '1970-01-01'){
		$_SESSION['error'] = 'Paid Date was wrong!';
	}else if($next_due_date == '' || $next_due_date == '1970-01-01'){
		$_SESSION['error'] = 'Next Due Date was wrong!';
	}else{
		$abr_row = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM kc_avr_receipt where id = '".$id."'"));
		
		mysqli_query($conn,"INSERT INTO kc_avr_receipt SET name_title = '".$abr_row['name_title']."', name = '".$abr_row['name']."', parent_sub_title = '$parent_sub_title', parent_name = '".$abr_row['parent_name']."', parent_name_relation = '".$abr_row['parent_name_relation']."', nationality = '".$abr_row['nationality']."', email = '".$abr_row['email']."', mobile = '".$abr_row['mobile']."', dob = '".$abr_row['dob']."', address = '".$abr_row['address']."', payment_type = '$payment_type', paid_amount = '$paid_amount',paid_date = '$paid_date',remarks = '$remarks',next_due_date = '$next_due_date',sales_person_id = '".$abr_row['sales_person_id']."',associate_id = '".$abr_row['associate_id']."',associate_percentage = '".$abr_row['associate_percentage']."',send_message = '$send_message',status = '1', addedon ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."' ");
	}
}

if(isset($_POST['editInformation'])){
	
	$id = filter_post($conn,$_POST['id']);
	$name_title = filter_post($conn,$_POST['name_title']);
	$name = filter_post($conn,$_POST['name']);
	
	$parent_name_relation = filter_post($conn,$_POST['parent_name_title']);
	$parent_sub_title = filter_post($conn,$_POST['parent_sub_title']);
	$parent_name = filter_post($conn,$_POST['parent_name']);
	$nationality = filter_post($conn,$_POST['nationality']);
	
	
	$mobile = (float) filter_post($conn,$_POST['mobile']);
	$dob = date("Y-m-d",strtotime(filter_post($conn,$_POST['dob'])));
	$address = addslashes($_POST['address']);
	$project = filter_post($conn,$_POST['project_block_plotnumber_totalarea']);
	$payment_type = filter_post($conn,$_POST['payment_type']);
	$bank_name = filter_post($conn,$_POST['bank_name']);
	$cheque_dd_number = filter_post($conn,$_POST['cheque_dd_number']);
	$paid_amount = (float) filter_post($conn,$_POST['paid_amount']);
	$paid_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['paid_date'])));
	$remarks = filter_post($conn,$_POST['transaction_remarks']);
	
	if(!($id > 0) || !is_numeric($id)){
		$_SESSION['error'] = 'Something was Really wrong!';
	}else if($name_title == ''){
		$_SESSION['error'] = 'Name Title was wrong!';
	}else if($name == ''){
		$_SESSION['error'] = 'Name was wrong!';
	}else{
		
		$already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_avr_receipt where id != '$id' and (email = '".$email."' or mobile = '$mobile') limit 0,1 "));
		if(isset($already_exits['id'])){
			$_SESSION['error'] = 'Email or Mobile Already Exists!';
		}else{
			mysqli_query($conn,"update kc_avr_receipt set name_title = '$name_title', name = '$name', parent_name = '$parent_name', parent_sub_title = '$parent_sub_title', parent_name_relation = '$parent_name_relation', nationality = '$nationality', mobile = '$mobile', dob = '$dob', address = '$address',payment_type= '$payment_type',bank_name = '$bank_name',project_block_plotnumber_totalarea='$project',cheque_dd_number = '$cheque_dd_number',paid_amount='$paid_amount',paid_date = '$paid_date', updated ='".date('Y-m-d H:i:s')."',remarks='$remarks' where id = '$id' ");

			$name_with_title = $name_title.' '.$name;
			mysqli_query($conn,"update kc_avr_receipt set name = '$name_with_title', mobile = '$mobile', addedon = '".$_SESSION['login_id']."' where id = '$id' limit 1 ");

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
	
	//echo "<pre>"; print_r($_POST); die;
	$customer_id = isset($_POST['customer_id'])?(int) $_POST['customer_id']:0;
	$block_id = isset($_POST['block_id'])?(int) $_POST['block_id']:0;
	$block_number_id = isset($_POST['block_number_id'])?(int) $_POST['block_number_id']:0;
	$associate = isset($_POST['associate'])?(int) $_POST['associate']:0;

	$customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select associate from kc_customer_blocks where customer_id = '".$customer_id."' and block_id = '$block_id' and block_number_id = '$block_number_id' limit 0,1 "));

	if(!($associate > 0)){
		$_SESSION['error'] = 'Please Select Associate!';
	}else if(!isset($customer_details['associate'])){
		$_SESSION['error'] = 'Block Details not Found!';
	}else{
		$error = false;
		mysqli_autocommit($conn,FALSE);

		if (!mysqli_query($conn,"insert into kc_block_number_associates_hist set customer_id = '$customer_id', block_id = '$block_id', block_number_id = '$block_number_id', associate = '".$customer_details['associate']."', added_by = '".$_SESSION['login_id']."' ")){
			$error = true;
		}
		if(!$error && !mysqli_query($conn,"update kc_customer_blocks set associate = '$associate' where customer_id = '".$customer_id."' and block_id = '$block_id' and block_number_id = '$block_number_id'")){
			$error = true;
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

		if (!mysqli_query($conn,"insert into kc_customer_blocks_hist (kc_customer_blocks_id, customer_id, block_id, block_number_id, rate_per_sqft, final_rate, customer_payment_type, registry, registry_date, registry_by, sales_person_id, associate, associate_percentage, status, action_type, addedon, added_by, deleted_by) select id, customer_id, block_id, block_number_id, rate_per_sqft, final_rate, customer_payment_type, registry, registry_date, registry_by, sales_person_id, associate, associate_percentage, status, 'Plot Number Changed', addedon, added_by, '".$_SESSION['login_id']."' from kc_customer_blocks where customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id';")){
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

		if(!$error && !mysqli_query($conn,"delete from kc_customer_transactions where id = '".$transaction_id."';")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}

		if (!mysqli_query($conn,"insert into kc_associate_transactions_hist (customer_id, kc_associate_transactions_id, associate_id, block_id, block_number_id, transaction_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, status, remarks, cancel_remarks, action_type, addedon, added_by, deleted_by) select id, customer_id, associate_id, block_id, block_number_id, transaction_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, status, remarks, '$cancel_remarks', 'Payment Cancelled', addedon, added_by, '".$_SESSION['login_id']."' from kc_associates_transactions where transaction_id = '$transaction_id';")){
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
		
		if($error){
			mysqli_rollback($conn);
			$_SESSION['error'] = 'Something went wrong!';
		}else{
			mysqli_commit($conn);
			$_SESSION['success'] = 'Transaction has been cancelled Successfully.';
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
	}else{
		
		$error = false;
		mysqli_autocommit($conn,FALSE);

		if(!mysqli_query($conn,"update kc_customer_blocks set registry = 'Yes', registry_date = '$registry_date', registry_by = '$registry_by' where customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id' limit 1 ")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
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
		header("Location: $url");
		exit();
	}
}

if(isset($_POST['applyDiscount'])){
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
		if($error){
			mysqli_rollback($conn);
			$_SESSION['error'] = 'Something went wrong!';
		}else{
			mysqli_commit($conn);
			$_SESSION['success'] = 'Discount Rate has been Successfully Updated.';
		}
		mysqli_close($conn);
		header("Location: $url");
		exit();
	}
}

if(isset($_GET['id']))
{
  // $del = $_POST['delete'];
  $id = $_GET['id'];
  $del = "update `kc_avr_receipt` set deleted= '".date('Y-m-d H:i:s')."' WHERE id=$id";
  mysqli_query($conn,$del);
  header('location:avr_receipt.php');
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
            AVR Receipt
            <small>Control panel</small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">AVR Receipt</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
			<div class="box">
                <div class="box-header">
					<?php 
					include("../includes/notification.php"); ?>
					<div class="row" style="margin-right: 0px;">
						<div class="col-sm-8">
							<h3 class="box-title">All AVR Receipt Information</h3>
						</div>
						<?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'add_avr_receipt')){ ?>
	                    <div class="col-2 ">
							<button class="btn btn-sm btn-success pull-right"  style="margin-left: 13px;"data-toggle="modal" data-target="#addAvrReceipt">Add AVR Receipt</button>
						</div>
						<?php } ?>
						<div class="col-2">
                           <a href="avr_excel_export.php?<?php if (isset($_GET['from_date']) && isset($_GET['to_date'])) {echo $uri ; }?>"  class="btn btn-sm btn-success pull-right" style="margin-right: 10px;"><i class="fa fa-file-excel-o"></i> Excel Export</a>
					    </div>
					</div>
					    <form action="" name="search_frm" id="search_frm" method="get" class="">
							<div class="form-group col-sm-3">
								<label for="from_date">From</label>
								<input type="text" class="form-control" id="from_date" name="from_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" data-validation="date" data-validation-format="dd-mm-yyyy" />
							</div>
							<div class="form-group col-sm-3">
								<label for="to_date">To</label>
								<input type="text" class="form-control" id="to_date" name="to_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" data-validation="date" data-validation-format="dd-mm-yyyy" class="form-control" />
							</div>
							<div class="form-group col-sm-3">
								<button type="submit" name="search" value="Search" class="btn btn-primary" style="margin-top: 24px;"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
							</div>
						</form>
				 </div>		
				<!-- <hr /> -->
                <div class="box-body no-padding">
                	<div class="table-responsive">
					 <table class="table table-striped table-hover table-bordered">
	                    <tr>
	                      <th>Sl No.</th>
						  <th>Details</th>
						  <th>Other Details</th>
						  <th>Payment Details</th>
						  <th>Plot Details</th>
	                      <th>Remarks</th>
	                      <th>Added On</th>
	                      <th>Action</th>
						</tr>
						<?php
						
						$total_records = mysqli_num_rows(mysqli_query($conn,$query));
						$total_pages = ceil($total_records/$limit);
						
						if($page == 1){
							$start = 0;
						}else{
							$start = ($page-1)*$limit;
						}
						$query .= " limit $start,$limit";
						$receipts = mysqli_query($conn,$query);
						if(mysqli_num_rows($receipts) > 0){
							$counter = $start + 1;

                      while($receipt = mysqli_fetch_assoc($receipts)){?>
					
								<tr>
									<td><?php echo $counter; ?>.</td>
									<td nowrap="nowrap">
										<strong><?php echo $receipt['name_title']; ?> <?php echo $receipt['name']; ?></strong><br>
	                                    <strong><?php echo $receipt['parent_name_relation']; ?> </strong> <?php if($receipt['parent_name'] != ''){ ?>of <strong><?php echo $receipt['parent_sub_title']; ?> <?php echo $receipt['parent_name']; } ?></strong><br>

									</td>
									<td>Mobile: <strong><?php echo $receipt['mobile']; ?></strong><br>
	                                    Address: <strong><?php echo $receipt['address']; ?></strong>
									</td>
									<td>
									     <!-- <button class="btn btn-xs btn-success" onClick="addTransaction(<?php //echo $receipt['id']; ?>);" data-toggle="tooltip" title="Add Transaction"><i class="fa fa-money"></i></button>  -->	
										<?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'view_transaction_avr_receipt')){ ?>

                                		 <button class="btn btn-xs btn-warning" onClick="getTransactions(<?php echo $receipt['id']; ?>);" data-toggle="tooltip" title="View Transactions"><i class="fa fa-money"></i></button><br>
										<?php } ?>
	                                    Payment Type: <strong><?php echo $receipt['payment_type']; ?></strong><br>
	                                    Paid Amount: <strong><?php echo $receipt['paid_amount']; ?></strong><br>
	                                    Paid Date: <strong class="text-danger"><?php echo date('jS M Y',strtotime($receipt['paid_date'])); ?></strong>
									</td>
									<td><strong class="text-danger"><?php echo $receipt['project_block_plotnumber_totalarea']; ?></strong></td>
									<td><?php echo $receipt['remarks']; ?></td>
									<td><?php 
									if($receipt['addedon'] !='0000-00-00 00:00:00')
									echo (date('jS M Y',strtotime($receipt['addedon']))); 
									?></td>
									<td nowrap="nowrap">
									<?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'view_avr_receipt')){ ?>

	                                	<button class="btn btn-xs btn-info" type="button" data-toggle="tooltip" title="View AVR Receipt Information" onclick = "viewInformation(<?php echo $receipt['id']; ?>);"><i class="fa fa-eye"></i></button>
	                                <?php } if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'edit_avr_receipt')){ ?>
	                                	<button class="btn btn-xs btn-warning" type="button" data-toggle="tooltip" title="Edit AVR Receipt Information" onclick = "editInformation(<?php echo $receipt['id']; ?>);"><i class="fa fa-pencil"></i></button>
	                                <?php } if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'delete_avr_receipt')){  ?>
	                                	<a href="avr_receipt.php?id=<?php echo $receipt['id']; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure');"><i class="fa fa-trash "></i></a>
	                                <?php } ?>
									</td>
								</tr>
								<?php
								$counter++; }
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

      <?php require('../includes/control-sidebar.php'); ?>
      <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
      <div class="control-sidebar-bg"></div>
    </div><!-- ./wrapper -->
	
	
	
	<div class="modal" id="addAvrReceipt">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form action="avr_receipt.php" name="" id="" method="post" class="form-horizontal">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add AVR Receipt</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-header with-border">
						<div class="col-md-12">
							<h3 class="box-title">Add AVR Receipt Panel</h3>
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
						  <label for="parent_name_title" class="col-sm-3 control-label"><input type="radio" value="S" name="parent_name_title" data-validation="required" data-validation-error-msg="required">S/<input type="radio" value="W" name="parent_name_title" data-validation="required" data-validation-error-msg="required">W/<input type="radio" value="D" name="parent_name_title" data-validation="required" data-validation-error-msg="required">D/<input type="radio" value="C" name="parent_name_title" data-validation="required" data-validation-error-msg="required">C of</label>
						  <div class="col-sm-8">
						  	<select class="form-control" name="parent_sub_title" id="parent_sub_title" style="width:15%;float:left;" data-validation="required">
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
							<input type="text" class="form-control" id="nationality" name="nationality" value="Indian">
						  </div>
						</div>

                        <div class="form-group">
						  <label for="dob" class="col-sm-3 control-label">DOB</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="dob" name="dob" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" data-validation="date" data-validation-format="dd-mm-yyyy" >
						  </div>
						</div>
                        
                        
                        
                        <div class="form-group">
						  <label for="address" class="col-sm-3 control-label">Address</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="address" name="address">
						  </div>
						</div>
                        
                        
                        <div class="form-group">
						  <label for="mobile" class="col-sm-3 control-label">Mobile</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="mobile" name="mobile" data-validation="number length" data-validation-length="10-10">
						  </div>
						</div>
                        
                       

						<div class="form-group">
						  <label for="project" class="col-sm-3 control-label">Project/Block/Plot Number/Total Area</label>
						  <div class="col-sm-8">
							<input type="text" name="project_block_plotnumber_totalarea" class="form-control" id="project">
						  </div>
						</div>
                        
                        <div class="form-group">
						  <label for="customer_payment_mode" class="col-sm-3 control-label">Payment Mode</label>
						  <div class="col-sm-8">
							<select class="form-control" id="customer_payment_mode" name="payment_type" data-validation="required" data-validation-error-msg="required" onChange="customerPaymentTypeChanged(this);">
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
							<input type="text" class="form-control" id="customer_cheque_dd_number" name="cheque_dd_number" data-validation="required" data-validation-depends-on="payment_type" data-validation-depends-on-value="DD, Cheque, NEFT, RTGS">
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
							<input type="text" class="form-control" id="customer_paid_date" name="paid_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" data-validation="date" data-validation-format="dd-mm-yyyy" >
						  </div>
						</div>
                        

                        <div class="form-group">
						  <label for="transaction_remarks" class="col-sm-3 control-label">Remarks</label>
						  <div class="col-sm-8">
							<textarea class="form-control" id="transaction_remarks" name="transaction_remarks"></textarea>
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
						  <label for="ab_paid_date" class="col-sm-3 control-label"><span class="cheque_dd_label">Paid</span> Date</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="ab_customer_paid_date" name="ab_paid_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" data-validation="date" data-validation-format="dd-mm-yyyy" data-validation-depends-on="ab_payment_type">
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
						  <label for="ab_associate" class="col-sm-3 control-label">Associate</label>
						  <div class="col-sm-8">
						  	<input type="text" class="form-control associate-autocomplete" data-for-id="ab_associate" placeholder="Name or Code or Mobile">
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
						<input type="hidden" name="name" id="id" value="<?php echo $receipt['name'];?>">
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
							<input type="text" class="form-control" id="cheque_dd_number" name="cheque_dd_number">
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
						  <label for="remarks" class="col-sm-3 control-label">Remarks</label>
						  <div class="col-sm-8">
							<textarea class="form-control" id="remarks" name="remarks"></textarea>
						  </div>
						</div>
                        <div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">Next Due Date</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="at_next_due_date" name="next_due_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" >
                            <input type="hidden" name="customer_id" id="customer_id">
                            <input type="hidden" name="block_id" id="block_id">
                            <input type="hidden" name="block_number_id" id="block_number_id">
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

    
    
    <div class="modal" id="viewReceiptTransaction">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="<?php echo $page_url; ?>" name="view_transaction_frm" id="view_transaction_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">All Receipt Transaction</h4>
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
			<form enctype="multipart/form-data" action="avr_receipt.php" name="edit_frm" id="edit_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Edit ABR Receipt Information</h4>
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
				<h4 class="modal-title">AVR Receipt Information</h4>
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
							<input type="text" class="form-control" id="registry_date" name="registry_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" data-validation="birthdate" data-validation-format="dd-mm-yyyy">
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
				<button type="submit" class="btn btn-primary" name="applyDiscount">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	<?php require('../includes/common-js.php'); ?>
	
    <script type="text/javascript">
    $(function(){
    	$("#cancelTransactionBack").click(function(){
    		$("#cancelTransaction").modal('hide');
    		getTransactions($("#cancel_customer_id").val(),$("#cancel_block_id").val(),$("#cancel_block_number_id").val());
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

	function addLatePayment(customer,block,block_number,next_due_date){
		$("#late_customer_id").val(customer);
		$("#late_block_id").val(block);
		$("#late_block_number_id").val(block_number);
		$("#late_next_due_date").val(next_due_date);
		$("#addLatePayment").modal('show');
	}

	function addTransaction(id){
		$('#id').val(id);
		
		$("#addTransaction").modal('show');
	}
	
	function getTransactions(id){
		$.ajax({
			url: '../dynamic/getAbrReceiptTransactions.php',
			type:'post',
			data:{id:id},
			success: function(resp){
				$("#view-transaction-container").html(resp);
				$("#viewReceiptTransaction").modal('show');
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
	
	function editInformation(id){
		$.ajax({
			url: '../dynamic/getEditAbrReceiptInformation.php',
			type:'post',
			data:{id:id},
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
	function viewInformation(id){
		$.ajax({
			url: '../dynamic/viewAbrReceipt.php',
			type:'post',
			data:{id:id},
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

	</script>
    
  </body>
</html>