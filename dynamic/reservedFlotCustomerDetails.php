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

$customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_customers where id = '".$customer_id."' limit 0,1 "));
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
        <td><?php echo isset($customer_details['parent_name_sub_title'])?$customer_details['parent_name_sub_title']:''; ?> <?php echo isset($customer_details['parent_name'])?$customer_details['parent_name']:''; ?></td>
    </tr>
    <tr>
        <td>Nationality</td>
        <td><?php echo isset($customer_details['nationality'])?$customer_details['nationality']:''; ?></td>
    </tr>
    <tr>
        <td>Profession</td>
        <td><?php echo isset($customer_details['profession'])?$customer_details['profession']:''; ?></td>
    </tr>
    <tr>
        <td>DOB</td>
        <td><?php echo isset($customer_details['dob'])?date("d-m-Y",strtotime($customer_details['dob'])):''; ?></td>
    </tr>
    <tr>
        <td>Nominee Name</td>
        <td><?php echo isset($customer_details['nominee_name'])?$customer_details['nominee_name']:''; ?></td>
    </tr>
    <tr>
        <td>Profession</td>
        <td><?php echo isset($customer_details['profession'])?$customer_details['profession']:''; ?></td>
    </tr>
    <tr>
        <td>Residential Status</td>
        <td><?php echo isset($customer_details['residentail_status'])?$customer_details['residentail_status']:''; ?></td>
    </tr>
    <tr>
        <td>PAN No./Aadhar No.</td>
        <td><?php echo isset($customer_details['pan_no'])?$customer_details['pan_no']:''; ?></td>
    </tr>
    <tr>
        <td>Address</td>
        <td><?php echo isset($customer_details['address'])?$customer_details['address']:''; ?></td>
    </tr>
    <tr>
        <td>Email</td>
        <td><?php echo isset($customer_details['email'])?$customer_details['email']:''; ?></td>
    </tr>
    <tr>
        <td>Residence/Office</td>
        <td><?php echo isset($customer_details['office_address'])?$customer_details['office_address']:''; ?></td>
    </tr>
</table>

<?php
  $blocks = mysqli_query($conn,"select id, block_id, block_number_id, registry, sales_person_id, associate, associate_percentage from kc_customer_blocks where customer_id = '".$customer_details['id']."' and status = '1' ");
  $block_names = array();
  while($block = mysqli_fetch_assoc($blocks)){
    $block_details = mysqli_fetch_assoc(mysqli_query($conn,"select name from kc_blocks where id = '".$block['block_id']."' limit 0,1 "));
    $block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select block_number from kc_block_numbers where id = '".$block['block_number_id']."' limit 0,1 "));
    $sales_person_detials = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_employees where id = '".$block['sales_person_id']."' and status = '1' limit 0,1 "));
    $associate_detials = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_associates where id = '".$block['associate']."' and status = '1' limit 0,1 "));
    
    $total_credited = totalCredited($conn,$customer_details['id'],$block['block_id'],$block['block_number_id']);
    $total_debited = totalDebited($conn,$customer_details['id'],$block['block_id'],$block['block_number_id']);
    $one_fourth = ($total_credited*25)/100;
    
    if($block['registry'] == "yes"){
      $registry_status = '<strong class="text-success">Registry Done</strong>';
    }else{
      $registry_status = '<strong class="text-danger">Registry Not Done</strong>';
    }
    ?>
    <div class="well">
    <?php
      echo '<h5 class="textt-danger">'.blockProjectName($conn,$block['block_id']).'<br>'.$block_details['name'].'('.$block_number_details['block_number'].')'."</h5>";//."($registry_status)"
      
      echo 'Total Credited: '.number_format($total_credited,2).'<br>';
      echo 'Total Debited: '.number_format($total_debited,2).'<br>';
      echo '<strong class="text-danger">Pending: '.number_format(($total_credited - $total_debited),2).'</strong><br>';
      
      if(isOutStandingPayment($conn,$customer_details['id'],$block['block_id'],$block['block_number_id'])){
        echo '<strong class="text-danger">Next Due: '.formatDate(nextDueDate($conn,$customer_details['id'],$block['block_id'],$block['block_number_id'])).'</strong><br><br>';
      }
      ?>
      <?php if($block['sales_person_id']>0){ ?>
      <strong>Sales Person Details</strong><br>
      Name: <strong><?php echo $sales_person_detials['name']; ?></strong><br>
      Mobile: <strong><?php echo $sales_person_detials['mobile_no']; ?></strong><br><br>
      <?php } if($block['associate']>0){ ?>
        <strong>Associate Details</strong><br>
        Name: <strong><?php echo $associate_detials['name']; ?></strong><br>
        Mobile: <strong><?php echo $associate_detials['mobile_no']; ?></strong><br>
        Percentage: <strong><?php echo $block['associate_percentage']; ?>%</strong>
      <?php } ?>
    </div>
    <?php
  } ?>