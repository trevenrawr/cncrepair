<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
$tacols = 65;
// This is a form for creating/editing a specific (barcoded) item.
?>
<script type="text/javascript" src="/js/item/specificitem.js"></script>
<form id="add_form" method="post" action="/cnc/nojs" onsubmit="return savePos();">
<div id="hiddenInputs">
	<input type="hidden" id="cust_id" name="cust_id" value="0" />
	<input type="hidden" id="owner_id" name="owner_id" value="0" />
	<input type="hidden" id="item_id" name="item_id" value="0" />
	<input type="hidden" id="itemtype_id" name="itemtype_id" />
</div>
<div id="repMain">
	<fieldset><legend>Item Specifics:</legend>
	<table class="right">
		<tr>
			<td><label for="barcode">*Barcode #:</label></td>
			<td><input type="text" id="barcode" name="barcode" maxlength="31" size="31" />
				<div class="relPos">
					<div id="barcodeShadow" class="suggShadow">
						<div id="barcodeBox" class="suggBox"></div>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<td><label for="modelnum">*Part #:</label></td>
			<td>
				<input type="text" id="modelnum" name="modelnum" maxlength="63" size="31" />
				<div class="relPos">
					<div id="modelnumShadow" class="suggShadow">
						<div id="modelnumBox" class="suggBox"></div>
					</div>
				</div>
			</td>
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
		<tr>
			<td><label for="owner">Owner:</label></td>
			<td>
				<input type="text" id="owner" name="owner" maxlength="63" size="31" value="CNC Repair" />
				<div class="relPos">
					<div id="ownerShadow" class="suggShadow">
						<div id="ownerBox" class="suggBox"></div>
					</div>
				</div>
			</td>
		</tr>
	</table>
	</fieldset>

	<fieldset class="left"><legend>Item Status:</legend>
		<input type="range" id="status" name="status" min="1" max="5" value="3" onchange="showStatus(this.value, this.id);" />
		<label for="status"><span id="txtstatus">needs work</span></label><br />
		<table><tr id="atCustomer">
			<td><label for="name">At Customer:</label></td>
			<td>
				<input type="text" id="name" name="name" maxlength="63" size="31" value="CNC Repair" />
				<div class="relPos">
					<div id="nameShadow" class="suggShadow">
						<div id="nameBox" class="suggBox"></div>
					</div>
				</div>
			</td>
		</tr>
		<tr id="rackShelf">
			<td colspan="2">
				<table>
					<tr>
						<td><label for="rack">Rack #:</label></td>
						<td><input type="text" id="rack" name="rack" size="3" maxlength="5" /></td>
						<td><label for="shelf">Shelf #:</label></td>
						<td><input type="text" id="shelf" name="shelf" size="3" maxlength="5" /></td>
					</tr>
				</table>
			</td>
		</tr>
		</table>
	</fieldset>

	<fieldset id="routing" class="left"><legend>Queuing Info:</legend>
	<table>
		<tr>
			<td><input type="checkbox" id="stockitem" onchange="cncstock();" /></td>
			<td><label for="stockitem"><span class="term">CNC Stock?<span class="note">This sets the parameters expected when an item is considered "stock."</span></span></label></td>
		</tr>
	</table>
	<table>
		<tr id="waitingFor">
			<td><label for="readyfor">Ready for:</label></td>
			<td>
				<select id="readyfor" name="readyfor">
					<option value="stock">Stock</option>
					<option value="receiving">Receiving</option>
					<option value="cleaning">Cleaning</option>
					<option value="repair">Repair</option>
					<option value="testing">Testing</option>
					<option value="shipping">Shipping</option>
					<option value="">At Customer</option>
				</select>
			</td>
		</tr>
	</table>
	<label for="priority">Priority:</label> <input type="range" id="priority" name="priority" min="1" max="4" value="0" onchange="showStatus(this.value, this.id);" />
	<div id="txtpriority" style="background-color: brown;">brown</div><br />
	</fieldset>

	<fieldset class="center"><legend>More Information:</legend>
	<input type="button" class="moreInfo" value="View full notes" id="viewOldNotes" onclick="view('oldnotes');" disabled="disabled" /><br />
	<input type="button" class="moreInfo" value="View item history" id="viewHist" onclick="view('history');" disabled="disabled" />
	</fieldset>
</div>

<div id="repSave">
	<input type="submit" value="Save" id="save" name="save" />
</div>

<div id="repNotes">
	<table>
		<tr>
			<td><label for="notes">Repair Notes:</label></td>
		</tr>
		<tr>
			<td>
				<textarea id="notes" name="notes" rows="<?=$tarows?>" cols="<?=$tacols?>" disabled="disabled"></textarea>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td><label for="shipnotes">Packaging damages/notes:</label></td>
		</tr>
		<tr>
			<td><textarea id="shipnotes" name="shipnotes" rows="<?=$tarows?>" cols="<?=$tacols?>" disabled="disabled"></textarea></td>
		</tr>
	</table>
</div>
</form>