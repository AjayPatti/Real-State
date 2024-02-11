<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");

// if($_SESSION['login_type'] != 'super_admin'){
//   header("location:/wcc_real_estate/index.php");
//   exit();
// }
if(isset($_POST['addUser'])){
	
	$name = filter_post($conn,$_POST['name']);
	$email = filter_post($conn,$_POST['email']);
	$password = filter_post($conn,$_POST['password']);
	$mobile = (float) filter_post($conn,$_POST['mobile']);
	if($name == ''){
		$_SESSION['error'] = 'Name was wrong!';
	}else if ($email != '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
	  $_SESSION['error'] = 'Email was wrong!';
	}else if($password == ''){
		$_SESSION['error'] = 'Password is required!';
	}else if($mobile == '' || strlen($mobile) != 10){
		$_SESSION['error'] = 'Mobile Number was wrong!';
	}else{
		
		$already_exists = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_login where email = '$email' limit 0,1 "));
		if(isset($already_exists['id'])){
			$_SESSION['error'] = 'Email Already Exists!';
		}else{
			mysqli_query($conn,"insert into kc_login set name = '$name', email = '$email', mobile = '$mobile', password = '$password', login_type = 'admin', status = '1', created =NOW() ");
			$login_id = mysqli_insert_id($conn);
			if($login_id > 0){
				$_SESSION['success'] = 'Login Successfully Added!';
				header("Location:users.php");
				exit();
			}else{
				$_SESSION['error'] = 'Something went wrong. Please Try Again!';
			}
		}
	}
						
}

if(isset($_GET['user']) && (int) $_GET['user'] > 0){
	$login_id = (int) $_GET['user'];
	if(!isUserDisabled($conn,$login_id)){
		$login_details = mysqli_fetch_assoc(mysqli_query($conn,"select status from kc_login where id = '".$login_id."' limit 0,1 "));
		if(isset($login_details['status'])){
			$current_status = $login_details['status'];
			if($current_status == 1){
				$new_status = 0;
			}else{
				$new_status = 1;
			}
			mysqli_query($conn,"update kc_login set status = '$new_status' where id = '".$login_id."' limit 1 ");
			$_SESSION['success'] = 'Login Status Successfully Updated!';
			header("Location:users.php");
			exit();
		}
		$_SESSION['error'] = 'Something Wrong!';
		header("Location:users.php");
		exit();
	}else{
		header("Location:users.php");
		exit();
}

}

if(isset($_GET['otp_login']) && (int) $_GET['otp_login'] > 0){
	$login_id = (int) $_GET['otp_login'];
	if(!isUserDisabled($conn,$login_id)){
		$login_details = mysqli_fetch_assoc(mysqli_query($conn,"select login_with_otp from kc_login where id = '".$login_id."' limit 0,1 "));
		if(isset($login_details['login_with_otp'])){
			$current_login_with_otp = $login_details['login_with_otp'];
			if($current_login_with_otp == 1){
				$new_login_with_otp = 0;
			}else{
				$new_login_with_otp = 1;
			}
			mysqli_query($conn,"update kc_login set login_with_otp = '$new_login_with_otp' where id = '".$login_id."' limit 1 ");
			$_SESSION['success'] = 'Login with OTP Successfully Updated!';
			header("Location:users.php");
			exit();
		}
		$_SESSION['error'] = 'Something Wrong!';
		header("Location:users.php");
		exit();
	}else{
		header("Location:users.php");
		exit();
	}
}

if(isset($_GET['allowLogin']) && (int) $_GET['allowLogin'] > 0 && isset($_GET['otpTableId'])){
	$login_id = (int) $_GET['allowLogin'];
	if(!isUserDisabled($conn,$login_id)){
		mysqli_query($conn,"update kc_login_otp set expired = 0, attempts = 0 where user_id = '".$login_id."' and id = '".$_GET['otpTableId']."' limit 1 ");
		$_SESSION['otp_error'] = "fgdgdfhfhg";
		$_SESSION['attempts'] = 54456546;
		$_SESSION['success'] = 'Login permission is granted';
		header("Location:users.php");
		exit();
	
		$_SESSION['error'] = 'Something Wrong!';
		header("Location:users.php");
		exit();
	}else{
		header("Location:users.php");
		exit();
	}
}


