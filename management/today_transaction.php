<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");
if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_today_transaction'))){ 
 	header("location:/wcc_real_estate/index.php");
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
            <li class="active">Current Transaction</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
			<div class="box">
                <div class="box-header">
					<?php 
					include("../includes/notification.php"); ?>
					<div class="col-sm-11">
						<h3 class="box-title">All Current Transaction</h3>
					</div>

          <?php 

          $tnx = mysqli_query($conn,"select * from kc_customer_transactions where addedon between '".date('Y-m-d 00:00:01')."' and '".date('Y-m-d 23:59:59')."' and cr_dr = 'dr'");

          if(mysqli_num_rows($tnx) > 0){ ?>
            <div class="col-sm-1">
                <a href="current_transaction_excel_export.php" class="btn btn-sm btn-success pull-right"><i class="fa fa-file-excel-o"></i> Excel Export</a>
            </div>
          <?php } ?>

				</div><!-- /.box-header -->
                <div class="box-body no-padding">
				
				 <table class="table table-striped table-hover table-bordered">
          <tr>
            <th>Sl No.</th>
					  <th>Customer Details</th>
					  <th>Block</th>
					  <th>Amount</th>
					  <th>Payment Type</th>
					  <th>Bank Details</th>
            <th>Date</th>
            <!-- <th>Status</th> -->
					</tr>
					<?php
					$limit = 50;
					if(isset($_GET['page'])){
						$page = $_GET['page'];
					}else{
						$page = 1;
					}
					
					$total_records = mysqli_fetch_assoc(mysqli_query($conn,"select COUNT(*) as total from kc_customer_transactions where addedon between '".date('Y-m-d 00:00:01')."' and '".date('Y-m-d 23:59:59')."' and cr_dr = 'dr'"));
					$total_pages = ceil($total_records['total']/$limit);
					
					if($page == 1){
						$start = 0;
					}else{
						$start = ($page-1)*$limit;
					}
					
					$transactions = mysqli_query($conn,"select * from kc_customer_transactions where addedon between '".date('Y-m-d 00:00:01')."' and '".date('Y-m-d 23:59:59')."' and cr_dr = 'dr' limit $start,$limit ");
          
					if(mysqli_num_rows($transactions) > 0){
						$counter = $start + 1;
							$totalAmountReceived = $totalPendingAmount = 0;
						while($transaction = mysqli_fetch_assoc($transactions)){ 
							$customer = mysqli_fetch_assoc(mysqli_query($conn,"select id,name_title,name,mobile,address from kc_customers where id = '".$transaction['customer_id']."'"));
							$block = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_blocks where id = '".$transaction['block_id']."'"));
							$totalAmountReceived += $transaction['amount'];
							?>
							<tr>
								<td><?php echo $counter; ?></td>
								<td>
									<strong>Name : </strong><?php echo ($customer['name_title'].' ' .$customer['name']).'<br>'.' ('.customerID($customer['id']).')'; ?><br>
									<strong>Mobile : </strong><?php echo $customer['mobile']; ?><br>
									<strong>Address : </strong><?php echo $customer['address']; ?>
										
								</td>
                    <td><?php echo $block['name']; ?> <br>
                    	<strong>Plot No : </strong><?php echo $transaction['block_number_id']; ?>
                    </td>
                    <td><i class="fa fa-inr"></i> <?php echo $transaction['amount']; ?></td>
                    <td><?php echo $transaction['payment_type']?$transaction['payment_type']:'N/A'; ?></td>
                    <td>
                    	<?php if($transaction['payment_type'] == 'Cash'){ ?>
                    		<strong> Cash Payment</strong>
                    	<?php } else{ ?>
                    		<strong>Bank Name : </strong><?php echo $transaction['bank_name']?$transaction['bank_name']:'N/A'; ?><br>
                		<?php if($transaction['payment_type'] == 'NEFT'){?>
                			<strong>NEFT Number : </strong><?php echo $transaction['cheque_dd_number']?$transaction['cheque_dd_number']:'N/A'; ?>
                    	<?php } elseif($transaction['payment_type'] == 'Cheque'){?>
                    		<strong>Cheque Number : </strong><?php echo $transaction['cheque_dd_number']?$transaction['cheque_dd_number']:'N/A'; ?>
                    	<?php } }?>
                    </td>
                    <td><?php echo date("d M Y h:i A",strtotime($transaction['addedon'])); ?></td>
  	<!-- <td>
                    	<?php if($transaction['status'] == 1){ ?>
                    		<label class="label label-success">Active</label>
                        <?php }else{ ?>
                        	<label class="label label-danger">Inactive</label>
                        <?php } ?>
                    </td> -->
							</tr>
							<?php
							$counter++;
							}?>
							<td colspan="3" align="right"><strong size="3">Total: &nbsp;&nbsp;</strong></td>
								<td><font size="3"  class="text-success"><?php echo number_format($totalAmountReceived,2); ?> ₹</font></td>
							<?php
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
								 <li <?php if((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)){ ?>class="active"<?php } ?>><a href="today_transaction.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
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
	


    <?php require('../includes/common-js.php'); ?>
	
   
    
  </body>
</html>

