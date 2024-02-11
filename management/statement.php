<?php
ob_start();
session_start();

require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");
require("../includes/checkAuth.php");

if(!isset($_GET['cb']) || !is_numeric($_GET['cb']) || !($_GET['cb'] > 0)){
    die;
}

$customer_blocks_id = $_GET['cb'];
$customer_block_details = mysqli_fetch_assoc(mysqli_query($conn,"select id, block_id, block_number_id, customer_id, final_rate from kc_customer_blocks where id = '".$customer_blocks_id."' and status = '1' limit 0,1 "));

if(!isset($customer_block_details['id'])){
    die;
}
$customer_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_customers where status = '1' and id = '".$customer_block_details['customer_id']."' limit 0,1 "));
$block_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_blocks where id = '".$customer_block_details['block_id']."' limit 0,1 "));
$block_number_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_block_numbers where id = '".$customer_block_details['block_number_id']."' limit 0,1 "));
$project_details = mysqli_fetch_assoc(mysqli_query($conn,"select * from kc_projects where id = '".$block_details['project_id']."' limit 0,1 "));
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>WCC Pvt. Ltd.</title>
<style type="text/css" media="print">
.hide{
    display:none;
}
</style>
<style type="text/css">
.button{
    background-color: #008CBA; border: none;color: white;padding: 15px 32px;text-align: center;text-decoration: none;font-size: 16px;cursor:pointer;
}
</style>
</head>

