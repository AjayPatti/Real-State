<?php

function sendMessage($conn,$templateID,$mobile,$variables_array){
  // return true;
  $templates = getSmsTemplates();

  $unicodeTemplates = unicodeTemplates();
  // $unicodeTemplates = array(1);

  if(isset($templates[$templateID])){
    $message = $templates[$templateID];
    // echo "<pre>";
   
    foreach($variables_array as $key=>$value){
      $message = str_replace($key,$value,$message);
    }
    // echo "<br>";
    // print_r($message);die;   
    // 27122023 chechk 

    if(strpos('variable',$message)){
      return false;
    }
    
    $authKey = "e295c16f00c29cb5f3f892d4b7ab8705";
    //Sender ID,While using route4 sender id should be 6 characters long.
    $senderId = "KCREPL";
    //Define route
    $route = "B";
    //Prepare you post parameters
    $postData = array(
    'authkey' => $authKey,
	  'mobiles' => $mobile,
	  'message' => $message,
	  'sender' => $senderId,
	  'route' => $route,
    
    'unicode' => (in_array($templateID,$unicodeTemplates))?1:'',
	);
  
	//API URL
	$url = "http://sms.quickinfotech.co.in/api/send_http.php";
  
	
  $request =""; //initialise the request variable
  $param['method']= "SendMessage";
  $param['send_to'] = $mobile;
  $param['msg'] = $message;
  $param['msg_type'] = (in_array($templateID,$unicodeTemplates))?"UNICODE_TEXT":"TEXT"; //Can be "FLASH”/"UNICODE_TEXT"
  $param['userid'] =  "2000189060"; //"surendrakumar";
  $param['auth_scheme'] = "plain";
  $param['password'] = "zUJjZb";    //"zUJjZb";
  $param['v'] = "1.1";
  $param['format'] = "text";
  
  //echo $mobile; die;
  
  //Have to URL encode the values
  foreach($param as $key=>$val) {
    $request.= $key."=".rawurlencode($val);
    //we have to urlencode the values
    $request.= "&";
    //append the ampersand (&) sign after each parameter/value pair
  }
  $request = substr($request, 0, strlen($request)-1);
  //remove final (&) sign from the request
  $url =
  "http://quickinfotech.msg4all.com/GatewayAPI/rest?".$request;
  
  // print_r($url);die;
  
  // echo $url; die;
  
	// init the resource
	$ch = curl_init();
  // print_r($ch);die;
	curl_setopt_array($ch, array(
	  CURLOPT_URL => $url,
	  CURLOPT_RETURNTRANSFER => false,
	  CURLOPT_POST => true,
	  CURLOPT_POSTFIELDS => $param
	  //,CURLOPT_FOLLOWLOCATION => true
	));
	//Ignore SSL certificate verification
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	//get response
	$output = curl_exec($ch);
	//print_r($output);die;

	if (curl_errno($ch)) {
	  //echo 'error:' . curl_error($ch);
    die;
	}
	curl_close($ch);
  	//die();
  	// echo $output;die;

	//print_r($resp_array); var_dump(trim(strtolower($resp_array[0])) == "success"); die;
    if(!mysqli_query($conn,"INSERT INTO `kc_message_reports`(`mobile_no`, `message`, `created`) VALUES ('$mobile','".addslashes($message)."','".date("Y-m-d H:i:s")."')")){
		//echo("Error description: " . mysqli_error($conn)); die;
	}

  // die;
    return true;
  }
  // return false;
}


