<?php
	ob_start();
	session_start();

	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/checkAuth.php");

	header("Content-Type: application/xls");    
	header("Content-Disposition: attachment; filename=registries-".date('d-M-y').".xls");  
	header("Pragma: no-cache"); 
	header("Expires: 0");

	$query = "select * from kc_customer_blocks where status = '1' and registry = 'yes' ";
	if( (isset($_GET['from_date']) && isset($_GET['to_date'])) || (isset($_GET['search_project']) && $_GET['search_project']>0) ){ 
		//echo "<pre>"; print_r($_GET); die;
		if(isset($_GET['from_date']) && isset($_GET['to_date'])){
			$query .= " and registry_date BETWEEN '".date("Y-m-d",strtotime($_GET['from_date']))."' AND '".date("Y-m-d",strtotime($_GET['to_date']))."'";
		}

		if(isset($_GET['search_project']) && $_GET['search_project']>0){
			$query .= " and block_id IN (select id from kc_blocks where status = '1' and project_id = '".$_GET['search_project']."' )";
		}

		$query .= " order by registry_date desc";
		$customers = mysqli_query($conn,$query);
		$search = true;
	}

	if($search && mysqli_num_rows($customers) > 0){

		echo '<table border="1">';
		//make the column headers what you want in whatever order you want
		echo '<tr><th>Sr.</th><th>Block</th><th>Plot No.</th><th>Area</th><th>Customer</th><th>Mobile No</th><th>Address</th><th>Associate</th><th>Total Amount</th><th>Received Amount</th><th>Pending Amount</th><th>Registry Date</th><th>Registry By</th><th>Khasra Number</th><th>Maliyat Value</th><th>Sale Value</th></tr>';
		//loop the query data to the table in same order as the headers
		$counter = 1;
		$total_debited = $total_credited = $pending_amount = 0;
		while($customer = mysqli_fetch_assoc($customers)){
			$total_debited = totalDebited($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']);
			$total_credited = totalCredited($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']);
			$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select area from kc_block_numbers where block_id = '".$customer['block_id']."' and id = '".$customer['block_number_id']."' and status = '1' "));
			$pending_amount = ($total_credited - $total_debited);

			$customer_detail = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_customers where id = '".$customer['customer_id']."' "));


		    echo "<tr><td>".$counter."</td><td>".blockName($conn,$customer['block_id'])."</td><td>".blockNumberName($conn,$customer['block_number_id'])."</td><td>".$block_details['area']."Sq. Ft.</td><td>".customerName($conn,$customer['customer_id']).' ('.customerID($customer['customer_id']).')'."</td><td>".$customer_detail['mobile']."</td><td>".$customer_detail['address']."</td><td>".associateName($conn,$customer['associate'])."</td><td>".number_format($total_credited,2)."</td><td>".number_format($total_debited,2)."</td><td>".number_format($pending_amount,2)."</td><td>".date("d M Y",strtotime($customer['registry_date']))."</td><td>".$customer['registry_by']."</td><td>".$customer['khasra_no']."</td><td>".$customer['maliyat_value']."</td><td>".$customer['sale_value']."</td></tr>";
		    $counter++;
		}
		echo '</table>';
	}
?>