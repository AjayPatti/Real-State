<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");
require("../includes/sendMail.php");
require("../includes/sendMessage.php");

 if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_customer'))){ 
 	header("location:/wcc_real_estate/index.php");
 	exit();
 }

$url = 'possession_transfer.php?search=Search';

$limit = 50;
if(isset($_GET['page'])){
	$page = $_GET['page'];
}else{
	$page = 1;
}

$page_url = $url.'&page='.$page;

if(isset($_GET['search_customer'])){
	$page_url .= "&search_customer=".$_GET['search_customer'];
}
if(isset($_GET['search_block_no'])){
	$page_url .= "&search_block_no=".$_GET['search_block_no'];
}
if(isset($_GET['search_project'])){
	$page_url .= "&search_project=".$_GET['search_project'];
}
if(isset($_GET['search_employee'])){
	$page_url .= "&search_employee=".$_GET['search_employee'];
}
if(isset($_GET['search_associate'])){
	$page_url .= "&search_associate=".$_GET['search_associate'];
}
if(isset($_GET['search_block'])){
	$page_url .= "&search_block=".$_GET['search_block'];
}

//echo $page_url; die;
if(isset($_POST['save'])){
	// print_r($_POST);die;
	$block = filter_post($conn,$_POST['block']);
	$customer_id = filter_post($conn,$_POST['customer_id']);
	$block_number = filter_post($conn,$_POST['block_number']);
	$registry_date = filter_post($conn,$_POST['registry_date']);
	$remarks = filter_post($conn,$_POST['transaction_remarks']);
	$project_id = filter_post($conn,$_POST['project']);
	$area = filter_post($conn,$_POST['area']);
	
	$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, project_id, name from kc_blocks where id = '".$block."' limit 0,1 "));
	$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, block_number from kc_block_numbers where id = '$block_number' limit 0,1 "));
	
	$send_message = isset($_POST['send_message'])?true:false;
	$customer_name=customerDetail($conn,$customer_id);
				// print_r($customer_name);die;
	if($customer_id == ''){
		$_SESSION['error'] = 'Customer was wrong!';
	}else if($registry_date == ''){
		$_SESSION['error'] = 'Registry date was wrong!';
	}
	

	/******* comment due to mohit sir ask on 04062020 **********/
	
	else if(!isset($block_details['id'])){
		$_SESSION['error'] = 'Block was wrong!';
	}else if(!isset($block_number_details['id'])){
		$_SESSION['error'] = 'Plot Number was wrong!';
	}else{
		// $already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_customers where name = '".$name."' and dob = '$dob' limit 0,1 "));	// or mobile = '$mobile'
		// if(isset($already_exits['id'])){
		// 	$_SESSION['error'] = 'Same Name and DOB Already Exists!';
		// }else{
			$error = false;
			mysqli_autocommit($conn,FALSE);
		
			if (!mysqli_query($conn,"insert into kc_possession_transfer set block_id = '$block ', customer_id = '$customer_id', plot_id = '$block_number',project_id='$project_id', registry_date = '$registry_date',area='$area', remark = '$remarks',  status = '1', created_at ='".date('Y-m-d H:i:s')."', created_by = '".$_SESSION['login_id']."' ")){
				$error = true;
				//echo("Error description: " . mysqli_error($conn)); die;
			}else{
				$customer_id = mysqli_insert_id($conn);
			}
				
			
			if(!$error){
				$mobile = $customer_name['mobile'];
				$name = $customer_name['name'];
				$name_title = $customer_name['name_title'];
				$already_exists = mysqli_fetch_assoc(mysqli_query($conn,"select id, type from kc_contacts where mobile = '$mobile' limit 0,1 "));
				if(!isset($already_exists['id'])){
					$name_with_title = $name_title.' '.$name;
					if (!mysqli_query($conn,"insert into kc_contacts set name = '$name_with_title', mobile = '$mobile', type = 'Possession Transfer', customer_id = '$customer_id', status = '1', created ='".date('Y-m-d H:i:s')."', created_by = '".$_SESSION['login_id']."' ")){
						$error = true;
						//echo("Error description: " . mysqli_error($conn)); die;
					}
					
				}else if($already_exists['type'] == "Contact"){
					if (!mysqli_query($conn,"update kc_contacts set type = 'Possession Transfer', customer_id = '$customer_id' where id = '".$already_exists['id']."' limit 1")){
						$error = true;
						

						//echo("Error description: " . mysqli_error($conn)); die;
					}
				}
			}

			if(!$error){
				
				mysqli_commit($conn);
				//echo "success"; die;
				$_SESSION['success'] = 'Customer Successfully Added! and Transaction Successfully Added!';
				
				$name_with_title = $name_title.' '.$name;
				if($send_message){
					$variables_array = array('variable1' => $name_with_title,'variable2'=>$block_number_details['block_number'],'variable3'=>$block_details['name'],'variable4'=>blockProjectName($conn,$block_details['id']));
					//    print_r($conn);die;
					if(sendMessage($conn,7,$mobile,$variables_array)){
						$_SESSION['success'] .= ' and Welcome Message sent Successfully!';
					}else if(!isset($_SESSION['error'])){
						$_SESSION['error'] = 'Welcome Message not sent!';
					}else if(isset($_SESSION['error'])){
						$_SESSION['error'] .= ' and Welcome Message not sent!';
					}
				}
				// die;
				//if($paid){
					// if(sendMail($email,$name_with_title,$paid_amount,$block_details['name'],$block_number_details['block_number'],$paid_date,"PaymentReceived")){
					// 	$_SESSION['success'] .= ' and Email Sent Successfully!';
					// }else{
					// 	$_SESSION['error'] = 'Email not sent!';
					// }
					
					
					if($send_message){
						$variables_array = array('variable1' => $name_with_title,'variable2'=>$paid_amount,'variable3'=>$block_details['name'],'variable4'=>$block_number_details['block_number'],'variable5'=>$paid_date);
						if(sendMessage($conn,8,$mobile,$variables_array)){
							$_SESSION['success'] .= ' and Transaction Message sent Successfully!';
						}else if(!isset($_SESSION['error'])){
							$_SESSION['error'] = 'Transaction Message not sent!';
						}else if(isset($_SESSION['error'])){
							$_SESSION['error'] .= ' and Transaction Message not sent!';
						}
					}
				//}
				header("Location:".$page_url);
				exit();
			}else{
				mysqli_rollback($conn);
				$_SESSION['error'] = 'Some Problem Occured during in storing data!';
			}	
		// }
	}				
}






