<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");
require("../includes/PhpXlsxGenerator.php");

$fileName = "report-" . date('Y-m-d') . ".xlsx"; 
 
// Define column names 
$excelData[] = array( 'Sl No.','Name', 'Relation', 'Relative', 'Mobile', 'Address', 'Payment Type', 'Paid Amount','Paid Date','Plot Details','Remarks','Added On'); 
 
// Fetch records from database and store in an array 


if(isset($_GET['search']) && isset($_GET['from_date']) && isset($_GET['to_date'])){
	$to_date = date("Y-m-d", strtotime($_GET['to_date']));
	$from_date = date("Y-m-d", strtotime($_GET['from_date']));
	$query = $conn->query("SELECT * from kc_avr_receipt where status = '1' and deleted is null And  paid_date between '".$from_date."' AND '".$to_date."' order by id desc "); 
	// $uri = explode('?', $_SERVER['REQUEST_URI'])[1];
}else{
     $query = $conn->query("SELECT * from kc_avr_receipt where status = '1' and deleted is null"); 
}

if($query->num_rows > 0){ 
  $counter = 1;
    while($row = $query->fetch_assoc()){
     
      $name =  $row['name_title'].' '.$row['name']; 
      $parent = $row['parent_sub_title'].' '.$row['parent_name'];
      // $addedon= isset($row['addedon'])?$row['addedon']:'';
      if($row['addedon'] =='0000-00-00 00:00:00') { $addedon = ''; }
      else{
        $addedon= date('Y-m-d',strtotime($row['addedon']));
      }
      $lineData = array( $counter,$name, $row['parent_name_relation'],$parent, $row['mobile'], $row['address'], $row['payment_type'], $row['paid_amount'], $row['paid_date'], $row['project_block_plotnumber_totalarea'], $row['remarks'],$addedon);  
      $excelData[] = $lineData; 
      $counter++;
    } 
   
} 
 
// Export data to excel and download as xlsx file 
$xlsx = CodexWorld\PhpXlsxGenerator::fromArray( $excelData ); 
$xlsx->downloadAs($fileName); 
 
exit; 
 

  ?>