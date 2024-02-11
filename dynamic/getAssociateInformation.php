<?php
ob_start();
session_start();

if(!isset($_POST['associateID']) || !is_numeric($_POST['associateID']) || !($_POST['associateID'] > 0)){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$associate_id = $_POST['associateID'];

$associate_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, code, name, mobile_no from kc_associates where id = '".$associate_id."' limit 0,1 "));
?>
<div class="form-group">
  <label for="code" class="col-sm-3 control-label">Associate Code</label>
  <div class="col-sm-8">
  <input type="text" class="form-control" id="code" name="code" maxlength="255" required value="<?php echo isset($associate_details['code'])?$associate_details['code']:''; ?>" />
  <input type="hidden" name="associate_id" value="<?php echo isset($associate_details['id'])?$associate_details['id']:''; ?>" />
  </div>
</div>

<div class="form-group">
  <label for="excel_file" class="col-sm-3 control-label">Associate Name</label>
  <div class="col-sm-8">
  <input type="text" class="form-control" id="name" name="name" maxlength="255" required value="<?php echo isset($associate_details['name'])?$associate_details['name']:''; ?>" />
  </div>
</div>
            
<div class="form-group">
  <label for="excel_file" class="col-sm-3 control-label">Associate Mobile</label>
  <div class="col-sm-8">
  <input type="text" class="form-control" id="mobile" name="mobile" maxlength="255" required value="<?php echo isset($associate_details['mobile_no'])?$associate_details['mobile_no']:''; ?>" />
  </div>
</div>