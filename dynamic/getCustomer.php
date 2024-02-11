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
	$customers_rs = mysqli_query($conn,"select id, name_title, name, mobile from kc_customers where name LIKE '%".$term."%' limit 0,20 ");
}else if(substr($term,0,2) == "m-"){
	$term = substr($term,0,2);
	$customers_rs = mysqli_query($conn,"select id, name_title, name, mobile from kc_customers where mobile LIKE '%".$term."%' limit 0,20 ");
}else if(substr($term,0,2) == "c-"){
	$term = (int) substr($term,0,2);
	$customers_rs = mysqli_query($conn,"select id, name_title, name, mobile from kc_customers where id = '".$term."' limit 0,1 ");
}else{
	$customers_rs = mysqli_query($conn,"select id, name_title, name, mobile from kc_customers where (name LIKE '%".$term."%' or mobile LIKE '%".$term."%') or (id = '".(int) $term."') limit 0,20 ");
}
$customers = array();
$counter = 0;
while($customer = mysqli_fetch_assoc($customers_rs)){
  $customers[$counter]['id'] = $customer['id'];
  $customers[$counter]['value'] = customerID($customer['id']).'-'.$customer['name_title'].' ' .$customer['name'].'('.$customer['mobile'].')';
  $customers[$counter]['label'] = customerID($customer['id']).'-'.$customer['name_title'].' ' .$customer['name'].'('.$customer['mobile'].')';

  $customer_block_numbers = getCustomerBlockNumberNames($conn, $customer['id']);
  foreach($customer_block_numbers as $block_name){
  	$customers[$counter]['label'] .= '<h6>'.$block_name.'</h6>';
  }

  $counter++;
}
echo json_encode($customers); die;
?>