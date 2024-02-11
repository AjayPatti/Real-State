<?php
ob_start();
session_start();

if(!isset($_POST['customer']) || !is_numeric($_POST['customer']) || !($_POST['customer'] > 0)){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$customer_id = isset($_POST['customer'])?(int) $_POST['customer']:0;
$block_id = isset($_POST['block'])?(int) $_POST['block']:0;
$block_number_id = isset($_POST['block_number'])?(int) $_POST['block_number']:0;

// $customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select cb.id as customer_block_id, cb.customer_id, cb.block_id, cb.block_number_id, cb.rate_per_sqft, cb.sales_person_id, bn.area, bn.block_number from kc_customer_blocks cb INNER JOIN kc_block_numbers bn ON bn.id = cb.block_number_id where cb.customer_id = '".$customer_id."' and cb.block_id = '$block_id' and cb.block_number_id = '$block_number_id' limit 0,1 "));
// if(!isset($customer_details['customer_id'])){
//   die;
// }
// $block_details = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_blocks where id = '".$customer_details['block_id']."' limit 0,1 "));
// if(!isset($block_details['name'])){
//   die;
// }
?>

<input type="hidden" name="customer_id" class="form-control" value="<?php echo $customer_id; ?>">
<input type="hidden" name="block_id" class="form-control" value="<?php echo $block_id; ?>">
<input type="hidden" name="block_number_id" class="form-control" value="<?php echo $block_number_id; ?>">
<input type="hidden" name="cr_dr" class="form-control" value="cr">

<div class="form-group">
    <label for="amount" class="col-sm-3 control-label">Amount<sup class="text-danger">*</sup></label>
    <div class="col-sm-8">
    <input type="number" name="amount" class="form-control" required="required" step="0.00">
    </div>
</div>
<div class="form-group">
    <label for="remarks" class="col-sm-3 control-label">Remarks<sup class="text-danger">*</sup></label>
    <div class="col-sm-8">
    <textarea class="form-control" id="remarks" name="remarks" required="required"></textarea>
    </div>

</div><!-- /.box -->
 <div class="form-group">
						  <label for="send_message" class="col-sm-3 control-label">Send Bounce Message</label>
						  <div class="col-sm-8">
						  	<input type="checkbox" name="send_message" id="send_message"  >
						  </div>
						</div>