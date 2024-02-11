<?php
$current_page_arr = explode("/", $_SERVER['PHP_SELF']);
$current_page = end($current_page_arr);
?>
<style type="text/css">
	.modal-backdrop.in {
		z-index: 0 !important;
	}
</style>
<section class="sidebar">
	<!-- Sidebar user panel -->
	<?php 
  
	?>
	<div class="user-panel">
		<div class="pull-left image">
			<img src="/<?php echo $host_name; ?>img/logo.png" class="img-circle" alt="User Image" />
		</div>
		<div class="pull-left info">
			<p>WCC <a href=""><i class="fa fa-circle text-success"></i> Online</a></p>
			
		</div>
	</div>
	<!-- search form -->
	<?php /*
	  <form action="#" method="get" class="sidebar-form">
		<div class="input-group">
		  <input type="text" name="q" class="form-control" placeholder="Search..." />
		  <span class="input-group-btn">
			<button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i></button>
		  </span>
		</div>
	  </form>
	  */ ?>
	<!-- /.search form -->
	<!-- sidebar menu: : style can be found in sidebar.less -->
	<ul class="sidebar-menu">
		<li class="header">MAIN NAVIGATION</li>

		<li class="<?php if ($current_page == "dashboard.php") {
						echo "active";
					} ?>"><a href="/<?php echo $host_name; ?>management/dashboard.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>

		<?php if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_send_message')) { ?>
			<li class="<?php if ($current_page == "send_message.php") {
							echo "active";
						} ?>"><a href="/<?php echo $host_name; ?>management/send_message.php"><i class="fa fa-envelope"></i> <span>Send Message</span></a></li>
		<?php } ?>

		<?php if ( ($_SESSION['login_type'] == 'super2admin')) { ?>
			<li class="<?php if ($current_page == "festival_messages.php") {
							echo "active";
						} ?>"><a href="/<?php echo $host_name; ?>management/festival_messages.php"><i class="fa fa-envelope"></i> <span>Festival Message</span></a></li>
		<?php } ?>

		<?php if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'view_visit_forms')) { ?>
			<li class="<?php if ($current_page == "visit_forms.php") {
							echo "active";
						} ?>"><a href="/<?php echo $host_name; ?>management/visit_forms.php"><i class="fa fa-male"></i> <span>Visit Forms</span></a></li>
		<?php } ?>
		<?php if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'Mutation')) { ?>
			<li class="<?php if ($current_page == "mutation.php") {
							echo "active";
						} ?>"><a href="/<?php echo $host_name; ?>management/mutation.php"><i class="fa fa-male"></i> <span>Mutation</span></a></li>
		<?php } ?>																				
		<?php if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'associate_tree_view')) { ?>
			<li class="<?php if ($current_page == "associate_tree_view.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/associate_tree_view.php"><i class="fa fa-money"></i>Asscoiate Tree View</a></li>
		<?php } ?>																				
		
		<?php if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'associate_ledger')) { ?>
			<li class="<?php if ($current_page == "associate_ledger.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/associate_ledger.php"><i class="fa fa-money"></i>Asscoiate Ladger</a></li>
		<?php } ?>																				
		<?php if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'possession_transfer')) { ?>
			<li class="<?php if ($current_page == "possession_transfer.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/possession_transfer.php"><i class="fa fa-money"></i>Possession Transfer</a></li>
		<?php } ?>																				
		<?php
		$master_pages = array('associates.php', 'customers.php', 'projects.php', 'blocks.php', 'block_numbers.php', 'plc.php', 'employees.php', 'users.php', 'contacts.php', 'accounts.php', 'today_transaction.php','account_history.php');
		?>
		<li class="treeview <?php if (in_array($current_page, $master_pages)) {
								echo 'active';
							} ?>">
			<a href="/<?php echo $host_name; ?>management/javascript:void(0);">

				<i class="fa fa-gear"></i> <span>Accounts Information</span> <i class="fa fa-angle-left pull-right"></i>

			</a>
			<ul class="treeview-menu">
				<?php if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_associate')) { ?>
					<li class="<?php if ($current_page == "associates.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/associates.php"><i class="fa fa-tree" aria-hidden="true"></i> Associates</a></li>
				<?php } ?>

				<?php if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_customer')) { ?>
					<li class="<?php if ($current_page == "customers.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/customers.php"><i class="fa fa-users"></i> Customers</a></li>
				<?php } ?>

				<?php if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_projects')) { ?>
					<li class="<?php if ($current_page == "projects.php" || $current_page == "blocks.php" || $current_page == "block_numbers.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/projects.php"><i class="fa fa-bank"></i> Projects</a></li>
				<?php } ?>

				<?php /*?><li class="<?php if($current_page == "relations.php"){ echo "active";  } ?>"><a href="/<?php echo $host_name; ?>management/relations.php"><i class="fa fa-street-view"></i> Relations</a></li><?php */ ?>

				<?php if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_plc')) { ?>
					<li class="<?php if ($current_page == "plc.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/plc.php"><i class="fa fa-money"></i> PLC</a></li>
				<?php } ?>

				<?php if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_employees')) { ?>
					<li class="<?php if ($current_page == "employees.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/employees.php"><i class="fa fa-user"></i> Employees</a></li>
				<?php } ?>

				<?php if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_users')) { ?>
					<li class="<?php if ($current_page == "users.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/users.php"><i class="fa fa-user-secret"></i> Users</a></li>
				<?php } ?>

				<?php if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_contacts')) { ?>
					<li class="<?php if ($current_page == "contacts.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/contacts.php"><i class="fa fa-bookmark"></i> Contacts</a></li>
				<?php }
				if (userCan($conn, $_SESSION['login_id'], $privilegeName = '')) { ?>
					<li class="<?php if ($current_page == "accounts.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/accounts.php"><i class="fa fa-exchange"></i> Accounts</a></li>
				<?php }
				if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_today_transaction')) { ?>
					<li class="<?php if ($current_page == "today_transaction.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/today_transaction.php"><i class="fa fa-inr"></i> <span>Current Transaction</span></a></li>
				<?php } 
				 if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_account_history')) { ?>
					<li class="<?php if ($current_page == "account_history.php"){
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/account_history.php"><i class="fa fa-inr"></i> <span>Accounts Histroy</span></a></li>
				<?php } ?>
			</ul>
		</li>


		<?php
		$reports = array('block_number_status.php', 'due_payments.php', 'collection.php', 'ledger.php', 'registries.php', 'pending_emi.php');

		?>
		<li class="treeview <?php if (in_array($current_page, $reports)) {
								echo 'active';
							} ?>">
			<a href="/<?php echo $host_name; ?>management/javascript:;">

				<i class="fa fa-file"></i> <span>Display</span> <i class="fa fa-angle-left pull-right"></i>

			</a>
			<ul class="treeview-menu">
				<?php if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_unreserved_plot_number')) { ?>
					<li class="<?php if ($current_page == "block_number_status.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/block_number_status.php"><i class="fa fa-unlock"></i> Unreserved Plot Numbers</a></li>
				<?php }
				if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_due_payment')) { ?>
					<li class="<?php if ($current_page == "due_payments.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/due_payments.php"><i class="fa fa-money"></i> Due Payments</a></li>
				<?php }
				if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_day_book')) { ?>
					<li class="<?php if ($current_page == "collection.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/collection.php"><i class="fa fa-rupee"></i> Day Book</a></li>
				<?php }
				if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_ledger')) { ?>
					<li class="<?php if ($current_page == "ledger.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/ledger.php"><i class="fa fa-book"></i> Ledger</a></li>
				<?php }
				if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_registry')) { ?>
					<li class="<?php if ($current_page == "registries.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/registries.php"><i class="fa fa-map-marker"></i> Registry</a></li>
				<?php }
				if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_pending_emi')) { ?>
					<li class="<?php if ($current_page == "pending_emi.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/pending_emi.php"><i class="fa fa-dollar"></i> Pending EMI</a></li>
				<?php } ?>
			</ul>
		</li>
		<?php
		$reports = array('expenses.php','expenses_type.php');

		?>
		<li class="treeview <?php if (in_array($current_page, $reports)) {
								echo 'active';
							} ?>">
			<a href="/<?php echo $host_name; ?>management/javascript:;">

				<i class="fa fa-file"></i> <span>Expenses</span> <i class="fa fa-angle-left pull-right"></i>

			</a>
			<ul class="treeview-menu">
				<?php if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'expenses')) { ?>
					<li class="<?php if ($current_page == "expenses.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/expenses.php"><i class="fa fa-unlock"></i>Expenese</a></li>
				<?php }
				if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'expenses_type')) { ?>
					<li class="<?php if ($current_page == "expenses_type.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/expenses_type.php"><i class="fa fa-money"></i>Expenese Type</a></li>
				<?php }?>
				<?php if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'expenses_type')) { ?>
					
				<?php } ?>
				
			</ul>
		</li>						

		<?php
		$cheque = array('cheques.php', 'cheque_report.php', 'cheque_cancel_report.php', 'cheque_clear_report.php');
		?>
		<li class="treeview <?php if (in_array($current_page, $cheque)) {
								echo 'active';
							} ?>">
			<a href="/<?php echo $host_name; ?>management/javascript:;">
				<i class="fa fa-check-circle"></i> <span>Cheques</span> <i class="fa fa-angle-left pull-right"></i>
			</a>
			<ul class="treeview-menu">
				<?php if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_cheque_prints')) { ?>
					<li class="<?php if ($current_page == "cheques.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/cheques.php"><i class="fa fa-money"></i> <span>Cheque Prints</span></a></li>
				<?php }
				if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_cheque_report')) { ?>
					<li class="<?php if ($current_page == "cheque_report.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/cheque_report.php"><i class="fa fa-user-secret"></i> <span>Cheque Reconciliation</span></a></li>
				<?php }
				if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_cheque_cancel_report')) { ?>
					<li class="<?php if ($current_page == "cheque_cancel_report.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/cheque_cancel_report.php"><i class="fa fa-life-ring"></i> <span>Cancel Cheques</span></a></li>
				<?php }
				if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_cheque_clear_report')) { ?>
					<li class="<?php if ($current_page == "cheque_clear_report.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/cheque_clear_report.php"><i class="fa fa-exchange"></i> <span>Clear Cheques</span></a></li>
				<?php } ?>
			</ul>
		</li>

		<!--NEFT -->
		<?php
		$neft = array( 'neft_report.php', 'neft_cancel_report.php', 'neft_clear_report.php');
		?>
		<li class="treeview <?php if (in_array($current_page, $neft)) {
								echo 'active';
							} ?>">
			<a href="/<?php echo $host_name; ?>management/javascript:;">
				<i class="fa fa-check-circle"></i> <span>NEFT</span> <i class="fa fa-angle-left pull-right"></i>
			</a>
			<ul class="treeview-menu">
				
				<?php if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_neft_report')) { ?>
					<li class="<?php if ($current_page == "neft_report.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/neft_report.php"><i class="fa fa-user-secret"></i> <span>Neft Reconciliation</span></a></li>
				<?php }
				if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_neft_cancel_report')) { ?>
					<li class="<?php if ($current_page == "neft_cancel_report.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/neft_cancel_report.php"><i class="fa fa-life-ring"></i> <span>Cancel Neft</span></a></li>
				<?php }
				if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_neft_clear_report')) { ?>
					<li class="<?php if ($current_page == "neft_clear_report.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/neft_clear_report.php"><i class="fa fa-exchange"></i> <span>Clear Neft</span></a></li>
				<?php } ?>
			</ul>
		</li> 
		<!-- Here -->

		<?php
		$purchase_page = array('purchase_info.php', 'purchase_ledger.php');
		?>


		<?php
		if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_purchase')) {
		?>

			<li class="treeview <?php if (in_array($current_page, $purchase_page)) {
									echo 'active';
								} ?>">
				<a href="/<?php echo $host_name; ?>management/javascript:;">

					<i class="fa fa-plus-square-o"></i> <span> Purchase</span> <i class="fa fa-angle-left pull-right"></i>

				</a>
				<ul class="treeview-menu">


					<li class="<?php if ($current_page == "purchase_info.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/purchase_info.php"><i class="fa fa-tree"></i> <span>Purchase Information</span></a></li>



					<li class="<?php if ($current_page == "purchase_ledger.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/purchase_ledger.php"><i class="fa fa-user"></i> <span>Ledger</span></a></li>



				</ul>
			</li>
		<?php } ?>

		<!-- Ended -->
		<?php
		if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_avr_receipt')) { ?>
			<li class="<?php if ($current_page == "avr_receipt.php") {
							echo "active";
						} ?>"><a href="/<?php echo $host_name; ?>management/avr_receipt.php"><i class="fa fa-money"></i> <span>AVR Receipt</span></a></li>
		<?php }
		if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_reports')) { ?>
			<li class="<?php if ($current_page == "report.php") {
							echo "active";
						} ?>"><a href="/<?php echo $host_name; ?>management/report.php"><i class="fa fa-bar-chart"></i> <span>Report</span></a></li>

		<?php }
		if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_cancel_plot_hist')) { ?>
			<li class="<?php if ($current_page == "cancel_plot_hist.php") {
							echo "active";
						} ?>"><a href="/<?php echo $host_name; ?>management/cancel_plot_hist.php"><i class="fa fa-history"></i> <span>Cancel Plots Reports</span></a></li>

		<?php }
		if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_refund_plot_list')) { ?>
			<li class="<?php if ($current_page == "refund_plot_list.php") {
							echo "active";
						} ?>"><a href="/<?php echo $host_name; ?>management/refund_plot_list.php"><i class="fa fa-bar-chart"></i> <span>Refund Plots Reports</span></a></li>
		<?php } ?>
		<?php
		$telemarketing_page = array('telemarketing.php', 'reminder.php', 'todayreport.php');
		?>

		<li class="treeview <?php if (in_array($current_page, $telemarketing_page)) {
								echo 'active';
							} ?>">
			<a href="/<?php echo $host_name; ?>management/javascript:;">

				<i class="fa fa-phone"></i> <span>Telemarketing</span> <i class="fa fa-angle-left pull-right"></i>

			</a>
			<ul class="treeview-menu">
				<?php
				$cur_date = date('Y-m-d');
				$start_date  = date('Y-m-d 00-00-01');
				$end_date	  = date('Y-m-d 23-59-59');
				$next_date = date('Y-m-d', strtotime('+7 days'));
				$total_records = mysqli_fetch_assoc(mysqli_query($conn, "SELECT count(*) as total from kc_customer_follow_ups WHERE next_follow_up_date = '" . $cur_date . "' "));
				?>
				<?php if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_reminder')) { ?>
					<li class="<?php if ($current_page == "reminder.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/reminder.php"><i class="fa fa-clock-o"></i> Reminder<sup class="badge label-danger "><?= $total_records['total'] ?></sup></a></li>
				<?php } ?>

				<?php if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_due_amount')) { ?>
					<li class="<?php if ($current_page == "telemarketing.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/telemarketing.php"><i class="fa fa-rupee" aria-hidden="true"></i> Due Amount</a></li>
				<?php }  ?>

				<?php if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_due_amount')) { ?>
					<li class="<?php if ($current_page == "todayreport.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/todayreport.php"><i class="fa fa-rupee" aria-hidden="true"></i> Today Report</a></li>
				<?php }  ?>
			</ul>
		</li>
		<li class="<?php if ($current_page == "asscoiate_customer_link_wcc.php") {
						echo "active";
					} ?>"><a href="/<?php echo $host_name; ?>management/asscoiate_customer_link_wcc.php"><i class="fa fa-dashboard"></i> <span>Accoiate And Customer Link</span></a></li>

		<?php
		if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_employee_login_logs')) { ?>
			<!-- 	<li class="<?php if ($current_page == "telemarketing.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/telemarketing.php"><i class="fa fa-phone"></i> <span>Telemarketing</span></a></li>	 -->

		<?php }
		if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_employee_login_logs')) { ?>
			<li class="<?php if ($current_page == "login_logs.php") {
							echo "active";
						} ?>"><a href="/<?php echo $host_name; ?>management/login_logs.php"><i class="fa fa-street-view"></i> <span>Employee Login Logs</span></a></li>
		<?php }
		if (userCan($conn, $_SESSION['login_id'], $privilegeName = 'manage_message_report')) { ?>
			<li class="<?php if ($current_page == "message_report.php") {
							echo "active";
						} ?>"><a href="/<?php echo $host_name; ?>management/message_report.php"><i class="fa fa-envelope"></i> <span>Sent Messages</span></a></li>
		<?php }
		$reports = array('remove.php', 'restore.php', 'backup.php');
		?>
		<li class="treeview <?php if (in_array($current_page, $reports)) {
								echo 'active';
							} ?>">
			<a href="/<?php echo $host_name; ?>management/javascript:;">
				<i class="fa fa-database"></i> <span>Databases</span> <i class="fa fa-angle-left pull-right"></i>
			</a>
			<ul class="treeview-menu">
				<?php if (($_SESSION['login_type'] == 'super_admin') || ($_SESSION['login_type'] == 'super2admin')) { ?>
					<li class="<?php if ($current_page == "remove.php") {
									echo "active";
								} ?>">
						<a id="remove-btn">
							<i class="fa fa-times"></i> <span>Remove</span></a>
						<button type="button" data-toggle="modal" data-target="#remove" style="display:none;" id="modal-toggle">
						</button>
					</li>
				<?php }
				if (($_SESSION['login_type'] == 'super_admin') || ($_SESSION['login_type'] == 'super2admin')) { ?>
					<li class="<?php if ($current_page == "restore.php") {
									echo "active";
								} ?>">
						<a id="restore-btn">
							<i class="fa fa-retweet"></i> <span>Restore</span></a>
						<button type="button" data-toggle="modal" data-target="#restore" style="display:none;" id="restore-modal-toggle">
						</button>
					</li>
				<?php }
				if (($_SESSION['login_type'] == 'super_admin') || ($_SESSION['login_type'] == 'super2admin')) { ?>
					<li class="<?php if ($current_page == "backup.php") {
									echo "active";
								} ?>"><a href="/<?php echo $host_name; ?>management/backup.php"><i class="fa fa-undo"></i> <span>Backup</span></a></li>
				<?php } ?>
			</ul>
		</li>
	
	</ul>
