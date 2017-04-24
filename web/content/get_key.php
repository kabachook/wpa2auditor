<?php
	if(isset($_COOKIE['key'])) {
		echo "YOU HAVE THE KEY";
	} else {
		echo '<div class="container">
	<div class="col-md-12">
		<div class="modal-dialog" style="margin-bottom:0">
			<div class="modal-content">
				<div class="panel-heading">
					<h3 class="panel-title">Sign Up</h3>
				</div>
				<div class="panel-body">
					<form role="form" method="post" action="">
						<fieldset>
							<div class="form-group">
								<input class="form-control" placeholder="Nickname" name="nick" type="text" value="" required="">
							</div>
							<div class="form-group">
								<input class="form-control" placeholder="E-mail" name="email" type="email" autofocus="" required="">
							</div>
							<!-- Change this to a button or input when using this as a form -->
							<?php echo $reg_message; ?>
							<input type="hidden" name="rec_valid" value="1" />
							<button class="btn btn-sm btn-success">Login</button>
						</fieldset>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<br/>
</div>';
	}
?>
