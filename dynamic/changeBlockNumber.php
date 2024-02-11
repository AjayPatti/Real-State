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

$customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select cb.id as customer_block_id, cb.customer_id, cb.block_id, cb.block_number_id, cb.sales_person_id from kc_customer_blocks cb INNER JOIN kc_block_numbers bn ON bn.id = cb.block_number_id where cb.customer_id = '".$customer_id."' and cb.block_id = '$block_id' and cb.block_number_id = '$block_number_id' limit 0,1 "));
if(!isset($customer_details['customer_id'])){
  die;
}


?>
<div class="form-group">
  <label for="search_block">Block</label>
  <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>" />
  <input type="hidden" name="block_id" value="<?php echo $block_id; ?>" />
  <input type="hidden" name="block_number_id" value="<?php echo $block_number_id; ?>" />
  <select class="form-control" id="changed_block" name="changed_block" onChange="changed_getBlockNumbers(this.value,<?php echo $block_number_id; ?>,<?php echo $block_id; ?>);">
    <option value="">Select Block</option>
    <?php
    $blocks = mysqli_query($conn,"select * from kc_blocks where status = '1' ");
    while($block = mysqli_fetch_assoc($blocks)){ ?>
        <option value="<?php echo $block['id']; ?>" <?php if($block['id'] == $block_id){ echo "selected"; } ?>><?php echo $block['name']; ?></option>
    <?php } ?>
  </select>
</div>
<div class="form-group">
  <label for="search_block_no">Plot Number</label>
  <select class="form-control" id="changed_block_no" name="changed_block_no" onchange="getPLC(this);">
        <option value="">Select Plot Number</option>
        <?php
        $query = "select id, block_number from kc_block_numbers where id = '$block_number_id' or (id NOT IN (select block_number_id from kc_customer_blocks where status = '1') and block_id = '$block_id') order by CAST(block_number AS unsigned)"; //area = '".$block_number_details['area']."' and 
        $block_numbers = mysqli_query($conn,$query);
        if(mysqli_num_rows($block_numbers) > 0){
          while($block_number = mysqli_fetch_assoc($block_numbers)){
              ?><option value="<?php echo $block_number['id']; ?>" <?php if($block_number['id'] == $block_number_id){ echo "selected"; } ?>><?php echo $block_number['block_number']; ?></option><?php
          }
        }
        /*$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select area from kc_block_numbers where id = '".$block_number_id."' and block_id = '$block_id' limit 0,1 "));
        if(isset($block_number_details['area'])){
          
        }*/ ?>
    </select>
</div>

<div class="form-group">
  <label for="plc" class="col-sm-3 control-label">PLC</label>
  <div class="col-sm-8">
  <select class="form-control select2" name="plc[]" id="changed_plc" multiple  style="width: 100%;" readonly>
    <?php
    $old_plc = mysqli_query($conn,"select plc_id from kc_customer_block_plc where customer_block_id = '".$customer_details['customer_block_id']."' and status = '1' ");
    $old_plcs = array();
    while($o_plc = mysqli_fetch_assoc($old_plc)){
      $old_plcs[] = $o_plc['plc_id'];
    }
    $plcs = mysqli_query($conn,"select * from kc_plc where status = '1' ");
    while($plc = mysqli_fetch_assoc($plcs)){ ?>
        <option value="<?php echo $plc['id']; ?>" <?php if(in_array($plc['id'],$old_plcs)){ echo "selected"; } ?>><?php echo $plc['name']; ?>(<?php echo $plc['plc_percentage']; ?> %)</option>
      <?php } ?>
    </select>
  </div>
</div>