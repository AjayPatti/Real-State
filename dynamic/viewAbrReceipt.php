<?php
ob_start();
session_start();

if(!isset($_POST['id']) || !is_numeric($_POST['id']) || !($_POST['id'] > 0)){
	exit();
}

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
$id = $_POST['id'];

$customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_avr_receipt where id = '".$id."' limit 0,1 "));
if(!isset($customer_details['id'])){
  die;
}
$parent_name_relation = '';
if($customer_details['parent_name_relation'] == "S"){
  $parent_name_relation = 'Son';
}else if($customer_details['parent_name_relation'] == "W"){
  $parent_name_relation = 'Wife';
}else if($customer_details['parent_name_relation'] == "D"){
  $parent_name_relation = 'Daughter';
}else if($customer_details['parent_name_relation'] == "C"){
  $parent_name_relation = 'Care';
}
?>
<table class="table table-border table-striped">
    <tr>
        <td>Name</td>
        <td><?php echo isset($customer_details['name_title'])?$customer_details['name_title']:''; ?> <?php echo isset($customer_details['name'])?$customer_details['name']:''; ?></td>
    </tr>
    <tr>
        <td><?php echo $parent_name_relation; ?> of</td>
        <td><?php echo isset($customer_details['parent_sub_title'])?$customer_details['parent_sub_title']:''; ?> <?php echo isset($customer_details['parent_name'])?$customer_details['parent_name']:''; ?></td>
    </tr>
    <tr>
        <td>Nationality</td>
        <td><?php echo isset($customer_details['nationality'])?$customer_details['nationality']:''; ?></td>
    </tr>
   
    <tr>
        <td>DOB</td>
        <td><?php echo isset($customer_details['dob'])?date("d-m-Y",strtotime($customer_details['dob'])):''; ?></td>
    </tr>
  
    <tr>
        <td>Mobile</td>
        <td><?php echo isset($customer_details['mobile'])?$customer_details['mobile']:''; ?></td>
    </tr>
    
    <tr>
        <td>Address</td>
        <td><?php echo isset($customer_details['address'])?$customer_details['address']:''; ?></td>
    </tr>
</table>