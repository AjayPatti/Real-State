<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");

if(!isset($_GET['cb']) || !is_numeric($_GET['cb']) || !($_GET['cb'] > 0)){
	die;
}

$customer_blocks_id = $_GET['cb'];
$customer_block_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, block_id, block_number_id, customer_id, final_rate, addedon from kc_customer_blocks where id = '".$customer_blocks_id."' and status = '1' limit 0,1 "));

if(!isset($customer_block_details['id'])){
    die;
}

$latestTransactionID = latestTransactionID($conn,$customer_block_details['customer_id'],$customer_block_details['block_id'],$customer_block_details['block_number_id']);

if(!$latestTransactionID){
    die("Something Went Wrong!");
}

if(!isReminderExists($conn,$latestTransactionID)){

    $customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select name_title, name, address from kc_customers where status = '1' and id = '".$customer_block_details['customer_id']."' limit 0,1 "));
    $block_details = mysqli_fetch_assoc(mysqli_query($conn,"select name, project_id from kc_blocks where id = '".$customer_block_details['block_id']."' limit 0,1 "));
    $block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select block_number from kc_block_numbers where id = '".$customer_block_details['block_number_id']."' limit 0,1 "));
    $project_details = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_projects where id = '".$block_details['project_id']."' limit 0,1 "));

    $full_name = $customer_details['name_title'].' '.$customer_details['name'];
    $address = $customer_details['address'];
    $block_name = $block_details['name'];
    $block_number_name = $block_number_details['block_number'];
    $project_name = $project_details['name'];

    $total_cr = totalCredited($conn,$customer_block_details['customer_id'],$customer_block_details['block_id'],$customer_block_details['block_number_id']);
    $total_dr = totalDebited($conn,$customer_block_details['customer_id'],$customer_block_details['block_id'],$customer_block_details['block_number_id']);
    $due_amount = $total_cr-$total_dr;

    $booking_date = date("Y-m-d",strtotime($customer_block_details['addedon']));
    $gross_amount = $customer_block_details['final_rate'];

    $latestTransactionIDWithoutLate = latestTransactionIDWithoutLate($conn,$customer_block_details['customer_id'],$customer_block_details['block_id'],$customer_block_details['block_number_id']);
   
    $late_amount = lateAmount($conn,$latestTransactionIDWithoutLate);
    $late_gst_amount = $late_amount - round($late_amount*100/(100+12));

    
    //echo "insert into kc_reminders set customer_id = '".$customer_block_details['customer_id']."', block_id = '".$customer_block_details['block_id']."', block_number_id = '".$customer_block_details['block_number_id']."', transaction_id = '$latestTransactionID', full_name = '$full_name', address = '$address', block_name = 'block_name', block_number_name = '$block_number_name', project_name = '$project_name', due_amount = '$due_amount', booking_date = '$booking_date', gross_amount = '$gross_amount', late_amount = '$late_amount', late_gst_amount = '$late_gst_amount', status = '1', created =NOW()"; die;

    mysqli_query($conn,"insert into kc_reminders set customer_id = '".$customer_block_details['customer_id']."', block_id = '".$customer_block_details['block_id']."', block_number_id = '".$customer_block_details['block_number_id']."', transaction_id = '$latestTransactionID', full_name = '$full_name', address = '$address', block_name = '$block_name', block_number_name = '$block_number_name', project_name = '$project_name', due_amount = '$due_amount', booking_date = '$booking_date', gross_amount = '$gross_amount', late_amount = '$late_amount', late_gst_amount = '$late_gst_amount', status = '1', created =NOW()");
}

$reminder_details = reminderDetails($conn,$latestTransactionID);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>WCC Pvt. Ltd.</title>
<style type="text/css" media="print">
.hide{
	display:none;
}
</style>
<style type="text/css">
.button{
	background-color: #008CBA; border: none;color: white;padding: 15px 32px;text-align: center;text-decoration: none;font-size: 16px;cursor:pointer;
}
</style>
</head>

