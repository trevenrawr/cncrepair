<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Purpose: Invoice/Quote/PSlip html view. Usually converted to pdf with dompdf or khtmltopdf.
 *
 * Info:  Invoice/quote data comes from getinvoice/getquote controllers.
 *
 * Author: Micah Leuba
 */


// This returns the relevant phone/fax/email info from complete customer info
function pOReInfo($info, $pORe, $id) {
	foreach ($info['customer'][$pORe] as $temp) {
		if ($temp['id'] == $id) {
			return $temp;
		}
	}
}

// Keeps track of whether we're printing a quote or an invoice/RMA/pslip
$qORi = 'inv';
if (strrpos($view, 'quote') !== false) $qORi = 'quote';

// Contains the "title" based on the "view"
$views = array('quoterepair' => 'Quote',
	'quoteexch' => 'Quote',
	'quotesale' => 'Quote',
	'inv' => 'Invoice',
	'rma' => 'Return Authorization',
	'custpslip' => 'Packing Slip',
	'cncpslip' => 'CNC PSlip',
	'customsinvoice' => 'US Customs Invoice');

// selects the correct phone/fax/email info
$phone = pOReInfo($info, 'phones', $info[$qORi]['phone_id']);
$fax = pOReInfo($info, 'phones', $info[$qORi]['fax_id']);
$email = pOReInfo($info, 'emails', $info[$qORi]['email_id']);

// Display values for the terms of the quote/invoice
$terms = array('credit' => 'CC',
	'net30' => 'Net-30',
	'wire' => 'Wire',
	'cod' => 'COD');

// Sets the terms
$info[$qORi]['terms'] = $terms[$info[$qORi]['terms']];

// Alters the "service" column values
$service = array('repair' => 'Your Item Rebuilt',
	'exch' => 'Rebuilt Exchange',
	'sale' => 'Sale');
// Test for Canadian or US/International Condition
$caORam = ($info['customer']['currency'] == 'United States of America Dollar' ) ? 'US' : 'CA';

// set subtotal, taxrate, and taxtype variables for invoices/quotes with taxes
if ($view != 'rma' && $view != 'cncpslip' && $view != 'custpslip') {
		if ($info[$qORi]['taxamnt'] != 0) {
			$taxconditon = 'set';
		}
		$subtotal = $info[$qORi]['total'] - $info[$qORi]['taxamnt'];
		$taxrate = $info[$qORi]['taxrate']*100.0;
		$taxtype = (isset($info[$qORi]['taxtype']) ) ? $info[$qORi]['taxtype'] : '' ;
		$dollar = ($info['customer']['currency'] == 'United States of America Dollar') ? 'USD$' : 'CA$';
}
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
<style type="text/css">

.half-width {
	width: 50%;
}
.bold {
	font-weight: bold;
}
.full-width {
	width: 100%;
}
.auto-width {
	width: auto;
}
.center {
	text-align: center;
}
.left {
	text-align: left;
}
.right {
	text-align: right;
}
.serif {
	font-family: serif;
}
h1 {
	text-align: right;
	font-weight: bold;
	font-size: 2em;
	font-family: serif;
	margin: 0em 1em 0em 0em;
}
table {
	border-spacing: 0px;
	border-collapse: collapse;
/* 	empty-cells: show; */
}
#main {
	position: relative;
	width: 100%;
	margin: 5px auto;
	font-family: sans-serif;
	font-size: 11pt;
}
#main #title tr td {
	background-color: rgb(238, 238, 238);
	border-bottom-color: rgb(0, 0, 0);
	border-bottom-style: solid;
	border-bottom-width: 1px;
	border-top-color: rgb(0, 0, 0);
	border-top-style: solid;
	border-top-width: 1px;
	width: 50%;

}
/* Cnc-repair Information */
#cncrepair-info {
	position: relative;
}
#main #cncrepair-info tr td {
	margin: 0;
	padding: 0;

}
/**************************************/
/* Customer shipping and billing information */
#customer {
	position: relative;
	width: 100%;
	margin-top: .6%;
}
#billto {
	width: 45%;
	float: left;
