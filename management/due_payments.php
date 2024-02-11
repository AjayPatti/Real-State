<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");

if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_due_payment'))){ 
 	header("location:/wcc_real_estate/index.php");
 	exit();
 }
//  $arr = array(1,5,19,200,999);
// $size =count($arr);
// for($i=0;$i<$size;$i++){
// 	for($j=$i+1;$j<$size;$j++){
// 		if($arr[$i] > $arr[$j]){
// 			$temp=$arr[$i];
// 			$arr[$i] =$arr[$j];
// 			$arr[$j]=$temp;
// 		}
// 	}
// }
// // print_r($arr);
// echo "Largest Number : ".(!empty($arr[2])?$arr[2]:FALSE)." Largest Second Number : ".(!empty($arr[1])?$arr[1]:FALSE);
// // print_r(implode(',',$arr));

//  die;
$search = false;
$outstandings = [];
if(isset($_GET['associate']) && (int) $_GET['associate'] > 0){
	$sales_person_id = (int) $_GET['associate'];
	$search = true;

	$outstandings = outStandingPayments($conn,$sales_person_id);

	if(sizeof($outstandings) > 0 && isset($_GET['action']) && $_GET['action'] == "export"){

		header("Content-Type: application/xls");    
		header("Content-Disposition: attachment; filename=due_payments.xls");  
		header("Pragma: no-cache"); 
		header("Expires: 0");
		
		echo '<table border="1">';
		
		echo '<tr><th>Sl No.</th><th>Customer</th><th>Block</th><th>Plot Number</th><th>Total Credeted</th><th>Total Debited</th><th>Pending Amount</th><th>Next Due Date</th></tr>';
		
		$counter = 1;
		foreach($outstandings as $outstanding){
			$customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_customers where id = '".$outstanding['customer_id']."' limit 0,1 "));
			$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_blocks where id = '".$outstanding['block_id']."' limit 0,1 "));
			$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select block_number from kc_block_numbers where id = '".$outstanding['block_number_id']."' limit 0,1 "));
			?>
			<tr>
				<td><?php echo $counter; ?></td>
				<td><?php echo $customer_details['name'].' ('.customerID($outstanding['customer_id']).')'; ?></td>
                <td><?php echo $block_details['name']; ?></td>
                <td><?php echo $block_number_details['block_number']; ?></td>
                <td><h4 class="text-primary"><?php echo number_format($outstanding['total_credited'],2); ?></h4></td>
                <td><h4 class="text-success"><?php echo number_format($outstanding['total_debited'],2); ?></h4></td>
                <td><h4 class="text-danger"><?php echo number_format($outstanding['total_credited'] - $outstanding['total_debited'],2); ?></h4></td>
                <td><h4 class="text-danger"><?php echo date("d M Y",strtotime($outstanding['next_due_date'])); ?></h4></td>
            </tr>
			<?php
			$counter++;
		}
		echo '</table>';
		die;
	}
}else{
	$query = "select ct.customer_id,mt.associate, ct.block_id, ct.block_number_id from kc_customer_transactions ct INNER JOIN kc_customer_blocks cb ON cb.customer_id = ct.customer_id and cb.block_id = ct.block_id and cb.block_number_id = ct.block_number_id INNER JOIN kc_associate_percentage as mt on ct.customer_id = mt.customer_id where  cb.status = '1' and cb.status = '1' group by cb.customer_id";
	
	$customers = mysqli_query($conn, $query);
	$counter = 0;
	while($customer = mysqli_fetch_assoc($customers)){
    $total= isOutStandingPayment($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']);
		if(isOutStandingPayment($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id'])){
			$outstandings[$counter]['customer_id'] = $customer['customer_id'];
			$outstandings[$counter]['block_id'] = $customer['block_id'];
			$outstandings[$counter]['block_number_id'] = $customer['block_number_id'];
			$outstandings[$counter]['associate_id'] = $customer['associate'];
			$outstandings[$counter]['total_credited'] = totalCredited($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']);
			$outstandings[$counter]['total_debited'] = totalDebited($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']);
			$outstandings[$counter]['next_due_date'] = nextDueDate($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']);
     		 $counter++;
		}
	}
	$i = 1;
	if(isset($outstandings) && sizeof($outstandings) > 0 && isset($_GET['action']) && $_GET['action'] == "export"){
		header("Content-Type: application/xls");    
		header("Content-Disposition: attachment; filename=due_payments.xls");  
		header("Pragma: no-cache"); 
		header("Expires: 0");
		
		echo '<table border="1">';
		if($i == 1){
			echo '<tr><th>Sl No.</th><th>Customer</th><th>Block</th><th>Plot Number</th><th>Total Credeted</th><th>Total Debited</th><th>Pending Amount</th><th>Next Due Date</th></tr>';
		}
		foreach($outstandings as $outstanding){
			
			$customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_customers where id = '".$outstanding['customer_id']."' limit 0,1 "));
			$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_blocks where id = '".$outstanding['block_id']."' limit 0,1 "));
			$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select block_number from kc_block_numbers where id = '".$outstanding['block_number_id']."' limit 0,1 "));
			?>
			<tr>
				<td><?php echo $i; ?></td>
				<td><?php echo $customer_details['name'].' ('.customerID($outstanding['customer_id']).')'; ?></td>
				<td><?php echo $block_details['name']; ?></td>
				<td><?php echo $block_number_details['block_number']; ?></td>
				<td><h4 class="text-primary"><?php echo number_format($outstanding['total_credited'],2); ?></h4></td>
				<td><h4 class="text-success"><?php echo number_format($outstanding['total_debited'],2); ?></h4></td>
				<td><h4 class="text-danger"><?php echo number_format($outstanding['total_credited'] - $outstanding['total_debited'],2); ?></h4></td>
				<td><h4 class="text-danger"><?php echo date("d M Y",strtotime($outstanding['next_due_date'])); ?></h4></td>
			</tr>
			<?php
			 $i++;
		}
		echo '</table>';
		
	}
	if(isset($_GET['action']) && $_GET['action'] == "export"){
		die;
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
	<link href="/<?php echo $host_name; ?>/css/jquery-ui.css" rel="stylesheet" type="text/css" />
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
    <style type="text/css">
    	.ui-autocomplete li{
    		font-family: 'Source Sans Pro','Helvetica Neue',Helvetica,Arial,sans-serif;
    		padding: 5px 8px;
    		font-weight: bold;
    	}
    	.ui-autocomplete li:hover{
    		background-color: #3c8dbc;
    		color: white;
    	}
    </style>
     <script>
    	function printDiv(divName) {
		     var printContents = document.getElementById(divName).innerHTML;
		     var originalContents = document.body.innerHTML;

		     document.body.innerHTML = printContents;

		     window.print();

		     document.body.innerHTML = originalContents;
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
            Masters
            <small>Control panel</small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="dashboard.php"><i class="fa fa-dashboard"></i> Home </a> </li>
            <li class="active">Due Payments</li>
          </ol>
        </section>
        <!-- Main content -->
        <section class="content">
			<div class="box">
                <div class="box-header">
					<?php 
					include("../includes/notification.php"); ?>
					<div class="row">
						<div class="col-sm-8">
							<h3 class="box-title">Due Payments</h3>
						</div>
					</div>
					<hr />
					<div class="row">
						<form action="" name="search_frm" id="search_frm" method="get" class="">
							<div class="form-group col-sm-3 ui-widget">
								<label for="associate">Associate <a href="javascript:void(0);" class="text-primary" data-toggle="popover" title="Associate Search Hint" data-content="'c-' for Code Search<br>'n-' for Name Search<br>'m-' for Mobile Search<br><b>Eg:</b> If want to search 9651 in only code then Search for 'c-9651' "><i class="fa fa-info-circle"></i></a></label>
							  	<input type="text" class="form-control" id="associate" name="associate" value="<?php echo (isset($_GET['name']) && $_GET['name'] != '')?$_GET['name']:''; ?>" />
							</div>
						</form>
			            <?php if(isset($outstandings) && sizeof($outstandings) > 0){ ?>
							<div class="col-sm-8 text-right">
				            	<a class="btn btn-sm btn-success" href="due_payments.php?associate=<?php echo isset($sales_person_id)??''; ?>&action=export" data-toggle="tooltip" title="Export to Excel"><i class="fa fa-file-excel-o">&nbsp;</i>Excel Export</a>
				            </div>	
				            <div class="col-sm-1">
				            	<a class="btn btn-sm btn-danger" href="javascript:void(0);" onclick="printDiv('printContent')" data-toggle="tooltip" title="Print"><i class="fa fa-print">&nbsp;</i>PDF Export</a>
				            </div>
			            <?php
			            } ?>
			            
			        </div>
				</div><!-- /.box-header -->
                <div class="box-body no-padding" id="printContent">
				
				 <?php if($search){ 
				 	$associate = $_GET['associate'];
				 	$name = mysqli_fetch_assoc(mysqli_query($conn,"SELECT name,mobile_no from kc_associates where id = '".$associate."'"));
				 	
					?>
					<h3 style="text-align: center;">&nbsp;All Outstandings for Associate <span class="text-danger"><?php echo $name['name'].'('.$name['mobile_no'].')'; ?></span></h3>
				<?php } ?>

				 <table class="table table-striped table-hover table-bordered">
                    <tr>
                      <th>Sl No.</th>
					  <th>Customer</th>
                      <th>Block</th>
                      <th>Plot Number</th>
                      <th>Total Credeted</th>
                      <th>Total Debited</th>
                      <th>Pending Amount</th>
                      <th>Next Due Date</th>
					</tr>
					<?php
				
					if(isset($outstandings) && sizeof($outstandings) > 0 ){
						$counter = 1;
						// echo "<pre>";
						// print_r($outstandings);die;
						foreach( $outstandings as $key=>$outstanding){
							
							$customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_customers where id = '".$outstanding['customer_id']."' limit 0,1 "));
							$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_blocks where id = '".$outstanding['block_id']."' limit 0,1 "));
							$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select block_number from kc_block_numbers where id = '".$outstanding['block_number_id']."' limit 0,1 "));
							?>
							<tr>
								<td><?php echo $counter; ?></td>
								<td><?php echo $customer_details['name'].' ('.customerID($outstanding['customer_id']).')'; ?></td>
                                <td><?php echo $block_details['name']; ?></td>
                                <td><?php echo $block_number_details['block_number']; ?></td>
                                <td><h4 class="text-primary"><?php echo number_format($outstanding['total_credited'],2); ?></h4></td>
                                <td><h4 class="text-success"><?php echo number_format($outstanding['total_debited'],2); ?></h4></td>
                                <td><h4 class="text-danger"><?php echo number_format($outstanding['total_credited'] - $outstanding['total_debited'],2); ?></h4></td>
                                <td><h4 class="text-danger"><?php echo date("d M Y",strtotime($outstanding['next_due_date'])); ?></h4></td>
                            </tr>
							<?php
							$counter++;
						}
					}else{
						?>
						<tr>
							<td colspan="10" align="center"><h4 class="text-red">No Record Found</h4></td>
						</tr>
						<?php
					}
					?>
                  </table>
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
	
    <script type="text/javascript">
		$(function() {
		    $( "#associate" ).autocomplete({
				source: function( request, response ) {
			        $.ajax( {
			          url: "../dynamic/getAssociateForDuePayment.php",
			          type:"post",
			          dataType: "json",
			          data: {
			            term: request.term
			          },
			          success: function( data ) {
			            // alert(data);
			            response( data );
			          }
			        } );
			      },
				minLength: 1,
				select: function( event, ui ) {
					//log( "Selected: " + ui.item.value + " aka " + ui.item.id );
					window.location.href = 'due_payments.php?associate='+ui.item.id+'&name='+ui.item.label;
				}
	    	});
	  	});
		
		function iCheckClicked(elem){
			 var for_attr = $(elem).attr('for');
		}
		
		
	</script>
    
  </body>
</html>

