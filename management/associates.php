<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");
if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_associate'))){
  header("location:/wcc_real_estate/index.php");
  exit();
}
$url = 'associates.php?search=Search';

$limit = 50;
if(isset($_GET['page'])){
	$page = $_GET['page'];
}else{
	$page = 1;
}

$page_url = $url.'&page='.$page;

if(isset($_GET['search_associate']) && (int) $_GET['search_associate'] > 0){
	$url .= '&search_associate='.$_GET['search_associate'];
	$page_url .= '&search_associate='.$_GET['search_associate'];
}


if(isset($_GET['export']) && $_GET['export'] == 'true'){
	

	header("Content-Type: application/xls");    
	header("Content-Disposition: attachment; filename=associates_".date('d-M-Y').".xls");  
	header("Pragma: no-cache"); 
	header("Expires: 0");
	
	echo '<table border="1">';
	
	echo '<tr><th>Sl No.</th><th>Code</th><th>Name</th><th>Mobile</th><th>Credit</th><th>Debit</th><th>Balance</th><th>Status</th><th>Added Date</th></tr>';
	
	$associates = mysqli_query($conn,"select id, code, name, mobile_no, status, addedon from kc_associates");
	$counter = 1;
	while($associate = mysqli_fetch_assoc($associates)){
		$credit = associateTotalCredited($conn,$associate['id']);
		$debit = associateTotalDebited($conn,$associate['id']);
		?>
		<tr>
			<td><?php echo $counter; ?></td>
			<td><?php echo $associate['code']; ?></td>
			<td><?php echo $associate['name']; ?></td>
            <td><?php echo $associate['mobile_no']; ?></td>
            <td><?php echo $credit ?> ₹</td>
            <td><?php echo $debit ?> ₹</td>
            <td><?php echo ($credit -  $debit); ?> ₹</td>
			<td>
            	<?php echo $associate['status']?'Active':'Inactive'; ?>
            </td>
            <td><?php echo date("d M Y h:i A",strtotime($associate['addedon'])); ?></td>
		</tr>
		<?php
		$counter++;
	}
	echo '</table>';
	die;
}


