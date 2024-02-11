<?php
ob_start();
session_start();


if(!isset($_POST['term']) || $_POST['term'] == ""){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$cust = $_POST['term'];

$custArray = array();
$block_numbers = mysqli_query($conn,"select id,block_number from kc_block_numbers where block_number = '".$cust."' ");
while($block_number = mysqli_fetch_assoc($block_numbers)){
	// $customerd = mysqli_query($conn,"select c.id, CONCAT(c.name_title, c.name, '-', c.mobile) as label, CONCAT(c.name_title, c.name, '-', c.mobile) as value, p.name as project_name, kc_customer_blocks.block_id,b.name as block_name,bn.block_number as block_number from kc_customer_blocks cb left join kc_blocks b on b.id = kc_customer_blocks.block_id left join kc_projects p on p.id = b.project_id left join kc_customers c on c.id = cb.customer_id LEFT JOIN kc_blocks b ON b.id = cb.block_id LEFT JOIN kc_block_numbers bn ON bn.id = cb.block_number_id where block_number_id = '".$block_number['id']."' limit 0,1 ");
	$custid = mysqli_fetch_assoc(mysqli_query($conn,"select customer_id from kc_customer_blocks where block_number_id = '".$block_number['id']."' limit 0,1 "));
	$customer = mysqli_fetch_assoc(mysqli_query($conn,"select id, CONCAT(name_title, name, '-', mobile) as label, CONCAT(name_title, name, '-', mobile) as value from kc_customers where id = '".$custid['customer_id']."' limit 0,1 "));

	$blocks = mysqli_query($conn,"select b.name as block_name,bn.block_number as block_number from kc_customer_blocks cb LEFT JOIN kc_blocks b ON b.id = cb.block_id LEFT JOIN kc_block_numbers bn ON bn.id = cb.block_number_id where customer_id = '".$customer['id']."'  ");
	if(mysqli_num_rows($blocks)){


		$customer['plot_detail'] = array();
		while($block = mysqli_fetch_assoc($blocks)){
			$customer['plot_detail'][] = $block['block_name'].' - '.$block['block_number'];
		}
		$custArray[] = $customer;
	}
}
// $custArray = array();
$customers = mysqli_query($conn,"select id, CONCAT(name_title, name, '-', mobile) as label, CONCAT(name_title, name, '-', mobile) as value from kc_customers where name LIKE '%".$cust."%' limit 0,50 ");
while($customer = mysqli_fetch_assoc($customers)){
	$blocks = mysqli_query($conn,"select b.name as block_name,bn.block_number as block_number from kc_customer_blocks cb LEFT JOIN kc_blocks b ON b.id = cb.block_id LEFT JOIN kc_block_numbers bn ON bn.id = cb.block_number_id where customer_id = '".$customer['id']."'  ");
	$customer['plot_detail'] = array();
	while($block = mysqli_fetch_assoc($blocks)){
		$customer['plot_detail'][] = $block['block_name'].' - '.$block['block_number'];
	}
	$custArray[] = $customer;
}
echo json_encode($custArray); die;
?>