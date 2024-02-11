<?php
ob_start();
session_start();

if(!isset($_POST['block']) || !is_numeric($_POST['block']) || !($_POST['block'] > 0)){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$block_id = $_POST['block'];

$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_blocks where id = '".$block_id."' limit 0,1 "));
?>
<div class="form-group">
    <label for="excel_file" class="col-sm-3 control-label">Block Name</label>
    <div class="col-sm-8">
    	<input type="hidden" value="<?php echo $block_id; ?>" name="block">
    	<input type="text" class="form-control" id="block_name_edit" name="block_name_edit" maxlength="255" value="<?php echo $block_details['name']; ?>" required>
    </div>
</div>