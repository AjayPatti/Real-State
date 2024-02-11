<?php
	ob_start();
	session_start();

	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/checkAuth.php");

	header("Content-Type: application/xls");
	header("Content-Disposition: attachment; filename=Ledger-".date('d-M-y').".xls");
	header("Pragma: no-cache");
	header("Expires: 0");

	$query = "select * from kc_farmer_transactions where status = '1' and  deleted is null";	// and remarks is NULL->to remove discount	// and cr_dr = 'dr'
	if(isset($_GET['farmer']) && (int) $_GET['farmer']>0 ){
		//echo "<pre>"; print_r($_GET); die;
		$farmer_id = (int) $_GET['farmer'];
		if(isset($_GET['farmer']) && isset($_GET['farmer'])){
			$query .= " and farmer_id = '$farmer_id' ";
		}
	}
    $transactions = mysqli_query($conn,$query);

	if(mysqli_num_rows($transactions) > 0){

		echo '<table border="1">';
		echo '<tr><th colspan="7" align="center">'.addslashes(farmerName($conn,(isset($_GET['farmer']) && $_GET['farmer'] != '')?$_GET['farmer']:'')).' ('.farmerID($_GET['farmer']).')</th></tr>';
		//make the column headers what you want in whatever order you want
		echo '<tr><th>Sr.</th>
                <th>Payment Type</th>
                <th>Bank Name</th>
                <th>Paid Date</th>
                <th>Next Due Date</th>
                <th>Credit</th>
                <th>Debit</th>
                </tr>';
		//loop the query data to the table in same order as the headers
		$counter = 1;
		$totalCredit = $totalDebit = 0;
		while($transaction = mysqli_fetch_assoc($transactions)){
			//$transaction = mysqli_fetch_assoc(mysqli_query($conn,"select * from uv_customer_transactions where customer_id = '".$block['customer_id']."' and block_id = '".$block['block_id']."' and block_number_id = '".$block['block_number_id']."' and cr_dr = 'dr' "));
			//$block = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_customer_blocks where block_id = '".$transaction['block_id']."' and block_number_id = '".$transaction['block_number_id']."' limit 0,1 "));
			//echo "<pre>"; print_r($transaction);
			//$total_debited = totalDebited($conn,$block['customer_id'],$block['block_id'],$block['block_number_id']);
			//$total_credited = totalCredited($conn,$block['customer_id'],$block['block_id'],$block['block_number_id']);

			//$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select area from kc_block_numbers where block_id = '".//$block['block_id']."' and id = '".$block['block_number_id']."' and status = '1' "));

			//$totalAmountReceived += $transaction['amount'];

			//$pending_amount = ($total_credited - $total_debited);
			//$totalPendingAmount += $pending_amount;

			if($transaction['cr_dr'] == 'cr' && $transaction['remarks'] == NULL){	//$counter == 1 &&
		        $transaction['amount'] = saleAmountFarmer($conn,$transaction['farmer_id']);
		    }

		    echo "<tr><td>".$counter."</td>";

            echo "<td>";
				if($transaction['payment_type'] == "Cheque" || $transaction['payment_type'] == "DD" || $transaction['payment_type'] == "NEFT" || $transaction['payment_type'] == "RTGS"){
			        echo "<br>".$transaction['payment_type'];
			    }else{
                    echo "Cash";
                }

		    echo "</td><td>";
				echo "<br>Bank Name: <strong>".$transaction['bank_name']."</strong>";
			        echo "<br> Number: <strong>".$transaction['cheque_dd_number']."</strong>";
			    if(trim($transaction['remarks']) != ''){ echo '<br /> Remarks: '.$transaction['remarks']; }

			echo "</td><td>".$transaction['paid_date']."</td><td>".$transaction['next_due_date']."</td><td>";

			if($transaction['cr_dr'] == "cr"){
				$totalCredit += $transaction['amount'];
				echo number_format($transaction['amount'],2);
			}
			echo "</td>";

			echo "<td>";
			if($transaction['cr_dr'] == "dr"){
				$totalDebit += $transaction['amount'];
				echo number_format($transaction['amount'],2);
			}
			echo "</td>";
		    $counter++;
		}
		echo '<tr><td colspan="5" align="right">Total: </td><td>'.number_format($totalCredit,2).'</td><td>'.number_format($totalDebit,2).'</td></tr>';
		echo '<tr><td colspan="5" align="right">Pending: </td><td colspan="2">'.number_format($totalCredit - $totalDebit,2).'</td></tr>';
		echo '</table>';
	}
?>
