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

if(substr($term,0,2) == "n-"){
	$term = substr($term,0,2);
	$contacts_rs = mysqli_query($conn,"select id, name, mobile from kc_contacts where name LIKE '%".$term."%' limit 0,20 ");
}else if(substr($term,0,2) == "m-"){
	$term = substr($term,0,2);
	$contacts_rs = mysqli_query($conn,"select id, name, mobile from kc_contacts where mobile LIKE '%".$term."%' limit 0,20 ");
}else if(substr($term,0,2) == "c-"){
	$term = (int) substr($term,0,2);
	$contacts_rs = mysqli_query($conn,"select id, name, mobile from kc_contacts where id = '".$term."' limit 0,1 ");
}else{
	$contacts_rs = mysqli_query($conn,"select id, name, mobile from kc_contacts where (name LIKE '%".$term."%' or mobile LIKE '%".$term."%') or (id = '".(int) $term."') limit 0,20 ");
}
$contacts = array();
$counter = 0;
while($contact = mysqli_fetch_assoc($contacts_rs)){
  $contacts[$counter]['id'] = $contact['id'];
  $contacts[$counter]['value'] = customerID($contact['id']).'-'.$contact['name'].'('.$contact['mobile'].')';
  $contacts[$counter]['label'] = customerID($contact['id']).'-'.$contact['name'].'('.$contact['mobile'].')';

  $customer_block_numbers = getCustomerBlockNumberNames($conn, $contact['id']);
  foreach($customer_block_numbers as $block_name){
  	$contacts[$counter]['label'] .= '<h6>'.$block_name.'</h6>';
  }

  $counter++;
}
echo json_encode($contacts); die;
?>