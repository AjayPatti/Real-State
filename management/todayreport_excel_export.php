<?php
	ob_start();
	session_start();

	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");
	require("../includes/checkAuth.php");

	header("Content-Type: application/xls");    
	header("Content-Disposition: attachment; filename=Today Report-".date('d-M-y').".xls");  
	header("Pragma: no-cache"); 
	header("Expires: 0");
	$start_date  = date('Y-m-d 00-00-01');
    $end_date  = date('Y-m-d 23-59-59');
    	
	$query =  "select cfuh. * from kc_customer_follow_ups_hist cfuh left join kc_customers kc ON kc.id=cfuh.customer_id where created_at between '".$start_date."' and '".$end_date."' AND kc.blacklisted = 0 ";
  // echo "$query";die;
	if($_SESSION['login_type'] != 'super_admin'){
      $query .= " and created_by = '".$_SESSION['login_id']."' ";
    }
	$hists = mysqli_query($conn,$query);

	echo '<table border="1">';
		//make the column headers what you want in whatever order you want
	echo '<tr><th>Sr.</th><th>Details</th><th>Project Details</th><th>Next Follows Date</th><th>User</th><th>Remark</th></tr>';

	if(mysqli_num_rows($hists) > 0){
                  $counter = 1;
                  while($hist = mysqli_fetch_assoc($hists)){
                    $customer = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_customers where id = '".$hist['customer_id']."'"));
                    $blocks = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_blocks where id = '".$hist['block_id']."' and status = '1' "));
                    $block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select block_number from kc_block_numbers where block_id = '".$blocks['id']."' AND id =  '".$hist['block_number_id']."' limit 0,1 "));
                    $payment_type =   mysqli_fetch_assoc(mysqli_query($conn, "SELECT customer_payment_type FROM kc_customer_blocks WHERE customer_id = '".$hist['customer_id']."' AND block_id = '".$hist['block_id']."' AND block_number_id = '".$hist['block_number_id']."' " ));
                    $userType = mysqli_fetch_assoc(mysqli_query($conn ,"SELECT name FROM kc_login WHERE id = '".$hist['created_by']."' "));
                    //print_r($userType);die;
                    $emi = nextEMIDetails($conn , $hist['customer_id'] , $hist['block_id'] , $hist['block_number_id']);
                    $part_amc = getPartAmount($conn , $hist['customer_id'] , $hist['block_id'] , $hist['block_number_id'] );

                    $total_amc= mysqli_fetch_assoc(mysqli_query($conn , "SELECT SUM(amount) AS total_cr FROM kc_customer_transactions WHERE customer_id = '".$hist['customer_id']."' AND cr_dr = 'cr' AND block_id = '".$hist['block_id']."' AND block_number_id = '".$hist['block_number_id']."' AND status = 1 "));
                    $total_paid_amc =  mysqli_fetch_assoc(mysqli_query($conn , "SELECT SUM(amount) AS total_dr FROM kc_customer_transactions WHERE customer_id = '".$hist['customer_id']."' AND cr_dr = 'dr' AND block_id = '".$hist['block_id']."' AND block_number_id = '".$hist['block_number_id']."' AND status = 1 "));
                    $due_amc = getPartAmount($conn , $hist['customer_id'] , $hist['block_id'] , $hist['block_number_id'] ) ;
                    // print_r($customer);die;
                     
                     
                    echo "<tr><td>".$counter."</td><td><strong>" . customerName($conn,$hist['customer_id']) ." </strong><br>
                      <br> <b>Mobile:</b>". $customer['mobile']."</td>
                      <td>".blockProjectName($conn,$hist['block_id'])."<br>".$blocks['name']."(".$block_number_details['block_number'].")"."</td><td><strong>".date("d-m-Y",strtotime($hist['next_follow_up_date']))."</strong></td><td><strong >". $userType['name']. "</strong></td><td><strong >".$hist['remarks']."</strong></td></tr>";
				
				 
		    $counter++;
		    	
                 } } ?>

             
	