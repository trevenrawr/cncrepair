<script type="text/javascript" src="/js/item/additem.js"></script>
<script type="text/javascript" src="/js/item/itemlist.js"></script>
<form id="add_form" method="post" action="/item/add" onsubmit="return addItem('/item/add');">
<div id="hiddenInputs">
	<input type="hidden" id="itemtype_id" name="itemtype_id" />
	<input type="hidden" id="cleaningprocs" name="cleaningprocs" />
	<input type="hidden" id="repairprocs" name="repairprocs" />
	<input type="hidden" id="testingprocs" name="testingprocs" />
	<input type="hidden" id="hts" name="hts" />
</div>
<div id="itemSignature">
	<span id="cby">Created by: <span id="createdby" class="strong"></span>
	on <span id="created" class="strong"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
	<span id="eby">Last Edited by: <span id="editedby" class="strong"></span>
	at <span id="lastedited" class="strong"></span>
</div>
<div id="itemMain">
	<fieldset>
	<legend>Item Vitals:</legend>
	<table>
		<tr>
			<td><label for="modelnum">*Part #:</label></td>
			<td colspan="2">
				<input type="text" id="modelnum" name="modelnum" maxlength="63" size="31" />
				<div class="relPos">
					<div id="modelnumShadow" class="suggShadow">
						<div id="modelnumBox" class="suggBox"></div>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<td><label for="make">*Make:</label></td>
			<td colspan="2">
				<input type="text" id="make" name="make" maxlength="63" size="31" />
				<div class="relPos">
					<div id="makeShadow" class="suggShadow">
						<div id="makeBox" class="suggBox"></div>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<td><label for="madein"><span class="term">Made In:</span></label></td>
			<td colspan="2">
				<select name="madein" id="madein" >
					<option value="" selected="selected"></option>
					<option value="Japan">Japan</option>
					<option value="United States" >United States</option>
					<option value="Canada">Canada</option>
					<option value="United Kingdom">United Kingdom</option>
					<option value="Albania">Albania</option>
					<option value="Algeria">Algeria</option>
					<option value="American Samoa">American Samoa</option>
					<option value="Andorra">Andorra</option>
					<option value="Angola">Angola</option>
					<option value="Anguilla">Anguilla</option>
					<option value="Antarctica">Antarctica</option>
					<option value="Antigua and Barbuda">Antigua and Barbuda</option>
					<option value="Argentina">Argentina</option>
					<option value="Armenia">Armenia</option>
					<option value="Aruba">Aruba</option>
					<option value="Australia">Australia</option>
					<option value="Austria">Austria</option>
					<option value="Azerbaijan">Azerbaijan</option>
					<option value="Bahamas">Bahamas</option>
					<option value="Bahrain">Bahrain</option>
					<option value="Bangladesh">Bangladesh</option>
					<option value="Barbados">Barbados</option>
					<option value="Belarus">Belarus</option>
					<option value="Belgium">Belgium</option>
					<option value="Belize">Belize</option>
					<option value="Benin">Benin</option>
					<option value="Bermuda">Bermuda</option>
					<option value="Bhutan">Bhutan</option>
					<option value="Bolivia">Bolivia</option>
					<option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
					<option value="Botswana">Botswana</option>
					<option value="Bouvet Island">Bouvet Island</option>
					<option value="Brazil">Brazil</option>
					<option value="Brunei Darussalam">Brunei Darussalam</option>
					<option value="Bulgaria">Bulgaria</option>
					<option value="Burkina Faso">Burkina Faso</option>
					<option value="Burundi">Burundi</option>
					<option value="Cambodia">Cambodia</option>
					<option value="Cameroon">Cameroon</option>
					<option value="Cape Verde">Cape Verde</option>
					<option value="Cayman Islands">Cayman Islands</option>
					<option value="Central African Republic">Central African Republic</option>
					<option value="Chad">Chad</option>
					<option value="Chile">Chile</option>
					<option value="China">China</option>
					<option value="Christmas Island">Christmas Island</option>
					<option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
					<option value="Colombia">Colombia</option>
					<option value="Comoros">Comoros</option>
					<option value="Congo">Congo</option>
					<option value="Cook Islands">Cook Islands</option>
					<option value="Costa Rica">Costa Rica</option>
					<option value="Cote D'ivoire">Cote D'ivoire</option>
					<option value="Croatia">Croatia</option>
					<option value="Cuba">Cuba</option>
					<option value="Cyprus">Cyprus</option>
					<option value="Czech Republic">Czech Republic</option>
					<option value="Denmark">Denmark</option>
					<option value="Djibouti">Djibouti</option>
					<option value="Dominica">Dominica</option>
					<option value="Dominican Republic">Dominican Republic</option>
					<option value="Ecuador">Ecuador</option>
					<option value="Egypt">Egypt</option>
					<option value="El Salvador">El Salvador</option>
					<option value="Equatorial Guinea">Equatorial Guinea</option>
					<option value="Eritrea">Eritrea</option>
					<option value="Estonia">Estonia</option>
					<option value="Ethiopia">Ethiopia</option>
					<option value="Faroe Islands">Faroe Islands</option>
					<option value="Fiji">Fiji</option>
					<option value="Finland">Finland</option>
					<option value="France">France</option>
					<option value="French Guiana">French Guiana</option>
					<option value="French Polynesia">French Polynesia</option>
					<option value="Gabon">Gabon</option>
					<option value="Gambia">Gambia</option>
					<option value="Georgia">Georgia</option>
					<option value="Germany">Germany</option>
					<option value="Ghana">Ghana</option>
					<option value="Gibraltar">Gibraltar</option>
					<option value="Greece">Greece</option>
					<option value="Greenland">Greenland</option>
					<option value="Grenada">Grenada</option>
					<option value="Guadeloupe">Guadeloupe</option>
					<option value="Guam">Guam</option>
					<option value="Guatemala">Guatemala</option>
					<option value="Guinea">Guinea</option>
					<option value="Guinea-bissau">Guinea-bissau</option>
					<option value="Guyana">Guyana</option>
					<option value="Haiti">Haiti</option>
					<option value="Honduras">Honduras</option>
					<option value="Hong Kong">Hong Kong</option>
					<option value="Hungary">Hungary</option>
					<option value="Iceland">Iceland</option>
					<option value="India">India</option>
					<option value="Indonesia">Indonesia</option>
					<option value="Iraq">Iraq</option>
					<option value="Ireland">Ireland</option>
					<option value="Israel">Israel</option>
					<option value="Italy">Italy</option>
					<option value="Jamaica">Jamaica</option>
					<option value="Jordan">Jordan</option>
					<option value="Kazakhstan">Kazakhstan</option>
					<option value="Kenya">Kenya</option>
					<option value="Kiribati">Kiribati</option>
					<option value="Korea, Republic of">Korea, Republic of</option>
					<option value="Kuwait">Kuwait</option>
					<option value="Kyrgyzstan">Kyrgyzstan</option>
					<option value="Latvia">Latvia</option>
					<option value="Lebanon">Lebanon</option>
					<option value="Lesotho">Lesotho</option>
					<option value="Liberia">Liberia</option>
					<option value="Liechtenstein">Liechtenstein</option>
					<option value="Lithuania">Lithuania</option>
					<option value="Luxembourg">Luxembourg</option>
					<option value="Macao">Macao</option>
					<option value="Madagascar">Madagascar</option>
					<option value="Malawi">Malawi</option>
					<option value="Malaysia">Malaysia</option>
					<option value="Maldives">Maldives</option>
					<option value="Mali">Mali</option>
					<option value="Malta">Malta</option>
					<option value="Marshall Islands">Marshall Islands</option>
					<option value="Martinique">Martinique</option>
					<option value="Mauritania">Mauritania</option>
					<option value="Mauritius">Mauritius</option>
					<option value="Mayotte">Mayotte</option>
					<option value="Mexico">Mexico</option>
					<option value="Moldova, Republic of">Moldova, Republic of</option>
					<option value="Monaco">Monaco</option>
					<option value="Mongolia">Mongolia</option>
					<option value="Montserrat">Montserrat</option>
					<option value="Morocco">Morocco</option>
					<option value="Mozambique">Mozambique</option>
					<option value="Myanmar">Myanmar</option>
					<option value="Namibia">Namibia</option>
					<option value="Nauru">Nauru</option>
					<option value="Nepal">Nepal</option>
					<option value="Netherlands">Netherlands</option>
					<option value="Netherlands Antilles">Netherlands Antilles</option>
					<option value="New Caledonia">New Caledonia</option>
					<option value="New Zealand">New Zealand</option>
					<option value="Nicaragua">Nicaragua</option>
					<option value="Niger">Niger</option>
					<option value="Nigeria">Nigeria</option>
					<option value="Niue">Niue</option>
					<option value="Norfolk Island">Norfolk Island</option>
					<option value="Northern Mariana Islands">Northern Mariana Islands</option>
					<option value="Norway">Norway</option>
					<option value="Oman">Oman</option>
					<option value="Pakistan">Pakistan</option>
					<option value="Palau">Palau</option>
					<option value="Panama">Panama</option>
					<option value="Papua New Guinea">Papua New Guinea</option>
					<option value="Paraguay">Paraguay</option>
					<option value="Peru">Peru</option>
					<option value="Philippines">Philippines</option>
					<option value="Pitcairn">Pitcairn</option>
					<option value="Poland">Poland</option>
					<option value="Portugal">Portugal</option>
					<option value="Puerto Rico">Puerto Rico</option>
					<option value="Qatar">Qatar</option>
					<option value="Reunion">Reunion</option>
					<option value="Romania">Romania</option>
					<option value="Russian Federation">Russian Federation</option>
					<option value="Rwanda">Rwanda</option>
					<option value="Saint Helena">Saint Helena</option>
					<option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
					<option value="Saint Lucia">Saint Lucia</option>
					<option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
					<option value="Samoa">Samoa</option>
					<option value="San Marino">San Marino</option>
					<option value="Sao Tome and Principe">Sao Tome and Principe</option>
					<option value="Saudi Arabia">Saudi Arabia</option>
					<option value="Senegal">Senegal</option>
					<option value="Serbia and Montenegro">Serbia and Montenegro</option>
					<option value="Seychelles">Seychelles</option>
					<option value="Sierra Leone">Sierra Leone</option>
					<option value="Singapore">Singapore</option>
					<option value="Slovakia">Slovakia</option>
					<option value="Slovenia">Slovenia</option>
					<option value="Solomon Islands">Solomon Islands</option>
					<option value="Somalia">Somalia</option>
					<option value="South Africa">South Africa</option>
					<option value="Spain">Spain</option>
					<option value="Sri Lanka">Sri Lanka</option>
					<option value="Sudan">Sudan</option>
					<option value="Suriname">Suriname</option>
					<option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
					<option value="Swaziland">Swaziland</option>
					<option value="Sweden">Sweden</option>
					<option value="Switzerland">Switzerland</option>
					<option value="Syrian Arab Republic">Syrian Arab Republic</option>
					<option value="Taiwan, Province of China">Taiwan, Province of China</option>
					<option value="Tajikistan">Tajikistan</option>
					<option value="Tanzania, United Republic of">Tanzania, United Republic of</option>
					<option value="Thailand">Thailand</option>
					<option value="Timor-leste">Timor-leste</option>
					<option value="Togo">Togo</option>
					<option value="Tokelau">Tokelau</option>
					<option value="Tonga">Tonga</option>
					<option value="Trinidad and Tobago">Trinidad and Tobago</option>
					<option value="Tunisia">Tunisia</option>
					<option value="Turkey">Turkey</option>
					<option value="Turkmenistan">Turkmenistan</option>
					<option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
					<option value="Tuvalu">Tuvalu</option>
					<option value="Uganda">Uganda</option>
					<option value="Ukraine">Ukraine</option>
					<option value="United Arab Emirates">United Arab Emirates</option>
					<option value="United Kingdom">United Kingdom</option>
					<option value="United States">United States</option>
					<option value="United States Minor Outlying Islands">US Minor Outlying Islands</option>
					<option value="Uruguay">Uruguay</option>
					<option value="Uzbekistan">Uzbekistan</option>
					<option value="Vanuatu">Vanuatu</option>
					<option value="Venezuela">Venezuela</option>
					<option value="Viet Nam">Viet Nam</option>
					<option value="Virgin Islands, British">Virgin Islands, British</option>
					<option value="Virgin Islands, U.S.">Virgin Islands, U.S.</option>
					<option value="Wallis and Futuna">Wallis and Futuna</option>
					<option value="Western Sahara">Western Sahara</option>
					<option value="Yemen">Yemen</option>
					<option value="Zambia">Zambia</option>
					<option value="Zimbabwe">Zimbabwe</option>
				</select>

			</td>
		</tr>
		<tr>
			<td><label for="htsview"><span class="term">Item HTS Code:</span></label></td>
			<td colspan="2">
				<input type="text" id="htsview" name="htsview" maxlength="63" size="31" readonly="readonly"  ondblclick="HtsCodes();" />
			</td>
		</tr>
		<tr>
			<td><label for="assembly"><span class="term">Assembly?:<span class="note">A new item must be saved before it's assembly parts can be edited.</span></span></label></td>
			<td>
				<input type="checkbox" id="assembly" name="attributes[]" value="assembly" onclick="enableAssem();" /></td>
			<td><input type="button" onclick="edit('assem');" id="editAssemLink" value="Edit Assembly Items" disabled="disabled" />
			</td>
		</tr>
		<tr>
			<td><label for="quickbooks"><span class="term">Quickbooks?:</span></label></td>
			<td><input type="checkbox" id="quickbooks" name="quickbooks" /></td>
		</tr>

	</table>
	</fieldset>

	<fieldset>
	<legend>Pricing Particulars:</legend>
	<table>
		<tr>
			<td><label for="exch">Exchangeable?:</label></td>
			<td><input type="checkbox" id="exch" name="attributes[]" value="exch" onchange="ratesCheck();" /></td>
			<td><label for="exchrate">Rate ($):</label></td>
			<td><input type="text" id="exchrate" name="exchrate" maxlength="10" size="10" /></td>
		</tr>
		<tr>
			<td><label for="repair">Repairable?:</label></td>
			<td><input type="checkbox" id="repair" name="attributes[]" value="repair" onchange="ratesCheck();" /></td>
			<td><label for="repairrate">Rate ($):</label></td>
			<td><input type="text" id="repairrate" name="repairrate" maxlength="10" size="10" /></td>
		</tr>
		<tr>
			<td><label for="sale">Sale Item?:</label></td>
			<td><input type="checkbox" id="sale" name="attributes[]" value="sale" onchange="ratesCheck();" /></td>
			<td><label for="salerate">Price ($):</label></td>
			<td><input type="text" id="salerate" name="salerate" maxlength="10" size="10" /></td>
		</tr>
		<tr>
			<td><label for="value">Value ($):</label></td>
			<td colspan="2"><input type="text" id="value" name="value" maxlength="10" size="10" /></td>
		</tr>
	</table>
	</fieldset>

	<fieldset>
	<legend>Shipping / Inventory Specs:</legend>
	<table>
		<tr>
			<td><label for="weight">Weight (lbs):</label></td>
			<td><input type="text" id="weight" name="weight" maxlength="10" size="10" /></td>
		</tr>
		<tr>
			<td><label for="dimensions">Dimensions (in):</label></td>
			<td><input type="text" id="dimensions" name="dimensions" maxlength="10" size="10" /></td>
		</tr>
		<tr>
			<td><label for="onhand">Quantity On Hand:</label></td>
			<td><input type="text" id="onhand" name="onhand" maxlength="10" size="10" disabled="disabled" /></td>
		</tr>
		<tr>
			<td><label for="onhold">Quantity On Hold:</label></td>
			<td><input type="text" id="onhold" name="onhold" maxlength="10" size="10" disabled="disabled" /></td>
		</tr>
		<tr>
			<td><label for="inuse">Num. In Assemblies:</label></td>
			<td><input type="text" id="inuse" name="inuse" maxlength="10" size="10" disabled="disabled" /></td>
		</tr>
	</table>
	</fieldset>
