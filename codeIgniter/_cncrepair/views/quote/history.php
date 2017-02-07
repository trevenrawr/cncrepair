<?php $i = 0; foreach ($custHistory as $history): 
	if (trim($history['type']) == 'Quote') {
		$qi = 'quote';
	} else {
		$qi = 'invoice';
	}
	$history['type'].$history['refnum'].$history['itemtotal'].$history['total'].$history['created'];
	?>
<tr id="<?=$qi.$history['refnum']?>" onDblclick="openQuote('<?=$qi.$history['refnum']?>');" class="r<?=$i%2?>">
	<td><?=$history['name']?></td>
	<td><a href="javascript:openQuote('<?=$qi.$history['refnum']?>');"><?=$history['type']?>#: <?=$history['refnum']?></a></td>
	<td><?=$history['purchaseorder']?></td>
	<td><?=$history['created']?></td>
	<td><?=$history['createdby']?></td>
	<td><?=$history['sent']?></td>
	<td><?=$history['itemtotal']?></td>
	<td>$<?=$history['total']?></td>
</tr>
<?php $i++; endforeach; ?>
<?php if (count($custHistory) == 0): ?>
<tr><td colspan="6">No Quotes or Invoices on record for your search criteria.</td></tr>
<?php endif; ?>