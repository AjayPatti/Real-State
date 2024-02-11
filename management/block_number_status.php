<?php
	ob_start();
	session_start();
	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/checkAuth.php");

	if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_unreserved_plot_number')))
	{ 
 		header("location:/wcc_real_estate/index.php");
 		exit();
 	}
	$url = 'block_number_status.php?search=Search';
	$limit = 100;
	if(isset($_GET['page']))
	{
		$page = $_GET['page'];
	}
	else
	{
		$page = 1;
	}
	$search = false;
	$query = "select kc_projects.id, kc_projects.name as project_name, kc_blocks.id, kc_blocks.name as block_name, kc_block_numbers.id, kc_block_numbers.block_number, kc_block_numbers.area, kc_block_numbers.road, kc_block_numbers.face, kc_block_numbers.addedon from kc_projects LEFT JOIN (kc_blocks LEFT JOIN kc_block_numbers ON kc_blocks.id = kc_block_numbers.block_id) ON kc_projects.id = kc_blocks.project_id where kc_block_numbers.status = '1' ";

	if(isset($_GET['search_project']) || isset($_GET['search_block']) || isset($_GET['search_block_no']) || (isset($_GET['search_block_no']) && $_GET['search_block_no']>0))
	{ 
		if(isset($_GET['search_block']) && $_GET['search_block']!='')
		{
			$query .= " and kc_block_numbers.block_id = '".$_GET['search_block']."'";
			$url .= '&search_block='.$_GET['search_block'];
		}

		if(isset($_GET['search_block_no']) && $_GET['search_block_no']!='')
		{
			$query .= " and kc_block_numbers.id = '".$_GET['search_block_no']."'";
			$url .= '&search_block_no='.$_GET['search_block_no'];
		}

		if(isset($_GET['search_project']) && $_GET['search_project']>0)
		{
			$query .= " and kc_block_numbers.block_id IN (select id from kc_blocks where status = '1' and project_id = '".$_GET['search_project']."' )";
			$url .= '&search_project='.$_GET['search_project'];
		}
		$total_records = mysqli_num_rows(mysqli_query($conn,$query));
		$total_pages = ceil($total_records/$limit);
		
		if($page == 1){
			$start = 0;
		}else{
			$start = ($page-1)*$limit;
		}

		//$query .= " order by registry_date desc limit $start,$limit";
		// $query .= "limit $start,$limit";
		//echo $query; die;
		$customers = mysqli_query($conn,$query);
		$search = true;
	}else{
		$total_records = mysqli_num_rows(mysqli_query($conn,$query));
		$total_pages = ceil($total_records/$limit);
		
		if($page == 1){
			$start = 0;
		}else{
			$start = ($page-1)*$limit;
		}

		$customers = mysqli_query($conn,$query);
		$search = true;
	}
