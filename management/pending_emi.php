<?php
	ob_start();
	session_start();

	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/checkAuth.php");
if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_pending_emi'))){ 
 	header("location:/wcc_real_estate/index.php");
 	exit();
 }
	$limit = 100;
	if(isset($_GET['page'])){
		$page = $_GET['page'];
	}else{
		$page = 1;
	}
	$counter = 1;
	$search = false;
	$url = 'pending_emi.php?search=Search';
	$query = "select ce.*, a.name as associate_name, a.code as associate_code, a.mobile_no as associate_mobile_no,cb.addedon from kc_customer_emi ce LEFT JOIN kc_customer_blocks cb ON ce.customer_id = cb.customer_id and ce.block_id = cb.block_id and ce.block_number_id = cb.block_number_id LEFT JOIN kc_associates a ON cb.associate = a.id  ";
	// print_r($query);die();
	// echo "select ce.*, a.name as associate_name, a.code as associate_code, a.mobile_no as associate_mobile_no from kc_customer_emi ce LEFT JOIN kc_customer_blocks cb ON ce.customer_id = cb.customer_id and ce.block_id = cb.block_id and ce.block_number_id = cb.block_number_id LEFT JOIN kc_associates a ON cb.associate = a.id  ";
	if( (isset($_GET['from_date']) && isset($_GET['to_date'])) || (isset($_POST['emi']) && $_POST['emi']>0) ){ 
		// echo "<pre>"; print_r($_GET); die;
		if(isset($_GET['from_date']) && isset($_GET['to_date'])){
			$query .= " where ce.emi_date BETWEEN '".date("Y-m-d",strtotime($_GET['from_date']))."' AND '".date("Y-m-d",strtotime($_GET['to_date']))."'";
			$url .= '&from_date='.$_GET['from_date'].'&to_date='.$_GET['to_date'];
			// echo $query;
		}
		// // print_r($_GET['search_project']);die();
		// $array = explode(",", $_GET['search_project']);
		// print_r($array);die();
		if(isset($_GET['search_project']) && sizeof($_GET['search_project'])>0){
			// echo "<pre>"; print_r($_GET); die();
			$search_projects = implode(",", $_GET['search_project']);
			
			$query .= " and cb.block_id IN (select id from kc_blocks where status = '1' and project_id IN (".$search_projects.") )";
			foreach ($_GET['search_project'] as $value) {
				$url .= '&search_project[]='.$value;
			}
		}
		if(isset($_GET['emi']) && ($_GET['emi']) == 'pending'){
			$query .= " and ce.emi_amount > ce.paid_amount";
			$url .= '&emi='.$_GET['emi'];
			// echo $query;

		}elseif(isset($_GET['emi']) && ($_GET['emi']) == 'paid'){
			$query .= " and ce.emi_amount = ce.paid_amount";
			$url .= '&emi='.$_GET['emi'];
		}

		if(isset($_GET['search_associate']) && (int) $_GET['search_associate'] > 0){
			$search_associates = implode(",", $_GET['search_associate']);
			// print_r($search_associates);die();
			$associate_id = (int) $search_associates;
			$query .= " and cb.associate = '$associate_id'";
			// print_r($query);die();
			foreach ($_GET['search_associate'] as $value) {
				$url .= '&search_associate[]='.$value;
			}
		}
		// echo $query; die;
		$query .= " group by ce.customer_id,ce.block_id,ce.block_number_id";
		// echo $query; die;
		$total_records = mysqli_num_rows(mysqli_query($conn,$query));	// or die(mysqli_error($conn))
		$total_pages = ceil($total_records/$limit);
		
		if($page == 1){
			$start = 0;
		}else{
			$start = ($page-1)*$limit;
		}
		$counter = $start+1;
		if(isset($_GET['action']) && $_GET['action'] == "export"){
			$query .= " order by a.name, ce.emi_date ";
		}else{
			$query .= " order by a.name, ce.emi_date asc limit $start,$limit";
		}
		

		$customers = mysqli_query($conn,$query);
		$search = true;


	}	


	if(isset($_GET['action']) && $_GET['action'] == "export"){

		header("Content-Type: application/xls");    
		header("Content-Disposition: attachment; filename=pending_emi.xls");  
		header("Pragma: no-cache"); 
		header("Expires: 0");
		
		echo '<table border="1">';
		
		echo '<tr><th>Sl No.</th><th>Customer</th><th>Customer Mobile</th><th>Customer Address</th><th>Project Name</th><th>Block Name</th><th>Block Number</th><th>Staus</th><th>EMI Amount</th><th>Pending EMI</th><th>Paid Amount</th><th>Due Amount</th><th>EMI Date</th><th>Booking Date</th><th>Associate Code</th><th>Associate Name</th><th>Associate Mobile</th></tr>';
		
		$counter = 1;
		foreach($customers as $customer){
			$customer_detail = customerNIAM($conn,$customer['customer_id'],true);
			$totalPendingEMIAmount = totalPendingEMIAmount($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id'],$_GET['from_date'],$_GET['to_date']);
			?>
			<tr>
				<td><?php echo $counter; ?></td>
				<td><?php echo $customer_detail['name_title'].' ' .$customer_detail['name'].' ('.customerID($customer['customer_id']).')'; ?></td>
				<td><?php echo $customer_detail['mobile']; ?></td>
				<td><?php echo $customer_detail['address']; ?></td>
				<td>
					<?php echo blockProjectName($conn,$customer['block_id']); ?>
				</td>
				<td>
					<?php echo blockName($conn,$customer['block_id']); ?>
				</td>
				<td>
					<?php echo blockNumberName($conn,$customer['block_number_id']); ?>
				</td>
				<td>
					<?php //if($customer['emi_amount'] > $customer['paid_amount']){
					if($totalPendingEMIAmount > 0){
					 	echo 'Pending';
					 }else{
					 	echo 'Paid';
					 } ?>		
				 </td>
				<td><?php echo number_format($customer['emi_amount'],2,'.',''); ?></td>
				<td>Count : <strong><?php echo totalPendingEMI($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id'],$_GET['from_date'],$_GET['to_date']); ?></strong>
				</td>
				<td><?php echo number_format(totalPaidEMIAmount($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id'],$_GET['from_date'],$_GET['to_date']),2,'.',''); ?></td>
				<td><?php echo number_format($totalPendingEMIAmount,2,'.',''); ?>
				</td>
				<td><?php echo date('d-m-Y',strtotime($customer['emi_date'])); ?></td>
				<td><?php echo date('d-m-Y',strtotime($customer['addedon'])); ?></td>
				<td><?php echo $customer['associate_code']; ?></td>
				<td><?php echo $customer['associate_name']; ?></td>
				<td><?php echo $customer['associate_mobile_no']; ?></td>
			</tr>		
							
		<?php
		$counter++;
		}
		echo '</table>';
		die;
	}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>WCC| Admin Panel</title>
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
            <li class="active">Pending EMI</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
			<div class="box">
                <div class="box-header">
					<?php 
					include("../includes/notification.php"); ?>
					<div class="col-sm-10">
						<h3 class="box-title">Pending EMI</h3>
					</div>
                	<?php if($search && mysqli_num_rows($customers) > 0){ ?>
                   <div class="col-sm-1">
							<a href="<?php echo $url; ?>&action=export" class="btn btn-sm btn-success pull-right" data-toggle="tooltip" title="Export to Excel"><i class="fa fa-file-excel-o">&nbsp;</i>Excel Export</a>
					</div>
					<div class="col-sm-1">
							<a class="btn btn-sm btn-danger" href="javascript:void(0);" onclick="printDiv('printContent')" data-toggle="tooltip" title="Print"><i class="fa fa-print">&nbsp;</i>PDF Export</a>
					</div>
					<?php } ?>
					<hr />
					<form action="" name="search_frm" id="search_frm" method="get" class="">
						<div class="col-md-12">
							<div class="form-group col-sm-2">
							<label for="from_date">From</label>
						  	<input type="text" class="form-control" id="from_date" name="from_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask=""  data-validation="date" data-validation-format="dd-mm-yyyy" />
						</div>
						<div class="form-group col-sm-2">
							<label for="to_date">To</label>
							<input type="text" class="form-control" id="to_date" name="to_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask=""  data-validation="date" data-validation-format="dd-mm-yyyy" class="form-control" />
						</div>
						<div class="form-group col-sm-2">
							<label for="search_project" class="text-left">Project</label>
						  	<select class="form-control select2" id="search_project" name="search_project[]" onChange="search_getBlocks(this.value);" multiple  readonly>
	                        	<option value="">Select Project</option>
	                            <?php
								$projects = mysqli_query($conn,"select * from kc_projects where status = '1' group by id ");
								while($project = mysqli_fetch_assoc($projects)){ 
								?>
                            	<option value="<?php echo $project['id']; ?>"><?php echo $project['name']; ?></option>
	                            <?php } ?>
                        	</select>
						</div>
						<div class="form-group col-md-2">
							<label for="search_associate" class="text-left">Associate <a href="javascript:void(0);" class="text-primary" data-toggle="popover" title="Associate Search Hint" data-content="'c-' for Code Search<br>'n-' for Name Search<br>'m-' for Mobile Search<br><b>Eg:</b> If want to search 9651 in only code then Search for 'c-9651' "><i class="fa fa-info-circle"></i></a></label>
							<select class="form-control select2-ajax" id="search_associate" name="search_associate[]" multiple  readonly>
	                        	<option value="">Select Associate</option>
	                            
                        	</select>
                    	</div>
						<div class="form-group col-sm-3 " style="padding-top: 25px;padding-left: 46px;" >
							
						  	 <label class="radio-inline"><input type="radio" name="emi" value="pending" id="pending" data-validation="required">&nbsp;&nbsp;Pending</label>
							<label class="radio-inline"><input type="radio" name="emi" value="paid" id="paid" data-validation="required">&nbsp;&nbsp;Paid</label>
							<label class="radio-inline"><input type="radio" name="emi" value="all" id="all" data-validation="required">&nbsp;&nbsp;All</label> 
						</div>
						<input type="submit" name="search" value="Search" class="btn btn-sm btn-primary" style="margin-top: 25px;">
						</div>
					</form>
				</div><!-- /.box-header -->
				
                <div class="box-body no-padding" id="printContent">
					
               

				 <table class="table table-striped table-hover table-bordered">
                    <tr>
						<th>Sr.</th>
						<th>Customer </th>
						<th>Plot Details</th>
						<th>Status</th>
						<th>EMI Amount</th>
						<th>Paid Amount</th>
						<th>Due Amount</th>
						<th>EMI Date</th>
						<th>Booking Date</th>
						<th>Associate </th>
					</tr>
					<?php

						if(isset($customers) && mysqli_num_rows($customers) > 0){
							while($customer = mysqli_fetch_assoc($customers)){
								$totalPendingEMIAmount = totalPendingEMIAmount($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id'],$_GET['from_date'],$_GET['to_date']);
								?>
								<tr>
									<td><?php echo $counter; ?></td>
									<td>
										<?php echo customerNIAM($conn,$customer['customer_id']); ?>
									</td>
									<td>
										<?php echo blockProjectName($conn,$customer['block_id']).'<br>'.blockName($conn,$customer['block_id']).'('.blockNumberName($conn,$customer['block_number_id']).')'; ?>
									</td>
										<?php //if($customer['emi_amount'] > $customer['paid_amount']){
										if($totalPendingEMIAmount > 0){
										 	$btn = 'btn btn-xs btn-danger';
										 	$status =  'Pending';
										 }else{
										 	$btn = 'btn btn-xs btn-success';
										 	$status =  'Paid';
										 } ?>		
									<td><a class="<?php echo $btn; ?>"><?php echo $status;?></a></td>
									<td><?php echo number_format($customer['emi_amount'],2,'.',''); ?><br>
									Count : <strong><?php echo totalPendingEMI($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id'],$_GET['from_date'],$_GET['to_date']); ?></strong></td>
									<td><?php echo number_format(totalPaidEMIAmount($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id'],$_GET['from_date'],$_GET['to_date']),2,'.',''); ?>
									</td>
									<td><?php echo number_format($totalPendingEMIAmount,2,'.',''	); ?>
									</td>
									<td><?php echo date('j M Y',strtotime($customer['emi_date'])); ?>
									</td>
									<td><?php echo date('j M Y',strtotime($customer['addedon'])); ?>
									</td>
									<td><?php echo $customer['associate_code'].'-'.$customer['associate_name'].'('.$customer['associate_mobile_no'].')'; ?></td>
								</tr>	
									
						<?php	
						$counter++;

						} ?>
						<?php
					}else{
						?>
						<tr>
							<td colspan="11" align="center"><h4 class="text-red">No Record Found</h4></td>
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
								 <li <?php if((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)){ ?>class="active"<?php } ?>><a href="<?php echo $url;?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
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
		$(document).ready(function() {
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
		    $( "#customer" ).autocomplete({
				source: function( request, response ) {
					        $.ajax( {
					          url: "../dynamic/getCustomers.php",
					          type:"post",
					          dataType: "json",
					          data: {
					            term: request.term
					          },
					          success: function( data ) {
					            //alert(data);
					            response( data );
					          }
					        } );
					      },
				minLength: 2,
				select: function( event, ui ) {
					//log( "Selected: " + ui.item.value + " aka " + ui.item.id );
					window.location.href = 'ledger.php?customer='+ui.item.id+'&name='+ui.item.label;
				}
	    	});
	  	});
	</script>
    
  </body>
</html>