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

$farmer_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_farmers where id = '".$farmer_id."' limit 0,1 "));
if(!isset($farmer_details['id'])){
  die;
}
$parent_name_relation = '';
if($farmer_details['parent_name_relation'] == "S"){
  $parent_name_relation = 'Son';
}else if($farmer_details['parent_name_relation'] == "W"){
  $parent_name_relation = 'Wife';
}else if($farmer_details['parent_name_relation'] == "D"){
  $parent_name_relation = 'Daughter';
}else if($farmer_details['parent_name_relation'] == "C"){
  $parent_name_relation = 'Care';
}
?>
<table class="table table-border table-striped">
    <tr>
        <td>Name</td>
        <td><?php echo isset($farmer_details['name_title'])?$farmer_details['name_title']:''; ?> <?php echo isset($farmer_details['name'])?$farmer_details['name']:''; ?></td>
    </tr>
    <tr>
        <td><?php echo $parent_name_relation; ?> of</td>
        <td><?php echo isset($farmer_details['parent_name_sub_title'])?$farmer_details['parent_name_sub_title']:''; ?> <?php echo isset($farmer_details['parent_name'])?$farmer_details['parent_name']:''; ?></td>
    </tr>

    <tr>
        <td>Village</td>
        <td><?php echo isset($farmer_details['village'])?$farmer_details['village']:''; ?></td>
    </tr>
    <tr>
        <td>Mobile</td>
        <td><?php echo isset($farmer_details['mobile'])?$farmer_details['mobile']:''; ?></td>
    </tr>
    <tr>
        <td>Khasra No</td>
        <td><?php echo isset($farmer_details['khasra_no'])?$farmer_details['khasra_no']:''; ?></td>
    </tr>
    <tr>
        <td>Area in Hectare</td>
        <td><?php echo isset($farmer_details['area_in_hectare'])?$farmer_details['area_in_hectare']:''; ?></td>
    </tr>
    <tr>
        <td>Area in Sq.Ft</td>
        <td><?php echo isset($farmer_details['area_in_sqft'])?$farmer_details['area_in_sqft']:''; ?></td>
    </tr>
    <tr>
        <td>Area in Biswa</td>
        <td><?php echo isset($farmer_details['area_in_biswa'])?$farmer_details['area_in_biswa']:''; ?></td>
    </tr>
    <tr>
        <td>Total Plot Value</td>
        <td><?php echo isset($farmer_details['final_rate'])?$farmer_details['final_rate']:''; ?></td>
    </tr>

    <tr>
        <td>Register By</td>
        <td><?php echo isset($farmer_details['register_by'])?$farmer_details['register_by']:''; ?></td>
    </tr>

</table>

<?php /*
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
  } */?>
