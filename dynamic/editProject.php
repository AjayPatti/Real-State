<?php
ob_start();
session_start();

if(!isset($_POST['project']) || !is_numeric($_POST['project']) || !($_POST['project'] > 0)){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$project_id = $_POST['project'];

$project_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_projects where id = '".$project_id."' limit 0,1 "));
?>
<div class="form-group">
  <label for="excel_file" class="col-sm-3 control-label">Project Name</label>
  <div class="col-sm-8">
  	<input type="hidden" value="<?php echo $project_id; ?>" name="project">
    <input type="text" class="form-control" id="project_name_edit" name="project_name_edit" maxlength="255" value="<?php echo $project_details['name']; ?>" required>
  </div>
</div>
<div class="form-group">
  <label for="send_message" class="col-sm-3 control-label">Send Reminder</label>
  <div class="col-sm-8">
  	<input type="checkbox" name="send_reminder" id="send_reminder" class="form-control" <?php if(isset($project_details['is_reminder']) && $project_details['is_reminder']==1){ echo 'checked="checked"'; } ?> />
  </div>
</div>