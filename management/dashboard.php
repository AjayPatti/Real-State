<?php 
ob_start();
session_start();
$_SESSION['timestamp'] = time();
require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/checkAuth.php");
require("../includes/common-functions.php");
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>WCC</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.4 -->
    <link href="/<?php echo $host_name; ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- FontAwesome 4.3.0 -->
    <link href="/<?php echo $host_name; ?>/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Ionicons 2.0.0 -->
    <link href="/<?php echo $host_name; ?>/css/ionicons.min.css" rel="stylesheet" type="text/css" />
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
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="/<?php echo $host_name; ?>/js/html5shiv.min.js"></script>
        <script src="/<?php echo $host_name; ?>/js/respond.min.js"></script>
    <![endif]-->
     <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <script type="text/javascript">
     // ["Copper", 8.94, "#b87333"],
     //    ["Silver", 10.49, "silver"],
     //    ["Gold", 19.30, "gold"],
     //    ["Platinum", 21.45, "color: #e5e4e2"]
    google.charts.load("current", {packages:['corechart']});
    // google.charts.setOnLoadCallback(drawChart);  
    google.charts.setOnLoadCallback(drawChartforDebit);
//     function drawChart() {
//       var data = google.visualization.arrayToDataTable([
//         ["Month", "Received Amount", { role: "style" } ],
       
//       <?php 
//         $start_date = date("Y-m",strtotime("-1 year"));
//         // echo $start_date; die;
//           // echo date('Y-m-d 00:00:01',strtotime($start_date)); die;
//         while($start_date <= date("Y-m")){
//           // echo "SELECT SUM(amount) as received_amt FROM kc_customer_transactions WHERE status = 1 and cr_dr = 'cr' and paid_date BETWEEN '".date('Y-m-d 00:00:01',strtotime($start_date))."' and  '".date('Y-m-t 23:59:59',strtotime($start_date))."'";

//             $query = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) as received_amt FROM kc_customer_transactions WHERE status = 1 and cr_dr = 'cr' and paid_date BETWEEN '".date('Y-m-d 00:00:01',strtotime($start_date))."' and  '".date('Y-m-t 23:59:59',strtotime($start_date))."'"));

//           echo "['".date('M Y',strtotime($start_date))."', ".$query['received_amt'].", \"'#e5e4e2'\"],";
//           $start_date = date('Y-m',strtotime($start_date." +1 month"));
//         }


//        //  if(date("m") >= 4){
//        //    $start_date = date('Y-m', strtotime('April'));
//        //    // echo $start_date;
//        //    while($start_date <= date('Y-m')){
            
//        //      $query = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) as received_amt FROM kc_customer_transactions WHERE status = 1 and cr_dr = 'dr' and paid_date BETWEEN '".date('Y-m-d 00:00:01',strtotime($start_date))."' and  '".date('Y-m-t 23:59:59',strtotime($start_date))."'"));
//        //       echo "['".date('M Y',strtotime($start_date))."', ".$query['received_amt'].", \"'#e5e4e2'\"],";
//        //       $start_date = date('Y-m',strtotime($start_date." +1 month"));
//        //    }
//        //  } elseif(date("m") < 4){
//        //      $start_date = date("Y-04",strtotime("-1 year"));
//        //      while($start_date <= date("Y-03")){
//        //          $query = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) as received_amt FROM kc_customer_transactions WHERE status = 1 and cr_dr = 'dr' and paid_date BETWEEN '".date('Y-m-d 00:00:01',strtotime($start_date))."' and  '".date('Y-m-t 23:59:59',strtotime($start_date))."'"));

//        //        echo "['".date('M Y',strtotime($start_date))."', ".$query['received_amt'].", \"'#e5e4e2'\"],";
//        //        $start_date = date('Y-m',strtotime($start_date." +1 month"));
//        //      }
//        // }else{
//        //  die();
//        // }
//          ?>
//       ]);
// // alert(data);$start_date
//       var view = new google.visualization.DataView(data);
//       view.setColumns([0, 1,
//                        { calc: "stringify",
//                          // date-format: "YYYY-MM",
//                          sourceColumn: 1,
//                          type: "string",
//                          role: "annotation" },
//                        2]);

