<?php
ob_start();
session_start();

$return_str = '';
if(!isset($_POST['hist']) || !is_numeric($_POST['hist']) || !($_POST['hist'] > 0)){
	exit();
}
require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$block_hist_id = $_POST['hist'];

$block_number = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_customer_blocks_hist where status = '1' and action_type = 'Cancel Booking' and id = '$block_hist_id' limit 0,1 "));
if(!isset($block_number['id'])){
    exit();
}



// echo "select id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, remarks, addedon from kc_customer_transactions_hist where customer_id = '".$block_number['customer_id']."' and block_id = '".$block_number['block_id']."' and block_number_id = '".$block_number['block_number_id']."' and status = '1' and action_type = 'Cancel Booking' and addedon >= '".$block_number['addedon']."' and deleted <= '".$block_number['deleted']."' ";die;

$transactions = mysqli_query($conn,"select id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, remarks, addedon from kc_customer_transactions_hist where customer_id = '".$block_number['customer_id']."' and block_id = '".$block_number['block_id']."' and block_number_id = '".$block_number['block_number_id']."' and status = '1' and action_type = 'Cancel Booking' and batch = '".$block_number['batch']."' ");  // and addedon >= '".$block_number['addedon']."' and deleted <= '".$block_number['deleted']."'
// print_r($transactions);die;
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
while($transaction = mysqli_fetch_assoc($transactions)){
//    echo"<pre>"; print_r($transaction);die;
	?>
    
    <?php
	if($transaction['cr_dr'] == "cr"){
		$credit += $transaction['amount'];
		?>
        <tr>
            <td><?php if(trim($transaction['remarks']) != ''){ echo $transaction['remarks'].'<br />'; } ?>On Date <strong><?php echo date("jS M Y",strtotime($transaction['addedon'])); ?></strong></td>
            <td><span class="text-info"><?php echo number_format($transaction['amount'],2);?> INR<br></span></td>
            <td>&nbsp;</td>
        </tr>
	<?php }else{
		$debit += $transaction['amount'];
		?>
        <tr>
            <td>
                <?php if(trim($transaction['remarks']) != ''){ echo $transaction['remarks'].'<br />'; } ?>
                On Date <strong><?php echo date("jS M Y",strtotime($transaction['paid_date'])); ?></strong><?php if(trim($transaction['remarks']) == ''){ echo 'by'; } ?> <strong><?php echo ($transaction['payment_type'] == "Cash")?'Payment':ucfirst($transaction['payment_type']); ?></strong><br>
                <?php
                if($transaction['payment_type'] == "Cheque" || $transaction['payment_type'] == "DD" || $transaction['payment_type'] == "NEFT" || $transaction['payment_type'] == "RTGS"){
                    echo "Bank Name: <strong>".$transaction['bank_name']."</strong><br>";
                    echo $transaction['payment_type']." Number: <strong>".$transaction['cheque_dd_number']."</strong>";
                }
                ?>
            </td>
            <td>&nbsp;</td>
            <td>
                <span class="text-success"><?php echo number_format($transaction['amount'],2); ?> INR </span>
            </td>
        </tr>
	<?php } ?>
    </tr>
    <?php
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