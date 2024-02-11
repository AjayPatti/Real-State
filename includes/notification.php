<?php
if(isset($_SESSION['success']) || isset($_SESSION['error'])){
	?>
	<div class="col-sm-12">
		<?php
		if(isset($_SESSION['success'])){
			?>
			<div class="alert alert-success alert-dismissable">
				<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
				<h4><i class="icon fa fa-check"></i> Alert!</h4>
				<?php
				echo $_SESSION['success'];
				unset($_SESSION['success']);
				?>
			</div>
			<?php
		}else if(isset($_SESSION['error'])){
			if(is_array($_SESSION['error'])){ ?>
					<div class="alert alert-danger alert-dismissable">
						<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
						
						<h4><i class="icon fa fa-ban"></i> Alert!</h4>
						<?php
						foreach($_SESSION['error'] as $error){
							echo $error."<br>";
						}
						?>
					</div>
					<?php
			}else{
			?>
			<div class="alert alert-danger alert-dismissable">
				<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
				<h4><i class="icon fa fa-ban"></i> Alert!</h4>
				<?php
				echo $_SESSION['error'];
				?>
			</div>
			<?php
			}
			unset($_SESSION['error']);
		}
		?>
	</div>
<?php
}
?>
