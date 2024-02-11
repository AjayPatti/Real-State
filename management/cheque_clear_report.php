<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");
if (!(userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_cheque_clear_report'))) {
	header("location:/wcc_real_estate/index.php");
	exit();
}

$url = 'cheque_clear_report.php?search=Search';

$limit = 100;
if (isset($_GET['page'])) {
	$page = $_GET['page'];
} else {
	$page = 1;
}

$page_url = $url . '&page=' . $page;

$query = "select COUNT(*) as total from kc_customer_transactions where status = '1' and payment_type  = 'Cheque' AND clear_remarks is not null ";
if(isset($_GET['search']) && isset($_GET['from_date']) && isset($_GET['to_date'])){
	$to_date = date("Y-m-d", strtotime($_GET['to_date']));
	$from_date = date("Y-m-d", strtotime($_GET['from_date']));
	$query .= " And paid_date between '".$from_date."' AND '".$to_date."' ";
}

$total_records = mysqli_fetch_assoc(mysqli_query($conn, $query));
// print_r($total_records); die;

$total_pages = ceil($total_records['total'] / $limit);
// echo $total_pages; die;

if ($page == 1) {
	$start = 0;
} else {
	$start = ($page - 1) * $limit;
}




if (isset($_GET['from_date']) && $_GET['from_date'] !='' && isset($_GET['to_date']) && $_GET['to_date'] !='') {
	$to_date = date("Y-m-d", strtotime($_GET['to_date']));
	$from_date = date("Y-m-d", strtotime($_GET['from_date']));
	// $block_number_id = (int) $_GET['to_date'];
	$url .= '&from_date=' . $from_date . '&to_date=' . $to_date;
	$uri = explode('?', $_SERVER['REQUEST_URI'])[1];

	$query =  "select cth.id as customer_block_id,cth.paid_account_no,cth.id,cth.customer_id, cth.bank_name,cth.remarks, cth.cheque_dd_number, cth.amount, cth.paid_date, cth.block_id, cth.block_number_id,cth.clear_remarks, b.project_id, b.name as block_name, bn.block_number as block_number_name, bn.area, p.name as project_name, c.name_title as customer_name_title, c.name as customer_name, c.mobile as customer_mobile, c.address as customer_address from kc_customer_transactions cth LEFT JOIN kc_blocks b ON cth.block_id = b.id LEFT JOIN kc_block_numbers bn ON cth.block_number_id = bn.id LEFT JOIN kc_projects p ON b.project_id = p.id LEFT JOIN kc_customers c ON cth.customer_id = c.id where cth.status = '1' AND cth.payment_type = 'Cheque' AND cth.clear_remarks is not null AND cth.paid_date >= '" . $from_date . "' AND cth.paid_date <= '" . $to_date . "' order by id  limit  $start,$limit ";
} else {
	$query =  "select cth.id as customer_block_id,cth.paid_account_no,cth.id,cth.customer_id, cth.bank_name,cth.remarks, cth.cheque_dd_number, cth.amount, cth.paid_date, cth.block_id, cth.block_number_id,cth.clear_remarks, b.project_id, b.name as block_name, bn.block_number as block_number_name, bn.area, p.name as project_name, c.name_title as customer_name_title, c.name as customer_name, c.mobile as customer_mobile, c.address as customer_address from kc_customer_transactions cth LEFT JOIN kc_blocks b ON cth.block_id = b.id LEFT JOIN kc_block_numbers bn ON cth.block_number_id = bn.id LEFT JOIN kc_projects p ON b.project_id = p.id LEFT JOIN kc_customers c ON cth.customer_id = c.id where cth.status = '1' AND cth.payment_type = 'Cheque' AND cth.clear_remarks is not null order by id desc limit  $start,$limit";
	// $uri = explode('?', $_SERVER['REQUEST_URI'])[1];
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
		.ui-autocomplete li {
			font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
			padding: 5px 8px;
			font-weight: bold;
		}

		.ui-autocomplete li:hover {
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
					<li class="active">Clear Cheque Report</li>
				</ol>
			</section>

			<!-- Main content -->
			<section class="content">
				<div class="box">
					<div class="box-header">
						<?php
						include("../includes/notification.php"); ?>
						<div class="col-sm-10">
							<h3 class="box-title">Clear Cheque Report</h3>
						</div>
						<div class="col-sm-1">
							<a href="cheque_clear_report_excel.php?<?php if (isset($_GET['from_date']) && $_GET['from_date'] !='' && isset($_GET['to_date']) && $_GET['to_date'] !='') {echo $uri ; }?>" class="btn btn-sm btn-success pull-right"><i class="fa fa-file-excel-o"></i> Excel Export</a>
						</div>
						<!-- <div class="col-sm-1">
							<a class="btn btn-sm btn-danger" href="javascript:void(0);" onclick="printDiv('printContent')" data-toggle="tooltip" title="Print"><i class="fa fa-print">&nbsp;</i>PDF Export</a>
					</div> -->
						<!--  <div class="col-sm-1">
							<a href="cheque_cancel_report.php" class="btn btn-sm btn-success pull-right">Report</a>
					</div> -->
						<form action="" name="search_frm" id="search_frm" method="get" class="">
							<div class="form-group col-sm-3">
								<label for="from_date">From</label>
								<input type="text" class="form-control" id="from_date" name="from_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" data-validation="date" data-validation-format="dd-mm-yyyy"   />
							</div>
							<div class="form-group col-sm-3">
								<label for="to_date">To</label>
								<input type="text" class="form-control" id="to_date" name="to_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" data-validation="date" data-validation-format="dd-mm-yyyy"  class="form-control" />
							</div>
							<div class="form-group col-sm-3">
								<button type="submit" name="search" value="Search" class="btn btn-primary" style="margin-top: 24px;"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
							</div>
						</form>

					</div><!-- /.box-header -->

					<div class="box-body no-padding" id="printContent">
						<div class="table-responsive">
							<table class="table table-striped table-hover table-bordered">
								<tr>
									<th>Sr.</th>
									<th>Project Details</th>
									<th>Customer Details</th>
									<th>Accounts Details</th>
									<th>Amount</th>
									<th>Paid Date</th>
									<th>Bank Detail</th>
									<th>Remarks</th>
								</tr>
								<?php
								// $limit = 100;
								// if (isset($_GET['page'])) {
								// 	$page = $_GET['page'];
								// } else {
								// 	$page = 1;
								// }


								// $total_records = mysqli_fetch_assoc(mysqli_query($conn, "select COUNT(*) as total from kc_customer_transactions where status = '1' and payment_type  = 'Cheque' AND clear_remarks is not null "));
								// // print_r($total_records); die;

								// $total_pages = ceil($total_records['total'] / $limit);
								// // echo $total_pages; die;

								// if ($page == 1) {
								// 	$start = 0;
								// } else {
								// 	$start = ($page - 1) * $limit;
								// }

								// $query =  "select cth.id as customer_block_id,cth.paid_account_no,cth.id,cth.customer_id, cth.bank_name,cth.remarks, cth.cheque_dd_number, cth.amount, cth.paid_date, cth.block_id, cth.block_number_id,cth.clear_remarks, b.project_id, b.name as block_name, bn.block_number as block_number_name, bn.area, p.name as project_name, c.name_title as customer_name_title, c.name as customer_name, c.mobile as customer_mobile, c.address as customer_address from kc_customer_transactions cth LEFT JOIN kc_blocks b ON cth.block_id = b.id LEFT JOIN kc_block_numbers bn ON cth.block_number_id = bn.id LEFT JOIN kc_projects p ON b.project_id = p.id LEFT JOIN kc_customers c ON cth.customer_id = c.id where cth.status = '1' AND cth.payment_type = 'Cheque' AND cth.clear_remarks is not null order by id desc limit  $start,$limit";




								// echo "<pre>";print_r($query);die;	
								$customers = mysqli_query($conn, $query);
								if (mysqli_num_rows($customers) > 0) {
									$counter = $start + 1;
									$total_debited_amt = $total_credited_amt = $total_pending_amt = 0;
									while ($customer = mysqli_fetch_assoc($customers)) {
										// echo "<pre>"; print_r($customer); die;
										$total_debited_amt += $total_debited = totalDebited($conn, $customer['customer_id'], $customer['block_id'], $customer['block_number_id']);
										$total_credited_amt += $total_credited = totalCredited($conn, $customer['customer_id'], $customer['block_id'], $customer['block_number_id']);
										$account_details = mysqli_fetch_assoc(mysqli_query($conn, "select * from kc_accounts where id = '" . $customer['paid_account_no'] . "'"));
										// echo "<pre>"; print_r($account_details); die;
										$total_pending_amt += $pending_amount = ($total_credited - $total_debited);
								?>
										<tr>
											<td>
												<?php echo $counter; ?>.</td>
											<td>
												<strong>Project : </strong><?php echo $customer['project_name']; ?><br>
												<strong>Block : </strong><?php echo $customer['block_name']; ?><br>
												<strong>Plot No. : </strong><?php echo $customer['block_number_name']; ?>

											</td>

											<td>
												<strong>Name : </strong><?php echo ($customer['customer_name_title'] . ' ' . $customer['customer_name']) . '<br>' . ' (' . customerID($customer['customer_id']) . ')'; ?><br>
												<strong>Mobile : </strong><?php echo $customer['customer_mobile']; ?><br>
												<strong>Address : </strong><?php echo $customer['customer_address']; ?>

											</td>

											<td>
												<strong>Name : </strong><?php echo $account_details['name']; ?><br>
												<strong>Bank Name : </strong><?php echo $account_details['bank_name']; ?><br>
												<strong>Branch Name : </strong><?php echo $account_details['branch_name']; ?><br>
												<strong>Account No : </strong><span class="text-danger"><?php echo $account_details['account_no']; ?></span><br>
												<strong>IFSC Code : </strong><span class="text-primary"> <?php echo $account_details['ifsc_code']; ?></span><br>
											</td>

											<td><i class="fa fa-inr"></i> <?php echo $customer['amount']; ?></td>
											<td><?php echo date('jS M Y', strtotime($customer['paid_date'])) ?></td>
											<td>
												<strong>Bank Name : </strong><?php echo $customer['bank_name'] ? $customer['bank_name'] : 'N/A'; ?><br>
												<strong>Cheque No : </strong><?php echo $customer['cheque_dd_number'] ? $customer['cheque_dd_number'] : 'N/A'; ?><br>
												<strong>Remarks : </strong><?php echo $customer['remarks'] ? $customer['remarks'] : 'N/A'; ?>
											</td>
											<td>
												<?php echo $customer['clear_remarks'] ? $customer['clear_remarks'] : 'N/A'; ?>
											</td>

										</tr>
									<?php
										$counter++;
									} ?>

								<?php
								} else {
								?>
									<tr>
										<td colspan="16" align="center">
											<h4 class="text-red">No Record Found</h4>
										</td>
									</tr>
								<?php
								}
								?>
							</table>
						</div>
					</div><!-- /.box-body -->


					<?php 
					if(isset($_GET['search']) && isset($_GET['from_date']) && isset($_GET['to_date'])){
						// print_r($page_url);die;
						if (isset($total_pages) && $total_pages > 1 ) { ?>
							<div class="box-footer clearfix">
								<ul class="pagination pagination-sm no-margin pull-right">

									<?php
									for ($i = 1; $i <= $total_pages; $i++) {
									?>
										<li <?php if ((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1) && isset($_GET['search'])) { ?>class="active" <?php } ?>><a href="<?php echo $url ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
									<?php
									}
									?>

								</ul>
							</div>
						<?php } ?>
					<?php }else 
					{

					 if (isset($total_pages) && $total_pages > 1) { ?>
						<div class="box-footer clearfix">
							<ul class="pagination pagination-sm no-margin pull-right">

								<?php
								for ($i = 1; $i <= $total_pages; $i++) {
								?>
									<li <?php if ((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)) { ?>class="active" <?php } ?>><a href="cheque_clear_report.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
								<?php
								}
								?>

							</ul>
						</div>
						<?php 
						}
					} ?>
					 
				</div><!-- /.box -->


			</section>

		</div><!-- /.content-wrapper -->
		<?php require('../includes/footer.php'); ?>

		<?php require('../includes/control-sidebar.php'); ?>
		<!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
		<div class="control-sidebar-bg"></div>
	</div><!-- ./wrapper -->

	<?php require('../includes/common-js.php'); ?>


</body>

</html>