<?php
function filter_post($conn,$post){
	return str_replace("\'","",strip_tags(mysqli_real_escape_string($conn,trim($post))));
}

function kcEncode($str){
  return base64_encode($str);
}

function kcDecode($str){
  return base64_decode($str);
}

function formatDate($date){
	return date("jS M Y",strtotime($date));
}

function formatDateTime($dateTime){
	return date("jS M Y h:i A",strtotime($dateTime));
}

function getUserIP()
{
    // Get real visitor IP behind CloudFlare network
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
              $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
              $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }

    return $ip;
}

function customerID($customerID){
  return sprintf("%05d",$customerID); //'KC'.
}

function farmerID($farmerID){
  return sprintf("%05d",$farmerID); //'KC'.
}

function blockName($conn,$blockID){
  $detail = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_blocks where id = '".$blockID."' "));  // and status = '1'
  return isset($detail['name'])?$detail['name']:'';
}

function blockNumberName($conn,$blockNumberID){
  $detail = mysqli_fetch_assoc(mysqli_query($conn,"select block_number from kc_block_numbers where id = '".$blockNumberID."' ")); // and status = '1'
  return isset($detail['block_number'])?$detail['block_number']:'';
  // return $detail['block_number'];
}

function blockProjectName($conn,$blockID){
  $detail = mysqli_fetch_assoc(mysqli_query($conn,"select p.name from kc_blocks b INNER JOIN kc_projects p ON b.project_id = p.id where b.id = '".$blockID."' "));  // and b.status = '1'
  return isset($detail['name'])?$detail['name']:'';
}

function companyAccountName($conn,$companyBankID){
  $detail = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_accounts where id = '".$companyBankID."' "));  
  // return ($detail['name'].' ' .$detail['account_no']);
  return isset($detail['name'])?$detail['name']:'';

  // return $detail['name'];

}

function customerName($conn,$customerID){
  $detail = mysqli_fetch_assoc(mysqli_query($conn,"select name_title, name from kc_customers where id = '".$customerID."' "));  // and status = '1'
  // $detail = mysqli_fetch_assoc(mysqli_query($conn,"SELECT `name_title`, `name` FROM `kc_customers` WHERE `id` = $customerID"));
  $name_title = isset($detail['name_title'])?$detail['name_title']:'';
  $name= isset($detail['name'])?$detail['name']:'';
  // return ($detail['name_title']. ' ' .$detail['name']);
  return ($name_title. ' '.$name);
   
}
// farmerName copied as farmerDetails

function farmerName($conn,$farmerID){
  $detail = mysqli_fetch_assoc(mysqli_query($conn,"select name_title, name from kc_farmers where id = '".$farmerID."' "));  // and status = '1'
  return ($detail['name_title'].' ' .$detail['name']);
}

function farmerDetails($conn,$farmerID){
  $detail = mysqli_fetch_assoc(mysqli_query($conn,"select id, name_title, mobile, name from kc_farmers where id = '".$farmerID."' "));  // and status = '1'
  // return ($detail['name_title'].' ' .$detail['name'].' '.$detail['mobile'].''.$detail['id']);
  return isset($detail['name'])?$detail:array();
}
function customerDetail($conn,$customerID){
  $detail = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_customers where id = '".$customerID."' ")); // and status = '1'
  return isset($detail['name'])?$detail:array();
}

 /*
  *
  * Returns String
    Customer Name
    Customer Id
    Address and Mobile
  */
function customerNIAM($conn,$customerID,$return_array = false){
  $detail = mysqli_fetch_assoc(mysqli_query($conn,"select name_title, name, mobile, address from kc_customers where id = '".$customerID."' limit 0,1 "));
  if($return_array){
    return $detail;
  }
  return ($detail['name_title'].' ' .$detail['name']).'<br>'.' ('.customerID($customerID).')'.'<br>'.$detail['mobile'].'<br>'.$detail['address'];
}

