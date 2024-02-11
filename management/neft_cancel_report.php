<?php
	ob_start();
	session_start();

	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/checkAuth.php");
	if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_neft_cancel_report'))){ 
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
            <li class="active">Cancel Neft Report</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
			<div class="box">
                <div class="box-header">
					<?php 
					include("../includes/notification.php"); ?>
					<div class="col-sm-10">
						<h3 class="box-title">Cancel Neft Report</h3>
					</div>
					<div class="col-sm-1">
							<a href="neft_cancel_report_excel.php" class="btn btn-sm btn-success pull-right"><i class="fa fa-file-excel-o"></i> Excel Export</a>
					</div>
					<!-- <div class="col-sm-1">
							<a class="btn btn-sm btn-danger" href="javascript:void(0);" onclick="printDiv('printContent')" data-toggle="tooltip" title="Print"><i class="fa fa-print">&nbsp;</i>PDF Export</a>
					</div> -->
                   <!--  <div class="col-sm-1">
							<a href="cheque_cancel_report.php" class="btn btn-sm btn-success pull-right">Report</a>
					</div> -->
					
				</div><!-- /.box-header -->
				
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
							<th>Remarks</th>
						</tr>
						<?php
							$limit = 100;
						if(isset($_GET['page'])){
							$page = $_GET['page'];
						}else{
							$page = 1;
						}
						$total_records = mysqli_fetch_assoc(mysqli_query($conn,"select COUNT(*) as total from kc_customer_transactions_hist where status = '1' and payment_type  = 'NEFT' AND action_type = 'Payment Cancelled'"));
						$total_pages = ceil($total_records['total']/$limit);
						// print_r($total_records);die;
						
						
						if($page == 1){
							$start = 0;
						}else{
							$start = ($page-1)*$limit;
						}
						
						$query =  "select cth.id as customer_block_id,cth.id, cth.customer_id,cth.cancel_remarks,cth.action_type, cth.bank_name,cth.remarks, cth.cheque_dd_number, cth.amount, cth.paid_date, cth.block_id, cth.block_number_id,cth.remarks, b.project_id, b.name as block_name, bn.block_number as block_number_name, bn.area, p.name as project_name, c.name_title as customer_name_title, c.name as customer_name, c.mobile as customer_mobile, c.address as customer_address from kc_customer_transactions_hist cth LEFT JOIN kc_blocks b ON cth.block_id = b.id LEFT JOIN kc_block_numbers bn ON cth.block_number_id = bn.id LEFT JOIN kc_projects p ON b.project_id = p.id LEFT JOIN kc_customers c ON cth.customer_id = c.id where cth.status = '1' AND cth.payment_type = 'NEFT' AND cth.action_type = 'Payment Cancelled' order by id desc limit $start,$limit ";
						
							
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
											<strong>Neft No : </strong><?php echo $customer['cheque_dd_number']?$customer['cheque_dd_number']:'N/A'; ?><br>
											<strong>Remarks : </strong><?php echo $customer['remarks']?$customer['remarks']:'N/A'; ?>
										</td>
										<td>
											<?php echo $customer['cancel_remarks']; ?>
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
								 <li <?php if((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)){ ?>class="active"<?php } ?>><a href="neft_cancel_report.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
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
			<form enctype="multipart/form-data" action="neft_cancel_report.php" name="add_late_payment_frm" id="add_late_payment_frm" method="post" class="form-horizontal dropzone">
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
        </section> 
          
      </div><!-- /.content-wrapper -->    
      <?php require('../includes/footer.php'); ?>

      <?php require('../includes/control-sidebar.php'); ?>
      <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
      <div class="control-sidebar-bg"></div>
    </div><!-- ./wrapper -->

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
</script>
    
  </body>
</html>