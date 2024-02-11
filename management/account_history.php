<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");
if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_projects'))){ 
  header("location:/wcc_real_estate/index.php");
  exit();
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
    .iradio_square-blue.has-error>.form-error {
        margin-top: 25px;
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
                    <li class="active">Accounts</li>
                </ol>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="box">
                    <div class="box-header">
                        <?php 
					include("../includes/notification.php"); ?>
                        <div class="col-sm-8">
                            <h3 class="box-title">All Accounts History</h3>
                        </div>
                    </div><!-- /.box-header -->
                    <form enctype="multipart/form-data" action="account_history.php" name="search_frm" id="search_frm"
                        method="get" class="form-inline has-validation-callback">
                        <div class="form-group">
                            <label for="search_associate">Accounts<a href="javascript:void(0);" class="text-primary"
                                    data-toggle="popover" title=""
                                    data-content="'c-' for Code Search<br>'n-' for Name Search<br>'m-' for Mobile Search<br><b>Eg:</b> If want to search 9651 in only code then Search for 'c-9651' "
                                    data-original-title="Associate Search Hint"><i
                                        class="fa fa-info-circle"></i></a></label>
                            <input type="text" class="form-control account_delails_search ui-autocomplete-input"
                                data-for-id="search_associate" placeholder="Name or Account No or bank name"
                                autocomplete="off">
                            <input type="hidden" name="search_associate" id="search_associate">
                            <input type="submit" name="search" value="Search" class="btn btn-sm btn-primary">
                        </div>
                    </form>
                    <br>
                    <div class="box-body no-padding">

                        <table class="table table-striped table-hover table-bordered">
                            <tr>
                                <th>Sl No.</th>
                                <th>Name</th>
                                <th>Bank Details</th>
                                <th>Payment Details</th>
                                <th>Added Date</th>
                                <th>Action</th>

                            </tr>
                            <?php
					$limit = 50;
					
					$query ='';
					if(isset($_GET['search_associate']) && (int) $_GET['search_associate'] > 0){
						$account_id = (int) $_GET['search_associate'];
						$query .= " where id = '$account_id' ";
					}
					

					$total_records = mysqli_fetch_assoc(mysqli_query($conn,"select COUNT(*) as total from kc_accounts $query "));
					$total_pages = ceil($total_records['total']/$limit);
					if(isset($_GET['page'])){
						$page = $_GET['page'];
					}else{
						$page = 1;
					}

					if($page == 1){
						$start = 0;
					}else{
						$start = ($page-1)*$limit;
					}
					
					$accounts = mysqli_query($conn,"select * from kc_accounts $query limit $start,$limit ");
					if(mysqli_num_rows($accounts) > 0){
						$counter = 1;
						while($account = mysqli_fetch_assoc($accounts)){ 
                            $trancation = (mysqli_query($conn,"select *  from  kc_customer_transactions where account_id ='".$account['id']."'"));
                            $totalCr =0;
                            $totalDr=0;
                            while($row=mysqli_fetch_assoc($trancation)){
                              if($row['cr_dr'] == 'cr'){
                                $totalCr+=$row['amount'];
                              }else{
                                $totalDr+=$row['amount'];
                              }
                            
                        }
                        
                           ?>

                            <tr>
                                <td><?php echo $counter; ?>.</td>
                                <td><?php echo $account['name']; ?></td>
                                <td>
                                    <strong>Account No : </strong><a href="javascript:void(0)"
                                        onclick="accountTransactions(<?php echo $account['id'];?>);"
                                        data-bs-toggle="modal" data-bs-target="#viewTransaction"
                                        title="All Account Transaction"><?php echo $account['account_no']; ?></a><br>
                                    <?php if($account['bank_name'] != Null){?>
                                    <strong>Bank Name : </strong><?php echo $account['bank_name']; ?><br>
                                    <?php } ?>
                                    <?php if($account['branch_name'] != Null){?>
                                    <strong>Branch Name : </strong><?php echo $account['branch_name']; ?><br>
                                    <?php } ?>
                                    <?php if($account['ifsc_code'] != Null){?>
                                    <strong>IFSC Code : </strong><?php echo $account['ifsc_code']; ?>
                                    <?php } ?>
                                </td>
                                <td>
                                    <strong>Credit Amount Total : </strong><?php echo  $totalCr; ?><br>
                                    <?php if($account['bank_name'] != Null){?>
                                    <strong>Debit Amount Total: </strong><?php echo  $totalDr; ?><br>
                                    <?php } ?>

                                </td>
                                <td><?php echo date("d M Y h:i A",strtotime($account['addedon'])); ?></td>
								<?php if($totalCr > 0):?>
                                <td><a href="javascript:void(0)"
                                        onclick="accountTransactions(<?php echo $account['id'];?>);"
                                        data-bs-toggle="modal" data-bs-target="#viewTransaction"
                                        title="All Account Transaction" class="btn btn-primary"><i
                                            class="fa fa-money"></i></a></td>
								<?php endif ?>				
                            </tr>
                            <?php
							$counter++;
						}
					}else{
						?>
                            <tr>
                                <td colspan="8" align="center">
                                    <h4 class="text-red">No Record Found</h4>
                                </td>
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
                            <li <?php if((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)){ ?>class="active"
                                <?php } ?>><a href="account_history.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
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
    <div class="modal" id="viewTransaction">
        <div class="modal-dialog">
            <div class="modal-content">
                <form enctype="multipart/form-data" action="customers.php" name="view_transaction_frm"
                    id="view_transaction_frm" method="post" class="form-horizontal dropzone">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Account Transactions</h4>
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
    function editaccount(id) {
        // alert(id);
        $.ajax({
            url: '../dynamic/editAccount.php',
            type: 'post',
            data: {
                id: id
            },
            success: function(resp) {
                $("#edit-account-container").html(resp);
                $("#editAccountModal").modal('show');
            }
        });
    }

    function accountTransactions(id) {
        console.log(id);
        $.ajax({
            url: '../dynamic/accountHistory.php',
            type: 'post',
            data: {
                account_id: id
            },
            success: function(resp) {
                $("#view-transaction-container").html(resp);
                $("[data-mask]").inputmask();
                $("#viewTransaction").modal('show');
            }
        });
    }
    </script>

</body>

</html>