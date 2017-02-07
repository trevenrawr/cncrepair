<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
$seg = $this->uri->segment(2);
$bar0 = '';
$val = 'Save';
$tacols = 70;
$js = 'positions';
$rack = (isset($rack)) ? $rack : '000'; // Update these when shelves and racks are numbered!
$shelf = (isset($shelf)) ? $shelf : '000';

// This sets the values for the routing buttons based on which page is being loaded.
switch ($seg) {
	case 'unpacking':
		$val = 'Unpacked';
		$route0['label'] = '';
		$route0['val'] = 'receiving';
		break;
	case 'receiving': // Could go to cleaning or straight to repair
		$val = 'Receive';
		$route0['label'] = 'Needs to be cleaned (to cleaning)';
		$route0['val'] = 'cleaning';
		$route1['label'] = 'Clean enough already (to repair)';
		$route1['val'] = 'repair';
		break;
	case 'cleaning':
		$val = 'Cleaned!';
		$stay = 'Needs more cleaning';
		$route0['label'] = 'Ready for repair';
		$route0['val'] = 'repair';
		break;
	case 'repair':
		$stay = 'Needs more repair work';
		$bar0 = '0';
		$route0['label'] = 'Ready for testing';
		$route0['val'] = 'testing';
		break;
	case 'testing': // Could go to repair or on to shipping
		$stay = 'Needs more testing';
		$bar0 = '0';
		$route0['label'] = 'Tested unhappy (to repair)';
		$route0['val'] = 'repair';
		$route1['label'] = 'Tested happy (ready to ship)';
		$route1['val'] = 'shipping';
		break;
	case 'assembling':
		$val = 'Assembled!';
		$stay = 'Ready for repair';
		$route0['label'] = 'Ready for testing';
		$route0['val'] = 'testing';
		$route1['label'] = 'Ready to ship';
		$route1['val'] = 'shipping';
		$js = 'assembling';
		break;
	case 'shipping':
		$val = 'Ship';
		$bar0 = '0';
		$route0['label'] = '';
		$route0['val'] = '';
		$tarows = $tarows - 5;
		break;
	default:
}
?>
<script type="text/javascript" src="/js/positions/<?=$js?>.js"></script>
<form id="add_form" method="post" action="/cnc/nojs" onsubmit="return savePos('<?=$seg?>');">
<div id="hiddenInputs">
	<input type="hidden" id="pos" name="pos" value="<?=$seg?>" />
	<input type="hidden" id="cust_id" name="cust_id" />
	<input type="hidden" id="quoteitem_id" name="quoteitem_id" />
	<input type="hidden" id="item_id" name="item_id" value="0" />
	<input type="hidden" id="itemtype_id" name="itemtype_id" value="0" />
	<input type="hidden" id="inv_id" name="inv_id" />
	<input type="hidden" id="invoiceitem_id" name="invoiceitem_id" />
	<input type="hidden" id="ship_inv_id" name="ship_inv_id" />
	<input type="hidden" id="ship_invoiceitem_id" name="ship_invoiceitem_id" />
	<input type="hidden" id="parent_id" name="parent_id" />
	<input type="hidden" id="currRow" name="currRow" />
	<input type="hidden" id="currTableRow" name="currTableRow" />
