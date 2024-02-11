<?php
ob_start();
session_start();

$return_str = '';

if(!isset($_POST['customer']) || !is_numeric($_POST['customer']) || !($_POST['customer'] > 0) || !isset($_POST['block']) || !is_numeric($_POST['block']) || !($_POST['block'] > 0) || !isset($_POST['block_number']) || !is_numeric($_POST['block_number']) || !($_POST['block_number'] > 0)){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$customer_id = $_POST['customer'];
$block_id = $_POST['block'];
$block_number_id = $_POST['block_number'];


// $transactions = mysqli_query($conn,
$query ="select id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, remarks, add_transaction_remarks,paid_account_no, addedon, clear_remarks, clear_date from kc_customer_transactions where customer_id = '".$customer_id."' and block_id = '$block_id' and block_number_id = '$block_number_id' and status = '1' and is_affect_sold_amount != '1'  ";


// echo "<pre>";print_r($query);die;
$transactions = mysqli_query($conn,$query);
// echo "<pre>";print_r($transactions);die;

?>
<tbody>
	<tr>
    	<th width="76%">Details</th>
        <td>Credit</td>
        <td>Debit</td>
        <td>Action</td>
    </tr>
<?php
$credit = 0;
$debit = 0;
$counter = 1;
while($transaction = mysqli_fetch_assoc($transactions)){

    // echo "<pre>";print_r($transaction);die;
								 

    if($counter == 1){
        $transaction['amount'] = saleAmount($conn,$customer_id,$block_id,$block_number_id);
    
        
    }
	?>
    
    <?php
	if($transaction['cr_dr'] == "cr"){
		$credit += $transaction['amount'];
    
		?>
        <tr>
            <td><?php if(trim($transaction['remarks']) != ''){ echo $transaction['remarks'].'<br />'; } ?>On Date <strong><?php echo date("jS M Y",strtotime($transaction['addedon'])); ?></strong></td>
            <td><span class="text-info"><?php echo number_format($transaction['amount'],2); ?> INR<br></span></td>
            <td>&nbsp;</td>
            <td><?php if(trim($transaction['remarks']) != ''){ ?><a onclick="cancelTransaction('<?php echo $transaction['id']; ?>','<?php echo $customer_id; ?>','<?php echo $block_id; ?>','<?php echo $block_number_id; ?>');" href="javascript:void(0);" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Cancel Transaction"><i class="fa fa-remove"></i></a><?php } ?></td>
        </tr>
	<?php }else{
		$debit += $transaction['amount'];
		?>
        <tr>
            <td> 
                <?php 
                // if(!$transaction['clear_date']){
                //     $transaction_status = 'Cleared';
                //     $text = 'text-success';
                // }else{
                //     $transaction_status = 'Clearance Pending';
                //     $text = 'text-danger';
                // }
                // echo "<pre>";print_r($transaction);
                 $transaction_status =  $transaction['clear_remarks'];
                   $data = isset($transaction_status)?'<strong class="text-success">Cleared</strong>':'<strong class="text-danger">Clearance Pending</strong>';
                

                if(trim($transaction['remarks']) != ''){ echo $transaction['remarks'].'<br />'; } ?>
                   On Date <strong><?php echo date("jS M Y",strtotime($transaction['paid_date'])); ?></strong> <?php if(trim($transaction['remarks']) == ''){ echo 'by'; } ?> <strong><?php echo ucfirst($transaction['payment_type']); //($transaction['payment_type'] == "Cash")?'Payment':ucfirst($transaction['payment_type']); ?></strong><br>
                    <?php
                  
                    if($transaction['payment_type'] == "Cheque" || $transaction['payment_type'] == "DD" || $transaction['payment_type'] == "NEFT" || $transaction['payment_type'] == "RTGS"){
                        echo "Bank Name: <strong>".$transaction['bank_name']."</strong><br>";
                        echo $transaction['payment_type']." Number: <strong>".$transaction['cheque_dd_number']."</strong>".' ('.$data.')';
                    }
                    ?>
                     <span class="text-info"><?php if($transaction['payment_type'] == "Cheque" ){ echo  companyAccountName($conn,$transaction['paid_account_no']); } ?> </span>

                     

                <?php if(trim($transaction['add_transaction_remarks']) != ''){ echo '<br />'.$transaction['add_transaction_remarks'];} ?>
            </td>
            <td>&nbsp;</td>
            <td>
                <span class="text-success"><?php echo number_format($transaction['amount'],2); ?> INR </span>
            </td>
            <td nowrap="nowrap">
                <?php 
                if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'view_transactions_print_customer_view_transactions')){ 
                if(trim($transaction['remarks']) == '' && $transaction['cr_dr'] == "dr"){ ?>
            	<?php /*<a target="_blank" href="receipt.php?receipt=<?php echo $transaction['id']; ?>" class="btn btn-xs btn-success" data-toggle="tooltip" title="Print Receipt"><i class="fa fa-print"></i></a>*/ ?>
               <a target="_blank" href="receipt2.php?receipt=<?php echo $transaction['id']; ?>" class="btn btn-xs btn-success" data-toggle="tooltip" title="Print Receipt "><i class="fa fa-print"></i></a>
            <?php 
            } } if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'view_transactions_cancel_customer_view_transactions')){ 
            ?>
                <a onclick="cancelTransaction('<?php echo $transaction['id']; ?>','<?php echo $customer_id; ?>','<?php echo $block_id; ?>','<?php echo $block_number_id; ?>');" href="javascript:void(0)" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Cancel Transaction"><i class="fa fa-remove"></i></a>
            <?php }  ?>

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
	<td nowrap="nowrap"><strong class="text-primary"> <?php echo number_format($credit,2);?> INR Cr.<br></strong></td>
    <td nowrap="nowrap"><strong class="text-success"> <?php echo number_format($debit,2); ?> INR Dr.<br></strong></td>
</tr>

<tr>
	<td width="10%" align="right" style="padding-right:15px;"><strong>Pending</strong></td>
	<td colspan="2" align="center"><strong class="text-danger"> <?php echo number_format($credit-$debit,2); ?> INR<br></strong></td>
</tr>
</tbody>