<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");
require("../includes/sendMessage.php");
if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_cheque_report'))){ 
 	header("location:/wcc_real_estate/index.php");
 	exit();
}

$url = 'cheque_report.php?search=Search';
// print_r($_SERVER);die;
$limit = 100;
if(isset($_GET['page'])){
	$page = $_GET['page'];
}else{
	$page = 1;
}

$page_url = $url.'&page='.$page;
// $uri = explode('?', $_SERVER['REQUEST_URI'])[1];

if(isset($_GET['search_cheque_dd_number'])){
	$page_url .="&search_cheque_dd_number=".$_GET['search_cheque_dd_number'];
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


$query =  "SELECT ct.id as customer_block_id, ct.id, ct.customer_id, ct.bank_name,ct.remarks, ct.cheque_dd_number, ct.amount, ct.paid_date, ct.block_id, ct.block_number_id, b.project_id, b.name as block_name, bn.block_number as block_number_name, bn.area, p.name as project_name, c.name_title as customer_name_title, c.name as customer_name, c.mobile as customer_mobile, c.address as customer_address from kc_customer_transactions ct LEFT JOIN kc_blocks b ON ct.block_id = b.id LEFT JOIN kc_block_numbers bn ON ct.block_number_id = bn.id LEFT JOIN kc_projects p ON b.project_id = p.id LEFT JOIN kc_customers c ON ct.customer_id = c.id  where ct.status = '1' AND ct.payment_type  = 'Cheque' AND ct.clear_date is null ";

if(isset($_GET['search_cheque_dd_number']) && $_GET['search_cheque_dd_number']>0){
	$check_no= $_GET['search_cheque_dd_number'];
	$query .= " and ct.cheque_dd_number LIKE '%" .$check_no."%'";
	
	// echo "$query";die;
	$url .= '&search_cheque_dd_number='.$_GET['search_cheque_dd_number'];
}


// print_r($_GET);die;


if(isset($_GET['search_customer']) && $_GET['search_customer'] != ''){
	//$query .= " and name LIKE '%".$_GET['search_customer']."%'";
	
	if(!ctype_digit($_GET['search_customer'])){
		$query .= " and c.name LIKE '%".$_GET['search_customer']."%' ";
		
	}else{
		$query .= " and ct.customer_id = '".$_GET['search_customer']."'";
		
	}
	$url .= '&search_customer='.$_GET['search_customer'];
}




if(isset($_GET['search_project']) && (int) $_GET['search_project'] > 0){
	$project_id = (int) $_GET['search_project'];
	$query .= " and ct.customer_id IN (SELECT customer_id from kc_customer_blocks WHERE block_id IN (SELECT id FROM kc_blocks WHERE project_id = '$project_id') )";
	// echo "$query";die;
	$url .= '&search_project='.$_GET['search_project'];
}
if(isset($_GET['search_block']) && (int) $_GET['search_block'] > 0){
	$block_id = (int) $_GET['search_block'];
	$query .= " and ct.customer_id IN (select customer_id from kc_customer_blocks where status = '1' and block_id = '$block_id' )";
	$url .= '&search_block='.$_GET['search_block'];
}
if(isset($_GET['search_block_no']) && (int) $_GET['search_block_no'] > 0){
	$block_number_id = (int) $_GET['search_block_no'];
	$query .= " and ct.customer_id IN (select customer_id from kc_customer_blocks where status = '1' and block_number_id = '$block_number_id' )";
	$url .= '&search_block_no='.$_GET['search_block_no'];
}
if(isset($_GET['from_date']) && (int) $_GET['to_date'] > 0){
	$newDate = date("Y-m-d", strtotime($_GET['to_date']));
	$newDate2 = date("Y-m-d", strtotime($_GET['from_date']));
	//  $block_number_id = (int) $_GET['to_date'];
	$query .= " and ct.customer_id IN (select customer_id from kc_customer_blocks where status = '1' AND ct.paid_date   BETWEEN  '".$newDate2."' AND  '".$newDate."' )";
	$url .= '&from_date='.$newDate2. '&to_date='.$newDate;

	
	// print_r($query);die;
}


if(isset($_POST['cancelTransaction'])){ 
	// echo "<pre>"; print_r($_POST); die;	
	$transaction_id = isset($_POST['cancel_transaction_id'])?(int) $_POST['cancel_transaction_id']:0;
	$cancel_remarks = isset($_POST['cancel_remarks'])?trim($_POST['cancel_remarks']):'';

	$transaction_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, customer_id, block_id, block_number_id from kc_customer_transactions where id = '".$transaction_id."' limit 0,1 "));

	$customer_id = isset($_POST['cancel_customer_id'])?(int)$_POST['cancel_customer_id']:0;
 	// print_r($customer_id); die();
	$block_id = isset($_POST['cancel_block_id'])?(int)$_POST['cancel_block_id']:0;
	$block_number_id = isset($_POST['cancel_block_number_id'])?(int) $_POST['cancel_block_number_id']:0;


	$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, project_id, name from kc_blocks where id = '".$block_id."' limit 0,1 "));
	$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, block_number from kc_block_numbers where id = '$block_number_id' limit 0,1 "));


	$amount = $_POST['amount'];
	$remarks = $_POST['cancel_remarks'];
	$send_message = isset($_POST['send_message'])?true:false;
	$apply_bonuce_charges = isset($_POST['apply_bonuce_charges'])?true:false;

	if(!isset($transaction_details['id'])){
		$_SESSION['error'] = 'Transaction not Found!';
	}else if($cancel_remarks == ""){
		$_SESSION['error'] = 'Cancel Remarks is required!';
	}else{
		$error = false;
		mysqli_autocommit($conn,FALSE);


	if($apply_bonuce_charges){
		if($amount == ""){
			$_SESSION['error'] = 'Amount is required after applying bounce charge.';
			header("Location:".$page_url);
			die();
		}else{
			if (!mysqli_query($conn,"INSERT into kc_customer_transactions_hist (kc_customer_transactions_id, customer_id, block_id, block_number_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, next_due_date, late_for_transaction_id, is_affect_sold_amount, status, remarks, add_transaction_remarks, cancel_remarks, clear_remarks, clear_date, paid_account_no, action_type, addedon, added_by, deleted_by) select id, customer_id, block_id, block_number_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, next_due_date, late_for_transaction_id, is_affect_sold_amount, status, remarks, add_transaction_remarks, '$cancel_remarks', clear_remarks, clear_date, paid_account_no, 'Payment Cancelled', addedon, added_by, '".$_SESSION['login_id']."' from kc_customer_transactions where id = '$transaction_id'")){
				$error = true;
				echo("Error description: " . mysqli_error($conn)); die;
			}

			$last_transaction_hist_id = mysqli_insert_id($conn);

			if(!$error && !mysqli_query($conn,"INSERT into kc_customer_transactions set customer_id = '$customer_id', block_id = '$block_id', block_number_id = '$block_number_id', amount = '$amount',next_due_date = '2000-01-01', cr_dr = 'cr', late_for_transaction_id = '$last_transaction_hist_id', status = '1', remarks = '$remarks', addedon ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."'")){
				$error = true;
			}
		}
		

		 if($send_message){

		 	$customer_tnx_id = mysqli_fetch_assoc(mysqli_query($conn,"SELECT cheque_dd_number from kc_customer_transactions where id = '".$transaction_id."'"));
		// print_r($customer_tnx_id);
			$customer_mobile = mysqli_fetch_assoc(mysqli_query($conn,"SELECT mobile from kc_customers where id = '".$customer_id."'"));
			$mobile = $customer_mobile['mobile'];
		 	$variables_array = array('variable1'=>$amount = $_POST['amount'],'variable2'=>$customer_tnx_id['cheque_dd_number']);
		 	if(sendMessage($conn,24,$mobile,$variables_array)){
		 		$_SESSION['success'] .= ' and Welcome Message sent Successfully!';
		 	}else if(!isset($_SESSION['error'])){
		 		$_SESSION['error'] = 'Welcome Message not sent!';
		 	}else if(isset($_SESSION['error'])){
		 		$_SESSION['error'] .= ' and Welcome Message not sent!';
		 	}
		 }
	}
	 else{
		if (!mysqli_query($conn,"INSERT into kc_customer_transactions_hist (kc_customer_transactions_id, customer_id, block_id, block_number_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, next_due_date, late_for_transaction_id, is_affect_sold_amount, status, remarks, add_transaction_remarks, cancel_remarks, clear_remarks, clear_date, paid_account_no, action_type, addedon, added_by, deleted_by) select id, customer_id, block_id, block_number_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, next_due_date, late_for_transaction_id, is_affect_sold_amount, status, remarks, add_transaction_remarks, '$cancel_remarks', clear_remarks, clear_date, paid_account_no, 'Payment Cancelled', addedon, added_by, '".$_SESSION['login_id']."' from kc_customer_transactions where id = '$transaction_id'")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}

		$last_transaction_hist_id = mysqli_insert_id($conn);
	 }

	
	

	

	if(!$error && !mysqli_query($conn,"delete from kc_customer_transactions where id = '".$transaction_id."'")){
		$error = true;
		echo("Error description: " . mysqli_error($conn)); die;
	}
	if (!mysqli_query($conn,"insert into kc_associate_transactions_hist (customer_id, kc_associate_transactions_id, associate_id, block_id, block_number_id, transaction_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, status, remarks, cancel_remarks, action_type, addedon, added_by, deleted_by) select id, customer_id, associate_id, block_id, block_number_id, transaction_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, status, remarks, '$cancel_remarks', 'Payment Cancelled', addedon, added_by, '".$_SESSION['login_id']."' from kc_associates_transactions where transaction_id = '$transaction_id'")){
		$error = true;
		echo("Error description: " . mysqli_error($conn)); die;
	}
	if(!$error && !mysqli_query($conn,"delete from kc_associates_transactions where transaction_id = '".$transaction_id."'")){
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
		$_SESSION['success'] = 'Cheque has been cancelled Successfully.';
	}
	mysqli_close($conn);
}
	
	header("Location:".$page_url);
	exit();
}




if(isset($_POST['markClear'])){

	// echo "<pre>"; print_r($_POST); die;
	$transaction_id = isset($_POST['cancel_transaction_id'])?(int) $_POST['cancel_transaction_id']:0;
	$clear_remarks = isset($_POST['clear_remarks'])?trim($_POST['clear_remarks']):'';
	$clear_date = isset($_POST['clear_date']);
	$paid_account_no = isset($_POST['paid_account_no']);
	// echo "<pre>";print_r($paid_account_no);die;
	$transaction_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, customer_id, block_id, block_number_id from kc_customer_transactions where id = '".$transaction_id."' limit 0,1 "));

	if(!isset($transaction_details['id'])){
		$_SESSION['error'] = 'Transaction not Found!';
	}else if($clear_date == ""){
		$_SESSION['error'] = 'Clear Date is required!';
	}else if($paid_account_no == ""){
		$_SESSION['error'] = 'Account No is required!';
	}else{
	$error = false;
	mysqli_autocommit($conn,FALSE);


	// echo "update kc_customer_transactions set clear_remarks = '$clear_remarks', clear_date = '".$_POST['clear_date']."', paid_account_no = '".$_POST['paid_account_no']."' where id = '$transaction_id'"; die;		// $error = true;
	
	if (!mysqli_query($conn,"update kc_customer_transactions set clear_remarks = '$clear_remarks', clear_date = '".$_POST['clear_date']."', paid_account_no = '".$_POST['paid_account_no']."' where id = '$transaction_id'")){
		$error = true;
		echo("Error description: " . mysqli_error($conn)); die;
	}
	// echo "dfhdt"; die();
	// if(!$error && !mysqli_query($conn,"delete from kc_customer_transactions where id = '".$transaction_id."';")){
	// 	$error = true;
	// 	echo("Error description: " . mysqli_error($conn)); die;
	// }


	if (!mysqli_query($conn,"insert into kc_associate_transactions_hist (customer_id, kc_associate_transactions_id, associate_id, block_id, block_number_id, transaction_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, status, remarks, cancel_remarks, action_type, addedon, added_by, deleted_by) select id, customer_id, associate_id, block_id, block_number_id, transaction_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, status, remarks, '$cancel_remarks', 'Payment Cleared', addedon, added_by, '".$_SESSION['login_id']."' from kc_associates_transactions where transaction_id = '$transaction_id'")){
		$error = true;
		echo("Error description: " . mysqli_error($conn)); die;
	}
	// if(!$error && !mysqli_query($conn,"delete from kc_associates_transactions where transaction_id = '".$transaction_id."';")){
	// 	$error = true;
	// 	echo("Error description: " . mysqli_error($conn)); die;
	// }

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
		$_SESSION['success'] = 'Cheque has been cleared Successfully.';
	}
	mysqli_close($conn);
	}
	
	header("Location:".$page_url);
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
    <!-- Bootstrap 3.3.4 -->
    <link href="/<?php echo $host_name; ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- FontAwesome 4.3.0 -->
    <link href="/<?php echo $host_name; ?>/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Ionicons 2.0.0 -->
    <link href="/<?php echo $host_name; ?>/css/ionicons.min.css" rel="stylesheet" type="text/css" />
	<link rel="icon" type="image/x-icon" href="/<?php echo $host_name; ?>img/logo.png">
	<!-- Select2 -->
    <link href="/<?php echo $host_name; ?>/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    
    <!-- jQuery UI -->
    <link href="/<?php echo $host_name; ?>/css/jquery-ui.css" rel="stylesheet" type="text/css" />
	
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
    	.ui-autocomplete li{
    		font-family: 'Source Sans Pro','Helvetica Neue',Helvetica,Arial,sans-serif;
    		padding: 5px 8px;
    		font-weight: bold;
    	}
    	.ui-autocomplete li:hover{
    		background-color: #3c8dbc;
    		color: white;
    	}
    </style>
    <script>
    	function printDiv(divName) {
		     var printContents = document.getElementById(divName).innerHTML;
		     var originalContents = document.body.innerHTML;

		     document.body.innerHTML = printContents;

		     window.print();

		     document.body.innerHTML = originalContents;
		}
    </script>
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
            <li class="active">Cheque Payment Report</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
			<div class="box">
                <div class="box-header">
					<?php 
					include("../includes/notification.php"); ?>
					<div class="row">
						<div class="col-sm-10">
							<h3 class="box-title">Cheque Payment Report</h3>
						</div>
						<div class="col-sm-1">
								<a href="<?php echo str_replace("cheque_report","cheque_report_excel",$url) ?>" class="btn btn-sm btn-success pull-right"><i class="fa fa-file-excel-o"></i> Excel Export</a>
						</div>
						<?php /*<div class="col-sm-1">
								<a class="btn btn-sm btn-danger" href="javascript:void(0);" onclick="printDiv('printContent')" data-toggle="tooltip" title="Print"><i class="fa fa-print">&nbsp;</i>PDF Export</a>
						</div> */ ?>
	                    <?php /* <div class="col-sm-1 pull-right">
							<a href="cheque_cancel_report.php" class="btn btn-sm btn-success">Cancel Cheques</a>
						</div> */ ?>
					</div>
					<hr />

					<form class="" action="cheque_report.php" name="search_frm" id="search_frm" method="get">

						<div class="form-group col-sm-3">
							<lable for ="search_cheque_dd_number" ><b>Cheque No</b></lable>
							<input type="text" class ="form-control" id="search_cheque_dd_number" name="search_cheque_dd_number" placeholder=" Cheque No">
	                       </div>
						   
							<div class="form-group col-sm-3">
							<label for="search_customer">Customer <a href="javascript:void(0);" class="text-primary" data-toggle="popover" title="Customer Search Hint" data-content="'c-' for Code Search<br>'n-' for Name Search<br>'m-' for Mobile Search<br><b>Eg:</b> If want to search 00942 in only code then Search for 'c-00942' <br><br> <b>OR</b> <br> <br> You can search similar name by Pressing 'Enter Key'"><i class="fa fa-info-circle"></i></a></label>
						  	
						  	<input type="text" class="form-control customer-autocomplete" placeholder="Name or Code or Mobile" data-for-id="search_customer">
							<input type="hidden" name="search_customer" id="search_customer">

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
						    </select>
						</div>
						
						
						<div class="form-group col-sm-3">
							<label for="search_block_no">Plot Number</label>
							<select class="form-control" id="search_block_no" name="search_block_no">
						        <option value="">Select Plot Number</option>
						    </select>
						</div>


						<div class="form-group col-sm-3">
							<label for="from_date">From</label>
						  	<input type="text" class="form-control" id="from_date" name="from_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" />
						</div>
						<div class="form-group col-sm-3">
							<label for="to_date">To</label>
							<input type="text" class="form-control" id="to_date" name="to_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" class="form-control" />
						</div>

						<button type="submit" name="search" value="Search" class="btn btn-primary" style="margin-top: 24px;"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
					</form>
					
				</div><!-- /.box-header --><br>
				
                <div class="box-body no-padding" id="printContent">
				 <div class="table-responsive">
					 <table class="table table-striped table-hover table-bordered">
	                    <tr>
							<th>Sr.</th>
							<th>Project Details</th>
							<th>Customer Details</th>
							<th>Amount</th>
							<th>Paid Date</th>
							<th>Bank Detail</th>
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
							
							$query .= " order by id desc limit $start,$limit";

							$customers = mysqli_query($conn,$query);
							if(mysqli_num_rows($customers) > 0){
								$counter = $start+1;
								$total_debited_amt = $total_credited_amt = $total_pending_amt = 0;
								while($customer = mysqli_fetch_assoc($customers)){
									// echo "<pre>"; print_r($customer); die;
									$total_debited_amt += $total_debited = totalDebited($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']);
									$total_credited_amt += $total_credited = totalCredited($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']);
									
									$total_pending_amt += $pending_amount = ($total_credited - $total_debited);
									?>
									<tr>
										<td><?php echo $counter; ?>.</td>
										<td>
											<strong>Project : </strong><?php echo $customer['project_name']; ?><br>
											<strong>Block : </strong><?php echo $customer['block_name']; ?><br>
											<strong>Plot No. : </strong><?php echo $customer['block_number_name']; ?>
												
										</td>

										<td>
											<strong>Name : </strong><?php echo ($customer['customer_name_title'].' ' .$customer['customer_name']).'<br>'.' ('.customerID($customer['customer_id']).')'; ?><br>
											<strong>Mobile : </strong><?php echo $customer['customer_mobile']; ?><br>
											<strong>Address : </strong><?php echo $customer['customer_address']; ?>
												
										</td>
										
										<td><?php echo $customer['amount']; ?></td>
										<td><?php echo $customer['paid_date'] ?></td>
										<td>
											<strong>Bank Name : </strong><?php echo $customer['bank_name']?$customer['bank_name']:'N/A'; ?><br>
											<strong>Cheque No : </strong><?php echo $customer['cheque_dd_number']?$customer['cheque_dd_number']:'N/A'; ?><br>
											<strong>Remarks : </strong><?php echo $customer['remarks']?$customer['remarks']:'N/A'; ?>
										</td>
										<td>
											<?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'cheque_cancel_cheque_report')){  ?>
											<a onclick="cancelTransaction('<?php echo $customer['id']; ?>','<?php echo customerID($customer['customer_id']); ?>','<?php echo $customer['block_id']; ?>','<?php echo $customer['block_number_id']; ?>');" href="javascript:void(0)" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Cancel Cheque"><i class="fa fa-remove"></i></a>
										<?php } if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'cheque_clear_cheque_report')){ ?>
											<a onclick="markClear('<?php echo $customer['id']; ?>','<?php echo customerID($customer['customer_id']); ?>','<?php echo $customer['block_id']; ?>','<?php echo $customer['block_number_id']; ?>');" class="btn btn-xs btn-success" data-toggle="tooltip" title="Mark Clear"><i class="fa fa-check"></i></a>
										<?php } ?>

										</td>
										
									</tr>
									<?php	
									$counter++;
								} ?>
								
								<?php
							}else{
								?>
								<tr>
									<td colspan="16" align="center"><h4 class="text-red">No Record Found</h4></td>
								</tr>
								<?php
							}
							?>
	                  </table>
              		</div>
                </div><!-- /.box-body -->
				
				<?php if(isset($total_pages) && $total_pages > 1){ ?>
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

  <div class="modal" id="cancelTransaction">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="<?php echo $page_url; ?>" name="add_late_payment_frm" id="add_late_payment_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Cancel Cheque</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-header with-border">
						<div class="col-md-12">
							<h3 class="box-title">Cancel Cheque Panel</h3>
						</div>
					</div><!-- /.box-header -->
					<!-- form start -->
					<div class="box-body">
						<div class="form-group">
						  <label for="cancel_remarks" class="col-sm-3 control-label">Remarks <sup class="text-danger">*</sup></label>
						  <div class="col-sm-8">
							<textarea class="form-control" id="cancel_remarks" name="cancel_remarks"></textarea>
							<input type="hidden" name="cancel_transaction_id" id="cancel_transaction_id">
							<input type="hidden" name="cancel_customer_id" id="cancel_customer_id">
                            <input type="hidden" name="cancel_block_id" id="cancel_block_id">
                            <input type="hidden" name="cancel_block_number_id" id="cancel_block_number_id">
						  </div>
						</div>
						<div class="form-group">
						  <label for="bonuce_charge" class="col-sm-3 control-label">Apply Bounce Charges</label>
						  <div class="col-sm-8">
						  	<input type="checkbox" id="check" name="apply_bonuce_charges" for="check">
						  </div>
						</div>
						<div class="apply_charges" style="display:none">

							<div class="form-group">
								<label for="amount" class="col-sm-3 control-label">Bounce Charge</label>
								<div class="col-sm-8">
									<input type="text" name="amount" id="amount" class="form-control" value="540" placeholder="Amount">
								</div>
							</div>
							<!-- <div class="form-group">
								<label for="remarks" class="col-sm-3 control-label">Remarks</label>
								<div class="col-sm-8">
									<input type="text" name="remarks" id="remarks" class="form-control" value="" readonly="readonly">
								</div>
							</div> -->
						
							<div class="form-group">
							    <label for="send_message" class="col-sm-3 control-label">Send Message</label>
							    <div class="col-sm-8">
							  	<input type="checkbox" id="send_message" name="send_message" class="form-control">
							  </div>
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


<!------------------------Mark Clear----------------------------------------->
	 <div class="modal" id="markClear">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="<?php echo $page_url; ?>" name="add_late_payment_frm" id="add_late_payment_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Cheque Mark Clear</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-header with-border">
						<div class="col-md-12">
							<h3 class="box-title">Mark Clear Panel</h3>
						</div>
					</div><!-- /.box-header -->
					<!-- form start -->
					<div class="box-body">
						<div class="form-group">
						  <label for="bank_name" class="col-sm-3 control-label">Cheque Clear Date<sup class="text-danger text-lg">*</sup></label>
						  <div class="col-sm-8">
							<input type="date" class="form-control" id="clear_date" name="clear_date" maxlength="255" required>
						  </div>
						</div>

						<div class="form-group">
						  <label for="paid_account_no" class="col-sm-3 control-label">Paid Account No.<sup class="text-danger text-lg">*</sup></label>
						  <div class="col-sm-8">
							<select class="form-control" name="paid_account_no" required>
								<option value="">Select Account No</option>
								<?php $account_no = mysqli_query($conn,"SELECT * FROM kc_accounts where status = 1 and deleted is null"); 
									while($account = mysqli_fetch_assoc($account_no)){
								?>
									<option value="<?php echo $account['id']; ?>"><?php echo $account['name']; ?> (<?php echo $account['account_no']; ?>)</option>
								<?php } ?>
							</select>
						  </div>
						</div>



						<div class="form-group">
						  <label for="clear_remarks" class="col-sm-3 control-label">Clear Remarks</label>
						  <div class="col-sm-8">
							<textarea class="form-control" id="clear_remarks" name="clear_remarks"></textarea>
							<input type="hidden" name="cancel_transaction_id" id="clear_transaction_id">
							<input type="hidden" name="cancel_customer_id" id="clear_customer_id">
                            <input type="hidden" name="cancel_block_id" id="clear_block_id">
                            <input type="hidden" name="cancel_block_number_id" id="clear_block_number_id">
						  </div>
						</div>
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="button" id="markClearBack" class="btn btn-info">Back</button>
				<button type="submit" class="btn btn-primary" id="markClearBtn" name="markClear">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
        </section> 
          
      </div><!-- /.content-wrapper -->    
      <?php require('../includes/footer.php'); ?>
    </div><!-- ./wrapper -->

    <?php require('../includes/common-js.php'); ?>
	<script type="text/javascript">
    $(function(){
    	$("#cancelTransactionBack").click(function(){
    		$("#cancelTransaction").modal('hide');
    		getTransactions($("#cancel_customer_id").val(),$("#cancel_block_id").val(),$("#cancel_block_number_id").val());
    	});
    });
   //  function cancelTransaction(transaction,customer,block,block_number){
   //  		$("#cancel_transaction_id").val(transaction);
   //  		$("#cancel_customer_id").val(customer);
			// $("#cancel_block_id").val(block);
			// $("#cancel_block_number_id").val(block_number);
   //  		$("#viewTransaction").modal('hide');
   //  		$("#cancelTransaction").modal('show');
   //  }

    function cancelTransaction(id,customer,block,block_number){
    		$("#cancel_transaction_id").val(id);
    		$("#cancel_customer_id").val(customer);
			$("#cancel_block_id").val(block);
			$("#cancel_block_number_id").val(block_number);
    		$("#viewTransaction").modal('hide');
    		$("#cancelTransaction").modal('show');
    	var cancelTransactionD = 'CancelledTransactionDetails';
		$.ajax({
			url: '../dynamic/applyBounceCharges.php',
			type:'post',
			dataType:'JSON',
			data:{CancelledTransactionDetails:cancelTransactionD,id:id,customer:customer, block:block, block_number:block_number},
			success:function(result){
				// alert('sdfhjk');
				$('#amount').val(result.amount);
				// $('#remarks').val(result.remarks);
				$("#cancelTransaction").modal('show');
			}
		})
    }

 //    function markClear(transaction,customer,block,block_number){
	// 	// alert(transaction);
	// 	// alert(customer);
	// 	// alert(block);
	// 	// alert(block_number);
	// 	$.ajax({
	// 		url: '../dynamic/chequeRecancellation.php',
	// 		type:'post',
	// 		data:{transaction:transaction,customer:customer,block:block,block_number:block_number},
	// 		success: function(resp){
	// 			$("#edit-account-container").html(resp);
	// 			$("#editAccountModal").modal('show');
	// 		}
	// 	});
	// }


	$(function(){
    	$("#markClearBack").click(function(){
    		$("#markClear").modal('hide');
    		getTransactions($("#cancel_customer_id").val(),$("#cancel_block_id").val(),$("#cancel_block_number_id").val());
    	});
    });
    function markClear(transaction,customer,block,block_number){
    		$("#clear_transaction_id").val(transaction);
    		$("#clear_customer_id").val(customer);
			$("#clear_block_id").val(block);
			$("#clear_block_number_id").val(block_number);
    		$("#viewTransaction").modal('hide');
    		$("#markClear").modal('show');
    	
    }
    function iCheckClicked(elem){
		var for_attr = $(elem).attr('for');
	    if(for_attr == "check"){
			if(!($(elem).is(":checked"))){
			 	$(".apply_charges").show();
			}else{
				$(".apply_charges").hide();
			}
		}
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
</script>
    
  </body>
</html>