</div>
<div id="repMain">
	<fieldset><legend>Item Specifics:</legend>
	<table class="right">
		<thead>
			<?php if ($seg == 'assembling'): ?>
			<tr>
				<td><label for="assembly">*Assembly Type:</label></td>
				<td>
					<input type="text" id="assembly" name="assembly" maxlength="63" size="31" />
					<div class="relPos">
						<div id="assemblyShadow" class="suggShadow">
							<div id="assemblyBox" class="suggBox"></div>
						</div>
					</div>
				</td>
			</tr>
			<tr class="center">
				<td colspan="2">(to create a new assembly)<br /><strong>OR</strong><br />(for existing assemblies)</td>
			</tr>
			<?php elseif ($seg == 'unpacking'): ?>
			<tr>
				<td><label for="name">*Customer Name:</label></td>
				<td>
					<input type="text" id="name" name="name" maxlength="63" size="31" />
					<div class="relPos">
						<div id="nameShadow" class="suggShadow">
							<div id="nameBox" class="suggBox"></div>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td><span class="term"><label for="incomingTypes">*Part #:</label><span class="note">These are parts quoted to the above customer in the past 30 days.</span></span></td>
				<td>
					<select id="incomingTypes" name="incomingTypes" disabled="disabled" onchange="updateModel();">
						<option value="someSerial">Look up customer to populate</option>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2"><textarea id="description" name="description" rows="4" cols="25" disabled="disabled"></textarea></td>
			</tr>
			<tr>
				<td><label for="serial">*Serial #:</label></td>
				<td>
					<input type="text" id="serial" name="serial" maxlength="63" size="31" />
					<div class="relPos">
						<div id="serialShadow" class="suggShadow">
							<div id="serialBox" class="suggBox"></div>
						</div>
					</div>
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<td><label for="barcode<?=$bar0?>">*Barcode #:</label></td>
				<td><input type="text" id="barcode<?=$bar0?>" name="barcode<?=$bar0?>" maxlength="31" size="31" />
					<div class="relPos">
						<div id="barcode<?=$bar0?>Shadow" class="suggShadow">
							<div id="barcode<?=$bar0?>Box" class="suggBox"></div>
						</div>
					</div>
					<?php if ($bar0 != ''): ?>
					<input type="hidden" id="itemtype_id0" name="itemtype_id0" />
					<input type="hidden" id="item_id0" name="item_id0" />
					<?php endif; ?>
				</td>
			</tr>
		</thead>
		<tbody id="barcodeList">
		</tbody>
		<?php if ($seg == "shipping"): ?>
		<tfoot>
			<tr id="shipCarrier">
				<td><label for="carrier">Carrier:</label></td>
				<td>
					<select id="carrier" name="carrier">
						<option value="UPS">UPS</option>
						<option value="FedEX">FedEX</option>
						<option value="DHL">DHL</option>
						<option value="other carrier">Other</option>
					</select>
				</td>
			</tr>
			<tr>
				<td><label for="trackingnum">Tracking #:</label></td>
				<td><input type="text" id="trackingnum" name="trackingnum" maxlength="63" size="31" /></td>
			</tr>
		</tfoot>
		<?php endif; ?>
	</table>
	</fieldset>
	
	<?php if ($seg == 'repair' || $seg == 'testing'): ?>
	<fieldset class="left"><legend>Item Status:</legend>
		<input type="range" id="status" name="status" min="1" max="5" value="3" onchange="showStatus(this.value, this.id);" />
		<label for="status"><span id="txtstatus">needs work</span></label>
	</fieldset>
	<?php elseif ($seg == 'receiving' || $seg == 'assembling' || $seg == 'shipping'): ?>
	<fieldset class="left"><legend>Priority:</legend>
		<input type="range" id="priority" name="priority" min="1" max="4" value="1" onchange="showStatus(this.value, this.id);" <?php if ($seg == 'shipping') echo 'disabled="disabled" ';?>/>
		<div id="txtpriority" style="background-color: brown;">brown</div>
	</fieldset>
	<?php endif; ?>

