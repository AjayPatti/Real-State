<?php
	ob_start();
	session_start();
	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/checkAuth.php");
	if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_due_amount'))){ 
		header("location:/wcc_real_estate/index.php");
		exit();
	}

 	$url = 'telemarketing.php?search=Search';

	$limit = 100;
	if(isset($_GET['page'])){
		$page = $_GET['page'];
	}else{
		$page = 1;
	}
	// print_r($page);die;
	$search = false;
	$search_project_url_string = $search_associate_url_string = '';
	$query = "select cfu.*,cb.id,cb.customer_id,cb.block_id,cb.block_number_id,cb.associate from kc_customer_follow_ups cfu left join kc_customer_blocks cb ON cb.customer_id=cfu.customer_id AND cb.block_id=cfu.block_id AND cb.block_number_id=cfu.block_number_id left join kc_customers kc ON kc.id=cfu.customer_id WHERE cfu.status = 0 AND kc.blacklisted = 0 ";
//    echo "$query";die;
	if(isset($_GET['search_project']) && $_GET['search_project'] != '' || isset($_GET['search_block']) && $_GET['search_block'] != '' || isset($_GET['search_block_no'])  && $_GET['search_block_no'] != '' || (isset($_GET['search_block_no']) && $_GET['search_block_no']>0) || (isset($_GET['search_associate']) && (int) $_GET['search_associate'] > 0)) { 
		//echo "$query";die;
		if(isset($_GET['search_block']) && $_GET['search_block']!=''){
			$query .= " and cfu.block_id = '".$_GET['search_block']."'";
			$url .= '&search_block='.$_GET['search_block'];
		}

		if(isset($_GET['search_block_no']) && $_GET['search_block_no']!=''){
			$query .= " and cfu.block_number_id = '".$_GET['search_block_no']."'";
			$url .= '&search_block_no='.$_GET['search_block_no'];
		}

		if(isset($_GET['search_project']) && is_array($_GET['search_project']) && sizeof($_GET['search_project'])>0){
			$query .= " and cfu.block_id IN (select id from kc_blocks where status = '1' and project_id IN ('".implode("','",$_GET['search_project'])."') )";
			foreach($_GET['search_project'] as $project_id){
				$url .= '&search_project[]='.$project_id;
				$search_project_url_string .= '&search_project[]='.$project_id;
			}
		}
		if(isset($_GET['datesearch'])&& $_GET['datesearch']!=''){
			$ddatesearch = explode('-',$_GET['datesearch']);
			//print_r($ddatesearch);die;
			
			$startdate = date('Y-m-d 00:00:01',strtotime($ddatesearch[0]));
			//echo $startdate;die;
			$enddate = date('Y-m-d 23:59:59',strtotime($ddatesearch[1]));
			$query .= "and cfu.created_at between '$startdate' and '$enddate' ";
			
		}
		if(isset($_GET['search_associate']) && (int) $_GET['search_associate'] > 0){
			$search_associates = implode(",", $_GET['search_associate']);
			// print_r($search_associates);die();
			//$associate_id = (int) $search_associates;
			$query .= " and cb.associate IN ($search_associates)";
			//print_r($query);die();
			foreach ($_GET['search_associate'] as $value) {
				$url .= '&search_associate[]='.$value;
				$search_associate_url_string .= '&search_associate[]='.$value;
			}
			//echo "$query";die;
		}
		$total_records = mysqli_num_rows(mysqli_query($conn,$query));
		$total_pages = ceil($total_records/$limit);
		if($page == 1){
			$start = 0;
		}else{
			$start = ($page-1)*$limit;
		}
		$query .= " limit $start,$limit";
		$customer_follow_ups = mysqli_query($conn,$query);
	
	}else{
		// $query = "select * from kc_customer_follow_ups WHERE status = '0' ";
		$query="select cfu.* from kc_customer_follow_ups cfu left join kc_customers kc ON kc.id=cfu.customer_id WHERE cfu.status = 0 AND kc.blacklisted = 0 ";
		// echo "$query";die;
		$total_records = mysqli_num_rows(mysqli_query($conn,$query));
		$total_pages = ceil($total_records/$limit);
		
		if($page == 1){
			$start = 0;
		}else{
			$start = ($page-1)*$limit;
		}
		$query .= " limit $start,$limit";

		$customer_follow_ups = mysqli_query($conn,$query);
		// print_r(mysqli_num_rows($customer_follow_ups));die;
	
		// if(isset($_GET['search_associate']) && (int) $_GET['search_associate'] > 0){
		// 	$search_associates = implode(",", $_GET['search_associate']);
		// 	// print_r($search_associates);die();
		// 	//$associate_id = (int) $search_associates;
		// 	$query .= " and cb.associate IN ($search_associates)";
		// 	//print_r($query);die();
		// 	foreach ($_GET['search_associate'] as $value) {
		// 		$url .= '&search_associate[]='.$value;
		// 		$search_associate_url_string .= '&search_associate[]='.$value;
		// 	}
		}

	// $limit = 100;
	// if(isset($_GET['page'])){
	// 	$page = $_GET['page'];
	// }else{
	// 	$page = 1;
	// }

	
	// $blocks = mysqli_query($conn,"SELECT * FROM kc_customer_blocks WHERE status = '1'");

	
	// 			while($r = mysqli_fetch_assoc($blocks)) {
	// 				$customer_id = $r['customer_id'];
	// 				$block_id = $r['block_id'];
	// 				$block_number_id = $r['block_number_id'];

	// 				$res = mysqli_fetch_assoc(mysqli_query($conn,"SELECT customer_id,block_id,block_number_id FROM kc_customer_follow_ups WHERE customer_id != '".$customer_id."' AND block_id = '".$block_id."' AND block_number_id = '".$block_number_id."' AND  deleted_at is null"));

	// 				if(!$res){

	// 				$total_cr = mysqli_fetch_assoc(mysqli_query($conn , "SELECT SUM(amount) AS total_cr FROM kc_customer_transactions WHERE customer_id = '".$customer_id."' AND cr_dr = 'cr' AND block_id = '".$block_id."' AND block_number_id = '".$block_number_id."' "));
 //   					$total_dr =  mysqli_fetch_assoc(mysqli_query($conn , "SELECT SUM(amount) AS total_dr FROM kc_customer_transactions WHERE customer_id = '".$customer_id."' AND cr_dr = 'dr' AND block_id = '".$block_id."' AND block_number_id = '".$block_number_id."' "));
   					
 //   					if($total_cr['total_cr'] > $total_dr['total_dr']){
 //   						// $detail = mysqli_fetch_assoc(mysqli_query($conn , "SELECT * FROM kc_customer_blocks WHERE customer_id = '".$customer_id."' AND block_id = '".$block_id."' AND block_number_id = '".$block_number_id."' AND status = '1' "));
 //   						$next_due_date = nextDueDate($conn,$customer_id,$block_id,$block_number_id);
 //   						if($r['customer_payment_type'] == 'EMI'){
 //   							$pending_EMI_amc = nextEMIDetails($conn ,$customer_id,$block_id,$block_number_id);
 //   							//print_r($pending_EMI_amc['emi_amount']);die;

 //   							mysqli_query($conn,"INSERT INTO kc_customer_follow_ups (customer_id,block_id,block_number_id,pending_amount,next_due_date,next_follow_up_date,created_by) VALUES( '$customer_id' , '$block_id', '$block_number_id' , '".$pending_EMI_amc['emi_amount']."' , '$next_due_date' , '$next_due_date' ,'".$_SESSION['login_id']."' ) ");

 //   							mysqli_query($conn,"INSERT INTO kc_customer_follow_ups_hist (customer_id,block_id,block_number_id,pending_amount,next_due_date,next_follow_up_date,created_by) VALUES( '$customer_id' , '$block_id', '$block_number_id' , '".$pending_EMI_amc['emi_amount']."' , '$next_due_date' , '$next_due_date' ,'".$_SESSION['login_id']."' ) ");

 //   						}
 //   						 if($r['customer_payment_type'] == 'Part') {

 //   						 $pending_PART_amc = getPartAmount($conn ,$customer_id ,$block_id,$block_number_id);

 //   						 mysqli_query($conn,"INSERT INTO kc_customer_follow_ups (customer_id,block_id,block_number_id,pending_amount,next_due_date,next_follow_up_date,created_by) VALUES( '$customer_id' , '$block_id', '$block_number_id' , '".$pending_PART_amc."' , '$next_due_date' , '$next_due_date' ,'".$_SESSION['login_id']."' ) ");

 //   						 mysqli_query($conn,"INSERT INTO kc_customer_follow_ups_hist (customer_id,block_id,block_number_id,pending_amount,next_due_date,next_follow_up_date,created_by) VALUES( '$customer_id' , '$block_id', '$block_number_id' , '".$pending_PART_amc."' , '$next_due_date' , '$next_due_date' ,'".$_SESSION['login_id']."' ) ");
 //   						}
   						 
 //   						}
 //   					}

				 	
	// 			}
				 //header("location :telemarketing.php");
				 
