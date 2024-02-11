<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");
if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_cheque_prints'))){ 
 	header("location:/wcc_real_estate/index.php");
 	exit();
 }
if(isset($_POST['addCheque'])){
	$name = filter_post($conn,$_POST['name']);
	$amount = filter_post($conn,$_POST['amount']);
	$date = date('Y-m-d',strtotime($_POST['date']));
	
	if($name == ''){
		$_SESSION['error'] = 'Project Name was wrong!';
	}else{
		mysqli_query($conn,"insert into kc_cheques set name = '$name', amount = '$amount', date = '$date', status = '1', addedon =NOW(), added_by = '".$_SESSION['login_id']."' ");
		$insertedId = mysqli_insert_id($conn);
		if($insertedId > 0){
			$_SESSION['success'] = 'Cheque Successfully Added!';
			header("Location:cheque_print.php?cheque=".$insertedId);
			exit();
		}else{
			$_SESSION['error'] = 'Cheque Name was wrong. Please Try Again!';
		}
	}
						
}

if(isset($_POST['editCheque'])){
	
	$cheque_id = filter_post($conn,$_POST['cheque']);
	$name = filter_post($conn,$_POST['name']);
	$amount = filter_post($conn,$_POST['amount']);
	$date = date('Y-m-d',strtotime($_POST['date']));
	
	if(!($cheque_id > 0) || !is_numeric($cheque_id)){
		$_SESSION['error'] = 'Something was Really wrong!';
	}else if($name == ''){
		$_SESSION['error'] = 'Name was wrong!';
	}else{
		mysqli_query($conn,"update kc_cheques set name = '$name', amount = '$amount', date = '$date' where id = '$cheque_id' ");
		$_SESSION['success'] = 'Cheque Name Successfully Updated!';
		header("Location:cheque_print.php?cheque=".$cheque_id);
		exit();
	}
						
}

if(isset($_GET['cheque']) && is_numeric($_GET['cheque'])){
	$cheque_id = $_GET['cheque'];
	$cheque_details = mysqli_fetch_assoc(mysqli_query($conn,"select status from kc_cheques where id = '".$cheque_id."' limit 0,1 "));
	if(isset($cheque_details['status'])){
		$current_status = $cheque_details['status'];
		if($current_status == 1){
			$new_status = 0;
		}else{
			$new_status = 1;
		}
		mysqli_query($conn,"update kc_cheques set status = '$new_status' where id = '".$cheque_id."' limit 1 ");
		$_SESSION['success'] = 'Cheque Status Successfully Updated!';
		header("Location:cheques.php");
		exit();
	}
	$_SESSION['error'] = 'Something Wrong!';
	header("Location:cheques.php");
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
            <li class="active">Cheques</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
			<div class="box">
                <div class="box-header">
					<?php 
					include("../includes/notification.php"); ?>
					<div class="col-sm-8">
						<h3 class="box-title">All Cheque</h3>
					</div>
					<?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'add_cheque_prints')) {?>
                    <div class="col-sm-4">
						<button class="btn btn-sm btn-success pull-right" data-toggle="modal" data-target="#addCheque">Add Cheque</button>
					</div>
				<?php } ?>
				</div><!-- /.box-header -->
                <div class="box-body no-padding">
				
				 <table class="table table-striped table-hover table-bordered">
                    <tr>
                      <th>Sl No.</th>
					  <th>Name</th>
					  <th>Amount</th>
					  <th>Date</th>
					  <th>Added Date</th>
                      <th>Status</th>
                      <?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'edit_cheque_prints')) {?>
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
					$total_records = mysqli_fetch_assoc(mysqli_query($conn,"select COUNT(*) as total from kc_cheques "));
					$total_pages = ceil($total_records['total']/$limit);
					
					if($page == 1){
						$start = 0;
					}else{
						$start = ($page-1)*$limit;
					}
					
					$cheques = mysqli_query($conn,"select * from kc_cheques limit $start,$limit ");
					if(mysqli_num_rows($cheques) > 0){
						$counter = 1;
						while($cheque = mysqli_fetch_assoc($cheques)){ ?>
							<tr>
								<td><?php echo $counter; ?></td>
								<td><?php echo $cheque['name']; ?></td>
								<td><?php echo $cheque['amount']; ?></td>
								<td><?php echo date("d M Y",strtotime($cheque['date'])); ?></td>
                                <td><?php echo date("d M Y h:i A",strtotime($cheque['addedon'])); ?></td>
								<td>
                                	<?php if($cheque['status'] == 1){ ?>
                                		<label class="label label-success">Active</label>
                                    <?php }else{ ?>
                                    	<label class="label label-danger">Inactive</label>
                                    <?php } ?>
                                </td>
                                <td>
                                	<?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'edit_cheque_prints')){?> 
                                	<button class="btn btn-xs btn-warning" type="button" data-toggle="tooltip" title="Edit Cheque" onclick = "editCheque(<?php echo $cheque['id']; ?>);"><i class="fa fa-pencil"></i></button>
                                	<?php } if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'status_cheque_prints')){
									if($cheque['status']){
										$button_class = 'btn-success';
										$icon_class = 'fa-lock';
										$btn_title = "Make Inactive";
									}else{
										$button_class = 'btn-danger';
										$icon_class = 'fa-unlock';
										$btn_title = "Make Active";
									}
									?>
									<a href="cheques.php?cheque=<?php echo $cheque['id']; ?>" data-toggle="tooltip" title="<?php echo $btn_title; ?>">
										<button class="btn btn-xs <?php echo $button_class; ?>" type="button"><i class="fa <?php echo $icon_class; ?>"></i></button>
									</a>
								<?php } if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'print_cheque_prints')){ ?>
									<a href="cheque_print.php?cheque=<?php echo $cheque['id']; ?>" target="_blank" data-toggle="tooltip" title="Print Cheque">
										<button class="btn btn-xs btn-primary" type="button"><i class="fa fa-print"></i></button>
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
								 <li <?php if((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)){ ?>class="active"<?php } ?>><a href="cheques.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
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
	
	<div class="modal" id="addCheque">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" name="add_cheque_frm" id="add_cheque_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add Cheque</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-header with-border">
						<div class="col-md-12">
							<h3 class="box-title">Add Cheque Panel</h3>
						</div>
					</div><!-- /.box-header -->
					<!-- form start -->
					<div class="box-body">
						
						<div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">Cheque Name</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="name" name="name" maxlength="255" required>
						  </div>
						</div>

						<div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">Cheque Amount</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="amount" name="amount" maxlength="10" required>
						  </div>
						</div>

						<div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">Cheque Date</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="date" name="date" required>
						  </div>
						</div>

                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="addCheque">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
    
    
     <div class="modal" id="editChequeModal">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="cheques.php" name="edit_frm" id="edit_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Edit Cheque</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-body" id="edit-cheque-container">
					
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="editCheque">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
    
	<?php require('../includes/common-js.php'); ?>
	
    <script type="text/javascript">
		function editCheque(cheque){
			$.ajax({
				url: '../dynamic/editCheque.php',
				type:'post',
				data:{cheque:cheque},
				success: function(resp){
					$("#edit-cheque-container").html(resp);
					$("#editChequeModal").modal('show');
				}
			});
		}
	</script>
    
  </body>
</html>

