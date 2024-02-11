<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");
 if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_employees'))){ 
 	header("location:/wcc_real_estate/index.php");
 	exit();
 }
if(isset($_POST['expensesAdd'])){
	
	$expenses = filter_post($conn,$_POST['expenses_id']);
	$amount = filter_post($conn,$_POST['amount']);
	$original_amount = filter_post($conn,$_POST['original_amount']);
	$remark	 = filter_post($conn,$_POST['remark']);
	// $mobile_no = (float) filter_post($conn,$_POST['employee_mobile']);
	if($expenses == ''){
		$_SESSION['error'] = 'Employee Name was wrong!';
	}else{
		
        mysqli_query($conn,"insert into `kc_expenses` set expense_type_id = '$expenses', amount='$amount',original_amount='$original_amount',remarks ='$remark',status = '1',created_by = '".$_SESSION['login_id']."', last_login ='".date('Y-m-d H:i:s')."' ,login_id= '".$_SESSION['login_id']."'");
			
		$relation_id = mysqli_insert_id($conn);
			if($relation_id > 0){
				// mysqli_query($conn,"insert into kc_contacts set name = '$name', mobile = '$mobile_no', type = 'Employee', customer_id = '$relation_id', status = '1', created =NOW(), created_by = '".$_SESSION['login_id']."' ");
				$_SESSION['success'] = 'Expenses Successfully Added!';
				header("Location:expenses.php");
				exit();
			}else{
				$_SESSION['error'] = 'Expenses Name was wrong. Please Try Again!';
			}
		// }
	}
						
}

// if(isset($_GET['expense_type']) && is_numeric($_GET['expense_type'])){
// 	$employee_id = $_GET['expense_type'];
// 	$employee_details = mysqli_fetch_assoc(mysqli_query($conn,"select status from kc_expense_types where id = '".$employee_id."' limit 0,1 "));
// 	if(isset($employee_details['status'])){
// 		$current_status = $employee_details['status'];
// 		if($current_status == 1){
// 			$new_status = 0;
// 		}else{
// 			$new_status = 1;
// 		}
		
