 <?php
 
if(!isset($_SESSION['login_id']) || !isset($_SESSION['login_type']) || ($_SESSION['login_type'] != "admin" && $_SESSION['login_type'] != "super_admin" && $_SESSION['login_type']!= "super2admin" )){
	header("Location:../index.php");
	exit();
}

$adminPages = array("dashboard.php","send_message.php","associates.php","customers.php","projects.php","blocks.php","block_numbers.php","plc.php","users.php","contacts.php","employees.php","block_number_status.php","due_payments.php","collection.php","ledger.php","registries.php","pending_emi.php","cheques.php","avr_receipt.php","report.php","login_logs.php","message_report.php","renewal.php","cheque_cancel_report.php","cheque_clear_report.php","cheque_report.php","cancel_plot_hist.php","excel_report_unreserveredPlot.php","day_book_excel_export.php","ledger_excel_export.php","registries_excel_export.php","cheque_print.php","change_password.php","receipt.php","receipt2.php","today_transaction.php","allotment_letter.php","receipt_abr_receipt.php","report_excel_export.php","applyBounceCharges.php","current_transaction_excel_export.php","viewcustomerfollowups.php","reminder.php","telemarketing.php","todayreport.php","todayreport_excel_export.php","cancel_plot_hist_excel_export.php",'purchase_info.php','purchase_ledger.php','visit_forms.php','avr_excel_export.php','cheque_clear_report_excel.php','visit_form_excel_export.php','cheque_report_excel.php','cheque_cancel_report_excel.php','farmer_excel_export.php');
//  var_dump(isset($_POST[$adminPages]));die();
$page = basename($_SERVER['PHP_SELF']);
// echo $_SESSION['login_type']; die;
if($_SESSION['login_type'] == "admin" && !in_array($page,$adminPages)){
	$_SESSION['error'] = "Not Authorized.";
	header("Location:../index.php");
	exit();
}
?>
