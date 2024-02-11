<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");
if(!(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_associate'))){
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
    <link href="/<?php echo $host_name; ?>/plugins/iCheck/flat/blue.css" rel="stylesheet" type="text/css" />
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
    <link href="/<?php echo $host_name; ?>css/tree.css" rel="stylesheet" type="text/css" />
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="/<?php echo $host_name; ?>/js/html5shiv.min.js"></script>
        <script src="/<?php echo $host_name; ?>/js/respond.min.js"></script>
    <![endif]-->
    <style>
    .img {
      margin-left: 18px;

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
                    <li class="active">Associates</li>
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
                                <h3 class="box-title">All Associate</h3>
                            </div>
                            <?php if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'add_associate')){ ?>
                            <div class="col-sm-4">
                                <!-- <button class="btn btn-sm btn-success pull-right" data-toggle="modal"
                                    style="margin-left: 13px;" data-target="#addAssociate">Add Associate</button> -->
                                <!-- <a class="btn btn-sm btn-success" href="associates.php?export=true" data-toggle="tooltip" title="Export All to Excel"><i class="fa fa-file-excel-o"></i>Export All Associates</a> -->
                                <!-- <a href="associates_excel_export.php" class="btn btn-sm btn-success pull-right"
                                    style="margin-right: 10px;"><i class="fa fa-file-excel-o"></i> Excel Export</a> -->
                            </div>

                            <?php }?>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-12">
                                <form enctype="multipart/form-data" action="associate_tree_view.php" name="search_frm"
                                    id="search_frm" method="get" class="form-inline">
                                    <?php if($_SESSION['login_type'] == "super_admin" || userCan($conn,$_SESSION['login_id'],$privilegeName = 'search_associate')){ ?>
                                    <div class="form-group">
                                        <?php /*<select id="search_for" class="form-control">
											<option value="all">Search For All</option>
											<option value="name">Search For Name</option>
											<option value="code">Search For Code</option>
											<option value="mobile">Search For Mobile Number</option>
										</select>*/ ?>
                                        <label for="search_associate">Associate <a href="javascript:void(0);"
                                                class="text-primary" data-toggle="popover" title="Associate Search Hint"
                                                data-content="'c-' for Code Search<br>'n-' for Name Search<br>'m-' for Mobile Search<br><b>Eg:</b> If want to search 9651 in only code then Search for 'c-9651' "><i
                                                    class="fa fa-info-circle"></i></a></label>
                                        <input type="text" class="form-control associate-autocomplete"
                                            data-for-id="search_associate" <?php /*data-search-for-id="search_for" */ ?>
                                            placeholder="Name or Code or Mobile">
                                        <input type="hidden" name="search_associate" id="search_associate">
                                        <input type="submit" name="search" value="Search"
                                            class="btn btn-sm btn-primary">
                                    </div>
                                    <?php } ?>
                                </form>
                               
                            </div>
                        </div>
                    </div><!-- /.box-header -->
                    <div class="box-body no-padding" style="margin-top: 41px">

                    <?php 
                                        // print_r($_SESSION['login_type']);die;
                             $query="SELECT * FROM kc_associates";           
                             $i= 0;
                             $arr = []; 
                             $firstIndex=0;
                             if(isset($_GET['search_associate']) ){
                                $parent_id= $_GET['search_associate'];
                                $parentQuery = $query." where id = '$parent_id'";
                                $query.= " where parent_id ='$parent_id' ";
                                
                                $parent_details = mysqli_query($conn,  $parentQuery);
                                $i=0;
                                while ($row = mysqli_fetch_assoc( $parent_details)) {
                                  if($i==0) $firstIndex =$row['id'];
                                  $arr[$row['id']]['name'] = $row['name'];
                                  $arr[$row['id']]['parent_id'] = $row['parent_id'];
                                  $arr[$row['id']]['id'] = $row['id'];
                                  $arr[$row['id']]['code'] = $row['code'];
                                  $i++;
                                }
                              }
                            //   echo $query.'<br>'.$parentQuery;die;
                              $res = (mysqli_query($conn,$query ));

                              while ($row = mysqli_fetch_assoc( $res )) {
                                 
                                $arr[$row['id']]['name'] = $row['name'];
                                $arr[$row['id']]['parent_id'] = $row['parent_id'];
                                $arr[$row['id']]['id'] = $row['id'];
                                $arr[$row['id']]['code'] = $row['code'];
                             
                              }
                       
                            if(!empty($arr)){

                                echo "<ul class='tree'>";
                                echo "<li><button style='display: inline-grid;' type='button' aria-pressed='false' data-js='node'><img src='../img/tree.png' class='img-circle img' data-type='superadmin' /> <span> ".$_SESSION['login_type']."</span></button><ul>";

                                    buildTreeView($arr,$firstIndex,1);

                                echo "</ul></li>";
                                echo "</ul>";
                            }
                          
                            function buildTreeView($arr, $parent, $first =false )
                            {
 
                              foreach ($arr as $id => $data){
                                
                                  if ($data['parent_id'] == $parent ) {
                                      echo "<li><button type='button' style='display: inline-grid;' aria-pressed='false' data-js='node' data-name=".$data['name']." onclick='getTransactions(this,".$data['id'].")'><img src='../img/tree.png' class='img-circle img' /> " . $data['name'] . "<span>(".$data['code'].")</span></button>";
                                      
                                      $children = array_filter($arr, function ($child) use ($id) { 
                                          return $child['parent_id'] == $id;
                                      });
                                 
                                      if (!empty($children)){
                                          echo "<ul>";
                                          buildTreeView($arr, $id);
                                          echo "</ul>";
                                      }

                                      echo "</li>";
                                     
                                    // print_r($data['parent_id']);
                                  }else{ 
                                    if($id == $parent && $first){
                                        echo " <button type='button' style='display: inline-grid;' aria-pressed='false' data-js='node' data-name=".$data['name']." onclick='getTransactions(this,".$data['id'].")'><img src='../img/tree.png' class='img-circle img' /> " . $data['name'] . "<span>(".$data['code'].")</span></button><ul>";
                                    }
                                  }
                                 
                              }
                              
                            } 
                            
                            
                    ?>
				
                </div><!-- /.box -->
            </section>

        </div><!-- /.content-wrapper -->
        <?php require('../includes/footer.php'); ?>

        <?php require('../includes/control-sidebar.php'); ?>
        <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
        <div class="control-sidebar-bg"></div>
    </div><!-- ./wrapper -->

    <div class="modal" id="viewTransaction">
        <div class="modal-dialog">
            <div class="modal-content">
                <form enctype="multipart/form-data" action="associate_tree_view.php" name="view_transaction_frm"
                    id="view_transaction_frm" method="post" class="form-horizontal dropzone">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">All Transactions <span id="associate_name" class="text-success"></span></h4>
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


    <script type="text/javascript">
    function getTransactions(elem, associate) {
        let name = $(elem).data('name');
        $('#associate_name').text(name)

        $.ajax({
            url: '../dynamic/getAssociateTransactions.php',
            type: 'post',
            data: {
                associate: associate
            },
            success: function(resp) {
                $("#view-transaction-container").html(resp);
                $("#viewTransaction").modal('show');
            }
        });
    }
    </script>

    <?php require('../includes/common-js.php'); ?>

    <script type="text/javascript">

    </script>

</body>

</html>