<body style="font-size:12px;">
	<button onClick="window.print();" class="hide button">Print</button>
    <table width="92%" border="0" style="margin-left:auto; margin-right:auto;">
      <tbody>
        <tr>
            <td colspan="4">
              	<div style="float:left;">
                	Reminder No.: <strong><?php echo reminderNumber($reminder_details['id'],$reminder_details['created']); ?></strong><br>
                    Customer ID: <strong><?php echo customerID($reminder_details['customer_id']); ?></strong><br>
                    <strong><?php echo $reminder_details['full_name']; ?><br>
                    <?php echo $reminder_details['address']; ?><br>
                    Uttar Pradesh,<br>
                    India</strong>
                </div>
            </td>
            <td>
                <img src="/<?php echo $host_name; ?>/img/logo.png" style="width:140; height:115px; margin-left:auto; margin-right:auto;" title="WCC Pvt. Ltd." title="WCC Pvt. Ltd." />
            </td>
        </tr>
        
        <tr>
        	<td colspan="6">Subject - Allotment of Unit no. <strong> <?php echo $reminder_details['block_name'];?></strong>/<strong><?php echo $reminder_details['block_number_name']; ?></strong> in <strong><?php echo $reminder_details['project_name']; ?></strong> located at Gosaiganj lucknow - sultanpur Road, Lucknow UP.
        </tr>
        <?php $saleAmount = saleAmount($conn,$reminder_details['customer_id'],$reminder_details['block_id'],$reminder_details['block_number_id']); ?>
        <tr>
        	<td colspan="6">
            	Warm greetings from WCC pvt. ltd.<br>
                <p>This is in furtherance to our payment request/demand notice, where in you were requested to remit the payments in terms and condition of the allotment letter.</p>
                <p>On perusal of your account maintained by us, we have notices that, a sum of Rs. <?php echo number_format($reminder_details['due_amount']-$reminder_details['late_amount'],2); ?>/- still remains outstanding and is overdue till date, which needs to be remitted by you immediately.Please note that non-payment of the said amount shall lead to breach of the Terms of the application dated <?php echo date("d-M-Y",strtotime($reminder_details['booking_date'])); ?> submitted by you and the Schedule of Payment attached with the allotment letter.<br>The said amount of Rs. <?php echo number_format($reminder_details['due_amount']-$reminder_details['late_amount'],2); ?>/- has to be remitted immediately along with Delayed Payment Chargres of Rs. <?php echo number_format($reminder_details['late_amount'],2); ?> /- in following manner by Cheque/Draft of RTGS.</p>
                <table border="1" style="border-collapse: collapse;" cellpadding="4" width="100%">
                    <thead>
                        <tr>
                            <th>Overdue Payable Components</th>
                            <th>Gross Amount</th>
                            <th>ST/GST</th>
                            <th>Total Payable (Rs.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><sup>2</sup>Basic</td>
                            <td align="right"><?php echo number_format($reminder_details['gross_amount']); ?></td>
                            <td align="right">0</td>
                            <td align="right"><?php echo number_format($reminder_details['gross_amount']); ?></td>
                        </tr>
                        <tr>
                            <td><sup>3</sup>Delayed Payment Charges(<sup>*</sup>including GST @ 12%)</td>
                            <td align="right"><?php echo number_format($reminder_details['late_amount']-$reminder_details['late_gst_amount']); ?></td>
                            <td align="right"><?php echo number_format($reminder_details['late_gst_amount']); ?></td>
                            <td align="right"><?php echo number_format($reminder_details['late_amount']); ?></td>
                        </tr>
                        <tr>
                            <td align="right"><strong>Total</strong></td>
                            <td align="right"><strong><?php echo number_format($reminder_details['gross_amount']+$reminder_details['late_amount']-$reminder_details['late_gst_amount']); ?></strong></td>
                            <td align="right"><strong><?php echo number_format($reminder_details['late_gst_amount']); ?></strong></td>
                            <td align="right"><strong><?php echo number_format($reminder_details['gross_amount']+$reminder_details['late_amount']); ?></strong></td>
                        </tr>
                    </tbody>
                </table>

                <p>Kindly note that the delayed payment charges are payable by you from the "due date" of the said demand, as stipulated in the terms and condition of the allotment letter and/or as demanded by the Company, till the actual payment is received.In the event if you fail to make the payment of the aforesaid amount, within a period of 15 days from the date of this letter, the Company reserves the right to take appropriate steps in accordance with the terms of the Buyer's Agreement I Allotment Letter.Should you require any assistance, please write to info@uvhomes.co.in or call us on - 0522-4002856
            </td>
        </tr>
        <tr>
        	<td colspan="6">
                Warm regards,
                <br>
                For <strong>WCC PVT. LTD.</strong>
            </td>
        </tr>
        <tr>
            <td colspan="6">
                <br><br>
                (Authorized Signatory)
            </td>
        </tr>
        <tr>
        	<td colspan="6">
            	<br>
            	<strong>Note:</strong>
                <ul style="font-size:10px;">
                	<li style="padding:3px 0;">Please ignore this communication if you have already remitted the above payment/TGS.</li>
                    <li style="padding:3px 0;">It shall be the sole responsibility of nonresident foreign national of indian origin to comply with the provisions of Foreign Exchange Management Act 1999 or statutory enactments or amendments Thereof & rules & regulationsof the Reserve Bank of India.Kindly make your payments from NRE/NRO Account only.</li>
                    <li style="padding:3px 0;">Service Tax as applicable wills believed for all payments on or after 1stjuly2010.</li>
                    <li style="padding:3px 0;">In the event a communication in the form of reminder I Final Notice has been issued to you for an outstanding payment(s), not with standing this reminder, the outstanding payment(s) has to be made in the period stipulated in such communication, failing which the Company shall be entitled to take action as stipulated in such communication.</li>
                </ol>
            </td> 
        </tr>
        <tr>
            <td colspan="6">
                <br>
                <strong>UVHomes Pvt. Ltd.</strong>
                <br>
                <strong>B-3/15, VINAY KHAND GOMTINAGAR LUCKNOW U.P.</strong>
            </td>
        </tr>
      </tbody>
    </table>

</body>
</html>