<?php
	ob_start();
	session_start();

	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/checkAuth.php");
if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_registry'))){ 
 	header("location:/wcc_real_estate/index.php");
 	exit();
 }
	$limit = 100;
	if(isset($_GET['page'])){
		$page = $_GET['page'];
	}else{
		$page = 1;
	}

	$search = false;

	$query = "select * from kc_customer_blocks where status = '1' and registry = 'yes' ";
	if( (isset($_GET['from_date']) && isset($_GET['to_date'])) || (isset($_GET['search_project']) && $_GET['search_project']>0) ){ 
		//echo "<pre>"; print_r($_GET); die;
		if(isset($_GET['from_date']) && isset($_GET['to_date'])){
			$query .= " and registry_date BETWEEN '".date("Y-m-d",strtotime($_GET['from_date']))."' AND '".date("Y-m-d",strtotime($_GET['to_date']))."'";
		}

		if(isset($_GET['search_project']) && $_GET['search_project']>0){
			$query .= " and block_id IN (select id from kc_blocks where status = '1' and project_id = '".$_GET['search_project']."' )";
		}
		
		$total_records = mysqli_num_rows(mysqli_query($conn,$query));
		$total_pages = ceil($total_records/$limit);
		
		if($page == 1){
			$start = 0;
		}else{
			$start = ($page-1)*$limit;
		}

		$query .= " order by registry_date desc limit $start,$limit";
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
            <li class="active">Mutation </li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
			<div class="box">
                <div class="box-header">
					<?php 
					include("../includes/notification.php"); ?>
					<div class="col-sm-10">
						<h3 class="box-title">All Mutation</h3>
					</div>
                	<?php if($search && mysqli_num_rows($customers) > 0){ ?>
                    <div class="col-sm-1">
							<a href="registries_excel_export.php?from_date=<?php echo isset($_GET['from_date'])?$_GET['from_date']:''; ?>&to_date=<?php echo isset($_GET['to_date'])?$_GET['to_date']:''; ?>&search_project=<?php echo isset($_GET['search_project'])?$_GET['search_project']:''; ?>&search=Search" class="btn btn-sm btn-success pull-right"><i class="fa fa-file-excel-o"></i> Excel Export</a>
					</div>
					<div class="col-md-1">
							<a class="btn btn-sm btn-danger" href="javascript:void(0);" onclick="printDiv('printContent')" data-toggle="tooltip" title="Print"><i class="fa fa-print">&nbsp;</i>PDF Export</a>
					</div>
					<?php } ?>
					<hr />
					<form action="" name="search_frm" id="search_frm" method="get" class="">
						<div class="form-group col-sm-3">
							<label for="from_date">From</label>
						  	<input type="text" class="form-control" id="from_date" name="from_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask=""  data-validation="date" data-validation-format="dd-mm-yyyy" />
						</div>
						<div class="form-group col-sm-3">
							<label for="to_date">To</label>
							<input type="text" class="form-control" id="to_date" name="to_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask=""  data-validation="date" data-validation-format="dd-mm-yyyy" class="form-control" />
						</div>
						<div class="form-group col-md-3 text-center">
						  	<label for="search_project" class="col-md-12 text-left">Project <span class="text-danger">*</span></label>
						  	<select class="form-control" id="search_project" name="search_project">
	                        	<option value="">Select Project</option>
	                            <?php
								$projects = mysqli_query($conn,"select * from kc_projects where status = '1' ");
								while($project = mysqli_fetch_assoc($projects)){ ?>
	                            	<option value="<?php echo $project['id']; ?>"><?php echo $project['name']; ?></option>
	                            <?php } ?>
                        	</select>
						</div>
						<input type="submit" name="search" value="Search" class="btn btn-sm btn-primary" style="margin-top: 25px;">
					</form>
				</div><!-- /.box-header -->
				
                <div class="box-body no-padding table-responsive" id="printContent">
				
				 <table class="table table-striped table-hover table-bordered">
                    <tr>
						<th>Sr.</th>
						<th width="8%">Block</th>
						<th>Plot No.</th>
						<th>Area</th>
						<th>Customer</th>
						<th>Associate</th>
						<th>Total Amount</th>
						<th>Received Amount</th>
						<th>Pending Amount</th>
						<th>Registry Date</th>
						<th>Registry By</th>
						<th>Registry Details</th>
					</tr>
					<?php
						
						if($search && mysqli_num_rows($customers) > 0){
							$counter = 1;
							while($customer = mysqli_fetch_assoc($customers)){
							
								$total_debited = totalDebited($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']);
								$total_credited = totalCredited($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']);

								$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select area from kc_block_numbers where block_id = '".$customer['block_id']."' and id = '".$customer['block_number_id']."' and status = '1' "));
								
								$pending_amount = ($total_credited - $total_debited);
								?>
								<tr>
									<td><?php echo $counter; ?>.</td>
									<td><?php echo blockName($conn,$customer['block_id']); ?></td>
									<td><?php echo blockNumberName($conn,$customer['block_number_id']); ?></td>
									<td><?php echo $block_details['area']; ?> Sq. Ft.</td>
									<td><?php echo customerName($conn,$customer['customer_id']).' ('.customerID($customer['customer_id']).')'; ?></td>
									<td><?php echo ($customer['associate'] > 0)?associateName($conn,$customer['associate']):''; ?></td>
									<td>
										<span class="text-primary"><?php echo number_format($total_credited,2); ?> ₹</span>
									</td>
									<td>
										<span class="text-success"><?php echo number_format($total_debited,2); ?> ₹</span>
									</td>
									<td>
										<span class="text-danger"><?php echo number_format($pending_amount,2); ?> ₹</span>
									</td>
									<td><?php echo date("d M Y",strtotime($customer['registry_date'])); ?></td>
									<td><?php echo $customer['registry_by']; ?></td>
									<td>
										<strong>Khasra No : </strong><?php echo $customer['khasra_no']?$customer['khasra_no']:'N/A'; ?><br>
										<strong>Maliyat Value : </strong><?php echo $customer['maliyat_value']?$customer['maliyat_value']:'N/A'; ?><br>
										<strong>Sale Value : </strong><?php echo $customer['sale_value']?$customer['sale_value']:'N/A'; ?><br>
									</td>
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
								 <li <?php if((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)){ ?>class="active"<?php } ?>><a href="mutation.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
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
					window.location.href = 'mutation.php?customer='+ui.item.id+'&name='+ui.item.label;
				}
	    	});
	  	});
	</script>
    
  </body>
</html>