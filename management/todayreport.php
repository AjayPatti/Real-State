<?php
	ob_start();
	session_start();
	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/checkAuth.php");

  if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_due_amount'))){ 
    header("location:/wcc_real_estate/index.php");
    exit();
  }
 	$url = 'todayreport.php?search=Search';		
  //echo "<pre>"; print_r($_SESSION['login_id']); die;
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
    <style type="text/css">
      @media print {
        .printAreahidden {
          display: none;
        }
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
            <li class="active">Today Report</li>
          </ol>
        </section>

        <section class="content">
          <div class="box">

          <div class="box-header">
            <div class="row">
              <div class="col-sm-10">
            <h3 class="box-title">Today Report</h3>
          </div>
           <?php 

            $row = mysqli_fetch_assoc(mysqli_query($conn,"select COUNT(*) as total from kc_customer_follow_ups_hist where created_at >= CURDATE() "));
       if($row > 0){ ?>
            <div class="row col-sm-1 ">
              <a href="todayreport_excel_export.php" class="printAreahidden btn btn-sm btn-success pull-right"><i class="fa fa-file-excel-o"></i> Excel Export</a>
            </div>
            <div class="row col-sm-1">
              <button class="printAreahidden btn btn-sm btn-primary pull-right" onClick="window.print()"><i class="fa fa-file-pdf-o"></i> Print</button>
            </div>
          
          <?php } ?>
        </div>
       </div>
      
        <div class="box-body no-padding">
          <div class="table-responsive">
           <table id="printableArea" class="table table-striped table-hover table-bordered">
            <tr>
              <th>SNo.</th>
              <th>Details</th>
              <th>Project Details</th>
              <!-- <th>Amount</th> -->
              <th>Next Follows Date</th>
              <th>User</th>
              <th>Remark</th>
              
            </tr>
              <?php
                $limit = 100;
                if(isset($_GET['page'])){
                  $page = $_GET['page'];
                }else{
                  $page = 1;
                }
                $start_date  = date('Y-m-d 00:00:01');
                $end_date  = date('Y-m-d 23:59:59');
                $total_records = mysqli_fetch_assoc(mysqli_query($conn,"select SUM(total) as total from (select COUNT(*) as total from kc_customer_follow_ups_hist where created_at between '".$start_date."' and '".$end_date."' group by customer_id, block_id, block_number_id) src "));
                if($_SESSION['login_type'] != 'super_admin'){
                  $total_records = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as total 
                  FROM kc_customer_follow_ups_hist 
                  WHERE created_at BETWEEN '".$start_date."' AND '".$end_date."'  
                  AND created_by = '".$_SESSION['login_id']."' 
                  GROUP BY customer_id, block_id, block_number_id"));
                  
                }
                
                // if($_SESSION['login_type'] != 'super_admin'){
                //   $total_records = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(total) as total from (select SUM(COUNT(*)) as total from kc_customer_follow_ups_hist where created_at between '".$start_date."' and '".$end_date."'  and created_by = '".$_SESSION['login_id']."' group by customer_id, block_id, block_number_id) src "));
                // } comment due to mysqli err on 12012024

                // if ($_SESSION['login_type'] != 'super_admin') {
                //   $query = "SELECT COUNT(*) as total 
                //             FROM kc_customer_follow_ups_hist 
                //             WHERE created_at BETWEEN '".$start_date."' AND '".$end_date."'  
                //             AND created_by = '".$_SESSION['login_id']."' 
                //             GROUP BY customer_id, block_id, block_number_id";
                  
                //   $result = mysqli_query($conn, $query);
                //   $total_records = mysqli_fetch_assoc($result);
                // }
                
                if(!isset($total_records['total'])){
                  $total_records['total'] = 0;
                }
                
                
                $total_pages = ceil($total_records['total']/$limit);
                
                if($page == 1){
                  $start = 0;
                }else{
                  $start = ($page-1)*$limit;
                }

                $query = "select cfuh.* from kc_customer_follow_ups_hist cfuh left join kc_customers kc ON kc.id= cfuh.customer_id where created_at between '".$start_date."' and '".$end_date."'AND kc.blacklisted = '0' ";
                if($_SESSION['login_type'] != 'super_admin'){
                  $query .= " and created_by = '".$_SESSION['login_id']."' ";
                }
                $query .= " group by customer_id, block_id, block_number_id limit $start,$limit ";
                // echo $query; die;
                $hists = mysqli_query($conn, $query);
                if(mysqli_num_rows($hists) > 0){
                  $counter = 1;
                  while($hist = mysqli_fetch_assoc($hists)){
                    //echo "<pre>"; print_r($hist); die;
                    $customer = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_customers where id = '".$hist['customer_id']."'"));
                    $blocks = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_blocks where id = '".$hist['block_id']."' "));  // and status = '1'
                   // print_r($blocks);die;
                    $block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select block_number from kc_block_numbers where block_id = '".$blocks['id']."' AND id =  '".$hist['block_number_id']."' limit 0,1 "));
                    $payment_type =   mysqli_fetch_assoc(mysqli_query($conn, "SELECT customer_payment_type FROM kc_customer_blocks WHERE customer_id = '".$hist['customer_id']."' AND block_id = '".$hist['block_id']."' AND block_number_id = '".$hist['block_number_id']."' " ));
                    $userType = mysqli_fetch_assoc(mysqli_query($conn ,"SELECT name FROM kc_login WHERE id = '".$hist['created_by']."' "));
                    $emi = nextEMIDetails($conn , $hist['customer_id'] , $hist['block_id'] , $hist['block_number_id']);
                    $part_amc = getPartAmount($conn , $hist['customer_id'] , $hist['block_id'] , $hist['block_number_id'] );

                    $total_amc= mysqli_fetch_assoc(mysqli_query($conn , "SELECT SUM(amount) AS total_cr FROM kc_customer_transactions WHERE customer_id = '".$hist['customer_id']."' AND cr_dr = 'cr' AND block_id = '".$hist['block_id']."' AND block_number_id = '".$hist['block_number_id']."' AND status = 1 "));
                    $total_paid_amc =  mysqli_fetch_assoc(mysqli_query($conn , "SELECT SUM(amount) AS total_dr FROM kc_customer_transactions WHERE customer_id = '".$hist['customer_id']."' AND cr_dr = 'dr' AND block_id = '".$hist['block_id']."' AND block_number_id = '".$hist['block_number_id']."' AND status = 1 "));
                    $due_amc = getPartAmount($conn , $hist['customer_id'] , $hist['block_id'] , $hist['block_number_id'] ) ;
                   
                    /*echo $hist['customer_id']."<br>";
                    echo $blocks['id']."<br>";
                    echo $hist['block_number_id']."<br>";*/

                    //print_r($customer_follow_up);

              ?>
                <tr>
                  <td><?php echo $counter; ?>.</td>
                  <td nowrap="nowrap">
                    <strong><?php echo customerName($conn,$hist['customer_id']); ?></strong><br>
                      <strong><?php echo $customer['parent_name_relation']; ?></strong> <?php if($customer['parent_name'] != ''){ ?>of <strong><?php echo isset($customer['parent_name_sub_title'])?$customer['parent_name_sub_title']:''; ?> <?php echo $customer['parent_name']; } ?></strong><br>
                      <?php if($customer['nominee_name'] != ''){ ?>
                        Co-owner: <strong class="text-danger"><?php echo $customer['nominee_name']; ?></strong>
                        <?php if($customer['nominee_relation'] != ''){ ?>
                          <strong class="text-danger">(<?php echo $customer['nominee_relation']; ?>)</strong>
                        <?php } ?>
                        <br> Mobile: <strong><?php echo $customer['mobile']; ?></strong>
                      <?php } ?>
                      <br> <b>Mobile:</b> <?php echo $customer['mobile']; ?>
                  </td>
                  <td nowrap="nowrap">
                    <?php 
                      echo '<h5 class="text-success">'.blockProjectName($conn,$hist['block_id']).'<br>'.$blocks['name'].'('.$block_number_details['block_number'].')'."</h5>";
                    ?>
                  </td>
                  <?php   /*<td nowrap="nowrap">
                    <?php if ($payment_type['customer_payment_type'] == 'EMI'){
                    echo '<strong class="text-warning"> EMI </strong> : '; 
                    }elseif($payment_type['customer_payment_type'] == 'Part'){
                        echo '<strong class="text-success"> Part </strong> : ';
                    }
                    if($payment_type['customer_payment_type'] == 'EMI'){
                    echo '<strong class="text-warning"> '.$emi['emi_amount'].' ₹</strong><br>';
                    } else{
                      echo '<strong class="text-success"> '.$part_amc.' ₹</strong><br>';
                    } echo "<br/>".'<strong class="text-warning"> Received : '.$total_paid_amc['total_dr'].' ₹</strong><br>'.'<strong class="text-success"> Total : '.$total_amc['total_cr'].' ₹</strong><br>'.'<strong class="text-danger"> Balance : '.$due_amc.' ₹</strong><br>' ;
                    ?>
                  </td>*/ ?>

                    <td nowrap="nowrap">
                    <?php  
                      echo '<strong class="text-info">'.date("d-m-Y",strtotime($hist['next_follow_up_date'])).'</strong>';
                    ?>
                      
                    </td>
                 <td nowrap="nowrap">
                  <?php  
                      echo '<strong class="text-info">'.$userType['name'].'</strong>';
                    ?>
                 </td>
                 <td>
                  <?php   
                    
                      echo '<strong class="text-info">'.$hist['remarks'].'</strong>';
                    
                    ?>
                    <?php $followup_created_date = ($hist['created_at'] != NULL)?$hist['created_at']:$hist['created_at']; ?>
										<br>
										<b class="text-danger">On <?php echo date("d-m-Y",strtotime($followup_created_date)); ?></b>
                 </td>
                </tr>
                <?php
                $counter++;
              }
            }else{
              ?>
              <tr>
                <td colspan="7" align="center"><h4 class="text-red">No Record Found</h4></td>
              </tr>
              <?php
            }
            ?>
            </table>
          </div>
          <?php if($total_pages > 1){ ?>
          <div class="box-footer clearfix">
            <ul class="pagination pagination-sm no-margin pull-right">
            <?php
              for($i = 1; $i <= $total_pages; $i++){
                ?>
                 <li <?php if((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)){ ?>class="active"<?php } ?>><a href="<?php echo $url ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                <?php
              }
            ?>
            </ul>
          </div>
        <?php } ?>
                </div><!-- /.box-body -->
            </div>
        </section>
      </div>
	</div>
	<?php require('../includes/common-js.php'); ?>
  </body>
</html>