<?php
	ob_start();
	session_start();
	error_reporting(0);
	// ini_set("display_errors",0);


	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/checkAuth.php");

	header("Content-Type: application/xls");    
	header("Content-Disposition: attachment; filename=day_book-".date('d-M-y').".xls");  
	header("Pragma: no-cache"); 
	header("Expires: 0");

	$search = false;
	$query = "select * from kc_customer_transactions where status = '1' and cr_dr = 'dr' and remarks is NULL and is_affect_sold_amount != '1' ";	// and remarks is NULL->to remove discount
	$abr_receipt = "select name,project_block_plotnumber_totalarea,paid_amount,bank_name,cheque_dd_number,addedon,
	payment_type,add_transaction_remarks from kc_avr_receipt where status = '1' and deleted is null";
	// echo $abr_receipt;die;

	if((isset($_GET['from_date']) && isset($_GET['to_date'])) || (isset($_GET['associate']) && $_GET['associate']>0) || (isset($_GET['addedby']) && $_GET['addedby']>0) ){ 
		// echo "<pre>"; print_r($_GET); die;
		if(isset($_GET['from_date']) && isset($_GET['to_date'])){
			$query .= " and paid_date BETWEEN '".date("Y-m-d",strtotime($_GET['from_date']))."' AND '".date("Y-m-d",strtotime($_GET['to_date']))."'";
			$abr_receipt .= " and addedon BETWEEN '".date("Y-m-d",strtotime($_GET['from_date']))."' AND '".date("Y-m-d",strtotime($_GET['to_date']))."'";
		}

		if(isset($_GET['associate']) && $_GET['associate']>0){
			$query .= " and block_number_id IN (select block_number_id from kc_customer_blocks where status = '1' and associate = '".$_GET['associate']."') ";
		}

		if(isset($_GET['addedby']) && $_GET['addedby']>0){
			$query .= " and added_by = '".$_GET['addedby']."' ";
			
		}
		$query .= " order by paid_date asc";
		//print_r($query);die;
		$transactions = mysqli_query($conn,$query);
		// echo $query;die;
		$abr_r = mysqli_query($conn,$abr_receipt);
		$search = true;
	}else{
		$query .= " order by paid_date asc";
		$transactions = mysqli_query($conn,$query);
		// echo $query;die;
		$abr_r = mysqli_query($conn,$abr_receipt);
		$search = true;
	}

	if($search && mysqli_num_rows($transactions) > 0){

		echo '<table border="1">';
		//make the column headers what you want in whatever order you want
		echo '<tr><th>Sr.</th><th>Block</th><th>Plot No.</th><th>Area</th><th>Customer</th><th>Associate</th><th>Amount Received</th><th>Payment Mode</th><th>Bank Name</th><th>Cheque/DD/NEFT/RTGS Number</th><th>Type of Payment</th><th>Date</th><th>Added By</th></tr>';
		//loop the query data to the table in same order as the headers
		$counter = 1;
		$totalAmountReceived = $totalPendingAmount = 0;
		while($transaction = mysqli_fetch_assoc($transactions)){
			// echo print_r($transaction);die;
			$block = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_customer_blocks where block_id = '".$transaction['block_id']."' and block_number_id = '".$transaction['block_number_id']."' limit 0,1 "));
			// echo print_r($block);die;
			$addedBy = mysqli_fetch_assoc(mysqli_query($conn,'select name from kc_login where id = "'.$transaction['added_by'].'"'));
			//echo "<pre>"; print_r($transaction);
			$total_debited = totalDebited($conn,$block['customer_id'],$block['block_id'],$block['block_number_id']);
			$total_credited = totalCredited($conn,$block['customer_id'],$block['block_id'],$block['block_number_id']);

			$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select area from kc_block_numbers where block_id = '".$block['block_id']."' and id = '".$block['block_number_id']."' and status = '1' "));
			
			$totalAmountReceived += $transaction['amount'];

			$pending_amount = ($total_credited - $total_debited);
			$totalPendingAmount += $pending_amount;

		    echo "<tr><td>".$counter."</td><td>".blockName($conn,$transaction['block_id'])."</td><td>".blockNumberName($conn,$transaction['block_number_id'])."</td><td>".$block_details['area']."Sq. Ft.</td><td>".customerName($conn,$transaction['customer_id']).' ('.customerID($transaction['customer_id']).')'."</td><td>".associateName($conn,$block['associate'])."</td><td>".(int) $transaction['amount']."</td><td>".$transaction['payment_type'];

		    if($transaction['payment_type'] == "Cheque" || $transaction['payment_type'] == "DD" || $transaction['payment_type'] == "NEFT" || $transaction['payment_type'] == "RTGS"){
                echo "<td>".$transaction['bank_name']."</td>";
                echo "<td>".$transaction['cheque_dd_number']."</td>";
            }else{
            	echo "<td></td><td></td>";
            }
		    if(trim($transaction['add_transaction_remarks']) != ''){
		    	echo "<td>".$transaction['add_transaction_remarks']."</td>";
		    }else{
		    	echo "<td></td>";
		    }

		    echo "<td>".date("d M Y",strtotime($transaction['paid_date']))."</td><td>".$addedBy['name']."</td></tr>";
		    $counter++;
		}

		if($search && mysqli_num_rows($abr_r) > 0){
			while($abr = mysqli_fetch_array($abr_r)){
				// echo  "<pre>";print_r($abr);die;
				echo "<tr><td>".$counter."</td><td colspan='3'>".$abr['project_block_plotnumber_totalarea']."Sq.Ft</td><td>".$abr['name']."</td><td>".'AVR'."</td><td>".$abr['paid_amount']."</td><td>".$abr['payment_type']."</td><td>".$abr['bank_name']."</td><td>".$abr['cheque_dd_number']."</td><td>".$abr['add_transaction_remarks']."</td><td>".date('jM Y',strtotime($abr['addedon']))."</td></tr>";	
			$counter++;
			}	

		$abr = mysqli_fetch_array(mysqli_query($conn,"SELECT sum(paid_amount) AS total_paid from kc_avr_receipt 
		where status = 1 and deleted is null"));

	 	$total = $totalAmountReceived+$abr['total_paid'];
		echo '<tr><td colspan="6" align="right">Total:</td><td class="text-success">'.number_format($total,2).'</td></tr>';
		
		}else{

			echo '<tr><td colspan="6" align="right">Total:</td><td class="text-success">'.number_format($totalAmountReceived,2).'</td></tr>';
			
		}

		$query = "select sum(amount) as amt from kc_customer_transactions where status = '1' and cr_dr = 'dr' and remarks is NULL and is_affect_sold_amount != '1' ";
		if(isset($_GET['from_date']) && isset($_GET['to_date'])){
			$query .= " and paid_date BETWEEN '".date("Y-m-d",strtotime($_GET['from_date']))."' AND '".date("Y-m-d",strtotime($_GET['to_date']))."'";
		}
		if(isset($_GET['associate']) && $_GET['associate']>0){
			$query .= " and block_number_id IN (select block_number_id from kc_customer_blocks where status = '1' and associate = '".$_GET['associate']."') ";
		}
		$transactions = mysqli_query($conn,$query);
		$grand_total = mysqli_fetch_assoc($transactions);
		if($search && mysqli_num_rows($transactions) > 0){
			$sup_grand = $grand_total['amt'] +AVRPaidAmount($conn);
		 	echo '<tr><td colspan="6" align="right">Grand Total:</td><td class="text-success">'.number_format($sup_grand,2).'</td></tr>';
		 	echo '</table>';
	 	}else{ 
		 	echo '<tr><td colspan="6" align="right">Grand Total:</td><td class="text-success">'.number_format($grand_total['amt'],2).'</td></tr>';
		 	echo '</table>';
	 	}
	}
?>