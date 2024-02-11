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

/*
if(isset($_GET['search_for']) && in_array($_GET['search_for'],['name','code','mobile'])){
	if($_GET['search_for'] == "name"){
		$associates_rs = mysqli_query($conn,"select id, code, name, mobile_no from kc_associates where name LIKE '%".$term."%' limit 0,20 ");
	}else if($_GET['search_for'] == "code"){
		$associates_rs = mysqli_query($conn,"select id, code, name, mobile_no from kc_associates where code LIKE '%".$term."%' limit 0,20 ");
	}else{
		$associates_rs = mysqli_query($conn,"select id, code, name, mobile_no from kc_associates where mobile_no LIKE '%".$term."%' limit 0,20 ");
	}
}else{
	$associates_rs = mysqli_query($conn,"select id, code, name, mobile_no from kc_associates where name LIKE '%".$term."%' or code LIKE '%".$term."%' or mobile_no LIKE '%".$term."%' limit 0,20 ");
}*/

if(isset($_GET['term'])){
	$term = $_GET['term'];
	$associates_rs = mysqli_query($conn,"select * from kc_projects where name LIKE '%".$term."%' And status ='1' limit 0,20 ");

}
$associates = array();
$counter = 0;
while($associate = mysqli_fetch_assoc($associates_rs)){
  $associates[$counter]['id'] = $associate['id'];
  $associates[$counter]['value'] = $associate['name'];
  $counter++;
}
echo json_encode($associates); die;
?>