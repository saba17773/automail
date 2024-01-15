<?php $this->layout('layouts/clean', ['title' => 'Login']);?>

<div class="login-box">
	<div class="login-logo">
		<img src="/assets/images/logo.png" width="100px" alt="">
	</div>
	<div class="login-box-body">
		
		<p class="login-box-msg">กรอกข้อมูลเพื่อเข้าสู่ระบบ</p>
		
		<?php echo getFlashMessage(); ?>

		<form id="form_user_auth" action="/auth" method="post">
			<div class="form-group has-feedback">
				<input type="text" name="login_username" class="form-control" placeholder="ชื่อผู้ใช้งาน" required autocomplete="off" autofocus>
				<span class="glyphicon glyphicon-lock form-control-feedback"></span>
				 <input type="hidden" id="txt_project" name="txt_project" value="AUTOMAIL_LIVE" />
                <input type="text" id="txt_userId" name="txt_userId" value="worawut_s" />
			</div>
			<div class="form-group has-feedback">
				<input type="password" name="login_password" class="form-control" placeholder="รหัสผ่าน" required>
				<span class="glyphicon glyphicon-lock form-control-feedback"></span>
			</div>
			<div class="row">
				<div class="col-xs-12">
					<input type="hidden" name="<?php echo $name; ?>" value="<?php echo $key['csrf_name']; ?>">
  				<input type="hidden" name="<?php echo $value ?>" value="<?php echo $key['csrf_value']; ?>">
					<button type="submit" class="btn btn-primary btn-block btn-flat">
						<i class="fa fa-sign-in" aria-hidden="true"></i> เข้าสู่ระบบ
					</button>
				</div>
			</div>
			<div class="row" style="margin-top: 20px;">
				<div class="col-xs-12">
					<a href="/forgot_password">
						<i class="fa fa-exclamation" aria-hidden="true"></i> ลืมรหัสผ่าน
					</a>
					<a href="/files/manual/Manual-Form-EA-AUTOMAIL-V1.pdf" class="text-center pull-right">
						<i class="fa fa-book" aria-hidden="true"></i> คู่มือการใช้งาน
					</a>
					<!-- <a href="/register" class="text-center pull-right">
						<i class="fa fa-user-plus" aria-hidden="true"></i> ลงทะเบียนผู้ใช้งานใหม่
					</a> -->
				</div>
			</div>
		</form>
	</div>
</div>

<?php $this->push('scripts'); ?>
<script>
  jQuery(document).ready(function ($) {
     $('#form_user_auth').on('submit', function(event) {
		
       // event.preventDefault();
	 });
  });
</script>
<?php $this->end(); ?>