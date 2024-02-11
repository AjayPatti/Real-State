<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");
require("../includes/sendMail.php");
require("../includes/sendMessage.php");

if (!userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_send_message')) {
    header("location:/wcc_real_estate/index.php");
    exit();
}

$url = $pagination_url = 'send_message.php';
$url .= '?search=true';
$pagination_url .= '?';

$limit = 200;   
if (isset($_GET['page'])) {
    $page = $_GET['page'];
    $url .= '&page=' . $_GET['page'];
} else {
    $page = 1;
}
// echo "<pre>";
// print_r($_SERVER['HTTP_REFERER'] );die;
$query = "select * from kc_contacts where status = '1' ";
if (isset($_GET['contact']) && (int) $_GET['contact'] > 0) {
    //echo "<pre>"; print_r($_GET); die;
    $id = (int) $_GET['contact'];
    if (isset($_GET['contact']) && isset($_GET['contact'])) {
        $query .= " and id = '" . $id . "'";
    }
    $contacts = mysqli_query($conn, $query);
    $search = true;
}
$allBtnText = 'Send To All';
if (isset($_GET['type']) && in_array($_GET['type'], array('contacts', 'customers', 'employees', 'associate','isTesting','visit'))) {
    $url .= "&type=" . $_GET['type'];

    if (isset($_GET['type']) && $_GET['type'] == "contacts") {
        $query .= " and type = 'Contact'";
        $pagination_url .= "type=" . $_GET['type'] . "&";
        $allBtnText = 'Send To All Contacts';
    } else if (isset($_GET['type']) && $_GET['type'] == "customers") {
        $query .= " and type = 'Customer'";
        $pagination_url .= "type=" . $_GET['type'] . "&";
        $allBtnText = 'Send To All Customers';
    } else if (isset($_GET['type']) && $_GET['type'] == "employees") {
        $query .= " and type = 'Employee'";
        $pagination_url .= "type=" . $_GET['type'] . "&";
        $allBtnText = 'Send To All Employees';
    } else if (isset($_GET['type']) && $_GET['type'] == "associate") {
        $query .= " and type = 'Associate'";
        $pagination_url .= "type=" . $_GET['type'] . "&";
        $allBtnText = 'Send To All Associates';
    } else if (isset($_GET['type']) && $_GET['type'] == "isTesting") {
        $query .= " and is_testing_number = '1'";
        $pagination_url .= "type=" . $_GET['type'] . "&";
        $allBtnText = 'Send To All Testing No';
    } else if (isset($_GET['type']) && $_GET['type'] == "visit") {
        $query .= " and type = 'visit'";
        $pagination_url .= "type=" . $_GET['type'] . "&";
        $allBtnText = 'Send To All Visits';
    }
}

if (isset($_GET['search_project']) || isset($_GET['search_block']) || isset($_GET['search_block_no']) || (isset($_GET['search_block_no']) && $_GET['search_block_no'] > 0)) {

    $query1 = "select cb.customer_id from kc_customer_blocks cb LEFT JOIN kc_blocks b ON cb.block_id = b.id LEFT JOIN kc_block_numbers bn ON cb.block_number_id = bn.id LEFT JOIN kc_projects p ON b.project_id = p.id LEFT JOIN kc_customers c ON cb.customer_id = c.id  where cb.status = '1'  ";

    if (isset($_GET['search_block']) && $_GET['search_block'] != '') {
        $query1 .= " and cb.block_id = '" . $_GET['search_block'] . "'";
        $pagination_url  .= 'search_block=' . $_GET['search_block'] . "&";
    }

    if (isset($_GET['search_block_no']) && $_GET['search_block_no'] != '') {
        $query1 .= " and cb.block_number_id = '" . $_GET['search_block_no'] . "'";
        $pagination_url  .= 'search_block_no=' . $_GET['search_block_no'] . "&";
    }

    if (isset($_GET['search_project']) && is_array($_GET['search_project']) && sizeof($_GET['search_project']) > 0) {
        $query1 .= " and cb.block_id IN (select id from kc_blocks where status = '1' and project_id IN ('" . implode("','", $_GET['search_project']) . "') )";
        foreach ($_GET['search_project'] as $project_id) {
            $pagination_url  .= 'search_project[]=' . $project_id . "&";
        }
    }


    $search = true;

    $query .= " and customer_id in ($query1) and type='customer'";
  
}

