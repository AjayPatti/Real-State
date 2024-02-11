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
$excelData[] = array( 'Sl No.','Code','Name', 'Mobile','Added On'); 
 
// Fetch records from database and store in an array 

$query = $conn->query("SELECT `name`, `code`,`mobile_no`,`addedon` from kc_associates"); 

if($query->num_rows > 0){ 
  $counter = 1;
  $credit = 0;
  $debit = 0;
    while($row = $query->fetch_assoc()){
     
    //   $credit = associateTotalCredited($conn,$row['id']);
	  // $debit = associateTotalDebited($conn,$row['id']);
    //   $balance = $credit -  $debit ;
      $addedon= date('d-m-Y',strtotime($row['addedon']));
      if($row['addedon'] =='0000-00-00 00:00:00') { $addedon = ''; }
      else{
        $addedon= date('d-m-Y',strtotime($row['addedon']));
      }
      $lineData = array( $counter,$row['code'],$row['name'], $row['mobile_no'],$addedon);  
      $excelData[] = $lineData; 
      $counter++;
    } 
   
} 
 
// Export data to excel and download as xlsx file 
$xlsx = CodexWorld\PhpXlsxGenerator::fromArray( $excelData ); 
$xlsx->downloadAs($fileName); 
 
exit; 


?>