z<?php
	ob_start();
	session_start();

	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/checkAuth.php");

	header("Content-Type: application/xls");    
	header("Content-Disposition: attachment; filename=report-".date('d-M-y').".xls");  
	header("Pragma: no-cache"); 
	header("Expires: 0");

	$query = "select cbh.id as customer_block_id, cbh.customer_id, cbh.block_id, cbh.block_number_id, cbh.registry, cbh.registry_date, cbh.registry_by, cbh.associate,b.project_id, b.name as block_name, bn.block_number as block_number_name, bn.area, p.name as project_name, c.name_title as customer_name_title, c.name as customer_name, c.mobile as customer_mobile, c.address as customer_address,a.code as associate_code, a.name as associate_name from kc_customer_blocks_hist cbh LEFT JOIN kc_blocks b ON cbh.block_id = b.id LEFT JOIN kc_block_numbers bn ON cbh.block_number_id = bn.id LEFT JOIN kc_projects p ON b.project_id = p.id LEFT JOIN kc_customers c ON cbh.customer_id = c.id LEFT JOIN kc_associates a ON cbh.associate = a.id where cbh.status = '1' AND cbh.action_type = 'Cancel Booking' ";
	


	$customers = mysqli_query($conn,$query);

	if(mysqli_num_rows($customers) > 0){

		echo '<table border="1">';
		//make the column headers what you want in whatever order you want
		echo '<tr><th>Sr.</th><th>Project</th><th>Block</th><th>Plot No.</th><th>Customer Name</th><th>Customer Mobile</th><th>Customer Address</th><th>Date of Booking</th><th>Rate</th><th>Area</th><th>Amount</th><th>Received Amount</th><th>Pending Amount</th></tr>';
		//loop the query data to the table in same order as the headers
		$counter = 1;
		$total_debited_amt = $total_credited_amt = $total_pending_amt = $total_debited = $total_credited = $pending_amount = 0;
		while($customer = mysqli_fetch_assoc($customers)){
			$total_debited_amt += $total_debited = totalDebited($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']);
			$total_credited_amt += $total_credited = totalCredited($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']);
			
			$total_pending_amt += $pending_amount = ($total_credited - $total_debited);

			if($customer['registry'] == "yes"){
				$registry = date("d-m-Y",strtotime($customer['registry_date']));
				$registry_by = $customer['registry_by'];
			}else{
				$registry = $registry_by = "-";
			}
			
				$rate = ratePerSq($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']);
			if($customer['area'] == ''){
				$rate = 0;
			}

		    echo "<tr><td>".$counter."</td><td>".$customer['project_name']."</td><td>".$customer['block_name']."</td><td>".$customer['block_number_name']."</td><td>".$customer['customer_name_title'].' ' .$customer['customer_name'].' ('.customerID($customer['customer_id']).')'."</td><td>".$customer['customer_mobile']."</td><td>".$customer['customer_address']."</td><td>".date("d-m-Y",strtotime(dateOfBooking($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id'])))."</td><td>".$rate."</td><td>".$customer['area']."</td><td>".number_format($total_credited,2)."</td><td>".number_format($total_debited,2)."</td><td>".number_format($pending_amount,2)."</td></tr>";
		    $counter++;
		}
	}
?>