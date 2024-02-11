<?php
    ob_start();
    session_start();
    if(!isset($_POST['associate']) || !is_numeric($_POST['associate']) || !($_POST['associate'] > 0)){
    	exit();
    }
    require("../includes/host.php");
    require("../includes/kc_connection.php");
    require("../includes/common-functions.php");
    $associates = mysqli_query($conn,"select id, customer_id, block_id, block_number_id from kc_customer_blocks where associate = '".$_POST['associate']."' and status = '1' "); 
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">Add Transaction</h4>
</div>
<div class="modal-body">
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="col-md-12">
                <h3 class="box-title">Add Transaction Panel</h3>
            </div>
        </div><!-- /.box-header -->
        <!-- form start -->
        <div class="box-body">
            <div class="form-group">
              <label for="excel_file" class="col-sm-3 control-label">Block</label>
              <div class="col-sm-8">
                <?php if(mysqli_num_rows($associates)>0){ ?>
                <select class="form-control" id="block" name="block" onChange="getCustomerId(this);">
                    <option value="">Select Block</option>
                    <?php
                        while($associate = mysqli_fetch_assoc($associates)){
                    ?>
                    <option for="<?php echo $associate['customer_id']; ?>" value="<?php echo ($associate['block_id'].'-'.$associate['block_number_id']); ?>"><?php echo (blockName($conn,$associate['block_id']).'- Plot Number '.blockNumberName($conn,$associate['block_number_id'])); ?></option>
                    <?php } ?>
                </select>
            <?php }else{ echo "<span class='text-danger'>No Block Available for this associate?</span>"; } ?>
              </div>

            </div>
            <div class="form-group">
              <label for="excel_file" class="col-sm-3 control-label">Payment Mode</label>
              <div class="col-sm-8">
                <input type="hidden" name="customer_id" id="customer_id" value="" />
                <input type="hidden" name="associate_id" id="associate_id" value="<?php echo $_POST['associate']; ?>" />
                <select class="form-control" id="payment_type" name="payment_type" onChange="paymentTypeChanged(this);">
                    <option value="">Select Payment Mode</option>
                    <option value="Cash">Cash</option>
                    <option value="DD">DD</option>
                    <option value="Cheque">Cheque</option>
                    <option value="NEFT">NEFT</option>
                    <option value="RTGS">RTGS</option>
                </select>
              </div>
            </div>
            
            <div class="form-group cheque_dd" style="display:none;">
              <label for="excel_file" class="col-sm-3 control-label">Bank Name</label>
              <div class="col-sm-8">
                <input type="text" class="form-control" id="bank_name" name="bank_name">
              </div>
            </div>
            <div class="form-group cheque_dd" style="display:none;">
              <label for="excel_file" class="col-sm-3 control-label"><span class="cheque_dd_label">&nbsp;</span> Number</label>
              <div class="col-sm-8">
                <input type="text" class="form-control" id="cheque_dd_number" name="cheque_dd_number">
              </div>
            </div>
            
            <div class="form-group">
              <label for="excel_file" class="col-sm-3 control-label">Paid Amount(INR)</label>
              <div class="col-sm-8">
                <input type="text" class="form-control" id="paid_amount" name="paid_amount">
              </div>
            </div>
            <div class="form-group">
              <label for="excel_file" class="col-sm-3 control-label"><span class="cheque_dd_label">Paid</span> Date</label>
              <div class="col-sm-8">
                <input type="text" class="form-control" id="paid_date" name="paid_date" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask="">
              </div>
            </div>
        </div><!-- /.box-body -->
        
    </div><!-- /.box -->
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
    <button type="submit" class="btn btn-primary" id="save" name="addTransaction">Save changes</button>
</div>