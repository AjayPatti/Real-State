<?php
ob_start();
session_start();

if(!isset($_GET['project']) || !is_numeric($_GET['project']) || !($_GET['project'] > 0)){
	$_SESSION['error'] = 'Unauthorized Access!';
	header("Location:projects.php");
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");
if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_block_projects'))){ 
  header("location:/wcc_real_estate/index.php");
  exit();
 }
$project_id = $_GET['project'];
$project_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_projects where id = '".$project_id."' limit 0,1 "));

if(isset($_POST['addBlock'])){
	
	$name = filter_post($conn,$_POST['block_name']);
	$default_rate = filter_post($conn,$_POST['default_rate']);
	$default_area = filter_post($conn,$_POST['default_area']);
	$plc_charges = filter_post($conn,$_POST['plc_charges']);
	
	if($name == ''){
		$_SESSION['error'] = 'Block Name was wrong!';
	}else{
		
		$already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_blocks where name = '".$name."' and project_id = '".$project_id."' limit 0,1 "));
		if(isset($already_exits['id'])){
			$_SESSION['error'] = 'Block Name Already Exists in '.$project_details['name'].' Project!';
		}else{
			mysqli_query($conn,"insert into kc_blocks set name = '$name', project_id = '$project_id', status = '1', addedon ='".date('Y-m-d H:i:s')."', added_by = '".$_SESSION['login_id']."' ");
			$block_id = mysqli_insert_id($conn);
			if($block_id > 0){
				$_SESSION['success'] = 'Block Successfully Added!';
				header("Location:blocks.php?project=".$_GET['project']);
				exit();
			}else{
				$_SESSION['error'] = 'Block Name was wrong. Please Try Again!';
			}
		}
	}
						
}

if(isset($_POST['editBlock'])){
	
	$block_id = filter_post($conn,$_POST['block']);
	$name = filter_post($conn,$_POST['block_name_edit']);
	
	if(!($block_id > 0) || !is_numeric($block_id)){
		$_SESSION['error'] = 'Something was Really wrong!';
	}else if($name == ''){
		$_SESSION['error'] = 'Name was wrong!';
	}else{
		
		$already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_blocks where id != '$block_id' and name = '$name' limit 0,1 "));
		if(isset($already_exits['id'])){
			$_SESSION['error'] = 'Block Name Already Exists!';
		}else{
			mysqli_query($conn,"update kc_blocks set name = '$name' where id = '$block_id' ");
			$_SESSION['success'] = 'Block Successfully Updated!';
			header("Location:blocks.php?project=".$_GET['project']);
			exit();
		}
	}
						
}

if(isset($_GET['block']) && is_numeric($_GET['block'])){
	$block_id = $_GET['block'];
	$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select status from kc_blocks where id = '".$block_id."' limit 0,1 "));
	if(isset($block_details['status'])){
		$current_status = $block_details['status'];
		if($current_status == 1){
			$new_status = 0;
		}else{
			$new_status = 1;
		}
		mysqli_query($conn,"update kc_blocks set status = '$new_status' where id = '".$block_id."' limit 1 ");
		$_SESSION['success'] = 'Block Status Successfully Updated!';
		header("Location:blocks.php?project=".$_GET['project']);
		exit();
	}
	$_SESSION['error'] = 'Something Wrong!';
	header("Location:blocks.php");
	exit();
}

