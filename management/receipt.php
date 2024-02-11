<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");

if(!isset($_GET['receipt']) || !is_numeric($_GET['receipt']) || !($_GET['receipt'] > 0)){
	die;
}

$transaction_id = $_GET['receipt'];
$transaction_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, block_id, block_number_id, customer_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, addedon from kc_customer_transactions where id = '".$transaction_id."' and status = '1' limit 0,1 "));

if(!isset($transaction_details['id']) || $transaction_details['cr_dr'] != 'dr' || !($transaction_details['amount'] > 0)){
	die;
}

$customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_customers where status = '1' and id = '".$transaction_details['customer_id']."' limit 0,1 "));
$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_blocks where id = '".$transaction_details['block_id']."' limit 0,1 "));
$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select block_number, area from kc_block_numbers where id = '".$transaction_details['block_number_id']."' limit 0,1 "));

//echo "<pre>"; print_r($customer_details); die;
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>WCC Real Estate Pvt. Ltd.</title>
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

<body>
	<button onClick="window.print();" class="hide button">Print</button>
    <table width="92%" border="0" style="margin-left:auto; margin-right:auto;">
      <tbody>
        <tr><td colspan="6" height="150">&nbsp;</td></tr>
        <tr>
          <?php /*?><td style="width:30%; height:150px;" colspan="5"><div style="float:left;"><img src="/<?php echo $host_name; ?>/img/logo.png" style="width:140px; height:115px; margin-left:auto; margin-right:auto;" title="WCC Pvt. Ltd." title="WCC Pvt. Ltd." /></div><div style="float:left; margin-left:20px; margin-top:30px;"><p style="text-align:justify;"><span style="font-size:24px; font-weight:bolder; color:#e02b20;">WCC Pvt. Ltd.</span><br /><em><strong>Reg. Off. :</strong> 3/15, Vinay Khand, Gomti Nagar, Lucknow - 226010<br> </em><em>Ph. No.: 0522 4002856 / Website: www.uvhomes.co.in</em></p></div></td>
          <td align="center" style="font-size:24px; font-weight:bolder; text-transform:uppercase; color:#e02b20;">Receipt</td><?php */?>
          <td colspan="6" align="center" style="font-size:20px; font-weight:bolder; text-transform:uppercase;">Receipt</td>
        </tr>
        <tr>
        	<td colspan="6">&nbsp;</td>
        </tr>
        <tr>
          <td width="15%" nowrap>Customer Code</td>
          <td width="2%">:</td>
          <td width="58%"><?php echo customerID($customer_details['id']); ?></td>
          <td width="10%" nowrap>Receipt No.</td>
          <td width="2%;">:</td>
          <td width="13%"><?php echo receiptNumber($conn,$transaction_details['id']); ?></td>
        </tr>
        <tr>
          <td colspan="3">&nbsp;</td>
          <td>Date</td>
          <td>:</td>
          <td><?php echo date("d.m.Y"); //strtotime($transaction_details['addedon']) ?></td>
        </tr>
        <tr>
          <td nowrap>RECEIVED with thanks from</td>
          <td>:</td>
          <td colspan="4" nowrap><?php echo $customer_details['name_title'].' '.$customer_details['name']; ?></td>
        </tr>
        <tr>
          <td>Co-owner(s)</td>
          <td>:</td>
          <td colspan="4"><?php echo $customer_details['nominee_name']; ?></td>
        </tr>

        <?php if(trim($customer_details['parent_name']) != ''){ ?>
          <tr>
            <td><?php echo $customer_details['parent_name_relation']; ?> of </td>
            <td>:</td>
            <td colspan="4"><?php echo $customer_details['parent_name_sub_title']; ?> <?php echo $customer_details['parent_name']; ?></td>
          </tr>
        <?php }else{ ?>
          <tr><td colspan="6">&nbsp;</td></tr>
        <?php } ?>
        <tr>
          <td nowrap>Payment in respect of</td>
          <td>:</td>
          <td colspan="4"><?php echo ucfirst($transaction_details['payment_type']); //($transaction_details['addedon'] > date("Y-m-d",strtotime("2018-10-26")) && $transaction_details['payment_type'] == "Cash")?"Payment":$transaction_details['payment_type']; ?></td>
        </tr>
        <tr>
          <td><?php echo ($transaction_details['payment_type'] == "Cheque" || $transaction_details['payment_type'] == "DD" || $transaction_details['payment_type'] == "NEFT" || $transaction_details['payment_type'] == "RTGS")?$transaction_details['payment_type']:"Cheque"; ?> No.</td>
          <td>:</td>
          <td>
          	<?php
			if($transaction_details['payment_type'] == "Cheque" || $transaction_details['payment_type'] == "DD" || $transaction_details['payment_type'] == "NEFT" || $transaction_details['payment_type'] == "RTGS"){
				echo $transaction_details['cheque_dd_number'];
			}else{
				echo "NA";
			}
			?>
          </td>
          <td>Date</td>
          <td>:</td>
          <td><?php echo date("d.m.Y",strtotime($transaction_details['paid_date'])); ?></td>
        </tr>
        <tr>
          <td>Drawn on</td>
          <td>:</td>
          <td colspan="4">
          	<?php
			if($transaction_details['payment_type'] == "Cheque" || $transaction_details['payment_type'] == "DD"|| $transaction_details['payment_type'] == "NEFT" || $transaction_details['payment_type'] == "RTGS"){
				echo $transaction_details['bank_name'];
			}else{
				echo "NA";
			} ?>
          </td>
        </tr>
        <tr>
          <td colspan="6">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="6"><table width="100%" border="1" style="border-collapse:collapse;">
            <tbody>
              <tr>
                <td align="center"><strong>S.No.</strong></td>
                <td align="center"><strong>Description</strong></td>
                <td align="center"><strong>Amount (Rs.)</strong></td>
              </tr>
              <tr>
                <td align="center">01</td>
                <td><?php echo $block_details['name']; ?> - PLOT NO. <?php echo $block_number_details['block_number']; ?> (Area - <?php echo $block_number_details['area']; ?> Sq. Ft.)</td>
                <td align="center"><?php echo number_format($transaction_details['amount'],2); ?></td>
              </tr>
            </tbody>
          </table></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>Rupees</td>
          <td>:</td>
          <td>&nbsp;</td>
          <td colspan="3" nowrap>For <strong>WCC Real Estate Pvt. Ltd.</strong></td>
        </tr>
        <tr>
          <td nowrap><strong><?php echo numberToWord($transaction_details['amount']); ?></strong></td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td nowrap>Subject to realization of Cheque / Draft</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td colspan="3" nowrap>Authorized Signature</td>
        </tr>
      </tbody>
    </table>

</body>
</html>