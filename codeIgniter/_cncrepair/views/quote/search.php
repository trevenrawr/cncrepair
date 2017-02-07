<?php if (!defined('BASEPATH')) exit('No direct script access allowed');?>
<script type="text/javascript" src="/js/quote/quotesearch.js"></script>
<form id="add_form" method="post" action="/cnc/nojs" onsubmit="return false;">
<div id="hiddenInputs">
	<input type="hidden" id="cust_id" name="cust_id" />
	<input type="hidden" id="currRow" name="currRow" value="0" />
</div>
<div id="quoteSearch">
<table>
	<tr>
		<td>
			<select id="searchTerm" name="searchTerm" onchange="criteriaChange(this.value);">
				<option value="name">Customer Name:</option>
				<option value="created">Date Created:</option>
				<option value="total">Total:</option>
				<option value="purchaseorder">P.O. No.:</option>
				<option value="refnum">Reference No.:</option>
				<option value="modelnum">Part #:</option>
			</select>
		</td>
		<td id="suggestInputArea">
			<input type="text" id="name" name="name" maxlength="31" size="31"/>
			<div class="relPos">
				<div id="nameShadow" class="suggShadow">
					<div id="nameBox" class="suggBox"></div>
				</div>
			</div>
			<input type="text" id="created" name="created" maxlength="31" size="31" style="display:none;"/>
			<input type="text" id="total" name="total" maxlength="31" size="31" style="display:none;"/>
			<input type="text" id="purchaseorder" name="purchaseorder" maxlength="31" size="31" style="display:none;"/>
			<input type="text" id="refnum" name="refnum" maxlength="31" size="31" style="display:none;"/>
			<input type="text" id="modelnum" name="modelnum" maxlength="31" size="31" style="display:none;"/>
			<div class="relPos">
				<div id="modelnumShadow" class="suggShadow">
					<div id="modelnumBox" class="suggBox"></div>
				</div>
			</div>
		</td>
		<td><input type="submit" id="searchHistory" value="Search" onclick="showHistory();"/></td>
	</tr>
</table>
<table id="itemsTable">
	<thead>
		<tr class="head">
			<td>Customer Name:</td>
			<td><span class="term">Ref. No.:<span class="note">Search just for the number itself</span></span></td>
			<td>P.O. No.:</td>
			<td><span class="term">Date Created:<span class="note">Search for "Date Created" in the listed format (eg. "Nov 14" or "Oct").</span></span></td>
			<td>Created by:</td>
			<td>Emailed:</td>
			<td class="cblist">No. of Items:</td>
			<td>Total:</td>
		</tr>
	</thead>
	<tbody id="searchList">
	<?php require_once '../codeIgniter/_cncrepair/views/quote/history.php';?>
	</tbody>
</table>
</div>
</form>