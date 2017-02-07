<script type="text/javascript" src="/js/item/itemlist.js"></script>
<div>
	<table id="itemsTable">
		<tr class="head">
			<td>Barcode</td>
			<td>Status</td>
			<td>On Hold?</td>
			<td>Location</td>
			<td class="viewBox">View:</td>
		</tr>
		<?php $i = 0;
		if (count($items) == 0): ?>
		<tr>
			<td colspan="5">
				There were no onhand items found of that part number.
			</td>
		</tr>
		<?php endif;
		foreach ($items as $item):  
			// Determine the class of the row (parent, subitem, regular item)
			if ($item['parent'] > 0) {
				if ($item['assembly'] == 1) {
					$print = ' parentItem';
					$free = '';
				} else {
					$print = ' childItem';
					$free = 'In assembly';
				}
				$assem = "'true'";
			} else {
				$print = '';
				$free = 'Free';
				$assem = "'false'";
			}
		?>
		<tr class="r<?=($i%2)?><?=$print?>">
			<td><?php if ($item['onhold'] < 2):?><a href="javascript:void();" onclick="window.opener.barcodeSelect({barcode: '<?=$item['barcode']?>', id: <?=$item['id']?>, itemtype_id: <?=$item['itemtype_id']?>}, <?=$row?>, <?=$assem?>);window.close();"><?=$item['barcode']?></a><?php else: ?><?=$item['barcode']?><?php endif; ?></td>
			<td class="capitalize"><?=$item['txtstatus']?></td>
			<td><?php switch($item['onhold']) {
				case 1:
					echo 'Held';
					break;
				case 2:
					echo 'Locked';
					break;
				case 0:
				default:
					echo $free;
					break;
			}?></td>
			<td><?php
				$location = ($item['rack'] != '') ? $item['rack'].' - '.$item['shelf'] : '';
				echo ($item['atcustomer'] == 0) ? $location : $item['atcust'];
			?></td>
			<td class="viewBox"><?php
				if ($item['barcode'] != '')
					echo '<a href="javascript:view(\''.$item['id'].'/notes\');">Notes</a> | <a href="javascript:view(\''.$item['id'].'/history\');">History</a>';
			?></td>
		</tr>
		<?php $i++; endforeach; ?>
	</table>
</div>
</div>
</body>
</html>