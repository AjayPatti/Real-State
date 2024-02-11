<?php
ob_start();
session_start();

if(!isset($_POST['customer']) || !is_numeric($_POST['customer']) || !($_POST['customer'] > 0)){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$customer_id = $_POST['customer'];

$customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_customers where id = '".$customer_id."' limit 0,1 "));
?>
<?php /*?><div class="form-group">
  <label for="excel_file" class="col-sm-3 control-label">Name</label>
  <div class="col-sm-8">
  	<input type="hidden" name="customer" value="<?php echo $customer_id; ?>">
    <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($customer_details['name'])?$customer_details['name']:''; ?>" required>
  </div>
</div>
<div class="form-group">
  <label for="excel_file" class="col-sm-3 control-label">Email</label>
  <div class="col-sm-8">
    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($customer_details['email'])?$customer_details['email']:''; ?>" required>
  </div>
</div>
<div class="form-group">
  <label for="excel_file" class="col-sm-3 control-label">Mobile</label>
  <div class="col-sm-8">
    <input type="text" class="form-control" id="mobile" name="mobile" value="<?php echo isset($customer_details['mobile'])?$customer_details['mobile']:''; ?>" required>
  </div>
</div>
<div class="form-group">
  <label for="excel_file" class="col-sm-3 control-label">DOB</label>
  <div class="col-sm-8">
    <input type="text" class="form-control" id="dob" name="dob" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" value="<?php echo isset($customer_details['dob'])?date("d-m-Y",strtotime($customer_details['dob'])):''; ?>" required>
  </div>
</div>
<div class="form-group">
  <label for="excel_file" class="col-sm-3 control-label">Address</label>
  <div class="col-sm-8">
    <input type="text" class="form-control" id="address" name="address" value="<?php echo isset($customer_details['address'])?$customer_details['address']:''; ?>">
  </div>
</div>
<?php */?> 

<div class="form-group">
  <label for="name_title" class="col-sm-3 control-label">Name</label>
  <div class="col-sm-8">
    <input type="hidden" name="customer" value="<?php echo $customer_id; ?>">
    <select class="form-control" name="name_title" id="name_title" style="width:15%;float:left;">
        <option value="Mr." <?php echo (isset($customer_details['name_title']) && $customer_details['name_title'] == "Mr.")?'selected':''; ?>>Mr.</option>
        <option value="Mrs." <?php echo (isset($customer_details['name_title']) && $customer_details['name_title'] == "Mrs.")?'selected':''; ?>>Mrs.</option>
        <option value="Ms." <?php echo (isset($customer_details['name_title']) && $customer_details['name_title'] == "Ms.")?'selected':''; ?>>Ms.</option>
        <option value="Dr." <?php echo (isset($customer_details['name_title']) && $customer_details['name_title'] == "Dr.")?'selected':''; ?>>Dr.</option>
        <option value="M/s." <?php echo (isset($customer_details['name_title']) && $customer_details['name_title'] == "M/s.")?'selected':''; ?>>M/s.</option>
    </select>
    <input type="text" class="form-control col-sm-8" id="name" name="name" style="width:85%;" value="<?php echo isset($customer_details['name'])?$customer_details['name']:''; ?>" required>
  </div>
</div>

<div class="form-group">
  <label for="parent_name" class="col-sm-3 control-label"><input type="radio" value="S" name="parent_name_title" <?php echo (isset($customer_details['parent_name_relation']) && $customer_details['parent_name_relation'] == "S")?'checked':''; ?>>S/<input type="radio" value="C" name="parent_name_title" <?php echo (isset($customer_details['parent_name_relation']) && $customer_details['parent_name_relation'] == "C")?'checked':''; ?>>C/<input type="radio" value="W" name="parent_name_title" <?php echo (isset($customer_details['parent_name_relation']) && $customer_details['parent_name_relation'] == "W")?'checked':''; ?>>W/<input type="radio" value="D" name="parent_name_title" <?php echo (isset($customer_details['parent_name_relation']) && $customer_details['parent_name_relation'] == "D")?'checked':''; ?>>D of</label>
  <div class="col-sm-8">
    <select class="form-control" name="parent_name_sub_title" id="parent_name_sub_title" style="width:15%;float:left;" data-validation="required">
      <option value="Mr." <?php echo (isset($customer_details['parent_name_sub_title']) && $customer_details['parent_name_sub_title'] == "Mr.")?'selected':''; ?>>Mr.</option>
      <option value="Mrs." <?php echo (isset($customer_details['parent_name_sub_title']) && $customer_details['parent_name_sub_title'] == "Mrs.")?'selected':''; ?>>Mrs.</option>
      <option value="Ms." <?php echo (isset($customer_details['parent_name_sub_title']) && $customer_details['parent_name_sub_title'] == "Ms.")?'selected':''; ?>>Ms.</option>
      <option value="Dr." <?php echo (isset($customer_details['parent_name_sub_title']) && $customer_details['parent_name_sub_title'] == "Dr.")?'selected':''; ?>>Dr.</option>
      <option value="M/s." <?php echo (isset($customer_details['parent_name_sub_title']) && $customer_details['parent_name_sub_title'] == "M/s.")?'selected':''; ?>>M/s.</option>
    </select>
    <input type="text" class="form-control col-sm-8" id="parent_name" name="parent_name" value="<?php echo isset($customer_details['parent_name'])?$customer_details['parent_name']:''; ?>" style="width:85%;"  />
  </div>
</div>

<div class="form-group">
  <label for="nationality" class="col-sm-3 control-label">Nationality</label>
  <div class="col-sm-8">
    <input type="text" class="form-control" id="nationality" name="nationality" value="<?php echo isset($customer_details['nationality'])?$customer_details['nationality']:''; ?>">
  </div>
</div>

<div class="form-group">
  <label for="profession" class="col-sm-3 control-label">Profession</label>
  <div class="col-sm-8">
    <input type="text" class="form-control" id="profession" name="profession" value="<?php echo isset($customer_details['profession'])?$customer_details['profession']:''; ?>" >
  </div>
</div>

<div class="form-group">
  <label for="dob" class="col-sm-3 control-label">DOB</label>
  <div class="col-sm-8">
    <input type="text" class="form-control" id="dob" name="dob" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" value="<?php echo isset($customer_details['dob'])?date("d-m-Y",strtotime($customer_details['dob'])):''; ?>"  required>
  </div>
</div>

<div class="form-group">
  <label for="nominee_name" class="col-sm-3 control-label">Co-owner Name</label>
  <div class="col-sm-8">
    <input type="text" class="form-control" id="nominee_name" name="nominee_name" value="<?php echo isset($customer_details['nominee_name'])?$customer_details['nominee_name']:''; ?>" >
  </div>
</div>

<div class="form-group">
  <label for="nominee_relation" class="col-sm-3 control-label">Co-owner Relation</label>
  <div class="col-sm-8">
  <input type="text" class="form-control" id="nominee_relation" name="nominee_relation" value="<?php echo isset($customer_details['nominee_relation'])?$customer_details['nominee_relation']:''; ?>">
  </div>
</div>

<div class="form-group">
  <label for="excel_file" class="col-sm-3 control-label">Residential Status</label>
  <div class="col-sm-8">
    <select class="form-control" name="residentail_status" id="residentail_status">
        <option value="Resident" <?php echo (isset($customer_details['residentail_status']) && $customer_details['residentail_status'] == "Resident")?'selected':''; ?>>Resident</option>
        <option value="Non-Resident" <?php echo (isset($customer_details['residentail_status']) && $customer_details['residentail_status'] == "Non-Resident")?'selected':''; ?>>Non-Resident</option>
        <option value="Foreign National of India Origin" <?php echo (isset($customer_details['residentail_status']) && $customer_details['residentail_status'] == "Foreign National of India Origin")?'selected':''; ?>>Foreign National of India Origin</option>
    </select>
  </div>
</div>

<div class="form-group">
  <label for="excel_file" class="col-sm-3 control-label">PAN No./Aadhar No.</label>
  <div class="col-sm-8">
    <input type="text" class="form-control" id="pan_no" name="pan_no" value="<?php echo isset($customer_details['pan_no'])?$customer_details['pan_no']:''; ?>" >
  </div>
</div>


<div class="form-group">
  <label for="excel_file" class="col-sm-3 control-label">Address</label>
  <div class="col-sm-8">
    <input type="text" class="form-control" id="address" name="address" value="<?php echo isset($customer_details['address'])?$customer_details['address']:''; ?>">
  </div>
</div>


<div class="form-group">
  <label for="excel_file" class="col-sm-3 control-label">Mobile</label>
  <div class="col-sm-8">
    <input type="text" class="form-control" id="mobile" name="mobile" value="<?php echo isset($customer_details['mobile'])?$customer_details['mobile']:''; ?>"  required>
  </div>
</div>

<div class="form-group">
  <label for="excel_file" class="col-sm-3 control-label">Email</label>
  <div class="col-sm-8">
    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($customer_details['email'])?$customer_details['email']:''; ?>" >
  </div>
</div>


<div class="form-group">
  <label for="excel_file" class="col-sm-3 control-label">Residence/Office</label>
  <div class="col-sm-8">
    <input type="text" class="form-control" id="office_address" name="office_address" value="<?php echo isset($customer_details['office_address'])?$customer_details['office_address']:''; ?>">
  </div>
</div>