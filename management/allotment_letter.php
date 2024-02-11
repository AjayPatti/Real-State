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
$customer_block_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, block_id, block_number_id, customer_id, final_rate from kc_customer_blocks where id = '".$customer_blocks_id."' and status = '1' limit 0,1 "));

if(!isset($customer_block_details['id'])){
	die;
}
$customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_customers where status = '1' and id = '".$customer_block_details['customer_id']."' limit 0,1 "));
$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_blocks where id = '".$customer_block_details['block_id']."' limit 0,1 "));
$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_block_numbers where id = '".$customer_block_details['block_number_id']."' limit 0,1 "));
$project_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_projects where id = '".$block_details['project_id']."' limit 0,1 "));
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

<body>
	<button onClick="window.print();" class="hide button">Print</button>
    <table width="92%" border="0" style="margin-left:auto; margin-right:auto;">
      <tbody>
        <tr>
          <td style="width:30%; height:100px;" colspan="6">
          	<div style="float:left;">
            	<?php /*?><img src="/<?php echo $host_name; ?>/img/logo.png" style="width:140; height:115px; margin-left:auto; margin-right:auto;" title="WCC Pvt. Ltd." title="WCC Pvt. Ltd." /><?php */?>
            </div>
            <div style="float:left; margin-left:20px; margin-top:30px;">
            	<?php /*?><p style="text-align:justify;">
                	<span style="font-size:24px; font-weight:bolder; color:#e02b20;">WCC Pvt. Ltd.</span>
                    <br />
                    <em><strong>Reg. Off. :</strong> 3/15, Vinay Khand, Gomti Nagar, Lucknow - 226010<br> </em>
                    <em>Ph. No.: 0522 4002856 / Website: www.uvhomes.co.in</em>
                </p><?php */?>
                
            </div>
          </td>
        </tr>
        <tr>
        	<td colspan="6" align="center"><u><strong style="font-size:16px;font-family:Cambria, 'Hoefler Text', 'Liberation Serif', Times, 'Times New Roman', serif;">Allotment Letter</strong></u></td>
        </tr>
        <tr>
          <td colspan="6" nowrap><p>To,<br>
            <?php echo $customer_details['name_title'].' '.$customer_details['name'].' ('.customerID($customer_details['id']).')'; ?><br>
            <?php echo $customer_details['address']; ?>
          </p></td>
          
        </tr>
        <tr>
        	<td colspan="6">Sub - Allotment of project <strong>"<?php echo $project_details['name']; ?>"</strong> Product- Plot/Land Block- <strong> <?php echo $block_details['name'];?></strong> Unit No. <strong><?php echo $block_number_details['block_number']; ?></strong> in <strong><?php echo $project_details['name']; ?>.</strong>
        </tr>
        <?php $saleAmount = saleAmount($conn,$customer_details['id'],$block_details['id'],$block_number_details['id']); ?>
        <tr>
        	<td colspan="6">
            	<br>
            	Dear Sir/Madam,<br>
                <p>We are pleased to inform you that property number <strong><?php echo $block_number_details['block_number']; ?></strong> measuring approximately <strong><?php echo $block_number_details['area']; ?>(sq. ft)</strong> has been allotted to you in our captioned project as a total of basic(BSP) cost of <strong>Rs. <?php echo number_format($saleAmount,2); ?>/- (<?php echo numberToWord($saleAmount); ?> Only).</strong></p>
                <p>You are requested to ensure timely payment of instalments. All the terms and conditions of allotment shall remain the as mentioned in the application form.</p>
                <p>Thanking and assuring you of your best services at all times.</p>
            </td>
        </tr>
        <tr>
        	<td colspan="6">
                <br><br>
                Yours faithfully
                <br>
                For <strong>WCC Pvt. Ltd.</strong>
            </td>
        </tr>
        <tr>
        	<td colspan="6">
            	<br>
            	<strong>Important:</strong>
                <ol>
                	<li style="padding:3px 0;">Delayed payments shall attract interest as per terms and condition of the Application form.</li>
                    <li style="padding:3px 0;">Strict Adherence of the installment schedule as agreed upon by you is solicited.</li>
                    <li style="padding:3px 0;">If the purchaser fails to pay the installment or unable to abide by the condition laid by the company then the company has cancel the booking with immediate effect.</li>
                    <li style="padding:3px 0;">Any Prime Location Charges(PLC) as applicable on the said plot will be charged from the purchaser at the time of registry of said plot.</li>
                </ol>
            </td>
            
        </tr>
      </tbody>
    </table>

</body>
</html>