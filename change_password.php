<?php
ob_start();
session_start();

require("includes/host.php");
require("includes/kc_connection.php");
require("includes/common-functions.php");
require("includes/checkAuth.php");

if(isset($_POST['changePassword']) && $_POST['changePassword']=="Change Password"){
	$pass = filter_post($conn,$_POST['pass']);
	$cpass = filter_post($conn,$_POST['cpass']);
	if($pass == ''){
		$_SESSION['error'] = 'Password is required?';
	}else if($cpass == ''){
		$_SESSION['error'] = 'Password is required?';
	}else if($pass != $cpass){
		$_SESSION['error'] = 'Password and Confirm Password Not Matched?';
	}else{
		$affected_rows = 0;
		mysqli_query($conn,"update kc_login set password = '$cpass', created =NOW() where id = '".$_SESSION['login_id']."' ");
		$affected_rows = mysqli_affected_rows($conn);
		if($affected_rows > 0){
			$_SESSION['success'] = 'Password change successfully!';
			header("Location:change_password.php");
			exit();
		}else{
			$_SESSION['error'] = 'Something went wrong. Please Try Again!';
		}
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

      <?php require('includes/header.php'); ?>
      <!-- Left side column. contains the logo and sidebar -->
      <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <?php echo require('includes/left_sidebar.php'); ?>
        <!-- /.sidebar -->
      </aside>

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Management
            <small>Change Password</small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Change Password</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
			<div class="box">
                <div class="box-header">
					<?php 
					include("includes/notification.php"); ?>
					<div class="col-sm-12">
						<h3 class="box-title">All Contacts</h3>
					</div>
				</div><!-- /.box-header -->
                	<form name="change_password_frm" id="change_password_frm" method="post" class="form-horizontal dropzone">
                      <div class="modal-body">
                        <div class="box box-info">
                            <!-- form start -->
                            <div class="box-body">
                                
                                <div class="form-group">
                                  <label for="name" class="col-sm-3 control-label">New Password</label>
                                  <div class="col-sm-8">
                                    <input type="text" class="form-control" id="pass" name="pass" maxlength="10" required>
                                  </div>
                                </div>
        
                                <div class="form-group">
                                  <label for="mobile" class="col-sm-3 control-label">Confirm Password</label>
                                  <div class="col-sm-8">
                                    <input type="text" class="form-control" id="cpass" name="cpass" maxlength="10" required>
                                  </div>
                                </div>
                            </div><!-- /.box-body -->
                            
                        </div><!-- /.box -->
                      </div>
                      <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="changePassword" name="changePassword" value="Change Password">Change Password</button>
                      </div>
                    </form>			
              </div><!-- /.box -->
        </section> 
          
      </div><!-- /.content-wrapper -->    
      <?php require('includes/footer.php'); ?>

      <?php require('includes/control-sidebar.php'); ?>
      <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
      <div class="control-sidebar-bg"></div>
    </div><!-- ./wrapper -->    
    
    <?php require('includes/common-js.php'); ?>
	
    <script type="text/javascript">
		$(document).ready(function(){
			$("#change_password_frm").on("submit",function(e){
				if($("#pass").val()==''){
					alert("Password is requird?")
					e.preventDefault();
				}
			});
		});
	</script>
    
  </body>
</html>

