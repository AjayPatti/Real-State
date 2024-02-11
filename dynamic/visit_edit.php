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
if(isset($_POST['editInformation'])){

    $id =$_POST['id'];
    $name =$_POST['name'];
    $mobile =$_POST['mobile'];
    $visit_date =$_POST['visit_date'];
    $associate_name =$_POST['associate_name'];
    $associate_id =$_POST['associate_id'];
    $project_id = $_POST['project_id'];
   // $sector =isset($_POST['sector'])?? '0';
    $visit ="UPDATE `kc_visit_forms` SET `associate_id`='$associate_id',`name`='$name',`mobile`='$mobile',`visit_datetime`='$visit_date',`project_id`='$project_id',`block_id`='0' WHERE id = '$id'";
     $visitReasult = mysqli_query($conn,$visit);
     if($visitReasult !=false){
        mysqli_query($conn,"update kc_contacts set name = '$name', mobile = '$mobile' where customer_id = '$id' and type = 'visit' ");
        $_SESSION['success'] = 'Updated Successfully done!';
        header("Location:../management/visit_forms.php");
        exit();
     }else{
        $_SESSION['error'] = ' Please Try Again!';
     }

}

// print_r($_POST);die;
if(isset($_POST['delete']) && isset($_POST['visitid'])){
    $id = $_POST['visitid'];
    $currentDate = date('Y-m-d');
    $visit ="UPDATE `kc_visit_forms` SET `deleted_at`='$currentDate' WHERE id = '$id'";
    // print_r($visit);die;
    $visitReasult = mysqli_query($conn,$visit);
    if($visitReasult == true){
        echo "1";die;
    }else{
        echo "0";
    }
}



?>