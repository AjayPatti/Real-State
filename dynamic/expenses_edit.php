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
$expenses_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_expenses where id='$expenses_id' limit 0,1 "));

?>

<div class="form-group">
						  <label for="project" class="col-sm-3 control-label">Expenses Type <span class="text-danger">*</span></label>
						  <div class="col-sm-8">
							<select class="form-control" id="project" name="expenses_id"  data-validation="required">
                            	<option value="">Select Project</option>
                                <?php
								$expenses_types = mysqli_query($conn,"select * from kc_expense_types where status = '1' AND deleted_at is null and deleted_by is null ");
								while($expenses_type = mysqli_fetch_assoc($expenses_types)){ ?>
                                	<option value="<?php echo $expenses_type['id']?>" <?php if($expenses_type['id']==$expenses_details['expense_type_id']){echo "selected=selected";}else{echo "";}?>><?php echo $expenses_type['expense_name']; ?></option>
                                <?php } ?>
                            </select>
						  </div>
						</div>
                        <input type="hidden" name="exp_id" value="<?php echo $expenses_details['id']; ?>">
                        <div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">Amount</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="employee_mobile" name="amount" value="<?php echo $expenses_details['amount']; ?>" maxlength="255" required>
						  </div>
						</div>
                        <div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">Original Amount</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="employee_mobile" name="original_amount" maxlength="255" value="<?php echo $expenses_details['original_amount']; ?>"  required>
						  </div>
						</div>
                        <div class="form-group">
						  <label for="excel_file" class="col-sm-3 control-label">Remark</label>
						  <div class="col-sm-8">
							<textarea rows="5" class="form-control"cols="5" name="remark" maxlength="255" required ><?php echo $expenses_details['remarks']; ?></textarea>
						  </div>
						</div>