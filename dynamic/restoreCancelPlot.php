<?php
ob_start();
session_start();
require("../includes/host.php");
require("../includes/kc_connection.php");
require("../includes/common-functions.php");

$return_str = '';
if (!isset($_POST['id']) || !is_numeric($_POST['id']) || !($_POST['id'] > 0)) {
    exit();
}

$id = $_POST['id'];
$batch = $_POST['batch'];


$customer_blocks_hist = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM kc_customer_blocks_hist WHERE id = '$id' and batch = '$batch'"));

// print_r($customer_blocks_hist);die;
// echo "<pre>"; print_r($customer_blocks_hist); die;

$restore = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM kc_refund_amount WHERE  customer_id = '" . $customer_blocks_hist['customer_id'] . "' AND block_id = '" . $customer_blocks_hist['block_id'] . "' AND block_number_id = '" . $customer_blocks_hist['block_number_id'] . "'and deleted IS NULL"));


if ($restore == 0) {
  
    $kc_customer_blocks = '';
  
    $query="insert into kc_customer_blocks (id, customer_id, block_id, block_number_id, rate_per_sqft, final_rate, customer_payment_type,
    number_of_installment, installment_amount, emi_payment_date, registry, registry_date, registry_by,
    khasra_no, maliyat_value, registry_by_user_id, registry_by_datetime, sale_value, sales_person_id, associate, associate_percentage, status, addedon, added_by) select kc_customer_blocks_id, customer_id, block_id, block_number_id, rate_per_sqft, final_rate, customer_payment_type,
    number_of_installment, installment_amount, emi_payment_date, registry, registry_date, registry_by, khasra_no, maliyat_value, registry_by_user_id, registry_by_datetime, sale_value, sales_person_id, associate, associate_percentage, status, addedon, added_by from kc_customer_blocks_hist where block_number_id = '". $customer_blocks_hist['block_number_id']."' and customer_id = '".$customer_blocks_hist['customer_id']."' and block_id = '". $customer_blocks_hist['block_id'] ."' and batch = '". $customer_blocks_hist['batch']."' and action_type = 'Cancel Booking'";


    if(! mysqli_query($conn, $query))
    {
    
        echo ("<br>Failed : " . mysqli_error($conn) ); 
        
        exit;   

    }
    else
    {

    $kc_customer_transactions = mysqli_query($conn, " insert into kc_customer_transactions (id, customer_id, block_id, block_number_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, next_due_date, late_for_transaction_id, is_affect_sold_amount, status, clear_remarks, clear_date,paid_account_no,  remarks, add_transaction_remarks, addedon, added_by) select kc_customer_transactions_id, customer_id, block_id, block_number_id, payment_type, bank_name, cheque_dd_number, amount, cr_dr, paid_date, next_due_date, late_for_transaction_id, is_affect_sold_amount, status, clear_remarks,clear_date, paid_account_no,  remarks, add_transaction_remarks, addedon,added_by from kc_customer_transactions_hist where block_number_id = '" . $customer_blocks_hist['block_number_id'] . "' and customer_id = '" . $customer_blocks_hist['customer_id'] . "' and block_id = '" . $customer_blocks_hist['block_id'] . "'  and batch = '" . $customer_blocks_hist['batch'] . "' and action_type = 'Cancel Booking'");

    $kc_receipt_numbers = mysqli_query($conn, " insert into kc_receipt_numbers (id, customer_id, block_id, block_number_id, transaction_id, receipt_id) select kc_receipt_numbers_id, customer_id, block_id, block_number_id, transaction_id, receipt_id from kc_receipt_numbers_hist where customer_id = '". $customer_blocks_hist['customer_id']."' and block_id = '". $customer_blocks_hist['block_id']."' and block_number_id = '". $customer_blocks_hist['block_number_id']. "'  and batch = '" . $customer_blocks_hist['batch'] . "' and action_type = 'Cancel Booking'");

    $kc_customer_emi = mysqli_query($conn, " insert into kc_customer_emi (id, customer_id, block_id, block_number_id, emi_amount, paid_amount, emi_date, paid_date, created) select  customer_emi_id, customer_id, block_id, block_number_id, emi_amount, paid_amount, emi_date, paid_date, created from kc_customer_emi_hist where  customer_id = '" . $customer_blocks_hist['customer_id'] . "' and block_id = '" . $customer_blocks_hist['block_id'] . "' and block_number_id = '" . $customer_blocks_hist['block_number_id'] . "'  and batch = '" . $customer_blocks_hist['batch'] . "' and action_type = 'Cancel Booking'");

    $kc_customer_plc = mysqli_query($conn, " insert into kc_customer_block_plc (id, customer_block_id, plc_id, name, plc_percentage, status, addedon) select  kc_customer_block_plc_id, customer_block_id, plc_id, name, plc_percentage, status,addedon from kc_customer_block_plc_hist where  customer_block_id = '" . $customer_blocks_hist['kc_customer_blocks_id'] . "' and batch = '" . $customer_blocks_hist['batch'] . "'");

    $kc_customer_plc_hist = mysqli_query($conn, "DELETE  FROM `kc_customer_block_plc_hist` WHERE customer_block_id = '" . $customer_blocks_hist['kc_customer_blocks_id'] . "'");

    $kc_receipt_numbers_hist = mysqli_query($conn, "DELETE  FROM `kc_receipt_numbers_hist` WHERE customer_id = '" . $customer_blocks_hist['customer_id'] . "' and block_id = '" . $customer_blocks_hist['block_id'] . "' and block_number_id = '" . $customer_blocks_hist['block_number_id'] . "'  and batch = '" . $customer_blocks_hist['batch'] . "'");

    $kc_customer_transactions_hist = mysqli_query($conn, "DELETE  FROM `kc_customer_transactions_hist` WHERE customer_id = '" . $customer_blocks_hist['customer_id'] . "' and block_id = '" . $customer_blocks_hist['block_id'] . "' and block_number_id = '" . $customer_blocks_hist['block_number_id'] . "'  and batch = '" . $customer_blocks_hist['batch'] . "'");

    $kc_customer_blocks_hist = mysqli_query($conn, "DELETE FROM `kc_customer_blocks_hist` WHERE customer_id = '" . $customer_blocks_hist['customer_id'] . "' and block_id = '" . $customer_blocks_hist['block_id'] . "' and block_number_id = '" . $customer_blocks_hist['block_number_id'] . "'  and batch = '" . $customer_blocks_hist['batch'] . "'");

    $kc_customer_emi_hist = mysqli_query($conn, "Delete FROM `kc_customer_emi_hist` WHERE customer_id = '" . $customer_blocks_hist['customer_id'] . "' and block_id = '" . $customer_blocks_hist['block_id'] . "' and block_number_id = '" . $customer_blocks_hist['block_number_id'] . "'  and batch = '" . $customer_blocks_hist['batch'] . "'");
    }

}