<!-- ROUTING INFORMATION BEGINS HERE -->
	<fieldset id="routing" <?php if ($seg == 'shipping') echo 'style="display:none;"'; ?> ><legend>Item Routing / Location:</legend>
	<table class="left">
		<?php if ($seg != 'receiving' && $seg != 'shipping' && $seg != 'unpacking'): ?>
			<tr>
				<td><input type="radio" id="stay" name="route" value="<?=($seg != 'assembling') ? $seg : 'repair';?>" checked="checked" /></td>
				<td><label for="stay"><?=$stay?></label></td>
			</tr>
		<?php endif; ?>
		<tr <?php if ($seg == 'unpacking') echo 'style="display:none;"'; ?>>
			<td><input type="radio" id="route0" name="route" value="<?=$route0['val']?>" <?php if ($seg == 'shipping' || $seg == 'unpacking') echo 'checked="checked"'; ?> /></td>
			<td><label for="route0"><?=$route0['label']?></label></td>
		</tr>
		<?php if ($seg == 'receiving' || $seg == 'testing' || $seg == 'assembling'): ?>
			<tr>
				<td><input type="radio" id="route1" name="route" value="<?=$route1['val']?>" /></td>
				<td><label for="route1"><?=$route1['label']?></label></td>
			</tr>
		<?php endif; 
			if ($seg == 'repair' || $seg == 'testing' || $seg == 'assembling'): ?>
			<tr>
				<td><input type="checkbox" id="stock" name="stock" /></td>
				<td><label for="stock"><span class="term">CNC Stock?<span class="note">This prevents items from showing in the queue.</span></span></label></td>
			</tr>
		<?php endif;
			if ($seg != 'shipping'): ?>
			<tr><td colspan="2">
				<table>
					<tr>
						<td><label for="rack">Rack #:</label></td>
						<td><input type="text" id="rack" name="rack" size="3" maxlength="5" value="<?=$rack?>" /></td>
						<td><label for="shelf">Shelf #:</label></td>
						<td><input type="text" id="shelf" name="shelf" size="3" maxlength="5" value="<?=$shelf?>" /></td>
					</tr>
				</table>
			</td></tr>
		<?php endif; ?>
	</table>
	</fieldset>

	<?php if ($seg != 'assembling'): ?>
	<fieldset class="center"><legend>More Information:</legend>
		<?php if ($seg != 'unpacking' && $seg != 'assembling'): ?>
		<input type="button" class="moreInfo" value="See items ready for <?=$seg?>" id="viewQueue" onclick="viewQueued('<?=$seg?>');" /><br />
		<?php endif;
			if ($seg == 'cleaning' || $seg == 'repair' || $seg == 'testing'): ?>
		<input type="button" class="moreInfo" value="View <?=$seg?> procedures" id="viewProcs" onclick="view('<?=$seg?>procs');" disabled="disabled" /><br />
		<?php endif;
			if ($seg == 'repair' || $seg == 'testing'): ?>
		<input type="button" class="moreInfo" value="View full notes" id="viewOldNotes" onclick="view('oldnotes');" disabled="disabled" /><br />
		<?php endif; ?>
		<input type="button" class="moreInfo" value="View item history" id="viewHist" onclick="view('history');" disabled="disabled" />
	</fieldset>
	<?php endif; ?>
</div>

<div id="repSave">
	<input type="submit" value="<?=$val?>" id="save" name="save" />
</div>
<div id="repNotes">
<?php if ($seg == 'unpacking' || $seg == 'shipping'): ?>
	<table>
		<tr>
			<td><label for="shipnotes">Packaging damages/notes:</label></td>
		</tr>
		<tr>
			<td>
				<textarea id="newnotes" name="newnotes" rows="<?=($tarows - 13)?>" cols="<?=$tacols?>" class="notesInput"></textarea><br />
				<textarea id="shipnotes" name="shipnotes" rows="<?=($tarows - 5)?>" cols="<?=$tacols?>" disabled="disabled" class="notesInput"></textarea>
			</td>
		</tr>
	</table>
<?php endif;
if ($seg == 'receiving' || $seg == 'shipping'): ?>
	<table class="receivingList">
		<thead>
			<tr class="head">
				<td>Invoice #:</td>
				<td>Service:</td>
				<td>Qty Remaining:</td>
				<td>Customer Name:</td>
				<td>Date Created:</td>
				<td>Ship to:</td>
			</tr>
		</thead>
		<tbody id="<?=$seg?>List">
		</tbody>
	</table>
<?php elseif ($seg == 'assembling'): ?>
	<table class="receivingList" id="barcodeListing">
		<tr class="head" id="barcodeHeader">
			<td>Part #:</td>
			<td colspan="2">Barcode:</td>
		</tr>
	</table>
<?php elseif ($seg == 'repair' || $seg == 'testing' || $seg == 'cleaning'): ?>
	<table>
		<tr>
			<td><label for="notes">Repair Notes:</label></td>
		</tr>
		<tr>
			<td>
				<textarea id="newnotes" name="newnotes" rows="<?=($tarows - 13)?>" cols="<?=$tacols?>" class="notesInput"></textarea><br />
				<textarea id="notes" name="notes" rows="<?=($tarows - 5)?>" cols="<?=$tacols?>" disabled="disabled" class="notesInput"></textarea>
			</td>
		</tr>
	</table>
<?php endif; ?>
</div>
</form>