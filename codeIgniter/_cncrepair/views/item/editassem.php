<?php if (!defined('BASEPATH')) exit('No direct script access allowed');?>
<script type="text/javascript" src="/js/item/editassem.js"></script>
<form id="add_form" action="/cnc/nojs" onsubmit="return editAssem('/item/edit/assem');">
<div id="hiddenInputs">
	<input type="hidden" id="tarows" value="<?=$tarows?>" />
	<input type="hidden" id="itemtype_id" />
	<input type="hidden" id="currRow" name="currRow" value="0" />
</div>
<div id="assemMain">
	<table id="editAssem">
	<thead>
		<tr>
			<td><label for="modelnum0">Part Number:</label></td>
			<td><label for="description0">Description:</label></td>
			<td><label for="quantity0">Qty:</label></td>
		</tr>
	</thead>
	<tbody id="editAssemBody">
		<!--First row placed by JS -->
	</tbody>
	</table>
</div>

<div id="assemButtons">
	<input type="submit" value="Save &amp; Close" id="close" />
	<input type="button" value="Close (no save)" id="justClose" onclick="gracefulClose();" />
</div>
</form>
</div>
</body>
</html>