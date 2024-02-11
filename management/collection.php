<?php
	ob_start();
	session_start();

	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/checkAuth.php");
	if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_day_book'))){ 
		header("location:/wcc_real_estate/index.php");
		exit();
	}
	$url = 'collection.php?search=Search';

	$limit = 500;
	if(isset($_GET['page'])){
		$page = $_GET['page'];
	}else{
		$page = 1;
	}

	$search = false;
	$query = "SELECT customer_id, block_id, block_number_id, payment_type, bank_name, cheque_dd_number, amount, paid_date, add_transaction_remarks,addedon,added_by from kc_customer_transactions where status = '1' and cr_dr = 'dr' and remarks is NULL and is_affect_sold_amount != '1'";	// and remarks is NULL->to remove discount	

	if( (isset($_GET['from_date']) && isset($_GET['to_date'])) || (isset($_GET['associate']) && $_GET['associate']>0) || (isset($_GET['addedby']) && $_GET['addedby']>0) ){ 
		// echo "<pre>"; print_r($_GET); die;
		if(isset($_GET['from_date']) && isset($_GET['to_date'])){
			$query .= " and paid_date BETWEEN '".date("Y-m-d",strtotime($_GET['from_date']))."' AND '".date("Y-m-d",strtotime($_GET['to_date']))."'";
			//print_r($query);die();
			// $abr_receipt .= " and addedon BETWEEN '".date("Y-m-d 00:00:00",strtotime($_GET['from_date']))."' AND '".date("Y-m-d 00:00:00",strtotime($_GET['to_date']))."'";
			$url .= '&from_date='.$_GET['from_date'].'&to_date='.$_GET['to_date'];
		}

		if(isset($_GET['associate']) && $_GET['associate']>0){
			$query .= " and block_number_id IN (select block_number_id from kc_customer_blocks where status = '1' and associate = '".$_GET['associate']."') ";
			$url .= '&associate='.$_GET['associate'];
		}
		
		if(isset($_GET['addedby']) && $_GET['addedby']>0){
			$query .= " and added_by = '".$_GET['addedby']."' ";
			// print_r($query);
			$url .= '&addedby='.$_GET['addedby'];
		}
		
	}	
	$combile_data ='';
	$abr_receipt = "SELECT name as customer_id, project_block_plotnumber_totalarea as block_id,remarks, payment_type, bank_name, cheque_dd_number, paid_amount, paid_date, add_transaction_remarks, deleted,added_by from kc_avr_receipt where status = '1' and deleted is null";

	if( (isset($_GET['from_date']) && isset($_GET['to_date'])) || (isset($_GET['associate']) && $_GET['associate']>0) ){ 
		//echo "<pre>"; print_r($_GET); die;
		if(isset($_GET['from_date']) && isset($_GET['to_date'])){
			// $query .= " and paid_date BETWEEN '".date("Y-m-d",strtotime($_GET['from_date']))."' AND '".date("Y-m-d",strtotime($_GET['to_date']))."'";
			$abr_receipt .= " and addedon BETWEEN '".date("Y-m-d 00:00:01",strtotime($_GET['from_date']))."' AND '".date("Y-m-d 23:59:59",strtotime($_GET['to_date']))."'";
		// print_r($abr_receipt);die();
			$url .= '&from_date='.$_GET['from_date'].'&to_date='.$_GET['to_date'];
		}
		$combile_data = $query.' UNION '.$abr_receipt;
		// print_r($combile_data);die();
		$total_records = mysqli_num_rows(mysqli_query($conn,$combile_data));
		// $total_record = mysqli_num_rows(mysqli_query($conn,$abr_receipt));
		
		// echo $total_records;die;
		$total_pages = ceil($total_records/$limit);
		
		if($page == 1){
			$start = 0;
		}else{
			$start = ($page-1)*$limit;
		}
	
		$combile_data .= "order by addedon desc limit $start,$limit";
		
		// $abr_receipt .= "order by addedon asc limit $start,$limit";
		$transactions = mysqli_query($conn,$combile_data);
		
		$search = true;
	}
		$combile_data = $query.' UNION '.$abr_receipt;
		// print_r($combile_data);die();
		$total_records = mysqli_num_rows(mysqli_query($conn,$combile_data));
		// $total_record = mysqli_num_rows(mysqli_query($conn,$abr_receipt));
		
		// echo $total_records;die;
		$total_pages = ceil($total_records/$limit);
		
		if($page == 1){
			$start = 0;
		}else{
			$start = ($page-1)*$limit;
		}
	
		$combile_data .= " order by addedon desc limit $start,$limit";
		// $abr_receipt .= "order by addedon asc limit $start,$limit";
		$transactions = mysqli_query($conn,$combile_data);
		
		$search = true;

	
		// $aaa = mysqli_fetch_array($transactions);
		// echo "<pre>"; print_r($aaa['customer_id']);die();


	
	
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
            <li class="active">Business</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
			<div class="box">
                <div class="box-header">
					<?php 
					include("../includes/notification.php"); ?>
					<div class="col-sm-10">
						<h3 class="box-title">All Business</h3>
					</div>
                	<?php if($search && mysqli_num_rows($transactions) > 0){ ?>
                    <div class="col-sm-1">
							<!-- <a href="day_book_excel_export.php?from_date=<?php //echo isset($_GET['from_date'])?$_GET['from_date']:''; ?>&to_date=<?php //echo isset($_GET['to_date'])?$_GET['to_date']:''; ?>&associate=<?php //echo isset($_GET['associate'])?$_GET['associate']:''; ?>&addedby=<?php //echo isset($_GET['addedby'])?$_GET['addedby']:''; ?>&search=Search" class="btn btn-sm btn-success pull-right"><i class="fa fa-file-excel-o"></i> Excel Export</a> -->
							<a href="javascript:void(0)" data-from_date="<?php echo isset($_GET['from_date'])?$_GET['from_date']:''; ?>" data-to_date="<?php echo isset($_GET['to_date'])?$_GET['to_date']:''; ?>" data-associate="<?php echo isset($_GET['associate'])?$_GET['associate']:''; ?>" data-addedby="<?php echo isset($_GET['addedby'])?$_GET['addedby']:''; ?>" search="Search" class="btn btn-sm btn-success pull-right" onclick="downloadExcel(this)" download ><i class="fa fa-file-excel-o"></i> Excel Export</a>
					</div>
					<div class="col-md-1">
                			<a class="btn btn-sm btn-danger" href="javascript:void(0);" onclick="printDiv('printContent')" data-toggle="tooltip" title="Print"><i class="fa fa-print">&nbsp;</i>PDF Export</a>
                	</div>
                	<?php } ?>
					<hr />
					<form action="" name="search_frm" id="search_frm" method="get" class="">
						<div class="form-group col-sm-3">
							<label for="from_date">From</label>
						  	<input type="date" class="form-control" id="from_date" name="from_date" data-validation-format="dd-mm-yyyy" />
						</div>
						<div class="form-group col-sm-3">
							<label for="to_date">To</label>
							<input type="date" class="form-control" id="to_date" name="to_date" data-validation-format="dd-mm-yyyy" class="form-control" />
						</div>
						<div class="form-group col-sm-3">
							<label for="associate">Associate <a href="javascript:void(0);" class="text-primary" data-toggle="popover" title="Associate Search Hint" data-content="'c-' for Code Search<br>'n-' for Name Search<br>'m-' for Mobile Search<br><b>Eg:</b> If want to search 9651 in only code then Search for 'c-9651' "><i class="fa fa-info-circle"></i></a></label>
							<input type="text" class="form-control associate-autocomplete" data-for-id="associate" placeholder="Name or Code or Mobile">
							<input type="hidden" name="associate" id="associate">							
						</div>
						<div class="form-group col-sm-2">
							<label>Added By</label>
							<input type="text" class="form-control addedby-autocomplete" data-for_id="addedby_id" placeholder="Name">
							<input type="hidden" name="addedby" id="addedby_id">
						</div>
						<input type="submit" name="search" value="Search" class="btn btn-sm btn-primary" style="margin-top: 25px;">
					</form>
				</div><!-- /.box-header -->
				
                <div class="box-body no-padding" id="printContent">
					<div class="table-responsive">
						
                	<?php
					

					if($search){ ?>
						<h3 style="text-align: center;">&nbsp;All Plot Numbers of Block</h3>
					<?php } ?>
				
				 <table class="table table-striped table-hover table-bordered">
                    <tr>
						<th>Sr.</th>
						<th width="8%">Block</th>
						<th>Plot No.</th>
						<th>Area</th>
						<th>Customer</th>
						<th>Associate</th>
						<th>Amount Received</th>
						<th>Payment Mode</th>
						<th>Type of Payment</th>
						<th>Date</th>
						<th>Added By</th>
					</tr>
					<?php
						
						if($search && mysqli_num_rows($transactions) > 0){
							$counter = $start+1;
							$totalAmountReceived = $totalPendingAmount = 0;
							while($transaction = mysqli_fetch_assoc($transactions)){
								// print_r($transaction['block_number_id']);
								$block = mysqli_fetch_assoc(mysqli_query($conn,"select id, customer_id, block_id, block_number_id, rate_per_sqft, final_rate, customer_payment_type, number_of_installment, installment_amount, emi_payment_date, registry, registry_date, registry_by, sales_person_id, associate, associate_percentage, status, addedon, added_by from kc_customer_blocks where block_id = '".$transaction['block_id']."' and block_number_id = '".$transaction['block_number_id']."' limit 0,1 "));

								$addedBy = mysqli_fetch_assoc(mysqli_query($conn,'select name from kc_login where id = "'.$transaction['added_by'].'"'));

								if(!empty($block)){
									$total_debited = totalDebited($conn,$block['customer_id'],$block['block_id'],$block['block_number_id']);
									$total_credited = totalCredited($conn,$block['customer_id'],$block['block_id'],$block['block_number_id']);
									$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select area from kc_block_numbers where block_id = '".$block['block_id']."' and id = '".$block['block_number_id']."' and status = '1' "));

								}
								else{
									$total_debited = 0;
									$total_credited = 0;
									$block_details = '';
								}


								
								$totalAmountReceived += $transaction['amount'];
								
								// print_r($totalAmountReceived); 
								$pending_amount = ($total_credited - $total_debited);
								$totalPendingAmount += $pending_amount;
								
								?>
								<tr>
									<td><?php echo $counter; ?>.</td>
									<?php if($transaction['block_id'] > 0){ ?>
									<td><?php echo blockName($conn,$transaction['block_id']); ?></td>
									<?php }else{ ?>
									<td colspan="3"><?php echo $transaction['block_id']; ?>  Sq. Ft.</td>
									<?php } ?>

									<?php if($transaction['block_number_id'] > 0){ ?>
										<td><?php echo blockNumberName($conn,$transaction['block_number_id']); ?></td>
									<?php }else{ ?>
										
									<?php } ?>

									<?php if(!empty($block_details['area'])){ ?>
										<td><?php echo $block_details['area']; ?>  Sq. Ft.</td>
									<?php }else{ ?>
										
										
									<?php } ?>

									<td><?php echo ($transaction['customer_id'] > 0)?customerName($conn,$transaction['customer_id']).' ('.customerID($transaction['customer_id']).')':$transaction['customer_id']; ?></td>
									<?php  $associate = mysqli_fetch_assoc(mysqli_query($conn,'select * from kc_associate_percentage where customer_id = "'.$block['customer_id'].'"'));
									?>	
									<td><?php echo (!empty($associate['associate']))?associateName($conn,$associate['associate']):'AVR'; ?></td>
									<td><?php echo number_format($transaction['amount'],2); ?> ₹</td>
									<td>
										<?php echo $transaction['payment_type']; ?>
										<?php if($transaction['payment_type'] == "Cheque" || $transaction['payment_type'] == "DD" || $transaction['payment_type'] == "NEFT" || $transaction['payment_type'] == "RTGS"){
					                        echo "<br>Bank Name: <strong>".$transaction['bank_name']."</strong><br>";
					                        echo $transaction['payment_type']." Number: <strong>".$transaction['cheque_dd_number']."</strong>";

					                    } ?>
									</td>
									<td><?php if(trim($transaction['add_transaction_remarks']) != ''){ echo $transaction['add_transaction_remarks']; }	?></td>
									<td><?php echo date("d M Y",strtotime($transaction['paid_date'])); ?></td>
									<td><?php echo $addedBy['name']; ?></td>
								</tr>
								<?php	
								$counter++;} 
								?> 
								
									<td colspan="6" align="right"><font size="3">Total: &nbsp;&nbsp;</font></td>
									<td class="text-success"><font size="3"><?php echo number_format($totalAmountReceived,2); ?> ₹</font></td>
									<td colspan="6">&nbsp;</td>
								
								
								<?php 	
								$query = "select sum(amount) as amt from kc_customer_transactions where status = '1' and cr_dr = 'dr' and remarks is NULL and is_affect_sold_amount != '1' ";
								if(isset($_GET['from_date']) && isset($_GET['to_date'])){
									$query .= " and paid_date BETWEEN '".date("Y-m-d",strtotime($_GET['from_date']))."' AND '".date("Y-m-d",strtotime($_GET['to_date']))."'";
									$url .= '&from_date='.$_GET['from_date'].'&to_date='.$_GET['to_date'];
								}
								if(isset($_GET['associate']) && $_GET['associate']>0){
									$query .= " and block_number_id IN (select block_number_id from kc_customer_blocks where status = '1' and associate = '".$_GET['associate']."') ";
									$url .= '&associate='.$_GET['associate'];
								}
								$transactions = mysqli_query($conn,$query);
								$grand_total = mysqli_fetch_assoc($transactions);
								
								if(isset($total_pages) && $total_pages > 1){
									if(!$search && mysqli_num_rows($transactions) > 0){
										$sup_grand = $grand_total['amt'] +AVRPaidAmount($conn);
									// print_r(AVRPaidAmount($conn));
								 	?>
								 	<tr>
										<td colspan="6" style="text-align:right;"><font size="3">Grand Total: &nbsp;&nbsp;</font></td>
										<td class="text-success"><font size="3"><?php echo number_format($sup_grand,2); ?> ₹</font></td> <!-- $sup_grand place of amount -->
									</tr>
									<?php }else{ ?>
									<tr>
										<td colspan="6" align="right"><font size="3">Grand Totaaaal: &nbsp;&nbsp;</font></td>
									 	<td class="text-success"><font size="3"><?php echo number_format($grand_total['amt'],2); ?> ₹</font></td>
									</tr>
									 
									<?php }
								} ?>

							 <?php 

						}else{
							?>
						


							<tr>
								<td colspan="13" align="center"><h4 class="text-red">No Record Found</h4></td>
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
								 <li <?php if((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)){ ?>class="active"<?php } ?>><a href="<?php echo $url; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
								<?php
							}
						?>
						
					  </ul>
					</div>
				<?php 
			
			}
			
		
			
			?>
				
              </div><!-- /.box -->
        </section> 
          
      </div><!-- /.content-wrapper -->    
	  <script>
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

		function downloadExcel(elem){
			let dateFrom = $(elem).data('from_date')
			console.log(dateFrom)
			let dateTo = $(elem).data('to_date')
			let associate = $(elem).data('#associate');
			let addedbyId = $(elem).data('#addedby');
			// var  url = '';
			if((dateFrom !='' && dateTo != '') || (associate !=undefined || addedbyId !=undefined)){
				url ='?from_date='+dateFrom+'&to_date='+dateTo+'&associate='+associate+'&addedby='+addedbyId +'&search=Search' ;		
			}else{
				url = '';
			}
			$.ajax({
				url:'day_book_excel_export.php',
				type:'get',
				data:{from_date:dateFrom,to_date:dateTo,associate:associate,addedby:addedbyId},
				dataType:'',
				success:function(resp){
					if(url !=''){
						window.location.href = `day_book_excel_export.php${url}`;
					}else{
						window.location.href = 'day_book_excel_export.php';
					}
				}
			})
		}

	  </script>
      <?php require('../includes/footer.php'); ?>
    </div><!-- ./wrapper -->

    <?php require('../includes/common-js.php'); ?>    
  </body>
</html>