/* 	border: 1px dashed black; */
	border-radius: 2em;
	background-color: #ffffff;
	border: solid 1px;
	border-collapse: separate;
	margin:  0 0 0 2%;
	padding: 1%;
}
#shipto {
	width: 45%;
	float: right;
	border-radius: 2em;
	background-color: #fffffff;
	border: solid 1px;
	border-collapse: separate;
	margin: 0 2% 0 0;
	padding: 1%;
}
/*******************************************/
/*Customer info box (PO NUMBER, TERMS)*/
#customer-info {
	position: relative;
	margin-top: .6%;
}
#customer-info table td {
/* 	border: solid 1px black; */
	border-left: dashed 1px black;
	border-right: dashed 1px black;
	text-align: center;
}
#customer-info th {
	background-color: rgb(238, 238, 238);
	border-top: solid 1px black;
	font-family: serif;
}
/************************************/
/* Item table styling           */
#item-list {
	position: relative;
	margin-top: .5%;
}
#item-list table {
	border-bottom: dashed 1px black;

}
#item-list th {
	border-top: solid 1px black;
	background-color: rgb(238, 238, 238);
	font-family: serif;
}

#item-list tr td {
	border-left: dashed 1px black;
	border-right: dashed 1px black;
	padding: .5%;
}
#item-list td:first-child {
	font-family: serif;
	width: 15%;
	text-align: center;
}
#item-list td:nth-of-type(4) {
	text-align: center;
}
#item-list td:nth-of-type(5) {
	text-align: right;
}
#item-list td:nth-of-type(6) {
	text-align: right;
}
/**************************************/
/* Total box table styling */
#total-box {
	position: relative;
}
#total-box table {
	float: right;
	width: 35%;
	margin: 0;
	border-top: none;
	border-bottom: solid 1px black;
/*	border-right: solid 1px black;
	border-left: solid 1px black;*/
	font-family: serif;
}
#total-box td {
	padding: 1%;
	border-left: dashed 1px black;
	border-right: dashed 1px black;
}
#total-box tr td:first-child {
	text-align: left;
	font-weight: bold;
}
#total-box tr:last-child td:first-child {
	font-size: 1.3em;
}
#total-box tr td:last-child {
	text-align: right;
	padding: 1%;
}
/*************************************/

</style>
<!-- HTML STARTS HERE -->
<?php
if ( isset($html) ) {
	$directory = "http://localhost/pics/drawing.png";
} else {
	$directory = getcwd()."/pics/drawing.png";
}
?>

</head>

<body>

<div id="main">

<!-- TITLE BAR SECTION -->
<div id ="title" >
	<table class="full-width" >
		<tbody>
			<tr>
				<td style="vertical-align: middle;"><img src="<?=$directory;?>" alt="PICTURE LOGO" /></td>
				<td class="right"><h1><?=$views[$view]?> <?php if ($view == 'rma') echo 'RMA'?></h1>

			</tr>
		</tbody>
	</table>
</div>