if(isset($_POST['editInformation'])){
	// print_r($_POST);die;
	$block = filter_post($conn,$_POST['block']);
	$customer_id = filter_post($conn,$_POST['customer_id']);
	$block_number = filter_post($conn,$_POST['block_number']);
	$registry_date = filter_post($conn,$_POST['registry_date']);
	$remarks = filter_post($conn,$_POST['transaction_remarks']);
	$project_id = filter_post($conn,$_POST['project']);
	$area = filter_post($conn,$_POST['area']);
	$possession_id = filter_post($conn,$_POST['possession_id']);

	if(!($customer_id > 0) || !is_numeric($customer_id)){
		$_SESSION['error'] = 'Something was Really wrong!';
	}else if($block_number == ''){
		$_SESSION['error'] = 'Block Number was wrong!';
	}else if($block == ''){
		$_SESSION['error'] = 'Block Number was wrong!';
	}else if($registry_date == ''){
		$_SESSION['error'] = 'Registry date was wrong!';
	}else{
		// $already_exits = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_customers where id != '$customer_id' and mobile = '$mobile' limit 0,1 "));
		// if(isset($already_exits['id'])){
		// 	$_SESSION['error'] = 'Mobile Already Exists!';
		// }else{
			mysqli_query($conn,"update kc_possession_transfer set block_id = '$block ', customer_id = '$customer_id', plot_id = '$block_number',project_id='$project_id', registry_date = '$registry_date',area='$area', remark = '$remarks',  status = '1', updated_at ='".date('Y-m-d H:i:s')."', updated_by = '".$_SESSION['login_id']."' where id='$possession_id'");

			// $name_with_title = $name_title.' '.$name;
			// mysqli_query($conn,"update kc_contacts set name = '$name_with_title', mobile = '$mobile', created_by = '".$_SESSION['login_id']."' where customer_id = '$customer_id' limit 1 ");

			$_SESSION['success'] = 'Information Successfully Updated!';
			header("Location:".$page_url);
			exit();
		// }
		
	}
						
}