</div>

<div id="itemTAs">
	<table>
		<tr>
			<td><label for="description">Description:</label></td>
			<td><label for="details">Office Notes:</label></td>
		</tr>
		<tr>
			<td><textarea id="description" name="description" rows="<?=$tarows?>" cols="32"></textarea></td>
			<td><textarea id="details" name="details" rows="<?=$tarows?>" cols="32"></textarea></td>
		</tr>
		<tr>
			<td colspan="2"><label for="packing">Packing Notes (and other information customers should know):</label></td>
		</tr>
		<tr>
			<td colspan="2"><textarea id="packing" name="packing" rows="<?=$tarows?>" cols="68"></textarea></td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="button" value="Edit Cleaning Procedures" onclick="edit('cleaningprocs');" />
				<input type="button" value="Edit Repair Procedures" onclick="edit('repairprocs');" />
				<input type="button" value="Edit Testing Procedures" onclick="edit('testingprocs');" />
			</td>
		</tr>
	</table>
</div>

<div id="itemSubmit">
	<input type="submit" value="Add Item" id="submit" name="submit" />
	<input type="button" value="Reset Form" name="goBlank" onclick="window.location.href = '/item/';" />
	<input type="button" value="Delete Item" id="deleteButton" onclick="confirmDelete();" style="visibility: hidden;" />
</div>

<div>
	<table id="itemsTable"  style="display: none;">
		<thead>
			<tr class="head">
				<td>Serial No:</td>
				<td>Barcode:</td>
				<td>Location:</td>
				<td>Last Seen:</td>
				<td>Status:</td>
				<td class="viewBox">View:</td>
			</tr>
		</thead>
		<tbody id="itemList">
		</tbody>
	</table>
</div>
</form>