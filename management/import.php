<?php
ob_start();
session_start();

ini_set('auto_detect_line_endings', true);
ini_set('memory_limit', '512M');
ini_set('post_max_size', '200M');
set_time_limit(0);

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");

if(isset($_POST['save'])){
	if(is_array($_FILES['excel_file']) && sizeof($_FILES['excel_file']) > 0){
		$file_name = $_FILES['excel_file']['name'];
		
		$dir = 'excel';
		move_uploaded_file($_FILES['excel_file']['tmp_name'], $dir."/".$file_name);
			
		$name_arr = explode(".",$file_name);
		if(end($name_arr) == "xls" or end($name_arr)=="xlsx"){
			require_once 'PHPExcel/Classes/PHPExcel.php';
			
			$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
			$cacheSettings = array( 'memoryCacheSize' => '256MB');
			PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
			
			$objPHPExcel1 = PHPExcel_IOFactory::load($dir."/".$file_name);
			
			$allDataInSheet = $objPHPExcel1->getActiveSheet()->toArray(null,true,true,true);
			//echo "<pre>"; print_r($allDataInSheet); die;
			if(is_array($allDataInSheet) && sizeof($allDataInSheet) > 0){
        $i = 0;
				foreach($allDataInSheet as $key => $row){
					
					if(is_array($row) && trim($row['A']) != '' && $row['A'] > 0){	//&& sizeof($row) == 52 
						//echo "<pre>"; print_r($row); die;
            $sl_no = $row['A'];
						$code = $row['B'];
						$name = $row['C'];
            $mobile_no = $row['D'];
            if($name == ''){
              $_SESSION['error'][] = $sl_no.'-> Associate Name was wrong!';
            }else if($mobile_no == '' || strlen($mobile_no) != 10){
              $_SESSION['error'][] = $sl_no.'-> Mobile Number was wrong!';
            }else{
      
              $already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select id, mobile_no, code from kc_associates where mobile_no = '$mobile_no' or code = '$code' limit 0,1 "));
              if(isset($already_exits['id']) && $already_exits['mobile_no'] == $mobile_no){
                $_SESSION['error'][] = $sl_no.'-> Mobile Number Already Exists!';
              }else if(isset($already_exits['id']) && $already_exits['code'] == $code){
                $_SESSION['error'][] = $sl_no.'-> Associate code Already Exists!';
              }else{
                // echo "insert into kc_associates set code = '$code', name = '$name', mobile_no = '$mobile_no', password = '12345', status = '1', addedon =NOW(), added_by = '".$_SESSION['login_id']."', last_login =NOW() ";die();
                mysqli_query($conn,"insert into kc_associates set code = '$code', name = '$name', mobile_no = '$mobile_no', password = '12345', status = '1', addedon =NOW(), added_by = '".$_SESSION['login_id']."', last_login =NOW() ");
                $relation_id = mysqli_insert_id($conn);
                if($relation_id > 0){
                  mysqli_query($conn,"insert into kc_contacts set name = '$name', mobile = '$mobile_no', type = 'Associate', customer_id = '$relation_id', status = '1', created =NOW(), created_by = '".$_SESSION['login_id']."' ");
                  $i++;
                }else{
                  $_SESSION['error'][] = $sl_no.'-> Problem in data!';
                }
              }
            }
					}
				}
			}
		}
		unlink($dir."/".$file_name);
    if(isset($i) && $i > 0){
      $_SESSION['success'] = $i.' Records Inserted Successfully';
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
            <li class="active">Reports</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
        	
		      <div class="box">
                <div class="box-header">
					<?php 
					include("../includes/notification.php"); ?>
					<div class="col-sm-4">
						<h3 class="box-title">Import Associates</h3>
					</div>
				</div><!-- /.box-header -->
        <div class="box-body no-padding">
					<form enctype="multipart/form-data" action="import.php" name="add_report_frm" id="add_report_frm" method="post" class="form-horizontal dropzone">
            <div class="form-group">
              <label for="excel_file" class="col-sm-3 control-label">Upload File</label>
              <div class="col-sm-8">
              <input type="file" class="form-control" id="excel_file" name="excel_file">
              </div>
            </div>

            <button type="submit" class="btn btn-primary" id="save" name="save">Save changes</button>
         	</form>
        </div><!-- /.box-body -->
				
				
				
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
  </body>
</html>

