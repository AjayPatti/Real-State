<?php
ob_start();
session_start();

if(!isset($_POST['cheque']) || !is_numeric($_POST['cheque']) || !($_POST['cheque'] > 0)){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$cheque_id = $_POST['cheque'];

$cheque_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_cheques where id = '".$cheque_id."' limit 0,1 "));
?>
<div class="form-group">
  <label for="excel_file" class="col-sm-3 control-label">Cheque Name</label>
  <div class="col-sm-8">
  	<input type="hidden" value="<?php echo $cheque_id; ?>" name="cheque">
    <input type="text" class="form-control" id="name" name="name" maxlength="255" value="<?php echo $cheque_details['name']; ?>" required>
  </div>
</div>
<div class="form-group">
  <label for="excel_file" class="col-sm-3 control-label">Cheque Amount</label>
  <div class="col-sm-8">
	<input type="text" class="form-control" id="amount" name="amount" maxlength="10" value="<?php echo $cheque_details['amount']; ?>" required>
  </div>
</div>

<div class="form-group">
  <label for="excel_file" class="col-sm-3 control-label">Cheque Date</label>
  <div class="col-sm-8">
	<input type="text" class="form-control" id="date" name="date" value="<?php echo date('d-m-Y',strtotime($cheque_details['date'])); ?>" required>
  </div>
</div>