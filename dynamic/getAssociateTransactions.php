<?php
ob_start();
session_start();

$return_str = '';

if(!isset($_POST['associate']) || !is_numeric($_POST['associate']) || !($_POST['associate'] > 0)){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$credit = 0;
$transactions = mysqli_query($conn,"select id, customer_id, associate_id, block_id, block_number_id, transaction_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, remarks, addedon from kc_associates_transactions where associate_id = '".$_POST['associate']."' and status = '1' ");

//$credit = associateTotalCredited($conn,$_POST['associate']);
?>
<tbody>
	<tr>
    	<th width="40%">Details</th>
        <td>Paid Amount</td>
        <td>Credit</td>
        <td>Debit</td>
        <td>Action</td>
    </tr>
    <?php
        $debit = $credit = 0;
        while($transaction = mysqli_fetch_assoc($transactions)){
            if($transaction['cr_dr'] == "cr"){
                $transaction_details = mysqli_fetch_assoc(mysqli_query($conn,"select amount from kc_customer_transactions where id = '".$transaction['transaction_id']."' limit 0,1 "));

                $credit += $transaction['amount'];
                ?>
                <tr>
                    <td>
                        Commission of <?php echo blockName($conn,$transaction['block_id']).'-'.blockNumberName($conn,$transaction['block_number_id']); ?><br>
                        On Date <strong><?php echo date("jS M Y",strtotime($transaction['addedon'])); ?></strong>
                    </td>
                    <td><?php echo number_format($transaction_details['amount'],2); ?></td>
                    <td><span class="text-info"><?php echo number_format($transaction['amount'],2); ?> INR<br></span></td>
                    <td>&nbsp;</td>
                    <td>NA</td>
                </tr>
            <?php }else{
                $debit += $transaction['amount']; ?>
                <tr>
                    <td> 
                           On Date <strong><?php echo date("jS M Y",strtotime($transaction['paid_date'])); ?></strong> by <strong><?php echo ucfirst($transaction['payment_type']);//($transaction['payment_type'] == "Cash")?'Payment':ucfirst($transaction['payment_type']); ?></strong><br>
                            <?php
                            if($transaction['payment_type'] == "Cheque" || $transaction['payment_type'] == "DD" || $transaction['payment_type'] == "NEFT" || $transaction['payment_type'] == "RTGS"){
                                echo "Bank Name: <strong>".$transaction['bank_name']."</strong><br>";
                                echo $transaction['payment_type']." Number: <strong>".$transaction['cheque_dd_number']."</strong>";
                            }
                            ?>
                    </td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>
                        <span class="text-success"><?php echo number_format($transaction['amount'],2); ?> INR </span>
                    </td>
                    <td nowrap="nowrap">
                    	<a target="_blank" href="associate_receipt.php?receipt=<?php echo $transaction['id']; ?>" class="btn btn-xs btn-success" data-toggle="tooltip" title="Print Receipt"><i class="fa fa-print"></i></a>
                        <a onclick="cancelTransaction('<?php echo $transaction['id']; ?>');" href="javascript:void(0)" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Cancel Transaction"><i class="fa fa-remove"></i></a>
                    </td>
                </tr>
            <?php }
        } ?>
        <tr>
        	<td width="10%" align="right" style="padding-right:15px;" colspan="2"><strong>Total</strong></td>
        	<td nowrap="nowrap"><strong class="text-primary"> <?php echo number_format($credit,2); ?> INR Cr.<br></strong></td>
            <td nowrap="nowrap"><strong class="text-success"> <?php echo number_format($debit,2); ?> INR Dr.<br></strong></td>
        </tr>

        <tr>
        	<td width="10%" align="right" style="padding-right:15px;" colspan="2"><strong>Pending</strong></td>
        	<td colspan="2" align="center"><strong class="text-danger"> <?php echo number_format($credit-$debit,2); ?> INR<br></strong></td>
        </tr>
</tbody>