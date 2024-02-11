<?php
	ob_start();
	session_start();

	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/checkAuth.php");

	header("Content-Type: application/xls");    
	header("Content-Disposition: attachment; filename=day_book-".date('d-M-y').".xls");  
	header("Pragma: no-cache"); 
	header("Expires: 0");

	$query = "select * from kc_customer_transactions where addedon between '".date('Y-m-d 00:00:01')."' and '".date('Y-m-d 23:59:59')."' and cr_dr = 'dr'";	
		$query .= " order by addedon asc";
		$transactions = mysqli_query($conn,$query);
		// $abr_r = mysqli_query($conn,$abr_receipt);
		
	if(mysqli_num_rows($transactions) > 0){

		echo '<table border="1">';
		//make the column headers what you want in whatever order you want
		echo '<tr><th>Sr.</th><th>Customer Name</th><th>Mobile</th><th>Address</th><th>Block</th><th>Plot No.</th><th>Amount</th><th>Payment Type</th><th>Bank Details</th><th>Cheque Number</th><th>Date</th></tr>';
		//loop the query data to the table in same order as the headers
		$counter = 1;
		$totalAmountReceived = $totalPendingAmount = 0;
		while($transaction = mysqli_fetch_assoc($transactions)){
			$customer = mysqli_fetch_assoc(mysqli_query($conn,"select id,name_title,name,mobile,address from kc_customers where id = '".$transaction['customer_id']."'"));
			$block = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_blocks where id = '".$transaction['block_id']."'"));
			
			$totalAmountReceived += $transaction['amount'];

			
			$amt = $customer['address']?$customer['address']:'N/A';
		    echo "<tr><td>".$counter."</td><td>".($customer['name_title'].' ' .$customer['name']).' ('.customerID($customer['id']).')'."</td><td>".$customer['mobile']."</td><td>".$amt."</td><td>".$block['name']."</td><td>".$transaction['block_number_id']."</td><td>".$transaction['amount']."</td><td>".$transaction['payment_type'];

		    if($transaction['payment_type'] == "Cheque" || $transaction['payment_type'] == "DD" || $transaction['payment_type'] == "NEFT" || $transaction['payment_type'] == "RTGS"){
                echo "<td>".$transaction['bank_name']."</td>";
                echo "<td>".$transaction['cheque_dd_number']."</td>";
            }else{
            	echo "<td>Cash Payment</td><td>N/A</td>";
            }

		    echo "<td>".date("d M Y",strtotime($transaction['addedon']))."</td></tr>";

		    $counter++;
		}

		echo '<tr><td colspan="6" align="right">Total:</td><td class="text-success">'.number_format($totalAmountReceived,2).'</td></tr>';

	}
?>