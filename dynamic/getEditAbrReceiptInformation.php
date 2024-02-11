<?php
ob_start();
session_start();

if (!isset($_POST['id']) || !is_numeric($_POST['id']) || !($_POST['id'] > 0)) {
  exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$id = $_POST['id'];

$customer_details = mysqli_fetch_assoc(mysqli_query($conn, "select * from kc_avr_receipt where id = '" . $id . "' limit 0,1 "));
// print_r($customer_details['paid_date']);die;
?>


<div class="form-group">
  <label for="name_title" class="col-sm-3 control-label">Name</label>
  <div class="col-sm-8">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <select class="form-control" name="name_title" id="name_title" style="width:15%;float:left;">
      <option value="Mr." <?php echo (isset($customer_details['name_title']) && $customer_details['name_title'] == "Mr.") ? 'selected' : ''; ?>>Mr.</option>
      <option value="Mrs." <?php echo (isset($customer_details['name_title']) && $customer_details['name_title'] == "Mrs.") ? 'selected' : ''; ?>>Mrs.</option>
      <option value="Ms." <?php echo (isset($customer_details['name_title']) && $customer_details['name_title'] == "Ms.") ? 'selected' : ''; ?>>Ms.</option>
      <option value="Dr." <?php echo (isset($customer_details['name_title']) && $customer_details['name_title'] == "Dr.") ? 'selected' : ''; ?>>Dr.</option>
      <option value="M/s." <?php echo (isset($customer_details['name_title']) && $customer_details['name_title'] == "M/s.") ? 'selected' : ''; ?>>M/s.</option>
    </select>
    <input type="text" class="form-control col-sm-8" id="name" name="name" style="width:85%;"
      value="<?php echo isset($customer_details['name']) ? $customer_details['name'] : ''; ?>" required>
  </div>
</div>

<div class="form-group">
  <label for="parent_name" class="col-sm-3 control-label"><input type="radio" value="S" name="parent_name_title" <?php echo (isset($customer_details['parent_name_relation']) && $customer_details['parent_name_relation'] == "S") ? 'checked' : ''; ?>>S/<input type="radio" value="W"
      name="parent_name_title" <?php echo (isset($customer_details['parent_name_relation']) && $customer_details['parent_name_relation'] == "W") ? 'checked' : ''; ?>>W/<input type="radio" value="D"
      name="parent_name_title" <?php echo (isset($customer_details['parent_name_relation']) && $customer_details['parent_name_relation'] == "D") ? 'checked' : ''; ?>>D/<input type="radio" value="C"
      name="parent_name_title" <?php echo (isset($customer_details['parent_name_relation']) && $customer_details['parent_name_relation'] == "C") ? 'checked' : ''; ?>>C of</label>
  <div class="col-sm-8">
    <select class="form-control" name="parent_sub_title" id="parent_sub_title" style="width:15%;float:left;"
      data-validation="required">
      <option value="Mr." <?php echo (isset($customer_details['parent_sub_title']) && $customer_details['parent_sub_title'] == "Mr.") ? 'selected' : ''; ?>>Mr.</option>
      <option value="Mrs." <?php echo (isset($customer_details['parent_sub_title']) && $customer_details['parent_sub_title'] == "Mrs.") ? 'selected' : ''; ?>>Mrs.</option>
      <option value="Ms." <?php echo (isset($customer_details['parent_sub_title']) && $customer_details['parent_sub_title'] == "Ms.") ? 'selected' : ''; ?>>Ms.</option>
      <option value="Dr." <?php echo (isset($customer_details['parent_sub_title']) && $customer_details['parent_sub_title'] == "Dr.") ? 'selected' : ''; ?>>Dr.</option>
      <option value="M/s." <?php echo (isset($customer_details['parent_sub_title']) && $customer_details['parent_sub_title'] == "M/s.") ? 'selected' : ''; ?>>M/s.</option>
    </select>
    <input type="text" class="form-control col-sm-8" id="parent_name" name="parent_name"
      value="<?php echo isset($customer_details['parent_name']) ? $customer_details['parent_name'] : ''; ?>"
      style="width:85%;" />
  </div>
</div>

<div class="form-group">
  <label for="nationality" class="col-sm-3 control-label">Nationality</label>
  <div class="col-sm-8">
    <input type="text" class="form-control" id="nationality" name="nationality"
      value="<?php echo isset($customer_details['nationality']) ? $customer_details['nationality'] : ''; ?>">
  </div>
</div>



<div class="form-group">
  <label for="dob" class="col-sm-3 control-label">DOB</label>
  <div class="col-sm-8">
    <input type="text" class="form-control" id="dob" name="dob" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" data-validation="date" data-validation-format="dd-mm-yyyy"
      value="<?php echo isset($customer_details['dob']) ? date("d-m-Y", strtotime($customer_details['dob'])) : ''; ?>"
      required>
  </div>
</div>

<div class="form-group">
  <label for="excel_file" class="col-sm-3 control-label">Address</label>
  <div class="col-sm-8">
    <input type="text" class="form-control" id="address" name="address"
      value="<?php echo isset($customer_details['address']) ? $customer_details['address'] : ''; ?>">
  </div>
</div>

<div class="form-group">
      <label for="excel_file" class="col-sm-3 control-label">Mobile</label>
      <div class="col-sm-8">
        <input type="text" class="form-control" id="mobile" name="mobile"
          value="<?php echo isset($customer_details['mobile']) ? $customer_details['mobile'] : ''; ?>" required>
      </div>
  </div>


<div class="form-group">
    <label for="project" class="col-sm-3 control-label">Project/Block/Plot Number/Total Area</label>
    <div class="col-sm-8">
      <input type="text" name="project_block_plotnumber_totalarea"   value="<?php echo isset($customer_details['project_block_plotnumber_totalarea']) ? $customer_details['project_block_plotnumber_totalarea'] : ''; ?>" class="form-control" id="project">
    </div>
</div>

<div class="box-body">
  <div class="form-group">
    <label for="payment_type" class="col-sm-3 control-label">Payment Mode</label>
    <div class="col-sm-8">
      <select class="form-control" id="payment_type" name="payment_type" onChange="paymentTypeChanged(this);">
        <option value="">Select Payment Mode</option>
        <option value="Cash"<?php echo (isset($customer_details['payment_type']) && $customer_details['payment_type'] == "Cash") ? 'selected' : ''; ?>>Cash</option>
        <option value="DD"<?php echo (isset($customer_details['payment_type']) && $customer_details['payment_type'] == "DD") ? 'selected' : ''; ?>>DD</option>
        <option value="Cheque"<?php echo (isset($customer_details['payment_type']) && $customer_details['payment_type'] == "Cheque") ? 'selected' : ''; ?>>Cheque</option>
        <option value="NEFT"<?php echo (isset($customer_details['payment_type']) && $customer_details['payment_type'] == "NEFT") ? 'selected' : ''; ?>>NEFT</option>
        <option value="RTGS"<?php echo (isset($customer_details['payment_type']) && $customer_details['payment_type'] == "RTGS") ? 'selected' : ''; ?>>RTGS</option>
      </select>
    </div>
  </div>

  <div class="form-group cheque_dd">
    <label for="excel_file" class="col-sm-3 control-label">Bank Name</label>
    <div class="col-sm-8">
      <input type="text" class="form-control" id="bank_name" name="bank_name"   value="<?php echo isset($customer_details['bank_name']) ? $customer_details['bank_name'] : ''; ?>">
    </div>
  </div>
  <div class="form-group cheque_dd" >
    <label for="excel_file" class="col-sm-3 control-label"><span class="cheque_dd_label">&nbsp;</span> Number</label>
    <div class="col-sm-8">
      <input type="text" class="form-control" id="cheque_dd_number" name="cheque_dd_number" value="<?php echo isset($customer_details['cheque_dd_number']) ? $customer_details['cheque_dd_number'] : ''; ?>">
    </div>
  </div>

  <div class="form-group">
    <label for="excel_file" class="col-sm-3 control-label">Paid Amount(INR)</label>
    <div class="col-sm-8">
      <input type="text" class="form-control" id="paid_amount" name="paid_amount" value="<?php echo isset($customer_details['paid_amount']) ? $customer_details['paid_amount'] : ''; ?>">
    </div>
  </div>
  <div class="form-group">
    <label for="excel_file" class="col-sm-3 control-label"><span class="cheque_dd_label">Paid</span> Date</label>
    <div class="col-sm-8">
      
    <input type="text" class="form-control" id="paid_date" name="paid_date"  value="<?php echo isset($customer_details['paid_date']) ? date("d-m-Y", strtotime($customer_details['paid_date']) ) : ''; ?>" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" data-validation="date" data-validation-format="dd-mm-yyyy">
    </div>
</div>

<div class="form-group">
    <label for="transaction_remarks" class="col-sm-3 control-label">Remarks</label>
    <div class="col-sm-8">
      <textarea class="form-control" id="transaction_remarks" name="transaction_remarks"><?php echo isset($customer_details['remarks']) ? $customer_details['remarks'] : ''; ?></textarea>
    </div>
 </div>

 

  <script type="text/javascript">
    
    function customerPaymentTypeChanged(elem){
		if($(elem).val() == "Cheque" || $(elem).val() == "DD" || $(elem).val() == "NEFT" || $(elem).val() == "RTGS"){
			$(".customer_cheque_dd").show();
			$(elem).parent().parent().parent().find('.cheque_dd_label').text($(elem).val());
		}else{
			$(".customer_cheque_dd").hide();
			$(elem).parent().parent().parent().find('.cheque_dd_label').text('Paid');
		}
	}
  </script>