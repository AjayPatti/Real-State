<?php
    ob_start();
    session_start();

    if(!isset($_POST['customer_blocks_hist_id']) || !is_numeric($_POST['customer_blocks_hist_id']) || !($_POST['customer_blocks_hist_id'] > 0)){
    	exit();
    }

    require("../includes/host.php");
    require("../includes/kc_connection.php");
    require("../includes/common-functions.php");
    $customer_blocks_hist_id = $_POST['customer_blocks_hist_id'];

    $transactions = mysqli_query($conn,"select * from kc_refund_amount where customer_blocks_hist_id = '".$customer_blocks_hist_id."' and status = '1' and deleted is null ");
?>
<tbody>
	<tr>
    	<th width="76%">Details</th>
        <td>Refund Amount</td>
        <td>Action</td>
    </tr>
<?php
    $refund_amount = 0;
    while($transaction = mysqli_fetch_assoc($transactions)){
        $refund_amount += $transaction['amount'];
?>
        <tr>
            <td><?php if(trim($transaction['remark']) != ''){ echo $transaction['remark'].'<br />'; } ?>On Date <strong><?php echo date("jS M Y",strtotime($transaction['addedon'])); ?></strong></td>
            <td><span class="text-info"><?php echo number_format($transaction['amount'],2); ?> INR<br></span></td>
            <td><span class="btn btn-xs btn-danger" onclick="return deleteRefundTransaction(<?php echo $transaction['id']; ?>, <?php echo $customer_blocks_hist_id; ?>)"><i class="fa fa-remove "></i></span></td>
            
            
            
        </tr>
    
            
        
    </tr>
<?php } ?>
<tr>
	<td width="10%" align="right" style="padding-right:15px;"><strong>Total</strong></td>
	<td nowrap="nowrap"><strong class="text-primary"> <?php echo number_format($refund_amount,2); ?> INR<br></strong></td>
</tr>
</tbody>