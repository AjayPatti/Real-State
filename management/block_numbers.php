<?php
ob_start();
session_start();

if(!isset($_GET['block']) || !is_numeric($_GET['block']) || !($_GET['block'] > 0)){
	$_SESSION['error'] = 'Unauthorized Access!';
	header("Location:blocks.php");
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");

    
if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_plot_number_projects'))){
  header("location:/wcc_real_estate/index.php");
  exit();
 }
$url = 'block_numbers.php?block='.$_GET['block'];
if(isset($_GET['page'])){
	$page = $_GET['page'];
}else{
	$page = 1;
}
$page_url = $url.'&page='.$page;


$block_id = $_GET['block'];
$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_blocks where id = '".$block_id."' limit 0,1 "));
$project_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_projects where id = '".$block_details['project_id']."' limit 0,1 "));

if(isset($_POST['addBlockNumber'])){
	// echo "<pre>"; print_r($_POST); die;
	$number = filter_post($conn,$_POST['block_number']);

	$area = filter_post($conn,$_POST['area']);
	$plc = (isset($_POST['plc']) && is_array($_POST['plc']))?$_POST['plc']:array();

	$road = filter_post($conn,$_POST['road']);
	$face = is_array($_POST['face'])?implode(',',$_POST['face']):'';

	/*else if($final_rate_posted != $final_rate){
		$_SESSION['error'] = 'Final Rate was wrong!';
	}*/
	//echo "<pre>"; print_r($plc); die;
	if($number == ''){
		$_SESSION['error'] = 'Block Number was wrong!';
	}else if(!is_numeric($area) || !($area > 0)){
		$_SESSION['error'] = 'Area was wrong!';
	}else if($road == ''){
		$_SESSION['error'] = 'Road was wrong!';
	}else{

		$already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_block_numbers where block_number = '".$number."' and block_id = '$block_id' limit 0,1 "));
		//echo "select id from kc_block_numbers where block_number = '".$number."' and block_id = '$block_id' limit 0,1 "; die;
		if(isset($already_exits['id'])){
			$_SESSION['error'] = 'Block Number Already Exists in '.$block_details['name'].' Block!';
		}else{
			mysqli_query($conn,"insert into kc_block_numbers set block_id = '$block_id', block_number = '$number', area = '$area', road = '$road', face = '$face', status = '1', addedon ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."' ");
			$block_number_id = mysqli_insert_id($conn);
			if($block_number_id > 0){

				if(is_array($plc) && sizeof($plc) > 0){
					foreach($plc as $plc_id){
						//$plc_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_plc where where = '".$plc_id."' limit 0,1 "));
						$already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_block_number_plc where block_number_id = '".$block_number_id."' and plc_id = '$plc_id' limit 0,1 "));
						if(!isset($already_exits['id'])){
							mysqli_query($conn,"insert into kc_block_number_plc set block_number_id = '$block_number_id', plc_id = '$plc_id', status = '1', addedon ='".date('Y-m-d H:i:s')."' ");
						}
					}
				}

				$_SESSION['success'] = 'Block Number Successfully Added!';
				header("Location: $page_url");
				exit();
			}else{
				$_SESSION['error'] = 'Block Number was wrong. Please Try Again!';
			}
		}
	}

}

if(isset($_POST['editBlockNumber'])){

	$block_number_id = filter_post($conn,$_POST['block_number']);
	$number = filter_post($conn,$_POST['block_number_edit']);

	$area = filter_post($conn,$_POST['area_edit']);
	$plc = (isset($_POST['plc_edit']) && is_array($_POST['plc_edit']))?$_POST['plc_edit']:array();

	$road = filter_post($conn,$_POST['road_edit']);
	$face = is_array($_POST['face_edit'])?implode(',',$_POST['face_edit']):'';

	$pre_details = mysqli_fetch_assoc(mysqli_query($conn,"select block_id from kc_block_numbers where id = '$block_number_id' limit 0,1 "));

	if(!($block_number_id > 0) || !is_numeric($block_number_id)){
		$_SESSION['error'] = 'Something was Really wrong!';
	}else if(!isset($pre_details['block_id'])){
		$_SESSION['error'] = 'Block Number Not Found!';
	}else if($number == ''){
		$_SESSION['error'] = 'Block Number was wrong!';
	}else if(!is_numeric($area) || !($area > 0)){
		$_SESSION['error'] = 'Area was wrong!';
	}else if($road == ''){
		$_SESSION['error'] = 'Road was wrong!';
	}else{
		$already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_block_numbers where id != '$block_number_id' and block_id = '".$pre_details['block_id']."' and block_number = '$number' limit 0,1 "));
		if(isset($already_exits['id'])){
			$_SESSION['error'] = 'Block Number Already Exists!';
		}else{
			mysqli_query($conn,"update kc_block_numbers set block_number = '$number', area = '$area', road = '$road', face = '$face' where id = '$block_number_id' ");
			//echo "<pre>"; print_r($plc); die;
			$plcs = mysqli_query($conn,"select * from kc_block_number_plc where block_number_id = '".$block_number_id."' and status = '1' ");
            while($plc_details = mysqli_fetch_assoc($plcs)){
                if(!in_array($plc_details['plc_id'],$plc)){
					//echo "delete from kc_block_number_plc where id = '".$plc_details['id']."' limit 1 "; die;
					mysqli_query($conn,"delete from kc_block_number_plc where id = '".$plc_details['id']."' limit 1 ");
				}
            }
			if(is_array($plc) && sizeof($plc) > 0){
				foreach($plc as $plc_id){
					$already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_block_number_plc where block_number_id = '".$block_number_id."' and plc_id = '$plc_id' limit 0,1 "));
					if(!isset($already_exits['id'])){
						mysqli_query($conn,"insert into kc_block_number_plc set block_number_id = '$block_number_id', plc_id = '$plc_id', status = '1', addedon ='".date('Y-m-d H:i:s')."' ");
					}
				}
			}

			$_SESSION['success'] = 'Block Number Successfully Updated!';
			header("Location: $page_url");
			exit();
		}
	}
}

// echo "pre";print_r($_GET);die;
if(isset($_GET['action']) && $_GET['action'] == "cancel" && isset($_GET['block_number']) && is_numeric($_GET['block_number'])){
    $block_number_id = $_GET['block_number'];
    // echo $block_number_id; die;

    $block = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM kc_customer_blocks_hist WHERE block_number_id = '".$block_number_id."' ORDER BY id DESC"));
	
		
    // print_r("insert into kc_customer_blocks_hist (kc_customer_blocks_id, customer_id, block_id, block_number_id, rate_per_sqft, final_rate, registry, registry_date, registry_by, sales_person_id, status,batch, addedon, added_by, deleted_by) select id, customer_id, block_id, block_number_id, rate_per_sqft, final_rate, registry, registry_date, registry_by, sales_person_id, status,'". $inBatch."', addedon, added_by, '".$_SESSION['login_id']."' from kc_customer_blocks where block_number_id = '$block_number_id';");die;

	$inBatch = isset($block)?($block['batch']+1):1;
    // print_r($inBatch); die;

	$customer_block_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, customer_id, block_id, block_number_id from kc_customer_blocks where block_number_id = '".$block_number_id."' limit 0,1 "));
	// print_r($customer_block_details); die;
	if(isset($customer_block_details['id'])){

		$error = false;
		// mysqli_autocommit($conn,FALSE);


		if (!mysqli_query($conn," insert into kc_customer_blocks_hist (kc_customer_blocks_id, customer_id, block_id, block_number_id, rate_per_sqft, final_rate, customer_payment_type,
		   number_of_installment, installment_amount, emi_payment_date, registry, registry_date, registry_by, khasra_no, maliyat_value, registry_by_user_id, registry_by_datetime, sale_value, sales_person_id, associate, associate_percentage, status, batch, addedon, added_by, deleted_by) select id, customer_id, block_id, block_number_id, rate_per_sqft, final_rate,customer_payment_type,number_of_installment, installment_amount, emi_payment_date, registry, registry_date, registry_by, khasra_no, maliyat_value, registry_by_user_id, registry_by_datetime, sale_value, sales_person_id, associate, associate_percentage, status,'".$inBatch."', addedon, added_by, '".$_SESSION['login_id']."' from kc_customer_blocks where block_number_id = '$block_number_id'; ")){
			$error = true;
			echo(" Error description: <br>" . mysqli_error($conn));
		}

		if (!$error && !mysqli_query($conn, " insert into kc_customer_block_plc_hist (kc_customer_block_plc_id, customer_block_id, plc_id, name, plc_percentage, status,batch, addedon, deleted_by) select id, customer_block_id, plc_id, name, plc_percentage, status,'" . $inBatch . "', addedon, '".$_SESSION['login_id']."' from kc_customer_block_plc where customer_block_id = '".$customer_block_details['id']."';")){
			$error = true;
			echo("Error description: <br>" . mysqli_error($conn));
		}


		// $query = "insert into kc_customer_transactions_hist (kc_customer_transactions_id, customer_id, block_id, block_number_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, next_due_date, late_for_transaction_id, is_affect_sold_amount, status,batch, clear_remarks, clear_date, paid_account_no, action_type, remarks, add_transaction_remarks, addedon, added_by, deleted_by) select id, customer_id, block_id, block_number_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, next_due_date, late_for_transaction_id, is_affect_sold_amount, status,'" . $inBatch . "', clear_remarks, clear_date, paid_account_no, 'Cancel Booking', remarks, add_transaction_remarks, addedon, added_by, '".$_SESSION['login_id']."' from kc_customer_transactions where customer_id = '".$customer_block_details['customer_id']."' and block_id = '".$customer_block_details['block_id']."' and block_number_id = '".$customer_block_details['block_number_id'];
		// echo "<pre>";print_r($query);die;






		if (!$error && !mysqli_query($conn, "insert into kc_customer_transactions_hist (kc_customer_transactions_id, customer_id, block_id, block_number_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, next_due_date, late_for_transaction_id, is_affect_sold_amount, status,batch, clear_remarks, clear_date, paid_account_no, action_type, remarks, add_transaction_remarks, addedon, added_by, deleted_by) select id, customer_id, block_id, block_number_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, next_due_date, late_for_transaction_id, is_affect_sold_amount, status,'" . $inBatch . "', clear_remarks, clear_date, paid_account_no, 'Cancel Booking', remarks, add_transaction_remarks, addedon, added_by, '".$_SESSION['login_id']."' from kc_customer_transactions where customer_id = '".$customer_block_details['customer_id']."' and block_id = '".$customer_block_details['block_id']."' and block_number_id = '".$customer_block_details['block_number_id']."';")){
			$error = true;
			echo("Error description: <br>" . mysqli_error($conn));
		}



		$transactions = mysqli_query($conn,"select id from kc_customer_transactions where customer_id = '".$customer_block_details['customer_id']."' and block_id = '".$customer_block_details['block_id']."' and block_number_id = '".$customer_block_details['block_number_id']."' and cr_dr = 'dr'; ");
		if($transaction = mysqli_fetch_assoc($transactions)){
			receiptNumber($conn,$transaction['id']);
		}

		if (!$error && !mysqli_query($conn," insert into kc_receipt_numbers_hist (kc_receipt_numbers_id, customer_id, block_id, block_number_id, transaction_id, receipt_id,batch, deleted_by) select id, customer_id, block_id, block_number_id, transaction_id, receipt_id,'".$inBatch."', '".$_SESSION['login_id']."' from kc_receipt_numbers where customer_id = '".$customer_block_details['customer_id']."' and block_id = '".$customer_block_details['block_id']."' and block_number_id = '".$customer_block_details['block_number_id']."';")){
			$error = true;
			echo("Error description: <br>" . mysqli_error($conn));
		}

		$isEmiTaken = isEmiTaken($conn,$customer_block_details['customer_id'],$customer_block_details['block_id'],$customer_block_details['block_number_id']);
		if ($isEmiTaken && !mysqli_query($conn, " insert into kc_customer_emi_hist (customer_emi_id, customer_id, block_id, block_number_id, emi_amount, paid_amount, emi_date, paid_date, created,batch, action_type, deleted_by) select  id, customer_id, block_id, block_number_id, emi_amount, paid_amount, emi_date, paid_date, created,'".$inBatch."', 'Cancel Booking', '".$_SESSION['login_id']."' from kc_customer_emi where  customer_id = '".$customer_block_details['customer_id']."' and block_id = '".$customer_block_details['block_id']."' and block_number_id = '".$customer_block_details['block_number_id']."';")){
	        $error = true;
	        echo("Error description: <br>" . mysqli_error($conn)); die;
	    }

		if(!$error && !mysqli_query($conn,"delete from kc_customer_blocks where block_number_id = '$block_number_id';")){
			$error = true;
			echo("Error description: " . mysqli_error($conn));die;
		}

		if(!$error && !mysqli_query($conn,"delete from kc_customer_block_plc where customer_block_id = '".$customer_block_details['id']."';")){
			$error = true;
			echo("Error description: " . mysqli_error($conn));die;
		}

		if(!$error && !mysqli_query($conn,"delete from kc_customer_transactions where customer_id = '".$customer_block_details['customer_id']."' and block_id = '".$customer_block_details['block_id']."' and block_number_id = '".$customer_block_details['block_number_id']."';")){
			$error = true;
			echo("Error description: " . mysqli_error($conn));die;
		}


		if(!$error && !mysqli_query($conn,"delete from kc_receipt_numbers where customer_id = '".$customer_block_details['customer_id']."' and block_id = '".$customer_block_details['block_id']."' and block_number_id = '".$customer_block_details['block_number_id']."';")){
			$error = true;
			echo("Error description: " . mysqli_error($conn));die;
		}

		if (!$error && $isEmiTaken && !mysqli_query($conn,"delete from kc_customer_emi where customer_id = '".$customer_block_details['customer_id']."' and block_id = '".$customer_block_details['block_id']."' and block_number_id = '".$customer_block_details['block_number_id']."' ")){
	        $error = true;
	        echo("Error description: " . mysqli_error($conn)); die;
	    }


		// if($error){
		// 	mysqli_rollback($conn);
		// }else{
		// 	mysqli_commit($conn);
		// }

		// // Close connection
		// mysqli_close($conn);

		// if(!$error){
		// 	//mysqli_query($conn,"update kc_block_numbers set status = '$new_status' where id = '".$block_number_id."' limit 1 ");
		// 	$_SESSION['success'] = 'Block Number has been Successfully Cancelled!';
		// 	header("Location: $page_url");
		// 	exit();
		// }else{
		// 	$_SESSION['error'] = 'Something Problem Occured!';
		// 	header("Location: $page_url");
		// 	exit();
		// }

	}
	// $_SESSION['error'] = 'Something Wrong!';
	// header("Location: $page_url");
	// exit();
// }else 
}
if(isset($_GET['action']) && $_GET['action'] == "remove" && isset($_GET['block_number']) && is_numeric($_GET['block_number'])){
	// 	//die;
	$block_number_id = $_GET['block_number'];
	echo "$block_number_id";
	$error = false;

	$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_block_numbers where id = '".$block_number_id."' limit 0,1 "));
	if(!isset($block_number_details['id'])){
		$error = true;
		$_SESSION['error'] = 'Block Number Not Found';
	}

	$customer_id = isBlockNumberBooked($conn,$block_number_id);
	if($customer_id){
		$error = true;
		$_SESSION['error'] = 'This Plot Could not be delete due to assigned to a customer';
	}

	// Till now everything is fine

	if (!$error && !mysqli_query($conn,"insert into kc_block_numbers_hist (block_number_table_id, block_id, block_number, area, road, face, status, addedon, added_by, deleted, deleted_by) select id, block_id, block_number, area, road, face, status, addedon, added_by, now() , '".$_SESSION['login_id']."' from kc_block_numbers where id = '".$block_number_id."';")){
		$error = true;
		echo("Error description: " . mysqli_error($conn));
		$_SESSION['error'] = mysqli_error($conn);
	}

	if(!$error && !mysqli_query($conn,"delete from kc_block_numbers where id = '".$block_number_id."';")){
		$error = true;
		$_SESSION['error'] = mysqli_error($conn);
	}

	if(!$error){
		$_SESSION['success'] = 'Plot Number has been Successfully Deleted!';
		header("Location: $page_url");
		exit();
	}else{
		header("Location: $page_url");
		exit();

	}
}

// }else if(isset($_GET['block_number']) && is_numeric($_GET['block_number'])){
// 	$block_number_id = $_GET['block_number'];
// 	$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select status from kc_block_numbers where id = '".$block_number_id."' limit 0,1 "));
// 	if(isset($block_number_details['status'])){
// 		$current_status = $block_number_details['status'];
// 		if($current_status == 1){
// 			$new_status = 0;
// 		}else{
// 			$new_status = 1;
// 		}
// 		mysqli_query($conn,"update kc_block_numbers set status = '$new_status' where id = '".$block_number_id."' limit 1 ");
// 		$_SESSION['success'] = 'Plot Number Status Successfully Updated!';
// 		header("Location: $page_url");
// 		exit();
// 	}
// 	$_SESSION['error'] = 'Something Wrong!';
// 	header("Location: $page_url");
// 	exit();
if(isset($_GET['action']) && $_GET['action'] == "status" && isset($_GET['block_number']) && is_numeric($_GET['block_number'])){
	$block_number_id = $_GET['block_number'];
	$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select status from kc_block_numbers where id = '".$block_number_id."' limit 0,1 "));
	if(isset($block_number_details['status'])){
		$current_status = $block_number_details['status'];
		if($current_status == 1){
			$new_status = 0;
		}else{
			$new_status = 1;
		}
		mysqli_query($conn,"update kc_block_numbers set status = '$new_status' where id = '".$block_number_id."' limit 1 ");
		$_SESSION['success'] = 'Plot Number Status Successfully Updated!';
		header("Location: $page_url");
		exit();
	}
	$_SESSION['error'] = 'Something Wrong!';
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
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
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
            <li><a href="dashboard.php"><i class="fa fa-dashboard"></i> Home </a> </li>
            <li class="active"><a href="projects.php"><i class="fa fa-bank"></i> Projects</a></li>
            <li class="active"><a href="blocks.php?project=<?php echo $project_details['id']; ?>"><i class="fa fa-building-o"></i> Blocks</a></li>
            <li class="active">Plot Numbers</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
			<div class="box">
                <div class="box-header">
					<?php
					include("../includes/notification.php"); ?>
					<div class="col-sm-8">
						<h3 class="box-title">All Plot Numbers of <strong class="text-danger"><?php echo $block_details['name']; ?></strong> of Project <strong class="text-danger"><?php echo $project_details['name']; ?></strong></h3>
					</div>
					<?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'add_plot_number_projects')) { ?>
                    <div class="col-sm-4">
						<button class="btn btn-sm btn-success pull-right" data-toggle="modal" data-target="#addBlockNumber">Add Plot Number</button>
					</div>
				<?php } ?>
				</div><!-- /.box-header -->
                <div class="box-body no-padding">

				 <table class="table table-striped table-hover table-bordered">
                    <tr>
                      <th>Sl No.</th>
					  <th>Number</th>
                      <th>PLC</th>
                      <th>Area</th>
                      <th>Road</th>
                      <th>Face</th>
                      <th>Added Date</th>
                      <th>Status</th>
                      <?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'view_projects')) {  ?>
					  <th>Action</th>
					<?php } ?>
					</tr>
					<?php
					$limit = 500;
					if(isset($_GET['page'])){
						$page = $_GET['page'];
					}else{
						$page = 1;
					}
					$total_records = mysqli_fetch_assoc(mysqli_query($conn,"select COUNT(*) as total from kc_block_numbers where block_id = '$block_id' "));
					$total_pages = ceil($total_records['total']/$limit);

					if($page == 1){
						$start = 0;
					}else{
						$start = ($page-1)*$limit;
					}
					$block_numbers = mysqli_query($conn,"select * from kc_block_numbers where block_id = '$block_id' order by cast(block_number as unsigned) limit $start,$limit ");

					//$block_numbers = mysqli_query($conn,"select * from kc_block_numbers where block_id = '$block_id' order by block_number + 0 limit $start,$limit ");
					if(mysqli_num_rows($block_numbers) > 0){
						$counter = $start+1;
						while($block_number = mysqli_fetch_assoc($block_numbers)){ ?>
							<tr>
								<td><?php echo $counter; ?></td>
								<td><?php echo $block_number['block_number'];	//$block_number['id'].'-'. ?></td>
                                <td>
                                	<?php
									$plcs = mysqli_query($conn,"select * from kc_block_number_plc where block_number_id = '".$block_number['id']."' ");
									while($plc = mysqli_fetch_assoc($plcs)){
										$plc_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_plc where id = '".$plc['plc_id']."' limit 0,1 "));
										echo $plc_details['name'].' ('.$plc_details['plc_percentage'].'%)<br>';
									}

									?>
								</td>
                                <td><?php echo $block_number['area']; ?> Sq. Ft.</td>
                                <td><?php echo $block_number['road']; ?></td>
                                <td><?php echo $block_number['face']; ?></td>
                                <td><?php echo date("d M Y h:i A",strtotime($block_number['addedon'])); ?></td>
								<td>
                                	<?php if($block_number['status'] == 1){ ?>
                                		<label class="label label-success">Active</label>
                                    <?php }else{ ?>
                                    	<label class="label label-danger">Inactive</label>
                                    <?php } ?>
                                </td>
                                <td>
                                	<?php
									if($block_number['status']){
										$button_class = 'btn-success';
										$icon_class = 'fa-lock';
										$btn_title = "Make Inactive";
									}else{
										$button_class = 'btn-danger';
										$icon_class = 'fa-unlock';
										$btn_title = "Make Active";
									}
									$customer_id = isBlockNumberBooked($conn,$block_number['id']);
									// $customer_id = isBlockNumberBooked($conn,4514);
									// echo $customer_id;die;
									// echo $_SESSION['login_id'];die;
									?>
									<?php if($customer_id){
										 if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'view_projects')) {  ?>
										<button class="btn btn-xs btn-info" type="button" data-toggle="tooltip" title="View Booking Details" onclick = "viewInformation(<?php echo $customer_id; ?>);"><i class="fa fa-eye"></i></button>
									<?php } if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'cancel_booking_projects')) {?>
                                    	<a href="<?php echo $page_url;?>&block_number=<?php echo $block_number['id']; ?>&action=cancel" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Cancel Booking" onclick = "return confirm('Are you sure you want to cancel Booking of Plot Number <?php echo $block_number['block_number']; ?>')"><i class="fa fa-remove"></i></a>
                                    <?php }}else{ ?>
                                    	<button class="btn btn-xs btn-warning" type="button" data-toggle="tooltip" title="Edit Plot Number" onclick = "editBlockNumber(<?php echo $block_number['id']; ?>);"><i class="fa fa-pencil"></i></button>
                                    <?php } ?>

									<a href="<?php echo $page_url;?>&block_number=<?php echo $block_number['id']; ?>&action=status" data-toggle="tooltip" title="<?php echo $btn_title; ?>">
										<button class="btn btn-xs <?php echo $button_class; ?>" type="button"><i class="fa <?php echo $icon_class; ?>"></i></button>
									</a>
									<?php if(!$customer_id){
										if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'plot_status_projects')) { ?>
										<a onclick="return confirm('Are you sure you want to delete this Plot');" href="<?php echo $page_url;?>&block_number=<?php echo $block_number['id']; ?>&action=remove" data-toggle="tooltip" title="Delete Plot Number">
											<button class="btn btn-xs btn-danger" type="button"><i class="fa fa-remove"></i></button>
										</a>
									<?php }} ?>
                                </td>
							</tr>
							<?php
							$counter++;
						}
					}else{
						?>
						<tr>
							<td colspan="10" align="center"><h4 class="text-red">No Record Found</h4></td>
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
								 <li <?php if((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)){ ?>class="active"<?php } ?>><a href="block_numbers.php?block=<?php echo $_GET['block']; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
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

	<div class="modal" id="addBlockNumber">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" name="add_block_number_frm" id="add_block_number_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add Plot Number</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-header with-border">
						<div class="col-md-12">
							<h3 class="box-title">Add Plot Number Panel</h3>
						</div>
					</div><!-- /.box-header -->
					<!-- form start -->
					<div class="box-body">

						<div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">Plot Number</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="block_number" name="block_number" maxlength="50" required>
						  </div>
						</div>

                        <div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">Total Area(sq. ft.)</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="area" name="area" maxlength="255" value="" required>
						  </div>
						</div>

                        <div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">PLC(%)</label>
						  <div class="col-sm-8">
							<select class="form-control select2" name="plc[]" id="plc" multiple  style="width: 100%;">
                            	<?php /*?><option value="">Select PLC</option><?php */?>
                                <?php
								$plcs = mysqli_query($conn,"select * from kc_plc where status = '1' ");
								while($plc = mysqli_fetch_assoc($plcs)){ ?>
                                	<option value="<?php echo $plc['id']; ?>"><?php echo $plc['name']; ?>(<?php echo $plc['plc_percentage']; ?> %)</option>
                                <?php } ?>
                            </select>
						  </div>
						</div>

                        <div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">Road</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="road" name="road" maxlength="255" value="" required>
						  </div>
						</div>

                        <div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">Face</label>
						  <div class="col-sm-8">
							<div class="checkbox icheck">
                              <label>
                                <input type="checkbox" value="East" name="face[]"> East
                              </label>
                            </div>
                            <div class="checkbox icheck">
                              <label>
                                <input type="checkbox" value="West" name="face[]"> West
                              </label>
                            </div>
                            <div class="checkbox icheck">
                              <label>
                                <input type="checkbox" value="North" name="face[]"> North
                              </label>
                            </div>
                            <div class="checkbox icheck">
                              <label>
                                <input type="checkbox" value="South" name="face[]"> South
                              </label>
                            </div>
						  </div>
						</div>

                    </div><!-- /.box-body -->

				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="addBlockNumber">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->


    <div class="modal" id="editBlockNumberModal">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="<?php echo $page_url;?>" name="edit_frm" id="edit_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Edit Plot Number</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-body" id="edit-block-number-container">

                    </div><!-- /.box-body -->

				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="editBlockNumber">Save changes</button>
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

	<?php require('../includes/common-js.php'); ?>

    <script type="text/javascript">
		/*function calculateFinalRate(){
			if($("#plc").val() == "yes"){
				var percentage = <?php //echo $block_details['plc_charges']; ?>;
				var default_rate = $("#rate").val();
				var final_amount = parseFloat(default_rate) + ((parseFloat(default_rate)*parseFloat(percentage))/100);
				return final_amount;
			}else{
				return $("#rate").val();
			}
		}

		function calculateFinalRateEdit(){
			if($("#plc_edit").val() == "yes"){
				var percentage = <?php //echo $block_details['plc_charges']; ?>;
				var default_rate = $("#rate_edit").val();
				var final_amount = parseFloat(default_rate) + ((parseFloat(default_rate)*parseFloat(percentage))/100);
				return final_amount;
			}else{
				return $("#rate_edit").val();
			}
		}*/

		$(function(){

		});

		function iCheckClicked(elem){
			 var for_attr = $(elem).attr('for');
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
		function editBlockNumber(block_number){
			$.ajax({
				url: '../dynamic/editBlockNumber.php',
				type:'post',
				data:{block_number:block_number},
				success: function(resp){
					$("#edit-block-number-container").html(resp);
					$("#editBlockNumberModal").modal('show');
					$(".select2").select2();
					$('input').iCheck({
					  checkboxClass: 'icheckbox_square-blue',
					  radioClass: 'iradio_square-blue',
					  click: function(){
						}
					});
				}
			});
		}
	</script>

  </body>
</html>

