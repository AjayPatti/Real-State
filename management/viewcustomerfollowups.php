<?php
	ob_start();
	session_start();

	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/checkAuth.php");

 if(!((userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_reminder')) || (userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_due_amount')))){ 
  header("location:/wcc_real_estate/index.php");
  exit();
 }
 $cus_id = isset($_GET['customer_id']) ? $_GET['customer_id']:'';
 $block_id = isset($_GET['block_id']) ?$_GET['block_id']:'';
 $block_number_id =    isset($_GET['block_number_id']) ?$_GET['block_number_id']:'';
 
 $url = 'viewcustomerfollowups.php?customer_id='.$cus_id.'&&block_id='.$block_id.'&&block_number_id='.$block_number_id.'';


//   $cus_id = isset($_POST['customer'])?$_POST['customer']:'';
//   $block_id = isset($_POST['block'])?$_POST['block']:'';
//   $block_number_id = isset($_POST['blocknumber'])?$_POST['blocknumber']:'';

// if(isset($_POST)){  $cus_id = ($_POST['customer'])?$_POST['customer']:'';
//   $block_id = ($_POST['block'])?$_POST['block']:'';
//   $block_number_id = ($_POST['block_number'])?$_POST['block_number']:'';
// }
  $query1 = "select cfu.*,cb.id,cb.customer_id,cb.block_id,cb.block_number_id,cb.associate from kc_customer_follow_ups cfu left join kc_customer_blocks cb ON cb.customer_id=cfu.customer_id AND cb.block_id=cfu.block_id AND cb.block_number_id=cfu.block_number_id WHERE cfu.status = 0 and cfu.customer_id = '".$cus_id."' and cfu.block_id = '".$block_id."' and cfu.block_number_id = '".$block_number_id."' ";

  $customer_follow_up = mysqli_query($conn,$query1);
  $takethis = mysqli_fetch_assoc($customer_follow_up);
//   var_dump($customer_follow_up);

