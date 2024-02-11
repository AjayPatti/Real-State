<?php
	ob_start();
	session_start();

	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/checkAuth.php");

	header("Content-Type: application/xls");    
	header("Content-Disposition: attachment; filename= Associate Ledger-".date('d-M-y').".xls");  
	header("Pragma: no-cache"); 
	header("Expires: 0");

	$search = false;
    $query = "select * from kc_associates_transactions where status = '1'";;	// and remarks is NULL->to remove discount	// and cr_dr = 'dr'
	if(isset($_GET['associate_id']) && (int) $_GET['associate_id']>0 ){ 
		//echo "<pre>"; print_r($_GET); die;
		$associate_id = (int) $_GET['associate_id'];
		if(isset($_GET['associate_id']) && isset($_GET['associate_id'])){
			$query .= " and associate_id = '$associate_id' ";
		}
		$query .= " order by block_number_id, cr_dr, paid_date asc";
		$transactions = mysqli_query($conn,$query);
		$search = true;
	}

	if($search && mysqli_num_rows($transactions) > 0){

		echo '<table border="1">';
		echo '<tr><th colspan="8" align="center">'.addslashes(associateName($conn,(isset($_GET['associate_id']) && $_GET['associate_id'] != '')?$_GET['associate_id']:'')).'</th></tr>';
		//make the column headers what you want in whatever order you want
		echo '<tr><th>Sr.</th><th>Block</th><th>Plot No.</th><th>Area</th><th>Date</th><th>Details</th><th>Paid Amount</th><th>Commission Percentage</th><th>Credit</th><th>Debit</th></tr>';
		//loop the query data to the table in same order as the headers
		$counter = 1;
		$totalCredit = $totalDebit = 0;
		while($transaction = mysqli_fetch_assoc($transactions)){
			$transaction_details = mysqli_fetch_assoc(mysqli_query($conn,"select amount from kc_customer_transactions where id = '".$transaction['transaction_id']."' limit 0,1 "));

			$block = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_customer_blocks where block_id = '".$transaction['block_id']."' and block_number_id = '".$transaction['block_number_id']."' limit 0,1 "));
			//echo "<pre>"; print_r($transaction);
			//$total_debited = totalDebited($conn,$block['customer_id'],$block['block_id'],$block['block_number_id']);
			//$total_credited = totalCredited($conn,$block['customer_id'],$block['block_id'],$block['block_number_id']);

			$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select area from kc_block_numbers where block_id = '".$block['block_id']."' and id = '".$block['block_number_id']."' "));	// and status = '1'
			
			//$totalAmountReceived += $transaction['amount'];

			//$pending_amount = ($total_credited - $total_debited);
			//$totalPendingAmount += $pending_amount;

			if($transaction['cr_dr'] == 'cr' && $transaction['remarks'] == NULL){	//$counter == 1 && 
		        $transaction['amount'] = saleAmount($conn,$transaction['customer_id'],$transaction['block_id'],$transaction['block_number_id']);
		    }

		    echo "<tr><td>".$counter."</td><td>".blockName($conn,$transaction['block_id'])."</td><td>".blockNumberName($conn,$transaction['block_number_id'])."</td><td>".$block_details['area']."</td><td>".date("d M Y",strtotime($transaction['paid_date']))."</td>";

		    echo "<td>".$transaction['payment_type'];
				if($transaction['payment_type'] == "Cheque" || $transaction['payment_type'] == "DD" || $transaction['payment_type'] == "NEFT" || $transaction['payment_type'] == "RTGS"){
			        echo "<br>Bank Name: <strong>".$transaction['bank_name']."</strong>";
			        echo "<br>".$transaction['payment_type']." Number: <strong>".$transaction['cheque_dd_number']."</strong>";
			    }
			    if(trim($transaction['remarks']) != ''){ echo '<br />'.$transaction['remarks']; }
			    if(trim($transaction['remarks']) != ''){ echo '<br />'.$transaction['remarks']; }
			echo "</td>";
			    
            $credit = associateTotalCreditedHistory($conn,$transaction['associate_id']);
            $debit = associateTotalDebitedHistory($conn,$transaction['associate_id']);
            $associateCommission = associateCommission($conn,$transaction['associate_id']);
            echo "<td>". number_format($transaction_details['amount'],2) ."</td>";
            echo "<td>". $associateCommission['associate_percentage'] ."</td>";
            
			echo "<td>";
			if($transaction['cr_dr'] == "cr"){
				$totalCredit +=  $credit;
				echo number_format( $credit,2);
			}
			echo "</td>";

			echo "<td>";
			if($transaction['cr_dr'] == "dr"){
				$totalDebit += $debit;
				echo number_format($debit,2);
			}
			echo "</td>";
		    $counter++;
		}
		echo '<tr><td colspan="8" align="right">Total: </td><td>'.number_format($totalCredit,2).'</td><td>'.number_format($totalDebit,2).'</td></tr>';
		echo '<tr><td colspan="8" align="right">Pending: </td><td colspan="2">'.number_format($totalCredit - $totalDebit,2).'</td></tr>';
		echo '</table>';
	}
?>