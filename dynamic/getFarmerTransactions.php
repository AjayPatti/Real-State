<?php
ob_start();
session_start();

$return_str = '';

if(!isset($_POST['farmer']) || !is_numeric($_POST['farmer']) || !($_POST['farmer'] > 0)){
	exit();
}
 
require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$farmer_id = $_POST['farmer'];

// echo "select * from kc_farmer_transactions where farmer_id = '".$farmer_id."' and status = '1'";die;


$transactions = mysqli_query($conn,"select * from kc_farmer_transactions where farmer_id = '".$farmer_id."' and status = '1'");
?>
<div class="col-sm-12">
    <a href="farmer_ledger_excel_export.php?farmer=<?php echo isset($_POST['farmer'])?$_POST['farmer']:''; ?>&search=Search" class="btn btn-sm btn-success"><i class="fa fa-file-excel-o"></i> Excel Export</a>
</div>
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
    // echo "<pre>";print_r($transaction); die();
    if($counter == 1){
        $transaction['amount'] = saleAmountFarmer($conn,$farmer_id);
    }
	?>

    <?php
	if($transaction['cr_dr'] == "cr"){
		$credit += $transaction['amount'];
		?>
        <tr>
            <td><?php if(trim($transaction['remarks']) != ''){ echo $transaction['remarks'].'<br />'; } ?>On Date <strong><?php echo date("jS M Y",strtotime($transaction['created'])); ?></strong></td>
            <td><span class="text-info"><?php echo number_format($transaction['amount'],2); ?> INR<br></span></td>
            <td>&nbsp;</td>
            <td><?php if(trim($transaction['remarks']) != ''){ ?><a onclick="cancelTransaction('<?php echo $transaction['id']; ?>','<?php echo $farmer_id; ?>');" href="javascript:void(0);" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Cancel Transaction"><i class="fa fa-remove"></i></a><?php } ?></td>
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
                //  $transaction_status =  $transaction['clear_remarks'];
                // if($transaction['payment_type'] == "Cheque"){
                //     $data = isset($transaction_status)?'<strong class="text-success">Cleared</strong>':'<strong class="text-danger">Clearance Pending</strong>';
                // }else{
                //
                // }
                $data = '';

                if(trim($transaction['remarks']) != ''){ echo $transaction['remarks'].'<br />'; } ?>
                   On Date <strong><?php echo date("jS M Y",strtotime($transaction['paid_date'])); ?></strong> <?php if(trim($transaction['remarks']) == ''){ echo 'by'; } ?> <strong><?php echo ucfirst($transaction['payment_type']); //($transaction['payment_type'] == "Cash")?'Payment':ucfirst($transaction['payment_type']); ?></strong><br>
                    <?php

                    if($transaction['payment_type'] == "Cheque" || $transaction['payment_type'] == "DD" || $transaction['payment_type'] == "NEFT" || $transaction['payment_type'] == "RTGS"){
                        echo "Bank Name: <strong>".$transaction['bank_name']."</strong><br>";
                        echo $transaction['payment_type']." Number: <strong>".$transaction['cheque_dd_number']."</strong>".' ('.$data.')';
                    }
                    ?>
                <?php if(trim($transaction['remarks']) != ''){ echo '<br />'.$transaction['remarks']; } ?>
            </td>
            <td>&nbsp;</td>
            <td>
                <span class="text-success"><?php echo number_format($transaction['amount'],2); ?> INR </span>
            </td>
            <td nowrap="nowrap">
                <?php
        
                if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_purchase')){//view_transactions_print_farmer_view_transactions
                if(trim($transaction['remarks']) == '' && $transaction['cr_dr'] == "dr"){ ?>
            	<?php /*<a target="_blank" href="receipt.php?receipt=<?php echo $transaction['id']; ?>" class="btn btn-xs btn-success" data-toggle="tooltip" title="Print Receipt"><i class="fa fa-print"></i></a>*/ ?>
               <a target="_blank" href="receipt2.php?receipt=<?php echo $transaction['id']; ?>" class="btn btn-xs btn-success" data-toggle="tooltip" title="Print Receipt "><i class="fa fa-print"></i></a>
               
            <?php

           } } if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'manage_purchase')){
                //  view_transactions_cancel_farmer_view_transactions        
            ?>
                <a onclick="cancelTransaction('<?php echo $transaction['id']; ?>','<?php echo $farmer_id; ?>');" href="javascript:void(0)" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Cancel Transaction"><i class="fa fa-remove"></i></a>
            <?php  }  ?>

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
