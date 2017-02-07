<?php if (!defined('BASEPATH')) exit('No direct script access allowed');?>
<script type="text/javascript" src="/js/quote/addquote.js"></script>
<form id="add_form" method="post" action="/cnc/nojs" onsubmit="return addQuote('/<?=$qORi?>/add');">
<div id="hiddenInputs">
	<input type="hidden" id="tarows" value="<?=$tarows?>" />
	<input type="hidden" id="cust_id" name="cust_id" />
	<input type="hidden" id="currRow" name="currRow" value="0" />
	<input type="hidden" id="itemsDel" name="itemsDel" />
	<input type="hidden" id="<?=isset($quote_id) ? 'quote_id' : 'inv_id' ?>" name="<?=isset($quote_id) ? 'quote_id' : 'inv_id' ?>" />
	<input type="hidden" id="taxrate" name="taxrate" value="0.00" />
	<input type="hidden" id="taxtype" name="taxtype" value="" />

</div>
<div id="quoteNum">
	<label for="refNumDisp"><a href="#" onclick="getQuote();"><?=($qORi == 'quote') ? 'Quote' : 'Invoice' ?>#:</a></label><br />
	<input type="text" id="refNumDisp" name="refNumDisp" size="6" disabled="disabled" value="<?=isset($quote_id) ? $quote_id : $inv_id ?>" /><br />
	<input type="text" id="date" name="date" value="<?=date('M d, Y')?>" disabled="disabled" />
</div>
<div id="editSignature">
	<span id="cby">Created by: <span id="createdbyname" class="strong"></span>
	on <span id="datecreated" class="strong"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
	<span id="eby">Last Edited by: <span id="editedbyname" class="strong"></span>
	at <span id="datelastedited" class="strong"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
	<span id="edate">Emailed on: <span id="emaildate" class="strong"></span></span>
</div>
<div id="quoteCust">
<table>
	<tr>
		<td><label for="name">*Customer:</label></td>
		<td>
			<input type="text" id="name" name="name" maxlength="31" size="31"/>
			<div class="relPos">
				<div id="nameShadow" class="suggShadow">
					<div id="nameBox" class="suggBox"></div>
				</div>
			</div>
		</td>
		<td>
			<a href="javascript:editCustomer();">Add/Edit Customer</a>
		</td>
	</tr>
	<tr>
		<td><!-- Billing: --></td>
		<td colspan="2" id="addressBox">
			<br />
			<br />
			<br />
			<br />
		</td>
	</tr>
	<tr>
		<td><label for="caller">Caller:</label></td>
		<td colspan="2">
			<input type="text" id="caller" name="caller" size="31" maxlength="63" />
		</td>
	</tr>
	<tr>
		<td><label for="phone_id">Phone:</label></td>
		<td colspan="2" class="left">
			<select id="phone_id" name="phone_id" disabled="disabled">
				<option value="">Choose customer name for options</option>
			</select>
			&nbsp;<a href="javascript:cboard('phone');">C</a>
		</td>
	</tr>
	<tr>
		<td><label for="fax_id">Fax:</label></td>
		<td colspan="2" class="left">
			<select id="fax_id" name="fax_id" disabled="disabled">
				<option value=""></option>
			</select>
			&nbsp;<a href="javascript:cboard('fax');">C</a>
		</td>
	</tr>
	<tr>
		<td><label for="email_id">Email:</label></td>
		<td colspan="2" class="left">
			<select id="email_id" name="email_id" disabled="disabled">
				<option value="">Add/Edit Customer to change options</option>
			</select>
			&nbsp;<a href="javascript:cboard('email');">C</a>
		</td>
	</tr>
	<tr>
		<td><label for="terms">Terms:</label></td>
		<td colspan="2"><select id="terms" name="terms">
			<option value="credit">Credit Card</option>
			<option value="net30">Net 30</option>
			<option value="wire">Wire</option>
			<option value="cod">COD</option>
		</select></td>
	</tr>
	<tr>
		<td><label for="purchaseorder">P.O. No.:</label></td>
		<td colspan="2"><input type="text" id="purchaseorder" name="purchaseorder" size="31" maxlength="31" /></td>
	</tr>
	<tr>
		<td><label for="taxid">TaxID:</label></td>
		<td colspan="2"><input type="text" class="disabled" id="taxid" name="taxid" size="31" maxlength="31" disabled="disabled"/></td>
	</tr>
	<tr>
		<td><label for="notes"><span class="term">Office Notes:<span class="note">This will show as the first logged repair note for each item on the quote/invoice.</span></span></label></td>
		<td colspan="2"><textarea id="notes" name="notes" rows="<?=$tarows+1?>" cols="45"></textarea></td>
	</tr>
