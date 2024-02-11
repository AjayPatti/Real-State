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

$customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_possession_transfer where id = '".$customer_id."' limit 0,1 "));
$customer_name =customerName($conn,$customer_details['customer_id']);
$block_name=blockName($conn,$customer_details['block_id']);
// print_r($block_name);die;
?>


<div class="box-body">

    <div class="form-group">
        <label for="sales_person" class="col-sm-3 control-label">Customer Name<span class="text-danger">*</span></label>
        <div class="col-sm-8">
            <input type="text" class="form-control customer-autocomplete" data-for-id="associate"
                placeholder="Customer Name" data-validation="required" value="<?php echo $customer_name ;?>">
            <input type="hidden" name="customer_id" id="customer_id" value="<?php echo $customer_details['customer_id'] ;?>">

        </div>
        <input type="hidden" name="possession_id" value="<?php echo $customer_details['id']?>">
    </div>
    <div class="form-group">
        <label for="project" class="col-sm-3 control-label">Project <span class="text-danger">*</span></label>
        <div class="col-sm-8">
            <select class="form-control project_id" id="project" name="project" onChange="getBlocks(this.value);"
                data-validation="required">
                <option value="">Select Project</option>
                <?php
$projects = mysqli_query($conn,"select * from kc_projects where status = '1' ");
while($project = mysqli_fetch_assoc($projects)){ ?>
                <option value="<?php echo $project['id']; ?>" <?php if($customer_details['project_id']==$project['id']){echo "selected=selected";}else{echo "";} ?>>
                    <?php echo $project['name']; ?></option>
                <?php } ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="block" class="col-sm-3 control-label">Block <span class="text-danger">*</span></label>
        <div class="col-sm-8">
            <select class="form-control block" id="block" name="block" onChange="getBlockNumbers(this.value);"
                data-validation="required">
                <option value="">Select Block</option>
                <?php
                $blocks = mysqli_query($conn,"select * from kc_blocks where status = '1' ");
while($block = mysqli_fetch_assoc($blocks)){ ?>
                <option value="<?php echo $block['id']; ?>" <?php if($block['id'] == $customer_details['block_id']){ echo "selected"; } ?>><?php echo $block['name']; ?></option>
                </option>
                <?php } 
                ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="block_number" class="col-sm-3 control-label">Plot Number <span class="text-danger">*</span></label>
        <div class="col-sm-8">
            <select class="form-control block_number" id="block_number" name="block_number" onChange="blockNumberChanged(this);"
                data-validation="required">
                <option value="">Select Plot Number</option>
                <?php
                $query = "select id, block_number from kc_block_numbers "; 
                $block_numbers = mysqli_query($conn,$query);
                if(mysqli_num_rows($block_numbers) > 0){
                while($block_number = mysqli_fetch_assoc($block_numbers)){
                    ?><option value="<?php echo $block_number['id']; ?>" <?php if($block_number['id'] == $customer_details['plot_id']){ echo "selected"; } ?>><?php echo $block_number['block_number']; ?></option><?php
                }
                }
        ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="area" class="col-sm-3 control-label">Total Area <span class="text-danger">*</span></label>
        <div class="col-sm-8">
            <input type="text" name="area" class="form-control area" id="area" value="<?php echo $customer_details['area']; ?>" readonly >
        </div>
    </div>

    <div class="form-group">
        <label for="customer_paid_date" class="col-sm-3 control-label"><span class="cheque_dd_label">Registry</span>
            Date</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" id="customer_paid_date" name="registry_date"
                data-inputmask="'alias': 'yyyy-mm-dd'" data-mask="" data-validation="date"
                data-validation-format="yyyy-mm-dd" data-validation-depends-on="payment_type" value="<?php echo $customer_details['registry_date'];?>">
        </div>
    </div>


    <div class="form-group">
        <label for="transaction_remarks" class="col-sm-3 control-label">Remarks</label>
        <div class="col-sm-8">
            <textarea class="form-control" id="transaction_remarks" name="transaction_remarks"><?php echo $customer_details['remark']; ?></textarea>
        </div>
    </div>

    <!-- <div class="form-group">
        <label for="send_message" class="col-sm-3 control-label">Send Message</label>
        <div class="col-sm-8">
            <input type="checkbox" name="send_message" id="send_message" class="form-control" />
        </div>
    </div> -->

</div><!-- /.box-body -->