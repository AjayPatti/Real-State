<?php
ob_start();
session_start();

$return_str = '';

if(!isset($_POST['archive_customer_block_id']) || !is_numeric($_POST['archive_customer_block_id']) || !($_POST['archive_customer_block_id'] > 0)){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$archive_customer_block_id = $_POST['archive_customer_block_id'];

$block_booking_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, customer_id, block_id, block_number_id, addedon, deleted from kc_customer_blocks_hist where id = '".$archive_customer_block_id."' and action_type = 'Cancel Booking' limit 0,1 "));
if(!isset($block_booking_details['id'])){
    echo "No Record Found";
    die;
}
$customer_id = $block_booking_details['customer_id'];
$block_id = $block_booking_details['block_id'];
$block_number_id = $block_booking_details['block_number_id'];

$transactions = mysqli_query($conn,"select id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, remarks, add_transaction_remarks, addedon from kc_customer_transactions_hist where customer_id = '".$customer_id."' and block_id = '$block_id' and block_number_id = '$block_number_id' and status = '1' and is_affect_sold_amount != '1' and action_type = 'Cancel Booking' and deleted >= '".date("Y-m-d H:i:s",strtotime($block_booking_details['deleted']." -2 minutes"))."' and deleted <= '".date("Y-m-d H:i:s",strtotime($block_booking_details['deleted']." +2 minutes"))."' ");
?>
<tbody>
	<tr>
    	<th width="76%">Details</th>
        <td>Credit</td>
        <td>Debit</td>
    </tr>
<?php
$credit = 0;
$debit = 0;
$counter = 1;
while($transaction = mysqli_fetch_assoc($transactions)){
	?>
    
    <?php
	if($transaction['cr_dr'] == "cr"){
		$credit += $transaction['amount'];
		?>
        <tr>
            <td><?php if(trim($transaction['remarks']) != ''){ echo $transaction['remarks'].'<br />'; } ?>On Date <strong><?php echo date("jS M Y",strtotime($transaction['addedon'])); ?></strong></td>
            <td><span class="text-info"><?php echo number_format($transaction['amount'],2); ?> INR<br></span></td>
            <td>&nbsp;</td>
        </tr>
	<?php }else{
		$debit += $transaction['amount'];
		?>
        <tr>
            <td> 
                <?php if(trim($transaction['remarks']) != ''){ echo $transaction['remarks'].'<br />'; } ?>
                   On Date <strong><?php echo date("jS M Y",strtotime($transaction['paid_date'])); ?></strong> <?php if(trim($transaction['remarks']) == ''){ echo 'by'; } ?> <strong><?php echo ucfirst($transaction['payment_type']); //($transaction['payment_type'] == "Cash")?'Payment':ucfirst($transaction['payment_type']); ?></strong><br>
                    <?php
                    if($transaction['payment_type'] == "Cheque" || $transaction['payment_type'] == "DD" || $transaction['payment_type'] == "NEFT" || $transaction['payment_type'] == "RTGS"){
                        echo "Bank Name: <strong>".$transaction['bank_name']."</strong><br>";
                        echo $transaction['payment_type']." Number: <strong>".$transaction['cheque_dd_number']."</strong>";
                    }
                    ?>
                <?php if(trim($transaction['add_transaction_remarks']) != ''){ echo '<br />'.$transaction['add_transaction_remarks']; } ?>
            </td>
            <td>&nbsp;</td>
            <td>
                <span class="text-success"><?php echo number_format($transaction['amount'],2); ?> INR </span>
            </td>
        </tr>
	<?php } ?>
    </tr>
    <?php
    $counter++;
}
?>
<tr>
	<td width="10%" align="right" style="padding-right:15px;"><strong>Total</strong></td>
	<td nowrap="nowrap"><strong class="text-primary"> <?php echo number_format($credit,2); ?> INR Cr.<br></strong></td>
    <td nowrap="nowrap"><strong class="text-success"> <?php echo number_format($debit,2); ?> INR Dr.<br></strong></td>
</tr>

<tr>
	<td width="10%" align="right" style="padding-right:15px;"><strong>Pending</strong></td>
	<td colspan="2" align="center"><strong class="text-danger"> <?php echo number_format($credit-$debit,2); ?> INR<br></strong></td>
</tr>
</tbody>