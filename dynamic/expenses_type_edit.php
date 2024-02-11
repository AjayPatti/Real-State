<?php
ob_start();
session_start();

if(!isset($_POST['expense_id']) || !is_numeric($_POST['expense_id']) || !($_POST['expense_id'] > 0)){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$expenses_id = isset($_POST['expense_id'])?(int) $_POST['expense_id']:0;
$expenses_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_expense_types where id='$expenses_id' limit 0,1 "));

?>
<div class="box-body">                   
    <div class="form-group">
        <label for="excel_file" class="col-sm-3 control-label"> Expenses Type</label>
        <div class="col-sm-8">
        <input type="text" class="form-control" id="employee_name" name="expenses_type" maxlength="255" value="<?php echo $expenses_details['expense_name'];?>" required>
        <input type="hidden" name="expense_id" value="<?php echo $expenses_details['id'];?>">
        </div>
    </div> 
</div>

