<?php
	ob_start();
	session_start();

	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/checkAuth.php");

	header("Content-Type: application/xls");    
	header("Content-Disposition: attachment; filename=Refund Plots Reports-".date('d-M-y').".xls");  
	header("Pragma: no-cache"); 
	header("Expires: 0");

	$query =  "select cbh.id as customer_block_id,cbh.id,cbh.deleted, cbh.customer_id, cbh.block_id, cbh.block_number_id, cbh.registry, cbh.registry_date, cbh.registry_by, cbh.associate,b.project_id, b.name as block_name, bn.block_number as block_number_name, bn.area, p.name as project_name, c.name_title as customer_name_title, c.name as customer_name, c.mobile as customer_mobile, c.address as customer_address,a.code as associate_code, a.name as associate_name from kc_customer_blocks_hist cbh LEFT JOIN kc_blocks b ON cbh.block_id = b.id LEFT JOIN kc_block_numbers bn ON cbh.block_number_id = bn.id LEFT JOIN kc_projects p ON b.project_id = p.id LEFT JOIN kc_customers c ON cbh.customer_id = c.id LEFT JOIN kc_associates a ON cbh.associate = a.id where cbh.status = '1' AND cbh.action_type = 'Cancel Booking'";

	if(isset($_GET['search_project']) || isset($_GET['search_block']) || isset($_GET['search_block_no']) || (isset($_GET['search_block_no']) && $_GET['search_block_no']>0))
	 	{
	 		if(isset($_GET['search_block']) && $_GET['search_block']!=''){
				$query .= " and cbh.block_id = '".$_GET['search_block']."' ";
				//$url .= '&search_block='.$_GET['search_block'];
			}
			//echo $query;die();

			if(isset($_GET['search_block_no']) && $_GET['search_block_no']!=''){
				$query .= " and cbh.block_number_id = '".$_GET['search_block_no']."'";
				//$url .= '&search_block_no='.$_GET['search_block_no'];
			}
			//echo $query;die();
			if(isset($_GET['search_project']) && is_array($_GET['search_project']) && sizeof($_GET['search_project'])>0){
				$query .= " and cbh.block_id IN (select id from kc_blocks where status = '1' and project_id IN ('".implode("','",$_GET['search_project'])."') )";
				foreach($_GET['search_project'] as $project_id){
					//$url .= '&search_project[]='.$project_id;
					//$search_project_url_string .= '&search_project[]='.$project_id;
				}
	 		}
			 if (isset($_GET['datesearch']) && $_GET['datesearch'] != '') {
				$ddatesearch = explode('-', $_GET['datesearch']);
		
				$startdate = date('Y-m-d 00:00:01', strtotime($ddatesearch[0]));
				$enddate = date('Y-m-d 23:59:59', strtotime($ddatesearch[1]));
				$query .= "and cbh.addedon between '$startdate' and '$enddate' ";
			}
 		$query .= "order by id desc";
 		$customers = mysqli_query($conn,$query);

		$search = true;
	 	}
	 	
	 	if($search && mysqli_num_rows($customers) > 0){
	 		echo '<table border="1">';
		//make the column headers what you want in whatever order you want
			echo '<tr><th>Sr.</th><th>Project</th><th>Block</th><th>Plot No.</th><th>Customer Name</th><th>Customer Mobile</th><th>Customer Address</th><th>Final Plot Amount</th><th>Deposited Amount</th><th>Refund Amount</th><th>Refund Date</th><th>Pending Refund Amount</th><th>Date of Cancellation</th></tr>';

			$counter = 1;
			$total_debited_amt = $total_credited_amt = $total_pending_amt = 0;
			while($customer = mysqli_fetch_assoc($customers)){
				//print_r($customer);
				$total_amount_details = mysqli_fetch_assoc(mysqli_query($conn,"select sum(amount) as total_amount from kc_customer_transactions_hist where customer_id = '".$customer['customer_id']."' and block_id = '".$customer['block_id']."' and block_number_id = '".$customer['block_number_id']."' and cr_dr = 'cr' and status = '1' and action_type = 'Cancel Booking'"));
								$total_amount = $total_amount_details['total_amount']?$total_amount_details['total_amount']:0;

								$total_paid_details = mysqli_fetch_assoc(mysqli_query($conn,"select sum(amount) as total_paid from kc_customer_transactions_hist where customer_id = '".$customer['customer_id']."' and block_id = '".$customer['block_id']."' and block_number_id = '".$customer['block_number_id']."' and cr_dr = 'dr' and status = '1' and remarks is NULL and action_type = 'Cancel Booking'"));
								$total_paid = $total_paid_details['total_paid']?$total_paid_details['total_paid']:0;

								$total_discount_details = mysqli_fetch_assoc(mysqli_query($conn,"select sum(amount) as total_discount from kc_customer_transactions_hist where customer_id = '".$customer['customer_id']."' and block_id = '".$customer['block_id']."' and block_number_id = '".$customer['block_number_id']."' and cr_dr = 'dr' and status = '1' and remarks is NOT NULL and action_type = 'Cancel Booking'"));
								$total_discount = $total_discount_details['total_discount']?$total_discount_details['total_discount']:0;

								$final_amount = $total_amount - $total_discount;
								//print_r($customer);
								$refund_date = mysqli_fetch_assoc(mysqli_query($conn,"select refund_date from kc_refund_amount where customer_id = '".$customer['customer_id']."' and block_id = '".$customer['block_id']."' and block_number_id = '".$customer['block_number_id']."' and deleted is null"));
								$total_refund = mysqli_fetch_assoc(mysqli_query($conn,"select sum(amount) as total_refunded from kc_refund_amount where customer_id = '".$customer['customer_id']."' and block_id = '".$customer['block_id']."' and block_number_id = '".$customer['block_number_id']."' and deleted is null"));
								 $total_refunded = $total_refund['total_refunded']?$total_refund['total_refunded']:0;
								
								 $pending_amount = ($total_paid-$total_refunded);
								 if(date("Y-m-d",strtotime($refund_date['refund_date'])) !='1970-01-01'){
								 	$refundDate = date("d-m-Y",strtotime($refund_date['refund_date']));
								 }else{
								 	$refundDate = '';
								 }
								 

				echo "<tr><td>".$counter."</td><td>".$customer['project_name']."</td><td>".$customer['block_name']."</td><td>".$customer['block_number_name']."</td><td>".$customer['customer_name_title'].' ' .$customer['customer_name'].' ('.customerID($customer['customer_id']).')'."</td><td>".$customer['customer_mobile']."</td><td>".$customer['customer_address']."</td><td>". $final_amount."</td><td>".$total_paid."</td><td>".($total_refunded?$total_refunded:0)."</td><td>".($refundDate)."</td><td>".($pending_amount?$pending_amount:0)."</td><td>".date("d M Y h:i A",strtotime($customer['deleted']))."</td></tr>";
				
				 
		    $counter++;

			}
	 	}
	 	//$customer['customer_address']
	 	//$final_amount

?>

