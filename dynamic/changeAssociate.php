<?php
ob_start();
session_start();

if(!isset($_POST['customer']) || !is_numeric($_POST['customer']) || !($_POST['customer'] > 0)){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$customer_id = isset($_POST['customer'])?(int) $_POST['customer']:0;
$block_id = isset($_POST['block'])?(int) $_POST['block']:0;
$block_number_id = isset($_POST['block_number'])?(int) $_POST['block_number']:0;

$customer_details = (mysqli_query($conn,"select  b.code,b.mobile_no,b.name, a.customer_id,a.id, a.associate, a.associate_percentage, a.block_id, a.block_number_id from kc_associate_percentage as a join kc_associates as b on b.id =a.associate where a.customer_id = '".$customer_id."' and a.block_id = '$block_id' and a.block_number_id = '$block_number_id' limit 0,7 "));
$associate_detail =[];
while($row =mysqli_fetch_assoc($customer_details)){
  $associate_detail[] =$row;
if(!isset($row['customer_id'])){
  die;
}

// $associate_detail[] =mysqli_fetch_array(mysqli_query($conn,"select id, code, name, mobile_no from kc_associates where id = '".$row['associate']."' limit 0,7 "));
}
// die;
// echo "<pre>"; print_r($associate_detail);

?>
<div class="form-group">
    <div class="row">
        <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>" />
        <input type="hidden" name="block_id" value="<?php echo $block_id; ?>" />
        <input type="hidden" name="block_number_id" value="<?php echo $block_number_id; ?>" />

        <!-- Include label inside appropriate column div -->
        <div class="col-sm-2"></div>
        <div class="col-sm-1">
            <label for="sales_person" class="control-label">Sales Person <span class="text-danger">*</span></label>
        </div>
        
        <div class="col-sm-4">
        <?php foreach($associate_detail as $key => $value): ?>
                <input type="text" class="form-control associate-autocomplete" data-for-id="change_associate_<?php echo $key; ?>" placeholder="Name or Code" value="<?php echo isset($value['code']) ? $value['code'].'-'.$value['name'].'('.$value['mobile_no'].')' : ''; ?>">
                <input type="text" name="associate_percentage[]" class="form-control" value="<?php echo $value['associate_percentage']; ?>" style=" margin-right: right; margin-left: 103%;width:93px; margin-top: -32px;"><br>
                <input type="hidden" name="associate_id[]" id="change_associate_<?php echo $key; ?>" value="<?php echo $value['associate']; ?>">
                <input type="hidden" name="id[]" id="associate_<?php echo $key; ?>" value="<?php echo $value['id']; ?>">
                <?php endforeach; ?>
              </div><br>
    </div>
</div>
