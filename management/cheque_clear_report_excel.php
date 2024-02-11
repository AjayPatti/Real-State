<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");

header("Content-Type: application/xls");
header("Content-Disposition: attachment; filename=report-" . date('d-M-y') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

if (isset($_GET['from_date']) && isset($_GET['to_date'])) {
	$to_date = date("Y-m-d", strtotime($_GET['to_date']));
	$from_date = date("Y-m-d", strtotime($_GET['from_date']));
	$query = "select cth.id as customer_block_id,cth.paid_account_no,cth.id,cth.customer_id, cth.bank_name,cth.remarks, cth.cheque_dd_number, cth.amount, cth.paid_date, cth.block_id, cth.block_number_id,cth.clear_remarks, b.project_id, b.name as block_name, bn.block_number as block_number_name, bn.area, p.name as project_name, c.name_title as customer_name_title, c.name as customer_name, c.mobile as customer_mobile, c.address as customer_address from kc_customer_transactions cth LEFT JOIN kc_blocks b ON cth.block_id = b.id LEFT JOIN kc_block_numbers bn ON cth.block_number_id = bn.id LEFT JOIN kc_projects p ON b.project_id = p.id LEFT JOIN kc_customers c ON cth.customer_id = c.id where cth.status = '1' AND cth.payment_type = 'Cheque' AND cth.clear_remarks is not null   And  paid_date between '" . $from_date . "' AND '" . $to_date . "' order by id desc  ";
} else {
	$query = "select cth.id as customer_block_id,cth.paid_account_no,cth.id,cth.customer_id, cth.bank_name,cth.remarks, cth.cheque_dd_number, cth.amount, cth.paid_date, cth.block_id, cth.block_number_id,cth.clear_remarks, b.project_id, b.name as block_name, bn.block_number as block_number_name, bn.area, p.name as project_name, c.name_title as customer_name_title, c.name as customer_name, c.mobile as customer_mobile, c.address as customer_address from kc_customer_transactions cth LEFT JOIN kc_blocks b ON cth.block_id = b.id LEFT JOIN kc_block_numbers bn ON cth.block_number_id = bn.id LEFT JOIN kc_projects p ON b.project_id = p.id LEFT JOIN kc_customers c ON cth.customer_id = c.id where cth.status = '1' AND cth.payment_type = 'Cheque' AND cth.clear_remarks is not null order by id desc  ";
}

$customers = mysqli_query($conn, $query);

if (mysqli_num_rows($customers) > 0) {

	echo '<table border="1">';
	//make the column headers what you want in whatever order you want
	echo '<tr><th>Sr.</th>
							<th>Project Name</th>
							<th>Block</th>
							<th>Plot No</th>
							<th>Customer Name(code)</th>
							<th>Company Name</th>
							<th>Company Account No </th> 
							<th>Amount</th>
							<th>Paid Date</th>
							<th> (Cheque) Bank Name</th>
							<th>cheque No</th>
							<th>Remarks</th>
							<th>Clear Remarks</th></tr>';
	//loop the query data to the table in same order as the headers
	$counter = 1;
	$total_debited_amt = $total_credited_amt = $total_pending_amt = 0;
	while ($customer = mysqli_fetch_assoc($customers)) {
		// echo "<pre>"; print_r($customer); die;
		// $total_debited_amt += $total_debited = totalDebited($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']);
		// echo "<pre>"; print_r($total_debited_amt); die;
		// $total_credited_amt += $total_credited = totalCredited($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']);
		$account_details = mysqli_fetch_assoc(mysqli_query($conn, "select * from kc_accounts where id = '" . $customer['paid_account_no'] . "'"));
		// echo "<pre>"; print_r($account_details); die;
		// $total_pending_amt += $pending_amount = ($total_credited - $total_debited);
?>

		<tr>
			<td><?php echo $counter; ?>.</td>
			<td>
				<?php echo $customer['project_name']; ?>
			</td>

			<td>
				<?php echo $customer['block_name']; ?>
			</td>

			<td>
				<?php echo $customer['block_number_name']; ?>

			</td>

			<td>
				<?php echo ($customer['customer_name_title'] . ' ' . $customer['customer_name']) . '(' . customerID($customer['customer_id']) . ')'; ?><br>
			</td>


			<!-- <td> <strong>Mobile : </strong><?php echo $customer['customer_mobile']; ?><br> -->
			<!-- <strong>Address : </strong><?php echo $customer['customer_address']; ?> </td> -->


			<td>
				<?php echo $account_details['name']; ?><br>
			</td>

			<!-- <td> <strong>Bank Name : </strong><?php echo $account_details['bank_name']; ?><br> -->
			<!-- <strong>Branch Name : </strong><?php echo $account_details['branch_name']; ?><br> </td>-->


			<td>
				<span class="text-danger"><?php echo $account_details['account_no']; ?></span><br>
			</td>

			<!--<td> <strong>IFSC Code : </strong><span class="text-primary"> <?php echo $account_details['ifsc_code']; ?></span><br> </td>-->


			<td><i class="fa fa-inr"></i> <?php echo $customer['amount']; ?></td>

			<td><?php echo $customer['paid_date'] ?></td>

			<td>
				<?php echo $customer['bank_name'] ? $customer['bank_name'] : 'N/A'; ?><br>
			</td>

			<td>
				<?php echo $customer['cheque_dd_number'] ? $customer['cheque_dd_number'] : 'N/A'; ?><br>
			</td>
			<td><?php echo $customer['remarks'] ? $customer['remarks'] : 'N/A'; ?></td>
			<td>
				<?php echo $customer['clear_remarks'] ? $customer['clear_remarks'] : 'N/A'; ?>
			</td>
		</tr>

<?php $counter++;
	}
}
?>