</table>
</div>
<div id="shipInv">
	<table>
		<tr>
			<td><label for="shipname">Ship to:</label></td>
			<td><input type="text" id="shipname" name="shipname" maxlength="63" size="31" tabindex="990" /></td>
		</tr>
		<tr>
			<td><label for="shipaddress">Shipping Address:</label></td>
			<td><input type="text" id="shipaddress" name="shipaddress" maxlength="63" size="31" tabindex="991" /></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="text" id="shipaddress1" name="shipaddress1" maxlength="63" size="31" tabindex="992" /></td>
		</tr>
		<tr>
			<td><label for="shipcity">City:</label></td>
			<td><input type="text" id="shipcity" name="shipcity" maxlength="63" size="31" tabindex="993" /></td>
		</tr>
		<tr>
			<td><label for="shipstate">State/Province:</label></td>
			<td><input type="text" id="shipstate" name="shipstate" maxlength="2" size="2" tabindex="994" /></td>
		</tr>
		<tr>
			<td><label for="shipzip">Postal Code:</label></td>
			<td><input type="text" id="shipzip" name="shipzip" maxlength="15" size="15" tabindex="995" /></td>
		</tr>
		<tr>
			<td><label for="shipcountry">Country:</label></td>
			<td><input type="text" id="shipcountry" name="shipcountry" maxlength="31" size="31" tabindex="996" /></td>
		</tr>
	</table>
</div>
<div id="quoteList">
<table>
	<thead><tr>
		<td><label for="modelnum0">*Part Number</label></td>
		<td><label for="type0"><span class="term">Service<span class="note">These can be set in master item management</span></span></label></td>
		<td><label for="description0">Description</label></td>
		<td><label for="quantity0">Qty</label></td>
		<td><label for="rate0">Rate</label></td>
		<td><label for="total0">Total</label></td>
		<td></td>
	</tr></thead>
	<tbody id="itemList">
	<!--<tr id="itemRow0">
		Item Rows are put here by JS.
	</tr> -->
	<tr id="lastRow">
		<td class="right"><label for="publicnotes"><span class="term">Public Notes:<span class="note">These notes will print on the customer copy of the quote/invoice.</span></span></label></td>
		<td colspan="5">
			<textarea id="publicnotes" name="publicnotes" rows="<?=$tarows+2?>" cols="72"></textarea>
		</td>
	</tr>
	<tr>
		<td></td>
		<td colspan="2">
			<input type="submit" value="Save" id="submit" name="submit" />
			<?php if ($qORi == 'quote'): ?>
			<input type="button" value="Create Invoice" id="createInv" name="createInv" onclick="makeInv();" />
			<?php endif; ?>
			<input type="button" value="Delete <?=($qORi == 'quote') ? 'Quote' : 'Invoice' ?>" id="delete" name="delete" onclick="deleteQuote();" />
		</td>
		<td colspan="3" class="right">
			<table style="width: 110%;">
				<tr>
					<td><input type="text" id="itemtotal" name="itemtotal" disabled="disabled" size="2" class="qtyInput" /> <label for="subtotal">Subtotal:</label></td>
					<td><input type="text" id="subtotal" name="subtotal" disabled="disabled" size="7" class="priceInput" /></td>
				</tr>
				<tr>
					<td><label for="taxamnt" id="taxname">Tax (<span id="taxpercent">00.0</span>%):</label></td>
					<td><input type="text" id="taxamnt" name="taxamnt" disabled="disabled" size="7" class="priceInput" /></td>
				</tr>
				<tr>
					<td><label for="total">Total:</label></td>
					<td><input type="text" id="total" name="total" disabled="disabled" size="7" class="priceInput" /></td>
				</tr>
			</table>
		</td>
	</tr>
	</tbody>
</table>
</div>
<div id="printPDF">
	<?php if ($qORi != 'quote'): ?>
	<select id="printType" name="printType" onchange="HtsCheck.check(); ">
		<option value="inv">Invoice</option>
		<option value="cncpslip">CNC P-Slip</option>
		<option value="custpslip">Cust P-Slip</option>
		<option value="rma">RMA</option>
		<option value="customsinvoice">US Customs Invoice</option>
	</select><br />
	<?php endif; ?>
	<input type="button" value="Save &amp; PDF" onclick="printPDF(<?=isset($quote_id) ? $quote_id : $inv_id ?>, 'print');" /><br />
	<input type="button" value="Save &amp; Email" onclick="printPDF(<?=isset ($quote_id) ? $quote_id : $inv_id ?>, 'email');" />
</div>

</form>