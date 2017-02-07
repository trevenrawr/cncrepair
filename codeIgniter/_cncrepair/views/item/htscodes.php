<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Purpose:	HTS (Harmonized Tariff Schedule) dialog view.  Used for editing and choosing hts codes for items.  Possibly change
 * 		to a menu-based system later?
 *
 * Info:	HTS database information is loaded from the htscode function in the item controller.  HTSVIEW is the code,
 * 		HTS is the sql id, HTSDESCRIPTON is the description of the hts code.
 *
 * Author: 	Micah Leuba
*/
?>
<script type="text/javascript" src="/js/item/htscodes.js"></script>

<form id="buttoms" action="/cnc/nojs" onsubmit="return false">

<div id="htscodes">
	<table style="width: 100%; ">
		<thead>
			<tr>
				<th >ID</th>
				<th style="width: 20%;">HTS Code</th>
				<th >Description</th>
				<th ></th>
				<th ></th>
			</tr>
		</thead>
		<tbody >
			<?php $row = 0;?>
			<?php foreach ($htscodes as $line ) : ?>
			<tr id="row<?=$row?>" >
				<td><?=$line['id']?></td>
				<td><input id="htsview<?=$row?>" type="text" size="14" readonly="readonly" ondblclick="HtsSelect('<?=$row?>');" value="<?=$line['hts']?>" /></td>
				<td><input id="htsdescription<?=$row?>" type="text" readonly="readonly" size="55" value="<?=$line['description']?>" /></td>
				<td><input type="button" value="Edit" onclick="EditRow('<?=$row?>'); "/></td>
				<td><a href="#" class="imglink" ><img onclick="RemoveRow('<?=$row?>')" src="/pics/delsmall.png" alt="X" /></a></td>
			</tr>
			<? $row++; endforeach; ?>
		</tbody>
	</table>
</div>

<div id="assemButtons">

	<input type="submit" value="Save &amp; Close" id="close" />
	<input type="button" value="Close" id="justClose" onclick="gracefulClose();" />

</div>
</form>

</body>
</html>