<?php 
ob_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/sendMessage.php");

$url = 'cron_festival_message.php';

$now = date_create()->format('Y-m-d');



$festive = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `kc_festival_message` WHERE `date`= '$now' AND `status`='0' AND  `deleted_by` IS NULL "));



   $test=[];
   $limit = 200;
   $start = !empty($festive['processed_records']) ? $festive['processed_records'] :0;

if(isset($festive['template_id']) && isset($festive['date'])) {
   
   if(date('Y-m-d H:i:s')>=date('Y-m-d 07:00:00')){
      // print_r($festive);die;

      if($festive['is_testing_sent'] ==''){
      
         $query= mysqli_query($conn,"SELECT * FROM `kc_contacts` WHERE `status` = '1' AND `is_testing_number`='1' LIMIT $start , $limit ");

         if(mysqli_num_rows($query) > 0){
            while($row = mysqli_fetch_assoc($query)){
               $test[$row['id']] = $row['mobile'];   
            } 
               sendWishes($conn, $festive['template_id'], implode(",",$test));
               $total_record = $festive['processed_records'] + count($test);
                
               $today_at = date_create()->format('Y-m-d H:i:s');


               $sql1 = mysqli_query($conn, "UPDATE `kc_festival_message` SET `is_testing_sent` ='$today_at' WHERE `date`= '$now' AND `template_id`= ".$festive['template_id']." ");
               exit(' message sent succesfully for testing');
         }
      } else if(date("Y-m-d H:i:s", strtotime('+4 hours'.$festive['is_testing_sent'])) <= date("Y-m-d H:i:s")) {
            
         $query= mysqli_query($conn,"SELECT * FROM `kc_contacts` WHERE `status` = '1' AND `is_testing_number`='0' LIMIT $start , $limit ");
            
            if(mysqli_num_rows($query) > 0){
               while($row = mysqli_fetch_assoc($query)){
                  $test[$row['id']] = $row['mobile'];   
                  }
                  sendWishes($conn, $festive['template_id'], implode(",",$test));
                  $total_record = $festive['processed_records'] + count($test);
                 
                  $sql1 = mysqli_query($conn, "UPDATE `kc_festival_message` SET `processed_records`='$total_record' WHERE `date`= '$now' AND `template_id`= ".$festive['template_id']." ");
                  exit(' message sent succesfully');
               
            }else{
               $test_rec_sql= mysqli_fetch_assoc(mysqli_query($conn,"SELECT `mobile_no` FROM `kc_message_reports` JOIN `kc_festival_message` WHERE kc_message_reports.`created` = kc_festival_message.`is_testing_sent`"));
               $testing_records = count(explode("," , $test_rec_sql['mobile_no']));

               $total_record = $festive['processed_records']+$testing_records;

               $sql2 =  mysqli_query($conn,"UPDATE `kc_festival_message` SET `status`= '1' , `processed_records`= ' $total_record'  WHERE  `date`= '$now'  AND `template_id`= ".$festive['template_id']." ");
               exit('completed');
            }
      }
   }
}

?>
  