<!-- CNCREPAIR INFO TABLE BELOW TITLE BAR -->
<div id="cncrepair-info">
	<table class="full-width" style="font-size: .7em;">
		<tbody class=" ">
			<?php switch ($caORam) {
				case 'CA': ?>
					<tr>
						<td style="width: 25%; ">DBA 4064208 Canada Inc.</td>
						<td style="width: 25%; ">Phone: 604-888-9050</td>
						<td style="width: 33%; "><?=$hours?></td>
						<td class="bold" style="width: 17%; "><?=$views[$view]?># <?=isset($quote_id) ? $quote_id : $inv_id ?><?php if ($view == 'rma') echo 'RMA'; ?></td>
					</tr>
					<tr>
						<td>22030 102 Ave.</td>
						<td>Fax: 866-313-6101</td>
						<td>www.cncrepair.ca</td>
						<td><?=$info[$qORi]['date']?></td>
					</tr>
					<tr>
						<td>Langley, BC Canada V1M 3V3</td>
						<td>Email: sales@cncrepair.com</td>
						<td>US Tax ID#: 98-0414520</td>
						<td></td>
					</tr>
					<?php break;
				default: ?>
					<tr>
						<td style="width: 25%; ">1770 Front St. #142</td>
						<td style="width: 25%; ">Phone: 408-331-1970</td>
						<td style="width: 33%; "><?=$hours?></td>
						<td class="bold" style="width: 17%; "><?=$views[$view]?># <?=isset($quote_id) ? $quote_id : $inv_id ?><?php if ($view == 'rma') echo 'RMA'; ?></td>
					</tr>
					<tr>
						<td>Lynden, WA 98264</td>
						<td>Fax: 866-313-6101</td>
						<td>www.cncrepair.com</td>
						<td><?=$info[$qORi]['date']?></td>
					</tr>
					<tr>
						<td>US Tax ID#: 98-0414520</td>
						<td>Email: sales@cncrepair.com</td>
						<td>4064208 Canada Inc.</td>
						<td></td>
					</tr>
				<?php } ?>

		</tbody>
	</table>
</div>

<!-- CUSTOMER SHIP AND BILL TO INFORMATION -->
<div id="customer">
	<table id="billto">
		<tbody>
			<tr>
				<td class="bold center serif">Bill To:</td>
				<td class="left" ><?=$info['customer']['name']?></td>
			</tr>
			<tr >
				<td><!-- Billing: --></td>
				<td class="left">
					<?=($info['customer']['address'] != '') ? $info['customer']['address'] : '';?><br />
					<?=($info['customer']['address1'] != '') ? $info['customer']['address1'] : '';?><br />
					<?=($info['customer']['city'] != '') ? $info['customer']['city'].', ' : '';?><?=$info['customer']['state'].' '.$info['customer']['zip']?><br />
					<?=$info['customer']['country']?>
				</td>
			</tr>
		</tbody>
	</table>
	<table id="shipto">
		<tbody>
			<tr>
				<td class="bold center serif">Ship To:</td>
				<td class="left"><?=$info['customer']['name']?></td>
			</tr>
			<tr>
				<td><!--Shipping: --></td>
				<td class="left">
					<?=$info[$qORi]['shipaddress']?><br />
					<?=($info[$qORi]['shipaddress1'] != '') ? $info[$qORi]['shipaddress1'] : '';?><br />
					<?=($info[$qORi]['shipcity'] != '') ? $info[$qORi]['shipcity'].', ' : '';?><?=$info[$qORi]['shipstate'].' '.$info[$qORi]['shipzip']?><br />
					<?=$info[$qORi]['shipcountry']?>
				</td>
			</tr>
		</tbody>
	</table>
	<div style="clear:both;"></div>
</div>

<!-- CUSTOMER INORMATION TABLE -->
<div id="customer-info">
	<table class="full-width">
		<thead>
			<tr>
				<th>Buyer</th>
				<th>P.O No.</th>
				<th>Terms</th>
				<th>Email</th>
				<th>Fax</th>
				<th>Phone</th>
				<th>TaxID</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?=(isset($info[$qORi]['caller']) ? $info[$qORi]['caller'] : '' ); ?></td>
				<td><?=$info[$qORi]['purchaseorder'];?></td>
				<td><?=$info[$qORi]['terms'];?></td>
				<td><?=((isset($email['name'])) ? $email['name'] : '').' '.(($email['email'] != '') ? '&lt;'.$email['email'].'&gt;' : '');?></td>
				<td><?=$fax['num']?></td>
				<td><?=((isset($phone['num'])) ? $phone['num'] : '').' '.(($phone['contact'] != '') ? '('.$phone['contact'].')' : '');?></td>
				<td><?=(isset($info[$qORi]['taxid']) ?  $info[$qORi]['taxid'] : '' );?></td>

			</tr>
		</tbody>
	</table>
</div>

