<?php
	ob_start();
	session_start();

	if(!isset($_POST['block_number']) || !is_numeric($_POST['block_number']) || !($_POST['block_number'] > 0)){
		exit();
	}

	require("../includes/host.php");
	require("../includes/kc_connection.php");
	require("../includes/common-functions.php");


	$block_id = $_POST['block_number'];


	$block_number = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_customer_blocks_hist where id = '".
	$block_id."' limit 0,1 "));

	$total_paid_details = mysqli_fetch_assoc(mysqli_query($conn,"select sum(amount) as total_paid from kc_customer_transactions_hist where customer_id = '".$block_number['customer_id']."' and block_id = '".$block_number['block_id']."' and block_number_id = '".$block_number['block_number_id']."' and cr_dr = 'dr' and status = '1' and remarks is NULL and action_type = 'Cancel Booking' and batch= '".$block_number['batch']."' ")); //addedon >= '".$block_number['addedon']."' and deleted <= '".$block_number['deleted']."'



	$total_paid = $total_paid_details['total_paid']?$total_paid_details['total_paid']:0;
	// echo print_r($total_paid);die;

	$total_refund = mysqli_fetch_assoc(mysqli_query($conn,"select sum(amount) as total_refunded from kc_refund_amount where customer_id = '".$block_number['customer_id']."' and block_id = '".$block_number['block_id']."' and block_number_id = '".$block_number['block_number_id']."' and customer_blocks_hist_id = '".$block_number['id']."' and deleted IS NULL "));
	// echo print_r($total_refund['total_refunded']);die;
	
	
	$total_refunded = $total_refund['total_refunded']?$total_refund['total_refunded']:0;

	$pending_amount = ($total_paid-$total_refunded);
?>

<div class="form-group">
  <label for="cancel_remarks" class="col-sm-3 control-label">Refund Amount</label>
  <div class="col-sm-8">
	<input type="text" class="form-control" name="refund_amount" id="refund_amount" value="<?php echo $pending_amount; ?>" data-validation="number" data-validation-allowing="range[1;<?php echo $pending_amount; ?>]" data-validation-error-msg="Maximum Allowed value is <?php echo $pending_amount; ?>">
  </div>
</div>
<div class="form-group">
  <label for="cancel_remarks" class="col-sm-3 control-label">Refund Date</label>
  <div class="col-sm-8">
  	<input type="date" class="form-control" id="refund_date" name="refund_date" />
  </div>
</div>
<div class="form-group">
  <label for="cancel_remarks" class="col-sm-3 control-label">Remarks</label>
  <div class="col-sm-8">
	<textarea class="form-control" id="refund_remarks" name="refund_remarks" data-validation="required"></textarea>
	<input type="hidden" name="refund_id" id="refund_id" value="<?php echo $block_number['id']; ?>">
	<input type="hidden" name="refund_customer_id" id="refund_customer_id" value="<?php echo $block_number['customer_id']; ?>">
    <input type="hidden" name="refund_block_id" id="refund_block_id" value="<?php echo $block_number['block_id']; ?>">
    <input type="hidden" name="refund_block_number_id" id="refund_block_number_id" value="<?php echo $block_number['block_number_id']; ?>">
  </div>
</div>