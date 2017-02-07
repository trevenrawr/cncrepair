<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
$priorityArray = Array('', 'brown', 'orange', 'blue', 'red');
?>
<script type="text/javascript" src="/js/item/itemlist.js"></script>
<form id="add_form" method="post" action="/positions/viewQueue">
<div id="hiddenInputs">
</div>
<div id="queueView">
	<table id="itemsTable">
		<tr class="head">
			<td>Part No:</td>
			<td>Priority:</td>
			<?php if ($pos == ''): ?>
			<td>Ready for:</td>
			<?php endif; ?>
			<td>Barcode:</td>
			<td>Queued at:</td>
			<td>Location:</td>
			<td>View:</td>
		</tr>
		<?php if ($pos == ''): ?>
		<tr>
			<td><input type="text" id="modelnum" name="modelnum" size="20" value="<?=$queue['filters']['modelnum']?>" /></td>
			<td>
				<select id="priority" name="priority">
					<option value="">No Preference</option>
					<option value="1" <?=($queue['filters']['priority'] == '1') ? ' selected="selected"' : '';?>>Brown</option>
					<option value="2"<?=($queue['filters']['priority'] == '2') ? ' selected="selected"' : '';?>>Orange</option>
					<option value="3"<?=($queue['filters']['priority'] == '3') ? ' selected="selected"' : '';?>>Blue</option>
					<option value="4"<?=($queue['filters']['priority'] == '4') ? ' selected="selected"' : '';?>>Red</option>
				</select>
			</td>
			<?php if ($pos == ''): ?>
			<td>
				<select id="readyfor" name="readyfor">
					<option value="">No Preference</option>
					<option value="receiving" <?=($queue['filters']['readyfor'] == 'receiving') ? ' selected="selected"' : '';?>>Receiving</option>
					<option value="cleaning"<?=($queue['filters']['readyfor'] == 'cleaning') ? ' selected="selected"' : '';?>>Cleaning</option>
					<option value="repair"<?=($queue['filters']['readyfor'] == 'repair') ? ' selected="selected"' : '';?>>Repair</option>
					<option value="testing"<?=($queue['filters']['readyfor'] == 'testing') ? ' selected="selected"' : '';?>>Testing</option>
					<option value="shipping"<?=($queue['filters']['readyfor'] == 'shipping') ? ' selected="selected"' : '';?>>Shipping</option>
				</select>
			</td>
			<?php endif; ?>
			<td></td>
			<td></td>
			<td></td>
			<td><input type="submit" id="submit" value="Filter" /><input type="button" id="clear" value="Clear" onclick="clearForm();" /></td>
		</tr>
		<?php endif;
			if (!is_array($queue['queue'])): ?>
		<tr>
			<td colspan="<?=($pos == '') ? '7' : '6'; ?>"><?=$queue['queue']?></td>
		</tr>
		<?php else:
			$i = 0;
			foreach ($queue['queue'] as $item): 
				$item['priority'] = $priorityArray[$item['priority']];
			?>
		<tr class="r<?=($i%2)?>">
			<td><a href="/item/index/<?=$item['itemtype_id']?>"><?=$item['modelnum']?></a></td>
			<td class="capitalize" style="background-color: <?=$item['priority']?>;"><?=$item['priority']?></td>
			<?php if ($pos == ''): ?>
			<td class="capitalize"><?=$item['readyfor']?></td>
				<?php if ($item['readyfor'] == '' || $item['readyfor'] == 'stock'): ?>
				<td><a href="/item/specific/<?=$item['barcode']?>"><?=$item['barcode']?></a></td>
				<?php else: ?>
				<td><a href="/positions/<?=$item['readyfor']?>/<?=$item['barcode']?>"><?=$item['barcode']?></a></td>
				<?php endif; ?>
			<?php else: ?>
			<td><a href="#" onclick="window.opener.goBarcode('<?=$item['barcode']?>');window.close();"><?=$item['barcode']?></a></td>
			<?php endif; ?>
			<td><?=$item['timeready']?></td>
			<td><?=$item['rack']?> - <?=$item['shelf']?></td>
			<td><a href="#" onclick="view('<?=$item['id']?>/notes');">Notes</a> | <a href="#" onclick="view('<?=$item['id']?>/history');">History</a></td>
		</tr>
		<?php $i++; endforeach; 
			if (count($queue['queue']) > 99): ?>
		<tr>
			<td colspan="<?=($pos == '') ? '7' : '6'; ?>">100 results are shown; more exist.  Restrict filters to reduce results.</td>
		</tr>
		<?php endif; endif; ?>
	</table>
</div>
<div>
	<?php if ($pos != ''): ?>
	<input type="button" value="Close" id="justClose" onclick="window.close();" />
	<?php endif; ?>
</div>

</form>
<?php if (isset($footer)): ?>
</div>
</body>
</html>
<?php endif; ?>