// DELETE REGISTRY CODE
// print_r($page_url);

if(isset($_GET['possession_delete'])){
	$possession_id = $_GET['possession_delete'];
	$possession_delete=mysqli_query($conn,"update kc_possession_transfer set  deleted_at ='".date('Y-m-d H:i:s')."', deleted_by = '".$_SESSION['login_id']."' where id='$possession_id'");
	if($possession_delete != false){
		$_SESSION['success'] = 'Information Successfully deleted!';
		header("Location:".$page_url);
		exit();
	}else{
		$_SESSION['error'] = 'Information not  deleted!';
		header("Location:".$page_url);
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
    <!-- jQuery UI 1.11.4 -->
    <link href="/<?php echo $host_name; ?>/plugins/jQueryUI/jquery-ui.css" rel="stylesheet" type="text/css" />
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
    <link href="/<?php echo $host_name; ?>/plugins/jvectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet"
        type="text/css" />
    <!-- Date Picker -->
    <link href="/<?php echo $host_name; ?>/plugins/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker -->
    <link href="/<?php echo $host_name; ?>/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet"
        type="text/css" />
    <!-- bootstrap wysihtml5 - text editor -->
    <link href="/<?php echo $host_name; ?>/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" rel="stylesheet"
        type="text/css" />

    <!-- Developer Css -->
    <link href="/<?php echo $host_name; ?>/css/style.css" rel="stylesheet" type="text/css" />

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="/<?php echo $host_name; ?>/js/html5shiv.min.js"></script>
        <script src="/<?php echo $host_name; ?>/js/respond.min.js"></script>
    <![endif]-->

    <style type="text/css">
    .iradio_square-blue.has-error>.form-error {
        margin-top: 25px;
    }

    .search-container {
        margin-top: 10px;
    }

    .dropdown.dropdown-lg .dropdown-menu {
        margin-top: -1px;
        padding: 6px 20px;
    }

    .input-group-btn .btn-group {
        display: flex !important;
    }

    .btn-group .btn {
        border-radius: 0;
        margin-left: -1px;
    }

    .btn-group .btn:last-child {
        border-top-right-radius: 4px;
        border-bottom-right-radius: 4px;
    }

    .btn-group .form-horizontal .btn[type="submit"] {
        border-top-left-radius: 4px;
        border-bottom-left-radius: 4px;
    }

    .form-horizontal .form-group {
        margin-left: 0;
        margin-right: 0;
    }

    .form-group .form-control:last-child {
        border-top-left-radius: 4px;
        border-bottom-left-radius: 4px;
    }

    @media screen and (min-width: 768px) {
        #adv-search {
            width: 500px;
            margin: 0 auto;
        }

        .dropdown.dropdown-lg {
            position: static !important;
        }

        .dropdown.dropdown-lg .dropdown-menu {
            min-width: 500px;
        }
    }

    /* .modal{
	overflow:auto!important;
} */
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
                    <li class="active">Possession Transfer</li>
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
                                <h3 class="box-title">All Possession Transfer</h3>
                            </div>
                            <?php //if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'add_customer')){ ?>
                            <div class="col-sm-4">
                                <button class="btn btn-sm btn-success pull-right" data-toggle="modal"
                                    data-target="#addPossessionTransfer">Add Possession Transfer</button>
                            </div>
                            <input type="hidden" id="carries">
                            <?php //} ?>
                        </div>
                        <hr />

                        <form class="" action="#" name="search_frm" id="search_frm" method="get">
                            <div class="form-group col-sm-3">
                                <label for="search_customer">Customer <a href="javascript:void(0);" class="text-primary"
                                        data-toggle="popover" title="Customer Search Hint"
                                        data-content="'c-' for Code Search<br>'n-' for Name Search<br>'m-' for Mobile Search<br><b>Eg:</b> If want to search 00942 in only code then Search for 'c-00942' <br><br> <b>OR</b> <br> <br> You can search similar name by Pressing 'Enter Key'"><i
                                            class="fa fa-info-circle"></i></a></label>

                                <input type="text" class="form-control customer-autocomplete"
                                    placeholder="Name or Code or Mobile" data-for-id="search_customer">
                                <input type="hidden" name="search_customer" id="search_customer">

							</div>
                            <button type="submit" name="search" value="Search" class="btn btn-primary"
                                style="margin-top: 24px;"><span class="glyphicon glyphicon-search"
                                    aria-hidden="true"></span></button>
                        </form>
                    </div>
                    <div class="box-body no-padding">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-bordered">
                                <tr>
                                    <th>Sl No.</th>
                                    <th>Customer Name</th>
                                    <th>Other Details</th>
                                    <th>Details</th>
                                    <?php //if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'view_customer')) {?>
                                    <th>Remarks</th>
                                    <th>Action</th>
                                    <?php //} ?>
                                </tr>
                                <?php
                        
						$query = "select * from kc_possession_transfer where  status = 1 And deleted_at is null ";
						// $query = "select * from kc_customers ";


						if(isset($_GET['search_customer']) && $_GET['search_customer'] != ''){
							//$query .= " and name LIKE '%".$_GET['search_customer']."%'";
							// if(!ctype_digit($_GET['search_customer'])){
								$query .= " and customer_id LIKE '%".$_GET['search_customer']."%'";
								
							// }
							$url .= '&search_customer='.$_GET['search_customer'];
						}
						
						$total_records = mysqli_num_rows(mysqli_query($conn,$query));
						$total_pages = ceil($total_records/$limit);
						
						if($page == 1){
							$start = 0;
						}else{
							$start = ($page-1)*$limit;
						}
						$query .= " limit $start,$limit";
						// echo $query;die;
						$customers = mysqli_query($conn,$query);
						if(mysqli_num_rows($customers) > 0){
							$counter = $start + 1;
                            // echo"<pre>";print_r($counter);die;
							while($customer = mysqli_fetch_assoc($customers)){
								// echo "<pre>";
								$blocks = mysqli_query($conn,"select id, block_id, block_number_id, installment_amount, registry, registry_date, registry_by, sales_person_id from kc_customer_blocks where customer_id = '".$customer['customer_id']."' and status = '1' ");
								$customerDetail =customerDetail($conn,$customer['customer_id']);
								// print_r($customerDetail);die;
								?>
                                <tr>

                                    <td><?php echo $counter; ?></td>
                                    <td nowrap="nowrap">
                                        <strong><?php echo $customerDetail['name_title']; ?>
                                            <?php echo $customerDetail['name'].' ('.customerID($customerDetail['id']).')'; ?></strong><br>
                                    </td>
                                    <td>
                                        Email: <strong><?php echo $customerDetail['email']; ?></strong><br>
                                        Mobile: <strong><?php echo $customerDetail['mobile']; ?></strong>
                                    </td>
                                    <td nowrap="nowrap">
                                        <?php
	                                    $block_names = array();
	                                    if(mysqli_num_rows($blocks) > 0){
											while($block = mysqli_fetch_assoc($blocks)){
												$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_blocks where id = '".$customer['block_id']."'  "));
												$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select block_number from kc_block_numbers where id = '".$customer['plot_id']."'  "));
												// print_r($block_number_details);
											

												$projects =  mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_projects where status = '1' AND id='".$customer['project_id']."' "));
												// print_r($projects['name']);die;
												?>
                                        Project Name: <strong><?php echo $projects['name']; ?></strong><br>
                                        Block: <strong><?php echo $block_details['name']; ?></strong><br>
                                        Block_number:
                                        <strong><?php echo $block_number_details['block_number']; ?></strong><br>
                                        Registry Date:
                                        <strong><?php echo date('Y-m-d',strtotime($customer['registry_date'])); ?></strong>
                                        <?php 		
										}
											}
									?>
                                    </td>
									<td><?php echo $customer['remark']; ?></td>
                                    <td nowrap="nowrap">
										<button class="btn btn-xs btn-warning" type="button" data-toggle="tooltip"
                                            title="Edit Possession's Information"
                                            onclick="editInformation(<?php echo $customer['id']; ?>);"><i
                                                class="fa fa-pencil"></i></button>	
												<a href="possession_transfer.php?possession_delete=<?php echo $customer['id']; ?>" data-toggle="tooltip" title="delete Expenses">
										<button class="btn btn-xs btn-danger <?php //echo //$button_class; ?>" type="button" data-id="<?php echo $customer['id']; ?>" onclick="if (confirm('Delete Possession Transfer are you sure?')){return true;}else{event.stopPropagation(); event.preventDefault();};"><i class="fa fa-trash "></i></button>
									</a>	
                                      
										

                                    </td>
                                </tr>
                                <?php
								$counter++;
							
						}
					}else{
							?>
                                <tr>
                                    <td colspan="9" align="center">
                                        <h4 class="text-red">No Record Found</h4>
                                    </td>
                                </tr>
                                <?php
						}
					
						?>
                            </table>
                        </div>
                    </div><!-- /.box-body -->

                    <?php if($total_pages > 1){ ?>
                    <div class="box-footer clearfix">
                        <ul class="pagination pagination-sm no-margin pull-right">

                            <?php
							for($i = 1; $i <= $total_pages; $i++){
								?>
                            <li <?php if((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)){ ?>class="active"
                                <?php } ?>><a href="<?php echo $url ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
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



    <div class="modal" id="addPossessionTransfer">
        <div class="modal-dialog">
            <div class="modal-content" id="addCustomer_tb">
                <form action="#" name="add_customer_frm" id="add_customer_frm" method="post" class="form-horizontal">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Add Possession Transfer</h4>
                    </div>
                    <div class="modal-body">
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <div class="col-md-12">
                                    <h3 class="box-title">Add Possession Transfer Panel</h3>
                                </div>
                            </div><!-- /.box-header -->
                            <!-- form start -->
                            <div class="box-body">

                                <div class="form-group">
                                    <label for="sales_person" class="col-sm-3 control-label">Customer Name<span
                                            class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control customer-autocomplete"
                                            data-for-id="associate" placeholder="Customer Name"
                                            data-validation="required">
                                        <input type="hidden" name="customer_id" id="customer_id">

                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="project" class="col-sm-3 control-label">Project <span
                                            class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                        <select class="form-control" id="project" name="project"
                                            onChange="getBlocks(this.value);" data-validation="required">
                                            <option value="">Select Project</option>
                                            <?php
								$projects = mysqli_query($conn,"select * from kc_projects where status = '1' ");
								while($project = mysqli_fetch_assoc($projects)){ ?>
                                            <option value="<?php echo $project['id']; ?>">
                                                <?php echo $project['name']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="block" class="col-sm-3 control-label">Block <span
                                            class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                        <select class="form-control block" id="block" name="block"
                                            onChange="getBlockNumbers(this.value);" data-validation="required">
                                            <option value="">Select Block</option>
                                            <?php
								/*$blocks = mysqli_query($conn,"select * from kc_blocks where status = '1' ");
								while($block = mysqli_fetch_assoc($blocks)){ ?>
                                            <option value="<?php echo $block['id']; ?>"><?php echo $block['name']; ?>
                                            </option>
                                            <?php }*/ ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="block_number" class="col-sm-3 control-label">Plot Number <span
                                            class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                        <select class="form-control block_number" id="block_number" name="block_number"
                                            onChange="blockNumberChanged(this);" data-validation="required">
                                            <option value="">Select Plot Number</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="area" class="col-sm-3 control-label">Total Area <span
                                            class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="text" name="area" class="form-control area" id="area" readonly
                                            data-validation="required">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="customer_paid_date" class="col-sm-3 control-label"><span
                                            class="cheque_dd_label">Registry</span> Date</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="customer_paid_date"
                                            name="registry_date" data-inputmask="'alias': 'yyyy-mm-dd'" data-mask=""
                                            data-validation="date" data-validation-format="yyyy-mm-dd"
                                            data-validation-depends-on="payment_type">
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label for="transaction_remarks" class="col-sm-3 control-label">Remarks</label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control" id="transaction_remarks"
                                            name="transaction_remarks"></textarea>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="send_message" class="col-sm-3 control-label">Send Message</label>
                                    <div class="col-sm-8">
                                        <input type="checkbox" name="send_message" id="send_message"
                                            class="form-control" />
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
    <div class="modal" id="editInformation">
        <div class="modal-dialog">
            <div class="modal-content">
                <form enctype="multipart/form-data" action="#" name="edit_frm" id="edit_frm"
                    method="post" class="form-horizontal dropzone">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Edit Customer Information</h4>
                    </div>
                    <div class="modal-body">
                        <div class="box box-info">
                            <div class="box-body" id="edit-information-container">

                            </div><!-- /.box-body -->

                        </div><!-- /.box -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="save" name="editInformation">Save
                            changes</button>
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <?php require('../includes/common-js.php'); ?>

    <script type="text/javascript">
    function getExpenses(elem) {
        let expensesId = $(elem).data('id');
        $('#expensediv').html('');
        $.ajax({
            url: '../dynamic/expenses_edit.php',
            type: 'post',
            data: {
                expense_id: expensesId
            },
            success: function(resp) {
                $('#expensediv').html(resp);
            }
        })
    }

    function getBlocks(project) {
		console.log(project);
        $("#area").val('');
        $("#rate").val('');
        $("#plc").val('');
        $("#block_number").val('');
        $.ajax({
            url: '../dynamic/getBlocks.php',
            type: 'post',
            data: {
                project: project
            },
            success: function(resp) {
                $(".block").html(resp);
            }
        });
    }

    function getBlockNumbers(block) {
        $("#area").val('');
        $("#rate").val('');
        $("#plc").val('');
        $("#payable_amount").val('');
        $.ajax({
            url: '../dynamic/getBlockNumbers.php',
            type: 'post',
            data: {
                block: block
            },
            success: function(resp) {
                $(".block_number").html(resp);
            }
        });
    }

    function blockNumberChanged(elem) {
        var block_number = $(elem).val();
        $("#area").val('');
        // $("#plc").select2("val", []);
        if (block_number != '' && !isNaN(block_number)) {
            $.ajax({
                url: '../dynamic/getBlockNumberDetailsJson.php',
                type: 'post',
                data: {
                    block_number: block_number
                },
                dataType: "json",
                success: function(resp) {
                    //alert(resp.area);
                    $(".area").val(resp.area);
                    // if(jQuery.isArray(resp.plc)){
                    // 	$("#plc").select2("val", resp.plc);
                    // }
                }
            });
        }
    }
	function editInformation(customer){
		$.ajax({
			url: '../dynamic/possession_transfer_edit.php',
			type:'post',
			data:{customer:customer},
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
    </script>
</body>

</html>