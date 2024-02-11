<?php
ob_start();
session_start();

if(!isset($_POST['contact']) || !is_numeric($_POST['contact']) || !((int) $_POST['contact'] > 0)){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$contact_id = (int) $_POST['contact'];

$contact_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_contacts where id = '".$contact_id."' limit 0,1 "));
?>
<div class="form-group">
  <label for="name" class="col-sm-3 control-label">Name</label>
  <div class="col-sm-8">
	<input type="hidden" value="<?php echo $contact_id; ?>" name="contact">
    <input type="text" class="form-control" id="name_edit" name="name_edit" maxlength="255" value="<?php echo $contact_details['name']; ?>" required>
  </div>
</div>

<div class="form-group">
  <label for="mobile" class="col-sm-3 control-label">Mobile</label>
  <div class="col-sm-8">
	<input type="text" class="form-control" id="mobile_edit" name="mobile_edit" maxlength="255" value="<?php echo $contact_details['mobile']; ?>" required>
  </div>
</div>