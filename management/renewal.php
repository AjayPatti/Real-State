<?php
	ob_start();
	session_start();

	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/checkAuth.php");

 if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_emi_payment'))){ 
  header("location:/wcc_real_estate/index.php");
  exit();
 }


	if(isset($_GET['customer']) && $_GET['customer']!='' && isset($_GET['block']) && $_GET['block']!='' && isset($_GET['cbn']) && $_GET['cbn']!='' && isset($_GET['action']) && $_GET['action']=='true'){
		$customer = $_GET['customer'];
		$block = $_GET['block'];
		$cbn = $_GET['cbn'];
	}else{
    header("Location: customers.php");
    exit();
  }

  $url = 'renewal.php?customer='.$_GET['customer'].'&block='.$_GET['block'].'&cbn='.$_GET['cbn'].'&action=true';

  if(isset($_POST['save'])){
    //echo "<pre>"; print_r($_POST); die;
    $payable_amount = filter_post($conn,$_POST['payable_amount']);
    $down_payment = filter_post($conn,$_POST['down_payment']);
    $number_of_installment = filter_post($conn,$_POST['number_of_installment']);
    $installment_amount = filter_post($conn,$_POST['installment_amount']);
    $emi_payment_date = date("Y-m-d",strtotime(filter_post($conn,$_POST['emi_payment_date'])));
    if(!($payable_amount > 0)){
      $_SESSION['error'] = 'Total Plot Value was wrong!';
    }else if(!($down_payment > 0)){
      $_SESSION['error'] = 'Down Payment was wrong!';
    }else if(!($number_of_installment > 0) || !is_numeric($number_of_installment)){
      $_SESSION['error'] = 'Number of Installment was wrong!';
    }else if(!($installment_amount > 0)){
      $_SESSION['error'] = 'Installment Amount was wrong!';
    }else if($emi_payment_date == "1970-01-01"){
      $_SESSION['error'] = 'Installment Date was wrong!';
    }else{
      $error = false;
      mysqli_autocommit($conn,FALSE);
        
      if (!mysqli_query($conn,"insert into kc_customer_emi_hist (customer_emi_id, customer_id, block_id, block_number_id, emi_amount, paid_amount, emi_date, paid_date, created, action_type, deleted_by) select  id, customer_id, block_id, block_number_id, emi_amount, paid_amount, emi_date, paid_date, created, 'EMI Update', '".$_SESSION['login_id']."' from kc_customer_emi where  customer_id = '$customer' and block_id = '$block' and block_number_id = '$cbn';")){
        $error = true;
        echo("Error description: " . mysqli_error($conn)); die;
      }

      if (!mysqli_query($conn,"insert into kc_change_emi set customer_id = '$customer', block_id = '$block', block_number_id = '$cbn', payable_amount = '$payable_amount', down_payment = '$down_payment', number_of_installment = '$number_of_installment', installment_amount = '$installment_amount', emi_payment_date = '$emi_payment_date', created_by = '".$_SESSION['login_id']."' ")){
        $error = true;
        echo("Error description: " . mysqli_error($conn)); die;
      }

      if (!mysqli_query($conn,"delete from kc_customer_emi where customer_id = '$customer' and block_id = '$block' and block_number_id = '$cbn' ")){
        $error = true;
        echo("Error description: " . mysqli_error($conn)); die;
      }

      if(!$error){
        for($i = 0; $i < $number_of_installment; $i++){
          if($i==0){
             $emi_payment_date = date("Y-m-d",strtotime($emi_payment_date));
          }else{
             $emi_payment_date = date("Y-m-d",strtotime('+1 month',strtotime($emi_payment_date)));
          }
          if(!mysqli_query($conn,"insert into kc_customer_emi set customer_id = '$customer', block_id = '$block', block_number_id = '$cbn', emi_amount = '$installment_amount', emi_date = '$emi_payment_date', created = NOW() ")){
            $error = true;
            //echo("Error description: " . mysqli_error($conn)); die;
          }
        }

        if(!makeEMIPaid($conn,$customer,$block,$cbn)){
          $error = true;
        }
      }

      if($error){
        mysqli_rollback($conn);
        $_SESSION['error'] .= ' Something Wrong!';
      }else{
        mysqli_commit($conn);
        $_SESSION['success'] = 'EMI Successfully Updated!';
      }
      mysqli_close($conn);
      header("Location:".$url);
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

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="/<?php echo $host_name; ?>/js/html5shiv.min.js"></script>
        <script src="/<?php echo $host_name; ?>/js/respond.min.js"></script>
    <![endif]-->


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
            <li><a href="dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Renewal</li>
          </ol>
        </section>

        <!-- Main content -->
        <!-- Main content -->
        <section class="content">
			<div class="box">
          <div class="box-header">
					<?php 
					include("../includes/notification.php"); ?>
					<div class="row">
						<?php
            $renewals = mysqli_query($conn,"select * from kc_customer_emi where customer_id = '".$customer."' and block_id = '".$block."' and block_number_id = '".$cbn."' ");

            if(mysqli_num_rows($renewals) > 0){
              ?>
              <div class="col-sm-12 text-right">
                <?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'print_emi_payment')){  ?>
                <a class="btn btn-sm btn-danger" href="javascript:void(0);" onclick="printDiv('printContent')" data-toggle="tooltip" title="Print"><i class="fa fa-print"></i></a>
              <?php } if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'edit_emi_emi_payment')){  ?>
                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editEmi"><i class="fa fa-pencil"></i> Edit EMI</button>
              <?php } ?>
              </div>
            <?php
            } ?>
					</div>
				</div>
        <div class="box-body no-padding" id="printContent">
          <div class="row">
            <div class="col-sm-12">
                <span class="col-sm-6"><b>Customer:</b> <?php echo customerName($conn,$customer).' ('.customerID($customer).')'; ?></span>
                <span class="col-sm-4"><b>Block:</b> <?php echo blockName($conn,$block); ?></span>
                <span class="col-sm-2"><b>Plot Number:</b> <?php echo blockNumberName($conn,$cbn); ?></span>
            </div>
          </div>
				 <table class="table table-striped table-hover table-bordered">
          <tr>
            <th>Sl No.</th>
					  <th>EMI Amount</th>
					  <th>EMI Date</th>
					  <th>Paid Amount</th>
					  <th>Paid Date</th>
            <th>Balance</th>
            <th>Status</th>
					</tr>
					<?php
						
						if(mysqli_num_rows($renewals) > 0){
							$counter = 1;
							while($renewal = mysqli_fetch_assoc($renewals)){ ?>
							<tr>
								<td><?php echo $counter; ?></td>
								<td nowrap="nowrap"><?php echo number_format($renewal['emi_amount'],2); ?> ₹</td>
								<td nowrap="nowrap"><?php echo date("d-m-Y",strtotime($renewal['emi_date'])); ?></td>
                <td nowrap="nowrap"><?php echo number_format($renewal['paid_amount'],2); ?> ₹</td>
                <td nowrap="nowrap"><?php if($renewal['paid_date']!=NULL){ echo date("d-m-Y",strtotime($renewal['paid_date'])); } ?></td>
                <td nowrap="nowrap"><?php echo number_format($renewal['emi_amount'] - $renewal['paid_amount'],2); ?> ₹</td>
                <td nowrap="nowrap">
                	<?php if($renewal['paid_date']==NULL){ ?>
                		<label class="label label-danger">Pending</label>
                	<?php }else{ ?>
                		<label class="label label-success">Successfully Paid</label>
                	<?php } ?>
								</td>
							</tr>
							<?php
							$counter++;
						}
					}else{
						?>
						<tr>
							<td colspan="6" align="center"><h4 class="text-red">No Record Found</h4></td>
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
      <!-- Add the sidebar's background. This div must be placed immediately after the control sidebar -->
      <div class="control-sidebar-bg"></div>
    </div><!-- ./wrapper -->

    <div class="modal" id="editEmi">
      <div class="modal-dialog">
        <div class="modal-content">
          <form enctype="multipart/form-data" action="<?php echo $url; ?>" name="edit_emi_frm" id="edit_emi_frm" method="post" class="form-horizontal">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title">Change EMI</h4>
            </div>
            <div class="modal-body">
              <div class="box box-info">
                <div class="box-header with-border">
                  <div class="col-md-12">
                    <h3 class="box-title">Change EMI Panel</h3>
                  </div>
                </div><!-- /.box-header -->
                <!-- form start -->
                <div class="box-body">
                  <div class="form-group">
                    <label for="payable_amount" class="col-sm-3 control-label">Sale Amount(INR)</label>
                    <div class="col-sm-8">
                    <input type="text" class="form-control" id="payable_amount" name="payable_amount" readonly="readonly" value="<?php echo number_format(saleAmount($conn,$customer,$block,$cbn), 2, '.', ''); ?>">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="down_payment" class="col-sm-3 control-label">First Payment(INR)</label>
                    <div class="col-sm-8">
                    <input type="text" class="form-control" id="down_payment" name="down_payment" readonly="readonly" value="<?php echo number_format(downPayment($conn,$customer,$block,$cbn), 2, '.', ''); ?>">
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="number_of_installment">Number of Installment <small class="text-danger">*</small></label>
                    <div class="col-sm-8">
                      <input class="form-control number cut copy paste" maxlength="3" name="number_of_installment" placeholder="Enter Number of Installment" type="text" id="number_of_installment" autocomplete="off" data-validation="required number" data-validation-allowing="range[1;200]" onkeyup="calculateInstallmentAmount(this);" />
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="installment_amount">Installment Amount <small class="text-danger">*</small></label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control number cut copy paste" id="installmentAmt" maxlength="6" name="installment_amount" placeholder="Enter Installment Amount" autocomplete="off" data-validation="required number" readonly="readonly" data-validation-allowing="range[1;500000], float" />
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="emi_payment_date" class="col-sm-3 control-label">EMI Payment Date <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                    <input type="text" class="form-control" data-validation="required" id="emi_payment_date" name="emi_payment_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask=""  data-validation="date" data-validation-format="dd-mm-yyyy">
                    </div>
                  </div>
                </div><!-- /.box-body -->
                
              </div><!-- /.box -->
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary" id="save" name="save">Save changes</button>
            </div>
          </form>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->


  	<?php require('../includes/common-js.php'); ?>
    <script type="text/javascript">
      function calculateInstallmentAmount(elem){
        var number_of_installment = $("#number_of_installment").val();
        var payable_amount = parseFloat($("#payable_amount").val());
        if(parseFloat($("#down_payment").val()) > 0){
          payable_amount -= parseFloat($("#down_payment").val());
        }
        if(payable_amount>0 && number_of_installment>0){
          var installment_amount = (payable_amount/number_of_installment);
          $('#installmentAmt').val(installment_amount.toFixed(2));
        }else{
          $('#installmentAmt').val();
        }
      }
    </script>

  </body>
</html>

