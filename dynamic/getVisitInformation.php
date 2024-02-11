<?php
ob_start();
session_start();

if(!isset($_POST['associateID']) || !is_numeric($_POST['associateID']) || !($_POST['associateID'] > 0)){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$associate_id = $_POST['associateID'];

$associate_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_visit_forms where id = '".$associate_id."' limit 0,1 "));
// print_r($associate_details);die;
?>
<div class="form-group">
  <label for="code" class="col-sm-3 control-label">Name</label>
  <div class="col-sm-8">
  <input type="text" class="form-control" id="code" name="name" maxlength="255" required value="<?php echo isset($associate_details['name'])?$associate_details['name']:''; ?>" />
  <input type="hidden" name="id" value="<?php echo isset($associate_details['id'])?$associate_details['id']:''; ?>" />
  </div>
</div>

<div class="form-group">
  <label for="excel_file" class="col-sm-3 control-label">Mobile Number</label>
  <div class="col-sm-8">
  <input type="text" class="form-control" id="name" name="mobile" maxlength="255" required value="<?php echo isset($associate_details['mobile'])?$associate_details['mobile']:''; ?>" />
  </div>
</div>
            
<div class="form-group">
  <label for="excel_file" class="col-sm-3 control-label">Visit Date</label>
  <div class="col-sm-8">
  <input type="text" class="form-control" id="mobile" name="visit_date" maxlength="255" data-inputmask="'alias': 'yyyy-mm-dd'" data-mask="" required value="<?php echo isset($associate_details['visit_datetime'])?$associate_details['visit_datetime']:''; ?>" />
  </div>
</div>
<div class="form-group">
  <label for="excel_file" class="col-sm-3 control-label">Associate</label>
  <div class="col-sm-8">
  <input type="text" class="form-control visit-forms-autocomplete"  data-for-id="search_associate" id="mobile" name="associate_name" maxlength="255" required value="<?php echo ($associate_details['associate_id'] > 0)?associateName($conn,$associate_details['associate_id']):''; ?>" />
  <input type="hidden" name="associate_id" class="associate_id"  id="associate_id" value="<?php  echo isset($associate_details['associate_id']) ?? ''?>">  
  </div>
</div>
<?php if($associate_details['project_id'] > 0){
        $detail = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_projects where id = '".$associate_details['project_id']."' and status = '1' "));
        $project_name = isset($detail['name'])?$detail['name']:'';
        }else{
            $project_name = 'NA';
        } 
?>
<div class="form-group">
  <label for="excel_file" class="col-sm-3 control-label">Project</label>
  <div class="col-sm-8">
  <input type="text" class="form-control visit-forms-autocomplete-project" id="mobile" name="project" data-for-id="search_associate" maxlength="255" required value="<?php echo $project_name; ?>" />

  <input type="hidden" name="project_id" id="project_id" class="product_id" value="<?php echo isset($associate_details['project_id']) ?? ''?>"> 
  </div>
</div>
<div class="form-group">
							<label for="send_message" class="col-sm-3 control-label">Send Message</label>
							<div class="col-sm-8">
								<input type="checkbox" name="send_message" id="send_message" class="form-control" />
							</div>
							</div>