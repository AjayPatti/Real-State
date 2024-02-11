<?php
ob_start();
session_start();

if(!isset($_GET['parent_id'])){
	exit();
}
$parent_id = $_GET['parent_id'];
// $dob = date("Y-m-d",strtotime($_POST['dob']));
require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$customer_details = (mysqli_query($conn,"select name,id,parent_id from kc_associates where parent_id = '".$parent_id."'")); // or
$associate=[];
while($row = mysqli_fetch_assoc($customer_details)){
    $associate[] = $row;
} ;
// print_r($customer_details);
echo  json_encode($associate);
?>