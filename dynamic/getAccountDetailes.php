<?php
ob_start();
session_start();

if(!isset($_GET['term'])){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");

$term = $_GET['term'];

if(substr($term,0,2) == "n-"){
	$term = substr($term,0,2);
	$associates_rs = mysqli_query($conn,"select id, account_no, name, bank_name from kc_accounts where name LIKE '%".$term."%' limit 0,20 ");
}else if(substr($term,0,2) == "m-"){
	$term = substr($term,0,2);
	$associates_rs = mysqli_query($conn,"select id, account_no, name, bank_name from kc_accounts where account_no LIKE '%".$term."%' limit 0,20 ");
}else if(substr($term,0,2) == "c-"){
	$term = substr($term,0,2);
	$associates_rs = mysqli_query($conn,"select id, account_no, name, bank_name from kc_accounts where bank_name LIKE '%".$term."%' limit 0,20 ");
}else{
	$associates_rs = mysqli_query($conn,"select id, account_no, name, bank_name from kc_accounts where name LIKE '%".$term."%' or bank_name LIKE '%".$term."%' or account_no LIKE '%".$term."%' limit 0,20 ");
}
// print_r("select id, account_no, name, bank_name from kc_accounts where name LIKE '%".$term."%' limit 0,20 ");die;
$associates = array();
$counter = 0;
while($associate = mysqli_fetch_assoc($associates_rs)){
  $associates[$counter]['id'] = $associate['id'];
  $associates[$counter]['value'] = $associate['name'].'-'.$associate['bank_name'].'('.$associate['account_no'].')';
  $counter++;
}
echo json_encode($associates); die;
?>