?>
<!DOCTYPE html>
<html>
  <head>
  	<style type="text/css">
  		.form-inline .form-control
		{
		    width: 100% !important;
		}
  	</style>
    <meta charset="UTF-8">
    <title>WCC | Plot Number Status <?php echo date('d-m-Y h:i A'); ?></title>
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
        <?php  require('../includes/left_sidebar.php'); ?>
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
            <li class="active">Unreserved Plot Numbers</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
			<div class="box">
                <div class="box-header">
					<?php 
					include("../includes/notification.php"); ?>
					<div class="row">
						<div class="col-sm-8">
							<h3 class="box-title">Unreserved Plot Numbers Report</h3>
						</div>
					</div><hr />
					<div class="col-sm-12">
                    	<div class="row" style="float:right;">
                    		<div class="col-md-3" >
	                    		<?php 
	                    		if(isset($_GET['search_block']) && (int) $_GET['search_block'] > 0)
								{
									$block_id = (int) $_GET['search_block'];
									$search = true;
								}else{
									$block_id = 1000;
								}
	                    		$query = mysqli_fetch_array(mysqli_query($conn,"SELECT * from kc_block_numbers where status = '1' and block_id = '$block_id' and id NOT IN (select block_number_id from kc_customer_blocks where status = '1')"));

	                    		if($search && mysqli_num_rows($customers) > 0 && $query != '')
								{ ?>
								<a href="excel_report_unreserveredPlot.php?search_project=<?php echo isset($_GET['search_project'])?$_GET['search_project']:''; ?>&search_block=<?php echo isset($_GET['search_block'])?$_GET['search_block']:''; ?>&search_block_no=<?php echo isset($_GET['search_block_no'])?$_GET['search_block_no']:''; ?>&search=Search" class="btn btn-sm btn-success pull-right"><i class="fa fa-file-excel-o"></i> Excel Export</a>
								<?php } ?>
	                    	</div>
	                    	<div class="col-md-3">
	                    		<?php $query = mysqli_fetch_array(mysqli_query($conn,"SELECT * from kc_block_numbers where status = '1' and block_id = '$block_id' and id NOT IN (select block_number_id from kc_customer_blocks where status = '1')"));

	                    		if($search && mysqli_num_rows($customers) > 0 && $query != ''){ ?> 
	                    			<a class="btn btn-sm btn-danger" href="javascript:void(0);" onclick="printDiv('printContent')" data-toggle="tooltip" title="Print"><i class="fa fa-print">&nbsp;</i>PDF Export</a>
	                    		<?php } ?>
	                    	</div>
                    	</div>
					</div>
					
					<div class="row">
						<form enctype="multipart/form-data" action="block_number_status.php" name="search_frm" id="search_frm" method="get" class="form-inline">
							<div class="form-group col-md-3">
								<label for="search_project" class="text-left">Project</label>
								<select class="form-control" id="search_project" name="search_project" onChange="search_getBlocks(this.value);">
									<option value="">Select Project</option>
									<?php
										$projects = mysqli_query($conn,"select * from kc_projects where status = '1' ");
										while($project = mysqli_fetch_assoc($projects))
										{ 
											?>
			                            	<option value="<?php echo $project['id']; ?>"><?php echo $project['name']; ?></option>
			                            	<?php 
										} 
									?>
								</select>
							</div>
							<div class="form-group col-md-3">
								<label for="search_block" class="text-left">Block</label>
								<select class="form-control" id="search_block" name="search_block" onChange="search_getBlockNumbers(this.value);" >
									<option value="">Select Block</option>
								</select>
							</div>
							<!-- <div class="form-group col-md-3 text-center">
								<label for="search_block_no" class="text-left">Plot Number</label>
								<select class="form-control" id="search_block_no" name="search_block_no" >
									<option value="">Select Plot Number</option>
								</select>
							</div> -->
							<div class="form-group col-md-1">
								<input type="submit" name="search" value="Search" class="btn btn-sm btn-primary" style="margin-top: 25px;">	
							</div>
						</form>
					</div>
					<br><!-- /.box-header -->
                <div class="box-body no-padding" id="printContent">
				<?php
				$search = false;
				$query = "select * from kc_block_numbers where status = '1'  ";

				if(isset($_GET['search_block']) && (int) $_GET['search_block'] > 0)
				{
					$block_id = (int) $_GET['search_block'];
					
					$search = true;
				}
				
				else
				{
					$block_id = 1000;
				}
				$query .= " and block_id = '".$block_id."'";
				if($search)
				{ ?>
					<h3 style="text-align: center;">&nbsp;All Plot Numbers of Block <?php //echo $block_name ?></h3>
				<?php 
				} ?>

				<table class="table table-striped table-hover table-bordered">
                    <tr>
						<th>Sl No.</th>
						<th>Project</th>
						<th>Block</th>
						<th>Plot Number</th>
						<th>PLC</th>
						<th>Area</th>
						<th>Road</th>
						<th>Face</th>
						<th>Added Date</th>
						<th>Status</th>
					</tr>
					<?php
					
					$limit = 100;
					if(isset($_GET['page']))
					{
						$page = $_GET['page'];
					}
					else
					{
						$page = 1;
					}
				
					$total_records = mysqli_num_rows(mysqli_query($conn,$query));
					$total_pages = ceil($total_records/$limit);
					
					if($page == 1){
						$start = 0;
					}else{
						$start = ($page-1)*$limit;
					}
					$query .= "limit $start,$limit";
					// echo $query; die();
					$block_numbers = mysqli_query($conn,$query);
					// print_r($query);die;
					if(mysqli_num_rows($block_numbers) > 0 && $search)
					{
						$counter = $start+1;
						$kc_blocks ='';
						while($block_number = mysqli_fetch_assoc($block_numbers))
						{ 
							// print_r($block_number['block_number']);
							if($block_id !=''){
								$kc_blocks = mysqli_fetch_assoc(mysqli_query($conn,"select id,project_id,name from kc_blocks where id = '".$block_id."'"));
							}else{
								$kc_blocks = mysqli_fetch_assoc(mysqli_query($conn,"select id,project_id,name from kc_blocks where id ='".$block_number['block_id']."'"));
							}
							
							$projects = mysqli_fetch_assoc(mysqli_query($conn,"select id,name from kc_projects where id = '".$kc_blocks['project_id']."'"));

							$kc_customer_blocks = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_customer_blocks where block_number_id  = '".$block_number['block_number']."'"));
							// echo "<pre>";
							// print_r($kc_customer_blocks);
							?>
							
							<tr>
								<td><?php echo $counter; ?></td>
								<td><?php echo $projects['name']; ?></td>
								<td><?php echo $kc_blocks['name']; ?></td>
								<td><?php echo $block_number['block_number']; ?></td>
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
									<?php if(!empty($kc_customer_blocks['block_number_id'])){?>
										<td><a href="javascript:void(0)" data-toggle="tooltip" title="View Customer's Information" onclick = "viewInformation(<?php echo $kc_customer_blocks['customer_id']; ?>);"><label class="label label-danger">Reserved</label>
									</td>
								<?php }else{?>
								<td><label class="label label-success">Unreserved</label></td>
								<?php }?>
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
								 <li <?php if((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)){ ?>class="active"<?php } ?>><a href="block_number_status.php?search_block=<?php echo $_GET['search_block']; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
								<?php
							}
						?>
						
					  </ul>
					</div>
				<?php } ?>
				
              </div><!-- /.box -->
        </section> 
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
			
		});
		
		function iCheckClicked(elem){
			 var for_attr = $(elem).attr('for');
		}
		
		function search_getBlocks(project){
			$("#search_block_no").val('');
			$.ajax({
				url: '../dynamic/getBlocks.php',
				type:'post',
				data:{project:project},
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
			url: '../dynamic/viewCustomer.php',
			type:'post',
			data:{customer:customer},
			success: function(resp){
				$("#view-information-container").html(resp);
				$("#viewInformation").modal('show');
			}
		});
	}
	</script>
    
  </body>
</html>

