<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");
require("../includes/privileges.php");
if($_SESSION['login_type'] != 'super_admin' && $_SESSION['login_type'] != 'super2admin'){
  header("location:/wcc_real_estate/index.php");
  exit();
}

// print_r($_SESSION['login_id']);
$id=$_GET['user'];
if(isUserDisabled($conn,$id)){
  header("Location:users.php");
	exit();
}

if(isset($_POST['button'])){
  // echo "<pre>"; print_r($_POST); die();
  foreach($privileges as $name => $detail){
    $userId = $_GET['user'];
    // echo "<pre>"; print_r($userId);die;
    foreach($detail['Privileges'] as $privilege ){
      
      $privilege_name = $privilege.'_'.$name;
      $status = isset($_POST[$privilege_name])?1:0;
      // echo "<pre>"; print_r(($_POST));die;
      // print_r($status );die;
      // var_dump(isset($_POST[$privilege_name]));die();
      $checkExistance = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_user_privileges where privileges_name = '$privilege_name' and user_id = '$userId' limit 0,1"));
      if(isset($checkExistance['id'])){
        mysqli_query($conn,"update kc_user_privileges set  status = '$status' where id = '".$checkExistance['id']."'");
      }else{
        mysqli_query($conn,"insert into kc_user_privileges set user_id = '$userId',privileges_name = '".$privilege.'_'.$name."', status = '$status'");
      }
          
    }
  }
  $_SESSION['success'] = "successful!";
  header("location:privileges.php?user=".$_GET['user']);
}



// if(isset($_POST['apply_privileges'])){die();
//   // echo "<pre>"; print_r($_POST);die();
//   

//     foreach ($_POST as $key =>$value) {
//       echo "insert into kc_user_privileges set user_id = '$userId',privileges_name = '".$key."'";
//       $add = mysqli_query($conn,"insert into kc_user_privileges set user_id = '$userId',privileges_name = '".$key."'");
//     }die();
// }

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
					   <?php include("../includes/notification.php"); ?>
    					<div class="col-sm-8">
    						<h3 class="box-title"><i class="fa fa-shield"></i> Privileges</h3><hr>
    					</div>
              <div class="col-sm-4"></div>
				    </div><!-- /.box-header -->
                <div class="box-body no-padding" style="margin-left:30px;">
                	<form method="post" action="">
                		
                				<?php 
                					foreach($privileges as $name => $detail){
                            ?>
		                				<div class="row">
		                					<div class="col-md-3"><?php echo $detail['name'] ?></div>
                              <div class="col-md-9">
                                <div class="row">
  		                					<?php 
  		                					foreach($detail['Privileges'] as $privilege){?>
  		                						<div class="col-md-3"><input type="checkbox" name="<?php echo $privilege.'_'.$name ?>" id="<?php echo $privilege.'_'.$name ?>" value="1" <?php if(userCan($conn,$_GET['user'],$privilege.'_'.$name)){ echo 'checked';} ?>> <label for="<?php echo $privilege.'_'.$name ?>"> <?php echo ucwords($privilege) ?></label></div>
  		                					<?php } ?>
                                </div>
                              </div>
		                				</div>
                            <hr>
		                		<?php } ?>
                        <div>
                          <button type="submit" class="btn btn-md btn-success" name="button">Apply</button>
                        </div>
                	     </form>
                      <br>
                    </div><!-- /.box-body -->
              </div><!-- /.box -->
        </section> 
          
      </div><!-- /.content-wrapper -->    
      <?php require('../includes/footer.php'); ?>
    </div><!-- ./wrapper -->
	<?php require('../includes/common-js.php'); ?>
  <script type="text/javascript">
    function iCheckClicked(elem){
      var for_attr = $(elem).attr('for');
      if(for_attr == "checkAll"){
        if(!($(elem).is(":checked"))){
          $('.contact').iCheck('check');
        }else{
          $('.contact').iCheck('uncheck');
        }
      }
    }
  </script>
  
  </body>
</html>