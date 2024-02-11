<?php
ob_start();
session_start();

$return_str = '';

if(!isset($_POST['id']) || !is_numeric($_POST['id']) || !($_POST['id'] > 0)){
    exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$id = $_POST['id'];
$transactions = mysqli_query($conn,"select * from kc_avr_receipt where id = '".$id."' and status = '1'");
?>
<tbody>
    <tr>
        <th width="76%">Details</th>
        <!-- <td>Credit</td> -->
        <td>Debit</td>
        <td>Action</td>
        
    </tr>
<?php
$counter = 1;
while($transaction = mysqli_fetch_assoc($transactions)){
    // if($counter == 1){
    //     $transaction['amount'] = saleAmount($conn,$customer_id,$block_id,$block_number_id);
    // }
    ?>
   
    <tbody>
        <tr>
            <td> 
                <?php if(trim($transaction['remarks']) != ''){ echo $transaction['remarks'].'<br />'; } ?>
                   On Date <strong><?php echo date("jS M Y",strtotime($transaction['paid_date'])); ?></strong> <?php if(trim($transaction['remarks']) == ''){ echo 'by'; } ?><br> <strong><?php echo ucfirst($transaction['payment_type']); //($transaction['payment_type'] == "Cash")?'Payment':ucfirst($transaction['payment_type']); ?></strong><br>
                   <!--  -->
                
            </td>
            
            <td>
                <span class="text-success"><?php echo number_format($transaction['paid_amount']); ?> INR </span>
            </td>
            <td nowrap="nowrap">
                <?php 
                if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'print_avr_receipt')){
                if(trim($transaction['remarks']) != ''){ ?>
                    <a target="_blank" href="receipt_abr_receipt.php?receipt=<?php echo $transaction['id']; ?>" class="btn btn-xs btn-success" data-toggle="tooltip" title="Print Receipt"><i class="fa fa-print"></i></a><?php } }
                    if(userCan($conn,$_SESSION['login_id'],$privilegeName = 'cancel_receipt')){
                     ?>
                        <a onclick="cancelTransaction('<?php echo $transaction['id']; ?>');" href="javascript:void(0)" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Cancel Transaction"><i class="fa fa-remove"></i></a>
                <?php } ?>
            </td>
        </tr>
    </tbody>
   
    </tr>
    <?php
    $counter++;?>
<tr>
    <td width="10%" align="right" style="padding-right:15px;"><strong>Total</strong></td>
    
    <td nowrap="nowrap"><strong class="text-success"> <?php echo number_format($transaction['paid_amount']); ?> INR Dr.<br></strong></td>
</tr>


<?php }
?>
</tbody>