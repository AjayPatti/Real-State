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
if(isset($_POST['addAccounts'])){
	$name = filter_post($conn,$_POST['name']);
	$bank_name = filter_post($conn,$_POST['bank_name']);
	$branch_name = filter_post($conn,$_POST['branch_name']);
	$account_no = filter_post($conn,$_POST['account_no']);
	$ifsc_code = filter_post($conn,$_POST['ifsc_code']);
	if($name == ''){
		$_SESSION['error'] = 'Name was wrong!';
	}elseif($account_no == ''){
		$_SESSION['error'] = 'Account No was wrong!';		
	}else{
		$already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_accounts where account_no = '".$account_no."' limit 0,1 "));
		if(isset($already_exits['id'])){
			$_SESSION['error'] = 'Account No Already Exists!';
		}else{
			mysqli_query($conn,"insert into kc_accounts set name = '$name',  bank_name = '$bank_name',  branch_name = '$branch_name',  account_no = '$account_no',  ifsc_code = '$ifsc_code',  status = '1', addedon =NOW(), added_by = '".$_SESSION['login_id']."' ");
			$block_id = mysqli_insert_id($conn);
			if($block_id > 0){
				$_SESSION['success'] = 'Account No Successfully Added!';
				header("Location:accounts.php");
				exit();
			}else{
				$_SESSION['error'] = 'Account No was wrong. Please Try Again!';
			}
		}
	}
						
}

if(isset($_POST['editAccount'])){

	$account_id = filter_post($conn,$_POST['id']);
	$name = filter_post($conn,$_POST['name']);
	$bank_name = filter_post($conn,$_POST['bank_name']);
	$branch_name = filter_post($conn,$_POST['branch_name']);
	$account_no = filter_post($conn,$_POST['account_no']);
	$ifsc_code = filter_post($conn,$_POST['ifsc_code']);
	if($name == ''){
		$_SESSION['error'] = 'Name was wrong!';
	}elseif($account_no == ''){
		$_SESSION['error'] = 'Account No was wrong!';		
	}else{
			mysqli_query($conn,"update kc_accounts set name = '$name',  bank_name = '$bank_name',  branch_name = '$branch_name',  account_no = '$account_no',  ifsc_code = '$ifsc_code' where id = '$account_id' ");
			$aa = mysqli_insert_id($conn);
			if($aa > 0){
				$_SESSION['success'] = 'Account No Successfully Added!';
				header("Location:accounts.php");
				exit();
			}else{
				$_SESSION['error'] = 'Account No was wrong. Please Try Again!';
			}
		}
	
						
}