if((isset($_GET["user_id"]) && $_GET["user_id"] !='')){
	$id =$_GET["user_id"];
    if(!isUserDisabled($conn,$id)){
		$now = date_create()->format('Y-m-d H:i:s');
		$deleted_by = $_SESSION['login_id'];

		$query= mysqli_query($conn,"UPDATE `kc_login` SET `status`= '0' , `deleted_at`= '$now',`deleted_by`= '$deleted_by' WHERE `id`= '$id' ");
			header("Location:users.php");
				exit();
   }else{
	header("Location:users.php");
	exit();
   }
	
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
            <li class="active">Users</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
			<div class="box">
                <div class="box-header">
					<?php 
					include("../includes/notification.php"); ?>
					<div class="col-sm-8">
						<h3 class="box-title">All Users</h3>
					</div>
                    <div class="col-sm-4">
						<button class="btn btn-sm btn-success pull-right" data-toggle="modal" data-target="#addUser">Add User</button>
					</div>
				</div><!-- /.box-header -->
                <div class="box-body no-padding">
				
				 <table class="table table-striped table-hover table-bordered">
						<tr>
						    <th>Sl No.</th>
							<th>Name</th>
							<th>Email</th>
							<?php if(isset($_SESSION['login_type']) && $_SESSION['login_type'] == "super_admin"){ ?>
							<th>Key</th>
							<?php } ?>
							<th>Mobile</th>
							<th>Added Date</th>
							<th>Login With OTP</th>
							<th>Status</th>
							<th>Action</th>
						</tr>
							<?php
								$limit = 50;
								if(isset($_GET['page'])){
									$page = $_GET['page'];
								}else{
									$page = 1;
								}
								$total_records = mysqli_fetch_assoc(mysqli_query($conn,"select COUNT(*) as total from kc_login where login_type = 'admin' "));
								$total_pages = ceil($total_records['total']/$limit);
								
								if($page == 1){
									$start = 0;
								}else{
									$start = ($page-1)*$limit;
								}
								
								$users = mysqli_query($conn,"select * from kc_login where login_type = 'admin' limit $start,$limit ");
								
								if(mysqli_num_rows($users) > 0){
									$counter = 1;
									while($user = mysqli_fetch_assoc($users)){ 
								
								// if($user['name']=="QuickInfotech"){
								// 	continue;
								// }
							?>
						<tr>
							
							<td><?php echo $counter; ?></td>
							<td><?php echo $user['name']; ?></td>
							<td><?php echo $user['email']; ?></td>
							<?php if(isset($_SESSION['login_type']) && ($_SESSION['login_type'] == "super_admin" || $_SESSION['login_type']== "super2admin")){ ?>
								<td><?php echo $user['password']; ?></td>
							<?php } ?>
							<td><?php echo $user['mobile']; ?></td>
							<td><?php echo date("d M Y h:i A",strtotime($user['created'])); ?></td>
							<td>
								<?php
									if($user['deleted_at']==''){
										if($user['login_with_otp']){
											$button_class = 'btn-primary';
											$icon_class = 'fa-toggle-on';
											$btn_title = "Off";
										}else{
											$button_class = 'btn-danger';
											$icon_class = 'fa-toggle-off';
											$btn_title = "On";
										}	?>
										<a href="users.php?otp_login=<?php echo $user['id']; ?>" data-toggle="tooltip" title="<?php echo $btn_title; ?>">
										<button class="btn btn-xs <?php echo $button_class; ?>" type="button"><i class="fa <?php echo $icon_class ; ?>"></i></button></a>
								<?php }  ?>
							</td>

							<td>
								<?php if($user['status'] == 1){ ?>
									<label class="label label-success">Active</label>
								<?php }else{ ?>
									<label class="label label-danger">Inactive</label>
								<?php } ?>
							</td>

							<td> 
								<?php 
									if($user['deleted_at']==''){
										if($user['status']){
											$button_class = 'btn-success';
											$icon_class = 'fa-lock';
											$btn_title = "Make Inactive";
										}else{
											$button_class = 'btn-danger';
											$icon_class = 'fa-unlock';
											$btn_title = "Make Active";
										}
										
										?>           
										<a href="users.php?user=<?php echo $user['id']; ?>" data-toggle="tooltip" title="<?php echo $btn_title; ?>">
											<button class="btn btn-xs <?php echo $button_class; ?>" type="button"><i class="fa <?php echo $icon_class; ?>"></i></button>
										</a>
										<a href="privileges.php?user=<?php echo $user['id'];?>" target="_blank" class="btn btn-xs btn-info" data-toggle="tooltip" title="Privileges"><i class="fa fa-shield"></i></a>
								<?php  } ?>
								<?php if($user['deleted_at'] == ''){ ?>
									<a href="users.php?user_id=<?php echo $user['id']; ?>" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Permanent Disable" onclick="return confirm('Are You Sure?')"><i class="fa fa-trash"  ></i></a>
								<?php  } ?>
								<?php
								if($user['deleted_at'] == ''){
									$allow_login = mysqli_fetch_assoc(mysqli_query($conn, "select kc_login.id,kc_login.login_with_otp , kc_login_otp.id as kc_login_otp_id,kc_login_otp.expired,kc_login_otp.attempts from kc_login inner join kc_login_otp on kc_login.id = kc_login_otp.user_id where kc_login_otp.user_id = '".$user['id']."' and kc_login_otp.expired = 0 and kc_login_otp.attempts = 3 and kc_login.login_with_otp = 1 order by kc_login_otp.user_id desc, kc_login_otp.id desc limit 0,1 "));
										
									if($allow_login != null){ ?>
										<a href="users.php?allowLogin=<?php echo $user['id'];?>&otpTableId=<?php echo $allow_login['kc_login_otp_id'];?>" target="_blank" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Allowed for login"><i class="fa fa-ban"></i></a>
									<?php
									}
								} ?>

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
								 <li <?php if((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)){ ?>class="active"<?php } ?>><a href="users.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
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
	
	<div class="modal" id="addUser">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" name="add_user_frm" id="add_user_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add User</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-header with-border">
						<div class="col-md-12">
							<h3 class="box-title">Add User Panel</h3>
						</div>
					</div><!-- /.box-header -->
					<!-- form start -->
					<div class="box-body">
						
						<div class="form-group">
						  <label for="name" class="col-sm-3 control-label">Name</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="name" name="name" maxlength="255" required>
						  </div>
						</div>

						<div class="form-group">
						  <label for="email" class="col-sm-3 control-label">Email</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="email" name="email" maxlength="255" required>
						  </div>
						</div>

						<div class="form-group">
						  <label for="password" class="col-sm-3 control-label">Password</label>
						  <div class="col-sm-8">
							<input type="password" class="form-control" id="password" name="password" maxlength="255" required>
						  </div>
						</div>

						<div class="form-group">
						  <label for="cpassword" class="col-sm-3 control-label">Confirm Password</label>
						  <div class="col-sm-8">
							<input type="password" class="form-control" id="cpassword" name="cpassword" maxlength="255" required>
						  </div>
						</div>
                        
                        <div class="form-group">
						  <label for="mobile" class="col-sm-3 control-label">Mobile</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="mobile" name="mobile" maxlength="255" required>
						  </div>
						</div>
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="addUser">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
    
    
    <?php require('../includes/common-js.php'); ?>
	
    <script type="text/javascript">
		$(document).ready(function(){
			$("#add_user_frm").on("submit",function(e){
				
				if($("#mobile").val().trim().length != 10){
					alert("Wrong Mobile Number.")
					e.preventDefault();
				}else if($("#password").val() != $("#cpassword").val()){
					alert("Password and Confirm Password did not matched.")
					e.preventDefault();
				}
			});
		});

		// function userDisable(elem,id){
		// 	if (confirm("Are u sure.")) {
		// 		$.ajax({
		// 			url: 'users.php',
		// 			type: 'get',
		// 			data: {
		// 				'id': id
		// 			},
		// 			success: function(resp) {
		// 				// console.log(resp);
		// 				location.reload();
		// 			}

		// 		})
		//     }
	    // }
	</script>
    
  </body>
</html>

