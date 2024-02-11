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

	$farmers_rs = mysqli_query($conn,"select id, name_title, name, mobile from kc_farmers where name LIKE '%".$term."%' and deleted is null limit 0,20 ");
}else if(substr($term,0,2) == "m-"){
	$term = substr($term,0,2);
	$farmers_rs = mysqli_query($conn,"select id, name_title, name, mobile from kc_farmers where mobile LIKE '%".$term."%' and deleted is null limit 0,20 ");
}else if(substr($term,0,2) == "c-"){
	$term = (int) substr($term,0,2);
	$farmers_rs = mysqli_query($conn,"select id, name_title, name, mobile from kc_farmers where id = '".$term."' and deleted is null limit 0,1 ");
}else{
	$farmers_rs = mysqli_query($conn,"select id, name_title, name, mobile from kc_farmers where (name LIKE '%".$term."%' or mobile LIKE '%".$term."%') or (id = '".(int) $term."') and deleted is null limit 0,20 ");
}
$farmers = array();
$counter = 0;
while($farmer = mysqli_fetch_assoc($farmers_rs)){
  $farmers[$counter]['id'] = $farmer['id'];
  $farmers[$counter]['value'] = farmerID($farmer['id']).'-'.$farmer['name_title'].' ' .$farmer['name'].'('.$farmer['mobile'].')';
  $farmers[$counter]['label'] = farmerID($farmer['id']).'-'.$farmer['name_title'].' ' .$farmer['name'].'('.$farmer['mobile'].')';

//   $farmer_block_numbers = getfarmerBlockNumberNames($conn, $farmer['id']);
//   foreach($farmer_block_numbers as $block_name){
//   	$farmers[$counter]['label'] .= '<h6>'.$block_name.'</h6>';
//   }

  $counter++;
}
echo json_encode($farmers); die;
?>
