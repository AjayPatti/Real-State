<?php
ob_start();
session_start();

error_reporting(E_ERROR | E_PARSE);

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");
if (!(userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_refund_plot_list'))) {
    header("location:/wcc_real_estate/index.php");
    exit();
}

$url = 'refund_plot_list.php?search=Search';
$limit = 100;

if (isset($_GET['page'])) {
    $page = $_GET['page'];
} else {
    $page = 1;
}

$page_url = $url . '&page=' . $page;

$search = false;
$search_project_url_string = $search_associate_url_string = '';

$query =  "select cbh.id as customer_block_id,cbh.id,cbh.deleted, cbh.customer_id,cbh.batch as batch, cbh.block_id, cbh.block_number_id, cbh.registry, cbh.registry_date, cbh.registry_by, cbh.associate,b.project_id, b.name as block_name, bn.block_number as block_number_name, bn.area, p.name as project_name, c.name_title as customer_name_title, c.name as customer_name, c.mobile as customer_mobile, c.address as customer_address,a.code as associate_code, a.name as associate_name from kc_customer_blocks_hist cbh LEFT JOIN kc_blocks b ON cbh.block_id = b.id LEFT JOIN kc_block_numbers bn ON cbh.block_number_id = bn.id LEFT JOIN kc_projects p ON b.project_id = p.id LEFT JOIN kc_customers c ON cbh.customer_id = c.id LEFT JOIN kc_associates a ON cbh.associate = a.id where cbh.status = '1' AND cbh.action_type = 'Cancel Booking'";
// echo $query;die;
if (isset($_GET['search_project']) || isset($_GET['search_customer']) || isset($_GET['search_block']) || isset($_GET['search_block_no']) || ((isset($_GET['search_block_no'])  && $_GET['search_block_no'] > 0)) || (isset($_GET['search_associate']) && (int) $_GET['search_associate'] > 0)) {

    if (isset($_GET['search_customer']) && $_GET['search_customer'] != '') {
        $query .= " and c.id = '" . $_GET['search_customer'] . "' ";
        $url .= '&search_customer=' . $_GET['search_customer'];
        $page_url .= '&search_customer=' . $_GET['search_customer'];
    }
    if (isset($_GET['search_block']) && $_GET['search_block'] != '') {
        $query .= " and cbh.block_id = '" . $_GET['search_block'] . "' ";
        $url .= '&search_block=' . $_GET['search_block'];
        $page_url .= '&search_block=' . $_GET['search_block'];
    }
    if (isset($_GET['search_block_no']) && $_GET['search_block_no'] != '') {
        $query .= " and cbh.block_number_id = '" . $_GET['search_block_no'] . "'";
        $url .= '&search_block_no=' . $_GET['search_block_no'];
        $page_url .= '&search_block_no=' . $_GET['search_block_no'];
    }
    if (isset($_GET['search_project']) && is_array($_GET['search_project']) && sizeof($_GET['search_project']) > 0) {
        $query .= " and cbh.block_id IN (select id from kc_blocks where status = '1' and project_id IN ('" . implode("','", $_GET['search_project']) . "') )";
        foreach ($_GET['search_project'] as $project_id) {
            $url .= '&search_project[]=' . $project_id;
            $search_project_url_string .= '&search_project[]=' . $project_id;
        }
    }
    if (isset($_GET['search_associate']) && is_array($_GET['search_associate']) && sizeof($_GET['search_associate']) > 0) {
        $query .= " and cbh.associate IN ('" . implode("','", $_GET['search_associate']) . "')";
        foreach ($_GET['search_associate'] as $associate_id) {
            $url .= '&search_associate[]=' . $associate_id;
            $search_associate_url_string .= '&search_associate[]=' . $associate_id;
        }
    }
    
    if (isset($_GET['datesearch']) && $_GET['datesearch'] != '') {
        $ddatesearch = explode('-', $_GET['datesearch']);

        $startdate = date('Y-m-d 00:00:01', strtotime($ddatesearch[0]));
        $enddate = date('Y-m-d 23:59:59', strtotime($ddatesearch[1]));
        $query .= "and cbh.addedon between '$startdate' and '$enddate' ";
    }
    
    $total_records = mysqli_num_rows(mysqli_query($conn, $query));
    $total_pages = ceil($total_records / $limit);

    if ($page == 1) {
        $start = 0;
    } else {
        $start = ($page - 1) * $limit;
    }

    //$query .= " order by registry_date desc limit $start,$limit";
    $query .= " order by b.name, cast(bn.block_number as unsigned) limit $start,$limit";
    $customers = mysqli_query($conn, $query);
    $search = true;
} else {
    $url = 'refund_plot_list.php?search=Search';
    $query =  "select cbh.id as customer_block_id,cbh.id,cbh.deleted, cbh.customer_id,cbh.batch as batch, cbh.block_id, cbh.block_number_id, cbh.registry, cbh.registry_date, cbh.registry_by, cbh.associate,b.project_id, b.name as block_name, bn.block_number as block_number_name, bn.area, p.name as project_name, c.name_title as customer_name_title, c.name as customer_name, c.mobile as customer_mobile, c.address as customer_address,a.code as associate_code, a.name as associate_name from kc_customer_blocks_hist cbh LEFT JOIN kc_blocks b ON cbh.block_id = b.id LEFT JOIN kc_block_numbers bn ON cbh.block_number_id = bn.id LEFT JOIN kc_projects p ON b.project_id = p.id LEFT JOIN kc_customers c ON cbh.customer_id = c.id LEFT JOIN kc_associates a ON cbh.associate = a.id where cbh.status = '1' AND cbh.action_type = 'Cancel Booking'";
    
    $total_records = mysqli_num_rows(mysqli_query($conn, $query));
    $total_pages = ceil($total_records / $limit);

    if ($page == 1) {
        $start = 0;
    } else {
        $start = ($page - 1) * $limit;
    }

    //$query .= " order by registry_date desc limit $start,$limit";
    $query .= " order by b.name, cast(bn.block_number as unsigned) limit $start,$limit";
    $customers = mysqli_query($conn, $query);
    $search = true;
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

    <!-- jQuery UI -->
    <link href="/<?php echo $host_name; ?>/css/jquery-ui.css" rel="stylesheet" type="text/css" />

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
        .ui-autocomplete li {
            font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            padding: 5px 8px;
            font-weight: bold;
        }

        .ui-autocomplete li:hover {
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
                    <li><a href="dashboard.php"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Refund Plot Report</li>
                </ol>
            </section>
            <!-- Main content -->
            <section class="content">
                <div class="box">
                    <div class="box-header">
                        <?php include("../includes/notification.php"); ?>
                        <div class="col-sm-10">
                            <h3 class="box-title">Refund Plot Report</h3>
                        </div>
                        <?php if ($search && mysqli_num_rows($customers) > 0) { ?>
                            <div class="col-sm-1">
                                <a href="refund_plot_list_excel_export.php?<?php echo $search_project_url_string; ?>&search_block=<?php echo isset($_GET['search_block']) ? $_GET['search_block'] : ''; ?>&search_block_no=<?php echo isset($_GET['search_block_no']) ? $_GET['search_block_no'] : ''; ?><?php echo $search_associate_url_string; ?>&<?php if(isset($_GET['datesearch'])  && $_GET['datesearch']!='' ) echo 'datesearch='.$_GET['datesearch']; ?>&search=Search" class="btn btn-sm btn-success pull-right"><i class="fa fa-file-excel-o"></i> Excel Export</a>
                            </div>
                        <?php } ?>
                        <hr />
                        <form action="" name="search_frm" id="search_frm" method="get" class="">
                            <div class="row">
                                <div class="form-group col-sm-3">
                                    <label for="search_customer">Customer <a href="javascript:void(0);" class="text-primary" data-toggle="popover" title="Customer Search Hint" data-content="'c-' for Code Search<br>'n-' for Name Search<br>'m-' for Mobile Search<br><b>Eg:</b> If want to search 00942 in only code then Search for 'c-00942' <br><br> <b>OR</b> <br> <br> You can search similar name by Pressing 'Enter Key'"><i class="fa fa-info-circle"></i></a></label>

                                    <input type="text" class="form-control customer-autocomplete" placeholder="Name or Code or Mobile" data-for-id="search_customer">
                                    <input type="hidden" name="search_customer" id="search_customer">

                                    <?php /*<input type="text" class="form-control" placeholder="Search Name" name="search_customer" id="search_customer">*/ ?>

                                </div>
                                <div class="form-group col-md-3">
                                    <label for="search_project" class="col-md-12 text-left">Project</label>
                                    <select class="form-control select2-w100" id="search_project" name="search_project[]" multiple onChange="search_getBlocks(this.value);">
                                        <option value="" disabled>Select Project</option>
                                        <?php
                                        $projects = mysqli_query($conn, "select * from kc_projects where status = '1' ");
                                        while ($project = mysqli_fetch_assoc($projects)) { ?>
                                            <option value="<?php echo $project['id']; ?>"><?php echo $project['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                    <!-- <span id="span"></span> -->
                                </div>
                                <div class="form-group col-md-2 text-center">
                                    <label for="search_block" class="col-md-12 text-left">Block</label>
                                    <select class="form-control" id="search_block" name="search_block" onChange="search_getBlockNumbers(this.value);">
                                        <option value="">Select Block</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2 text-center">
                                    <label for="search_block_no" class="col-md-12 text-left">Plot Number</label>
                                    <select class="form-control" id="search_block_no" name="search_block_no">
                                        <option value="">Select Plot Number</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2 text-center">
                                    <label for="search_associate" class="col-md-12 text-left">Associate <a href="javascript:void(0);" class="text-primary" data-toggle="popover" title="Associate Search Hint" data-content="'c-' for Code Search<br>'n-' for Name Search<br>'m-' for Mobile Search<br><b>Eg:</b> If want to search 9651 in only code then Search for 'c-9651' "><i class="fa fa-info-circle"></i></a></label>
                                    <select class="form-control select2-ajax" id="search_associate" name="search_associate[]" multiple style="width:100%;" readonly>
                                        <option value="">Select Associate</option>

                                    </select>
                                </div>

                                <div class="form-group col-md-2 text-center">
                                    <label for="search_associate" class="col-md-12 text-left">Date Search </label>
                                    <div><input type="text" class="form-control" placeholder="Date" id="datepick" name="datesearch" readonly=""></div>
                                </div>

                                <div class="col-md-1">
                                    <input type="submit" name="search" value="Search" class="btn btn-sm btn-primary" style="margin-top: 25px;">
                                </div>
                            </div>
                        </form>

                        <!--  <div class="col-sm-1">
							<a href="cancel_plot_report_excel_export.php" class="btn btn-sm btn-success pull-right"><i class="fa fa-file-excel-o"></i> Excel Export</a>
					</div>
					<div class="col-sm-1">
							<a class="btn btn-sm btn-danger" href="javascript:void(0);" onclick="printDiv('printContent')" data-toggle="tooltip" title="Print"><i class="fa fa-print">&nbsp;</i>PDF Export</a>
					</div> -->

                    </div><!-- /.box-header -->

                    <div class="box-body no-padding" id="printContent">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-bordered">
                                <tr>
                                    <th>Sr.</th>
                                    <th>Project Details</th>
                                    <th>Customer Details</th>
                                    <th>Final Plot Amount</th>
                                    <th>Deposited</th>
                                    <th>Refunded</th>
                                    <th>Pending Refund</th>
                                    <th>Cancel Date</th>
                                    <th>Action</th>
                                </tr>
                                <?php
                                if ($search && mysqli_num_rows($customers) > 0) {
                                    $counter = $start + 1;
                                    $total_debited_amt = $total_credited_amt = $total_pending_amt = 0;
                                    while ($customer = mysqli_fetch_assoc($customers)) {
                                        // echo '<pre>';
                                        // print_r($customer);
                                        // die();
                                        $total_amount_details = mysqli_fetch_assoc(mysqli_query($conn, "select sum(amount) as total_amount from kc_customer_transactions_hist where customer_id = '" . $customer['customer_id'] . "' and block_id = '" . $customer['block_id'] . "' and block_number_id = '" . $customer['block_number_id'] . "' and cr_dr = 'cr' and batch = '" . $customer['batch'] . "' and status = '1' and action_type = 'Cancel Booking'"));
                                        $total_amount = $total_amount_details['total_amount'] ? $total_amount_details['total_amount'] : 0;
                                        $total_paid_details = mysqli_fetch_assoc(mysqli_query($conn, "select sum(amount) as total_paid from kc_customer_transactions_hist where customer_id = '" . $customer['customer_id'] . "' and block_id = '" . $customer['block_id'] . "' and block_number_id = '" . $customer['block_number_id'] . "' and cr_dr = 'dr' and batch = '".$customer['batch']."' and status = '1' and remarks is NULL and action_type = 'Cancel Booking'"));
                                        $total_paid = $total_paid_details['total_paid'] ? $total_paid_details['total_paid'] : 0;
                                        $total_discount_details = mysqli_fetch_assoc(mysqli_query($conn, "select sum(amount) as total_discount from kc_customer_transactions_hist where customer_id = '" . $customer['customer_id'] . "' and block_id = '" . $customer['block_id'] . "' and block_number_id = '" . $customer['block_number_id'] . "' and cr_dr = 'dr' and batch = '" . $customer['batch'] . "'  and status = '1' and remarks is NOT NULL and action_type = 'Cancel Booking'"));
                                        $total_discount = $total_discount_details['total_discount'] ? $total_discount_details['total_discount'] : 0;

                                        $final_amount = $total_amount - $total_discount;

                                        $total_refund = mysqli_fetch_assoc(mysqli_query($conn, "select sum(amount) as total_refunded from kc_refund_amount where customer_id = '" . $customer['customer_id'] . "' and block_id = '" . $customer['block_id'] . "' and block_number_id = '" . $customer['block_number_id'] . "'  and deleted is null"));
                                        $total_refunded = $total_refund['total_refunded'] ? $total_refund['total_refunded'] : 0;

                                        $pending_amount = ($total_paid - $total_refunded);

                                        $customerBlock =  mysqli_num_rows(mysqli_query($conn, "SELECT * FROM kc_customer_blocks WHERE  block_id = '" . $customer['block_id'] . "' and block_number_id = '" . $customer['block_number_id'] . "'"));
                                        // echo "<pre>"; print_r($customerBlock); die;

                                        // $associate =  mysqli_fetch_assoc(mysqli_query($conn, "SELECT id, code, name, mobile_no FROM kc_associates WHERE  id = '" . $customer['associate'] . "' "));
                                        // $associate_name = isset($associate['id'])?$associate['code'].'-'.$associate['name'].'('.$associate['mobile_no'].')':'';
                                        $associate_name = $customer['code'].'-'.$customer['name']; ?>
                                        <tr>
                                            <td><?php echo $counter; ?>.</td>
                                            <td>
                                                <strong>Project : </strong><?php echo $customer['project_name']; ?><br>
                                                <strong>Block : </strong><?php echo $customer['block_name']; ?><br>
                                                <strong>Plot No. : </strong><?php echo $customer['block_number_name']; ?><br>
                                                <strong>Associate : </strong><?php echo $associate_name; ?>
                                            </td>
                                            <td>
                                                <strong>Name : </strong><?php echo ($customer['customer_name_title'] . ' ' . $customer['customer_name']) . '<br>' . ' (' . customerID($customer['customer_id']) . ')'; ?><br>
                                                <strong>Mobile : </strong><?php echo $customer['customer_mobile']; ?><br>
                                                <strong>Address : </strong><?php echo $customer['customer_address']; ?>
                                            </td>
                                            <td nowrap="nowrap"><span class="text-success"><?php echo $final_amount; ?> <i class="fa fa-inr"></i></span></td>
                                            <td nowrap="nowrap"><span class="text-success"><?php echo $total_paid; ?> <i class="fa fa-inr"></i></span></td>
                                            <td nowrap="nowrap"><span class="text-info"><?php echo $total_refunded ? $total_refunded : 0; ?> <i class="fa fa-inr"></i></span></td>
                                            <td nowrap="nowrap"><span class="text-danger"><?php echo ($pending_amount ? $pending_amount : 0); ?> <i class="fa fa-inr"></i></span></td>
                                            <td width="8%"><?php echo date("d M Y h:i A", strtotime($customer['deleted'])); ?></td>
                                            <td width="8%">
                                                <?php if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'view_transaction_cancel_plot_hist')) {  ?>
                                                    <button class="btn btn-xs btn-info" onClick="getTransactions(<?php echo $customer['customer_block_id']; ?>);" data-toggle="tooltip" title="View Old Transactions"><i class="fa fa-eye"></i></button>
                                                <?php }
                                                
                                                if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'view_refund_cancel_plot_hist')) {
                                                    if ($total_refund['total_refunded'] > 0) { ?>
                                                        <button class="btn btn-xs btn-warning" onClick="getRefundTransactions(<?php echo $customer['customer_block_id']; ?>);" data-toggle="tooltip" title="View Refund Transactions"><i class="fa fa-money"></i></button>
                                                    <?php }
                                                }                                          
                            
                                                if (userCanView($conn, $_SESSION['login_id'], $privilegeName = 'restore_cancelled')){
                                                    if (($total_refunded <= 0) && ($customerBlock == 0)) { ?>
                                                        <button class="btn btn-xs btn-success" onClick="restoreCancelPlot(<?php echo $customer['customer_block_id'].','. $customer['batch'];?>);" data-toggle="tooltip" title="Restore Cancelled Plot"><i class="fa fa-money"></i></button>
                                                    <?php }
                                                } ?>                                          
                                            </td>
                                        </tr>
                                        <?php $counter++;
                                    } 
                                } else { ?>
                                    <tr>
                                        <td colspan="16" align="center">
                                            <h4 class="text-red">No Record Found</h4>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </div><!-- /.box-body -->

                    <?php if (isset($total_pages) && $total_pages > 1) { ?>
                        <div class="box-footer clearfix">
                            <ul class="pagination pagination-sm no-margin pull-right">

                                <?php
                                for ($i = 1; $i <= $total_pages; $i++) {
                                ?>
                                    <li <?php if ((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)) { ?>class="active" <?php } ?>><a href="<?php echo $url; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
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
   

    <div class="modal" id="viewRefundTransaction">
        <div class="modal-dialog">
            <div class="modal-content">
                <form enctype="multipart/form-data" action="" name="view_refund_transaction_frm" id="view_refund_transaction_frm" method="post" class="form-horizontal dropzone">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">All Transactions</h4>
                    </div>
                    <div class="modal-body">
                        <div class="box box-info">
                            <div class="box-body">
                                <table class="table table-bordered" id="view-refund-transaction-container">
                                </table>
                            </div><!-- /.box-body -->
                        </div><!-- /.box -->
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div class="modal" id="viewTransaction">
        <div class="modal-dialog">
            <div class="modal-content">
                <form enctype="multipart/form-data" action="" name="view_transaction_frm" id="view_transaction_frm" method="post" class="form-horizontal dropzone">
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
    <?php require('../includes/common-js.php'); ?>
    <script type="text/javascript">
        $(function() {
            $(".select2-ajax").select2({
                ajax: {
                    url: '../dynamic/getAssociateMultiple.php',
                    dataType: 'json',

                    data: function(params) {
                        var query = {
                            term: params.term
                        }

                        // Query parameters will be ?search=[term]&type=public
                        return query;
                    },
                    processResults: function(data) {
                        // Transforms the top-level key of the response object from 'items' to 'results'
                        return {
                            // alert('data');
                            results: data.items
                        };
                    }
                    // Additional AJAX parameters go here; see the end of this chapter for the full code of this example
                }
            });
        });

        function addRefundAmount(block_number,batch) {
            $.ajax({
                url: '../dynamic/RefundAmount.php',
                type: 'post',
                data: {
                    block_number: block_number,
                    batch: batch
                },
                success: function(resp) {
                    $("#add-refund-amount-container").html(resp);
                    $("#addRefundAmount").modal('show');
                }
            });
        }

        function search_getBlocks(project) {
            var projects = $("#search_project").val();

            $("#search_block_no").val('');
            $.ajax({
                url: '../dynamic/getBlocksByMultipleProjects.php',
                type: 'post',
                data: {
                    projects: projects
                },
                success: function(resp) {
                    $("#search_block").html(resp);
                }
            });
        }

        function getRefundTransactions(customer_blocks_hist_id) {
            $.ajax({
                url: '../dynamic/getRefundTransactions.php',
                type: 'post',
                data: {
                    customer_blocks_hist_id: customer_blocks_hist_id
                },
                success: function(resp) {
                    $("#view-refund-transaction-container").html(resp);
                    $("#viewRefundTransaction").modal('show');
                }
            });
        }

        function getTransactions(hist) {
            $.ajax({
                url: '../dynamic/getCancelTransactions.php',
                type: 'post',
                data: {
                    hist: hist
                },
                success: function(resp) {
                    $("#view-transaction-container").html(resp);
                    $("#viewTransaction").modal('show');
                }
            });

        }

        function search_getBlockNumbers(block) {
            $.ajax({
                url: '../dynamic/getCancleBlockNumbers.php',
                type: 'post',
                data: {
                    block: block,
                    type: 'booked'
                },
                success: function(resp) {
                    if (resp.trim() != '') {
                        $("#search_block_no").html(resp);
                    } else {
                        $("#search_block_no").html('<option value="">Select Plot Number</option>');
                    }
                }
            });
        }

        function restoreCancelPlot(id, batch) {
           var confirmation = confirm("Do you want to restore this cancelled booking ?");
           if(confirmation == true ){
                var again = confirm("You are on way to restore the cancelled booking, Go Ahead ?");
                if(again == true ){
                  
                    $.ajax({
                        url: '../dynamic/restoreCancelPlot.php',
                        type: 'post',
                        data: {
                            id: id,
                            batch: batch,
                        },
                        // success: function(resp) {

                        // }
                     });
                }
            }
        }
        //    }
           
        // }


        $(document).on('click','input[name="search"]',function(){
        	var val = $('#search_project').val();
        	// if(val == null){
        	// 	$('#span').css({'color':'red'});
        	// 	$('#span').html('Project field is required.');
        	// 	return false;
        	// }else{
        		$('#span').html('');
        		return true;
        	// }
        })
    </script>
    <script>
        $(document).ready(function() {
            var start = "{{ date('d-m-Y',strtotime($startdate)) }}";
            var end = "{{ date('d-m-Y',strtotime($enddate)) }}";

            $('input[name="datesearch"]').daterangepicker({

                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Previous 7 Days': [moment(), moment().subtract(6, 'days')],

                    'This Month': [moment().startOf('month'), moment().endOf('month')],

                }

            });

        })
    </script>


</body>

</html>