// 	id	"4"
// cid	"2"
// remark	"asdf"
// date	"05-03-2021"

	if(isset($_POST['saveFollowup'])){
		// echo "<pre>"; print_r($_POST); die;
		$customer_id = $_POST['customer'];
		$block_id = $_POST['block'];
		$block_number_id = $_POST['block_number'];
		$remarks = $_POST['remark'];
		$date = date("Y-m-d", strtotime($_POST['date']));


		if($remarks == ''){
			$_SESSION['error'] = 'Remarks required!';
		}else if($date == '' || $date == '1970-01-01'){
			$_SESSION['error'] = 'Next Follow Up Date was wrong!';
		}else{
			// $customer_followup_detail = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_customer_follow_ups where customer_id = '".$customer_id."' and block_id = '".$block_id."' and block_number_id = '".$block_number_id."' limit 0,1 "));
//
			$error = false;
			mysqli_autocommit($conn,FALSE);

			$nextDueDate = nextDueDate($conn,$customer_id,$block_id,$block_number_id);
			$pending_amount = totalCredited($conn,$customer_id,$block_id,$block_number_id) - totalDebited($conn,$customer_id,$block_id,$block_number_id);
			if (!mysqli_query($conn , "INSERT INTO kc_customer_follow_ups_hist (customer_id, block_id, block_number_id, pending_amount, next_due_date, next_follow_up_date, remarks, created_by, created_at) VALUES ( '".$customer_id."' , '".$block_id."', '".$block_number_id."', '".$pending_amount."', '".$nextDueDate."', '".$date."', '".$remarks."', '".$_SESSION['login_id']."', '".date("Y-m-d H:i:s")."' )")){
				$error = true;
				echo("Error description: " . mysqli_error($conn)); die;
			}

			if(!$error && !mysqli_query($conn , "UPDATE kc_customer_follow_ups SET next_follow_up_date = '".$date."', remarks = '".$remarks."', updated_by = '".$_SESSION['login_id']."', updated_at = '".date("Y-m-d H:i:s")."' WHERE  customer_id = '".$customer_id."' and block_id = '".$block_id."' and block_number_id = '".$block_number_id."' ")){
				$error = true;
				echo("Error description: " . mysqli_error($conn)); die;
			}

			if(!$error){
				mysqli_commit($conn);
				$_SESSION['success'] = 'Followup Updated';
				header("Location:".$url);
				exit();
			}else{
				mysqli_rollback($conn);
				$_SESSION['error'] = 'Some Problem Occured during in storing data!';
			}
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
            <li class="active">Telemarketing</li>
          </ol>
        </section>

        <!-- Main content -->
        <!-- Main content -->
        <section class="content">
        	<div class="box">

        	<div class="box-header">
        		<?php 
					include("../includes/notification.php"); ?>
        		<div class="row">
        			<div class="col-sm-10">
						<h3 class="box-title">All Due Amount</h3>
					</div>
				</div>
				<div class="col-sm-12 " id="successdiv">
				</div>
				<hr/>
        		<form action="" name="search_frm" id="search_frm" method="get" class="">
					<div class="row">
						<div class="form-group col-md-3">
							<label for="search_project" class="col-md-12 text-left">Project</label>
						  	<select class="form-control select2-w100" id="search_project" name="search_project[]" multiple onChange="search_getBlocks(this.value);">
	                        	<option value="">Select Project</option>
	                            <?php
								$projects = mysqli_query($conn,"select * from kc_projects where status = '1' ");
								while($project = mysqli_fetch_assoc($projects)){ ?>
	                            	<option value="<?php echo $project['id']; ?>"><?php echo $project['name']; ?></option>
	                            <?php } ?>
                        	</select>
						</div>
						<div class="form-group col-md-2 text-center">
						  	<label for="search_block" class="col-md-12 text-left">Block</label>
							<select class="form-control" id="search_block" name="search_block" onChange="search_getBlockNumbers(this.value);">
						        <option value="">Select Block</option>
						    </select>
						</div>
						<div class="form-group col-md-2 text-center">
						  	<label for="search_block_no" class="col-md-12 text-left">Plot Number</label>
							<select class="form-control" id="search_block_no" name="search_block_no">
						        <option value="">Select Plot Number</option>
						    </select>
						</div>
						<div class="form-group col-md-2 text-center">
							<label for="search_associate" class="col-md-12 text-left">Associate <a href="javascript:void(0);" class="text-primary" data-toggle="popover" title="Associate Search Hint" data-content="'c-' for Code Search<br>'n-' for Name Search<br>'m-' for Mobile Search<br><b>Eg:</b> If want to search 9651 in only code then Search for 'c-9651' "><i class="fa fa-info-circle"></i></a></label>
							<select class="form-control select2-ajax" id="search_associate" name="search_associate[]" multiple style="width:100%;" readonly>
	                        	<option value="">Select Associate</option>
	                            
                        	</select>
                    	</div>
						<div class="form-group col-md-2 text-center">
							<label for="search_associate" class="col-md-12 text-left">Date Search </label>
							<div><input type="text" class="form-control" placeholder="Date" id="datepick" name="datesearch" readonly=""></div>
                    	</div>
						<div class="col-md-1">
							<input type="submit" name="search" value="Search" class="btn btn-sm btn-primary" style="margin-top: 25px;">
						</div>
					</div>
					</form>
        	</div>
			<div class="box-body no-padding">
                	<div class="table-responsive">
					 <table class="table table-striped table-hover table-bordered">
	                    <tr>
	                      <th>SNo.</th>
						  <th>Details</th>
						  <th>Project Details</th>
						  <th>Amount</th>
						  <th>Next Due Date</th>
						  <th>Next Follows Date</th>
						  <th>User</th>
						  <?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'view_customer')) {?>
	                      <th>Action</th>
	                  <?php } ?>
						</tr>
						<?php
						
							
							/* $query = "select * from kc_customer_follow_ups WHERE status = '0' ";
						
						$total_records = mysqli_num_rows(mysqli_query($conn,$query));
						$total_pages = ceil($total_records/$limit);
						if($page == 1){
							$start = 0;
						}else{
							$start = ($page-1)*$limit;
						}
						//ORDER BY next_follow_up_date 
						$query .= " limit $start,$limit"; */
						//$customer_follow_ups = mysqli_query($conn,$query);
						if(mysqli_num_rows($customer_follow_ups) > 0){
							$counter = $start + 1;
							while($customer_follow_up = mysqli_fetch_assoc($customer_follow_ups)){
								$blocks = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_blocks where id = '".$customer_follow_up['block_id']."' "));	// and status = '1'
								$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select block_number from kc_block_numbers where block_id = '".$blocks['id']."' AND id =  '".$customer_follow_up['block_number_id']."' limit 0,1 "));

								$customer = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_customers where id = '".$customer_follow_up['customer_id']."'"));
								$payment_type = 	mysqli_fetch_assoc(mysqli_query($conn, "SELECT customer_payment_type FROM kc_customer_blocks WHERE customer_id = '".$customer_follow_up['customer_id']."' AND block_id = '".$customer_follow_up['block_id']."' AND block_number_id = '".$customer_follow_up['block_number_id']."' " ));
								$userType = mysqli_fetch_assoc(mysqli_query($conn ,"SELECT name FROM kc_login WHERE id = '".$customer_follow_up['updated_by']."' "));
								$emi = nextEMIDetails($conn , $customer_follow_up['customer_id'] , $customer_follow_up['block_id'] , $customer_follow_up['block_number_id']);
								$part_amc = getPartAmount($conn , $customer_follow_up['customer_id'] , $customer_follow_up['block_id'] , $customer_follow_up['block_number_id'] );

								$total_amc= mysqli_fetch_assoc(mysqli_query($conn , "SELECT SUM(amount) AS total_cr FROM kc_customer_transactions WHERE customer_id = '".$customer_follow_up['customer_id']."' AND cr_dr = 'cr' AND block_id = '".$customer_follow_up['block_id']."' AND block_number_id = '".$customer_follow_up['block_number_id']."' AND status = 1 "));
								$total_paid_amc =  mysqli_fetch_assoc(mysqli_query($conn , "SELECT SUM(amount) AS total_dr FROM kc_customer_transactions WHERE customer_id = '".$customer_follow_up['customer_id']."' AND cr_dr = 'dr' AND block_id = '".$customer_follow_up['block_id']."' AND block_number_id = '".$customer_follow_up['block_number_id']."' AND status = 1 "));
								$due_amc = getPartAmount($conn , $customer_follow_up['customer_id'] , $customer_follow_up['block_id'] , $customer_follow_up['block_number_id'] ) ;
								//print_r($customer_follow_up);die;
								//print_r($total_paid_amc);
								?>
								<tr>
									<td><?php echo $counter."."; ?></td>
									<td nowrap="nowrap">
										<strong><?php echo $customer['name_title']; ?> <?php echo $customer['name'].' ('.customerID($customer['id']).')'; ?></strong><br>
	                                    <strong><?php echo $customer['parent_name_relation']; ?></strong> <?php if($customer['parent_name'] != ''){ ?>of <strong><?php echo isset($customer['parent_name_sub_title'])?$customer['parent_name_sub_title']:''; ?> <?php echo $customer['parent_name']; } ?></strong><br>
	                                    <?php if($customer['nominee_name'] != ''){ ?>
	                                    	Co-owner: <strong class="text-danger"><?php echo $customer['nominee_name']; ?></strong>
	                                    	<?php if($customer['nominee_relation'] != ''){ ?>
		                                    	<strong class="text-danger">(<?php echo $customer['nominee_relation']; ?>)</strong>
		                                    <?php } ?>
		                                    <br> Mobile: <strong><?php echo $customer['mobile']; ?></strong>
	                                    <?php } ?>
	                                    <br> <b>Mobile:</b> <?php echo $customer['mobile']; ?>
	                                </td>
	                                <td nowrap="nowrap">
	                                	<?php 
	                                	//print_r($customer_follow_up['block_id']);

	                                	 echo '<h5 class="text-success">'.blockProjectName($conn,$customer_follow_up['block_id']).'<br>'.(isset($blocks['name'])?$blocks['name']:'').'('.(isset($block_number_details['block_number'])?$block_number_details['block_number']:'').')'."</h5>";
	                                	 
	                                	?>
	                                </td>
	                                <td nowrap="nowrap">
										<?php if(!isset($payment_type['customer_payment_type'])){
											echo '<b class="text-danger">Booking has been <br>cancelled or removed.</b>';
										}else{
											?>
											<?php if (isset($payment_type['customer_payment_type']) && $payment_type['customer_payment_type'] == 'EMI'){
											echo '<strong class="text-warning"> EMI </strong> : '; 
											}elseif(isset($payment_type['customer_payment_type']) && $payment_type['customer_payment_type'] == 'Part'){
													echo '<strong class="text-success"> Part </strong> : ';
											}
											if(isset($payment_type['customer_payment_type']) && $payment_type['customer_payment_type'] == 'EMI'){
											echo '<strong class="text-warning"> '.$emi['emi_amount'].' ₹</strong><br>';
											} else{
												echo '<strong class="text-success"> '.$part_amc.' ₹</strong><br>';
											} echo "<br/>".'<strong class="text-warning"> Received : '.$total_paid_amc['total_dr'].' ₹</strong><br>'.'<strong class="text-success"> Total : '.$total_amc['total_cr'].' ₹</strong><br>'.'<strong class="text-danger"> Balance : '.$due_amc.' ₹</strong><br>' ;
										}
											?>
	                                </td>
	                                <!-- <td nowrap="nowrap">
	                                	<?php if($payment_type['customer_payment_type'] == 'EMI'){
	                                	echo '<strong class="text-warning"> '.$emi['emi_amount'].' ₹</strong><br>';
	                                	}
	                                	else{
	                                		echo '<strong class="text-warning"> '.$part_amc.' ₹</strong><br>';
	                                	}
	                                	?>
	                                </td> -->
	                               <!--  <td nowrap="nowrap">
	                                	<?php echo '<strong class="text-danger"> '.$total_amc['total_cr'].' ₹</strong><br>' ; ?>
	                                </td> -->
	                                 <!-- <td nowrap="nowrap">
	                                	<?php echo '<strong class="text-warning"> '.$total_paid_amc['total_dr'].' ₹</strong><br>' ; ?>
	                                </td> -->
	                               <!--  <td nowrap="nowrap">
	                                	<?php echo '<strong class="text-success"> '.$due_amc.' ₹</strong><br>' ; ?>
	                                </td> -->
	                                <td nowrap="nowrap">
	                                	<?php  
													echo '<strong class="text-info">'.date("d-m-Y",strtotime($customer_follow_up['next_due_date'])).'</strong>';
	                                	?>
	                                		
	                                	</td>

	                                	<td nowrap="nowrap">
	                                	<?php  
	                                		echo '<strong class="text-info">'.date("d-m-Y",strtotime($customer_follow_up['next_follow_up_date'])).'</strong>';
	                                	?>
	                                		
	                                	</td>
	                               <td nowrap="nowrap">
	                               	<?php  
	                                		echo '<strong class="text-info">'.(isset($userType['name'])?$userType['name']:'').'</strong>';
	                                	?>
										<?php $followup_created_date = ($customer_follow_up['updated_at'] != NULL)?$customer_follow_up['updated_at']:$customer_follow_up['created_at']; ?>
										<br>
										<b class="text-danger">On <?php echo date("d-m-Y",strtotime($followup_created_date)); ?></b>
										
	                               </td>
	                                <td nowrap="nowrap">
	                                	<?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'customer_follow_ups_due_amount')){ ?>
	                                	<button class="btn btn-xs btn-warning" type="button" data-toggle="tooltip" title="Followup" onclick = "followUp(<?php echo $customer_follow_up['customer_id']; ?>,<?php echo $customer_follow_up['block_id']; ?>,<?php echo $customer_follow_up['block_number_id']; ?>);"><i class="fa fa-plus"></i></button>
	                                <?php } if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'view_customer_follow_ups_due_amount')){ ?>
	                                	<a href="../management/viewcustomerfollowups.php?customer_id=<?= $customer_follow_up['customer_id'];?>&block_id=<?= $customer_follow_up['block_id'];?>&block_number_id=<?= $customer_follow_up['block_number_id'];?>"><button class="btn btn-xs btn-info" type="button" data-toggle="tooltip" title="View Followups History" ><i class="fa fa-eye"></i></button></a>
	                                <?php } ?>
	                                    
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
                </div><!-- /.box-body -->
            </div>
        </section> 

      

	<div class="modal" id="actionFollowUp">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="#"  id="followUp_form" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Follow Up</h4>
			  </div>
			  <div class="modal-body">
					<div class="box-body" id="edit-information-container">
						<input type="hidden" name="customer" id="followup_customer_id">
						<input type="hidden" name="block" id="followup_block_id">
						<input type="hidden" name="block_number" id="followup_block_number_id">
						<div class="form-group">
							<label class="col-sm-3 control-label" for="exampleInputEmail1">Remark</label>
							    <div class="col-md-7">
								    <input type="text" class="form-control remark" id="remark" name="remark" data-validation="required">
								    <span id="span1"></span>
								</div>
						</div>
						<div class="form-group">
						    <label class="col-sm-3 control-label" for="exampleInputEmail1">Next Follow Up Date</label>
							    <div class="col-md-7">
								    <input type="text" class="form-control date" id="date" name="date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask=""  data-validation="date" data-validation-format="dd-mm-yyyy">
								</div>
						</div>
			    			
					</div>
					
				</div><!-- /.box -->
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="saveFollowup">Save</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

     </div><!-- /.content-wrapper -->  
 	<?php require('../includes/footer.php'); ?>

    </div><!-- ./wrapper -->

    <?php require('../includes/common-js.php'); ?>

    <script type="text/javascript">
    	function search_getBlocks(project){
	  		var projects = $("#search_project").val();

			$("#search_block_no").val('');
			$.ajax({
				url: '../dynamic/getBlocksByMultipleProjects.php',
				type:'post',
				data:{projects:projects},
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
    	function viewInformation(customer){
			$.ajax({
				url: '../dynamic/viewCustomerFollowups.php',
				type:'post',
				data:{customer:customer},
				success: function(resp){
					$("#view-information-container").html(resp);
					$("#viewInformation").modal('show');
				}
			});
		}

		function followUp(customer, block, block_number)
		{
			//alert(id+" "+cid);
			$("#followup_customer_id").val(customer);
			$("#followup_block_id").val(block);
			$("#followup_block_number_id").val(block_number);
			$("#actionFollowUp").modal('show');
			
		}
		
		/*
		Not In Use
		$('#modal_id').on("submit", function(event){
		 	var remark = $('.remark').val();
		 	var date = $('.date').val();
			if(remark == '' || date == '' ){
				if(remark == ''){
					$('#span1').css({'color':'red'});
					$('#span1').html('Remark field is required.');
				}
				if(date == ''){
					$('#span2').css({'color':'red'});
					$('#span2').html('Date field is required.');
				}
				return false;
			}else{
				if(remark != ''){
					$('#span1').html('');
				}
				if( date != ''){
					$('#span2').html('');
				}
				
			}
			event.preventDefault();
        	 
        	var formValues= $(this).serialize();
        	 $.ajax({
            type: 'post',
            url: '../dynamic/actionCustomerFollowups.php',
            dataType: 'json',
            data: formValues,
            success: function (data) {
               	if(data == "success"){
               		alert('here');
               		//$('#actionFollowUp').modal("hide");
               		 $("#date").val('');
               		 $("#remark").val('');
               		 $("#actionFollowUp").modal('hide');
               		$("#successdiv").append(
                  '<div class="alert alert-success alert-dismissable">'+
                    '<button type="button" class="close" data-dismiss="alert">'+
                        '<span aria-hidden="true">&times;</span>'+
                        '<span class="sr-only">Close</span>'+
                    '</button>Next Followups Date Successfully Added ! </div>'
                );
               	  }
           	  	} 
        	  })
        	})

		 $( function() {
			$( ".select2-ajax" ).select2({
				ajax: {
				    url: '../dynamic/getAssociateMultiple.php',
				    dataType: 'json',
		          	
		          	data: function (params) {
				      var query = {
				        term: params.term
				      }

				      // Query parameters will be ?search=[term]&type=public
				      return query;
				    },
				    processResults: function (data) {
				      // Transforms the top-level key of the response object from 'items' to 'results'
				      return {
				    	// alert('data');
						results: data.items
				      };
				    }
				    // Additional AJAX parameters go here; see the end of this chapter for the full code of this example
				  }
			});
	  	});
	  	*/

    </script>
    <script>
  $(document).ready(function(){
    var start = "{{ date('d-m-Y',strtotime($startdate)) }}";
    var end = "{{ date('d-m-Y',strtotime($enddate)) }}";
   
    $('input[name="datesearch"]').daterangepicker({
      
      ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Previous 7 Days': [moment(), moment().subtract(6, 'days')],
           
           'This Month': [moment().startOf('month'), moment().endOf('month')],
            
        }

     });
  
  })
</script>
    
  </body>
</html>