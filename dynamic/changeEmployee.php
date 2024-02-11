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

$customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select customer_id, block_id, block_number_id, sales_person_id from kc_customer_blocks where customer_id = '".$customer_id."' and block_id = '$block_id' and block_number_id = '$block_number_id' limit 0,1 "));
if(!isset($customer_details['customer_id'])){
  die;
}
?>
<div class="form-group">
  <label for="sales_person" class="col-sm-3 control-label">Sales Person <span class="text-danger">*</span></label>
  <div class="col-sm-8">
    <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>" />
    <input type="hidden" name="block_id" value="<?php echo $block_id; ?>" />
    <input type="hidden" name="block_number_id" value="<?php echo $block_number_id; ?>" />
    <select class="form-control" id="sales_person" name="sales_person" data-validation="required">
      <option value="">Select Employee</option>
      <?php
      $employees = mysqli_query($conn,"select * from kc_employees where status = '1' ");
      while($employee = mysqli_fetch_assoc($employees)){ ?>
          <option value="<?php echo $employee['id']; ?>" <?php if($employee['id'] == $customer_details['sales_person_id']){ echo "selected";  } ?>><?php echo $employee['name']; ?></option>
        <?php } ?>
    </select>
  </div>
</div>