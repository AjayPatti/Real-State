<?php
	ob_start();
	session_start();
	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/checkAuth.php");
	require("../includes/sendMail.php");
	require("../includes/sendMessage.php");
  if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_message_report'))){ 
    header("location:/wcc_real_estate/index.php");
    exit();
   }
	$url = $pagination_url = 'message_report.php';
	$url .= '?search=true';
	$pagination_url .= '?';

	$limit = 500;
	if(isset($_GET['page'])){
	    $page = $_GET['page'];
	    $url .= '&page='.$_GET['page'];
	}else{
	    $page = 1;
	}
	$query = "select * from kc_message_reports order by created desc ";
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
      .btn-app{
        color: white;
        border: none;
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
            <li class="active">Sent Messages</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
			<div class="box">
        <div class="box-header">
			<?php 
				include("../includes/notification.php");
			?>
			<div class="col-sm-4">
				<h3 class="box-title">Sent Messages</h3>
			</div>
        </div><!-- /.box-header -->
        <div class="box-body no-padding">
        	<form enctype="multipart/form-data" action="<?php echo $url; ?>" name="send_message_frm" id="send_message_frm" method="post" class="form-horizontal dropzone">
                 <table class="table table-striped table-hover table-bordered">
                    <tr>
                      <th>SNo.</th>
                      <th nowrap="nowrap">Mobile</th>
                      <th>Message</th>
                      <th>Sent Time</th>
                    </tr>
                    <?php
                    $total_records = mysqli_num_rows(mysqli_query($conn,$query));
                    $total_pages = ceil($total_records/$limit);
                    
                    if($page == 1){
                        $start = 0;
                    }else{
                        $start = ($page-1)*$limit;
                    }
                    
                    $query .= " limit $start,$limit";

                    $messages = mysqli_query($conn,$query);
                    if(mysqli_num_rows($messages) > 0){
                        $counter = $start+1;
                        while($message = mysqli_fetch_assoc($messages)){ 
                          $mobile =  str_replace(","," " , $message['mobile_no']);
                        //  
                        
                        // print_r($mobile); ?>
                            <tr>
                                <td><?php echo $counter; ?>.</td>
                                <td ><?php echo  ($mobile) ; ?></td>
                                <td><?php echo $message['message']; ?></td>
                                <td nowrap="nowrap"><?php echo date("d M Y h:i A",strtotime($message['created'])); ?></td>
                            </tr>
                            <?php
                            $counter++;
                        } ?>
                        <?php
                    }else{
                        ?>
                        <tr>
                            <td colspan="4" align="center"><h4 class="text-red">No Record Found</h4></td>
                        </tr>
                        <?php
                    }
                    ?>
                  </table>
              </form>
        </div><!-- /.box-body -->
				
				<?php if($total_pages > 1){ ?>
					<div class="box-footer clearfix">
					  <ul class="pagination pagination-sm no-margin pull-right">
					   
						<?php
							for($i = 1; $i <= $total_pages; $i++){
								?>
								 <li <?php if((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)){ ?>class="active"<?php } ?>><a href="<?php echo $pagination_url ?>page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
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