if(isset($_POST['addAssociate'])){
	
	$code = filter_post($conn,$_POST['code']);
	$name = filter_post($conn,$_POST['name']);
	$parent_id = filter_post($conn,$_POST['parent_id']);
	$mobile_no = (float) filter_post($conn,$_POST['mobile']);
	if($code == ''){
		$_SESSION['error'] = 'Associate Code was wrong!';
	}else if($name == ''){
		$_SESSION['error'] = 'Associate Name was wrong!';
	}else if($mobile_no == '' || strlen($mobile_no) != 10){
		$_SESSION['error'] = 'Mobile Number was wrong!';
	}else{
		
		$already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select id, code from kc_associates where mobile_no = '$mobile_no' or code = '$code' limit 0,1 "));
		if(isset($already_exits['id'])){
			if($code == $already_exits['code']){
				$_SESSION['error'] = 'Code Already Exists!';
			}else{
				$_SESSION['error'] = 'Mobile Number Already Exists!';
			}
		}else{
			mysqli_query($conn,"insert into kc_associates set code = '$code', name = '$name', mobile_no = '$mobile_no', password = '12345', status = '1', addedon ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."', last_login ='".date('Y-m-d H:i:s')."',parent_id='$parent_id' ");
			$relation_id = mysqli_insert_id($conn);
			if($relation_id > 0){
				mysqli_query($conn,"insert into kc_contacts set name = '$name', mobile = '$mobile_no', type = 'Associate', customer_id = '$relation_id', status = '1', created ='".date('Y-m-d H:i:s')."', created_by = '".$_SESSION['login_id']."' ");
				$_SESSION['success'] = 'Associate Successfully Added!';
				header("Location:$page_url");
				exit();
			}else{
				$_SESSION['error'] = 'Associate Name was wrong. Please Try Again!';
			}
		}
	}
						
}

if(isset($_POST['editInformation'])){
	//echo "<pre>"; print_r($_POST); die;
	$associateID = filter_post($conn,$_POST['associate_id']);
	$code = filter_post($conn,$_POST['code']);
	$name = filter_post($conn,$_POST['name']);
	$mobile_no = (float) filter_post($conn,$_POST['mobile']);
	if($associateID == ''){
		$_SESSION['error'] = 'Something was wrong!';
	}elseif($code == ''){
		$_SESSION['error'] = 'Associate Code was wrong!';
	}elseif($name == ''){
		$_SESSION['error'] = 'Associate Name was wrong!';
	}else if($mobile_no == '' || strlen($mobile_no) != 10){
		$_SESSION['error'] = 'Mobile Number was wrong!';
	}else{
		
		$already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select id, code from kc_associates where (mobile_no = '$mobile_no' or code = '$code') and id != '$associateID' limit 0,1 "));
		if(isset($already_exits['id'])){
			if($code == $already_exits['code']){
				$_SESSION['error'] = 'Code Already Exists!';
			}else{
				$_SESSION['error'] = 'Mobile Number Already Exists!';
			}
		}else{
			mysqli_query($conn,"update kc_associates set code = '$code', name = '$name', mobile_no = '$mobile_no', updated ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."' where id = '$associateID' ");
			$relation_id = mysqli_affected_rows($conn);
			if($relation_id > 0){
				mysqli_query($conn,"update kc_contacts set name = '$name', mobile = '$mobile_no' where customer_id = '$associateID' and type = 'Associate' ");
				$_SESSION['success'] = 'Associate Successfully Added!';
				header("Location:$page_url");
				exit();
			}else{
				$_SESSION['error'] = 'Associate Name was wrong. Please Try Again!';
			}
		}
	}
						
}

if(isset($_GET['associate']) && is_numeric($_GET['associate'])){
	$associate_id = $_GET['associate'];
	$associate_details = mysqli_fetch_assoc(mysqli_query($conn,"select status from kc_associates where id = '".$associate_id."' limit 0,1 "));
	// echo "<pre>";print_r($associate_details);die;
	if(isset($associate_details['status'])){
		$current_status = $associate_details['status'];
		if($current_status == 1){
			$new_status = 0;
		}else{
			$new_status = 1;
		}
		
		mysqli_query($conn,"update kc_associates set status = '$new_status' where id = '".$associate_id."' limit 1 ");
		$_SESSION['success'] = 'Associate Status Successfully Updated!';
		header("Location:$page_url");
		exit();
	}
	$_SESSION['error'] = 'Something Wrong!';
	header("Location:$page_url");
	exit();
}

if(isset($_POST['addTransaction'])){
	//echo "<pre>"; print_r($_POST); die;
	$blocks = (explode("-",$_POST['block']));
	$customer_id = (int) filter_post($conn,$_POST['customer_id']);
	$associate_id = (int) filter_post($conn,$_POST['associate_id']);
	$block_id = $blocks['0'];
	$block_number_id = $blocks['1'];
	
	$payment_type = filter_post($conn,$_POST['payment_type']);
	$bank_name = filter_post($conn,$_POST['bank_name']);
	$cheque_dd_number = filter_post($conn,$_POST['cheque_dd_number']);
	$paid_amount = (float) filter_post($conn,$_POST['paid_amount']);
	$paid_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['paid_date'])));
	
	$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, name from kc_blocks where id = '".$block_id."' limit 0,1 "));
	$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, block_number from kc_block_numbers where id = '$block_number_id' limit 0,1 "));
	
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
	}else{
		$total_paid = mysqli_fetch_assoc(mysqli_query($conn,"select sum(amount) as total_paid from kc_associates_transactions where customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id' limit 0,1 "));
		$total_credit = associateTotalCredited($conn,$associate_id);
		if(($total_paid['total_paid']+$paid_amount) > $total_credit){
			$_SESSION['error'] = 'Paid Amount was Greater than total Credited Amount!';
		}else{
			$associate_details = mysqli_fetch_assoc(mysqli_query($conn,"select name, mobile_no from kc_associates where id = '".$associate_id."' limit 0,1 "));
			
			$name_with_title = $associate_details['name'];
						
			$variables_array = array('variable1' => $name_with_title,'variable2'=>$paid_amount,'variable3'=>$paid_date,'variable4'=>$block_details['name'],'variable5'=>$block_number_details['block_number']);
			/*if(sendMessage($conn,3,$customer_details['mobile'],$variables_array)){
				if(!isset($_SESSION['success'])){
					$_SESSION['success'] = 'Message sent Successfully!';
				}else if(isset($_SESSION['success'])){
					$_SESSION['success'] .= ' and Message sent Successfully!';
				}
			}else if(!isset($_SESSION['error'])){
				$_SESSION['error'] = 'Message not sent!';
			}else if(isset($_SESSION['error'])){
				$_SESSION['error'] .= ' and Message not sent!';
			}*/
			
			mysqli_query($conn,"insert into kc_associates_transactions set customer_id = '$customer_id', associate_id = '$associate_id', block_id = '$block_id', block_number_id = '$block_number_id', payment_type = '$payment_type', bank_name = '$bank_name', cheque_dd_number = '$cheque_dd_number', amount = '$paid_amount', cr_dr = 'dr', paid_date = '$paid_date', status = '1', addedon ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."' ");
			$_SESSION['success'] = 'Transaction Successfully Added!';
			header("Location:$page_url");
			exit();					
                                                                            
		}
	}
	header("Location:$page_url");
	exit();					
}

