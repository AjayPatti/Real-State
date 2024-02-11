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


	$query = "select kc_projects.id, kc_projects.name as project_name, kc_blocks.id, kc_blocks.name as block_name, kc_block_numbers.id, kc_block_numbers.block_number, kc_block_numbers.area, kc_block_numbers.road, kc_block_numbers.face, kc_block_numbers.addedon from kc_projects LEFT JOIN (kc_blocks LEFT JOIN kc_block_numbers ON kc_blocks.id = kc_block_numbers.block_id) ON kc_projects.id = kc_blocks.project_id where kc_block_numbers.status = '1' ";
	if(isset($_GET['search_project']) || isset($_GET['search_block']) || isset($_GET['search_block_no']) || (isset($_GET['search_block_no']) && $_GET['search_block_no']>0)){ 
		// echo "<pre>"; print_r($_GET); die;
		if(isset($_GET['search_block']) && $_GET['search_block']!=''){
			$query .= " and kc_block_numbers.block_id = '".$_GET['search_block']."'";
		}

		if(isset($_GET['search_block_no']) && $_GET['search_block_no']!=''){
			$query .= " and kc_block_numbers.id = '".$_GET['search_block_no']."'";
		}

		if(isset($_GET['search_project']) && $_GET['search_project']>0){
			$query .= " and kc_block_numbers.block_id IN (select id from kc_blocks where status = '1' and project_id = '".$_GET['search_project']."' )";
		}

		//$query .= " order by registry_date desc limit $start,$limit";
		// $query .= "limit $start,$limit";
		// echo $query; die;
		$customers = mysqli_query($conn,$query);
		$search = true;
	}

	if($search && mysqli_num_rows($customers) > 0){

		echo '<table border="1">';
		//make the column headers what you want in whatever order you want
		echo '<tr><th>Sr.</th><th>Project</th><th>Block</th><th>Plot No.</th><th>PLC</th><th>Area</th><th>Road</th><th>Face</th><th>Date</th></tr>';
		//loop the query data to the table in same order as the headers
		$counter = 1;
		$query = "select * from kc_block_numbers where status = '1' and id NOT IN (select block_number_id from kc_customer_blocks where status = '1') ";

		if(isset($_GET['search_block']) && (int) $_GET['search_block'] > 0){
			$block_id = (int) $_GET['search_block'];
		}else{
			$block_id = 1000;
		}
		$query .= "and block_id = '$block_id'";
		// echo $query;
		$block_numbers = mysqli_query($conn,$query);	
			while($block_number = mysqli_fetch_array($block_numbers)){ 
				// print_r($block_number);
				$kc_blocks = mysqli_fetch_assoc(mysqli_query($conn,"select id,project_id,name from kc_blocks where id = '".$block_id."'"));
				$projects = mysqli_fetch_assoc(mysqli_query($conn,"select id,name from kc_projects where id = '".$kc_blocks['project_id']."'"));

		    echo "<tr><td>".$counter."</td><td>".$projects['name']."</td><td>".$kc_blocks['name']."</td><td>".$block_number['block_number']."</td><td>".exportPLCList($conn,$block_number['id'])."</td><td>".$block_number['area']." Sq. Ft.</td><td>".$block_number['road']."</td><td>".$block_number['face']."</td><td>".date('jM Y',strtotime($block_number['addedon']))."</td></tr>";
		    $counter++;
		    
		}
		
	}
?>