function getSmsTemplates(){
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

function templatesWithoutVariable(){
  return [2,3,4,5,6,11,12,13,14,15,16,17,18,20,21,22,23,25,26,27,28,29,30,31];

}

function unicodeTemplates(){
  return array(11,12,14,18,20,21,22,23,25,26,27,28,29,30);
}


function sendWishes($conn,$templateID,$mobile_nos){
  // return false;
 
  if(!in_array($templateID, templatesWithoutVariable())){
   
    return false;
  }
  // print_r('gsgfgkjdhg');die;
  $templates = getSmsTemplates();

  $unicodeTemplates = unicodeTemplates();
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
        // CURLOPT_URL => $url,
        // CURLOPT_RETURNTRANSFER => true,
        // CURLOPT_POST => true,
        // CURLOPT_POSTFIELDS => $postData
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
    // die();
    // echo $output;



    //print_r($resp_array); var_dump(trim(strtolower($resp_array[0])) == "success"); die;
    //return true;
    // if($output){

        // // echo $templateID;die;
        // if($mobile_nos=='7275042684') return false;
        
      if(!mysqli_query($conn,"INSERT INTO `kc_message_reports`(`mobile_no`, `message`, `created`) VALUES ('$mobile_nos','".addslashes($message)."','".date("Y-m-d H:i:s")."')")){

        //echo("Error description: " . mysqli_error($conn)); die;
      }
      return true;
    // }
    return true;
  }
  // return false;
}


function sendOTP($conn,$mobile,$userId, $associate = false){ //7896541230
    // echo $mobile; die;
    return true;
    // return false;
  if(isset($mobile)){
    //   echo '123';die;
    $otp=rand(434343,897989);
    
    $message = 'Your WCC verification OTP code is '.$otp.' OTP valid for 10 minutes only, one time use. Please do not share this OTP with anyone.';
    $authKey = "e295c16f00c29cb5f3f892d4b7ab8705";
    //Sender ID,While using route4 sender id should be 6 characters long.
    $senderId = "KCREPL";
    //Define route
    $route = "B";
    //Prepare you post parameters
    // print_r($message); die;
    $postData = array(
        'authkey' => $authKey,
        'mobiles' => $mobile,
        'message' => $message,
        'sender' => $senderId,
        'route' => $route,

    );
    //API URL
    $url = "http://sms.quickinfotech.co.in/api/send_http.php";
    // die();
    // init the resource
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData
        //,CURLOPT_FOLLOWLOCATION => true
    ));
    //Ignore SSL certificate verification
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    //get response
    $output = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'error:' . curl_error($ch);
    }
    curl_close($ch);
    // print_r($output); die;

    if($associate){
      $record = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_associate_login_otp where associate_id = '".$userId."' limit 0,1"));
      if(isset($record['id'])){
        mysqli_query($conn,"update kc_associate_login_otp set otp = '".$otp."', updated = '".date('Y-m-d H:i:s')."' where associate_id = '".$userId."' ");

        $otp_id = $record['id'];
      }else{
        mysqli_query($conn,"INSERT INTO kc_associate_login_otp set associate_id = '".$userId."', otp = '".$otp."', created = '".date('Y-m-d H:i:s')."', updated = '".date('Y-m-d H:i:s')."'");
        $otp_id = mysqli_insert_id($conn);
      }
      return $otp_id;
    }else if(mysqli_query($conn,"INSERT INTO kc_login_otp set user_id = '".$userId."', otp = '".$otp."', created = '".date('Y-m-d H:i:s')."'")){
        $_SESSION['otp_timestamp'] = time();
        //echo("Error description: " . mysqli_error($conn)); die;
    }
    //die();
    // echo $output;
    if($output){


      return true;
    }
    return true;
  }
  // return false;
}


// New API
//Your authentication key
  // $authKey = "e295c16f00c29cb5f3f892d4b7ab8705";
  // //Sender ID,While using route4 sender id should be 6 characters long.
  // $senderId = "KCREPL";
  // //Define route
  // $route = "B";
  // //Prepare you post parameters
  // $postData = array(
  //     'authkey' => $authKey,
  //     'mobiles' => $mobileNumber,
  //     'message' => $message,
  //     'sender' => $senderId,
  //     'route' => $route
  // );
  // //API URL
  // $url = "http://sms.quickinfotech.co.in/api/send_http.php";
  // // init the resource
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
  // //Print error if any
  // if (curl_errno($ch)) {
  //     echo 'error:' . curl_error($ch);
  // }
  // curl_close($ch);
  // echo $output;
