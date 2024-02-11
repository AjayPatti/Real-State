<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");
if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_projects'))){ 
  header("location:/wcc_real_estate/index.php");
  exit();
 }
if(isset($_POST['addProject'])){
	$name = filter_post($conn,$_POST['project_name']);
	$send_reminder = filter_post($conn,$_POST['send_reminder']);
	if($name == ''){
		$_SESSION['error'] = 'Project Name was wrong!';
	}else{
		if(isset($send_reminder) && $send_reminder == 'on'){
			$reminder = 1;
		}else{
			$reminder = 0;
		}
		$already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_projects where name = '".$name."' limit 0,1 "));
		if(isset($already_exits['id'])){
			$_SESSION['error'] = 'Project Name Already Exists!';
		}else{

			mysqli_query($conn,"insert into kc_projects set name = '$name', is_reminder = '$reminder', status = '1', addedon =NOW(), added_by = '".$_SESSION['login_id']."' ");
			$block_id = mysqli_insert_id($conn);
			if($block_id > 0){
				header("Location:projects.php");
				$_SESSION['success'] = 'Project Successfully Added!';
				exit();
			}else{
				$_SESSION['error'] = 'Project Name was wrong. Please Try Again!';
			}
		}
	}
						
}

if(isset($_POST['editProject'])){
	// echo "<pre>"; print_r($_POST); die;
	$project_id = filter_post($conn,$_POST['project']);
	$name = filter_post($conn,$_POST['project_name_edit']);
	$send_reminder = filter_post($conn,$_POST['send_reminder']);
	if(!($project_id > 0) || !is_numeric($project_id)){
		$_SESSION['error'] = 'Something was Really wrong!';
	}else if($name == ''){
		$_SESSION['error'] = 'Name was wrong!';
	}else{
		if(isset($send_reminder) && $send_reminder == 'on'){
			$reminder = 1;
		}else{
			$reminder = 0;
		}
		$already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_projects where id != '$project_id' and name = '$name' limit 0,1 "));
		if(isset($already_exits['id'])){
			$_SESSION['error'] = 'Project Name Already Exists!';
		}else{
			mysqli_query($conn,"update kc_projects set name = '$name', is_reminder = '$reminder' where id = '$project_id' ");
			$_SESSION['success'] = 'Project Name Successfully Updated!';
			header("Location:projects.php");
			exit();
		}
	}
						
}
// echo "<pre>"; print_r($_GET); die;
if(isset($_GET['project']) && is_numeric($_GET['project'])){
	$project_id = $_GET['project'];
	// echo "<pre>";print_r($project_id);die;
	$project_details = mysqli_fetch_assoc(mysqli_query($conn,"select status from kc_projects where id = '".$project_id."' limit 0,1 "));
	// echo "<pre>"; print_r($project_details); die;
	if(isset($project_details['status'])){
		$current_status = $project_details['status'];
		if($current_status == 1){
			$new_status = 0;
		}else{
			$new_status = 1;
		}
		mysqli_query($conn,"update kc_projects set status = '$new_status' where id = '".$project_id."' limit 1 ");
		$_SESSION['success'] = 'Project Status Successfully Updated!';
		header("Location:projects.php");
		exit();
	}
	$_SESSION['error'] = 'Something Wrong!';
	header("Location:projects.php");
	exit();
}

