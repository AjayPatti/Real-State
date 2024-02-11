<?php
ob_start();
session_start();

if(!isset($_GET['term']) || strlen($_GET['term']) < 3){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$term = $_GET['term'];

	
if(substr($term,0,2) == "n-"){
	$term = substr($term,2,strlen($term));
	$associates_rs = mysqli_query($conn,"select id, code, name, mobile_no from kc_associates where name LIKE '%".$term."%' limit 0,20 ");
}else if(substr($term,0,2) == "m-"){
	$term = substr($term,2,strlen($term));
	$associates_rs = mysqli_query($conn,"select id, code, name, mobile_no from kc_associates where mobile_no LIKE '%".$term."%' limit 0,20 ");
}else if(substr($term,0,2) == "c-"){
	$term = substr($term,2,strlen($term));
	$associates_rs = mysqli_query($conn,"select id, code, name, mobile_no from kc_associates where code LIKE '%".$term."%' limit 0,20 ");
}else{
	$associates_rs = mysqli_query($conn,"select id, code, name, mobile_no from kc_associates where name LIKE '%".$term."%' or code LIKE '%".$term."%' or mobile_no LIKE '%".$term."%' limit 0,20 ");
}



$associates = array();
$counter = 0;
while($associate = mysqli_fetch_assoc($associates_rs)){
  $associates[$counter]['id'] = $associate['id'];
  $associates[$counter]['text'] = $associate['code'].'-'.$associate['name'].'('.$associate['mobile_no'].')';
  $counter++;
}
echo json_encode(['items'=>$associates]); die;
?>