// 		mysqli_query($conn,"update kc_expense_types set status = '$new_status' where id = '".$employee_id."' limit 1 ");
// 		$_SESSION['success'] = 'Expense Types Status Successfully Updated!';
// 		header("Location:expenses_type.php");
// 		exit();
// 	}
// 	$_SESSION['error'] = 'Something Wrong!';
// 	header("Location:expenses_type.php");
// 	exit();
// }
if(isset($_POST['expense_edit'])){

	$expenses_name = $_POST['expenses_id'];
	$exp_id = $_POST['exp_id'];
	$amount = $_POST['amount'];
	$original_amount = $_POST['original_amount'];
	$remark = $_POST['remark'];
		if(mysqli_query($conn,"update kc_expenses set expense_type_id = '$expenses_name',amount='$amount',original_amount='$original_amount',remarks='$remark',updated_by='".$_SESSION['login_id']."', updated_at=NOW() where id = '".$exp_id ."'  ")){
            $_SESSION['success'] = 'Expense  Updated Successfully Updated!';
            header("Location:expenses.php");
            exit();
        }else{
            $_SESSION['error'] = 'Something Wrong!';
            header("Location:expenses.php");
            exit();

        }
	//}
}
if(isset($_GET['expense_delete'])){
	$expense_id = $_GET['expense_delete'];
		if(mysqli_query($conn,"update kc_expenses set deleted_by = '".$_SESSION['login_id']."', deleted_at= NOW() where id = '".$expense_id."'  ")){
            $_SESSION['success'] = 'Expense Types delete Successfully Updated!';
            header("Location:expenses.php");
            exit();
        }else{
            $_SESSION['error'] = 'Something Wrong!';
            header("Location:expenses.php");
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
            <li class="active">Expenses</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
			<div class="box">
                <div class="box-header">
					<?php 
					include("../includes/notification.php"); ?>
					<div class="col-sm-8">
						<h3 class="box-title">All Expenses</h3>
					</div>
					<?php //if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'add_employees')){ ?>
                    <div class="col-sm-4">
						<button class="btn btn-sm btn-success pull-right" data-toggle="modal" data-target="#expenses">Add Expenses</button>
					</div>
				<?php //} ?>
				</div><!-- /.box-header -->
                <div class="box-body no-padding">
				
				 <table class="table table-striped table-hover table-bordered">
                    <tr>
                      <th>Sl No.</th>
					  <th>Name</th>
					  <th>Amount</th>
					  <th>Original Amount</th>
					  <th>Remarks</th>
					  <th>Added Date</th>
                      <!-- <th>Status</th> -->
                      <?php //if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'status_employee')){ ?>
					  <th>Action</th>
					<?php //} ?>
					</tr>
					<?php
					$limit = 50;
					if(isset($_GET['page'])){
						$page = $_GET['page'];
					}else{
						$page = 1;
					}
					$total_records = mysqli_fetch_assoc(mysqli_query($conn,"select COUNT(*) as total from kc_expenses where deleted_at is null and deleted_by is null"));
					$total_pages = ceil($total_records['total']/$limit);
					
					if($page == 1){
						$start = 0;
					}else{
						$start = ($page-1)*$limit;
					}
					
					$expenses = mysqli_query($conn,"select * from kc_expenses where deleted_at is null and deleted_by is null limit $start,$limit ");
					if(mysqli_num_rows($expenses) > 0){
						$counter = 1;
						while($expense = mysqli_fetch_assoc($expenses)){ ?>
							<tr>
								<td><?php echo $counter; ?></td>
								<?php $expensesType= expensesTypeName($conn,$expense['expense_type_id']);
								?>
								<td><?php echo $expensesType; ?></td>
								<td><?php echo $expense['amount']; ?></td>
								<td><?php echo $expense['original_amount']; ?></td>
								<td><?php echo $expense['remarks']; ?></td>
                                <td><?php echo date("d M Y h:i A",strtotime($expense['created_at'])); ?></td>
								<!-- <td> -->
                                	<?php //if($expense['status'] == 1){ ?>
                                		<!-- <label class="label label-success">Active</label> -->
                                    <?php //}else{ ?>
                                    	<!-- <label class="label label-danger">Inactive</label> -->
                                    <?php// } ?>
                                <!-- </td> -->
                                 <?php //if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'status_employees')){ ?>
                                <td>
                                	<?php
									// if($expense['status']){
									// 	$button_class = 'btn-success';
									// 	$icon_class = 'fa-lock';
									// 	$btn_title = "Make Inactive";
									// }else{
									// 	$button_class = 'btn-danger';
									// 	$icon_class = 'fa-unlock';
									// 	$btn_title = "Make Active";
									// }
									?>
                                    
                                    <!-- <a href="expenses_type.php?expense_type=<?php //echo $expense['id']; ?>" data-toggle="tooltip" title="<?php// echo $btn_title; ?>">
										<button class="btn btn-xs <?php //echo $button_class; ?>" type="button"><i class="fa <?php //echo $icon_class; ?>"></i></button>
									</a> -->
                                    <a href="#" data-toggle="modal" data-target="#expenses_edit" title="Edit Expenses">
										<button class="btn btn-xs btn-success <?php //echo //$button_class; ?>" type="button" data-id="<?php echo $expense['id']; ?>" onclick="getExpenses(this)"><i class="fa fa-pencil "></i></button>
									</a>		
                                    <a href="expenses.php?expense_delete=<?php echo $expense['id']; ?>" data-toggle="tooltip" title="delete Expenses">
										<button class="btn btn-xs btn-danger <?php //echo //$button_class; ?>" type="button" data-id="<?php echo $expense['id']; ?>" onclick="if (confirm('Delete Expenses Type item?')){return true;}else{event.stopPropagation(); event.preventDefault();};"><i class="fa fa-trash "></i></button>
									</a>
                                   
                                </td>
                            <?php //} ?>
							</tr>
							<?php
							$counter++;
						}
					}else{
						?>
						<tr>
								
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
								 <li <?php if((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)){ ?>class="active"<?php } ?>><a href="expenses.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
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
	
	<div class="modal" id="expenses">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" name="add_employee_frm" id="add_employee_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add Expenses Type</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-header with-border">
						<div class="col-md-12">
							<h3 class="box-title">Add Expenses Panel</h3>
						</div>
					</div><!-- /.box-header -->
					<!-- form start -->
					<div class="box-body">
                    <div class="form-group">
						  <label for="project" class="col-sm-3 control-label">Expenses Type <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<select class="form-control" id="project" name="expenses_id"  data-validation="required">
                            	<option value="">Expenses Type</option>
                                <?php
								$expenses_types = mysqli_query($conn,"select * from kc_expense_types where status = '1' AND deleted_at is null and deleted_by is null ");
								while($expenses_type = mysqli_fetch_assoc($expenses_types)){ ?>
                                	<option value="<?php echo $expenses_type['id']; ?>"><?php echo $expenses_type['expense_name']; ?></option>
                                <?php } ?>
                            </select>
						  </div>
						</div>
                        
                        <div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">Amount</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="employee_mobile" name="amount" maxlength="255" required>
						  </div>
						</div>
                        <div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">Original Amount</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="employee_mobile" name="original_amount" maxlength="255" required>
						  </div>
						</div>
                        <div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">Remark</label>
						  <div class="col-sm-8">
							<textarea rows="5" class="form-control"cols="5" name="remark" maxlength="255" required ></textarea>
						  </div>
						</div>
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="expensesAdd">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	<div class="modal" id="expenses_edit">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" name="add_employee_frm" id="add_employee_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"> Expenses Edit</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-header with-border">
						<div class="col-md-12">
							<h3 class="box-title">Expenses Edit Panel</h3>
						</div>
					</div>
                    <div id="expensediv"></div>
                    <!-- /.box-header -->
					<!-- form start -->
					<!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="expense_edit">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
    
    
    <?php require('../includes/common-js.php'); ?>
	
    <script type="text/javascript">
	function getExpenses(elem){
        let expensesId=$(elem).data('id');
        $('#expensediv').html('');
        $.ajax({
            url:'../dynamic/expenses_edit.php',
            type:'post',
            data:{expense_id:expensesId},
            success:function(resp){
                $('#expensediv').html(resp);
            }
        })
    }
	
	</script>
    
  </body>
</html>