if(isset($_POST['saveFollowup'])){
    // echo "<pre>"; print_r($_POST); die;
    $customer_id = $_POST['customer'];
    $block_id = $_POST['block'];
    $block_number_id = $_POST['block_number'];
    $remarks = $_POST['remark'];
    $date = date("Y-m-d", strtotime($_POST['date']));


    if($remarks == ''){
        $_SESSION['error'] = 'Remarks required!';
    }else if($date == '' || $date == '1970-01-01'){
        $_SESSION['error'] = 'Next Follow Up Date was wrong!';
    }else{
        // $customer_followup_detail = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_customer_follow_ups where customer_id = '".$customer_id."' and block_id = '".$block_id."' and block_number_id = '".$block_number_id."' limit 0,1 "));
//
        $error = false;
        mysqli_autocommit($conn,FALSE);

        $nextDueDate = nextDueDate($conn,$customer_id,$block_id,$block_number_id);
        $pending_amount = totalCredited($conn,$customer_id,$block_id,$block_number_id) - totalDebited($conn,$customer_id,$block_id,$block_number_id);
        if (!mysqli_query($conn , "INSERT INTO kc_customer_follow_ups_hist (customer_id, block_id, block_number_id, pending_amount, next_due_date, next_follow_up_date, remarks, created_by, created_at) VALUES ( '".$customer_id."' , '".$block_id."', '".$block_number_id."', '".$pending_amount."', '".$nextDueDate."', '".$date."', '".$remarks."', '".$_SESSION['login_id']."', '".date("Y-m-d H:i:s")."' )")){
            $error = true;
            echo("Error description: " . mysqli_error($conn)); die;
        }

        if(!$error && !mysqli_query($conn , "UPDATE kc_customer_follow_ups SET next_follow_up_date = '".$date."', remarks = '".$remarks."', updated_by = '".$_SESSION['login_id']."', updated_at = '".date("Y-m-d H:i:s")."' WHERE  customer_id = '".$customer_id."' and block_id = '".$block_id."' and block_number_id = '".$block_number_id."' ")){
            $error = true;
            echo("Error description: " . mysqli_error($conn)); die;
        }

        if(!$error){
            mysqli_commit($conn);
            $_SESSION['success'] = 'Followup Updated';
                header("Location:".$url);
                
                exit();
        }else{
            mysqli_rollback($conn);
            $_SESSION['error'] = 'Some Problem Occured during in storing data!';
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

  
     </head>
  <body class="skin-blue sidebar-mini">
    <div class="wrapper">

      <?php require('../includes/header.php'); ?>
      <!-- Left side column. contains the logo and sidebar -->
      <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <?php  require('../includes/left_sidebar.php'); ?>
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
            <li><a href="dashboard.php"><i class="fa fa-dashboard"></i>Home</a></li>
            <li class="active">View Follow Ups</li>
          </ol>
        </section>
         <section class="content">
         	<div class="box">

        	<div class="box-header">
        		<div class="row">

        			<div class="col-sm-3">
						<h3 class="box-title">Follow Ups</h3>
					</div>
                    <div class="col-sm-9 " id="successdiv">
				    </div>
        			<div class="col-sm-7">
                    <?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'customer_follow_ups_due_amount')){ ?>
	                    <button class="btn btn-xs btn-warning" type="button" data-toggle="tooltip"     title="Followup" onclick = "followUp(<?php echo $cus_id; ?>,<?php echo $block_id; ?>,<?php echo $block_number_id; ?>);">
                       Add
                        </button>
	                <?php } ?>
					</div>
                                             
				</div>
				<hr/>
			<div class="box-body no-padding">
                	<div class="table-responsive">
					 <table class="table table-striped table-hover table-bordered">
	                    <tr>
	                      <th>SNo.</th>
						  <th>Details</th>
						  <th>Project Details</th>
						  <th>Payment Type</th>
						  <th>Pending Amount</th>
						  <th>Next Due Date</th>
						  <th>Next Follows Date</th>
						  <th>Remarks</th>
						  
						  
						</tr>
					<?php 
					
					$customer_follow_ups = mysqli_query($conn , "SELECT * FROM kc_customer_follow_ups_hist WHERE customer_id = '".$cus_id."' AND block_id = '".$block_id."' AND block_number_id = '".$block_number_id."' ORDER BY id DESC");
					$counter = 1 ;
						while($customer_follow_up =mysqli_fetch_assoc($customer_follow_ups)){
							//print_r($customer_follow_up);
							$blocks = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_blocks where id = '".$customer_follow_up['block_id']."' and status = '1' "));
							$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select block_number from kc_block_numbers where block_id = '".$blocks['id']."' AND id =  '".$customer_follow_up['block_number_id']."' limit 0,1 "));
							//print_r($block_number_details);die;
							$customer = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_customers where id = '".$customer_follow_up['customer_id']."'"));
							$payment_type = 	mysqli_fetch_assoc(mysqli_query($conn, "SELECT customer_payment_type FROM kc_customer_blocks WHERE customer_id = '".$customer_follow_up['customer_id']."' AND block_id = '".$customer_follow_up['block_id']."' AND block_number_id = '".$customer_follow_up['block_number_id']."' " ));
							$emi = nextEMIDetails($conn , $customer_follow_up['customer_id'] , $customer_follow_up['block_id'] , $customer_follow_up['block_number_id']);
							$part_amc = getPartAmount($conn , $customer_follow_up['customer_id'] , $customer_follow_up['block_id'] , $customer_follow_up['block_number_id'] );

							$userType = mysqli_fetch_assoc(mysqli_query($conn ,"SELECT name FROM kc_login WHERE id = '".$customer_follow_up['created_by']."' "));
							 //print_r($customer_follow_up);die;
						
					?>
					<tr>
						<td nowrap="nowrap"><?= $counter."." ;?></td>
						<td nowrap="nowrap">
										<strong><?php echo $customer['name_title']; ?> <?php echo $customer['name'].' ('.customerID($customer['id']).')'; ?></strong><br>
	                                    <strong><?php echo $customer['parent_name_relation']; ?></strong> <?php if($customer['parent_name'] != ''){ ?>of <strong><?php echo isset($customer['parent_name_sub_title'])?$customer['parent_name_sub_title']:''; ?> <?php echo $customer['parent_name']; } ?></strong><br>
	                                    <?php if($customer['nominee_name'] != ''){ ?>
	                                    	Co-owner: <strong class="text-danger"><?php echo $customer['nominee_name']; ?></strong>
	                                    	<?php if($customer['nominee_relation'] != ''){ ?>
		                                    	<strong class="text-danger">(<?php echo $customer['nominee_relation']; ?>)</strong>
		                                    <?php } ?>
		                                    <br> Mobile: <strong><?php echo $customer['mobile']; ?></strong>
	                                    <?php } ?>
	                                </td>
	                                 <td nowrap="nowrap">
	                                	<?php 
	                                	//print_r($customer_follow_up['block_id']);

	                                	 echo '<h5 class="text-success">'.blockProjectName($conn,$customer_follow_up['block_id']).'<br>'.$blocks['name'].'('.$block_number_details['block_number'].')'."</h5>";
	                                	 
	                                	?>
	                                </td>
	                                <td nowrap="nowrap">
	                                	<?php if ($payment_type['customer_payment_type'] == 'EMI'){
	                                	echo '<strong class="text-warning"> EMI </strong><br>'; 
	                                	}elseif($payment_type['customer_payment_type'] == 'Part'){
	                                			echo '<strong class="text-warning"> Part </strong><br>';
	                                	}
	                                	?>
	                                </td>
	                                <td nowrap="nowrap">
	                                	<?php 
	                                	if($payment_type['customer_payment_type'] == 'EMI'){
											if($emi){
												echo '<strong class="text-warning"> '.$emi['emi_amount'].' ₹</strong><br>';
											}else{
												echo "No due EMI";											
											}
	                                	}
	                                	else{
	                                		echo '<strong class="text-warning"> '.$customer_follow_up['pending_amount'].' ₹</strong><br>';
	                                	}
	                                	?>
	                                </td>
	                                <td nowrap="nowrap">
	                                	<?php  

											echo '<strong class="text-info">'.date("d-m-Y",strtotime($customer_follow_up['next_due_date'])).'</strong>';
										
	                                	?>
	                                		
	                                	</td>
	                                	<td nowrap="nowrap">
	                                	<?php  
	                                		echo '<strong class="text-info">'.date("d-m-Y",strtotime($customer_follow_up['next_follow_up_date'])).'</strong>';
	                                	?>
	                                		
	                                	</td>
	                                <td>
										<?= $customer_follow_up['remarks']; ?>
										<br>
										by <b><?php echo isset($userType['name'])?$userType['name']:''; ?></b>
										<br>
										on <b><?php echo isset($customer_follow_up['created_at'])?date("d-m-Y",strtotime($customer_follow_up['created_at'])):''; ?></b>
									</td>
					</tr>
				<?php $counter ++; } ?>
			</table>
				</div>
			</div>
		</div>
		</section>
      </div>
	</div>



    
	<div class="modal" id="actionFollowUp">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="#"  id="followUp_form" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Follow Up</h4>
			  </div>
			  <div class="modal-body">
					<div class="box-body" id="edit-information-container">
						<input type="hidden" name="customer" id="followup_customer_id">
						<input type="hidden" name="block" id="followup_block_id">
						<input type="hidden" name="block_number" id="followup_block_number_id">
						<div class="form-group">
							<label class="col-sm-3 control-label" for="exampleInputEmail1">Remark</label>
							    <div class="col-md-7">
								    <input type="text" class="form-control remark" id="remark" name="remark" data-validation="required">
								    <span id="span1"></span>
								</div>
						</div>
						<div class="form-group">
						    <label class="col-sm-3 control-label" for="exampleInputEmail1">Next Follow Up Date</label>
							    <div class="col-md-7">
								    <input type="text" class="form-control date" id="date" name="date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask=""  data-validation="date" data-validation-format="dd-mm-yyyy">
								</div>
						</div>
			    			
					</div>
					
				</div><!-- /.box -->
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="saveFollowup">Save</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	<?php require('../includes/common-js.php'); ?>
    <script>

function followUp(customer, block, block_number)
		{
			//alert(id+" "+cid);
			$("#followup_customer_id").val(customer);
			$("#followup_block_id").val(block);
			$("#followup_block_number_id").val(block_number);
			$("#actionFollowUp").modal('show');
			
		}

    </script>
    </body>
</html>
 	
