<?php
ob_start();
session_start();


require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");

if (isset($_POST['dd_number'])){
	$cheque_dd_number= $_POST['dd_number'];

    $result=mysqli_query($conn,"SELECT   ct.id, ct.customer_id as customer_id,  ct.cheque_dd_number, ct.amount, ct.payment_type, ct.block_id, ct.block_number_id, b.project_id, b.name as block_name, bn.block_number as block_number_name,  p.name as project_name, c.name_title as customer_name_title, c.name as customer_name,c.parent_name as customer_parent_name,c.parent_name_relation as customer_parent_name_relation, ct.paid_date as paid_date, c.mobile as customer_mobile  from kc_customer_transactions as ct   LEFT JOIN kc_blocks b ON ct.block_id = b.id LEFT JOIN kc_block_numbers bn ON ct.block_number_id = bn.id LEFT JOIN kc_projects p ON b.project_id = p.id LEFT JOIN kc_customers c ON ct.customer_id = c.id  WHERE ct.status= '1' AND ct.cheque_dd_number= '$cheque_dd_number' ") ;
   

    // join kc_customers as kc  on ct.customer_id = kc.id
}
$counter= 1;
?>
<tbody>
    <tr>
        <th>Sr.</th>
        <th>Customer Details</th>
        <th>Plot Details</th>
        <th>Payment Details</th>
    </tr>
   
        <?php while($row= mysqli_fetch_assoc($result)) {?>
    <tr>
        <td ><strong><?php echo $counter ?></strong></td>
        <td nowrap="nowrap">  
                              <strong> <?php echo $row['customer_name_title']; ?> <?php echo $row['customer_name']; ?></strong>
                              (<strong> <?php echo $row['customer_id']; ?></strong>)<br> 
                              <strong>( <?php echo $row['customer_parent_name_relation']; ?> )</strong> of <strong> <?php echo $row['customer_parent_name']; ?> </strong><br> 
                               
                            <strong><?php echo $row['customer_mobile']; ?></strong></td><br>
        
        <td nowrap="nowrap" ><strong> <?php echo $row['project_name']; ?><br>
											 <?php echo $row['block_name']; ?>
											(<?php echo $row['block_number_name']; ?>)<br>
												
         

        <td nowrap="nowrap">Type:<strong><?php echo $row['payment_type']; ?></strong><br>
                            Amount:<strong><?php echo $row['amount']; ?></strong><br>
                            Cheque No:<strong><?php echo $row['cheque_dd_number']; ?></strong><br>
                            Date:<strong><?php echo date('d-m-Y',strtotime($row['paid_date'])); ?></strong><br></td>
      
    </tr>

  <?php $counter++; }   ?>
</tbody>