//       var options = {
//         title: "Amount Received Per Month",
//         height:180,
//         bar: {groupWidth: "10%"},
//         legend: { position: "none"},
        
//       };
//       var chart = new google.visualization.ColumnChart(document.getElementById("columnchart_values"));
//       chart.draw(view, options);
//   }



  function drawChartforDebit() {
      var data = google.visualization.arrayToDataTable([
        ["Month", "Received Amount", { role: "style" } ],
       
      <?php 
        $start_date = date("Y-m-d",strtotime("-1 year"));
        // echo $start_date; die;
          // echo date('Y-m-d 00:00:01',strtotime($start_date)); die;
        while($start_date <= date("Y-m-d")){
          $query = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) as received_amt FROM kc_customer_transactions WHERE status = 1 and cr_dr = 'dr' and paid_date BETWEEN '".date('Y-m-01',strtotime($start_date))."' and  '".date('Y-m-t',strtotime($start_date))."'"));

          echo "['".date('M Y',strtotime($start_date))."', ".$query['received_amt'].", \"'#e5e4e2'\"],";
          $start_date = date('Y-m-d',strtotime($start_date." +1 month"));
        }


       //  if(date("m") >= 4){
       //    $start_date = date('Y-m', strtotime('April'));
       //    // echo $start_date;
       //    while($start_date <= date('Y-m')){
            
       //      $query = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) as received_amt FROM kc_customer_transactions WHERE status = 1 and cr_dr = 'dr' and paid_date BETWEEN '".date('Y-m-d 00:00:01',strtotime($start_date))."' and  '".date('Y-m-t 23:59:59',strtotime($start_date))."'"));
       //       echo "['".date('M Y',strtotime($start_date))."', ".$query['received_amt'].", \"'#e5e4e2'\"],";
       //       $start_date = date('Y-m',strtotime($start_date." +1 month"));
       //    }
       //  } elseif(date("m") < 4){
       //      $start_date = date("Y-04",strtotime("-1 year"));
       //      while($start_date <= date("Y-03")){
       //          $query = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) as received_amt FROM kc_customer_transactions WHERE status = 1 and cr_dr = 'dr' and paid_date BETWEEN '".date('Y-m-d 00:00:01',strtotime($start_date))."' and  '".date('Y-m-t 23:59:59',strtotime($start_date))."'"));

       //        echo "['".date('M Y',strtotime($start_date))."', ".$query['received_amt'].", \"'#e5e4e2'\"],";
       //        $start_date = date('Y-m',strtotime($start_date." +1 month"));
       //      }
       // }else{
       //  die();
       // }
         ?>
      ]);
