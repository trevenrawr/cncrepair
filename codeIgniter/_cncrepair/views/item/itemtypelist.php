<?php if (!defined('BASEPATH')) exit('No direct script access allowed');?>
<script type="text/javascript" src="/js/item/typelist.js"></script>
<form id="add_form" method="post" action="/item/typelist">
<div>
	<table id="itemsTable">
	<tr class="head">
		<td>Part No:</td>
		<td>Make:</td>
		<td>Description:</td>
		<td>On Hand:</td>
		<td>On Hold:</td>
		<td>In Assembly:</td>
	</tr>
	<tr>
		<td>
			<input type="text" id="modelnum" name="modelnum" size="17" value="<?=$filters['modelnum']?>" />
			<div class="relPos">
				<div id="modelnumShadow" class="suggShadow">
					<div id="modelnumBox" class="suggBox"></div>
				</div>
			</div>
		</td>
		<td>
			<input type="text" id="make" name="make" size="17" value="<?=$filters['make']?>" />
			<div class="relPos">
				<div id="makeShadow" class="suggShadow">
					<div id="makeBox" class="suggBox"></div>
				</div>
			</div>
		</td>
		<td></td>
		<td></td>
		<td><input type="submit" value="Filter" /></td>
		<td><input type="button" value="Clear" onclick="clearForm();" /></td>
	</tr>
	<?php $i = 0;
		if (count($items) == 0): ?>
	<tr>
		<td colspan="6">
			There were no items found for your search criteria. :(
		</td>
	</tr>
	<?php endif;
		foreach ($items as $item):
		if ($i%30 == 29): ?>
	<tr class="head">
		<td>Part No:</td>
		<td>Make:</td>
		<td>Description:</td>
		<td>On Hand:</td>
		<td>On Hold:</td>
		<td>In Assembly:</td>
	</tr>
	<?php endif; ?>
	<tr id="item<?=$item['id']?>" class="r<?=($i%2)?>">
		<td><a href="/item/index/<?=$item['id']?>"><?=$item['modelnum']?></a></td>
		<td><?=$item['make']?></td>
		<td><?=$item['description']?></td>
		<td><?=$item['onhand']?></td>
		<td><?=$item['onhold']?></td>
		<td><?=$item['inuse']?></td>
	</tr>
	<?php $i++; endforeach;
		if (count($items) > 99): ?>
			<tr>
				<td colspan="6">100 results are shown; more exist.  Restrict filters to reduce results.</td>
			</tr>
	<?php endif; ?>
	</table>
</div>
</form>