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

$customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select cb.id as customer_block_id, cb.customer_id, cb.block_id, cb.block_number_id, cb.rate_per_sqft, cb.sales_person_id, bn.area, bn.block_number from kc_customer_blocks cb INNER JOIN kc_block_numbers bn ON bn.id = cb.block_number_id where cb.customer_id = '".$customer_id."' and cb.block_id = '$block_id' and cb.block_number_id = '$block_number_id' limit 0,1 "));
if(!isset($customer_details['customer_id'])){
  die;
}
$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_blocks where id = '".$customer_details['block_id']."' limit 0,1 "));
if(!isset($block_details['name'])){
  die;
}
?>
<div class="form-group">
  <label for="rr_block">Block</label>
  <input type="hidden" name="rr_customer_id" value="<?php echo $customer_id; ?>" />
  <select class="form-control" id="rr_block" name="rr_block">
    <option value="<?php echo $block_id; ?>"><?php echo $block_details['name']; ?></option>
  </select>
</div>
<div class="form-group">
  <label for="rr_block_no">Block Number</label>
  <select class="form-control" id="rr_block_no" name="rr_block_no">
    <option value="<?php echo $customer_details['block_number_id']; ?>"><?php echo $customer_details['block_number']; ?></option>
  </select>
</div>

<div class="form-group">
  <label for="rr_plc">PLC</label>
  <select class="form-control select2" name="plc[]" id="rr_plc" multiple  style="width: 100%;" disabled="disabled">
    <?php
    $old_plc = mysqli_query($conn,"select plc_id from kc_customer_block_plc where customer_block_id = '".$customer_details['customer_block_id']."' and status = '1' ");
    while($o_plc = mysqli_fetch_assoc($old_plc)){
      $plc_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_plc where status = '1' and id='".$o_plc['plc_id']."' limit 0,1 ")); ?>
      <option value="<?php echo $plc_details['id']; ?>" selected="selected" data-percentage="<?php echo $plc_details['plc_percentage']; ?>"><?php echo $plc_details['name']; ?>(<?php echo $plc_details['plc_percentage']; ?> %)</option>
      <?php } ?>
  </select>
</div>
<div class="form-group">
  <label for="rr_area">Total Area</label>
  <input type="text" class="form-control" id="rr_area" name="rr_area" readonly="readonly" value="<?php echo $customer_details['area']; ?>">
</div>
<hr>
<div class="form-group">
  <label for="affect_sold">Will Affect Sold Amount</label>
  <input type="checkbox" id="affect_sold" name="affect_sold">
</div>
<hr>
<div class="form-group">
  <label for="rr_revised_rate">Revised Rate per sq. ft. <span class="text-danger" style="font-size: 11px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;If before Rate 1500 Rs and Revised Rate now is 1600 Rs Then Enter 100 Rs(1600-1500)</span></label>
  <input type="text" class="form-control" id="rr_revised_rate" name="rr_revised_rate" data-validation="required number" data-validation-allowing="range[1;9999]" onkeyup="rr_calculateAmount();">
</div>
<div class="form-group">
  <label for="rr_payable_amount">Total Revised Plot Value(INR)</label>
  <input type="text" class="form-control" id="rr_payable_amount" name="rr_payable_amount" data-validation="required number" data-validation-allowing="range[1;999999]" readonly="readonly">
</div>