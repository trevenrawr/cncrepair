<?php if (!defined('BASEPATH')) exit('No direct script access allowed');?>
<script type="text/javascript" src="/js/item/itemlist.js"></script>
<form id="add_form" method="post" action="/item/fulllist">
<div>
	<table id="itemsTable">
	<tr class="head">
		<td>Part No:</td>
		<td>Status:</td>
		<td>Location:</td>
		<td>Barcode:</td>
		<td>Last Seen:</td>
		<td>Owner:</td>
		<td class="viewBox">View:</td>
	</tr>
	<tr>
		<td><input type="text" id="modelnum" name="modelnum" size="17" value="<?=$filters['modelnum']?>" /></td>
		<td><input type="text" id="status" name="status" size="10" value="<?=$filters['status']?>" /></td>
		<td><!--<input type="text" id="atcust" name="atcust" size="17" value="<?=$filters['atcust']?>" />--></td>
		<td></td>
		<td></td>
		<td><input type="text" id="owner" name="owner" size="17" value="<?=$filters['owner']?>" /></td>
		<td><input type="submit" value="Filter" /><input type="button" value="Clear" onclick="clearForm();" /></td>
	</tr>
	<?php $i = 0;
		if (count($items) == 0): ?>
	<tr>
		<td colspan="7">
			There were no items found for your search criteria. :(
		</td>
	</tr>
	<?php endif;
		foreach ($items as $item):
		if ($i%30 == 29): ?>
	<tr class="head">
		<td>Part No:</td>
		<td>Status:</td>
		<td>Location:</td>
		<td>Barcode:</td>
		<td>Last Seen:</td>
		<td>Owner:</td>
		<td class="viewBox">View:</td>
	</tr>
	<?php endif; 
	// Determine the class of the row (parent, subitem, regular item)
	if ($item['parent'] > 0) {
		if ($item['assembly'] == 1) {
			$print = ' parentItem';
		} else {
			$print = ' childItem';
		}
	} else {
		$print = '';
	}
	?>
	<tr id="item<?=$item['id']?>" class="r<?=($i%2)?><?=$print?>">
		<td><a href="/item/index/<?=$item['itemtype_id']?>"><?=$item['modelnum']?></a></td>
		<td class="capitalize"><?=$item['txtstatus']?></td>
		<td><?php
			$location = ($item['rack'] != '') ? $item['rack'].' - '.$item['shelf'] : '';
			echo ($item['atcustomer'] == 0) ? $location : '<a href="/customer/index/'.$item['atcustomer'].'">'.$item['atcust'].'</a>';
		?></td>
		<td><a href="/item/specific/<?=$item['barcode']?>"><?=$item['barcode']?></a></td>
		<td><?=$item['lastseen']?></td>
		<td><?=$item['owner']?></td>
		<td class="viewBox"><?php
			if ($item['barcode'] != '')
				echo '<a href="#" onclick="view(\''.$item['id'].'/notes\');">Notes</a> | <a href="#" onclick="view(\''.$item['id'].'/history\');">History</a>';
		?></td>
	</tr>
	<?php $i++; endforeach;
		if (count($items) > 99): ?>
			<tr>
				<td colspan="7">100 results are shown; more exist.  Restrict filters to reduce results.</td>
			</tr>
	<?php endif; ?>
	</table>
</div>
</form>