if(isset($_GET['account']) && is_numeric($_GET['account'])){
	$account_id = $_GET['account'];
	$account_details = mysqli_fetch_assoc(mysqli_query($conn,"select status from kc_accounts where id = '".$account_id."' limit 0,1 "));
	if(isset($account_details['status'])){
		$current_status = $account_details['status'];
		if($current_status == 1){
			$new_status = 0;
		}else{
			$new_status = 1;
		}
		mysqli_query($conn,"update kc_accounts set status = '$new_status' where id = '".$account_id."' limit 1 ");
		$_SESSION['success'] = 'Account Status Successfully Updated!';
		header("Location:accounts.php");
		exit();
	}
	$_SESSION['error'] = 'Something Wrong!';
	header("Location:accounts.php");
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
	<link rel="icon" type="image/x-icon" href="/<?php echo $host_name; ?>img/logo.png">
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
            <li class="active">Accounts</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
			<div class="box">
                <div class="box-header">
					<?php 
					include("../includes/notification.php"); ?>
					<div class="col-sm-8">
						<h3 class="box-title">All Accounts</h3>
					</div>
					<?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'add_projects')) {?>
                    <div class="col-sm-4">
						<button class="btn btn-sm btn-success pull-right" data-toggle="modal" data-target="#addAccounts">Add Accounts</button>
					</div>
				<?php } ?>
				</div><!-- /.box-header -->
                <div class="box-body no-padding">
				
				 <table class="table table-striped table-hover table-bordered">
                    <tr>
                      <th>Sl No.</th>
					  <th>Name</th>
					  <th>Bank Details</th>
					  <th>Added Date</th>
                      <th>Status</th>
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
					$total_records = mysqli_fetch_assoc(mysqli_query($conn,"select COUNT(*) as total from kc_accounts "));
					$total_pages = ceil($total_records['total']/$limit);
					
					if($page == 1){
						$start = 0;
					}else{
						$start = ($page-1)*$limit;
					}
					
					$accounts = mysqli_query($conn,"select * from kc_accounts limit $start,$limit ");
					if(mysqli_num_rows($accounts) > 0){
						$counter = 1;
						while($account = mysqli_fetch_assoc($accounts)){ ?>
							<tr>
								<td><?php echo $counter; ?>.</td>
								<td><?php echo $account['name']; ?></td>
								<td>
									<strong>Account No : </strong><?php echo $account['account_no']; ?><br>
									<?php if($account['bank_name'] != Null){?>
										<strong>Bank Name : </strong><?php echo $account['bank_name']; ?><br>
									<?php } ?>
									<?php if($account['branch_name'] != Null){?>
										<strong>Branch Name : </strong><?php echo $account['branch_name']; ?><br>
									<?php } ?>
									<?php if($account['ifsc_code'] != Null){?>
										<strong>IFSC Code : </strong><?php echo $account['ifsc_code']; ?>
									<?php } ?>
								</td>
                                <td><?php echo date("d M Y h:i A",strtotime($account['addedon'])); ?></td>
								<td>
                                	<?php if($account['status'] == 1){ ?>
                                		<label class="label label-success">Active</label>
                                    <?php }else{ ?>
                                    	<label class="label label-danger">Inactive</label>
                                    <?php } ?>
                                </td>
                                <td>
                                	<?php
                                	if(userCan($conn,$_SESSION['login_id'],$privilegeName = '')) {
									if($account['status']){
										$button_class = 'btn-success';
										$icon_class = 'fa-lock';
										$btn_title = "Make Inactive";
									}else{
										$button_class = 'btn-danger';
										$icon_class = 'fa-unlock';
										$btn_title = "Make Active";
									}
									?>
                                    
									<a href="accounts.php?account=<?php echo $account['id']; ?>" data-toggle="tooltip" title="<?php echo $btn_title; ?>">
										<button class="btn btn-xs <?php echo $button_class; ?>" type="button"><i class="fa <?php echo $icon_class; ?>"></i></button>
									</a>
                                    <?php } if(userCan($conn,$_SESSION['login_id'],$privilegeName = '')) { ?>
                                    <button class="btn btn-xs btn-warning" type="button" data-toggle="tooltip" title="Edit account" onclick = "editaccount(<?php echo $account['id']; ?>);"><i class="fa fa-pencil"></i></button>
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
								 <li <?php if((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)){ ?>class="active"<?php } ?>><a href="accounts.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
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
	
	<div class="modal" id="addAccounts">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" name="add_project_frm" id="add_project_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add Accounts</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-header with-border">
						<div class="col-md-12">
							<h3 class="box-title">Add Accounts Panel</h3>
						</div>
					</div><!-- /.box-header -->
					<!-- form start -->
					<div class="box-body">
						
						<div class="form-group">
						  <label for="name" class="col-sm-3 control-label">Name<sup class="text-danger text-lg">*</sup></label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="name" name="name" maxlength="255" required>
						  </div>
						</div>

						<div class="form-group">
						  <label for="bank_name" class="col-sm-3 control-label">Bank Name</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="bank_name" name="bank_name" maxlength="255">
						  </div>
						</div>

						<div class="form-group">
						  <label for="branch_name" class="col-sm-3 control-label">Branch Name</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="branch_name" name="branch_name" maxlength="255">
						  </div>
						</div>

						<div class="form-group">
						  <label for="account_no" class="col-sm-3 control-label">Account No.<sup class="text-danger">*</sup></label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="account_no" name="account_no" maxlength="255" required>
						  </div>
						</div>

						<div class="form-group">
						  <label for="ifsc_code" class="col-sm-3 control-label">IFSC Code</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="ifsc_code" name="ifsc_code" maxlength="255">
						  </div>
						</div>

						

                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="addAccounts">Save </button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
    
    
     <div class="modal" id="editAccountModal">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="accounts.php" name="edit_frm" id="edit_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Edit Account</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-body" id="edit-account-container">
					
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="editAccount">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
    
	<?php require('../includes/common-js.php'); ?>
	
<script type="text/javascript">
	function editaccount(id){
		// alert(id);
		$.ajax({
			url: '../dynamic/editAccount.php',
			type:'post',
			data:{id:id},
			success: function(resp){
				$("#edit-account-container").html(resp);
				$("#editAccountModal").modal('show');
			}
		});
	}
</script>
    
  </body>
</html>