<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");
require("../includes/sendMail.php");
require("../includes/sendMessage.php");

 if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_purchase'))){
 	header("location:/wcc_real_estate/index.php");
 	exit();
 }

$url = 'purchase_info.php?search=Search';

$limit = 50;
if(isset($_GET['page'])){
	$page = $_GET['page'];
}else{
	$page = 1;
}

$page_url = $url.'&page='.$page;


if(isset($_GET['search_farmer'])){
	$page_url .= "&search_farmer=".$_GET['search_farmer'];
}


if(isset($_GET['search_village'])){
	$page_url .= "&search_village=".$_GET['search_village'];
}
if(isset($_GET['search_broker'])){
	$page_url .= "&search_broker=".$_GET['search_broker'];
}

//print_r($page_url); die;
if(isset($_POST['save'])){
	// echo "<pre>"; print_r($_POST); die;
	$name_title = filter_post($conn,$_POST['name_title']);
	$name = ($_POST['name'])?filter_post($conn,$_POST['name']):'';

	$parent_name_relation = isset($_POST['parent_name_relation'])?filter_post($conn,$_POST['parent_name_relation']):'';
	$parent_name_sub_title = isset($_POST['parent_name_sub_title'])?filter_post($conn,$_POST['parent_name_sub_title']):'';
	$parent_name = filter_post($conn,$_POST['parent_name']);
	// $nationality = filter_post($conn,$_POST['nationality']);
	// $profession = filter_post($conn,$_POST['profession']);
	// $nominee_name = filter_post($conn,$_POST['nominee_name']);
	// $nominee_relation = filter_post($conn,$_POST['nominee_relation']);

	// $residentail_status = filter_post($conn,$_POST['residentail_status']);
	// $pan_no = filter_post($conn,$_POST['pan_no']);

	// $email = filter_post($conn,$_POST['email']);
	$mobile = isset($_POST['mobile'])?(float)filter_post($conn,$_POST['mobile']):'';
    $village = addslashes($_POST['village']);
    $khasra_no = addslashes($_POST['khasra_no']);
	// $dob = date("Y-m-d",strtotime(filter_post($conn,$_POST['dob'])));
	// $address = addslashes($_POST['address']);
	// $office_address = addslashes($_POST['office_address']);

	// $block = (int) filter_post($conn,$_POST['block']);
	// $block_number = (int) filter_post($conn,$_POST['block_number']);

	// $block_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, project_id, name from kc_blocks where id = '".$block."' limit 0,1 "));
	// $block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, block_number from kc_block_numbers where id = '$block_number' limit 0,1 "));

	// $rate = (float) filter_post($conn,$_POST['rate']);

	$purchaser_name = filter_post($conn,$_POST['purchaser']);
	$payable_amount = (float) filter_post($conn,$_POST['payable_amount']);
	$amount = (float) filter_post($conn,$_POST['amount']);
	$area_in_hectare = (float) filter_post($conn,$_POST['area_in_hectare']);
	// $area_in_sqft = (float) filter_post($conn,$_POST['area_in_sqft']);
	$area_in_biswa = (float) filter_post($conn,$_POST['area_in_biswa']);
	//$per_biswa = filter_post($conn,$_POST['amount']);
	//$total_area_biswa = filter_post($conn,$_POST['total_area_biswa']);
	$broker = filter_post($conn,$_POST['broker']);
	$seller = filter_post($conn,$_POST['seller']);
	$farmers_account = filter_post($conn,$_POST['farmersaccount']);
	$farmers_bankname = filter_post($conn,$_POST['farmersbankname']);
	$farmers_branch = filter_post($conn,$_POST['farmersbranch']);
	$farmers_ifsccode = filter_post($conn,$_POST['farmersifsccode']);
	//$plot_value = filter_post($conn,$_POST['payable_amount']);


	// $payment_type = filter_post($conn,$_POST['payment_type']);
	// $bank_name = filter_post($conn,$_POST['bank_name']);
	// $cheque_dd_number = filter_post($conn,$_POST['cheque_dd_number']);
	// $paid_amount = (float) filter_post($conn,$_POST['paid_amount']);
	// $paid_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['paid_date'])));
	// $add_transaction_remarks = filter_post($conn,$_POST['transaction_remarks']);

	// $next_due_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['next_due_date'])));

	// $customer_payment_type = filter_post($conn,$_POST['customer_payment_type']);
	$send_message = isset($_POST['send_message'])?true:false;

	// if($customer_payment_type != "EMI"){
	// 	$number_of_installment = 0;
	// 	$installment_amount = 0;
	// 	$emi_payment_date = '1970-01-01';
	// }else{
	// 	$number_of_installment = filter_post($conn,$_POST['number_of_installment']);
	// 	$installment_amount = filter_post($conn,$_POST['installment_amount']);
	// 	$emi_payment_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['emi_payment_date'])));
	// 	$next_due_date = date("Y-m-d",strtotime('+1 month',strtotime($emi_payment_date)));
	// }

	// $register_by = filter_post($conn,$_POST['register_by']);
	// $sales_person_id = (isset($_POST['sales_person']) && $_POST['sales_person'] != '')?filter_post($conn,$_POST['sales_person']):0;
	// $associate = (isset($_POST['associate']) && $_POST['associate'] != '')?filter_post($conn,$_POST['associate']):0;
	// $associate_percentage = (isset($_POST['associate_percentage']) && $_POST['associate_percentage'] != '')?filter_post($conn,$_POST['associate_percentage']):0;


	if($name_title == ''){
		$_SESSION['error'] = 'Name Title was wrong!';
	}else if($name == '' && ($mobile == '' && strlen($mobile) != 10)){
		$_SESSION['error'] = 'Name Or Phone was entered wrong!';
	}
    // else if ($email != '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
	//   $_SESSION['error'] = 'Email was wrong!';
	// }
    // else if($mobile == '' || strlen($mobile) != 10){
	// 	$_SESSION['error'] = 'Mobile was wrong!';
	// }
    else if($khasra_no == ''){
		$_SESSION['error'] = 'Khasra No was wrong!';
	}
    // else if($dob == '' || $dob == '1970-01-01'){
	// 	$_SESSION['error'] = 'DOB(Date of Birth) was wrong!';
	// }else if($payment_type != '' && $payment_type != 'Cash' && $payment_type != 'DD' && $payment_type != 'Cheque' && $payment_type != 'NEFT' && $payment_type != 'RTGS'){
	// 	$_SESSION['error'] = 'Payment Mode was wrong!';
	// }else if(($payment_type == 'DD' || $payment_type == 'Cheque' || $payment_type == 'NEFT' || $payment_type == 'RTGS') && $bank_name == ""){
	// 	$_SESSION['error'] = 'Bank Name was wrong!';
	// }else if(($payment_type == 'DD' || $payment_type == 'Cheque' || $payment_type == 'NEFT' || $payment_type == 'RTGS') && $cheque_dd_number == ""){
	// 	$_SESSION['error'] = 'Cheque/DD Number was wrong!';
	// }else if(!($rate > 0)){
	// 	$_SESSION['error'] = 'Rate was wrong!';
	// }else if($customer_payment_type == "EMI" && (!($number_of_installment > 0) || !is_numeric($number_of_installment))){
	// 	$_SESSION['error'] = 'Number of Installment was wrong!';
	// }else if($customer_payment_type == "EMI" && !($installment_amount > 0)){
	// 	$_SESSION['error'] = 'Installment Amount was wrong!';
	// }else if($customer_payment_type == "EMI" && $emi_payment_date == "1970-01-01"){
	// 	$_SESSION['error'] = 'Installment Date was wrong!';
	// }
    else if(!($payable_amount > 0)){
		$_SESSION['error'] = 'Total Plot Value was wrong!';
	}else if($purchaser_name == ''){
		$_SESSION['error'] = 'Purchaser Name was wrong!';
	}
    // else if($register_by == ''){
	// 	$_SESSION['error'] = 'Register By was wrong!';
	// }
    else if($amount == ''){
		$_SESSION['error'] = 'Rate per Biswa was wrong!';
	}else if($area_in_hectare == ''){
		$_SESSION['error'] = 'Total Area In Hectare was wrong!';
	}
	// else if($area_in_sqft == ''){
	// 	$_SESSION['error'] = 'Total Area In Sq.Ft was wrong!';
	// }
	else if($area_in_biswa == ''){
		$_SESSION['error'] = 'Total Area In Biswa  was wrong!';
	}
	/******* comment due to mohit sir ask on 04062020 **********/
	/*else if($paid_amount > 0 && $paid_amount > $payable_amount){
		$_SESSION['error'] = 'Total Plot Value must be greater than Paid Amount!';
	}*/
	/******* comment due to mohit sir ask on 04062020 **********/
	/*else if($next_due_date == '' || $next_due_date == '1970-01-01'){
		$_SESSION['error'] = 'Next Due Date was wrong!';
	}else if( !($next_due_date > date("Y-m-d",strtotime("+3 days")))){
		$_SESSION['error'] = 'Next Due Date should be greater than '.date("jS F Y",strtotime("+3 days"));
	}else if(!isset($block_details['id'])){
		$_SESSION['error'] = 'Block was wrong!';
	}else if(!isset($block_number_details['id'])){
		$_SESSION['error'] = 'Plot Number was wrong!';
	}*//*else if($associate > 0 && (!is_numeric($associate_percentage) || !($associate_percentage > 0))){
		$_SESSION['error'] = 'Associate Percentage was wrong!';
	}*//*else if($paid_amount > 0 && $customer_payment_type == "EMI" && $installment_amount > $paid_amount){
		$_SESSION['error'] = 'Paid Amount was wrong!';
	}*/
	else{

		// $already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_farmers where mobile = '$mobile'  limit 0,1 "));	// or mobile = '$mobile'
		// if(isset($already_exits['id'])){
			// $_SESSION['error'] = 'Mobile No Already Exists!';
		//  }
		//  else{

			// $error = false;
			// mysqli_autocommit($conn,FALSE);
            // , khasra_no = '$khasra_no',register_by = '$register_by'
			$query1 = "insert into kc_farmers set name_title = '$name_title', name = '$name', parent_name='$parent_name',parent_name_relation='$parent_name_relation',parent_name_sub_title='$parent_name_sub_title',village = '$village', mobile='$mobile', area_hectare = '$area_in_hectare', per_biswa = '$amount', total_area_biswa = '$area_in_biswa', plot_value = '$payable_amount', broker = '$broker', status = '1', created ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."', khashra_no = '$khasra_no', purchaser_name = '$purchaser_name', seller = '$seller' ";
			if (!mysqli_query($conn,$query1))
			{
				$error = true;
				echo("Error description: " . mysqli_error($conn)); die;
			}else{
            
			
				// echo "<pre>"; print_r("insert into kc_farmers set name_title = '$name_title', name = '$name', parent_name='$parent_name',parent_name_title='$parent_name_relation',parent_name_sub_title='$parent_name_sub_title',village = '$village', mobile='$mobile',area_hectare = '$area_in_hectare', per_biswa = '$amount', total_area_biswa = '$area_in_biswa', plot_value = '$payable_amount', broker = '$broker', status = '1', created ='" . date('Y-m-d H:i:s') . "', added_by = '" . $_SESSION['login_id'] . "', khasra_no = '$khasra_no', purchaser_name = '$purchaser_name'"); die;
				$farmer_id = mysqli_insert_id($conn);

				$farmers_account_details = "INSERT INTO `kc_farmers_account`(`farmers_id`, `account_number`, `bank_name`, `branch_name`, `ifsc_code`) VALUES ('$farmer_id','$farmers_account','$farmers_bankname','$farmers_branch','$farmers_ifsccode')";
				if (!mysqli_query($conn,$farmers_account_details))
				{
				$error = true;
				echo("Error description: " . mysqli_error($conn)); die;
				}
				else
				{
					$farmer_account_id = mysqli_insert_id($conn);
				}
				// echo $farmer_id;
				// 11
				// die;



			}



            if(!$error && !mysqli_query($conn,"insert into kc_farmer_transactions set farmer_id = '$farmer_id',amount = '$payable_amount',cr_dr ='cr', payment_type='', paid_date = '".date("Y-m-d")."', status = '1', created ='".date('Y-m-d H:i:s')."',added_by = '".$_SESSION['login_id']."' ")){
				//$error = true;
				echo("Error description: " . mysqli_error($conn)); die;
			}




			if(!$error){
				mysqli_commit($conn);
				//echo "success"; die;
				$_SESSION['success'] = 'Farmer Successfully Added! and Transaction Successfully Added!';

				$name_with_title = $name_title.' '.$name;
				if($send_message){
					$variables_array = array('variable1' => $name_with_title,'variable2'=>$block_number_details['block_number'],'variable3'=>$block_details['name'],'variable4'=>blockProjectName($conn,$block_details['project_id']));
					if(0){//sendMessage($conn,7,$mobile,$variables_array)
						$_SESSION['success'] .= ' and Welcome Message sent Successfully!';
					}else if(!isset($_SESSION['error'])){
						$_SESSION['error'] = 'Welcome Message not sent!';
					}else if(isset($_SESSION['error'])){
						$_SESSION['error'] .= ' and Welcome Message not sent!';
					}
				}

				if(0){//$paid
					if(0){//sendMail($email,$name_with_title,$paid_amount,$block_details['name'],$block_number_details['block_number'],$paid_date,"PaymentReceived")
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
// echo "<pre>"; print_r($_POST); die;
	$farmer_id = (int) filter_post($conn,$_POST['farmer_id']);
	$payment_type = filter_post($conn,$_POST['payment_type']);
	$bank_name = filter_post($conn,$_POST['bank_name']);
	$cheque_dd_number = filter_post($conn,$_POST['cheque_dd_number']);
	//echo"<pre>"; print_r($cheque_dd_number); die;
	$paid_amount = (float) filter_post($conn,$_POST['paid_amount']);
	$paid_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['paid_date'])));
	$add_transaction_remarks = filter_post($conn,$_POST['transaction_remarks']);
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


	//echo $check; die;
	if(!($farmer_id > 0)){
		$_SESSION['error'] = 'Something was Really wrong!';
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
	}else if($next_due_date == '' || ($next_due_date == '1970-01-01' && !isLastEmiPayment($conn,$farmer_id,$block_id,$block_number_id))){
		$_SESSION['error'] = 'Next Due Date was wrong!';
	}/*else if( !($next_due_date > date("Y-m-d",strtotime("+3 days")))){
		$_SESSION['error'] = 'Next Due Date should be greater than '.date("jS F Y",strtotime("+3 days"));
	}*/else{
		$total_loan = mysqli_fetch_assoc(mysqli_query($conn,"select sum(amount) as total_loan from kc_farmer_transactions where farmer_id = '$farmer_id' and cr_dr = 'cr' limit 0,1 "));
		$total_paid = mysqli_fetch_assoc(mysqli_query($conn,"select sum(amount) as total_paid from kc_farmer_transactions where farmer_id = '$farmer_id' and cr_dr = 'dr' limit 0,1 "));


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

			if(!mysqli_query($conn,"insert into kc_farmer_transactions set farmer_id = '$farmer_id', payment_type = '$payment_type', bank_name = '$bank_name', cheque_dd_number = '$cheque_dd_number', amount = '$paid_amount', cr_dr = 'dr', paid_date = '$paid_date', remarks = '$add_transaction_remarks', next_due_date = '$next_due_date', status = '1', created ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."' ")){
				$error = true;
			}else{
				$paid_transaction_id = mysqli_insert_id($conn);

				receiptNumber($conn,$paid_transaction_id);

				if(!makeAssociateCredit($conn,$paid_transaction_id)){
					$error = true;
				}
			}


			if($error){
				mysqli_rollback($conn);
				$_SESSION['error'] = 'Something Wrong!';
			}else{
				mysqli_commit($conn);
				$_SESSION['success'] = 'Transaction Successfully Added!';

				$farmer_details = mysqli_fetch_assoc(mysqli_query($conn,"select name_title, name, email, mobile from kc_farmers where id = '".$farmer_id."' limit 0,1 "));

				$name_with_title = $farmer_details['name_title'].' '.$farmer_details['name'];



				// if($send_message){
				// 	$variables_array = array('variable1' => $name_with_title,'variable2'=>$paid_amount,'variable3'=>$block_number_details['block_number'],'variable4'=>$block_details['name'],'variable5'=>$paid_date);
				// 	if(sendMessage($conn,9,$farmer_details['mobile'],$variables_array)){
				// 		$_SESSION['success'] .= ' and Message sent Successfully!';
				// 	}else if(!isset($_SESSION['error'])){
				// 		$_SESSION['error'] = ' Message not sent!';
				// 	}else if(isset($_SESSION['error'])){
				// 		$_SESSION['error'] .= ' and Message not sent!';
				// 	}
				// }
				header("Location:".$page_url);
				exit();
			}

		}
	}
}
// echo "<pre>"; print_r($_POST); die;
   if(isset($_POST['editInformation'])){
	 $farmer_id = filter_post($conn,$_POST['farmer']);
	 $name_title = filter_post($conn,$_POST['name_title']);
	 $name = filter_post($conn,$_POST['name']);

	 $parent_name_relation = filter_post($conn,$_POST['parent_name_relation']);
	 $parent_name_sub_title = filter_post($conn,$_POST['parent_name_sub_title']);
	 $parent_name = filter_post($conn,$_POST['parent_name']);

	 $mobile = (float) filter_post($conn,$_POST['mobile']);
	 // $dob = date("Y-m-d",strtotime(filter_post($conn,$_POST['dob'])));
	 $village = addslashes($_POST['village']);
	 $khasra_no = (float) filter_post($conn,$_POST['khasra_no']);
	 $area_in_hectare = (float) filter_post($conn,$_POST['area_in_hectare']);
	 $per_biswa= (float) filter_post($conn,$_POST['per_biswa']);
	//  $area_in_sqft = (float) filter_post($conn,$_POST['area_in_sqft']);
	 $total_area_biswa = (float) filter_post($conn,$_POST['total_area_biswa']);
	 $payable_amount = (float) filter_post($conn,$_POST['payable_amount']);
	 $broker = filter_post($conn,$_POST['broker']);
	$purchaser_name = filter_post($conn,$_POST['purchaser']);
	$seller = filter_post($conn,$_POST['seller']);
	$farmers_detilas_id = filter_post($conn,$_POST['farmersid']);
	$farmers_ids = filter_post($conn,$_POST['farmerid']);
	$farmers_account = filter_post($conn,$_POST['farmersaccount']);
	$farmers_bankname = filter_post($conn,$_POST['farmersbankname']);
	$farmers_branch = filter_post($conn,$_POST['farmersbranch']);
	$farmers_ifsccode = filter_post($conn,$_POST['farmersifsccode']);

	 if(!($farmer_id > 0) || !is_numeric($farmer_id)){
		 $_SESSION['error'] = 'Something was Really wrong!';
	 }else if($name_title == ''){
		 $_SESSION['error'] = 'Name Title was wrong!';
	 }else if($name == ''){
		 $_SESSION['error'] = 'Name was wrong!';
	 }
	 // else if ($email != '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
	 //   $_SESSION['error'] = 'Email was wrong!';
	 // }
	 else if($mobile == '' || strlen($mobile) != 10){
		 $_SESSION['error'] = 'Mobile was wrong!';
	 }
	 // else if($dob == '' || $dob == '1970-01-01'){
	 // 	$_SESSION['error'] = 'DOB(Date of Birth) was wrong!';
	 // }
	//  else{
		// $already_exits_account_id = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_farmers_account where farmers_id != '$farmer_id'"));
		// $already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_farmers where id != '$farmer_id'  and mobile = '$mobile' limit 0,1 "));
		//  if(isset($already_exits['id'])){
			//  $_SESSION['error'] = 'Mobile Already Exists!';
		//  }
		 else{
			 //, khasra_no = '$khasra_no'
			mysqli_query($conn,"update kc_farmers set name_title = '$name_title', name = '$name', parent_name = '$parent_name', parent_name_relation = '$parent_name_relation', parent_name_sub_title = '$parent_name_sub_title', mobile = '$mobile', khashra_no = '$khasra_no', village = '$village', area_hectare = '$area_in_hectare',per_biswa='$per_biswa',total_area_biswa = '$total_area_biswa',plot_value = '$payable_amount', broker = '$broker', purchaser_name='$purchaser_name', seller= '$seller'  where id = '$farmer_id'  ");
			$name_with_title = $name_title.' '.$name;
			
			if($farmer_id == $farmers_detilas_id && isset($farmers_detilas_id))
			{
				mysqli_query($conn, "UPDATE `kc_farmers_account` SET `account_number`='$farmers_account',`bank_name`='$farmers_bankname',`branch_name`='$farmers_branch',`ifsc_code`='$farmers_ifsccode' WHERE `farmers_id` =  $farmers_detilas_id");
			}
			else
			{
				if(isset($farmers_ids))
				{
					mysqli_query($conn, "INSERT INTO `kc_farmers_account`(`farmers_id`, `account_number`, `bank_name`, `branch_name`, `ifsc_code`) VALUES ('$farmer_id','$farmers_account','$farmers_bankname','$farmers_branch','$farmers_ifsccode')");	
				}
			}
			$_SESSION['success'] = 'Information Successfully Updated!';
			 header("Location:".$page_url);
			 exit();
		 }
	 }

//  }

 if(isset($_POST['editRegistry'])){
	$farmer_id = filter_post($conn,$_POST['farmer']);
	// echo "<pre>"; print_r($farmer_id); die;
	// $registry_date=filter_post($conn,$_POST['registry_date']);
	$registry_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['registry_date'])));
	$khashra_no=filter_post($conn,$_POST['khashra_no']);
	// echo "<pre>"; print_r($khashra_no); die;
	$purchase_value=filter_post($conn,$_POST['purchase_value']);
	// echo "<pre>"; print_r($purchase_value); die;
	$maliyat_value=filter_post($conn,$_POST['maliyat_value']);
	// echo "<pre>"; print_r($maliyat_value); die;
	$stamp_fee=filter_post($conn,$_POST['stamp_fee']);
	// echo "<pre>"; print_r($stamp_fee); die;
	mysqli_query($conn, "update kc_farmers set registry_date = '$registry_date',khashra_no='$khashra_no',purchase_value='$purchase_value',maliyat_value='$maliyat_value',stamp_fee='$stamp_fee' WHERE id='$farmer_id' ");
	$_SESSION['success'] = 'Registry Successfully Updated!';
			 header("Location:".$page_url);
			 exit();
			// die;
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

	// echo "<pre>"; print_r($_POST); die;
	$transaction_id = isset($_POST['cancel_transaction_id'])?(int) $_POST['cancel_transaction_id']:0;
	$cancel_remarks = isset($_POST['cancel_remarks'])?trim($_POST['cancel_remarks']):'';

	$transaction_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, farmer_id from kc_farmer_transactions where id = '".$transaction_id."' limit 0,1 "));

	if(!isset($transaction_details['id'])){
		$_SESSION['error'] = 'Transaction not Found!';
	}else if($cancel_remarks == ""){
		$_SESSION['error'] = 'Cancel Remarks is required!';
	}else{
		$error = false;
		mysqli_autocommit($conn,FALSE);
		
		if (!mysqli_query($conn,"insert into kc_farmer_transactions_hist(kc_farmer_transactions_id, farmer_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, next_due_date, status, remarks, cancel_remarks, created, added_by, deleted_by)select id, farmer_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, next_due_date, status, remarks, '$cancel_remarks', created, added_by, '".$_SESSION['login_id']."' from kc_farmer_transactions where id = '$transaction_id';")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}

		if(!$error && !mysqli_query($conn,"delete from kc_farmer_transactions where id = '".$transaction_id."';")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
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
    // echo "<pre>"; print_r($_POST); die;
	$farmer_id = (int) filter_post($conn,$_POST['registry_farmer_id']);
	$registry_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['registry_date'])));
	// $purchase_date = filter_post($conn,$_POST['purchase_date']);
	$khashra_no=filter_post($conn,$_POST['khashra_no']);
	// echo "<pre>"; print_r($khashra_no); die;

	$maliyat_value = filter_post($conn,$_POST['maliyat_value']);
	$purchase_value = filter_post($conn,$_POST['purchase_value']);
	$stamp_fee = filter_post($conn,$_POST['stamp_fee']);

	if(!($farmer_id > 0)){
		$_SESSION['error'] = 'Something was Really wrong!';
	}
    // else if(!isset($block_details['id'])){
	// 	$_SESSION['error'] = 'Opps Something was Really wrong!';
	// }else if(!isset($block_number_details['id'])){
	// 	$_SESSION['error'] = 'Oppps! Something was Really wrong!';
	// }
    else if($registry_date == '' || $registry_date == '1970-01-01'){
		$_SESSION['error'] = 'Registry Date was wrong!';
	}else if($khashra_no == ''){
		$_SESSION['error'] = 'Khasra No is required!';
	}
    else if($maliyat_value == ''){
		$_SESSION['error'] = 'Maliyat Value is required!';
	}
    // else{

		// $already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_farmers where khashra_no = '".$khashra_no."' limit 0,1 "));	// or mobile = '$mobile'
		// if(isset($already_exits['id'])){
			// $_SESSION['error'] = 'Khasra No Already Exists!';
        // }
		else{
            $error = false;
            mysqli_autocommit($conn,FALSE);
			$query = "update kc_farmers set registry = 'Yes', registry_date = '$registry_date',  khashra_no = '$khashra_no',maliyat_value='$maliyat_value',purchase_value='$purchase_value', stamp_fee='$stamp_fee' where id = '$farmer_id' limit 1 ";
            if(!mysqli_query($conn,$query)){
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
// }


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
	 // print_r($customer_id); die();
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
        // echo ; die();
		if(!$error && !mysqli_query($conn,"insert into kc_customer_transactions set customer_id = '$customer_id', block_id = '$block_id', block_number_id = '$block_number_id', amount = '$amount',next_due_date = '2000-01-01', cr_dr = 'cr', status = '1', remarks = '$remarks', addedon ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."'")){
			$error = true;
		}
		$insert_id = mysqli_insert_id($conn);
		if($error){
			mysqli_rollback($conn);
			$_SESSION['error'] = 'Something went wrong!';
		}else{
			if($send_message){


				$customer_mobile = mysqli_fetch_assoc(mysqli_query($conn,"SELECT mobile from kc_customers where id = '".$customer_id."'"));
				$mobile = $customer_mobile['mobile'];
			 	$variables_array = array('variable1'=>$amount = $_POST['amount']);
			 	if(sendMessage($conn,26,$mobile,$variables_array)){
			 		$_SESSION['success'] .= ' and Welcome Message sent Successfully!';
			 	}else if(!isset($_SESSION['error'])){
			 		$_SESSION['error'] = 'Welcome Message not sent!';
			 	}else if(isset($_SESSION['error'])){
			 		$_SESSION['error'] .= ' and Welcome Message not sent!';
			 	}
			 }
			mysqli_commit($conn);
			$_SESSION['success'] = 'Extra Charges Added Successfully !';
		}
		mysqli_close($conn);

		header("Location: ".$page_url);
		exit();
	}
}

if(isset($_GET['deleteInfo'])){
    $farmerId = $_GET['deleteInfo'];
     mysqli_query($conn,"DELETE  FROM `kc_farmers` WHERE id = '$farmerId'");
    mysqli_query($conn,"UPDATE `kc_farmer_transactions` SET deleted = '".date('Y-m-d H:i:s')."' WHERE farmer_id = '$farmerId'");
    mysqli_query($conn,"UPDATE `kc_farmer_transactions_hist` SET deleted = '".date('Y-m-d H:i:s')."' WHERE farmer_id = '$farmerId'");
    $_SESSION['success'] = 'Farmer Deleted Successfully !';
    header("Location: ".$page_url);
    exit();
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

	<!-- Select2 -->
    <link href="/<?php echo $host_name; ?>/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
	<link rel="icon" type="image/x-icon" href="/<?php echo $host_name; ?>img/logo.png">
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
.about_form
{
	display:flex;
	justify-content:space-between;
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
           Purchase 
          </h1>
          <ol class="breadcrumb">
            <li><a href="dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Purchase Info</li>
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
							<h3 class="box-title">All Farmers</h3>
						</div>
					<?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_purchase')){ ?>
	                    <div class="col-sm-4">
							<button class="btn btn-sm btn-success pull-right" data-toggle="modal" data-target="#addCustomer">Add Farmer</button>
						</div>
					<?php } ?>
					</div>

					<hr />

					<form class="" action="purchase_info.php" name="search_frm" id="search_frm" method="get">
						<div class="form-group col-sm-3">
							<label for="search_farmer">Farmer Name<a href="javascript:void(0);" class="text-primary" data-toggle="popover"><i class="fa fa-info-circle"></i></a></label>
						  	<input type="text" class="form-control farmer-autocomplete" placeholder="Name or Code or Mobile" data-for-id="search_farmer" name="search_farmer">
							<input type="hidden" name="search_farmer" id="search_farmer">

							<?php /*<input type="text" class="form-control" placeholder="Search Name" name="search_customer" id="search_customer">*/ ?>
						</div>

						<div class="form-group col-sm-3">
							<label for="search_village">Village<a href="javascript:void(0);" class="text-primary" data-toggle="popover"></a></label>

						  	<input type="text" class="form-control farmer-autocomplete" placeholder="Village Name" data-for-id="search_village">
						</div>

						<div class="form-group col-sm-3">
							<label for="search_customer">Broker<a href="javascript:void(0);" class="text-primary" data-toggle="popover"></a></label>

						  	<input type="text" class="form-control farmer-autocomplete" placeholder="Broker Name" data-for-id="search_broker">
						</div>

						<button type="submit" name="search" value="Search" class="btn btn-primary" style="margin-top: 24px;"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
					</form>
                    <div class="col-sm-12">
                        <a href="farmer_excel_export.php?farmer=<?php echo isset($_GET['farmer'])?$_GET['farmer']:'';?>&search=Search" class="btn btn-sm btn-success pull-right"><i class="fa fa-file-excel-o"></i> Excel Export</a>
                    </div>
				</div>
                <div class="box-body no-padding">
                	<div class="table-responsive">
					 <table class="table table-striped table-hover table-bordered">
	                    <tr>
	                      <th>Sl No.</th>
						  <th>Details</th>
						  <th>Other Details</th>
						  <th>Area Details</th>
						  <th>Registry Details</th>
						  <?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = ''))/*view_customer*/ {?>
	                      <th>Action</th>
	                  <?php } ?>
						</tr>
						<?php
						$query = "select * from kc_farmers where status = '1'";

						if( isset($_GET['search_farmer']) || isset($_GET['search_village']) || isset($_GET['search_broker'])){
							//$query .= " and name LIKE '%".$_GET['search_farmer']."%'";
							if(!ctype_digit($_GET['search_farmer'])){
								$query .= " and name LIKE '%".$_GET['search_farmer']."%'";
							}else{
								$query .= " and id = '".$_GET['search_farmer']."'";
							}
							$url .= '&search_farmer='.$_GET['search_farmer'];
						}
					
			

						$quy=mysqli_query($conn,$query);
						$total_records = mysqli_num_rows($quy);
						$total_pages = ceil($total_records/$limit);

						if($page == 1){
							$start = 0;
						}else{
							$start = ($page-1)*$limit;
						}
						$query .= " limit $start,$limit";
						// echo $query;
						$farmers = mysqli_query($conn,$query);
						if(mysqli_num_rows($farmers) > 0){
							$counter = $start + 1;
							while($farmer = mysqli_fetch_assoc($farmers)) { ?>
						
								<tr>
									<td><?php echo $counter; ?></td>
									<td >
										<strong><?php echo $farmer['name_title']; ?> <?php echo $farmer['name'].' ('.farmerID($farmer['id']).')'; ?></strong><br>
	                                    <strong><?php echo $farmer['parent_name_relation']; ?></strong> <?php if($farmer['parent_name'] != ''){ ?>of <strong><?php echo isset($farmer['parent_name_sub_title'])?$farmer['parent_name_sub_title']:''; ?> <?php echo $farmer['parent_name']; } ?></strong><br>
										<!-- Purchaser:<br> <strong><?php echo $farmer ['purchaser_name'];?></strong> -->
	                                </td>
	                                <td>
	                                    Mobile: <strong><?php echo $farmer['mobile']; ?></strong><br>
	                                    Village: <strong><?php echo $farmer['village']; ?></strong><br>
	                                    Broker: <strong><?php echo $farmer['broker'];?></strong><br>
										Purchaser: <strong><?php echo $farmer ['purchaser_name'];?></strong>
	                                </td>
	                                <td nowrap="nowrap">
                                        In Hectare: <strong><?php echo $farmer['area_hectare']; ?></strong><br>
                                        <!-- In Sq.Ft: <strong><?php echo $farmer['area_sqft']; ?></strong><br> -->
                                        Per Biswa: <strong><?php echo $farmer['per_biswa']; ?></strong><br>
										Total Area Biswa: <strong><?php echo $farmer['total_area_biswa']; ?></strong><br>
	                                </td>
									<td nowrap="nowrap">
										Date: <strong><?php echo $farmer['registry_date']; ?></strong><br>
										Khasra No.: <strong><?php echo $farmer['khashra_no']; ?></strong><br>
										Purchase Value: <strong><?php echo $farmer['purchase_value']; ?></strong><br>
										Maliyat Value: <strong><?php echo $farmer['maliyat_value']; ?></strong><br>
										Stamp Fee: <strong><?php echo $farmer['stamp_fee']; ?></strong>
									</td>

	                                <td nowrap="nowrap">

                                        <button class="btn btn-xs btn-success" onClick="addTransaction(<?php echo $farmer['id']; ?>);" data-toggle="tooltip" title="Add Transaction"><i class="fa fa-money"></i></button>

                                        <button class="btn btn-xs btn-warning" onClick="getTransactions(<?php echo $farmer['id']; ?>);" data-toggle="tooltip" title="View Transactions"><i class="fa fa-money"></i></button>

										<?php if(!($farmer['registry_date']!='' || $farmer['khashra_no']!='' || $farmer['purchase_value']!='' || $farmer['maliyat_value']!='' ||  $farmer['stamp_fee']!='')) { ?>
                                        <button class="btn btn-xs btn-danger" onClick="registry(<?php echo $farmer['id']; ?>);" data-toggle="tooltip" title="Registry"><i class="fa fa-plus"></i></button>
										<?php }
										else{?>
										<button class="btn btn-xs btn-warning" type="button" data-toggle="tooltip" title="Edit registry" onclick = "editRegistry(<?php echo $farmer['id']; ?>);"><i class="fa fa-pencil"></i></button>
										<?php }?>


                                        <?php /*<a class="btn btn-xs btn-danger" target="_blank" href="allotment_letter.php?cb=<?php echo $block['id']; ?>" data-toggle="tooltip" title="Print Allotment"><i class="fa fa-print"></i></a>*/ ?>

	                                	<?php  if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'view_farmer')){ ?>
	                                	<button class="btn btn-xs btn-info" type="button" data-toggle="tooltip" title="View Farmer's Information" onclick = "viewInformation(<?php echo $farmer['id']; ?>);"><i class="fa fa-eye"></i></button>
	                                <?php } if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'edit_customer_customer')){ ?>
	                                	<button class="btn btn-xs btn-warning" type="button" data-toggle="tooltip" title="Edit Customer's Information" onclick = "editInformation(<?php echo $farmer['id']; ?>);"><i class="fa fa-pencil"></i></button>
	                                <?php }  ?>
                                        <a href="purchase_info.php?deleteInfo=<?php echo $farmer['id']; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure');"><i class="fa fa-trash "></i></a>
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
		<div class="modal-content">
			<form action="<?php echo $page_url; ?>" name="add_customer_frm" id="add_customer_frm" method="post" class="form-horizontal">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add Farmer</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-header with-border">
						<div class="col-md-12 about_form">
							<h3 class="box-title">Add Farmer Panel</h3>
							<h3 class="box-title" style="margin-right:14.5em;">Account Details</h3>
						</div>
					</div><!-- /.box-header -->
					<!-- form start -->
					<div class="box-body">
						<div class="row">
							<div class="col-md-7">
								<div class="form-group">
									<label for="name" class="col-sm-4 control-label">Name <span class="text-danger">*</span></label>
									<div class="col-sm-8">
										<select class="form-control" name="name_title" id="name_title" style="width:20%;float:left;" >
											<option value="Mr.">Mr.</option>
											<option value="Mrs.">Mrs.</option>
											<option value="Ms.">Ms.</option>
											<option value="Dr.">Dr.</option>
											<option value="M/s.">M/s.</option>
										</select>
										<input type="text" class="form-control col-sm-8" id="name" name="name" style="width:80%;"  >
									</div>
								</div>

								<div class="form-group">
									<label for="parent_name_relation" class="col-sm-4 control-label"><input type="radio" value="S" name="parent_name_relation" data-validation="required" data-validation-error-msg="required">S/<input type="radio" value="C" name="parent_name_relation" data-validation="required" data-validation-error-msg="required">C/<input type="radio" value="W" name="parent_name_relation" data-validation="required" data-validation-error-msg="required">W/<input type="radio" value="D" name="parent_name_relation" data-validation="required" data-validation-error-msg="required">D of<span class="text-danger"> *</span></label>
									<div class="col-sm-8">
										<select class="form-control" name="parent_name_sub_title" id="parent_name_sub_title" style="width:20%;float:left;" data-validation="required">
											<option value="Mr.">Mr.</option>
											<option value="Mrs.">Mrs.</option>
											<option value="Ms.">Ms.</option>
											<option value="Dr.">Dr.</option>
											<option value="M/s.">M/s.</option>
										</select>
										<input type="text" class="form-control col-sm-8" id="parent_name" name="parent_name" style="width:80%;" data-validation="required"/>
									</div>
								</div>

								<div class="form-group">
									<label for="village" class="col-sm-4 control-label">Village <span class="text-danger">*</span></label>
									<div class="col-sm-8">
										<input type="text" class="form-control" id="village" name="village">
									</div>
								</div>

								<div class="form-group">
									<label for="mobile" class="col-sm-4 control-label">Mobile <span class="text-danger">*</span></label>
									<div class="col-sm-8">
										<input type="text" class="form-control" id="mobile" name="mobile"  >
									</div>
								</div>

								<div class="form-group">
									<label for="purchaser" class="col-sm-4 control-label">Purchaser Name <span class="text-danger">*</span></label>
									<div class="col-sm-8">
										<input type="text" class="form-control" id="purchaser" name = "purchaser" data-validation="required" >
									</div>
								</div>
						
								<div class="form-group">
									<label for="khasra_no" class="col-sm-4 control-label">Khasra No <span class="text-danger">*</span></label>
									<div class="col-sm-8">
										<input type="text" class="form-control" name="khasra_no" id="khasra_no" data-validation="required">
									</div>
								</div>
							
								<div class="form-group">
									<label for="area_in_hectare" class="col-sm-4 control-label">Total Area In Hectare <span class="text-danger">*</span></label>
									<div class="col-sm-8">
										<input type="text" class="form-control" name="area_in_hectare" id="ar_hectare" data-validation="required">
									</div>
								</div>

								 <!-- <div class="form-group"> --> 
									<!-- <label for="area_in_sqft" class="col-sm-3 control-label">Total Area In Sq.Ft <span class="text-danger">*</span></label> -->
									<!-- <div class="col-sm-8"> -->
										<!-- <input type="text" class="form-control" name="area_in_sqft" id="ar_sqft" data-validation="required"> -->
									<!-- </div> -->
								<!-- </div>  -->

								<div class="form-group">
									<label for="amount" class="col-sm-4 control-label">Rate per Biswa <span class="text-danger">*</span></label>
									<div class="col-sm-8">
										<input type="text" class="form-control" name="amount" id="amount" autocomplete="off" data-validation="required" data-validation-allowing="range[1;10000]">
									</div>
								</div>

								<div class="form-group">
									<label for="area_in_biswa" class="col-sm-4 control-label">Total Area In Biswa <span class="text-danger">*</span></label>
									<div class="col-sm-8">
										<input type="text" class="form-control" name="area_in_biswa" id="ar_biswa" data-validation="required">
									</div>
								</div>

								<div class="form-group">
									<label for="payable_amount" class="col-sm-4 control-label">Total Plot Value(INR) <span class="text-danger">*</span></label>
									<div class="col-sm-8">
										<input type="text" class="form-control" id="payable_amount"  name="payable_amount" data-validation="number" data-validation-allowing="range[1;1000000000]">
									</div>
								</div>

								<div class="form-group">
									<label for="customer_paid_amount" class="col-sm-4 control-label">Broker <span class="text-danger"> *</span></label></label>
									<div class="col-sm-8">
										<input type="text" class="form-control" placeholder="Enter Broker Name" name="broker">
									</div>
								</div>
								<div class="form-group">
									<label for="seller_paid_amount" class="col-sm-4 control-label">Seller <span class="text-danger"> *</span></label></label>
									<div class="col-sm-8">
										<input type="text" class="form-control" placeholder="Enter Seller Name" name="seller">
									</div>
								</div>

								<div class="form-group">
									<label for="send_message" class="col-sm-4 control-label">Send Message</label>
									<div class="col-sm-8">
										<input type="checkbox" name="send_message" id="send_message" class="form-control" />
									</div>
								</div>
							</div>
							<div class="col-md-5">
								
								<div class="form-group">
									<label for="village" class="col-sm-4 control-label">Account No.</label>
									<div class="col-sm-8">
										<input type="text" class="form-control" id="farmers-account" name="farmersaccount">
									</div>
								</div>
								<div class="form-group">
									<label for="village" class="col-sm-4 control-label">Bank Name</label>
									<div class="col-sm-8">
										<input type="text" class="form-control" id="farmers-bankname" name="farmersbankname">
									</div>
								</div>
								<div class="form-group">
									<label for="village" class="col-sm-4 control-label">Branch</label>
									<div class="col-sm-8">
										<input type="text" class="form-control" id="farmers-branch" name="farmersbranch">
									</div>
								</div>
								<div class="form-group">
									<label for="village" class="col-sm-4 control-label">IFSC Code</label>
									<div class="col-sm-8">
										<input type="text" class="form-control" id="farmers-ifsccode" name="farmersifsccode">
									</div>
								</div>
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







                        <?php /*?>

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
							<input type="text" class="form-control" id="customer_paid_date" name="paid_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" data-validation="date" data-validation-format="dd-mm-yyyy" data-validation-depends-on="payment_type">
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
							<input type="text" class="form-control" id="next_due_date" name="next_due_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask=""  data-validation="date" data-validation-format="dd-mm-yyyy">
						  </div>
						</div>

                        <div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">Registry of Plot?</label>
						  <div class="col-sm-8">
							<select class="form-control" id="registry" name="registry" required>
                            	<option value="">Select</option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
						  </div>
						</div><?php */?>



						<?php /*<div class="form-group">
						  <label for="sales_person" class="col-sm-3 control-label">Register By<span class="text-danger">*</span></label>
						  <div class="col-sm-8">
						  		<input type="text" class="form-control" name="register_by" data-validation="required">

						  </div>
						</div>*/?>



					<!-- Registery Modal -->
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
							<input type="hidden" name="registry_farmer_id" id="registry_farmer_id">
                            <!-- <input type="hidden" name="registry_block_id" id="registry_block_id">
                            <input type="hidden" name="registry_block_number_id" id="registry_block_number_id"> -->
						  </div>
						</div>

						<!-- <div class="form-group">
						  <label for="registry_by" class="col-sm-3 control-label">Registry By</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="registry_by" name="registry_by" data-validation="required">
						  </div>
						</div> -->

						<!-- <div class="form-group">
						  <label for="khasra_no" class="col-sm-3 control-label">Purchase Date</label>
						  <div class="col-sm-8">
							<!-- <input type="text" class="form-control" id="Purchase Date" name="Purchase Date<" data-validation="required" data-validation-format="dd-mm-yyyy"> -->

							<!-- <input type="text" class="form-control" id="Purchase Date" name="purchase date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" data-validation="birthdate" data-validation-format="dd-mm-yyyy">

						  </div>
						</div> --> 

						<div class="form-group">
						  <label for="khasra_no" class="col-sm-3 control-label">Khasra Number</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="khasra_no" name="khashra_no" data-validation="required">
						  </div>
						</div>

						<div class="form-group">
						  <label for="purchase_value" class="col-sm-3 control-label">Purchase Value</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="purchase_value" name="purchase_value" data-validation="required">
						  </div>
						</div>

						<div class="form-group">
						  <label for="maliyat_value" class="col-sm-3 control-label">Maliyat Value</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="maliyat_value" name="maliyat_value" data-validation="required">
						  </div>
						</div>

						<div class="form-group">
						  <label for="sale_value" class="col-sm-3 control-label">Stamp Fee</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="stamp_fee" name="stamp_fee" data-validation="required">
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

	<!--Edit Registery Modal -->
	<div class="modal" id="editRegistryModal">
		<div class="modal-dialog">
			<div class="modal-content"> 
				<form  action="<?php echo $page_url; ?>" name="edit_registry_frm" id="edit_registry_frm" method="post" class="form-horizontal dropzone">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Edit Registry Information</h4>
				</div>
				<div class="modal-body">
					<div class="box box-info">
						<div class="box-header with-border">
							<div class="col-md-12">
								<h3 class="box-title">Edit Registry Panel</h3>
							</div>
						</div><!-- /.box-header -->
						<!-- form start -->
						<div class="box-body" id="editRegistryContainer">
						</div><!-- /.box-body -->
	  
						</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="editRegistry">Save changes</button>
			  </div>
		  </form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div>






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
							<input type="text" class="form-control" data-validation="required number" id="ab_payable_amount" name="ab_payable_amount">
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
						  <label for="transaction_remarks" class="col-sm-3 control-label">Remarks</label>
						  <div class="col-sm-8">
							<textarea class="form-control" id="at_transaction_remarks" name="transaction_remarks"></textarea>
						  </div>
						</div>
                        <div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">Next Due Date</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="at_next_due_date" name="next_due_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" >


                            <input type="hidden" name="farmer_id" id="farmer_id">
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
				<h4 class="modal-title">Edit Farmer Information</h4>
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
				<h4 class="modal-title">Farmer Information</h4>
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
							<input type="hidden" name="cancel_farmer_id" id="cancel_farmer_id">
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

     <!--registryModal -->
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
							<input type="hidden" name="registry_farmer_id" id="registry_farmer_id">
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

						<?php /*<div class="form-group">
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
						</div>*/?>
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

	<?php require('../includes/common-js.php'); ?>

    <script type="text/javascript">
    $(function(){
    	$("#cancelTransactionBack").click(function(){
    		$("#cancelTransaction").modal('hide');
    		getTransactions($("#cancel_farmer_id").val());
    	});
    });
    function cancelTransaction(transaction,farmer){
    	if(confirm('Are you sure you want to cancel this transaction?')){
    		$("#cancel_transaction_id").val(transaction);
    		$("#cancel_farmer_id").val(farmer);
    		$("#viewTransaction").modal('hide');
    		$("#cancelTransaction").modal('show');
    	}
    }

    function registry(farmer){
    	$("#registry_farmer_id").val(farmer);
		$("#registryModal").modal('show');
   
	
	}

	function editRegistry(farmerid){
		// if(farmerid !='')
		// {					
			$.ajax({
				url: '../dynamic/editRegistryDetails.php',
				type:'post',
				data:{farmer:farmerid},
				async:false,
				success: function(data){
					$("#editRegistryContainer").html(data);
					$("[data-mask]").inputmask();
					$("#editRegistryModal").modal('show');
				}					
			});
		}
	
	// }
	function addLatePayment(customer,block,block_number,next_due_date){
		$("#late_customer_id").val(customer);
		$("#late_block_id").val(block);
		$("#late_block_number_id").val(block_number);
		$("#late_next_due_date").val(next_due_date);
		$("#addLatePayment").modal('show');
	}

	function addTransaction(farmer){
		$("#farmer_id").val(farmer);
        $("#at_next_due_date").val('').removeAttr('readonly','readonly');
		$("#addTransaction").modal('show');
	}

	function getTransactions(farmer){
		$.ajax({
			url: '../dynamic/getFarmerTransactions.php',
			type:'post',
			data:{farmer:farmer},
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

	function editInformation(farmer){
		$.ajax({
			url: '../dynamic/getFarmerInformation.php',
			type:'post',
			data:{farmer:farmer},
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
	
	function viewInformation(farmer){
		$.ajax({
			url: '../dynamic/viewFarmer.php',
			type:'post',
			data:{farmer:farmer},
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
						// alert('Same Name and DOB Already Exists!');
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

    $('#ar_hectare').on('keyup', function(){
        let hectare = $('#ar_hectare').val();
        $('#ar_sqft').val(107639*hectare);
        $('#ar_biswa').val(79.732668*hectare);
    });

    $('#ar_sqft').on('keyup', function(){
        let sqft = $('#ar_sqft').val();
        $('#ar_hectare').val(0.000009290303997*sqft);
        $('#ar_biswa').val(0.0007*sqft);
    });

    $('#ar_biswa').on('keyup', function(){
        let biswa = $('#ar_biswa').val();
        $('#ar_hectare').val(0.0125*biswa);
        // $('#ar_sqft').val(1350*biswa);
    });

	$('#amount').on('keyup', function(){
		let biswa = $('#ar_biswa').val();
        let amount = $('#amount').val();
		var total = Math.ceil(biswa * amount);
        $('#payable_amount').val(total);
        // $('#ar_sqft').val(1350*biswa);
    });

	$('#mobile').on('keyup',function(){
		$('#name , #name_title').prop('required',false);
	});



	</script>

  </body>
</html>

