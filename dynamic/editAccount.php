<?php
ob_start();
session_start();

if(!isset($_POST['id']) || !is_numeric($_POST['id']) || !($_POST['id'] > 0)){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$account_id = $_POST['id'];

$account_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_accounts where id = '".$account_id."' limit 0,1 "));
?>
<input type="hidden" name="id" value=" <?php echo $_POST['id']; ?>">
<div class="form-group">
  <label for="name" class="col-sm-3 control-label">Name<sup class="text-danger text-lg">*</sup></label>
  <div class="col-sm-8">
	<input type="text" class="form-control" id="name" name="name" maxlength="255" value="<?php echo $account_details['name']; ?>" required>
  </div>
</div>

<div class="form-group">
  <label for="bank_name" class="col-sm-3 control-label">Bank Name</label>
  <div class="col-sm-8">
	<input type="text" class="form-control" id="bank_name" name="bank_name" maxlength="255" value="<?php echo $account_details['bank_name']; ?>">
  </div>
</div>

<div class="form-group">
  <label for="branch_name" class="col-sm-3 control-label">Branch Name</label>
  <div class="col-sm-8">
	<input type="text" class="form-control" id="branch_name" name="branch_name" maxlength="255" value="<?php echo $account_details['branch_name']; ?>">
  </div>
</div>

<div class="form-group">
  <label for="account_no" class="col-sm-3 control-label">Account No.<sup class="text-danger">*</sup></label>
  <div class="col-sm-8">
	<input type="text" class="form-control" id="account_no" name="account_no" maxlength="255" value="<?php echo $account_details['account_no']; ?>" required>
  </div>
</div>

<div class="form-group">
  <label for="ifsc_code" class="col-sm-3 control-label">IFSC Code</label>
  <div class="col-sm-8">
	<input type="text" class="form-control" id="ifsc_code" name="ifsc_code" maxlength="255" value="<?php echo $account_details['ifsc_code']; ?>">
  </div>
</div>