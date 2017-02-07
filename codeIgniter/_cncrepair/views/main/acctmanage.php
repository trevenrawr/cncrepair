<?php $seg = $this->uri->segment(2); ?>
<script type="text/javascript" src="/js/login.js"></script>
<script type="text/javascript" src="/js/md5.js"></script>
<form id="add_form" method="post" action="/cnc/nojs" onsubmit="return <?=$seg?>('/cnc/home');">
<div id="hiddenInputs">
</div>
<div>
<table id="itemsTable">
	<tr class="head">
		<td>Username</td>
		<td>Name</td>
		<td class="cblist"><span class="term">Manager<span class="note">Managers can manage master items, perform inventory checks, and change user privileges.</span></span></td>
		<td class="cblist"><span class="term">Office<span class="note">Office position can manage customers and create quotes and invoices.</span></span></td>
		<td class="cblist"><span class="term">Ship/Receive<span class="note">Receiving position can use the unpacking, receiving, and shipping tools.</span></span></td>
		<td class="cblist"><span class="term">Cleaning<span class="note">Cleaning position can use the cleaning tool.</span></span></td>
		<td class="cblist"><span class="term">Assembling<span class="note">Assembling position can assemble and disassemble items.</span></span></td>
		<td class="cblist"><span class="term">Repair<span class="note">Repair position can manage specific items and use the repair and testing tools.</span></span></td>
		<td class="cblist"><span class="term">Locked<span class="note">Locked accounts cannot be logged into, but remain on file.</span></span></td>
	</tr>
	<?php $i = 0; foreach ($users as $user): 
		$pos = $this->user_model->getPos($user['position']); ?>
	<tr class="r<?=$i%2?>">
		<td><?=$user['user']?></td>
		<td><?=$user['name']?></td>
		<td><input type="checkbox" id="<?=$user['user']?>b" <?=($pos['boss'] !== false) ? 'checked="checked"' : ''; ?> <?=($user['user'] == $this->session->userdata('user')) ? 'disabled="disabled"' : false;?> /></td>
		<td><input type="checkbox" id="<?=$user['user']?>o" <?=($pos['office'] !== false) ? 'checked="checked"' : ''; ?> /></td>
		<td><input type="checkbox" id="<?=$user['user']?>i" <?=($pos['unpacking'] !== false) ? 'checked="checked"' : ''; ?> /></td>
		<td><input type="checkbox" id="<?=$user['user']?>c" <?=($pos['cleaning'] !== false) ? 'checked="checked"' : ''; ?> /></td>
		<td><input type="checkbox" id="<?=$user['user']?>a" <?=($pos['assembling'] !== false) ? 'checked="checked"' : ''; ?> /></td>
		<td><input type="checkbox" id="<?=$user['user']?>r" <?=($pos['repair'] !== false) ? 'checked="checked"' : ''; ?> /></td>
		<td><input type="checkbox" id="<?=$user['user']?>l" <?=($user['locked'] == 1) ? 'checked="checked"' : ''; ?> <?=($user['user'] == $this->session->userdata('user')) ? 'disabled="disabled"' : false;?> /></td>
	</tr>
	<?php $i++; endforeach; ?>
</table>
</div><div>
<input type="submit" value="Save" />
</div>
</form>