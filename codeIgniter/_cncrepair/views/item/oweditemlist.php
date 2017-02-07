<?php if (!defined('BASEPATH')) exit('No direct script access allowed');?>
<script type="text/javascript" src="/js/item/itemlist.js"></script>
<form id="add_form" method="post" action="/item/oweditemlist">
<div>
	<table id="itemsTable">
	<tr class="head">
		<td>Customer:</td>
		<td>Part No:</td>
		<td>Invoice #:</td>
		<td>Item Sent:</td>
		<td><span class="term">Due Date:<span class="note">Defined as 15 days after item was shipped to the customer.</span></span></td>
	</tr>
	<tr>
		<td><input type="text" id="name" name="name" size="17" value="<?=$filters['name']?>" /></td>
		<td><input type="text" id="modelnum" name="modelnum" size="17" value="<?=$filters['modelnum']?>" /></td>
		<td><input type="text" id="inv_id" name="inv_id" size="8" value="<?=$filters['inv_id']?>" /></td>
		<td></td>
		<td><input type="submit" value="Filter" /><input type="button" value="Clear" onclick="clearForm();" /></td>
	</tr>
	<?php $i = 0;
		if (count($items) == 0): ?>
	<tr>
		<td colspan="5">
			There are no items owed to you!
		</td>
	</tr>
	<?php endif;
		foreach ($items as $item):
		if ($i%30 == 29): ?>
	<tr class="head">
		<td>Customer:</td>
		<td>Part No:</td>
		<td>Invoice #:</td>
		<td>Item Sent:</td>
		<td><span class="term">Due Date:<span class="note">Defined as 15 days after item was shipped to the customer.</span></span></td>
	</tr>
	<?php endif; ?>
	<tr id="item<?=$item['id']?>" class="r<?=($i%2)?>">
		<td><a href="/customer/index/<?=$item['cust_id']?>"><?=$item['name']?></a></td>
		<td><a href="/item/index/<?=$item['itemtype_id']?>"><?=$item['modelnum']?></a></td>
		<td><a href="/invoice/index/<?=$item['inv_id']?>"><?=$item['inv_id']?></a></td>
		<td><a href="/item/specific/<?=$item['barcode']?>"><?=$item['barcode']?></a></td>
		<td><?=$item['due_date']?></td>
	</tr>
	<?php $i++; endforeach;
		if (count($items) > 99): ?>
			<tr>
				<td colspan="5">100 results are shown; more exist.  Restrict filters to reduce results.</td>
			</tr>
	<?php endif; ?>
	</table>
</div>
</form>