<!-- ITEM LIST TABLE.  CONTAINS DATA RELATIVE TO DOCUMENT TYPE -->
<div id="item-list" >
	<table class="full-width">
		<thead>
			<tr>
				<th>Part #</th>
				<th>Description</th>
				<?php if ($view != 'rma' && $view != 'cncpslip' && $view != 'custpslip'): ?>
				<th>Service</th>
				<?php endif; ?>
				<th >Qty</th>
				<?php if ($view != 'rma' && $view != 'cncpslip' && $view != 'custpslip'): ?>
				<th>Rate</th>
				<th>Total</th>
				<?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php $colour = false;
			foreach ($info['items'] as $item):
			if ($item['print'] != 'subitem'):
			if ($colour == true) {
				echo '<tr style="background-color: rgb(236, 236, 236); ">';
			} else { echo '<tr>'; } ?>
				<td><?=$item['modelnum']?></td>
				<td>
					<?=$item['description']?>
					<?=($item['packing'] != '') ? '<br />'.$item['packing'] : '';?>
					<?//=($item['officenotes'] != '') ? '<br />'.$item['officenotes'] : '';?><br />
					<?php if (($item['type'] == 'repair' && $view == 'quote') || $view == 'rma') : ?>
						<strong>Item Serial: _______________________</strong>
					<?php endif; ?>
					<?php if( $view == 'customsinvoice' && isset($item['hts']) ) : ?>
						<br /><strong>ITEM MADE IN___<?=$item['madein']?>___</strong>
						<br />
						<br /><strong>HTC___<?=$item['hts']?>___BASE ITEM</strong>
						<br /><strong>HTC___9802.00.40_______LABOR IN CANADA</strong>
						<br />
						<br /><strong>WE CERTIFY THAT THIS DECLARATION IS TRUE AND CORRECT.</strong>
					<?php endif; ?>
				</td>
				<?php if ($view != 'rma' && $view != 'custpslip' && $view != 'cncpslip'): ?>
				<td><?=$service[$item['type']]?></td>
				<?php endif; ?>
				<td><?=$item['quantity']?></td>
				<?php if ($view != 'rma' && $view != 'custpslip' && $view != 'cncpslip'): ?>
				<td><?=$item['rate']?></td>
				<td><?=number_format($item['rate'] * $item['quantity'], 2)?></td>
				<?php endif; ?>
			</tr>
			<?php endif; $colour = ! $colour;  endforeach; ?>
		</tbody>
	</table>
</div>

<!-- AMOUNT DUE TABLE -->
<?php if ($view != 'rma' && $view != 'custpslip' && $view != 'cncpslip'): ?>
<div id="total-box">
	<table>
		<tbody>
			<?php if ( isset($taxconditon) ) : ?>
				<tr>
					<td>Subtotal</td>
					<td><?=$subtotal?></td>
				</tr>
				<tr>
					<td><?=$taxtype?> (%<?=$taxrate?>)</td>
					<td><?=$info[$qORi]['taxamnt'];?></td>
				</tr>
			<?php endif; ?>
				<tr>
					<td>Total</td>
					<td><?=$dollar?> <?=$info[$qORi]['total']?></td>
				</tr>

		</tbody>
	</table>
</div>
<?php endif; ?>

<!-- PUBLIC NOTES ON INVOICE -->
<div id="pubnotes">
	<?php if ($info[$qORi]['publicnotes'] != '' && $info[$qORi]['publicnotes'] != 'null'): ?>
	<p><strong>Notes:</strong><br />
	<?=$info[$qORi]['publicnotes']?></p>
	<?php endif; ?>
</div>


<div id="boilerplate">
	<?=$message?>
	<?php if( $view == 'customsinvoice' ) : ?>
		<br />
		<br />
		<strong><p style="text-align: right; font-size: 1.4em;">Total FOR USA CUSTOMS: <?=$dollar?> <?=$info[$qORi]['total']?><p></strong>
	<?php endif; ?>
</div>


</body>
</html>