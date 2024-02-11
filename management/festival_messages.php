<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");
require("../includes/sendMessage.php");


if($_SESSION['login_type'] != 'super2admin'){
  header("location:/wcc_real_estate/index.php");
  exit();
}
 
$url = $pagination_url = 'festival_messages.php';
$url .= '?search=true';
$pagination_url .= '?';
 
// festival  insretion queries
if(isset($_POST['date']) && isset($_POST['template'])) {
        
        $date=date("Y-m-d", strtotime($_POST['date']));
        $template = $_POST['template'];
        $status = $_POST['status'];
        $template_id = $_POST['template_id'];
        $now = date_create()->format('Y-m-d H:i:s');
        $sql= mysqli_query($conn,'INSERT INTO kc_festival_message(`date`, `template_id`,`status`,`created_at`,`created_by`)VALUES ("'.$date.'" ,"'.$template_id.'","'.$status.'" ,"'.$now.'" ,"'.$_SESSION['login_id'].'" )');
        
       
        
       
        $add = mysqli_insert_id($conn);
        if($add > 0){
            $_SESSION['success'] = 'Calander Successfully Added!';
            header("festival_messages.php");
            exit();
        }else{
            $_SESSION['error'] = 'Something Went Wrong.Try Again!';
        }
 }
   
  
    
    //   soft delete query and permanent delete query
 if(isset($_POST['id']) && !empty($_POST['id'])){
    $date=date("Y-m-d", strtotime($_POST['date']));
    $id = $_POST['id'];
    $deleted_by = $_SESSION['login_id'];
    
    $now = date_create()->format('Y-m-d H:i:s');
   
    $query = mysqli_query($conn,"UPDATE kc_festival_message Set deleted_at='$now' ,deleted_by='$deleted_by' where id=   $id" );
 
    if($query !=false){
       echo "deleted";
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
    <!-- <meta http-equiv="Content-Type" content="text/html; charset=utf-8"> -->
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
        #ui-datepicker-div { font-size: 12px; } 
    </style>
</head>

<body class="skin-blue sidebar-mini">
    <div class="wrapper">

        <?php require('../includes/header.php'); ?>
        <!-- Left side column. contains the logo and sidebar -->
        <aside class="main-sidebar">
            <!-- sidebar: style can be found in sidebar.less -->
            <?php require('../includes/left_sidebar.php');  ?>
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
                       
                </div> 
                   
                       <div class="container"> 
                          <form action="#"   method="post" class="">
                                <div class="form-group col-sm-3">
                                    <label for="datepicker">Calander </label>
                                

                                    <input type="text" class="form-control " id="datepicker" name="date" placeholder="" data-date-format="yyyy-mm-dd"   required autocomplete="off"> 
                                    <input type="hidden" name="status" id="status" value="0">
                                </div>
                                <div class="form-group col-sm-7">
                                    <label >Select Template </label>
                                    <select class="form-control" id="template" name="template" required>
                                                <option value="">Select Template</option>
                                                    <optgroup label="Wishes">
                                                        <option  data-template_id="2" value="2">May the light that we celebrate at Diwali show us the way and lead us together on the path of social peace and harmony.Happy Diwali Team WCC</option>
                                                        <option  data-template_id="3" value="3">Best wishes for joy and love this Christmas season,for you and your family.Merry Christmas Team WCC</option>
                                                        <option data-template_id="11" value="11">कनौजिया सिटी परिवार की ओर से झिलमिलाते दीपों की रोशनी से प्रकाशित ये दीपावली आपके घर में सुख समृद्धि और आशीर्वाद ले कर आये| शुभ दीपावली !कनौजिया सिटी रियल एस्टेट प्र0 लि0</option>
                                                        <option data-template_id="12" value="12">रोशनी और ख़ुशी के इस पावन पर्व पर आपकी सारी मनोकामनाएं पूरी हो और घर में सुख सम्पन्नता बरसे| शुभ धनतेरस! कनौजिया सिटी रियल एस्टेट प्र0 लि0</option>
                                                        <option data-template_id="13" value="13">May this Dhanteras Festival Wishing you with happiness, Wealth & Prosperity. Happy Dhanteras WCC Real Estate Pvt. Ltd.</option>
                                                        <option data-template_id="14" value="14">झिलमिलाते दीपों की रोशनी से प्रकाशित ये दीपावली आपके घर में सुख समृद्धि ले कर आये| शुभ दीपावली! कनौजिया सिटी रियल एस्टेट प्र0 लि0</option>
                                                        <option data-template_id="15" value="15">Have a wonderful year filled with peace, prosperity and happiness. Happy Diwali WCC Real Estate Pvt. Ltd.</option>
                                                        <option data-template_id="16" value="16">WCC wishing You and your family a very Happy New Year with a bright future.Team WCC</option>
                                                        <option data-template_id="17" value="17">Wishing you all a very Happy Republic Day! Freedom in the mind, strength in the words, pureness in our blood and pride in our souls. Let's salute our martyrs on Republic Day Team WCC</option>
                                                        <option data-template_id="18" value="18">आप को और आपके पूरे परिवार को होली की हार्दिक शुभकामनाएँ टीम कनौजिया सिटी / अभिनंदन रिसॉर्ट</option>
                                                        <option data-template_id="20" value="20">आप को और आपके पूरे परिवार को राखी की हार्दिक शुभकामनाएँ टीम कनौजिया सिटी / अभिनंदन रिसॉर्ट</option>
                                                        <option data-template_id="21" value="21">आप सभी को स्वतंत्रता दिवस की हार्दिक शुभकामनाएँ। टीम कनौजिया सिटी / अभिनंदन रिजॉर्ट</option>
                                                        <option data-template_id="22" value="22">आप को और आपके पूरे परिवार को दशहरा की हार्दिक शुभकामनाएँ टीम कनौजिया सिटी / अभिनंदन रिसॉर्ट</option>
                                                        <option data-template_id="23" value="23">आप सभी को विजय दशमी दशहरा की हार्दिक शुभकामनाएँ टीम कनौजिया सिटी</option>
                                                        <option data-template_id="25" value="25">धनतेरस के इस पावन पर्व पर आप सभी की सारी मनोकामनाएँ पूरी हो | शुभ धनतेरस कनौजिया सिटी रियल एस्टेट प्र0 लि0</option>
                                                        <option data-template_id="26" value="26">आप सभी को नववर्ष की हार्दिक शुभकामनाएँ टीम कनौजिया सिटी</option>
                                                        <option data-template_id="27" value="27">आप सभी को गणतंत्र दिवस की हार्दिक शुभकामनाएँ टीम कनौजिया सिटी</option>
                                                        <option data-template_id="29" value="29">आपको राम नवमी की हार्दिक बधाई , कनौजिया सिटी रियल एस्टेट प्र0 लि0</option>
                                                    </optgroup>  
                                                </option> 
                                        </select>
                                </div>
                                <button type="submit" name="submit" value="submit" class="btn btn-primary" style="margin-top: 24px;"><span  aria-hidden="true"></span>set</button>
                          </form> 
                       </div>
                       <div class="row">
                         <div class="col-sm-2"></div>
                         <div class="form-group col-sm-9 ui-widget">
                              <label >Upcomig Events </label><br>
                              <table class="table table-striped table-hover table-bordered">
                                 <tr>   
                                    <th>Sl No.</th>
                                    <th>Date</th>
                                    <th>Template </th>
                                    <th>Operation</th>
                                 </tr>
                                     <!-- here php applied for upcoming_events -->
                                   <?php 
                                    $now = date_create()->format('Y-m-d'); 
                                    $template= getSmsTemplates();
                                    $result = mysqli_query($conn,"SELECT * FROM `kc_festival_message` where date >= '$now' AND deleted_at IS NULL  limit 0,4 ");
                                    if(mysqli_num_rows($result)>0){
                                       $Sl_No=1;
                                       $template= getSmsTemplates();
                                        while ($row = mysqli_fetch_assoc($result)){
                                               ?>
                                                <tr>

                                                    <td class='pickup'><span style='display:none'><?php echo $row['template_id'] ?></span><?php echo  $Sl_No  ; ?></td>
                                                    <td nowrap="nowrap"><?php echo date("d-m-Y",strtotime($row['date'])); ?></td>
                                                    <td><?php echo $template[$row['template_id']]; ?></td>
                                                    <td><a onclick="deleteRecord(this)" href="javascript:void(0)" data-id="<?php echo $row['id']; ?>" class="btn btn-xs btn-danger" data-toggle="tooltip" title="" data-original-title="Delete "><i class="fa fa-remove"></i></a>
                                                    </td>
                                                    <?php  $Sl_No++; }  ?>
                                                 </tr>
                                       <?php } else {?>
                                       <tr>
                                            <td colspan="9" align="center">
                                                <h4 class="text-red">No Festival Is Set</h4>
                                            </td>
                                        </tr>
                                      <?php } ?>
                                </table>
                          </div>
                      </div>
                      <div class="row">
                          <div class="col-sm-2"></div>
                           <div class="form-group col-sm-9 ui-widget">
                                  <label >Previous Events </label><br>
                              <table class="table table-striped table-hover table-bordered">
                                    <tr>
                                        <th>Sl No.</th>
                                        <th>Date</th>
                                        <th>Template</th>
                                    </tr>
                                    <?php 
                                        $now = date_create()->format('Y-m-d'); 
                                        $no_of_data = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM `kc_festival_message`  where date < '$now' "));
                                        $limit=4;
                                        $totalpages = ceil($no_of_data/$limit);
                                        if (isset($_GET['page'])) {
                                            $page = $_GET['page'];
                                            $page = $_GET['page'];
                                            $url .= '&page=' . $_GET['page'];
                                            } else{
                                            $page = 1;
                                            }
                            
                                        $start = ( $page-1 )*$limit ;
                                        
                                        $result = mysqli_query($conn, "SELECT * FROM `kc_festival_message` where date < '$now'  limit  $start , $limit ");
                                        if(mysqli_num_rows($result)>0){
                                        $Sl_No= $start+1;
                                        while ($row = mysqli_fetch_assoc($result)){
                                        ?>
                                    <tr >
                                        <td><?php echo  $Sl_No ; ?>.</td>
                                        <td nowrap="nowrap"><?php echo date("d-m-Y",strtotime($row['date'])); ?></td>
                                        <td><?php echo $template[$row['template_id']]; ?></td> <?php   $Sl_No++ ; } ?>  
                                    </tr>
                                    <?php } else {?>
                                        <tr>
                                                <td colspan="9" align="center">
                                                    <h4 class="text-red">No Festival Is Set</h4>
                                                </td>
                                            </tr>
                                            <?php } ?>
                              </table>
                               <?php if ($totalpages > 1) { ?>
                               <div class="box-footer clearfix">
                                   <ul class="pagination pagination-sm no-margin pull-right">
                                    <?php
                                     for ($i = 1; $i <= $totalpages; $i++) {
                                        ?>
                                        <li <?php if ((isset($_GET['page']) && $i == $_GET['page']) || (!isset($_GET['page']) && $i == 1)) { ?>class="active" <?php } ?>><a href="<?php echo $pagination_url ?>page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                                      <?php }  ?>
                                    </ul>
                               </div>
                               <?php } ?>
                            </div>   
                        </div>        
            </section>           
        </div> 
        <?php require('../includes/footer.php'); ?>
    </div> <!-- ./wrapper -->
    <?php require('../includes/common-js.php'); ?>  
    <script>
        $(document).ready(function(){
            $("form").on('submit',function(e){
                e.preventDefault();
            var dateGet = $('#datepicker').val();
            var status = $('#status').val();
            var tem = $('#template').val();
            var tem_id = $('#template optgroup').find('option[value="'+tem+'"]').data('template_id');
                // console.log(tem_id)
                if(dateGet !='' && tem !='' && status !=''){
                        $.ajax({
                            type: "POST",
                            url: "festival_messages.php",
                            data:{
                                date:dateGet,template:tem,status:status,template_id : tem_id,     
                            },
                            success:function(data){
                                // console.log(data );                             
                            location.reload();     
                            }
                        });
                    }
            });
            $('#datepicker').datepicker({
            startDate: new Date(),
            dateFormat:'MM-DD-YYYY',
            });
           
         });
         function deleteRecord(elem){
                // elem.preventDefault();
                let id = $(elem).data('id');
               if (confirm("Are You Sure")){
                //  console.log(id);
                    $.ajax({
                        url:'festival_messages.php',
                        type:'post',
                        data:{id:id},
                        success:function(resp){
                        location.reload();
                        // window.location.reload(true);
                        }
                    });
                }
            }
    </script> 
</body>
</html>        