function isOutStandingPayment($conn,$customerID,$blockID,$blockNumberID){
	$total_cr = mysqli_fetch_assoc(mysqli_query($conn,"select SUM(amount) as total_credited from kc_customer_transactions where customer_id = '".$customerID."' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."' and cr_dr = 'cr' and status = '1' "));
	$total_dr = mysqli_fetch_assoc(mysqli_query($conn,"select SUM(amount) as total_debited from kc_customer_transactions where customer_id = '".$customerID."' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."' and cr_dr = 'dr' and status = '1' "));
	if(($total_cr['total_credited']-$total_dr['total_debited']) > 0){
		return true;
	}
	return false;
}

function nextDueDate($conn,$customerID,$blockID,$blockNumberID){
	$next_due_date = mysqli_fetch_assoc(mysqli_query($conn,"select next_due_date from kc_customer_transactions where customer_id = '".$customerID."' and status = '1' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."' order by next_due_date desc limit 0,1 "));
	$upcoming_due_date = $next_due_date['next_due_date'];
	return $upcoming_due_date;
}
function isLastEmiPayment($conn,$customerID,$blockID,$blockNumberID,$nextNumber = 0){
  // echo $nextNumber;
  if($nextNumber > 0){
    $nextNumber -= 1;
  }
  // echo "select count(*) as total from kc_customer_emi where customer_id = '$customerID' and block_id = '$blockID' and block_number_id = '$blockNumberID' and emi_amount > paid_amount order by emi_date asc";die;
  $emi_details = mysqli_fetch_assoc(mysqli_query($conn,"select count(*) as total from kc_customer_emi where customer_id = '$customerID' and block_id = '$blockID' and block_number_id = '$blockNumberID' and emi_amount > paid_amount order by emi_date asc"));
  if($emi_details['total'] == 1){
    return true;
  }else{
    return false;
  }
}
function isLatePaymentApplied($conn,$customerID,$blockID,$blockNumberID,$nextDueDate){
  $isLatePaymentDetails = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_customer_transactions where customer_id = '".$customerID."' and status = '1' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."' and next_due_date = '$nextDueDate' and cr_dr = 'cr' and remarks is NOT NULL limit 0,1 "));
  return isset($isLatePaymentDetails['id'])?$isLatePaymentDetails['id']:'';
}

function saleAmount($conn,$customerID,$blockID,$blockNumberID){
  $total_cr = mysqli_fetch_assoc(mysqli_query($conn,"select SUM(amount) as total_credited from kc_customer_transactions where customer_id = '".$customerID."' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."' and cr_dr = 'cr' and status = '1' and remarks is NULL "));

  return $total_cr['total_credited'] + affectSoldAmountTotalCredited($conn,$customerID,$blockID,$blockNumberID) - affectSoldAmountTotalDebited($conn,$customerID,$blockID,$blockNumberID);
}

function saleAmountFarmer($conn,$farmerID){
  $total_cr = mysqli_fetch_assoc(mysqli_query($conn,"select SUM(amount) as total_credited from kc_farmer_transactions where farmer_id = '".$farmerID."' and cr_dr = 'cr' and status = '1' and remarks is NULL "));
  return $total_cr['total_credited'];
}

function saleAmountWithoutPLC($conn,$customerID,$blockID,$blockNumberID){
  $rate_per_sqft = mysqli_fetch_assoc(mysqli_query($conn,"select rate_per_sqft from kc_customer_blocks where customer_id = '".$customerID."' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."' and status = '1' limit 0,1 "));

  if(!isset($rate_per_sqft['rate_per_sqft'])){
    die('Something Wrong!');
  }

  $block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select block_number from kc_block_numbers where id = '".$blockNumberID."' limit 0,1 "));
  if(!isset($block_number_details['block_number'])){
    die('Something Wrong!!');
  }

  $area = mysqli_fetch_assoc(mysqli_query($conn,"select area from kc_block_numbers where block_id = '".$blockID."' and block_number = '".$block_number_details['block_number']."' and status = '1' limit 0,1 "));
  if(!isset($area['area'])){
    die('Something Wrong!!!');
  }
  return ($rate_per_sqft['rate_per_sqft'] * $area['area']);
}

function totalCredited($conn,$customerID,$blockID,$blockNumberID){
	$total_cr = mysqli_fetch_assoc(mysqli_query($conn,"select SUM(amount) as total_credited from kc_customer_transactions where customer_id = '".$customerID."' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."' and cr_dr = 'cr' and is_affect_sold_amount != '1' and status = '1' "));
  $affectSoldAmountTotalCredited = affectSoldAmountTotalCredited($conn,$customerID,$blockID,$blockNumberID);
  $affectSoldAmountTotalDebited = affectSoldAmountTotalDebited($conn,$customerID,$blockID,$blockNumberID);
  //echo $affectSoldAmountTotalDebited;
	return $total_cr['total_credited'] + $affectSoldAmountTotalCredited - $affectSoldAmountTotalDebited;
}

function totalDebited($conn,$customerID,$blockID,$blockNumberID){
	$total_dr = mysqli_fetch_assoc(mysqli_query($conn,"select SUM(amount) as total_debited from kc_customer_transactions where customer_id = '".$customerID."' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."' and cr_dr = 'dr' and is_affect_sold_amount != '1' and status = '1' "));
	return $total_dr['total_debited'];
}

function outStandingPayments($conn,$associateID = 0){
	$return_array = array();

  $query = "select ct.customer_id, ct.block_id, ct.block_number_id from kc_customer_transactions ct";
  if($associateID > 0){
    $query .= " INNER JOIN kc_customer_blocks cb ON cb.customer_id = ct.customer_id and cb.block_id = ct.block_id and cb.block_number_id = ct.block_number_id where cb.sales_person_id = '".$associateID."' and cb.status = '1' and cb.status = '1' group by cb.customer_id, cb.block_id, cb.block_number_id ";
  }else{
    $query .= " where ct.status = '1' group by ct.customer_id, ct.block_id, ct.block_number_id";
  }


	$customers = mysqli_query($conn, $query);
	$counter = 0;
	while($customer = mysqli_fetch_assoc($customers)){
		if(isOutStandingPayment($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id'])){
			$return_array[$counter]['customer_id'] = $customer['customer_id'];
			$return_array[$counter]['block_id'] = $customer['block_id'];
			$return_array[$counter]['block_number_id'] = $customer['block_number_id'];
			$return_array[$counter]['total_credited'] = totalCredited($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']);
			$return_array[$counter]['total_debited'] = totalDebited($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']);
			$return_array[$counter]['next_due_date'] = nextDueDate($conn,$customer['customer_id'],$customer['block_id'],$customer['block_number_id']);
			$counter++;
		}
	}
	return $return_array;
}

// function isOutStandingPaymentForAssociate($conn,$associateID,$customerID,$blockID,$blockNumberID){
//   $total_cr = mysqli_fetch_assoc(mysqli_query($conn,"select SUM(amount) as total_credited from kc_associates_transactions where customer_id = '".$customerID."' and associate_id = '".$associateID."' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."' and cr_dr = 'cr' and status = '1' "));
//   $total_dr = mysqli_fetch_assoc(mysqli_query($conn,"select SUM(amount) as total_debited from kc_associates_transactions where customer_id = '".$customerID."' and associate_id = '".$associateID."' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."' and cr_dr = 'dr' and status = '1' "));
//   if(($total_cr['total_credited']-$total_dr['total_debited']) > 0){
//     return true;
//   }
//   return false;
// }

// function outStandingPaymentsForAssociate($conn,$associateID = 0){
//   $return_array = array();

//   $query = "select at.customer_id, at.associate_id, at.block_id, at.block_number_id from kc_associates_transactions at";
//   if($associateID > 0){
//     $query .= " INNER JOIN kc_customer_blocks cb ON cb.customer_id = at.customer_id and cb.block_id = at.block_id and cb.block_number_id = at.block_number_id where cb.sales_person_id = '".$associateID."' and cb.status = '1' and cb.status = '1' group by cb.customer_id, cb.block_id, cb.block_number_id ";
//   }else{
//     $query .= " where at.status = '1' group by at.customer_id, at.block_id, at.block_number_id";
//   }


//   $associates = mysqli_query($conn, $query);
//   $counter = 0;
//   while($associate = mysqli_fetch_assoc($associates)){
//     if(isOutStandingPayment($conn,$associate['associate_id'],$associate['customer_id'],$associate['block_id'],$associate['block_number_id'])){
//       $return_array[$counter]['associate_id'] = $associate['associate_id'];
//       $return_array[$counter]['customer_id'] = $associate['customer_id'];
//       $return_array[$counter]['block_id'] = $associate['block_id'];
//       $return_array[$counter]['block_number_id'] = $associate['block_number_id'];
//       $return_array[$counter]['total_credited'] = associateTotalCredited($conn,$associate['associate_id']);
//       $return_array[$counter]['total_debited'] = associateTotalDebited($conn,$associate['associate_id']);
//       $counter++;
//     }
//   }
//   return $return_array;
// }

function getReceiptNumberDetail($conn,$transactionID){
  $receipt_details = mysqli_fetch_assoc(mysqli_query($conn, "select * from kc_receipt_numbers where transaction_id = '$transactionID' limit 0,1 "));
  return $receipt_details;
}
function receiptNumber($conn,$transactionID){
	$receipt_details = mysqli_fetch_assoc(mysqli_query($conn, "select receipt_id from kc_receipt_numbers where transaction_id = '$transactionID' limit 0,1 "));
	if(!isset($receipt_details['receipt_id'])){
    $transaction_details = mysqli_fetch_assoc(mysqli_query($conn, "select customer_id, block_id, block_number_id from kc_customer_transactions where id = '$transactionID' limit 0,1 "));

		$max_receipt_no = mysqli_fetch_assoc(mysqli_query($conn, "select max(receipt_id) as max_receipt_number from kc_receipt_numbers where customer_id = '".$transaction_details['customer_id']."' and block_id='".$transaction_details['block_id']."' and block_number_id='".$transaction_details['block_number_id']."' "));
		if($max_receipt_no['max_receipt_number'] == ""){
			$receipt_number = 1;
		}else{
			$receipt_number = $max_receipt_no['max_receipt_number']+1;
		}
		mysqli_query($conn,"insert into kc_receipt_numbers set	customer_id='".$transaction_details['customer_id']."', block_id='".$transaction_details['block_id']."', block_number_id='".$transaction_details['block_number_id']."', transaction_id = '$transactionID', receipt_id='".$receipt_number."' ");
		$receipt_number = 'KC'.sprintf("%05d",$receipt_number);
	}else{
		$receipt_number = 'KC'.sprintf("%05d",$receipt_details['receipt_id']);
	}
	return $receipt_number;
}


  /**
   * Created by PhpStorm.
   * User: sakthikarthi
   * Date: 9/22/14
   * Time: 11:26 AM
   * Converting Currency Numbers to words currency format
   */
function numberToWord($number){

   $no = round($number);
   $point = round($number - $no, 2) * 100;
   $hundred = null;
   $digits_1 = strlen($no);
   $i = 0;
   $str = array();
   $words = array('0' => '', '1' => 'one', '2' => 'two',
    '3' => 'three', '4' => 'four', '5' => 'five', '6' => 'six',
    '7' => 'seven', '8' => 'eight', '9' => 'nine',
    '10' => 'ten', '11' => 'eleven', '12' => 'twelve',
    '13' => 'thirteen', '14' => 'fourteen',
    '15' => 'fifteen', '16' => 'sixteen', '17' => 'seventeen',
    '18' => 'eighteen', '19' =>'nineteen', '20' => 'twenty',
    '30' => 'thirty', '40' => 'forty', '50' => 'fifty',
    '60' => 'sixty', '70' => 'seventy',
    '80' => 'eighty', '90' => 'ninety');
   $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');
   while ($i < $digits_1) {
     $divider = ($i == 2) ? 10 : 100;
     $number = floor($no % $divider);
     $no = floor($no / $divider);
     $i += ($divider == 10) ? 1 : 2;
     if ($number) {
        $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
        $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
        $str [] = ($number < 21) ? $words[$number] .
            " " . $digits[$counter] . $plural . " " . $hundred
            :
            $words[floor($number / 10) * 10]
            . " " . $words[$number % 10] . " "
            . $digits[$counter] . $plural . " " . $hundred;
     } else $str[] = null;
  }
  $str = array_reverse($str);
  $result = implode('', $str);
  $points = ($point) ?
    "and " . $words[$point / 10] . " " .
          $words[$point = $point % 10] : '';
  $return_str = ucwords($result) . "Rupees  ";
  if($point > 0){
	  $return_str .=  $points . " Paise";
  }
  return $return_str;
}


function isBlockNumberBooked($conn,$blockNumberID){
  $booking_details = mysqli_fetch_assoc(mysqli_query($conn,"select customer_id from kc_customer_blocks where block_number_id = '".$blockNumberID."' and status = '1' limit 0,1 "));
  return isset($booking_details['customer_id'])?$booking_details['customer_id']:false;
}

function latestTransactionID($conn,$customerID,$blockID,$blockNumberID){
  $latest_trans_details = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_customer_transactions where customer_id = '".$customerID."' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."' and status = '1' order by id desc limit 0,1 "));
  return isset($latest_trans_details['id'])?$latest_trans_details['id']:0;
}

function latestTransactionIDWithoutLate($conn,$customerID,$blockID,$blockNumberID){
  //echo "select id from kc_customer_transactions where customer_id = '".$customerID."' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."' and status = '1' and remarks is NULL order by id desc limit 0,1 "; die;
  $latest_trans_details = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_customer_transactions where customer_id = '".$customerID."' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."' and status = '1' and remarks is NULL order by id desc limit 0,1 "));
  return isset($latest_trans_details['id'])?$latest_trans_details['id']:0;
}

function isReminderExists($conn,$transactionID){
  $reminder_details = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_reminders where transaction_id = '".$transactionID."' limit 0,1 "));
  return isset($reminder_details['id']);
}
function reminderDetails($conn,$transactionID){
  $reminder_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, customer_id, block_id, block_number_id, transaction_id, full_name, address, block_name, block_number_name, project_name, due_amount, booking_date, gross_amount, late_amount, late_gst_amount, status, created from kc_reminders where transaction_id = '".$transactionID."' limit 0,1 "));
  return isset($reminder_details['id'])?$reminder_details:false;
}
function reminderNumber($reminderID,$remCreatedDate){
  return date("ym",strtotime($remCreatedDate)).sprintf("%06d",$reminderID);
}
function lateAmount($conn,$transactionID){
  $late_amount_details = mysqli_fetch_assoc(mysqli_query($conn,"select amount from kc_customer_transactions where status = '1' and cr_dr = 'cr' and remarks is NOT NULL and late_for_transaction_id = '".$transactionID."' limit 0,1 "));
  return isset($late_amount_details['amount'])?$late_amount_details['amount']:0;
}
function totalLateAmount($conn,$customerID,$blockID,$blockNumberID){
  $late_amount_details = mysqli_fetch_assoc(mysqli_query($conn,"select SUM(amount) as total_late_amount from kc_customer_transactions where customer_id = '".$customerID."' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."' and status = '1' and cr_dr = 'cr' and remarks is NOT NULL and late_for_transaction_id is NOT NULL "));
  return isset($late_amount_details['total_late_amount'])?$late_amount_details['total_late_amount']:0;
}

/* ------ Associate Functions -------------*/
function printBlockAssociateName($conn,$customerID,$blockID,$blockNumberID){
  $block_associate_details = blockNumberAssociateDetails($conn,$customerID,$blockID,$blockNumberID);
  if(!isset($block_associate_details['associate'])){
    return '';
  }
  $associate_details = mysqli_fetch_assoc(mysqli_query($conn,"select code, name, mobile_no from kc_associates where id = '".$block_associate_details['associate']."' limit 0,1 "));
  return isset($associate_details['name'])?$associate_details['code'].'-'.$associate_details['name'].'('.$associate_details['mobile_no'].')':'';
}
function associateName($conn,$associateID){
  $associate_details = mysqli_fetch_assoc(mysqli_query($conn,"select name, code, mobile_no from kc_associates where id = '".$associateID."' limit 0,1 "));
  return isset($associate_details['name'])?(($associate_details['code'] != '')?$associate_details['code'].'-':'').$associate_details['name'].'('.$associate_details['mobile_no'].')':0;
  // return isset($associate_details['name'])?(($associate_details['code'] != '')?$associate_details['code']:()).$associate_details['name'].'('.$associate_details['mobile_no'].')':0;
}
function associateHadCommissionForTransaction($conn,$transactionID){
  $associate_commission_details = mysqli_fetch_assoc(mysqli_query($conn,"select id from kc_associates_transactions where transaction_id = '".$transactionID."' limit 0,1 "));
 
  return isset($associate_commission_details['id'])?true:false;
}
function blockNumberAssociateDetails($conn,$customerID,$blockID,$blockNumberID){
  $block_associate_details = mysqli_fetch_assoc(mysqli_query($conn,"select associate, associate_percentage from `kc_associate_percentage` where customer_id = '".$customerID."' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."' and status = '1'  limit 0,7 "));
  
  return (isset($block_associate_details['associate']) && $block_associate_details['associate'] > 0)?$block_associate_details:array();
}
function makeAssociateCredit($conn,$transactionID){
  $transaction_details = mysqli_fetch_assoc(mysqli_query($conn,"select customer_id, block_id, block_number_id, amount from kc_customer_transactions where id = '".$transactionID."' and cr_dr = 'dr' limit 0,1 "));
  if(isset($transaction_details['customer_id']) && !associateHadCommissionForTransaction($conn,$transactionID)){
    $block_associate_details = blockNumberAssociateDetails($conn,$transaction_details['customer_id'],$transaction_details['block_id'],$transaction_details['block_number_id']);
    
    //print_r(  $block_associate_details);die;

    if(isset($block_associate_details['associate']) && isset($block_associate_details['associate_percentage']) && isset($block_associate_details['associate_percentage']) > 0){

        $associate_commision = round(($transaction_details['amount']*$block_associate_details['associate_percentage'])/100);
        if(!mysqli_query($conn,"insert into kc_associates_transactions set customer_id = '".$transaction_details['customer_id']."', block_id = '".$transaction_details['block_id']."', block_number_id = '".$transaction_details['block_number_id']."', transaction_id = '$transactionID', associate_id = '".$block_associate_details['associate']."', amount = '".$associate_commision."', cr_dr = 'cr', paid_date = '".date("Y-m-d")."', status = '1', addedon =NOW(), added_by = '".$_SESSION['login_id']."' ")){
          return false;
          //echo("Error description: " . mysqli_error($conn)); die;
        }

    }
  }
  return true;
}

function associateTotalCredited($conn,$associateID){
  $total_cr = mysqli_fetch_assoc(mysqli_query($conn,"select SUM(amount) as total_credited from kc_associates_transactions where associate_id = '".$associateID."' and status = '1' and cr_dr = 'cr' "));
  if($total_cr['total_credited']>0){
    return $total_cr['total_credited'];
  }else{
    return 0;
  }
}

function associateTotalDebited($conn,$associateID){
  $total_cr = mysqli_fetch_assoc(mysqli_query($conn,"select SUM(amount) as total_credited from kc_associates_transactions where associate_id = '".$associateID."' and status = '1' and cr_dr = 'dr' "));
  if($total_cr['total_credited']>0){
    return $total_cr['total_credited'];
  }else{
    return 0;
  }
}

function isEmiTaken($conn,$customerID,$blockID,$blockNumberID){
  $block_details = mysqli_fetch_assoc(mysqli_query($conn,"select customer_payment_type from kc_customer_blocks where customer_id = '".$customerID."' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."' and status = '1'  limit 0,1 "));
  return (isset($block_details['customer_payment_type']) && $block_details['customer_payment_type']=="EMI")?true:false;
}

function totalEmiPaid($installmentAmount,$paidAmount){
  return $total_emi = floor($paidAmount/$installmentAmount);
}

function makeEMIPaid($conn,$customerID,$blockID,$blockNumberID){
  $noerror = true;

  if(!mysqli_query($conn,"update kc_customer_emi set paid_amount = '0', paid_date = NULL where customer_id = '".$customerID."' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."'")){
    $noerror = false;
  }

  $transactions = mysqli_query($conn,"select amount, paid_date from kc_customer_transactions where customer_id = '".$customerID."' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."' and cr_dr = 'dr' and is_affect_sold_amount = '0' and status = '1' order by paid_date asc, id asc limit 1,1000 ");

  while($transaction = mysqli_fetch_assoc($transactions)){
    $totalPaidAmount = $transaction['amount'];
    $paidDate = $transaction['paid_date'];
    while($totalPaidAmount > 0){
      $nextEMIDetails = nextEMIDetails($conn,$customerID,$blockID,$blockNumberID,1);
      // echo "<pre>"; print_r($nextEMIDetails); die;
      if(isset($nextEMIDetails['id'])){
        if(($nextEMIDetails['emi_amount'] - $nextEMIDetails['paid_amount']) <= $totalPaidAmount){
          if(!mysqli_query($conn,"update kc_customer_emi set paid_amount = '".$nextEMIDetails['emi_amount']."', paid_date = '$paidDate' where id = '".$nextEMIDetails['id']."' limit 1")){
            $noerror = false;
          }
          $totalPaidAmount -= ($nextEMIDetails['emi_amount'] - $nextEMIDetails['paid_amount']);
        }else{
          if(!mysqli_query($conn,"update kc_customer_emi set paid_amount = (paid_amount + '".$totalPaidAmount."'), paid_date = '$paidDate' where id = '".$nextEMIDetails['id']."' limit 1")){
            $noerror = false;
          }
          $totalPaidAmount = 0;
        }
      }else{
        $totalPaidAmount = 0;
      }
    }
  }
  if($noerror){
    $nextEMIDetails = nextEMIDetails($conn,$customerID,$blockID,$blockNumberID,1);
    if(isset($nextEMIDetails['emi_date'])){
      $next_due_date = nextDueDate($conn,$customerID,$blockID,$blockNumberID);
      if(!mysqli_query($conn,"update kc_customer_transactions set next_due_date = '".$nextEMIDetails['emi_date']."' where customer_id = '".$customerID."' and status = '1' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."' and next_due_date = '$next_due_date' limit 1")){
        $noerror = false;
      }
    }
  }
  return $noerror;
}

// $nextNumber = 1 for next due date, 2 for 2nd next due date, 3 for 3rd next due date and so on....
function nextEMIDetails($conn,$customerID,$blockID,$blockNumberID,$nextNumber = 0){
  // echo $nextNumber;
  if($nextNumber > 0){
    $nextNumber -= 1;
  }
  $emi_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, emi_amount, paid_amount, emi_date from kc_customer_emi where customer_id = '$customerID' and block_id = '$blockID' and block_number_id = '$blockNumberID' and emi_amount > paid_amount order by emi_date asc limit $nextNumber,1"));
  // ECHO "select id, emi_amount, paid_amount, emi_date from kc_customer_emi where customer_id = '$customerID' and block_id = '$blockID' and block_number_id = '$blockNumberID' and emi_amount > paid_amount order by emi_date asc limit $nextNumber,1";die;
  if($emi_details){
    return $emi_details;
  }else{
   return false;
  }
}

function dateOfBooking($conn,$customerID,$blockID,$blockNumberID){
  $first_payment_details = mysqli_fetch_assoc(mysqli_query($conn,"select paid_date from kc_customer_transactions where customer_id = '$customerID' and block_id = '$blockID' and block_number_id = '$blockNumberID' and cr_dr = 'dr' and status = '1' order by paid_date asc limit 0,1"));
  return isset($first_payment_details['paid_date'])?$first_payment_details['paid_date']:'';
}

function downPayment($conn,$customerID,$blockID,$blockNumberID){
  $first_payment_details = mysqli_fetch_assoc(mysqli_query($conn,"select amount from kc_customer_transactions where customer_id = '$customerID' and block_id = '$blockID' and block_number_id = '$blockNumberID' and cr_dr = 'dr' and status = '1' order by paid_date asc limit 0,1"));
  return isset($first_payment_details['amount'])?$first_payment_details['amount']:0;
}

function affectSoldAmountTotalDebited($conn,$customerID,$blockID,$blockNumberID){
  $total_dr = mysqli_fetch_assoc(mysqli_query($conn,"select SUM(amount) as total_debited from kc_customer_transactions where customer_id = '".$customerID."' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."' and cr_dr = 'dr' and is_affect_sold_amount = '1' and status = '1' "));
  return $total_dr['total_debited'];
}

function affectSoldAmountTotalCredited($conn,$customerID,$blockID,$blockNumberID){
  $total_cr = mysqli_fetch_assoc(mysqli_query($conn,"select SUM(amount) as total_credited from kc_customer_transactions where customer_id = '".$customerID."' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."' and cr_dr = 'cr' and is_affect_sold_amount = '1' and status = '1' "));
  return $total_cr['total_credited'];
}

function ratePerSq($conn,$customerID,$blockID,$blockNumberID){
  $saleAmount = saleAmount($conn,$customerID,$blockID,$blockNumberID);
  // print_r($saleAmount);
  // echo "select area from kc_block_numbers where id = '".$blockNumberID."' limit 0,1 ";die();
  $area = mysqli_fetch_assoc(mysqli_query($conn,"select area from kc_block_numbers where id = '".$blockNumberID."' limit 0,1 ")); // and status = '1'
  if(!isset($area['area'])){
    return "Error";
  }
  return $saleAmount/$area['area'];
}

function totalEmiAmount($conn,$customerID,$blockID,$blockNumberID){
  $emi_details = mysqli_fetch_assoc(mysqli_query($conn,"select SUM(emi_amount) as total_emi_amount from kc_customer_emi where customer_id = '$customerID' and block_id = '$blockID' and block_number_id = '$blockNumberID' "));
  return $emi_details['total_emi_amount'];
}
// function convertDataToChartForm($data)
// {
//     $newData = array();
//     $firstLine = true;

//     foreach ($data as $dataRow)
//     {
//         if ($firstLine)
//         {
//             $newData[] = array_keys($dataRow);
//             $firstLine = false;
//         }

//         $newData[] = array_values($dataRow);
//     }

//     return $newData;
// }
function exportPLCList($conn,$block_number_id){
  $plcs = mysqli_query($conn,"select * from kc_block_number_plc where block_number_id = '".$block_number_id."' ");
  while($plc = mysqli_fetch_assoc($plcs)){
    $plc_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_plc where id = '".$plc['plc_id']."' limit 0,1 "));
    return $plc_details['name'].' ('.$plc_details['plc_percentage'].'%)<br>';
  }
}
function countBlockNo($conn,$blockID){
  $date = date("Y-m-d 00:00:00");
  $block = mysqli_fetch_assoc(mysqli_query($conn,"SELECT Count(block_number_id) as block_no from kc_customer_blocks where block_id = '".$blockID."'  and status = '1' and addedon >= '".$date."'"));
  return $block['block_no']?$block['block_no']:0;

}
function AVRPaidAmount($conn){
  $abr = mysqli_fetch_array(mysqli_query($conn,"SELECT sum(paid_amount) AS total_paid from kc_avr_receipt where status = 1 and deleted is null"));
  return $abr['total_paid']?$abr['total_paid']:0;
}
function totalPendingEMI($conn,$customerID,$blockID,$blockNumberID,$from_date,$to_date){
  $emi_count = mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(*) as emi from kc_customer_emi where customer_id = '".$customerID."' and block_id = '$blockID' and block_number_id = '$blockNumberID' and emi_date BETWEEN '".date("Y-m-d",strtotime($from_date))."' AND '".date("Y-m-d",strtotime($to_date))."' and emi_amount > paid_amount"));
  return $emi_count['emi'];
}

function totalPaidEMIAmount($conn,$customerID,$blockID,$blockNumberID,$from_date,$to_date){
 $paid_amount = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(paid_amount) as paid_amount from kc_customer_emi where customer_id = '".$customerID."' and block_id = '$blockID' and block_number_id = '$blockNumberID' and emi_date BETWEEN '".date("Y-m-d",strtotime($from_date))."' AND '".date("Y-m-d",strtotime($to_date))."'"));
  return $paid_amount['paid_amount'];
}

function totalPendingEMIAmount($conn,$customerID,$blockID,$blockNumberID,$from_date,$to_date){
 $emi_amount = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(emi_amount - paid_amount) as emi_amount from kc_customer_emi where customer_id = '".$customerID."' and block_id = '$blockID' and block_number_id = '$blockNumberID' and emi_date BETWEEN '".date("Y-m-d",strtotime($from_date))."' AND '".date("Y-m-d",strtotime($to_date))."' and emi_amount > paid_amount"));
  return $emi_amount['emi_amount'];
}

function userCan($conn,$userId,$privilegeName){
  
  $user_detail = mysqli_fetch_assoc(mysqli_query($conn,"select login_type from kc_login where id = '".$userId."' limit 0,1 "));


  if(($user_detail['login_type'] == 'super_admin') || ($user_detail['login_type'] == 'super2admin')){
    return true;
  }
  $checkExistance = mysqli_fetch_assoc(mysqli_query($conn,"select status from kc_user_privileges where privileges_name = '$privilegeName' and user_id = '$userId' limit 0,1"));
  return (boolean) (isset($checkExistance['status']) && $checkExistance['status'] == 1);
}

// ADDED ON 22-07-2022 downwards

function userCanView($conn,$userId,$privilegeName){ 

  $checkExistance = mysqli_fetch_assoc(mysqli_query($conn,"select status from kc_user_privileges where privileges_name = '$privilegeName' and user_id = '$userId' limit 0,1"));
  return (boolean) (isset($checkExistance['status']) && $checkExistance['status'] == 1);
}

// Upside 


function getLastPayment($conn,$customerId,$blockId,$blockNumberId){
  $last_amt_date = mysqli_fetch_assoc(mysqli_query($conn,"SELECT amount,paid_date from kc_customer_transactions where customer_id = '".$customerId."' and block_id = '".$blockId."' and block_number_id = '".$blockNumberId."' and cr_dr = 'dr' and payment_type != '' ORDER BY paid_date DESC limit 0,1"));
  return isset($last_amt_date)?$last_amt_date:0;//and cr_dr = 'dr' and payment_type != '' => For discounted transaction.
}

//isset($last_amt_date['amount'])?$last_amt_date['amount']:0
function getSmsBalance(){
  $url = "http://sms.quickinfotech.co.in/api/balance.php?authkey=e295c16f00c29cb5f3f892d4b7ab8705&route=B";

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $curl_scraped_page = curl_exec($ch);
  curl_close($ch);
  $response = $curl_scraped_page;
  return number_format(str_replace('"','',$response));
}

function getCustomerBlockNumberNames($conn,$customerID){
  $customer_blocks = mysqli_query($conn,"select block_id, block_number_id from kc_customer_blocks where customer_id = '".$customerID."' ");

  $blockDetails = [];
  while($customer_block = mysqli_fetch_assoc($customer_blocks)){
    $project_name = blockProjectName($conn,$customer_block['block_id']);
    $block_name = blockName($conn,$customer_block['block_id']); 
    $block_number_name = blockNumberName($conn,$customer_block['block_number_id']);

    $blockDetails[] = $project_name.'-'.$block_name.'-'.$block_number_name;
  }
  return $blockDetails;
}
function getPartAmount($conn,$customerId,$blockId,$blockNumberId) {
  $total_cr = mysqli_fetch_assoc(mysqli_query($conn , "SELECT SUM(amount) AS total_cr FROM kc_customer_transactions WHERE customer_id = '".$customerId."' AND cr_dr = 'cr' AND block_id = '".$blockId."' AND block_number_id = '".$blockNumberId."' AND status = 1 "));
   $total_dr =  mysqli_fetch_assoc(mysqli_query($conn , "SELECT SUM(amount) AS total_dr FROM kc_customer_transactions WHERE customer_id = '".$customerId."' AND cr_dr = 'dr' AND block_id = '".$blockId."' AND block_number_id = '".$blockNumberId."' AND status = 1 "));
   return $total_cr['total_cr']-$total_dr['total_dr'];
}

function csrf_token($length = 40) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    $_SESSION['csrf_token'] = $randomString;
    // return $randomString;
}

// Added afterwards July 2022

  function getNumberofInstallments($conn,$block_id,$customer_id,$block_number_id){

    $installment_index = mysqli_query($conn,"SELECT count(*) AS installment_index from kc_customer_emi where customer_id = '$customer_id' and block_id = '$block_id' and block_number_id = '$block_number_id' ");
    
    return $installment_index;
  }

  function saleAmount2($conn,$customerID,$blockID,$blockNumberID){ 

    $total_cr = mysqli_fetch_assoc(mysqli_query($conn,"select SUM(amount) as total_credited from kc_customer_transactions where customer_id = '".$customerID."' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."' and cr_dr = 'cr' and status = '1' "));

    return $total_cr['total_credited'] ;
    
  }

  function pendingtransactions($conn,$customerID,$blockID,$blockNumberID){
    
    $emi_amount = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(emi_amount - paid_amount) as emi_amount from kc_customer_emi where customer_id = '".$customerID."' and block_id = '$blockID' and block_number_id = '$blockNumberID'  and emi_amount > paid_amount"));

    return $emi_amount['emi_amount'];
  }


  function IsExtraChargeApplied($conn,$customer,$block,$block_number_id){
    $extra_amount = mysqli_fetch_assoc(mysqli_query($conn,"select amount from kc_customer_transactions where customer_id = '$customer' and block_id ='$block' and block_number_id ='$block_number_id' and cr_dr = 'cr' and add_transaction_remarks = 'Extra Charges Applied' "));

    return isset($extra_amount['amount'])? $extra_amount['amount'] : 0;
  }

  function extraPaidAmount($conn,$customer,$block,$block_number_id){
    $credited = mysqli_fetch_assoc(mysqli_query($conn,"Select sum(amount) as credit from kc_customer_transactions where customer_id = '$customer' and block_id ='$block' and block_number_id ='$block_number_id' and cr_dr = 'cr' "));
    $debited = mysqli_fetch_assoc(mysqli_query($conn,"Select sum(amount) as debited from kc_customer_transactions where customer_id = '$customer' and block_id ='$block' and block_number_id ='$block_number_id' and cr_dr = 'dr'"));
  
    if(($credited['credit']-$debited['debited'])<0){
      return $credited['credit']-$debited['debited'];
    }
    
    return 0;
  }

  function makeemipaidforExtraAmount($conn,$customer,$block,$block_number_id,$rate,$emi_date,$paid_date,$extra_amount){
    
    // echo $extra_amount; die;
    // echo $rate; die;
      $error = false ;
      // return $rate;
      if($extra_amount>$rate){
        
        // return $extra_amount;
          $count = ceil($extra_amount/ $rate);
          $exact_count = $extra_amount/$rate;
          $takethis = $exact_count - (int)$exact_count ; 
          for($i=1; $i<=$count; $i++){

            if($count==1){
              $rate = $extra_amount/$rate;
            }
            if($i==$count){
              $rate = $takethis ;
            }

            if(!mysqli_query($conn, "update kc_customer_emi set paid_amount ='$rate' where paid_amount ='0.00' and customer_id ='$customer' and block_id ='$block' and block_number_id ='$block_number_id'"));
            {
               $error = true ;
            }
            $extra_amount-=$rate;
          }
      }else{
        $emi_Details = nextEMIDetails($conn,$customer,$block,$block_number_id,1);
        // print_r($emi_Details);die;
        if(!mysqli_query($conn, "update kc_customer_emi set paid_amount ='$extra_amount',paid_date ='$paid_date' where id =  '".$emi_Details['id']."' "));
        {
          $error = "update kc_customer_emi set paid_amount ='".$extra_amount."',paid_date ='".$paid_date."' where id = '".$emi_Details['id']."' limit 1";       
          return $error;
        }
      }

      if(!$error){
        return true;
      }
      
      return false;
    }

    function saleAmountAfterExtraCharge($conn,$customerID,$blockID,$blockNumberID){
      $total_cr = mysqli_fetch_assoc(mysqli_query($conn,"select SUM(amount) as total_credited from kc_customer_transactions where customer_id = '".$customerID."' and block_id = '".$blockID."' and block_number_id = '".$blockNumberID."' and cr_dr = 'cr' and status = '1' and remarks is NULL "));
    
      return $total_cr['total_credited'] + affectSoldAmountTotalCredited($conn,$customerID,$blockID,$blockNumberID) - affectSoldAmountTotalDebited($conn,$customerID,$blockID,$blockNumberID) + IsExtraChargeApplied($conn,$customerID,$blockID,$blockNumberID);
    }

    function isUserDisabled($conn,$id){
      $query = mysqli_fetch_assoc(mysqli_query($conn,"SELECT `deleted_at` FROM `kc_login` WHERE `id`='$id' "));

      return (boolean) !empty($query['deleted_at']);
    }
    function expensesTypeName($conn,$id){
      // print_r($id);die;
      $query = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_expense_types where `id`='$id' AND deleted_at is null and deleted_by is null  "));
      // print_r($query['expense_name']);die;
      return $query['expense_name'];
    }
?>
