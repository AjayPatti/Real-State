<?php 
ob_start();
session_start();


require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");

$customer_id = $_POST['customer'];
$block_id =$_POST['block'];
$block_number_id =$_POST['block_number'];

$customer_details = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `registry_date`,`registry_by`,`khasra_no`,`maliyat_value`,`sale_value`,`registry_by_user_id`,`registry_by_datetime`,`sales_person_id` from kc_customer_blocks where `customer_id` = '".$customer_id."' AND block_number_id = ".$block_number_id." AND `block_id` = ".$block_id."  limit 0,1 "));
// print_r($customer_details);

// $registry_date = $customer_details['khasra_no'];
// $registry_by = $customer_details['khasra_no'];
// $khasra_no = $customer_details['khasra_no'];
// $maliyat_value = $customer_details['khasra_no'];
// $sale_value = $customer_details['khasra_no'];
// $created_by = $_SESSION['login_id'];
// $registry_by_user_id = $customer_details['registry_by_user_id'];
// $registry_by_datetime = $customer_details['registry_by_datetime'] ;
// $sales_person_id = $customer_details['sales_person_id'];

// $query = mysqli_query($conn,"INSERT ");
?>

    <div class="form-group">
        <label for="registry_date" class="col-sm-3 control-label">Registry Date</label>
        <div class="col-sm-8">
        <input type="text" class="form-control" id="registry_date" name="registry_date" value="<?php echo isset($customer_details['registry_date']) ? date("d-m-Y",strtotime($customer_details['registry_date'])) : '' ;?>" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="" data-validation="date" data-validation-format="dd-mm-yyyy">
        <input type="hidden" name="registry_customer_id" id="registry_customer_id" value="<?php echo $customer_id ?>">
        <input type="hidden" name="registry_block_id" id="registry_block_id" value="<?php echo $block_id ?>">
        <input type="hidden" name="registry_block_number_id" id="registry_block_number_id" value="<?php echo $block_number_id ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="registry_by" class="col-sm-3 control-label">Registry By</label>
        <div class="col-sm-8">
        <input type="text" class="form-control" id="registry_by" name="registry_by" data-validation="required" value="<?php echo isset($customer_details['registry_by']) ? $customer_details['registry_by'] : '' ;?>">
        </div>
    </div>

    <div class="form-group">
        <label for="khasra_no" class="col-sm-3 control-label">Khasra Number</label>
        <div class="col-sm-8">
        <input type="text" class="form-control" id="khasra_no" name="khasra_no" data-validation="required" value="<?php echo isset($customer_details['khasra_no']) ? $customer_details['khasra_no'] : '' ;?>">
        </div>
    </div>

    <div class="form-group">
        <label for="maliyat_value" class="col-sm-3 control-label">Maliyat Value</label>
        <div class="col-sm-8">
        <input type="text" class="form-control" id="maliyat_value" name="maliyat_value" data-validation="required" value="<?php echo isset($customer_details['maliyat_value']) ? $customer_details['maliyat_value'] : '' ;?>">
        </div>
    </div>

    <div class="form-group">
        <label for="sale_value" class="col-sm-3 control-label">Sale Value</label>
        <div class="col-sm-8">
        <input type="text" class="form-control" id="sale_value" name="sale_value" data-validation="required" value="<?php echo isset($customer_details['sale_value']) ? $customer_details['sale_value'] : '' ;?>">
        </div>
    </div>