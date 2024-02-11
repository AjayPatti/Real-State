<?php
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

	$query = "select cb.id as customer_block_id, cb.customer_id, cb.block_id, cb.block_number_id, cb.registry, cb.registry_date, cb.registry_by, cb.associate,b.project_id, b.name as block_name, bn.block_number as block_number_name, bn.area, p.name as project_name, c.name_title as customer_name_title, c.name as customer_name, c.mobile as customer_mobile, c.address as customer_address,a.code as associate_code, a.name as associate_name, cfu.next_follow_up_date, cfu.remarks from kc_customer_blocks cb LEFT JOIN kc_blocks b ON cb.block_id = b.id LEFT JOIN kc_block_numbers bn ON cb.block_number_id = bn.id LEFT JOIN kc_projects p ON b.project_id = p.id LEFT JOIN kc_customers c ON cb.customer_id = c.id LEFT JOIN kc_associates a ON cb.associate = a.id left join kc_customer_follow_ups cfu ON cb.customer_id=cfu.customer_id AND cb.block_id=cfu.block_id AND cb.block_number_id=cfu.block_number_id where cb.status = '1' ";
	if(isset($_GET['search_project']) || isset($_GET['search_block']) || isset($_GET['search_block_no']) || (isset($_GET['search_block_no']) && $_GET['search_block_no']>0) || (isset($_GET['search_associate']) && (int) $_GET['search_associate'] > 0)){  
		//echo "<pre>"; print_r($_GET); die;
		if(isset($_GET['search_block']) && $_GET['search_block']!=''){
			$query .= " and cb.block_id = '".$_GET['search_block']."'";
		}

		if(isset($_GET['search_block_no']) && $_GET['search_block_no']!=''){
			$query .= " and cb.block_number_id = '".$_GET['search_block_no']."'";
		}

		//echo sizeof($_GET['search_project']); die;
		if(isset($_GET['search_project']) && is_array($_GET['search_project']) && sizeof($_GET['search_project'])>0){
			$query .= " and cb.block_id IN (select id from kc_blocks where status = '1' and project_id IN ('".implode("','",$_GET['search_project'])."') )";
		}

		if(isset($_GET['search_associate']) && (int) $_GET['search_associate'] > 0){
			$search_associates = implode(",", $_GET['search_associate']);
			// print_r($search_associates);die();
			//$associate_id = (int) $search_associates;
			$query .= " and cb.associate IN ($search_associates)";
		}

		if(isset($_GET['datesearch'])&& $_GET['datesearch']!=''){
			$ddatesearch = explode('-',$_GET['datesearch']);
			
				$startdate = date('Y-m-d 00:00:01',strtotime($ddatesearch[0]));
				$enddate = date('Y-m-d 23:59:59',strtotime($ddatesearch[1]));
			$query .= "and cb.addedon between '$startdate' and '$enddate' ";
			
		}
		
		$query .= " order by b.name, cast(bn.block_number as unsigned)";

		$customers = mysqli_query($conn,$query);
		$search = true;
	}

	if($search && mysqli_num_rows($customers) > 0){

		echo '<table border="1">';
		//make the column headers what you want in whatever order you want
		echo '<tr><th>Sr.</th><th>Project</th><th>Block</th><th>Plot No.</th><th>Customer Name</th><th>Customer Mobile</th><th>Customer Address</th><th>Date of Booking</th><th>Registry</th><th>Registry By</th><th>Associate Code</th><th>Associate</th><th>Rate</th><th>Area</th><th>Amount</th><th>Received Amount</th><th>Pending Amount</th><th>Last Paid Amount</th><th>Last Paid Date</th><th>Next Follow Date</th><th>Remarks</th></tr>';
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
			
			$last_payment_detail =  getLastPayment($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']);

			$amount_last_payment_detail = '';
			$paid_date_last_payment_detail = '';
			if(!empty($last_payment_detail['amount'])){
				$amount_last_payment_detail = $last_payment_detail['amount'];
			}
			else{
				$amount_last_payment_detail = 00.00;
			}
			if(!empty($last_payment_detail['paid_date'])){
				$paid_date_last_payment_detail = $last_payment_detail['paid_date'];
			}
			else{
				$paid_date_last_payment_detail = '';
			}
		    echo "<tr><td>".$counter."</td><td>".$customer['project_name']."</td><td>".$customer['block_name']."</td><td>".$customer['block_number_name']."</td><td>".$customer['customer_name_title'].' ' .$customer['customer_name'].' ('.customerID($customer['customer_id']).')'."</td><td>".$customer['customer_mobile']."</td><td>".$customer['customer_address']."</td><td>".date("d-m-Y",strtotime(dateOfBooking($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id'])))."</td><td>".$registry."</td><td>".$registry_by."</td><td>".$customer['associate_code']."</td><td>".$customer['associate_name']."</td><td>".number_format(ratePerSq($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']),2)."</td><td>".$customer['area']."</td><td>".number_format($total_credited,2)."</td><td>".number_format($total_debited,2)."</td><td>".number_format($pending_amount,2)."</td><td>".$amount_last_payment_detail."</td><td>".$paid_date_last_payment_detail."</td><td>".$customer['next_follow_up_date']."</td><td>".$customer['remarks']."</td></tr>";
		    $counter++;
		}
		echo "<tr><td colspan='13' align='right'>Total</td><td>".number_format($total_credited_amt,2)."</td><td>".number_format($total_debited_amt,2)."</td><td>".number_format($total_pending_amt,2)."</td></tr></table>";
	}
?>