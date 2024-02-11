<?php
	ob_start();
	session_start();

	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/checkAuth.php");
if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_ledger'))){ 
 	header("location:/wcc_real_estate/index.php");
 	exit();
 }
	$limit = 1000;
	if(isset($_GET['page'])){
		$page = $_GET['page'];
	}else{
		$page = 1;
	}

	$search = false;
	// echo "select * from kc_customer_transactions where status = '1' and is_affect_sold_amount != '1' "; die();
	$query = "select * from kc_associates_transactions where status = '1'";	// and remarks is NULL->to remove discount	// and cr_dr = 'dr'
	if(isset($_GET['associate']) && (int) $_GET['associate']>0 ){ 
		//echo "<pre>"; print_r($_GET); die;
		$customer_id = (int) $_GET['associate'];
		// print_r($_GET);
		if(isset($_GET['associate']) && isset($_GET['associate'])){
			$query .= " and associate_id = '".$customer_id."'";
		}
        
		$total_records = mysqli_num_rows(mysqli_query($conn,$query));
		$total_pages = ceil($total_records/$limit);
		
		if($page == 1){
			$start = 0;
		}else{
			$start = ($page-1)*$limit;
		}

		$query .= " order by block_number_id, cr_dr, paid_date asc limit $start,$limit";
		// print_r($query); die();
		$transactions = mysqli_query($conn,$query);
		$search = true;

		$name  = associateName($conn,$customer_id);
		// echo "<pre>"; print_r($customer); die;
		// $name = customerID($customer['id']).'-'.$customer['name_title'].' '.$customer['name'].'('.$customer['mobile'].')';
	}	
    // $query = "select * from associate_details ";	
    // $result=mysqli_query($con,$query);
    // $row = mysqli_fetch_assoc($result);
    // print_r($row);die;
   
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
            <li class="active">Ledger</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
			<div class="box">
                <div class="box-header">
					<?php 
					include("../includes/notification.php"); ?>
					<div class="col-sm-6">
						<h3 class="box-title">Associates Ledger</h3>
					</div>
					<?php /*if(isset($_GET['name']) && $_GET['name'] != ''){*/ ?>
					<?php if($search && isset($name)){ ?>
	                    <div class="col-sm-4">
		                    <a href="associate_ledger_excel_export.php?associate_id=<?php echo isset($_GET['associate'])?$_GET['associate']:''; ?>&search=Search" class="btn btn-sm btn-success pull-right"><i class="fa fa-file-excel-o"></i> Excel Export</a> 
						</div>
						<div class="col-md-2 text-right">
							<a class="btn btn-sm btn-danger" href="javascript:void(0);" onclick="printDiv('printContent')" data-toggle="tooltip" title="Print"><i class="fa fa-print">&nbsp;</i>PDF Export</a>
						</div>
					<?php } ?>
					<hr />
					<form action="" name="search_frm" id="search_frm" method="get" class="">
						<div class="form-group col-sm-3 ui-widget">
							<label for="customer">Associates <a href="javascript:void(0);" class="text-primary" data-toggle="popover" title="Customer Search Hint" data-content="'c-' for Code Search<br>'n-' for Name Search<br>'m-' for Mobile Search<br><b>Eg:</b> If want to search 00942 in only code then Search for 'c-00942' "><i class="fa fa-info-circle"></i></a></label>
						  	<?php /*<input type="text" class="form-control" id="customer" name="customer" value="<?php echo (isset($_GET['name']) && $_GET['name'] != '')?$_GET['name']:''; ?>" />*/ ?>
						  	<input type="text" class="form-control associate-autocomplete" placeholder="Name or Code or Mobile" data-for-id="search_customer">
							<input type="hidden" name="associate" id="search_customer">
						</div>
						<button type="submit" name="search" value="Search" class="btn btn-primary" style="margin-top: 24px;"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
					</form>
				</div><!-- /.box-header -->
				
                <div class="box-body no-padding" id="printContent">
				<?php /*if(isset($_GET['name']) && $_GET['name'] != ''){ ?>
					<h4 class="text-center"><?php echo customerName($conn,(isset($_GET['customer']) && $_GET['customer'] != '')?$_GET['customer']:''); ?></h4>
				<?php }*/
				if($search && isset($name )){ ?>
					<h4 class="text-center"><?php echo $name ; ?></h4>
				<?php } ?>



				 <table class="table table-striped table-hover table-bordered">
                    <tr>
						<th width="2%">Sr.</th>
						<th width="8%">Block</th>
						<th>Plot No.</th>
						<th>Area</th>
						<?php /* ?><th>Client Name</th>
						<th>Associate</th><?php */ ?>
						<th>Date</th>
						<th>Details</th>
						<th>Paid Amount</th>
						<th>Commission  Percentage</th>
						<th>Credit</th>
						<th>Debit</th>
					</tr>
					<?php
						
						if($search && mysqli_num_rows($transactions) > 0){
							$counter = 1;
							$totalCredit = $totalDebit = 0;
							while($transaction = mysqli_fetch_assoc($transactions)){
							// print_r($transaction);die;
								$transaction_details = mysqli_fetch_assoc(mysqli_query($conn,"select amount from kc_customer_transactions where id = '".$transaction['transaction_id']."' limit 0,1 "));
								// print_r($transaction_details['amount']);die;
								$block = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_customer_blocks where block_id = '".$transaction['block_id']."' and block_number_id = '".$transaction['block_number_id']."' limit 0,1 "));
                               
							
								//$total_debited = totalDebited($conn,$block['customer_id'],$block['block_id'],$block['block_number_id']);
								//$total_credited = totalCredited($conn,$block['customer_id'],$block['block_id'],$block['block_number_id']);

								$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select area from kc_block_numbers where block_id = '".$block['block_id']."' and id = '".$block['block_number_id']."' "));	// and status = '1'
								
								if($transaction['cr_dr'] == 'cr' && $transaction['remarks'] == NULL){	//if($counter == 1){//commented on 17/11/2020 due to displaying wrong bounce charges.	//$counter == 1 && removed on 01/01/2021
							        $transaction['amount'] = saleAmount($conn,$transaction['customer_id'],$transaction['block_id'],$transaction['block_number_id']);
							    }
								//$totalAmountReceived += $transaction['amount'];
                               
								//$pending_amount = ($total_credited - $total_debited);
								//$totalPendingAmount += $pending_amount;
								?>
								<tr>
									<td><?php echo $counter; ?>.</td>
									<td><?php echo blockName($conn,$transaction['block_id']); ?></td>
									<td><?php echo blockNumberName($conn,$transaction['block_number_id']); ?></td>
									<td><?php echo isset($block_details['area'])??''; ?> Sq. Ft.</td>
									<td><?php echo date("d M Y",strtotime($transaction['paid_date'])); ?></td>

									<td>
										<?php echo $transaction['payment_type']; ?>
										<?php if($transaction['payment_type'] == "Cheque" || $transaction['payment_type'] == "DD" || $transaction['payment_type'] == "NEFT" || $transaction['payment_type'] == "RTGS"){
					                        echo "<br>Bank Name: <strong>".$transaction['bank_name']."</strong>";
					                        echo "<br>".$transaction['payment_type']." Number: <strong>".$transaction['cheque_dd_number']."</strong>";
					                    }
					                    if(trim($transaction['remarks']) != ''){ echo '<br />'.$transaction['remarks']; }
					                    if(trim($transaction['remarks']) != ''){ echo '<br />'.$transaction['remarks']; }
					                    ?>	
									</td>
									<?php 
                                    $credit = associateTotalCreditedHistory($conn,$transaction['associate_id']);
									
							        $debit = associateTotalDebitedHistory($conn,$transaction['associate_id']);
                            		$associateCommission = associateCommission($conn,$transaction['associate_id']);
									// echo "<pre>";
									// print_r($associateCommission['associate_percentage']);die;
									?>
									<td><?php echo number_format($transaction_details['amount'],2); ?></td>
									<td><?php echo isset($associateCommission['associate_percentage'])??''; ?>%</td>
									<td>
                                        <?php if($transaction['cr_dr'] == "cr"){
                                            $totalCredit +=  $credit;
                                             	
											?>
											<?php echo number_format($credit,2); ?> ₹
										<?php } ?>
									</td>
									<td>
										<?php
										if($transaction['cr_dr'] == "dr"){
											$totalDebit +=  $debit;
											?>
											<?php echo number_format($debit,2); ?> ₹
										<?php } ?>
									</td>
								</tr>
								<?php	
								$counter++;
							} ?>
							<tr>
								<td colspan="8" align="right"><font size="3">Total: &nbsp;&nbsp;</font></td>
								<td class="text-success"><font size="3"><?php echo number_format($totalCredit,2); ?> ₹</font></td>
								<td class="text-success"><font size="3"><?php echo number_format($totalDebit,2); ?> ₹</font></td>
								<?php /*<td colspan="6">&nbsp;</td>
								<td colspan="6" class="text-danger"><font size="3"><?php echo number_format($totalPendingAmount,2); ?> ₹</font></td>*/ ?>
							</tr>
							<tr>
								<td colspan="8" align="right"><font size="3">Pending: &nbsp;&nbsp;</font></td>
								<td class="text-danger" colspan="2"><font size="3"><?php echo number_format($totalCredit - $totalDebit,2); ?> ₹</font></td>
								<?php /*<td colspan="6">&nbsp;</td>
								<td colspan="6" class="text-danger"><font size="3"><?php echo number_format($totalPendingAmount,2); ?> ₹</font></td>*/ ?>
							</tr>
							<?php
						}else{
							?>
							<tr>
								<td colspan="9" align="center"><h4 class="text-red">No Record Found</h4></td>
							</tr>
							<?php
						}
						?>
                  </table>
                </div><!-- /.box-body -->
				
				<?php if(isset($total_pages) && $total_pages > 1){ ?>
					<div class="box-footer clearfix">
					  <ul class="pagination pagination-sm no-margin pull-right">
					   
						<?php
							for($i = 1; $i <= $total_pages; $i++){
								?>
								 <li <?php if((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)){ ?>class="active"<?php } ?>><a href="associate_ledger.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
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
	
    <script type="text/javascript">
		
	</script>
    
  </body>
</html>