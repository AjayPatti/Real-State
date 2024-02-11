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

$query = "SELECT ct.id as customer_block_id,ct.id, ct.customer_id, ct.bank_name,ct.remarks, ct.cheque_dd_number, ct.amount, ct.paid_date, ct.block_id, ct.block_number_id, b.project_id, b.name as block_name, bn.block_number as block_number_name, bn.area, p.name as project_name, c.name_title as customer_name_title, c.name as customer_name, c.mobile as customer_mobile, c.address as customer_address from kc_customer_transactions ct LEFT JOIN kc_blocks b ON ct.block_id = b.id LEFT JOIN kc_block_numbers bn ON ct.block_number_id = bn.id LEFT JOIN kc_projects p ON b.project_id = p.id LEFT JOIN kc_customers c ON ct.customer_id = c.id  where ct.status = '1' AND ct.payment_type  = 'Cheque' AND ct.clear_date is null ";


if (isset($_GET['search_customer']) && $_GET['search_customer'] != '') {
	//$query .= " and name LIKE '%".$_GET['search_customer']."%'";
	if (!ctype_digit($_GET['search_customer'])) {
		$query .= " and c.name LIKE '%" . $_GET['search_customer'] . "%'";
	} else {
		$query .= " and ct.customer_id = '" . $_GET['search_customer'] . "'";
	}
}
if (isset($_GET['search_cheque_dd_number']) && $_GET['search_cheque_dd_number'] > 0) {
	$check_no = $_GET['search_cheque_dd_number'];
	$query .= " and ct.cheque_dd_number LIKE '%" . $check_no . "%'";
}
if (isset($_GET['search_project']) && (int) $_GET['search_project'] > 0) {
	$project_id = (int) $_GET['search_project'];
	$query .= " and ct.customer_id IN (SELECT customer_id from kc_customer_blocks WHERE block_id IN (SELECT id FROM kc_blocks WHERE project_id = '$project_id') )";
}
if (isset($_GET['search_block']) && (int) $_GET['search_block'] > 0) {
	$block_id = (int) $_GET['search_block'];
	$query .= " and ct.customer_id IN (select customer_id from kc_customer_blocks where status = '1' and block_id = '$block_id' )";
}
if (isset($_GET['search_block_no']) && (int) $_GET['search_block_no'] > 0) {
	$block_number_id = (int) $_GET['search_block_no'];
	$query .= " and ct.customer_id IN (select customer_id from kc_customer_blocks where status = '1' and block_number_id = '$block_number_id' )";
}
if (isset($_GET['from_date']) && (int) $_GET['to_date'] > 0) {
	$newDate = date("Y-m-d", strtotime($_GET['to_date']));
	$newDate2 = date("Y-m-d", strtotime($_GET['from_date']));
	$block_number_id = (int) $_GET['to_date'];
	$query .= " and ct.customer_id IN (select customer_id from kc_customer_blocks where status = '1' AND ct.paid_date   BETWEEN  '" . $newDate2 . "' AND  '" . $newDate . "' )";
}

$query .= " order by id";


$customers = mysqli_query($conn, $query);

if (mysqli_num_rows($customers) > 0) {

	echo '<table border="1">';
	//make the column headers what you want in whatever order you want
	echo '<tr><th>Sr.</th>
							<th>Project Name</th>
							<th>Block</th>
							<th>Plot No</th>
							<th>Customer Name(code)</th>
							<th>Mobile</th>
							<th>Amount</th>
							<th>Paid Date</th>
							<th>Bank Name</th>
							<th>Cheque No</th>
							<th>Remarks</th></tr>';
	//loop the query data to the table in same order as the headers
	$counter = 1;
	$total_debited_amt = $total_credited_amt = $total_pending_amt = 0;
	while ($customer = mysqli_fetch_assoc($customers)) {

		// echo "<pre>"; print_r($customer); die;
		$total_debited_amt += $total_debited = totalDebited($conn, $customer['customer_id'], $customer['block_id'], $customer['block_number_id']);
		$total_credited_amt += $total_credited = totalCredited($conn, $customer['customer_id'], $customer['block_id'], $customer['block_number_id']);

		$total_pending_amt += $pending_amount = ($total_credited - $total_debited);

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
				<?php echo ($customer['customer_name_title'] . ' ' . $customer['customer_name']) . '(' . customerID($customer['customer_id']) . ')'; ?>
				<!-- <strong>Address : </strong><?php echo $customer['customer_address']; ?> -->
			</td>
			<td><?php echo $customer['customer_mobile']; ?></td>
			<td><?php echo $customer['amount']; ?></td>
			<td><?php echo $customer['paid_date'] ?></td>
			<td>
				<?php echo $customer['bank_name'] ? $customer['bank_name'] : 'N/A'; ?>
			</td>
			<td><?php echo $customer['cheque_dd_number'] ? $customer['cheque_dd_number'] : 'N/A'; ?></td>
			<td><?php echo $customer['remarks'] ? $customer['remarks'] : 'N/A'; ?></td>
		</tr>
<?php $counter++;
	}
}
?>