<body>
    <button onClick="window.print();" class="hide button">Print</button>
    <table width="92%" border="1" style="margin-left:auto; margin-right:auto; border-collapse: collapse;" cellpadding="4">
        <tbody>
            <td colspan="4">
              <div style="width:43%;float:left;text-align:right;">
                <img src="/<?php echo $host_name; ?>img/logo.png" style="width:50px; height:50px;" alt="" title="WCC Pvt. Ltd.">
              </div>
              <div style="width:57%;float:left;padding-top: 10px;">
                <strong style="font-size:24px;font-family:Cambria, 'Hoefler Text', 'Liberation Serif', Times, 'Times New Roman', serif;">WCC Pvt. Ltd.</strong>
              </div>
              <div style="clear:both;"></div>
            </td>
            <tr>
                <td colspan="4" align="center"><strong style="font-size:16px;font-family:Cambria, 'Hoefler Text', 'Liberation Serif', Times, 'Times New Roman', serif;">Statement of Account as on <?php echo date("d-M-Y"); ?></strong></td>
            </tr>

            <tr>
                <td width="10%">Ref No</td>
                <td width="50%"><?php echo date("YmdHi"); ?></td>
                <td width="15%">Property Name</td>
                <td width="25%"><?php echo $project_details['name']; ?></td>
            </tr>
            <tr>
                <td>Customer Name</td>
                <td><?php echo $customer_details['name_title'].' '.$customer_details['name']; ?></td>
                <td>Unit Code</td>
                <td><?php echo $block_details['name'];?>-<?php echo $block_number_details['block_number']; ?></td>
            </tr>
            <tr>
                <td>Customer No</td>
                <td><?php echo customerID($customer_details['id']); ?></td>
                <td>Payment Plan</td>
                <td>Installment</td>
            </tr>
            <?php
            $saleAmount = saleAmount($conn,$customer_details['id'],$customer_block_details['block_id'],$customer_block_details['block_number_id']);
            $saleAmountWithoutPLC = saleAmountWithoutPLC($conn,$customer_details['id'],$customer_block_details['block_id'],$customer_block_details['block_number_id']);
            $totalLateAmount = totalLateAmount($conn,$customer_details['id'],$customer_block_details['block_id'],$customer_block_details['block_number_id']);
            ?>
            <tr>
                <td>Address</td>
                <td><?php echo $customer_details['address']; ?></td>
                <td>Basic Selling Price(Rs.)</td>
                <td><?php echo number_format($saleAmountWithoutPLC,2); ?></td>
            </tr>
            <tr>
                <td>PAN</td>
                <td><?php echo $customer_details['pan_no']; ?></td>
                <td>PLC(Rs.)</td>
                <td><?php echo number_format(($saleAmount - $saleAmountWithoutPLC),2); ?></td>
            </tr>
            <tr>
                <td>Email</td>
                <td><?php echo $customer_details['email']; ?></td>
                <td>Other Charges</td>
                <td>0.00</td>
            </tr>
            <tr>
                <td>Phone</td>
                <td colspan="3"><?php echo $customer_details['mobile']; ?></td>
            </tr>
            <tr>
                <td>Area</td>
                <td><?php echo $block_number_details['area']; ?> Sq. Ft.</td>
                <td>Cost of Property (Rs.) (A)</td>
                <td><?php echo number_format($saleAmount,2); ?></td>
            </tr>
        </tbody>
    </table>
    <div style="min-height: 20px;"></div>
    <table width="92%" border="1" style="margin-left:auto; margin-right:auto; border-collapse: collapse;" cellpadding="4">
        <thead>
            <tr>
                <th colspan="6" align="center"><strong style="font-size:16px;font-family:Cambria, 'Hoefler Text', 'Liberation Serif', Times, 'Times New Roman', serif;">Details of Payment Received /Credited</strong></th>
            </tr>
            <tr>
                <th>Decription</th>
                <th>Date</th>
                <th>Receipt/CN/ DN* No.</th>
                <th>Amount(Rs.)</th>
                <th>Delayed Payment Charges Rs.</th>
                <th>Total Amout Received</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $transactions = mysqli_query($conn,"select * from kc_customer_transactions where customer_id = '".$customer_details['id']."' and block_id = '".$customer_block_details['block_id']."' and block_number_id = '".$customer_block_details['block_number_id']."' and status = '1' and ((cr_dr = 'cr' and remarks != '') or cr_dr = 'dr') ");
            $amount = $delayed = $received = 0;
            $counter = 1;
            while($transaction = mysqli_fetch_assoc($transactions)){ ?>
                <tr>
                    <td>
                        <?php 
                        if($counter == 1){   //$transaction['cr_dr'] == "cr" && $transaction['remarks'] == ""
                            echo "Booking Amount";
                        }else if($transaction['cr_dr'] == "cr"){
                            echo "Delayed Payment Charges";
                        }else{
                            echo "Instalment Receipt";
                        } ?>
                    </td>
                    <td>
                        <?php
                        if($transaction['cr_dr'] == "cr"){
                            echo date("d-m-Y",strtotime($transaction['addedon']));
                        }else{
                            echo date("d-m-Y",strtotime($transaction['paid_date']));
                        } ?>     
                    </td>
                    <td>
                        <?php
                        if($transaction['cr_dr'] == "cr"){
                            echo "NA";
                        }else{
                            echo receiptNumber($conn,$transaction['id']);
                            if($transaction['cr_dr'] == "dr" && $transaction['payment_type'] != 'Cash'){
                                echo '/'.$transaction['payment_type'].'/'.$transaction['cheque_dd_number'];
                            }

                            /*if($transaction['cr_dr'] == "dr"){
                                echo '/'.$transaction['payment_type'];
                                if($transaction['payment_type'] != 'Cash'){
                                    echo '/'.$transaction['cheque_dd_number'];
                                }
                            }*/
                        } ?>  
                    </td>
                    <td align="right">
                        <?php
                        if($transaction['cr_dr'] == "cr" && $transaction['remarks'] == ""){
                            $amount += $transaction['amount'];
                            echo number_format($transaction['amount'],2);
                        }else{
                            echo "-";
                        }
                        ?>
                    </td>
                    <td align="right">
                        <?php
                        if($transaction['cr_dr'] == "cr" && $transaction['remarks'] != ""){
                            $delayed += $transaction['amount'];
                            echo number_format($transaction['amount'],2);
                        }else{
                            echo "-";
                        }
                        ?>
                    </td>
                    <td align="right">
                        <?php
                        if($transaction['cr_dr'] != "cr"){
                            $received += $transaction['amount'];
                            echo number_format($transaction['amount'],2);
                        }else{
                            echo "-";
                        }
                        ?>
                    </td>
                </tr>
            <?php
                $counter++;
            } ?>

            <tr>
                <td colspan="3" align="right"><strong>Total(B)</strong></td>
                <td align="right"><strong><?php echo number_format($amount,2); ?></strong></td>
                <td align="right"><strong><?php echo number_format($delayed,2); ?></strong></td>
                <td align="right"><strong><?php echo number_format($received,2); ?></strong></td>
            </tr>
            <tr>
                <td colspan="5" align="right">Balance Due/ Overdue</td>
                <td align="right"><?php echo number_format($saleAmount - $received,2); ?></td>
            </tr>
            <tr>
                <td colspan="5" align="right">Delayed Payment charges</td>
                <td align="right"><?php echo number_format($delayed,2); ?></td>
            </tr>
            <tr>
                <td colspan="5" align="right">NET AMOUNT PAYABLE</td>
                <td align="right"><strong><?php echo number_format(($saleAmount + $delayed) - $received,2); ?></strong></td>
            </tr>

        </tbody>
    </table>
    <div style="margin-left: 50px;">
        <p>*this is Systm generated statement kindly confirm Account department for actual dues.</p>
    </div>
</body>
</html>