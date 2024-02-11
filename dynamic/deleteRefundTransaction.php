<?php
    ob_start();
    session_start();

    if(!isset($_POST['refund_id']) || !is_numeric($_POST['refund_id']) || !($_POST['refund_id'] > 0)){
    	exit();
    }

    require("../includes/host.php");
    require("../includes/kc_connection.php");
    require("../includes/common-functions.php");
    $refund_id = $_POST['refund_id'];


    mysqli_query($conn,"update kc_refund_amount set deleted ='".date('Y-m-d H:i:s')."', deleted_by = '".$_SESSION['login_id']."' where id = '".$refund_id."'   ");
    echo "1"; die;
?>