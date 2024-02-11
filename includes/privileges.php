	<?php 
	$privileges = [
	'visit_forms' => ['name' => 'Visit Forms','Privileges' => ['view']],
	'send_message' => ['name' => 'Send Message','Privileges' => ['manage']],
	'associate' => ['name' => 'Associate', 'Privileges' => ['manage', 'add', 'edit', 'search', 'status']],
	'mutation' => ['name' => 'Mutation', 'Privileges' => ['manage']],
	'Associate_tree_view' => ['name' => 'Associate Tree View', 'Privileges' => ['manage']],
	'Associate_ledger' => ['name' => 'Associate Ledger', 'Privileges' => ['manage']],
	'customer' => ['name' => 'Customer', 'Privileges' => ['manage', 'add', 'add_block', 'view', 'edit_customer', 'add_transactions', 'view_transactions', 'print_allotment', 'change_associate', 'change_plot_number', 'registry', 'add_revised_rate', 'apply_discount']],

	'customer_view_transactions' =>['name' => 'Customer View Transactions', 'Privileges' => ['manage','view_transactions_print', 'view_transactions_cancel']],

	'emi_payment' =>['name' => 'Emi Payment', 'Privileges' => ['manage','print', 'edit_emi']], 

	'projects' => ['name' => 'Projects', 'Privileges' => ['manage', 'add', 'edit', 'p_status', 'manage_block', 'add_block', 'edit_block', 'block_status', 'manage_plot_number', 'add_plot_number', 'view', 'cancel_booking', 'plot_status']],

	'plc' => ['name' => 'PLC', 'Privileges' => ['manage', 'add', 'status']],
	'employees' => ['name' =>'Employees', 'Privileges' => ['manage', 'add', 'status']],
	'users' => ['name' => 'Users', 'Privileges' => ['manage', 'add_user', 'status']],		
	'contacts' => ['name' => 'Contact', 'Privileges' => ['manage', 'add', 'edit', 'status']],

	'unreserved_plot_number' => ['name' => 'Unreserved Plot Number', 'Privileges' => ['manage']],
	'due_payment' => ['name' => 'Due Payment', 'Privileges' => ['manage']],
	'day_book' => ['name' => 'Day Book', 'Privileges' => ['manage']],
	'ledger' => ['name' => 'Ledger', 'Privileges' => ['manage']],
	'registry' => ['name' => 'Registry', 'Privileges' => ['manage']],
	'pending_emi' => ['name' => 'Pending Emi', 'Privileges' => ['manage']],


	'cheque_prints' => ['name' => 'Cheque Prints', 'Privileges' => ['manage', 'add', 'edit', 'print', 'status']],
	'cheque_report' => ['name' => 'Cheque Report', 'Privileges' => ['manage', 'cheque_cancel', 'cheque_clear']],
	'cheque_cancel_report' => ['name' => 'Cheque Cancel Report', 'Privileges' => ['manage']],
	'cheque_clear_report' => ['name' => 'Cheque Clear Report', 'Privileges' => ['manage']],

	'neft_report' => ['name' => 'Neft Report', 'Privileges' => ['manage', 'neft_cancel', 'neft_clear']],
	'neft_cancel_report' => ['name' => 'neft Cancel Report', 'Privileges' => ['manage']],
	'neft_clear_report' => ['name' => 'neft Clear Report', 'Privileges' => ['manage']],

	'avr_receipt' => ['name' => 'AVR Receipt', 'Privileges' => ['manage', 'add', 'view', 'edit', 'delete', 	'view_transaction', 'print', 'cancel_receipt']],
	'reports' => ['name' => 'Reports', 'Privileges' => ['manage']],
	'cancel_plot_hist' => ['name' => 'Cancel Plot History', 'Privileges' => ['manage','view_transaction','add_refund','view_refund']],

	'today_transaction' => ['name' => 'Current Transactions' , 'Privileges' => ['manage']],
	'account_history' => ['name' => 'Account History' , 'Privileges' => ['manage']],

	'employee_login_logs' => ['name' => 'Employee login logs', 'Privileges' => ['manage']],
	'message_report' => ['name' => 'Send Message Reports', 'Privileges' => ['manage']],
	'reminder' => ['name' => 'Reminder' , 'Privileges' => ['manage','customer_follow_ups','view_customer_follow_ups']] ,
	'due_amount' => ['name' => 'Due Amount' , 'Privileges' => ['manage','customer_follow_ups','view_customer_follow_ups']] ,
	'purchase' => ['name' => 'Purchase', 'Privileges' => ['manage']],
	'expenses' => ['name' => 'expenses', 'Privileges' => ['expenses','expenses_type']],
	





	
];

// purchase  Added on 22/07/2022
?>


