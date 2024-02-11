<?php
	ob_start();
	session_start();

	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/checkAuth.php");
	if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'view_visit_forms'))){
	header("location:/wcc_real_estate/index.php");
	exit();
	}
	$url = 'visit_forms.php?search=Search';

	$limit = 50;
	if(isset($_GET['page'])){
		$page = $_GET['page'];
	}else{
		$page = 1;
	}

	$page_url = $url.'&page='.$page;

	$search = false;
	$search_project_url_string = $search_associate_url_string = '';
	$query = "select * from kc_visit_forms where deleted_at IS Null";
	if(isset($_GET['datesearch']) && $_GET['datesearch']!=''){
		$ddatesearch = explode('-',$_GET['datesearch']);
		
		$startdate = date('Y-m-d',strtotime($ddatesearch[0]));
		$enddate = date('Y-m-d',strtotime($ddatesearch[1]));
		$query .= " And visit_datetime between '$startdate' and '$enddate' ";
	}	
	$total_records = mysqli_num_rows(mysqli_query($conn,$query));
	$total_pages = ceil($total_records/$limit);
	
	if($page == 1){
		$start = 0;
	}else{
		$start = ($page-1)*$limit;
	}

	//$query .= " order by registry_date desc limit $start,$limit";
	$query .= " limit $start,$limit";
	// echo $query; die;
	$visits = mysqli_query($conn,$query);
	$search = true;
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>WCC | Admin Panel</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport"><!-- jQuery UI 1.11.4 -->
    <link href="/<?php echo $host_name; ?>/plugins/jQueryUI/jquery-ui.css" rel="stylesheet" type="text/css" />
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
            <li class="active">Visit Forms</li>
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
							<h3 class="box-title">All Visit Forms</h3>
						</div>
						<?php if($search && mysqli_num_rows($visits) > 0){ ?>
							<div class="col-sm-4">
								<a href="visit_form_excel_export.php?<?php if(isset($_GET['datesearch'])  && $_GET['datesearch']!='' ) echo 'datesearch='.$_GET['datesearch'];?>&search=Search" class="btn btn-sm btn-success pull-right"><i class="fa fa-file-excel-o"></i> Excel Export</a>
							</div>							
						<?php } ?>
					</div>
					<div class="alert-container" style="display:none">
					<div class="alert alert-success alert-dismissable">
						<h5>Visit Successfully done </h5>
					</div>
					</div>
					<hr>
					<div class="row">
							<form action="" name="search_frm" id="search_frm" method="get" class="">
							<div class="form-group col-md-2 text-center">
								<label for="search_associate" class="col-md-12 text-left" style="padding-left:0 !important" for="datepick">Visit Date </label>
								<div><input type="text" class="form-control" placeholder="Date" id="datepick" name="datesearch" readonly=""></div>
							</div>
							<div class="col-md-5">
								<input type="submit" name="search" value="Search" class="btn btn-sm btn-primary" style="margin-top: 25px;">
								<?php if(isset($_GET['datesearch'])&& $_GET['datesearch']!=''){ ?>
									<a href="<?php echo $page_url; ?>" class="btn btn-sm btn-danger" style="margin-top: 25px;"><i class="fa fa-eraser"></i></a>
								<?php } ?>
							</div>
						</form>
						<div class="col-md-5">
							<button class="btn btn-sm btn-success pull-right" data-toggle="modal" style="margin-left: 13px;" data-target="#addAssociate">Add Visit</button>
						</div>
					</div>

				</div><!-- /.box-header -->
			
				
                <div class="box-body no-padding">
				
				 <table class="table table-striped table-hover table-bordered">
                    <tr>
                      <th>Sl No.</th>
					  <th>Name</th>
					  <th>Mobile</th>
					  <th>Visit Date</th>
					  <th>Associate</th>
					  <th>Project</th>
					  <!-- <th>Sector</th> -->
					  <th>Added Date</th>
					</tr>
					<?php
					
					if($search && mysqli_num_rows($visits) > 0){
						$counter = $start + 1;
						while($visit = mysqli_fetch_assoc($visits)){
							if($visit['project_id'] > 0){
								$detail = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_projects where id = '".$visit['project_id']."' and status = '1' "));
								$project_name = isset($detail['name'])?$detail['name']:'';
							}else{
								$project_name = 'NA';
							}
							if($visit['block_id'] > 0){
								$block_name = blockName($conn,$visit['block_id']);
							}else{
								$block_name = 'NA';
							}
							?>
							<tr>
								<td><?php echo $counter; ?></td>
								<td><?php echo $visit['name']; ?></td>
								<td><?php echo $visit['mobile']; ?></td>
                                
                                <td><?php echo date("d M Y h:i A",strtotime($visit['visit_datetime'])); ?></td>
                                
								<td><?php echo ($visit['associate_id'] > 0)?associateName($conn,$visit['associate_id']):''; ?></td>

								<td><?php echo $project_name; ?></td>
								<!-- <td><?php //echo $block_name; ?></td> -->
                                
                                <td><?php echo date("d M Y h:i A",strtotime($visit['addedon'])); ?></td>
                                <td><button class="btn btn-xs btn-warning" type="button" data-toggle="tooltip" title="Edit Associate's Information" onclick = "editInformation(this);" data-id="<?php echo $visit['id']; ?>"><i class="fa fa-pencil"></i></button>
								<?php if($_SESSION['login_type'] =='super_admin'){ ?>							
                               <button class="btn btn-xs btn-danger" type="button" data-toggle="tooltip" title="Delete Visit's Information" onclick = "deleteInformation(this);" data-id="<?php echo $visit['id']; ?>"><i class="fa fa-trash"></i></button>
							<?php }?>
							</td>
							</tr>
							<?php
							$counter++;
						}
					}else{
						?>
						<tr>
							<td colspan="8" align="center"><h4 class="text-red">No Record Found</h4></td>
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
								 <li <?php if((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)){ ?>class="active"<?php } ?>><a href="<?php echo $url; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
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
	
	<div class="modal" id="addAssociate">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" name="add_associate_visit" id="add_associate_visit" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add Visits</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-header with-border">
						<div class="col-md-12">
							<h3 class="box-title">Add Visits Panel</h3>
						</div>
					</div><!-- /.box-header -->
					<!-- form start -->
					<div class="box-body">
						<div class="form-group">
						  <label for="code" class="col-sm-3 control-label">Name</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="code" name="name" maxlength="255" required>
						  </div>
						</div>

						<div class="form-group">
						  <label for="name" class="col-sm-3 control-label">Mobile</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="name" name="mobile" maxlength="255" required>
						  </div>
						</div>
                        
                        <div class="form-group">
						  <label for="mobile" class="col-sm-3 control-label">Visit Date</label>
						  <div class="col-sm-8">
						
							<input type="text" class="form-control" id="visit_date" name="visit_date" maxlength="255" data-inputmask="'alias': 'yyyy-mm-dd'" data-mask="" required>
						  </div>
						</div>
                        <div class="form-group">
						  <label for="mobile" class="col-sm-3 control-label">Associate</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control  visit-forms-autocomplete" id="visit-forms-autocomplete" data-for-id="search_associate" name="associate_name" maxlength="255" required>
							<input type="hidden" name="associate_id" id="associate_id" class="associate_id">
						  </div>
						</div>
                        <div class="form-group">
						  <label for="mobile" class="col-sm-3 control-label">Project</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control visit-forms-autocomplete-project" id="project" data-for-id="search_associate"  name="project" maxlength="255" required>
							<input type="hidden" name="project_id" id="project_id" class="project_id">  
						</div>
						</div>
                        <!-- <div class="form-group">
						  <label for="mobile" class="col-sm-3 control-label">Send Message</label>
						  <div class="col-sm-8">
						  <input type="checkbox" name="send_message" id="send_message" class="form-control" />
						  </div>
						</div> -->
							<div class="form-group">
							<label for="send_message" class="col-sm-3 control-label">Send Message</label>
								<div class="col-sm-8">
									<input type="checkbox" name="send_message" id="send_message" class="form-control" />
								</div>
							</div>
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="addAssociate">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div>

	<div class="modal" id="addTransaction">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="" name="add_transaction_frm" id="add_transaction_frm" method="post" class="form-horizontal dropzone" onSubmit="return confirm('Are you sure All Details are correctly Filled?');">
			  

			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

    
    
    <div class="modal" id="viewTransaction">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="visit_edit.php" name="view_transaction_frm" id="view_transaction_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">All Transactions</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-body">
						
                        <table class="table table-bordered" id="view-transaction-container">
                        </table>
                        
                        
                        
                        
						
					</div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	<div class="modal" id="editInformation">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="../dynamic/visit_edit.php" name="edit_frm" id="edit_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Edit Visit Information</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-body" id="edit-information-container">
					
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="save" name="editInformation">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	<div class="modal" id="cancelTransaction">
	  <div class="modal-dialog">
		<div class="modal-content">
			<form enctype="multipart/form-data" action="" name="add_late_payment_frm" id="add_late_payment_frm" method="post" class="form-horizontal dropzone">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Cancel Transaction</h4>
			  </div>
			  <div class="modal-body">
				<div class="box box-info">
					<div class="box-header with-border">
						<div class="col-md-12">
							<h3 class="box-title">Cancel Transaction Panel</h3>
						</div>
					</div><!-- /.box-header -->
					<!-- form start -->
					<div class="box-body">
						<div class="form-group">
						  <label for="cancel_remarks" class="col-sm-3 control-label">Remarks</label>
						  <div class="col-sm-8">
							<textarea class="form-control" id="cancel_remarks" name="cancel_remarks"></textarea>
							<input type="hidden" name="cancel_transaction_id" id="cancel_transaction_id">
							<input type="hidden" name="cancel_customer_id" id="cancel_customer_id">
                            <input type="hidden" name="cancel_block_id" id="cancel_block_id">
                            <input type="hidden" name="cancel_block_number_id" id="cancel_block_number_id">
						  </div>
						</div>
                    </div><!-- /.box-body -->
					
				</div><!-- /.box -->
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
				<button type="button" id="cancelTransactionBack" class="btn btn-info">Back</button>
				<button type="submit" class="btn btn-primary" id="cancelTransactionBtn" name="cancelTransaction">Save changes</button>
			  </div>
			</form>
		</div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
    

    <script type="text/javascript">
    	function addTransaction(associate){
    		$.ajax({
				url: '../dynamic/addAssociateTransactions.php',
				type:'post',
				data:{associate:associate},
				success: function(resp){
					$("#add_transaction_frm").html(resp);
					$("[data-mask]").inputmask();
					$("#addTransaction").modal('show');
				}
			});
		}

		function getCustomerId(elem){
			var customerId = $(elem).find(":selected").attr('for');
			$("#customer_id").val(customerId);
		}

		function getTransactions(associate){
			$.ajax({
				url: '../dynamic/getAssociateTransactions.php',
				type:'post',
				data:{associate:associate},
				success: function(resp){
					$("#view-transaction-container").html(resp);
					$("#viewTransaction").modal('show');
				}
			});
		}

		function paymentTypeChanged(elem){
			if($(elem).val() == "Cheque" || $(elem).val() == "DD" || $(elem).val() == "NEFT" || $(elem).val() == "RTGS"){
				$(".cheque_dd").show();
				$(elem).parent().parent().parent().find('.cheque_dd_label').text($(elem).val());
			}else{
				$(".cheque_dd").hide();
				$(elem).parent().parent().parent().find('.cheque_dd_label').text('Paid');
			}
		}

		$(function(){
	    	$("#cancelTransactionBack").click(function(){
	    		$("#cancelTransaction").modal('hide');
	    		getTransactions($("#cancel_customer_id").val(),$("#cancel_block_id").val(),$("#cancel_block_number_id").val());
	    	});
	    });

	    function cancelTransaction(transaction,customer,block,block_number){
	    	if(confirm('Are you sure you want to cancel this transaction?')){
	    		$("#cancel_transaction_id").val(transaction);
	    		$("#cancel_customer_id").val(customer);
				$("#cancel_block_id").val(block);
				$("#cancel_block_number_id").val(block_number);
	    		$("#viewTransaction").modal('hide');
	    		$("#cancelTransaction").modal('show');
	    	}
	    }

	    function editInformation(elem){
			associateID =$(elem).data('id');
			$.ajax({
				url: '../dynamic/getVisitInformation.php',
				type:'post',
				data:{associateID:associateID},
				success: function(resp){
					$("#edit-information-container").html(resp);
					$("[data-mask]").inputmask();
					$('input').iCheck({
						  checkboxClass: 'icheckbox_square-blue',
						  radioClass: 'iradio_square-blue',
						  click: function(){
							}
						});
					$("#editInformation").modal('show');
					
				}
			});
		}
		function deleteInformation(elem){
			if(confirm("Are you sure?") == true){
				visitId =$(elem).data('id');
				$.ajax({
					url:'../dynamic/visit_edit.php',
					type:'post',
					data:{visitid:visitId,delete:'delete'},
					success:function(resp){
						location.reload();
					},
					error:function(resp){
	
					}
				})

			}

		}
    </script>
    
    <?php require('../includes/common-js.php'); ?>
	
    <script>
		$(document).ready(function(){
			var start = "{{ date('d-m-Y',strtotime($startdate)) }}";
			var end = "{{ date('d-m-Y',strtotime($enddate)) }}";
		
			$('input[name="datesearch"]').daterangepicker({
			
			ranges: {
				'Today': [moment(), moment()],
				'Tomorrow': [moment().add(1, 'days'), moment().add(1, 'days')],
				'Next 7 Days': [moment(), moment().add(6, 'days')],
				
				'This Month': [moment().startOf('month'), moment().endOf('month')],
					
				}

			});

		$('#add_associate_visit').on('submit',function(e){
			formData = $(this).serialize();
			e.preventDefault();
			$.ajax({
				url:'../dynamic/visitAssociates.php',
				type:'post',
				data:formData,
				success:function(resp){
					location.reload();
				},
				error:function(resp){

				}
			})

		})	

	})

	</script>
   
  </body>
</html>

