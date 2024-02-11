<?php
	ob_start();
	session_start();

	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/checkAuth.php");

	header("Content-Type: application/xls");    
	header("Content-Disposition: attachment; filename=visit_report-".date('d-M-y').".xls");  
	header("Pragma: no-cache"); 
	header("Expires: 0");

	

	$query = "select * from kc_visit_forms where deleted_at IS Null";

	if(isset($_GET['datesearch']) && $_GET['datesearch']!=''){
		$ddatesearch = explode('-',$_GET['datesearch']);
		
		$startdate = date('Y-m-d',strtotime($ddatesearch[0]));
		$enddate = date('Y-m-d',strtotime($ddatesearch[1]));
		$query .= " AND visit_datetime between '$startdate' and '$enddate' ";
	}
	
	$query .= " order by name";

	
	$customers = mysqli_query($conn,$query);
	$search = true;

	if($search && mysqli_num_rows($customers) > 0){

		echo '<table border="1">';
		//make the column headers what you want in whatever order you want
		echo '<tr><th>Sr.</th><th>Name</th><th>Mobile</th><th>Visit Date</th><th>Associate</th><th>Project</th><th>Sector</th><th>Added Date</th></tr>';
		//loop the query data to the table in same order as the headers
		$counter = 1;
		$total_debited_amt = $total_credited_amt = $total_pending_amt = $total_debited = $total_credited = $pending_amount = 0;
		while($customer = mysqli_fetch_assoc($customers)){
			
			if($customer['project_id'] > 0){
				$detail = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_projects where id = '".$customer['project_id']."' and status = '1' "));
				$project_name = isset($detail['name'])?$detail['name']:'';
			}else{
				$project_name = 'NA';
			}
			if($customer['block_id'] > 0){
				$block_name = blockName($conn,$customer['block_id']);
			}else{
				$block_name = 'NA';
			}
			if($customer['associate_id'] > 0){
				$associate_name = associateName($conn,$customer['associate_id']);
			}
			else{
				$associate_name = 'NA';
			}

		    echo "<tr><td>".$counter."</td><td>".$customer['name']."</td><td>".$customer['mobile']."</td><td>".date("d M Y h:i A",strtotime($customer['visit_datetime']))."</td><td>".$associate_name."</td><td>".$project_name."</td><td>".$block_name."</td><td>".date("d M Y h:i A",strtotime($customer['addedon']))."</td></tr>";
		    $counter++;
		}
		echo "</table>";
	}
?>