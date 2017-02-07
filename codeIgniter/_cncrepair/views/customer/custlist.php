<script type="text/javascript" src="/js/customerlist.js"></script>
<form id="add_form" method="post" action="/customer/add" onsubmit="return addcustomer('/customer/add');">
<table id="itemsTable" style="display: table;">
<?php $i = 0;
	foreach ($customers as $customer):
	if ($i%30 == 0): ?>
<tr class="head">
	<td>Customer Name:</td>
	<td>City:</td>
	<td>State:</td>
</tr>
	<?php endif; ?>
<tr id="customer<?=$customer['id']?>" class="r<?=($i%2)?>">
	<td><?=$customer['name']?></td>
	<td><?=$customer['city']?></td>
	<td><?=$customer['state']?></td>
	<td><a href="#" onclick="view('<?=$customer['id']?>/notes');">Notes</a></td>
</tr>
<?php $i++; endforeach;?>
</table>
</form>