if(isset($_POST['cancelTransaction'])){
	
	//echo "<pre>"; print_r($_POST); die;
	$transaction_id = isset($_POST['cancel_transaction_id'])?(int) $_POST['cancel_transaction_id']:0;
	$cancel_remarks = isset($_POST['cancel_remarks'])?trim($_POST['cancel_remarks']):'';

	$transaction_details = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_customer_transactions where id = '".$transaction_id."' limit 0,1 "));

	if(!isset($transaction_details['id'])){
		$_SESSION['error'] = 'Transaction not Found!';
	}else if($cancel_remarks == ""){
		$_SESSION['error'] = 'Cancel Remarks is required!';
	}else{
		$error = false;
		mysqli_autocommit($conn,FALSE);

		if (!mysqli_query($conn,"insert into kc_associate_transactions_hist (customer_id, kc_associate_transactions_id, associate_id, block_id, block_number_id, transaction_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, status, remarks, cancel_remarks, action_type, addedon, added_by, deleted_by) select id, customer_id, associate_id, block_id, block_number_id, transaction_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, status, remarks, '$cancel_remarks', 'Payment Cancelled', addedon, added_by, '".$_SESSION['login_id']."' from kc_associates_transactions where id = '$transaction_id';")){
			$error = true;
			echo("Error description: " . mysqli_error($conn)); die;
		}

		if(!$error && !mysqli_query($conn,"delete from kc_associates_transactions where id = '".$transaction_id."';")){
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
	header("Location: $page_url");
	exit();
}

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>WCC | Admin Panel</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport"><!-- jQuery UI 1.11.4 -->
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
    <link href="/<?php echo $host_name; ?>/plugins/iCheck/flat/blue.css" rel="stylesheet" type="text/css" />
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
            <li class="active">Associates</li>
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
							<h3 class="box-title">All Associate</h3>
						</div>
						<?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'add_associate')){ ?>
	                    <div class="col-sm-4">
							<button class="btn btn-sm btn-success pull-right" data-toggle="modal" style="margin-left: 13px;" data-target="#addAssociate">Add Associate</button>
							<!-- <a class="btn btn-sm btn-success" href="associates.php?export=true" data-toggle="tooltip" title="Export All to Excel"><i class="fa fa-file-excel-o"></i>Export All Associates</a> -->
							<a href="associates_excel_export.php"  class="btn btn-sm btn-success pull-right" style="margin-right: 10px;"><i class="fa fa-file-excel-o"></i> Excel Export</a>
					    </div>
						
					<?php }?>
					</div>
					<hr>
					<div class="row">
						<div class="col-sm-12">
							<form enctype="multipart/form-data" action="associates.php" name="search_frm" id="search_frm" method="get" class="form-inline">
								<?php if($_SESSION['login_type'] == "super_admin" || userCan($conn,$_SESSION['login_id'],$privilegeName = 'search_associate')){ ?>
									<div class="form-group">
										<?php /*<select id="search_for" class="form-control">
											<option value="all">Search For All</option>
											<option value="name">Search For Name</option>
											<option value="code">Search For Code</option>
											<option value="mobile">Search For Mobile Number</option>
										</select>*/ ?>
										<label for="search_associate">Associate <a href="javascript:void(0);" class="text-primary" data-toggle="popover" title="Associate Search Hint" data-content="'c-' for Code Search<br>'n-' for Name Search<br>'m-' for Mobile Search<br><b>Eg:</b> If want to search 9651 in only code then Search for 'c-9651' "><i class="fa fa-info-circle"></i></a></label>
										<input type="text" class="form-control associate-autocomplete" data-for-id="search_associate" <?php /*data-search-for-id="search_for" */ ?> placeholder="Name or Code or Mobile">
										<input type="hidden" name="search_associate" id="search_associate">
									    <input type="submit" name="search" value="Search" class="btn btn-sm btn-primary">
									</div>
								<?php } ?>
							</form>
							<?php 
							// <div class="col-sm-12 text-right">
				            	// <a class="btn btn-sm btn-success" href="associates.php?export=true" data-toggle="tooltip" title="Export All to Excel"><i class="fa fa-file-excel-o">&nbsp;</i>Export All Associates</a>
				            // </div> ?>
			            </div>
			        </div>
				</div><!-- /.box-header -->
                <div class="box-body no-padding">
				
				 <table class="table table-striped table-hover table-bordered">
                    <tr>
                      <th>Sl No.</th>
					  <!-- <th>Code</th> -->
					  <th>Name</th>
					  <th>Mobile</th>
					  <th>Amount</th>
					  <th>Added Date</th>
                      <th>Status</th>
                      <?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'edit_associate')){ ?>
					  <th>Action</th>													
					  <?php } ?>
					</tr>
					<?php
					
					
					$query = "select * from kc_associates";
					if(isset($_GET['search_associate']) && (int) $_GET['search_associate'] > 0){
						$associate_id = (int) $_GET['search_associate'];
						$query .= " where id = '$associate_id' ";
					}
					
					$total_records = mysqli_num_rows(mysqli_query($conn,$query));
					$total_pages = ceil($total_records/$limit);
					//echo $total_pages;
					if($page == 1){
						$start = 0;
					}else{
						$start = ($page-1)*$limit;
					}
					$query .= " limit $start,$limit";
					$associates = mysqli_query($conn,$query);
					if(mysqli_num_rows($associates) > 0){
						$counter = $start + 1;
						$credit = 0;
						$debit = 0;
						while($associate = mysqli_fetch_assoc($associates)){
							$credit = associateTotalCredited($conn,$associate['id']);
							$debit = associateTotalDebited($conn,$associate['id']);
					?>
							<tr>
								<td><?php echo $counter; ?></td>
								<!-- <td><?php //echo $associate['code']; ?></td> -->
								<td><a href="javascript:void(0) " type="button" data-toggle="tooltip"  onclick = "viewInformation(<?php echo $associate['id']; ?>);" ><?php echo $associate['name']; ?></a></td>
                                <td><?php echo $associate['mobile_no']; ?></td>
                                <td>
                                	<strong>Credit:</strong> <span class="text-success"><?php echo $credit ?> ₹</span><br />
                                	<strong>Debit:</strong> <span class="text-primary"><?php echo $debit ?> ₹</span><br />
                                	<strong>Balance:</strong> <span class="text-warning"><?php echo ($credit -  $debit); ?> ₹</span>
                                </td>
                                <td><?php echo date("d M Y h:i A",strtotime($associate['addedon'])); ?></td>
								<td>
                                	<?php if($associate['status'] == 1){ ?>
                                		<label class="label label-success">Active</label>
                                    <?php }else{ ?>
                                    	<label class="label label-danger">Inactive</label>
                                    <?php } ?>
                                </td>
                                <td>
                                	<?php
                                	if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'status_associate')){
									if($associate['status']){
										$button_class = 'btn-success';
										$icon_class = 'fa-lock';
										$btn_title = "Make Inactive";
									}else{
										$button_class = 'btn-danger';
										$icon_class = 'fa-unlock';
										$btn_title = "Make Active";
									}
									?>
                                    
                                    <a href="<?php echo $page_url; ?>&associate=<?php echo $associate['id']; ?>" data-toggle="tooltip" title="<?php echo $btn_title; ?>">
										<button class="btn btn-xs <?php echo $button_class; ?>" type ="button"><i class="fa <?php echo $icon_class; ?>"></i></button>
									</a>
								<?php } if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'edit_associate','edit_associate')){?>
									<button class="btn btn-xs btn-warning" type="button" data-toggle="tooltip" title="Edit Associate's Information" onclick = "editInformation(<?php echo $associate['id']; ?>);"><i class="fa fa-pencil"></i></button>															
									<?php } if($credit > 0){ ?>
										<button class="btn btn-xs btn-primary" onClick="addTransaction(<?php echo $associate['id']; ?>);" data-toggle="tooltip" title="Add Transaction"><i class="fa fa-money"></i></button>

										<button class="btn btn-xs btn-warning" onClick="getTransactions(<?php echo $associate['id']; ?>);" data-toggle="tooltip" title="View Transactions"><i class="fa fa-money"></i></button>
									<?php } ?>
                                   
                                </td>
							</tr>
							<?php
							$counter++;
						}
					}else{
						?>
						<tr>
							<td colspan="8" align="center"><h4 class="text-red">No Record Found</h4></td>
						</tr>
						<?php
					}
					?>
                  </table>
                </div><!-- /.box-body -->
				
				<?php if($total_pages > 1){ ?>
					<div class="box-footer clearfix">
					  <ul class="pagination pagination-sm no-margin pull-right">
					   
						<?php
							for($i = 1; $i <= $total_pages; $i++){
								?>
								 <li <?php if((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)){ ?>class="active"<?php } ?>><a href="<?php echo $url; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
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
	
	<div class="modal" id="addAssociate">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" name="add_associate_frm" id="add_associate_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add Associate</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-header with-border">
						<div class="col-md-12">
							<h3 class="box-title">Add Associate Panel</h3>
						</div>
					</div><!-- /.box-header -->
					<!-- form start -->
					<div class="box-body">
						<div class="form-group">
						  <label for="code" class="col-sm-3 control-label">Associate Code</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="code" name="code" maxlength="255" required>
						  </div>
						</div>

						<div class="form-group">
						  <label for="name" class="col-sm-3 control-label">Associate Name</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="name" name="name" maxlength="255" required>
						  </div>
						</div>
                        
                        <div class="form-group">
						  <label for="mobile" class="col-sm-3 control-label">Associate Mobile</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="mobile" name="mobile" maxlength="255" required>
						  </div>
						</div>
                        <div class="form-group">
						  <label for="mobile" class="col-sm-3 control-label">Parents</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control associate-autocomplete " data-for-id="search_associate" id="parent_id" name="assoicate_name" maxlength="255" required>
							<input type="hidden" name="parent_id" class="form-control parent_id">
						  </div>
						</div>
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="addAssociate">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	<div class="modal" id="addTransaction">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="" name="add_transaction_frm" id="add_transaction_frm" method="post" class="form-horizontal dropzone" onSubmit="return confirm('Are you sure All Details are correctly Filled?');">
			  

			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

    
    
    <div class="modal" id="viewTransaction">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="customers.php" name="view_transaction_frm" id="view_transaction_frm" method="post" class="form-horizontal dropzone">
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

	<div class="modal" id="editInformation">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="" name="edit_frm" id="edit_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Edit Associate Information</h4>
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
			<form enctype="multipart/form-data" action="" name="edit_frm" id="edit_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">View Associate Information</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-body" id="view-information-container">
					
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	<div class="modal" id="cancelTransaction">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="" name="add_late_payment_frm" id="add_late_payment_frm" method="post" class="form-horizontal dropzone">
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
    

    <script type="text/javascript">
    	function addTransaction(associate){
    		$.ajax({
				url: '../dynamic/addAssociateTransactions.php',
				type:'post',
				data:{associate:associate},
				success: function(resp){
					$("#add_transaction_frm").html(resp);
					$("[data-mask]").inputmask();
					$("#addTransaction").modal('show');
				}
			});
		}

		function getCustomerId(elem){
			var customerId = $(elem).find(":selected").attr('for');
			$("#customer_id").val(customerId);
		}

		function getTransactions(associate){
			$.ajax({
				url: '../dynamic/getAssociateTransactions.php',
				type:'post',
				data:{associate:associate},
				success: function(resp){
					$("#view-transaction-container").html(resp);
					$("#viewTransaction").modal('show');
				}
			});
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

	    function editInformation(associateID){
			$.ajax({
				url: '../dynamic/getAssociateInformation.php',
				type:'post',
				data:{associateID:associateID},
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
		function viewInformation(associateID){
			$.ajax({
				url: '../dynamic/viewAssociateInformation.php',
				type:'post',
				data:{associateID:associateID},
				success: function(resp){
					$("#view-information-container").html(resp);
					$("[data-mask]").inputmask();
					$('input').iCheck({
						  checkboxClass: 'icheckbox_square-blue',
						  radioClass: 'iradio_square-blue',
						  click: function(){
							}
						});
					$("#viewInformation").modal('show');
				}
			});
		}
    </script>
    
    <?php require('../includes/common-js.php'); ?>
	
    <script type="text/javascript">
	
	</script>
    
  </body>
</html>

