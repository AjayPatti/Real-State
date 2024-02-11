<?php 


function getSmsTemplates_test(){
    return array(
        1=>'Dear variable1, Happy variable2 from Team WCC.',
        2=> 'May the light that we celebrate at Diwali show us the way
  and lead us together on the path of social peace and harmony.
  Happy Diwali
  Team WCC',
      
        7=> "Hi Customer,
  Thank you for choosing us. We're happy to have you!
  Plot No.: variable2 in variable4 has been marked as booked by you.
  WCC Real Estate Pvt. Ltd.",
    
       );
  }
  
  function templatesWithoutVariable_test(){
    return [2,3,4,5,6,11,12,13,14,15,16,17,18,20,21,22,23,25,26,27,28,29,30,31];
  
  }
  
  function unicodeTemplates_test(){
    return array(11,12,14,18,20,21,22,23,25,26,27,28,29,30);
  }


function sendWishes_test($conn,$templateID,$mobile_nos){
  // return false;
 
  if(!in_array($templateID, templatesWithoutVariable_test())){
   
    return false;
  }
  // print_r('gsgfgkjdhg');die;
  $templates = getSmsTemplates_test();

  $unicodeTemplates = unicodeTemplates_test();
  // $unicodeTemplates = array(1);
  // echo $mobile_nos; die;
  if(isset($templates[$templateID])){
    
    $message = $templates[$templateID];

    $authKey = "e295c16f00c29cb5f3f892d4b7ab8705";
    //Sender ID,While using route4 sender id should be 6 characters long.
    $senderId = "KCREPL";
    //Define route
    $route = "B";
    //Prepare you post parameters
    $postData = array(
        'authkey' => $authKey,
        'mobiles' => $mobile_nos,
        'message' => $message,
        'sender' => $senderId,
        'route' => $route,

       'unicode' => (in_array($templateID,$unicodeTemplates))?1:'',
    );

    //API URL
    $url = "http://sms.quickinfotech.co.in/api/send_http.php";
    // die();
    // init the resource
    // $ch = curl_init();
    // curl_setopt_array($ch, array(
    //     CURLOPT_URL => $url,
    //     CURLOPT_RETURNTRANSFER => true,
    //     CURLOPT_POST => true,
    //     CURLOPT_POSTFIELDS => $postData
    //     //,CURLOPT_FOLLOWLOCATION => true
    // ));
    // //Ignore SSL certificate verification
    // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    // //get response
    // $output = curl_exec($ch);
    // // print_r($output);die;
    // //Print error if any
    // if (curl_errno($ch)) {
    //     echo 'error:' . curl_error($ch);
    // }
    // curl_close($ch);
    //die();
    // echo $output;



    //print_r($resp_array); var_dump(trim(strtolower($resp_array[0])) == "success"); die;
    //return true;
    // if($output){

        // // echo $templateID;die;
        // if($mobile_nos=='7275042684') return false;
        
      if(!mysqli_query($conn,"INSERT INTO `kc_message_reports_test`(`mobile_no`, `message`, `created`) VALUES ('$mobile_nos','".addslashes($message)."','".date("Y-m-d H:i:s")."')")){

        //echo("Error description: " . mysqli_error($conn)); die;
      }
      return true;
    // }
    return true;
  }
  // return false;
}
?>