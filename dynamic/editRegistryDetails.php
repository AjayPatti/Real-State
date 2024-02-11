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
$farmerregistry_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_farmers where id = '".$farmer_id."' limit 0,1 "));
// echo "<pre>";
$farmerregistry_details_id = $farmerregistry_details['id'];
// if(isset($farmerregistry_details_id))
// {
//   $farmerregistry_details_account = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `kc_farmers_account` WHERE `farmers_id` = $farmerregistry_details_id"));
// }
?>
    
					
						<div class="form-group">
						  <label for="registry_date" class="col-sm-3 control-label">Registry Date</label>
						  <div class="col-sm-8">
						  <input type="hidden" name="farmer" value="<?php echo $farmer_id; ?>">
							<input type="text" class="form-control" id="registry_date" name="registry_date" value="<?php echo  isset( $farmerregistry_details['registry_date'])?date("d-m-Y",strtotime($farmerregistry_details['registry_date'])):'';  ?>" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" data-validation="birthdate" data-validation-format="dd-mm-yyyy">
							<!-- <input type="hidden" name="editregistry_farmer_id" id="editregistry_farmer_id"> -->
                            <!-- <input type="hidden" name="registry_block_id" id="registry_block_id">
                            <input type="hidden" name="registry_block_number_id" id="registry_block_number_id"> -->
						  </div>
						</div>
						 

						<div class="form-group">
						  <label for="khasra_no" class="col-sm-3 control-label">Khasra Number</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="khasra_no" name="khashra_no" value="<?php echo $farmerregistry_details['khashra_no']; ?>" data-validation="required">
						  </div>
						</div>

						<div class="form-group">
						  <label for="purchase_value" class="col-sm-3 control-label">Purchase Value</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="purchase_value" name="purchase_value" value="<?php echo $farmerregistry_details['purchase_value'];?>" data-validation="required">
						  </div>
						</div>

						<div class="form-group">
						  <label for="maliyat_value" class="col-sm-3 control-label">Maliyat Value</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="maliyat_value" name="maliyat_value" value="<?php echo $farmerregistry_details['maliyat_value'];?>" data-validation="required">
						  </div>
						</div>

						<div class="form-group">
						  <label for="sale_value" class="col-sm-3 control-label">Stamp Fee</label>
						  <div class="col-sm-8">
							<input type="text" class="form-control" id="stamp_fee" name="stamp_fee" value="<?php echo $farmerregistry_details['stamp_fee'];?>" data-validation="required">
						  </div>
						</div>
                    
