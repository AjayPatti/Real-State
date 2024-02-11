<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");
 if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_plc'))){ 
 	header("location:/wcc_real_estate/index.php");
 	exit();
 }
if(isset($_POST['addPLC'])){
	
	$project_id = filter_post($conn,$_POST['project']);
	$name = filter_post($conn,$_POST['plc_name']);
	$plc_percentage = filter_post($conn,$_POST['plc_percentage']);
	
	if(!is_numeric($project_id) && !($project_id > 0)){
		$_SESSION['error'] = 'Project was wrong!';
	}else if($name == ''){
		$_SESSION['error'] = 'PLC Name was wrong!';
	}else if(!is_numeric($plc_percentage) && !($plc_percentage > 0)){
		$_SESSION['error'] = 'PLC Percentage was wrong!';
	}else{
		
		$already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_plc where project_id = '".$project_id."' and name = '".$name."' limit 0,1 "));
		if(isset($already_exits['id'])){
			$project_details = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_projects where id = '".$project_id."' limit 0,1 "));
			$_SESSION['error'] = 'PLC Name Already Exists in project '.$project_details['name'];
		}else{
			mysqli_query($conn,"insert into kc_plc set project_id = '".$project_id."', name = '$name', plc_percentage = '$plc_percentage', status = '1', addedon =NOW() ");
			$relation_id = mysqli_insert_id($conn);
			if($relation_id > 0){
				$_SESSION['success'] = 'PLC Successfully Added!';
				header("Location:plc.php");
				exit();
			}else{
				$_SESSION['error'] = 'PLC Name was wrong. Please Try Again!';
			}
		}
	}
						
}

if(isset($_GET['plc']) && is_numeric($_GET['plc'])){
	$plc_id = $_GET['plc'];
	$plc_details = mysqli_fetch_assoc(mysqli_query($conn,"select status from kc_plc where id = '".$plc_id."' limit 0,1 "));
	if(isset($plc_details['status'])){
		$current_status = $plc_details['status'];
		if($current_status == 1){
			$new_status = 0;
		}else{
			$new_status = 1;
		}
		mysqli_query($conn,"update kc_plc set status = '$new_status' where id = '".$plc_id."' limit 1 ");
		$_SESSION['success'] = 'PLC Status Successfully Updated!';
		header("Location:plc.php");
		exit();
	}
	$_SESSION['error'] = 'Something Wrong!';
	header("Location:plc.php");
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
            <li class="active">PLC</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
			<div class="box">
                <div class="box-header">
					<?php 
					include("../includes/notification.php"); ?>
					<div class="col-sm-8">
						<h3 class="box-title">All PLC</h3>
					</div>
					<?php  if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'add_plc')){ 
 							?>
                    <div class="col-sm-4">
						<button class="btn btn-sm btn-success pull-right" data-toggle="modal" data-target="#addPLC">Add PLC</button>
					</div>
				<?php } ?>
				</div><!-- /.box-header -->
                <div class="box-body no-padding">
				
				 <table class="table table-striped table-hover table-bordered">
                    <tr>
                      <th>Sl No.</th>
                      <th>Project Name</th>
					  <th>Name</th>
					  <th>PLC (%)</th>
					  <th>Added Date</th>
                      <th>Status</th>
                      <?php  if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'status_plc')){ 
 							?>
					  <th>Action</th>
					<?php } ?>
					</tr>
					<?php
					$limit = 50;
					if(isset($_GET['page'])){
						$page = $_GET['page'];
					}else{
						$page = 1;
					}
					$total_records = mysqli_fetch_assoc(mysqli_query($conn,"select COUNT(*) as total from kc_plc "));
					$total_pages = ceil($total_records['total']/$limit);
					
					if($page == 1){
						$start = 0;
					}else{
						$start = ($page-1)*$limit;
					}
					
					$plcs = mysqli_query($conn,"select * from kc_plc limit $start,$limit ");
					if(mysqli_num_rows($plcs) > 0){
						$counter = 1;
						while($plc = mysqli_fetch_assoc($plcs)){
							$project_details = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_projects where id = '".$plc['project_id']."' limit 0,1 "));
							?>
							<tr>
								<td><?php echo $counter; ?></td>
								<td><?php echo $project_details['name']; ?></td>
								<td><?php echo $plc['name']; ?></td>
								<td><?php echo $plc['plc_percentage']; ?> %</td>
                                <td><?php echo date("d M Y h:i A",strtotime($plc['addedon'])); ?></td>
								<td>
                                	<?php if($plc['status'] == 1){ ?>
                                		<label class="label label-success">Active</label>
                                    <?php }else{ ?>
                                    	<label class="label label-danger">Inactive</label>
                                    <?php } ?>
                                </td>
                                <?php
                                	if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'status_plc')){?>
                                <td>
                                	<?php
									if($plc['status']){
										$button_class = 'btn-success';
										$icon_class = 'fa-lock';
										$btn_title = "Make Inactive";
									}else{
										$button_class = 'btn-danger';
										$icon_class = 'fa-unlock';
										$btn_title = "Make Active";
									}
									?>
                                    
                                    <a href="plc.php?plc=<?php echo $plc['id']; ?>" data-toggle="tooltip" title="<?php echo $btn_title; ?>">
										<button class="btn btn-xs <?php echo $button_class; ?>" type="button"><i class="fa <?php echo $icon_class; ?>"></i></button>
									</a>
                                   
                                </td>
                            <?php } ?>
							</tr>
							<?php
							$counter++;
						}
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
								 <li <?php if((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)){ ?>class="active"<?php } ?>><a href="plc.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
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
	
	<div class="modal" id="addPLC">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" name="add_plc_frm" id="add_plc_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add PLC</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-header with-border">
						<div class="col-md-12">
							<h3 class="box-title">Add PLC Panel</h3>
						</div>
					</div><!-- /.box-header -->
					<!-- form start -->
					<div class="box-body">
						
                        <div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">Project</label>
						  <div class="col-sm-8">
							<select class="form-control" id="project" name="project" required>
                            	<option value="">Select Project</option>
                                <?php
								$projects = mysqli_query($conn,"select * from kc_projects where status = '1' ");
								while($project = mysqli_fetch_assoc($projects)){ ?>
                                	<option value="<?php echo $project['id']; ?>"><?php echo $project['name']; ?></option>
                               <?php } ?>
                            </select>
						  </div>
						</div>
						
                        <div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">PLC Name</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="plc_name" name="plc_name" maxlength="255" required>
						  </div>
						</div>
                        
                        <div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">PLC (%)</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="plc_percentage" name="plc_percentage" maxlength="5" required>
						  </div>
						</div>
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="addPLC">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
    
    
    <?php require('../includes/common-js.php'); ?>
	
    <script type="text/javascript">
	
	</script>
    
  </body>
</html>