if(isset($_GET['block_app_visitus']) && is_numeric($_GET['block_app_visitus'])){
	$block_id = $_GET['block_app_visitus'];
	$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select app_visitus_status from kc_blocks where id = '".$block_id."' limit 0,1 "));
	if(isset($block_details['app_visitus_status'])){
		$current_status = $block_details['app_visitus_status'];
		if($current_status == 1){
			$new_status = 0;
		}else{
			$new_status = 1;
		}
		mysqli_query($conn,"update kc_blocks set app_visitus_status = '$new_status' where id = '".$block_id."' limit 1 ");
		$_SESSION['success'] = 'Block VisitUs Status Successfully Updated!';
		header("Location:blocks.php?project=".$_GET['project']);
		exit();
	}
	$_SESSION['error'] = 'Something Wrong!';
	header("Location:blocks.php");
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
	<link rel="icon" type="image/x-icon" href="/<?php echo $host_name; ?>img/logo.png">
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
            <li class="active"><a href="projects.php"><i class="fa fa-bank"></i> Projects</a></li>
            <li class="active">Blocks</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
			<div class="box">
                <div class="box-header">
					<?php 
					include("../includes/notification.php"); ?>
					<div class="col-sm-8">
						<h3 class="box-title">All Blocks of Project <strong class="text-warning"><?php echo $project_details['name']; ?></strong></h3>
					</div>
					<?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'add_block_projects')){ ?>
                    <div class="col-sm-4">
						<button class="btn btn-sm btn-success pull-right" data-toggle="modal" data-target="#addBlock">Add Block</button>
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
					  <th>Action</th>
					</tr>
					<?php
					$limit = 50;
					if(isset($_GET['page'])){
						$page = $_GET['page'];
					}else{
						$page = 1;
					}
					$total_records = mysqli_fetch_assoc(mysqli_query($conn,"select COUNT(*) as total from kc_blocks where project_id = '".$project_id."' "));
					$total_pages = ceil($total_records['total']/$limit);
					
					if($page == 1){
						$start = 0;
					}else{
						$start = ($page-1)*$limit;
					}
					
					$blocks = mysqli_query($conn,"select * from kc_blocks where project_id = '".$project_id."' limit $start,$limit ");
					if(mysqli_num_rows($blocks) > 0){
						$counter = 1;
						while($block = mysqli_fetch_assoc($blocks)){ ?>
							<tr>
								<td><?php echo $counter; ?></td>
								<td><?php echo $block['name']; ?></td>
								<td><?php echo date("d M Y h:i A",strtotime($block['addedon'])); ?></td>
								<td>
                                	<?php if($block['status'] == 1){ ?>
                                		<label class="label label-success">Active</label>
                                    <?php }else{ ?>
                                    	<label class="label label-danger">Inactive</label>
                                    <?php } ?>
                                </td>
								<td>
                                	<?php if($block['app_visitus_status'] == 1){ ?>
                                		<label class="label label-success">Active</label>
                                    <?php }else{ ?>
                                    	<label class="label label-danger">Inactive</label>
                                    <?php } ?>
                                </td>
                                <td>
                                	<?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'edit_block_projects')) {?>
                                		<button class="btn btn-xs btn-warning" type="button" data-toggle="tooltip" title="Edit Block" onclick = "editBlock(<?php echo $block['id']; ?>);"><i class="fa fa-pencil"></i></button>


                                	<?php } ?>
									
									<?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'block_status_projects')) {
										if($block['status']){
											$button_class = 'btn-success';
											$icon_class = 'fa-lock';
											$btn_title = "Make Inactive";
										}else{
											$button_class = 'btn-danger';
											$icon_class = 'fa-unlock';
											$btn_title = "Make Active";
										}

										if($block['app_visitus_status']){
											$visitus_button_class = 'btn-success';
											$visitus_icon_class = 'fa-lock';
											$visitus_btn_title = "Make App VisitUs Inactive";
										}else{
											$visitus_button_class = 'btn-danger';
											$visitus_icon_class = 'fa-unlock';
											$visitus_btn_title = "Make App VisitUs Active";
										}
										?>
                                    
                                    	<a href="blocks.php?project=<?php echo $_GET['project']; ?>&block=<?php echo $block['id']; ?>" data-toggle="tooltip" title="<?php echo $btn_title; ?>">
											<button class="btn btn-xs <?php echo $button_class; ?>" type="button"><i class="fa <?php echo $icon_class; ?>"></i></button>
										</a>

										<a href="blocks.php?project=<?php echo $_GET['project']; ?>&block_app_visitus=<?php echo $block['id']; ?>" data-toggle="tooltip" title="<?php echo $visitus_btn_title; ?>">
											<button class="btn btn-xs <?php echo $visitus_button_class; ?>" type="button"><i class="fa <?php echo $visitus_icon_class; ?>"></i></button>
										</a>
									<?php } ?>
									

									<?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_plot_number_projects')) {?>
										<a href="block_numbers.php?block=<?php echo $block['id']; ?>" data-toggle="tooltip" title="Manage Plot Numbers">
											<button class="btn btn-xs btn-primary" type="button"><i class="fa fa-building-o"></i></button>
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
								 <li <?php if((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)){ ?>class="active"<?php } ?>><a href="blocks.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
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
	
	<div class="modal" id="addBlock">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" name="add_block_frm" id="add_block_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add Block</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-header with-border">
						<div class="col-md-12">
							<h3 class="box-title">Add Block Panel</h3>
						</div>
					</div><!-- /.box-header -->
					<!-- form start -->
					<div class="box-body">
						
						<div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">Block Name</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="block_name" name="block_name" maxlength="255" required>
						  </div>
						</div>
                        
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="addBlock">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
    
     <div class="modal" id="editBlockModal">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="blocks.php?project=<?php echo $_GET['project']; ?>" name="edit_frm" id="edit_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Edit Block</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-body" id="edit-block-container">
					
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="editBlock">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
    
	<?php require('../includes/common-js.php'); ?>
	
    <script type="text/javascript">
		function editBlock(block){
			$.ajax({
				url: '../dynamic/editBlock.php',
				type:'post',
				data:{block:block},
				success: function(resp){
					$("#edit-block-container").html(resp);
					$("#editBlockModal").modal('show');
				}
			});
		}
	</script>
    
  </body>
</html>