// alert(data);$start_date
      var view = new google.visualization.DataView(data);
      view.setColumns([0, 1,
                       { calc: "stringify",
                         // date-format: "YYYY-MM",
                         sourceColumn: 1,
                         type: "string",
                         role: "annotation" },
                       2]);

      var options = {
        title: "Amount Received Per Month",
        height:180,
        bar: {groupWidth: "10%"},
        legend: { position: "none"},
        
      };
      var chart = new google.visualization.ColumnChart(document.getElementById("columnchart_totalDebit"));
      chart.draw(view, options);
  }
  </script>
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
            Dashboard
            <small>Control panel</small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Dashboard</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <!-- Small boxes (Stat box) -->
          <div class="row">
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-navy">
                <?php
                $new_booking = mysqli_fetch_assoc(mysqli_query($conn,"select COUNT(*) as total from kc_customer_blocks where addedon >= '".date("Y-m-d 00:00:00")."' and status = '1' ")); ?>
                <div class="inner">
                  <h3><?php echo $new_booking['total']; ?></h3>
                  <p>Total Today New Booking</p>
                </div>
                <div class="icon">
                  <i class="fa fa-bookmark"></i>
                </div>
                <a href="customers.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div><!-- ./col -->


            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-blue">
                <?php
                $total_associates = mysqli_fetch_assoc(mysqli_query($conn,"select COUNT(*) as total from kc_associates where status = '1' ")); ?>
                <div class="inner">
                  <h3><?php echo $total_associates['total']; ?></h3>
                  <p>Total Associates</p>
                </div>
                <div class="icon">
                  <i class="fa fa-user-secret"></i>
                </div>
                <a href="associates.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div><!-- ./col -->



            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-fuchsia">
                <?php
                $total_contacts = mysqli_fetch_assoc(mysqli_query($conn,"select COUNT(*) as total from kc_projects where status = '1' ")); ?>
                <div class="inner">
                  <h3><?php echo $total_contacts['total']; ?></h3>
                  <p>Total Project</p>
                </div>
                <div class="icon">
                  <i class="fa fa-bookmark"></i>
                </div>
                <a href="projects.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div><!-- ./col -->


            <?php if(($_SESSION['login_type'] == "super_admin") || ($_SESSION['login_type'] == "super2admin" )){ ?>
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-aqua">
                <?php
                $total_customers = mysqli_fetch_assoc(mysqli_query($conn,"select COUNT(*) as total from kc_customers where status = '1' ")); ?>
                <div class="inner">
                  <h3><?php echo $total_customers['total']; ?></h3>
                  <p>Total Customers</p>
                </div>
                <div class="icon">
                  <i class="fa fa-users"></i>
                </div>
                <a href="customers.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div><!-- ./col -->
            <?php } ?>


            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-green">
                <?php
                $total_contacts = mysqli_fetch_assoc(mysqli_query($conn,"select COUNT(*) as total from kc_contacts where status = '1' ")); ?>
                <div class="inner">
                  <h3><?php echo $total_contacts['total']; ?></h3>
                  <p>Total Contacts</p>
                </div>
                <div class="icon">
                  <i class="fa fa-phone"></i>
                </div>
                <a href="contacts.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div><!-- ./col -->


            <div class="col-lg-3 col-xs-6">
              <div class="small-box bg-olive">
                <?php
                $total_contacts = mysqli_fetch_assoc(mysqli_query($conn,"select COUNT(*) as total from kc_login where status = '1' and login_type = 'admin' ")); ?>
                <div class="inner">
                  <h3><?php echo $total_contacts['total']; ?></h3>
                  <p>Total User</p>
                </div>
                <div class="icon">
                  <i class="fa fa-user"></i>
                </div>
                <a href="users.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div> <!-- ./col -->



            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-orange">
                <?php
                $employee = mysqli_fetch_assoc(mysqli_query($conn,"select COUNT(*) as total from kc_employees where status = '1' ")); ?>
                <div class="inner">
                  <h3><?php echo $employee['total']; ?></h3>
                  <p>Total Employee</p>
                </div>
                <div class="icon">
                  <i class="fa fa-user"></i>
                </div>
                <a href="employees.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div><!-- ./col -->

            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-red">
                <?php
                $plc = mysqli_fetch_assoc(mysqli_query($conn,"select COUNT(*) as total from kc_plc where status = '1' ")); ?>
                <div class="inner">
                  <h3><?php echo $plc['total']; ?></h3>
                  <p>Total PLC</p>
                </div>
                <div class="icon">
                  <i class="fa fa-arrow-circle-right"></i>
                </div>
                <a href="plc.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div><!-- ./col -->
            
          </div><!-- /.row -->

          <div class="row">
          	<?php if(($_SESSION['login_type'] == "super_admin") || ($_SESSION['login_type'] == "super2admin" )){ ?>
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-aqua">
                
                <div class="inner">
                  <h5>Today New Booking</h5>
                  <table class="table table-bordered table-hover" style="color:white;"> 
                  <thead>
                    <tr>
                      <!-- <th>SNo.</th> -->
                      <th>Project</th>
                      <th>Count</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      $total_projects = mysqli_query($conn,"SELECT block_id from kc_customer_blocks where status = '1' and addedon >= '".date("Y-m-d 00:00:00")."' GROUP BY block_id"); 
                      while($project = mysqli_fetch_array($total_projects)){
                        $blocks= mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_blocks where id = '".$project['block_id']."'"));
                        $projects_name= mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_projects where id = '".$blocks['project_id']."'"));
                    ?>
                    <tr>
                      <td><?php echo $projects_name['name']; ?></td>
                      <td><?php echo countBlockNo($conn,$project['block_id']); ?></td>
                    </tr>
                    <?php  } ?>

                  </tbody>
                </table>
                </div>
                
                <a href="customers.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div>

            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-blue">
                
                <div class="inner">
                  <h5>Current New Transactions</h5>
                  <table class="table table-bordered table-hover" style="color:white;"> 
                  <thead>
                    <tr>
                      <!-- <th>SNo.</th> -->
                      <th>Cash Payment</th>
                      <th>Bank Transfers</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      $cash_payment = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(amount) as cash_amount from kc_customer_transactions where payment_type = 'Cash' and addedon between '".date('Y-m-d 00:00:01')."' and '".date('Y-m-d 23:59:59')."' and status = 1 and cr_dr = 'dr'")); 
                      $cheque_payment = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(amount) as cheque_amount from kc_customer_transactions where payment_type != 'Cash' and addedon between '".date('Y-m-d 00:00:01')."' and '".date('Y-m-d 23:59:59')."' and status = 1 and cr_dr = 'dr'"));
                    ?>
                    <tr>
                      <td>₹ <?php echo $cash_payment['cash_amount']?$cash_payment['cash_amount']:'0'; ?></td>
                      <td>₹ <?php echo $cheque_payment['cheque_amount']?$cheque_payment['cheque_amount']:'0'; ?></td>
                    </tr>

                  </tbody>
                </table>
                </div>
                
                <a href="today_transaction.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div>

            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-fuchsia">
                <div class="inner">
                  <h5>SMS Balance</h5>
                  <div class="text-center"><h4 id="sms-balance">N/A</h4></div>
                </div>
                
                <a href="javascript:void(0);" id="checkSmsBalance" class="small-box-footer">Check Balance <i class="fa fa-refresh" id="fa-sms-spin"></i></a>
              </div>
            </div>

        	<?php } ?>
          
          <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-orange">
                <?php
                $due_payments = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(emi_amount)as total FROM `kc_customer_emi` WHERE `paid_date`< CURDATE(); ")); ?>
                <div class="inner">
                  <h3>₹<?php echo $due_payments['total'] ; ?></h3>
                  <p>Due Payments</p>
                </div>
                <div class="icon">
                  <i class="fa fa-user"></i>
                </div>
                <a href="" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div><!-- ./col -->
          </div>
          
          <?php if(($_SESSION['login_type'] == "super_admin") || ($_SESSION['login_type'] == "super2admin" )){ ?>
          <div class="row" style="overflow: scroll;">
            <?php if(($_SESSION['login_type'] == "super_admin") || ($_SESSION['login_type'] == "super2admin" )){ ?>
              <div class="col-lg-12 col-xs-12" style="width:100%">
                <div class="small-box bg">
                  <div  class="inner" id="columnchart_values">
                    <!--  -->
                  </div>
                </div>
              </div>  
            <?php } ?>
          </div>
          <?php } ?>



          <!-- <?php if(($_SESSION['login_type'] == "super_admin") || ($_SESSION['login_type'] == "super2admin" )){ ?> -->
          <!-- <div class="row" style="overflow: scroll;"> -->
            <!-- <?php if(($_SESSION['login_type'] == "super_admin") || ($_SESSION['login_type'] == "super2admin" )){ ?> -->
              <!-- <div class="col-lg-12 col-xs-12" style="width:100%"> -->
                <!-- <div class="small-box bg"> -->
                  <!-- <div  class="inner" id="columnchart_totalDebit"> -->
                    <!--  -->
                 
                  <!-- </div> -->
                <!-- </div> -->
              <!-- </div>   -->
            <!-- <?php } ?> -->
          <!-- </div> -->
          <!-- <?php } ?> -->
      </div><!-- /.content-wrapper -->    
      <?php require('../includes/footer.php'); ?>
    </div><!-- ./wrapper -->

    <?php require('../includes/common-js.php'); ?>

    <script type="text/javascript">
      $(function(){
        $("#checkSmsBalance").click(function(){
          $("#fa-sms-spin").addClass('fa-spin');
          $.ajax({
            url: '../dynamic/getSmsBalance.php',
            type:'post',
            data:{},
            success: function(resp){
              $("#sms-balance").html(resp);
              $("#fa-sms-spin").removeClass('fa-spin');
            },
            error: function(resp){
              $("#fa-sms-spin").removeClass('fa-spin');
            }
          });
        });
      });
    </script>
  </body>
</html>