<?php $seg = $this->uri->segment(2); ?>
<script type="text/javascript" src="/js/login.js"></script>
<script type="text/javascript" src="/js/md5.js"></script>
<form id="add_form" method="post" action="/cnc/nojs" onsubmit="return <?=$seg?>();">
<div id="hiddenInputs">
	<input type="hidden" id="referrer" value="/<?php echo $this->session->flashdata('referrer'); ?>" />
</div>
<table id="loginForm">
	<tr>
		<td colspan="2" class="loginError"><?php echo $this->session->flashdata('message'); ?></td>
	</tr>
	<tr <?php if ($seg == 'manage') echo 'style="display:none;"'; ?>>
		<td><label for="user"><span class="term">Username:<span class="note">Only alphanumeric characters are allowed.</span></span></label></td>
		<td><input type="text" id="user" name="user" maxlength="31" size="31" value="<?=$this->session->userdata('user')?>"/></td>
	</tr>
	<?php	if ($seg == 'account' || $seg == 'manage'): ?>
	<tr>
		<td><label for="name"><span class="term">Actual (First) Name:<span class="note">This name will be "stamped" on your notes.</span></span></label></td>
		<td><input type="text" id="name" name="name" maxlength="31" size="31" value="<?=($this->session->flashdata('name')) ? $this->session->flashdata('name') : $this->session->userdata('name')?>"/></td>
	</tr>
	<?php endif; 
	if ($seg == 'manage'): ?>
	<tr>
		<td><label for="oldpassword">Current password:</label></td>
		<td><input type="password" id="oldpassword" name="oldpassword" maxlength="31" size="31" /></td>
	</tr>
	<?php endif; ?>
	<tr>
		<td><label for="password"><?=($seg == 'manage') ? 'New ' : '';?>Password:</label></td>
		<td><input type="password" id="password" name="password" maxlength="31" size="31" /></td>
	</tr>
	<?php if ($seg == 'account' || $seg == 'manage'): ?>
	<tr>
		<td><label for="password1">Confirm <?=($seg == 'manage') ? 'new ' : '';?>password:</label></td>
		<td><input type="password" id="password1" name="password1" maxlength="31" size="31" /></td>
	</tr>
	<?php endif; ?>
	<tr>
		<td></td>
		<td><input type="submit" id="submit" name="submit" value="<?php
			if ($seg == 'account') {
				echo 'Create Account';
			} else if ($seg == 'manage') {
				echo 'Save';
			} else {
				echo 'Log In';
			} ?>" /></td>
	</tr>
</table>
</form>