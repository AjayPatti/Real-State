<?php
ob_start();
session_start();

if(!isset($_POST['name']) || $_POST['name'] == "" || !isset($_POST['dob']) || date("Y-m-d",strtotime($_POST['dob'])) == "1970-01-01" ){
	exit();
}
$name = $_POST['name'];
$dob = date("Y-m-d",strtotime($_POST['dob']));
require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_customers where name = '".$name."' and dob = '$dob' limit 0,1 ")); // or 
// print_r($customer_details);
if(isset($customer_details['id'])){
  echo "1"; die;
}
echo "0"; die;
?>