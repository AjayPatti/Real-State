<?php
ob_start();
session_start();

if(!isset($_POST['block_number']) || !is_numeric($_POST['block_number']) || !($_POST['block_number'] > 0)){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$block_number_id = $_POST['block_number'];

$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_block_numbers where id = '".$block_number_id."' limit 0,1 "));
$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_blocks where id = '".$block_number_details['block_id']."' limit 0,1 "));
?>
<div class="form-group">
    <label for="excel_file" class="col-sm-3 control-label">Plot Number</label>
    <div class="col-sm-8">
    	<input type="hidden" value="<?php echo $block_number_id; ?>" name="block_number">
    	<input type="text" class="form-control" id="block_number_edit" name="block_number_edit" maxlength="50" value="<?php echo $block_number_details['block_number']; ?>" required>
    </div>
</div>


<div class="form-group">
    <label for="excel_file" class="col-sm-3 control-label">Total Area(sq. ft.)</label>
    <div class="col-sm-8">
        <input type="text" class="form-control" id="area_edit" name="area_edit" maxlength="255" value="<?php echo $block_number_details['area']; ?>" required>
    </div>
</div>

<div class="form-group">
    <label for="excel_file" class="col-sm-3 control-label">PLC</label>
    <div class="col-sm-8">
        <select class="form-control select2" name="plc_edit[]" id="plc_edit" multiple  style="width: 100%;">
            <?php
            $selected_plc = array();
            $plcs = mysqli_query($conn,"select * from kc_block_number_plc where block_number_id = '".$block_number_details['id']."' ");
            while($plc = mysqli_fetch_assoc($plcs)){
                $selected_plc[] = $plc['plc_id'];
            }
        $plcs = mysqli_query($conn,"select * from kc_plc where status = '1' ");
            while($plc = mysqli_fetch_assoc($plcs)){ ?>
                <option value="<?php echo $plc['id']; ?>" <?php if(in_array($plc['id'],$selected_plc)){ echo "selected"; } ?>><?php echo $plc['name']; ?>(<?php echo $plc['plc_percentage']; ?> %)</option>
            <?php } ?>
        </select>
     </div>
</div>


<div class="form-group">
<label for="excel_file" class="col-sm-3 control-label">Road</label>
<div class="col-sm-8">
<input type="text" class="form-control" id="road_edit" name="road_edit" maxlength="255" value="<?php echo $block_number_details['road']; ?>" required>
</div>
</div>

<div class="form-group">
    <label for="excel_file" class="col-sm-3 control-label">Face</label>
    <div class="col-sm-8">
    	<?php $faces = explode(",",$block_number_details['face']); ?>
        <div class="checkbox icheck">
          <label>
            <input type="checkbox" value="East" name="face_edit[]" <?php if(in_array('East',$faces)){ echo "checked"; } ?>> East
          </label>
        </div>
        <div class="checkbox icheck">
          <label>
            <input type="checkbox" value="West" name="face_edit[]" <?php if(in_array('West',$faces)){ echo "checked"; } ?>> West
          </label>
        </div>
        <div class="checkbox icheck">
          <label>
            <input type="checkbox" value="North" name="face_edit[]" <?php if(in_array('North',$faces)){ echo "checked"; } ?>> North
          </label>
        </div>
        <div class="checkbox icheck">
          <label>
            <input type="checkbox" value="South" name="face_edit[]" <?php if(in_array('South',$faces)){ echo "checked"; } ?>> South
          </label>
        </div>
    </div>
</div>