if(isset($_GET['project_app_visitus']) && is_numeric($_GET['project_app_visitus'])){
	$project_id = $_GET['project_app_visitus'];
	$project_details = mysqli_fetch_assoc(mysqli_query($conn,"select app_visitus_status from kc_projects where id = '".$project_id."' limit 0,1 "));
	if(isset($project_details['app_visitus_status'])){
		$current_status = $project_details['app_visitus_status'];
		if($current_status == 1){
			$new_status = 0;
		}else{
			$new_status = 1;
		}
		mysqli_query($conn,"update kc_projects set app_visitus_status = '$new_status' where id = '".$project_id."' limit 1 ");
		$_SESSION['success'] = 'Project Status Successfully Updated!';
		header("Location:projects.php");
		exit();
	}
	$_SESSION['error'] = 'Something Wrong!';
	header("Location:projects.php");
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
    <!-- jQuery UI 1.11.4 -->
    <link href="/<?php echo $host_name; ?>/plugins/jQueryUI/jquery-ui.css" rel="stylesheet" type="text/css" />
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
    <style type="text/css">
    	.iradio_square-blue.has-error > .form-error{
    		margin-top: 25px;
    	}
    </style>
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
            <li class="active">Projects</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
			<div class="box">
                <div class="box-header">
					<?php 
					include("../includes/notification.php"); ?>
					<div class="col-sm-8">
						<h3 class="box-title">All Projects</h3>
					</div>
					<?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'add_projects')) {?>
                    <div class="col-sm-4">
						<button class="btn btn-sm btn-success pull-right" data-toggle="modal" data-target="#addProject">Add Project</button>
					</div>
				<?php } ?>
				</div><!-- /.box-header -->
                <div class="box-body no-padding">
				
				 <table class="table table-striped table-hover table-bordered">
                    <tr>
                      <th>Sl No.</th>
					  <th>Name</th>
					  <th>Added Date</th>
                      <th>Status</th>
                      <th>App VisitUs Status</th>
                      <?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'edit_projects')) {?>
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
					$total_records = mysqli_fetch_assoc(mysqli_query($conn,"select COUNT(*) as total from kc_projects "));
					$total_pages = ceil($total_records['total']/$limit);
					
					if($page == 1){
						$start = 0;
					}else{
						$start = ($page-1)*$limit;
					}
					
					$projects = mysqli_query($conn,"select * from kc_projects limit $start,$limit ");
					if(mysqli_num_rows($projects) > 0){
						$counter = 1;
						while($project = mysqli_fetch_assoc($projects)){ ?>
							<tr>
								<td><?php echo $counter; ?></td>
								<td><?php echo $project['name']; ?></td>
                                <td><?php echo date("d M Y h:i A",strtotime($project['addedon'])); ?></td>
								<td>
                                	<?php if($project['status'] == 1){ ?>
                                		<label class="label label-success">Active</label>
                                    <?php }else{ ?>
                                    	<label class="label label-danger">Inactive</label>
                                    <?php } ?>
                                </td>
								<td>
                                	<?php if($project['app_visitus_status'] == 1){ ?>
                                		<label class="label label-success">Active</label>
                                    <?php }else{ ?>
                                    	<label class="label label-danger">Inactive</label>
                                    <?php } ?>
                                </td>
                                <td>
                                	<?php
                                	if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'p_status_projects')) {
										if($project['status']){
											$button_class = 'btn-success';
											$icon_class = 'fa-lock';
											$btn_title = "Make Inactive";
										}else{
											$button_class = 'btn-danger';
											$icon_class = 'fa-unlock';
											$btn_title = "Make Active";
										}

										if($project['app_visitus_status']){
											$visitus_button_class = 'btn-success';
											$visitus_icon_class = 'fa-lock';
											$visitus_btn_title = "Make App VisitUs Inactive";
										}else{
											$visitus_button_class = 'btn-danger';
											$visitus_icon_class = 'fa-unlock';
											$visitus_btn_title = "Make App VisitUs Active";
										}
										?>
                                    
										<a href="projects.php?project=<?php echo $project['id']; ?>" data-toggle="tooltip" title="<?php echo $btn_title; ?>">
										<button class="btn btn-xs <?php echo $button_class; ?>" type="button"><i class="fa <?php echo $icon_class; ?>"></i></button>
										</a>

										<a href="projects.php?project_app_visitus=<?php echo $project['id']; ?>" data-toggle="tooltip" title="<?php echo $visitus_btn_title; ?>">
											<button class="btn btn-xs <?php echo $visitus_button_class; ?>" type="button"><i class="fa <?php echo $visitus_icon_class; ?>"></i></button>
										</a>
                                    <?php } ?>

									<?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'edit_projects')) { ?>
                                    	<button class="btn btn-xs btn-warning" type="button" data-toggle="tooltip" title="Edit Project" onclick = "editProject(<?php echo $project['id']; ?>);"><i class="fa fa-pencil"></i></button>
                                	<?php } ?>

									<?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_block_projects')) { ?>
										<a href="blocks.php?project=<?php echo $project['id']; ?>" data-toggle="tooltip" title="Manage Blocks">
											<button class="btn btn-xs btn-primary" type="button"><i class="fa fa-cubes"></i></button>
										</a>
									<?php } ?>
                                </td>
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
								 <li <?php if((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)){ ?>class="active"<?php } ?>><a href="projects.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
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
    </div><!-- ./wrapper -->
	
	<div class="modal" id="addProject">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" name="add_project_frm" id="add_project_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add Project</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-header with-border">
						<div class="col-md-12">
							<h3 class="box-title">Add Project Panel</h3>
						</div>
					</div><!-- /.box-header -->
					<!-- form start --> 
					<div class="box-body">
						
						<div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">Project Name</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="project_name" name="project_name" maxlength="255" required>
						  </div>
						</div>

						<div class="form-group">
						  <label for="send_message" class="col-sm-3 control-label">Send Reminder</label>
						  <div class="col-sm-8">
						  	<input type="checkbox" name="send_reminder" id="send_reminder" value="1" class="form-control" />
						  </div>
		 				</div>

                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="addProject">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
    
    
     <div class="modal" id="editProjectModal">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="projects.php" name="edit_frm" id="edit_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Edit Project</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-body" id="edit-project-container">
					
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="editProject">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
    
	<?php require('../includes/common-js.php'); ?>
	
    <script type="text/javascript">
    	$('input').iCheck({
			checkboxClass: 'icheckbox_square-blue',
			radioClass: 'iradio_square-blue',
			click: function(){
			}
		});

		function iCheckClicked(elem){
			var for_attr = $(elem).attr('for');
		}

		function editProject(project){
			$.ajax({
				url: '../dynamic/editProject.php',
				type:'post',
				data:{project:project},
				success: function(resp){
					$("#edit-project-container").html(resp);
					$("#editProjectModal").modal('show');
					$("[data-mask]").inputmask();
					$('input').iCheck({
						checkboxClass: 'icheckbox_square-blue',
						radioClass: 'iradio_square-blue',
						click: function(){
						}
					});
				}
			});
		}
	</script>
    
  </body>
</html>