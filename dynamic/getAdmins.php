<?php
ob_start();
session_start();

if(!isset($_GET['term']) ){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");

$term = $_GET['term'];

if($term != NULL){
	// echo "scfas";die;
	$admins = mysqli_query($conn,"select id, email, name, mobile from kc_login where name LIKE '%".$term."%' or mobile LIKE '%".$term."%' limit 0,20 ");
	// echo $admins; die;
}
$admin_rs = array();
$counter = 0;
while($admin = mysqli_fetch_assoc($admins)){
  $admin_rs[$counter]['id'] = $admin['id'];
  $admin_rs[$counter]['value'] = $admin['name'].'('.$admin['mobile'].')';
  $counter++;
}
echo json_encode($admin_rs); die;
?>