</section>
<div class="modal" id="remove">
	<div class="modal-dialog modal-md">
		<div class="modal-content">

			<!-- Modal Header -->
			<div class="modal-header">
				<h4 class="modal-title">Remove Database</h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>

			<!-- Modal body -->
			<div class="modal-body">
				<form method="post" action="remove_database.php">
					<div class="row">
						<div class="col-md-8">
							<label for="Super Admin Password">Super Admin Password</label>
							<input type="password" name="password" class="form-control" placeholder="Enter Password" required="required">
						</div>
						<div class="col-md-8">
							<label for="Confirm Password">Confirm Password</label>
							<input type="password" name="c_password" class="form-control" placeholder="Confirm Password" required="required">
						</div>
						<div class="col-md-8">
							<button type="submit" class="btn btn-success">Done</button>
						</div>
					</div>
				</form>
			</div>



		</div>
	</div>
</div>

<div class="modal" id="restore">
	<div class="modal-dialog modal-md">
		<div class="modal-content">

			<!-- Modal Header -->
			<div class="modal-header">
				<h4 class="modal-title">Restore Database</h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>

			<!-- Modal body -->
			<div class="modal-body">
				<form method="post" action="restore_database.php" enctype="multipart/form-data">
					<div class="row">
						<div class="col-md-8">
							<label for="Database.sql">Database.sql</label>
							<input type="file" name="kc_db" class="form-control" required="required">
						</div>
						<div class="col-md-8">
							<input type="submit" class="btn btn-success" name="import" value="Done">
						</div>
					</div>
				</form>
			</div>



		</div>
	</div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
	$('#remove-btn').on('click', function() {
		if (confirm('Are you sure')) {
			$('#modal-toggle').trigger("click");
		} else {
			return false;
		}
	});
	$('#restore-btn').on('click', function() {
		if (confirm('Are you sure that you remove your database.')) {
			$('#restore-modal-toggle').trigger("click");
		} else {
			return false;
		}
	});
</script>