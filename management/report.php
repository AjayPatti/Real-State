<?php
	ob_start();
	session_start();

	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/checkAuth.php");
	if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_reports'))){ 
	 	header("location:/wcc_real_estate/index.php");
	 	exit();
 	}
	$url = 'report.php?search=Search';

	$limit = 100;
	if(isset($_GET['page'])){
		$page = $_GET['page'];
	}else{
		$page = 1;
	}
	// print_r($page);die;
	$search = false;
	$search_project_url_string = $search_associate_url_string = '';
	$query = "select cb.id as customer_block_id, cb.customer_id, cb.block_id, cb.block_number_id, cb.registry, cb.registry_date,cb.addedon, cb.registry_by, cb.associate,b.project_id, b.name as block_name, bn.block_number as block_number_name, bn.area, p.name as project_name, c.name_title as customer_name_title, c.name as customer_name, c.mobile as customer_mobile, c.address as customer_address,a.code as associate_code, a.name as associate_name from kc_customer_blocks cb LEFT JOIN kc_blocks b ON cb.block_id = b.id LEFT JOIN kc_block_numbers bn ON cb.block_number_id = bn.id LEFT JOIN kc_projects p ON b.project_id = p.id LEFT JOIN kc_customers c ON cb.customer_id = c.id LEFT JOIN kc_associates a ON cb.associate = a.id where cb.status = '1' ";
	if(isset($_GET['search_project']) || isset($_GET['search_block']) || isset($_GET['search_block_no']) || (isset($_GET['search_block_no']) && $_GET['search_block_no']>0) || (isset($_GET['search_associate']) && (int) $_GET['search_associate'] > 0)){ 
		//echo "<pre>"; print_r($_GET); die;
		if(isset($_GET['search_block']) && $_GET['search_block']!=''){
			$query .= " and cb.block_id = '".$_GET['search_block']."'";
			$url .= '&search_block='.$_GET['search_block'];
		}

		if(isset($_GET['search_block_no']) && $_GET['search_block_no']!=''){
			$query .= " and cb.block_number_id = '".$_GET['search_block_no']."'";
			$url .= '&search_block_no='.$_GET['search_block_no'];
		}

		if(isset($_GET['search_project']) && is_array($_GET['search_project']) && sizeof($_GET['search_project'])>0){
			$query .= " and cb.block_id IN (select id from kc_blocks where status = '1' and project_id IN ('".implode("','",$_GET['search_project'])."') )";
			foreach($_GET['search_project'] as $project_id){
				$url .= '&search_project[]='.$project_id;
				$search_project_url_string .= '&search_project[]='.$project_id;
			}
		}
		//echo $query;die();
		if(isset($_GET['datesearch'])&& $_GET['datesearch']!=''){
			$ddatesearch = explode('-',$_GET['datesearch']);
			
				$startdate = date('Y-m-d 00:00:01',strtotime($ddatesearch[0]));
				$enddate = date('Y-m-d 23:59:59',strtotime($ddatesearch[1]));
			$query .= "and cb.addedon between '$startdate' and '$enddate' ";
			
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
		}
		//echo $query; die;
		$total_records = mysqli_num_rows(mysqli_query($conn,$query));
		$total_pages = ceil($total_records/$limit);
		
		if($page == 1){
			$start = 0;
		}else{
			$start = ($page-1)*$limit;
		}

		//$query .= " order by registry_date desc limit $start,$limit";
		$query .= " order by b.name, cast(bn.block_number as unsigned) limit $start,$limit";
		//echo $query; die;
		$customers = mysqli_query($conn,$query);
		$search = true;
	}else{
		$url = 'report.php?search=Search';
		$query = "select cb.id as customer_block_id, cb.customer_id, cb.block_id, cb.block_number_id, cb.registry, cb.registry_date,cb.addedon, cb.registry_by, cb.associate,b.project_id, b.name as block_name, bn.block_number as block_number_name, bn.area, p.name as project_name, c.name_title as customer_name_title, c.name as customer_name, c.mobile as customer_mobile, c.address as customer_address,a.code as associate_code, a.name as associate_name from kc_customer_blocks cb LEFT JOIN kc_blocks b ON cb.block_id = b.id LEFT JOIN kc_block_numbers bn ON cb.block_number_id = bn.id LEFT JOIN kc_projects p ON b.project_id = p.id LEFT JOIN kc_customers c ON cb.customer_id = c.id LEFT JOIN kc_associates a ON cb.associate = a.id where cb.status = '1' ";
		$total_records = mysqli_num_rows(mysqli_query($conn,$query));
		$total_pages = ceil($total_records/$limit);
		if($page == 1){
			$start = 0;
		}else{
			$start = ($page-1)*$limit;
		}
		$query .= " order by b.name, cast(bn.block_number as unsigned) limit $start,$limit";
		//echo $query;die;
		$customers = mysqli_query($conn,$query);
		$search = true;
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
    
    <!-- jQuery UI -->
    <link href="/<?php echo $host_name; ?>/css/jquery-ui.css" rel="stylesheet" type="text/css" />
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
		.select_2_box .select2-container{
			max-height: 78px;
    		overflow-y: scroll;
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
            <li class="active">Report</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
			<div class="box">
                <div class="box-header">
					<?php 
					include("../includes/notification.php"); ?>
					<div class="col-sm-10">
						<h3 class="box-title">All Report</h3>
					</div>
                	<?php if($search && mysqli_num_rows($customers) > 0){ ?>
                    <div class="col-sm-1">
							<a href="report_excel_export.php?<?php echo $search_project_url_string; ?>&search_block=<?php echo isset($_GET['search_block'])?$_GET['search_block']:''; ?>&search_block_no=<?php echo isset($_GET['search_block_no'])?$_GET['search_block_no']:''; ?>&search_associate=<?php echo $search_associate_url_string; ?>&<?php if(isset($_GET['datesearch'])  && $_GET['datesearch']!='' ) echo 'datesearch='.$_GET['datesearch'];?>&search=Search" class="btn btn-sm btn-success pull-right"><i class="fa fa-file-excel-o"></i> Excel Export</a>
					</div>
					<div class="col-sm-1">
							<a class="btn btn-sm btn-danger" href="javascript:void(0);" onclick="printDiv('printContent')" data-toggle="tooltip" title="Print"><i class="fa fa-print">&nbsp;</i>PDF Export</a>
					</div>
					<?php } ?>
					<hr />
					<form action="" name="search_frm" id="search_frm" method="get" class="">
					<div class="row">
						<div class="form-group col-md-3 select_2_box">
							<label for="search_project" class="col-md-12 text-left">Project</label>
						  	<select class="form-control select2-w100" id="search_project" name="search_project[]" multiple onChange="search_getBlocks(this.value);">
	                        	<option value="" disabled>Select Project</option>
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
							<!-- <input type="text" class="form-control associate-autocomplete" data-for-id="search_associate" placeholder="Name or Code or Mobile"> -->
							<!-- <input type="hidden" name="search_associate" id="search_associate"> -->
							<?php /*
							<select class="form-control" id="search_associate" name="search_associate">
						        <option value="">Select Associate</option>
						        <?php
								$associates = mysqli_query($conn,"select * from kc_associates where status = '1' order by name ");
								while($associate = mysqli_fetch_assoc($associates)){ ?>
						        	<option value="<?php echo $associate['id']; ?>"><?php echo $associate['name']; ?></option>
						        <?php } ?>
						    </select>*/ ?>
						<div class="form-group col-md-2 text-center">
							<label for="search_associate" class="col-md-12 text-left">Date Search </label>
							<div><input type="text" class="form-control" placeholder="Date" id="datepick" name="datesearch" readonly=""></div>
                    	</div>
						<div class="col-md-1">
							<input type="submit" name="search" value="Search" class="btn btn-sm btn-primary" style="margin-top: 25px;">
						</div>
					</div>
					</form>
				</div><!-- /.box-header -->
				
                <div class="box-body no-padding" id="printContent">
				 <div class="table-responsive">
					 <table class="table table-striped table-hover table-bordered">
	                    <tr>
							<th>Sr.</th>
							<th>Project Details</th>
							<!-- <th>Block</th>
							<th>Plot No.</th> -->
							<th>Customer Details</th>
							<!-- <th>Customer Mobile</th>
							<th>Customer Address</th> -->
							<th>Date of Booking</th>
							<th>Registry</th>
							<th>Registry By</th>
							<th>Associate</th>
							<th>Rate</th>
							<th>Area</th>
							<th>Amount</th>
							<th>Received Amount</th>
							<th>Pending Amount</th>
							<th>Last Paid Amount</th>
							<th>Last Paid Date</th>
						</tr>
						<?php
							
							if($search && mysqli_num_rows($customers) > 0){
								$counter = $start+1;
								$total_debited_amt = $total_credited_amt = $total_pending_amt = 0;
								while($customer = mysqli_fetch_assoc($customers)){
									// echo "<pre>"; print_r($customer); die;
									
									$total_debited_amt += $total_debited = totalDebited($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']);
									$total_credited_amt += $total_credited = totalCredited($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']);
									
									$total_pending_amt += $pending_amount = ($total_credited - $total_debited); ?>
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
										<td><?php echo date("d-m-Y",strtotime(dateOfBooking($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']))); ?></td>
										<td>
											<?php if($customer['registry'] == "yes"){
												echo date("d-m-Y",strtotime($customer['registry_date']));
											}else{
												echo "-";
											} ?>
										</td>
										<td>
											<?php if($customer['registry'] == "yes"){
												echo $customer['registry_by'];
											}else{
												echo "-";
											} ?>
										</td>
										<td><?php echo ($customer['associate'] > 0)?$customer['associate_name'].'('.$customer['associate_code'].')':''; ?></td>
										<td>
											<span class="text-warning">
												<?php //echo ($customer['rate_per_sqft'] > 0)?$customer['rate_per_sqft']:''; ?>

												<?php echo number_format(ratePerSq($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']),2); ?> ₹
											</span>
										</td>
										<td><?php echo $customer['area']; ?> Sq. Ft.</td>
										<td>
											<span class="text-primary"><?php echo number_format($total_credited,2); ?> ₹</span>
										</td>
										<td>
											<span class="text-success"><?php echo number_format($total_debited,2); ?> ₹</span>
										</td>
										<td>
											<span class="text-danger"><?php echo number_format($pending_amount,2); ?> ₹</span>
										</td>
										<?php $last_payment_detail =  getLastPayment($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']); ?>

										<td><?php if(!empty($last_payment_detail['amount'])){ echo $last_payment_detail['amount']; }else { echo "00.00"; }  ?> </td>

										<td><?php if(!empty($last_payment_detail['paid_date'])){ echo date('j-m-Y',strtotime($last_payment_detail['paid_date'])); } ?></td>
									</tr>
									<?php	
									$counter++;
								} ?>
								<tr>
									<td colspan="9" align="right">Total</td>
									<td>
										<span class="text-primary"><?php echo number_format($total_credited_amt,2); ?> ₹</span>
									</td>
									<td>
										<span class="text-success"><?php echo number_format($total_debited_amt,2); ?> ₹</span>
									</td>
									<td>
										<span class="text-danger"><?php echo number_format($total_pending_amt,2); ?> ₹</span>
									</td>
								</tr>
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

    <?php require('../includes/common-js.php'); ?>
	
    <script type="text/javascript">
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


		$("#search_project").on('change', function(){
			var search_project_val = [];
			var search_project_text = [];
			var search_project_val =  $(this).val();
			var search_project_text =  $(this).text();

			console.log(typeof(search_project_val));
			console.log('sdhksdfjksdfhjksdfjksfhjk');
			console.log(typeof(search_project_text));
		});

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