<?php
ob_start();
session_start();

if(!isset($_POST['farmer']) || !is_numeric($_POST['farmer']) || !($_POST['farmer'] > 0)){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$farmer_id = $_POST['farmer'];
$farmer_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_farmers where id = '".$farmer_id."' limit 0,1 "));
// echo "<pre>";
$farmer_details_id = $farmer_details['id'];
if(isset($farmer_details_id))
{
  $farmer_details_account = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `kc_farmers_account` WHERE `farmers_id` = $farmer_details_id"));  
  // print_r($farmer_details_account);
}
// print_r($farmer_details);
// echo "<hr>";
// die;
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

<div class="container-fluid">
    <div class="row">
      <div class="col-md-7">  
        <div class="form-group">
          <label for="name_title" class="col-sm-4 control-label">Name <span class="text-danger"> *</span></label>
          <div class="col-sm-8">
            <input type="hidden" name="farmer" value="<?php echo $farmer_id; ?>">
            <select class="form-control" name="name_title" id="edit_name_title" style="width:20%;float:left;">
                <option value="Mr." <?php echo (isset($farmer_details['name_title']) && $farmer_details['name_title'] == "Mr.")?'selected':''; ?>>Mr.</option>
                <option value="Mrs." <?php echo (isset($farmer_details['name_title']) && $farmer_details['name_title'] == "Mrs.")?'selected':''; ?>>Mrs.</option>
                <option value="Ms." <?php echo (isset($farmer_details['name_title']) && $farmer_details['name_title'] == "Ms.")?'selected':''; ?>>Ms.</option>
                <option value="Dr." <?php echo (isset($farmer_details['name_title']) && $farmer_details['name_title'] == "Dr.")?'selected':''; ?>>Dr.</option>
                <option value="M/s." <?php echo (isset($farmer_details['name_title']) && $farmer_details['name_title'] == "M/s.")?'selected':''; ?>>M/s.</option>
            </select>
            <input type="text" class="form-control col-sm-8" id="edit_name" name="name" style="width:80%;" value="<?php echo isset($farmer_details['name'])?$farmer_details['name']:''; ?>" required>
          </div>
        </div>
        <div class="form-group">
          <label for="parent_name" class="col-sm-4 control-label"><input type="radio" value="S" name="parent_name_relation" <?php echo (isset($farmer_details['parent_name_relation']) && $farmer_details['parent_name_relation'] == "S")?'checked':''; ?>>S/<input type="radio" value="C" name="parent_name_relation" <?php echo (isset($farmer_details['parent_name_relation']) && $farmer_details['parent_name_relation'] == "C")?'checked':''; ?>>C/<input type="radio" value="W" name="parent_name_relation" <?php echo (isset($farmer_details['parent_name_relation']) && $farmer_details['parent_name_relation'] == "W")?'checked':''; ?>>W/<input type="radio" value="D" name="parent_name_relation" <?php echo (isset($farmer_details['parent_name_relation']) && $farmer_details['parent_name_relation'] == "D")?'checked':''; ?>>D of <span class="text-danger"> *</span></label>
          <div class="col-sm-8">
            <select class="form-control" name="parent_name_sub_title" id="parent_name_sub_title" style="width:20%;float:left;" data-validation="required">
              <option value="Mr." <?php echo (isset($farmer_details['parent_name_sub_title']) && $farmer_details['parent_name_sub_title'] == "Mr.")?'selected':''; ?>>Mr.</option>
              <option value="Mrs." <?php echo (isset($farmer_details['parent_name_sub_title']) && $farmer_details['parent_name_sub_title'] == "Mrs.")?'selected':''; ?>>Mrs.</option>
              <option value="Ms." <?php echo (isset($farmer_details['parent_name_sub_title']) && $farmer_details['parent_name_sub_title'] == "Ms.")?'selected':''; ?>>Ms.</option>
              <option value="Dr." <?php echo (isset($farmer_details['parent_name_sub_title']) && $farmer_details['parent_name_sub_title'] == "Dr.")?'selected':''; ?>>Dr.</option>
              <option value="M/s." <?php echo (isset($farmer_details['parent_name_sub_title']) && $farmer_details['parent_name_sub_title'] == "M/s.")?'selected':''; ?>>M/s.</option>
            </select>
            <input type="text" class="form-control col-sm-8" id="parent_name" name="parent_name" value="<?php echo isset($farmer_details['parent_name'])?$farmer_details['parent_name']:''; ?>" style="width:80%;"  />
          </div>
        </div>
        <div class="form-group">
          <label for="excel_file" class="col-sm-4 control-label">Village <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <input type="text" class="form-control" id="village" name="village" value="<?php echo isset($farmer_details['village'])?$farmer_details['village']:''; ?>">
          </div>
        </div>
        <div class="form-group">
          <label for="excel_file" class="col-sm-4 control-label">Mobile <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <input type="text" class="form-control" id="edit_mobile" name="mobile" value="<?php echo isset($farmer_details['mobile'])?$farmer_details['mobile']:''; ?>"  required>
          </div>
        </div>
        <div class="form-group">
          <label for="purchaser" class="col-sm-4 control-label">Purchaser Name <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <input type="text" class="form-control" id="purchaser" name = "purchaser" data-validation="required" value="<?php echo isset($farmer_details['purchaser_name'])?$farmer_details['purchaser_name']:''; ?> " >
          </div>
        </div>
        <div class="form-group">
            <label for="khasra_no" class="col-sm-4 control-label">Khasra No <span class="text-danger">*</span></label>
            <div class="col-sm-8">
            <input type="text" class="form-control" name="khasra_no" id="khasra_no"  value="<?php echo isset($farmer_details['khashra_no'])?$farmer_details['khashra_no']:''; ?>" data-validation="required">
            </div>
        </div>
        <div class="form-group">
            <label for="area_in_hectare" class="col-sm-4 control-label">Total Area In Hectare <span class="text-danger">*</span></label>
            <div class="col-sm-8">
            <input type="text" class="form-control" name="area_in_hectare" id="edit_area_hectare"  value="<?php echo isset($farmer_details['area_hectare'])?$farmer_details['area_hectare']:''; ?>" data-validation="required">
            </div>
        </div>
        <div class="form-group">
           <label for="rate_per_Biswa" class="col-sm-4 control-label">Rate per Biswa <span class="text-danger">*</span></label>
           <div class="col-sm-8">
           <input type="text" class="form-control" name="per_biswa" id="edit_amount"  value="<?php echo isset($farmer_details['per_biswa'])?$farmer_details['per_biswa']:'0'; ?>" autocomplete="off" data-validation="required"
           data-validation-allowing="range[1;10000]">
          </div>
        </div> 
         <!-- <div class="form-group"> -->
            <!-- <label for="area_in_sqft" class="col-sm-4 control-label">Total Area In Sq.Ft <span class="text-danger">*</span></label> -->
            <!-- <div class="col-sm-8"> -->
            <!-- <input type="text" class="form-control" name="area_in_sqft" id="ar_sqft"  value="<?php echo isset($farmer_details['area_sqft'])?$farmer_details['area_sqft']:''; ?>"data-validation="required"> 
             </div> 
         </div>-->
        <div class="form-group">
            <label for="area_in_biswa" class="col-sm-4 control-label">Total Area In Biswa <span class="text-danger">*</span></label>
            <div class="col-sm-8">
            <input type="text" class="form-control" name="total_area_biswa" id="edit_area_biswa"  value="<?php echo isset($farmer_details['total_area_biswa'])?$farmer_details['total_area_biswa']:''; ?>" data-validation="required">
            </div>
        </div>
        <div class="form-group">
            <label for="payable_amount" class="col-sm-4 control-label">Total Plot Value(INR) <span class="text-danger">*</span></label>
            <div class="col-sm-8">
            <input type="text" class="form-control" id="edit_payable_amount"  name="payable_amount"  value="<?php echo isset($farmer_details['plot_value'])?$farmer_details['plot_value']:''; ?>" data-validation="number" data-validation-allowing="range[1;1000000000]">
            </div>
        </div>
        <div class="form-group">
            <label for="broker" class="col-sm-4 control-label">Broker<span class="text-danger">*</span></label>
            <div class="col-sm-8">
                <input type="text" class="form-control" name="broker" id="broker" value="<?php echo isset($farmer_details['broker'])?$farmer_details['broker']:'';?>" data-validation="required">
            </div>
        </div>
        <div class="form-group">
            <label for="seller_paid_amount" class="col-sm-4 control-label">Seller <span class="text-danger"> *</span></label></label>
            <div class="col-sm-8">
              <input type="text" class="form-control"  name="seller"  value="<?php echo isset($farmer_details['seller'])?$farmer_details['seller']:''; ?>" >
            </div>
          </div>
      </div>
      <div class="col-md-5">
      <!-- <h5>Account Details</h5> -->
        <div class="form-group">
          <!-- <label for="village" class="col-sm-4 control-label">Account No.</label> -->
          <div class="col-sm-8">
            <input type="hidden" class="form-control" id="farmer-id" name="farmerid"  value= "<?php echo $farmer_id; ?>"  >
            <input type="hidden" class="form-control" id="farmers-id" name="farmersid"  value= "<?php echo isset($farmer_details_account['farmers_id'])?$farmer_details_account['farmers_id']:''; ?>"  >
          </div>
        </div>
        <div class="form-group">
          <label for="village" class="col-sm-4 control-label">Account No.</label>
          <div class="col-sm-8">
            <input type="text" class="form-control" id="farmers-account" name="farmersaccount" value= "<?php echo isset($farmer_details_account['account_number'])?$farmer_details_account['account_number']:''; ?>"  >
          </div>
        </div>
        <div class="form-group">
          <label for="village" class="col-sm-4 control-label">Bank Name</label>
          <div class="col-sm-8">
            <input type="text" class="form-control" id="farmers-bankname" name="farmersbankname" value= "<?php echo isset($farmer_details_account['bank_name'])?$farmer_details_account['bank_name']:''; ?>"  >
          </div>
        </div>
        <div class="form-group">
          <label for="village" class="col-sm-4 control-label">Branch</label>
          <div class="col-sm-8">
            <input type="text" class="form-control" id="farmers-branch" name="farmersbranch" value= "<?php echo isset($farmer_details_account['branch_name'])?$farmer_details_account['branch_name']:''; ?>"  >
          </div>
        </div>
        <div class="form-group">
          <label for="village" class="col-sm-4 control-label">IFSC Code</label>
          <div class="col-sm-8">
            <input type="text" class="form-control" id="farmers-ifsccode" name="farmersifsccode" value= "<?php echo isset($farmer_details_account['ifsc_code'])?$farmer_details_account['ifsc_code']:''; ?>"  >
          </div>
        </div>
      </div>
    </div>
</div>

<?php require('../includes/common-js.php'); ?>

 <script type="text/javascript">

$('#edit_area_hectare').on('keyup', function(){
        let hectare = $('#edit_area_hectare').val();
        $('#ar_sqft').val(107639*hectare);
        $('#edit_area_biswa').val(79.732668*hectare);
    });

    // $('#ar_sqft').on('keyup', function(){
        // let sqft = $('#ar_sqft').val();
        // $('#ar_hectare').val(0.000009290303997*sqft);
        // $('#ar_biswa').val(0.0007*sqft);
    // });

	$('#edit_area_biswa').on('keyup', function(){
        let biswa = $('#edit_area_biswa').val();
        $('#edit_area_hectare').val(0.0125*biswa);
        // $('#ar_sqft').val(1350*biswa);
    });

	$('#edit_amount').on('keyup', function(){
		let biswa = $('#edit_area_biswa').val();
    let amount = $('#edit_amount').val();
		var total = Math.ceil(biswa *amount);
     $('#edit_payable_amount').val(total);
        // $('#ar_sqft').val(1350*biswa);
    });

	$('#edit_mobile').on('keyup',function(){
		$('#edit_name , #edit_name_title').prop('required',false);
	});

  </script>
            