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

$name =$_POST['name'];
$mobile =$_POST['mobile'];
$visit_date =$_POST['visit_date'];
$associate_name =$_POST['associate_name'];
$associate_id =$_POST['associate_id'];
$project_id =$_POST['project_id'];
$sector =$_POST['sector'];

$visit = "INSERT INTO `kc_visit_forms`(`associate_id`, `name`, `mobile`, `visit_datetime`, `project_id`, `block_id`, `status`, `ipaddress`) VALUES ('$associate_id','$name','$mobile','$visit_date','$project_id','1','1','1')";
// print_r($visit);
 $visitReasult = mysqli_query($conn,$visit);
 $visitId = mysqli_insert_id($conn);
 if($visitReasult !=false){
   mysqli_query($conn,"insert into kc_contacts set name = '$name', mobile = '$mobile', type = 'visit', customer_id = '$visitId', status = '1', created ='".date('Y-m-d H:i:s')."', created_by = '".$_SESSION['login_id']."' ");
   echo json_encode("success");
   }else{
      echo json_encode("error");
   }



?>