if (isset($_POST['sendToAll']) && $_POST['sendToAll'] == "Send To All" && isset($_POST['template'])) {
    $contacts = mysqli_query($conn, $query);
    $message_count = 0;
    if (mysqli_num_rows($contacts) > 0) {
        while ($contact = mysqli_fetch_assoc($contacts)) {
            if (strlen($contact['mobile']) == 10) {
                $variables_array = array_filter($_POST['variables']);
                $variables_array['variable1'] = $contact['name'];
                if (sendMessage($conn, $_POST['template'], $contact['mobile'], $variables_array)) {

                    $message_count++;
                }
            }
        }
    }
    if ($message_count > 0) {
        $_SESSION['success'] = $message_count . ' messages sent successfully.';
    } else {
        $_SESSION['error'] = 'No messages sent.';
    }
    // $url added in locatiion 20122023
    header("location:$url"); 
    exit();
}

if (isset($_POST['send']) && $_POST['send'] == "Send" && isset($_POST['template']) && isset($_POST['contacts']) && is_array($_POST['contacts']) && sizeof($_POST['contacts']) > 0 && is_array($_POST['names']) && sizeof($_POST['contacts']) <= sizeof($_POST['names'])) {

    $message_count = 0;

    $combine = false;
    if (in_array($_POST['template'], templatesWithoutVariable())) {
        $combine = true;
    }

    if ($combine) {
        $mobile_nos = implode(',', $_POST['contacts']);
        sendWishes($conn, $_POST['template'], $mobile_nos);
        $message_count = sizeof($_POST['contacts']);
    } else {
        foreach ($_POST['contacts'] as $key => $mobile_no) {
            if (isset($_POST['names'][$key]) && strlen($mobile_no) == 10) {
                $variables_array = array_filter($_POST['variables']);
                $variables_array['variable1'] = $_POST['names'][$key];
                if (sendMessage($conn, $_POST['template'], $mobile_no, $variables_array)) {
                    $message_count++;
                }
            }
        }
    }





    if ($message_count > 0) {
        $_SESSION['success'] = $message_count . ' messages sent successfully.';
    } else {
        $_SESSION['error'] = 'No messages sent.';
    }
    header("location:$url");
    exit();
}
//  print_r($_GET['user']);die;
if(isset($_GET['user']) && (int) $_GET['user'] > 0){
	$id = (int) $_GET['user'];
	
    $sql = mysqli_fetch_assoc(mysqli_query($conn,"SELECT `is_testing_number` FROM `kc_contacts` WHERE id= $id limit 0,1 "));
    if(isset($sql['is_testing_number'])){
        $current_status = $sql['is_testing_number'];
        
        
        if($current_status == 0){
            $new_status = 1;
        }else{
            $new_status = 0;
        }
    
        mysqli_query($conn,"UPDATE kc_contacts SET is_testing_number = '$new_status' where id = $id limit 1 ");
    }
    $url .= '&focus='.$id;
    header("Location:$url");
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
    <!-- jQuery UI -->
    <link href="/<?php echo $host_name; ?>/css/jquery-ui.css" rel="stylesheet" type="text/css" />

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="/<?php echo $host_name; ?>/js/html5shiv.min.js"></script>
        <script src="/<?php echo $host_name; ?>/js/respond.min.js"></script>
    <![endif]-->
    <style type="text/css">
        .btn-app {
            color: white;
            border: none;
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
                    <li class="active">Send Message</li>
                </ol>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="box">
                    <div class="box-header">
                        <?php
                        include("../includes/notification.php"); ?>
                        <div class="col-sm-4">
                            <h3 class="box-title">Send Message</h3>
                        </div>
                        <div class="col-sm-8">
                            <div class="col-sm-2">
                                <a href="send_message.php" class="btn btn-app btn-primary" style="background-color: #3c8dbc;">
                                    <i class="fa fa-bars"></i> All
                                </a>
                            </div>
                            <div class="col-sm-2">
                                <a href="send_message.php?type=associate" class="btn btn-app btn-info" style="background-color: #00c0ef;">
                                    <i class="fa fa-tree"></i> Associate
                                </a>
                            </div>
                            <div class="col-sm-2">
                                <a href="send_message.php?type=contacts" class="btn btn-app btn-danger" style="background-color: #d73925;">
                                    <i class="fa fa-bookmark"></i> Contacts
                                </a>
                            </div>
                            <div class="col-sm-2">
                                <a href="send_message.php?type=customers" class="btn btn-app btn-success" id="customer" style="background-color: #00a65a;">
                                    <i class="fa fa-users"></i> Customers
                                </a>
                            </div>
                            <div class="col-sm-2">
                                <a href="send_message.php?type=employees" class="btn btn-app btn-warning" style="background-color: #f0ad4e;">
                                    <i class="fa fa-user"></i> Employees
                                </a>
                            </div>
                            <div class="col-sm-2">
                                <a href="send_message.php?type=visit" class="btn btn-app btn-warning" style="background-color:#5685cb;">
                                    <i class="fa fa-user"></i>Visit
                                </a>
                            </div>
                            <?php if(isset($_SESSION['login_id'])   && $_SESSION['login_type'] == "super2admin"  )  { ?>
                            <div class="col-sm-2">
                                <a href="send_message.php?type=isTesting" class="btn btn-app btn-success" style="background-color: #00a65a;">
                                    <i class="fa fa-user"></i> Is Testing
                                </a>
                            </div>
                            <?php  } ?>
                        </div>
                    </div><!-- /.box-header -->

                    <?php  if(isset($_GET['type']) && $_GET['type'] == 'customers'){ ?>
                    <div class="box-header" id="searchTemp">
                        <form action="" name="search_frm" id="search_frm" method="get" class="">
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="search_project" class="col-md-12 text-left">Project</label>
                                    <select class="form-control select2-w100" id="search_project" name="search_project[]" multiple onChange="search_getBlocks(this.value);">
                                        <option value="">Select Project</option>
                                        <?php
                                        $projects = mysqli_query($conn, "select * from kc_projects where status = '1' ");
                                        while ($project = mysqli_fetch_assoc($projects)) { ?>
                                            <option value="<?php echo $project['id']; ?>"><?php echo $project['name']; ?></option>
                                        <?php } ?>
                                    </select>
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
                                <div class="col-md-1">
                                    <input type="submit" name="search" value="Search" class="btn btn-sm btn-primary" style="margin-top: 25px;">
                                </div>
                            </div>
                        </form>
                    </div><!-- /.box-header -->
                    <?php } ?>
                    <div class="container">
                        <form action="" name="search_frm" id="search_frm" method="get" class="">
                            <div class="form-group col-sm-3 ui-widget">
                                <label for="customer">All Contact <a href="javascript:void(0);" class="text-primary" data-toggle="popover" title="Contact Search Hint" data-content="'c-' for Code Search<br>'n-' for Name Search<br>'m-' for Mobile Search<br><b>Eg:</b> If want to search 00942 in only code then Search for 'c-00942' "><i class="fa fa-info-circle"></i></a></label>
                                <?php /*<input type="text" class="form-control" id="customer" name="customer" value="<?php echo (isset($_GET['name']) && $_GET['name'] != '')?$_GET['name']:''; ?>" />*/ ?>
                                <input type="text" class="form-control contact-autocomplete" placeholder="Name or Code or Mobile" data-for-id="search_contact">
                                <input type="hidden" name="contact" id="search_contact">
                            </div>
                            <button type="submit" name="search" value="Search" class="btn btn-primary" style="margin-top: 24px;"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
                        </form>
                    </div>
                    <div class="box-body no-padding">
                        <form enctype="multipart/form-data" action="<?php echo $url; ?>" name="send_message_frm" id="send_message_frm" method="post" class="form-horizontal dropzone">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="excel_file" class="col-sm-3 control-label">Select Template</label>
                                    <div class="col-sm-8">
                                        <select class="form-control" id="template" name="template" required>
                                            <option value="">Select Template</option>
                                            <optgroup label="Wishes">
                                                
                                                <option value="2">May the light that we celebrate at Diwali show us the way and lead us together on the path of social peace and harmony.Happy Diwali Team WCC</option>

                                               
                                                
                                                
                                            </optgroup>
                                            <optgroup label="Transactional">
                                                <option value="7">Hi VAR1 Thank you for choosing us. We're happy to have you! Plot No.: VAR2 - VAR3 in VAR4 has been marked as booked by you WCC Real Estate Pvt. Ltd.</option>

                                                


                                                
                                            </optgroup>
                                          
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 variable2" style="display:none;">
                                <div class="form-group">
                                    <label for="variable2" class="col-sm-3 control-label">VAR2</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" id="variable2" name="variables[variable2]">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 variable3" style="display:none;">
                                <div class="form-group">
                                    <label for="variable3" class="col-sm-3 control-label">VAR3</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" id="variable3" name="variables[variable3]">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 variable4" style="display:none;">
                                <div class="form-group">
                                    <label for="variable4" class="col-sm-3 control-label">VAR4</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" id="variable4" name="variables[variable4]">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 variable5" style="display:none;">
                                <div class="form-group">
                                    <label for="variable5" class="col-sm-3 control-label">VAR5</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" id="variable5" name="variables[variable5]">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 variable6" style="display:none;">
                                <div class="form-group">
                                    <label for="variable6" class="col-sm-3 control-label">VAR6</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" id="variable6" name="variables[variable6]">
                                    </div>
                                </div>
                            </div>
                            <table class="table table-striped table-hover table-bordered">
                                <tr>
                                    <th><input type="checkbox" id="checkAll" class="checkAll" for="checkAll"></th>
                                    <th>Sl No.</th>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Type</th>
                                    <th>Created</th>
                                    <?php      
                                    if(isset($_SESSION['login_id'])   && $_SESSION['login_type'] == "super2admin"  ) { ?>
                                    <th>Is Testing No.</th>
                                    <?php } ?>
                                </tr>
                                <?php
                                $query .= " group by mobile";
                                $total_records = mysqli_num_rows(mysqli_query($conn, $query));
                                $total_pages = ceil($total_records / $limit);

                                if ($page == 1) {
                                    $start = 0;
                                } else {
                                    $start = ($page - 1) * $limit;
                                }

                                $query .= " limit $start,$limit";

                                $contacts = mysqli_query($conn, $query);
                                if (mysqli_num_rows($contacts) > 0) {
                                    $counter = $start + 1;
                                    while ($contact = mysqli_fetch_assoc($contacts)) { ?>
                                        <tr>
                                            <td><input type="checkbox" name="contacts[<?php echo $contact['id']; ?>]" value="<?php echo $contact['mobile']; ?>" class="contact" id="row_<?php echo $contact['id']; ?>"></td>
                                            <td><?php echo $counter; ?>.</td>
                                            <td><?php echo $contact['name']; ?><input type="hidden" name="names[<?php echo $contact['id']; ?>]" value="<?php echo $contact['name']; ?>"></td>
                                            <td><?php echo $contact['mobile']; ?></td>
                                            <td>
                                                <?php if ($contact['type'] == "Associate") { ?>
                                                    <label class="label label-info">Associate</label>
                                                <?php } else if ($contact['type'] == "Customer") { ?>
                                                    <label class="label label-success">Customer</label>
                                                <?php } else if ($contact['type'] == "Contact") { ?>
                                                    <label class="label label-danger">Contact</label>
                                                <?php } else if($contact['type'] == "visit") { ?>
                                                    <label class="label" style="background-color:#5685cb;">Visit</label>
                                                <?php } else { ?>   
                                                    <label class="label label-warning">Employee</label>
                                                <?php } ?>
                                            </td>
                                            <td><?php echo date("d M Y h:i A", strtotime($contact['created'])); ?></td>

                                            <?php 
                                               
                                                    if($contact['is_testing_number']){
                                                        $button_class = 'btn-success';
                                                        $label_class = 'label-success';
                                                        $btn_title = "Make No";
                                                        $label_value = 'Yes';
                                                    }else{
                                                        $button_class = 'btn-danger';
                                                        $label_class = 'label-danger';
                                                        $btn_title = "Make Yes";
                                                        $label_value = 'No';
                                                    }
                                                     
                                            ?> 
                                            <?php 
                                            
                                                if(isset($_SESSION['login_id'])   && $_SESSION['login_type'] == "super2admin"  ) { ?>
                                                  
                                                <td><a href="<?php echo $url; ?>&user=<?php echo $contact['id']; ?>"  data-toggle="tooltip" title="<?php echo $btn_title; ?>"><button id="<?php echo $contact['id']?>"  class="btn btn-xs  ref  <?php echo $button_class; ?>" type="button"><label  class="label <?php echo $label_class; ?>"><?php echo $label_value ; ?></label></button></a></td>
                                             <?php } ?>
                                                    
                                        </tr>
                                    <?php
                                        $counter++;
                                    } ?>
                                    <tr>
                                        <td colspan="6" align="center">

                                            <?php /*<button id="sendToAll" class="btn btn-sm btn-primary" name="sendToAll" onclick="return validateAll();" value="Send To All"><?php echo $allBtnText; ?></button>*/ ?>
                                            <button id="send" type="submit" name="send" class="btn btn-primary btn-sm" onclick="return validate();" value="Send">Send To Selected</button>
                                        </td>
                                    </tr>
                                <?php
                                } else {
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
                        </form>
                    </div><!-- /.box-body -->

                    <?php if ($total_pages > 1) { ?>
                        <div class="box-footer clearfix">
                            <ul class="pagination pagination-sm no-margin pull-right">

                                <?php


                                for ($i = 1; $i <= $total_pages; $i++) {
                                ?>
                                    <li <?php if ((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)) { ?>class="active" <?php } ?>><a href="<?php echo $pagination_url ?>page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
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

    <?php require('../includes/common-js.php'); ?>

    <script type="text/javascript">
        
        //  $(document).on('click','.ref', function(e){ 
        //     let selectedId = $(this).attr('id')
        //     localStorage.setItem('target', selectedId);
        // })

        // $(document).ready(function(){

            
        //     let retrieve = localStorage.getItem('target');
        //     // console.log(retrieve)
        //     if(retrieve){                
        //         let a = document.createElement('a')
        //         a.href = '#'+retrieve;
        //         // console.log(a)
        //         document.body.appendChild(a);
        //         a.click()
        //     }

        // });


       
        $(function() {
            $("#template").change(function() {
                if ($(this).val() == '8' || $(this).val() == '9') {
                    $(".variable5").show();
                } else {
                    $(".variable5").hide();
                }

                if ($(this).val() == '7' || $(this).val() == '8' || $(this).val() == '9') {
                    $(".variable4").show();
                } else {
                    $(".variable4").hide();
                }

                if ($(this).val() == '7' || $(this).val() == '8' || $(this).val() == '9') {
                    $(".variable3").show();
                } else {
                    $(".variable3").hide();
                }

                if ($(this).val() == '7' || $(this).val() == '8' || $(this).val() == '9') {
                    $(".variable2").show();
                } else {
                    $(".variable2").hide();
                }

                <?php /*else if($(this).val() == '1' || $(this).val() == '5'){
				$(".variable2").show();
			}else{
				$(".variable2").find('input').val('');
				$(".variable2").hide();
			}*/ ?>
            });
            /*$("#send_message_frm").submit(function(e){

              if($("#template").val() == ""){
                alert("Please select a Message Template.");
                e.preventDefault();
              }else if($("#template").val() == "1" && $("#variable2").val() == ""){
                alert("Please Fill Festival Name.");
                e.preventDefault();
              }else if($(".contact:checked").length == 0){
                alert("Please select atleast one Contact.");
                e.preventDefault();
              }
            });*/
            <?php if(isset($_GET['focus'])){ ?>
                $("#row_<?php echo $_GET['focus']; ?>").focus();
            <?php } ?>
        });

        function validate() {
            if ($("#template").val() == "") {
                alert("Please select a Message Template.");
                return false;
            } else if ($("#template").val() == "1" && $("#variable2").val() == "") {
                alert("Please Fill Festival Name.");
                return false;
            } else if ($(".contact:checked").length == 0) {
                alert("Please select atleast one Contact.");
                return false;
            }

            var error = false;
            $(".variable2,.variable3,.variable4,.variable5,.variable6").each(function() {
                if ($(this).css('display') != "none" && $(this).find('input').val() == "") {
                    error = true;
                }
            });
            if (error) {
                alert("Please Fill all variable field");
                return false;
            }
        }

        function validateAll() {

            if ($("#template").val() == "") {
                alert("Please select a Message Template.");
                return false;
            }
            var error = false;
            $(".variable2,.variable3,.variable4,.variable5,.variable6").each(function() {
                if ($(this).css('display') != "none") {
                    error = true;
                }
            });
            if (error) {
                alert("This Message Template is not allowed for <?php echo $allBtnText; ?>");
                return false;
            }
            if (!confirm('Are you sure you want this message <?php echo $allBtnText; ?>')) {
                return false;
            }
        }

        function iCheckClicked(elem) {
            var for_attr = $(elem).attr('for');
            if (for_attr == "checkAll") {
                if (!($(elem).is(":checked"))) {
                    $('.contact').iCheck('check');
                } else {
                    $('.contact').iCheck('uncheck');
                }
            }
        }

        /********************************************* **/

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

        function search_getBlockNumbers(block) {
            $.ajax({
                url: '../dynamic/getBlockNumbers.php',
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
        // function contactStatusUpdate(elem){
        //    var user = $(elem).data('id');
        //    $.ajax({
        //     url:'send_message.php?user='+user,
        //     type:'get',
        //     data:'',
        //     success:function(resp){
                
        //         location.reload();
        //     }
        //    });
        // }
        
    </script>

</body>

</html>
