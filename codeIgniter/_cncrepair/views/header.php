<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
$pos = $this->user_model->getPos();?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title><?=$title?> - CNC Repair Database</title>
<meta name="description" content="CNC Repair Database" />
<meta name="keywords" content="cncrepair, database, addcustomer" />
<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
<link rel="stylesheet" href="/css/cnc.css" type="text/css" />
<link rel="stylesheet" href="/css/dropdown.css" type="text/css" />
<link rel="icon" type="image/png" href="/pics/favicon.png" />
<!--<script type="text/javascript" src="https://www.google.com/jsapi?key=ABQIAAAAQTZ0eDcbWpg6rJodQEF4cBT2yXp_ZAY8_ufC3CFXhHIE1NvwkxTUf8ISFExAKeQjBZVqwXqk2Todmg"></script>
<script type="text/javascript">google.load("jquery", "1.5.1");</script>-->
<script type="text/javascript" src="/js/httpObj.js"></script>
<script type="text/javascript" src="/js/tbtlib.js"></script>
<script type="text/javascript" src="/js/suggest.js"></script>
</head>

<body onload="<?php if($bodyOnload) echo $bodyOnload; ?>">
<?php if (!isset($load['nomenu'])): ?>
<div id="banner"> 
<span id="logo"><a href="/"><img src="/pics/logo.jpg" width="100" height="100" alt="T-Bag Tech: CNC Repair" /></a></span>
<span id="title">CNC Repair Database</span>
<span id="username"><?php if ($this->session->userdata('user') != '') echo 'Welcome, ' . $this->session->userdata('name'); ?></span>
<div id="menu">
	<ul class="dropdown dropdown-horizontal">
		<li><a href="/">Home</a></li>
		<?php if ($pos['office'] !== false): ?>
		<li><a href="#" class="dir">Manage</a>
			<ul>
				<?php if ($pos['office'] !== false): ?>
				<li><a href="/item">Master Items</a></li>
				<li><a href="/item/specific">Specific Items</a></li>
				<?php endif; ?>
				<li><a href="/customer">Customers</a></li>
			</ul>
		</li>
		<?php endif; ?>
		<?php if ($pos['office'] !== false): ?>
		<li><a href="#" class="dir">Office</a>
			<ul>
				<li><a href="/quote">Create Quote</a></li>
				<li><a href="/quote/search">Customer History</a></li>
				<li><a href="/invoice">Invoice Tool</a></li>
				<li><a href="/quote/messages">Mng. Messages</a></li>
				<li><a href="/item/oweditemlist">Owed Item List</a><li>
			</ul>
		</li>
		<?php endif; ?>
		<?php if ($pos['receiving'] || $pos['cleaning'] || $pos['repair'] || $pos['shipping']): ?>
		<li><a href="#" class="dir">Positions</a>
			<ul>
				<?php if ($pos['unpacking'] !== false): ?>
				<li><a href="/positions/unpacking">Unpacking</a></li>
				<?php endif;
					if ($pos['receiving'] !== false): ?>
				<li><a href="/positions/receiving">Receiving</a></li>
				<?php endif;
					if ($pos['cleaning'] !== false): ?>
				<li><a href="/positions/cleaning">Cleaning</a></li>
				<?php endif;
					if ($pos['repair'] !== false): ?>
				<li><a href="/positions/repair">Repair</a></li>
				<?php endif;
					if ($pos['assembling'] !== false): ?>
				<li><a href="/positions/assembling">Assembling</a></li>
				<?php endif;
					if ($pos['testing'] !== false): ?>
				<li><a href="/positions/testing">Testing</a></li>
				<?php endif;
					if ($pos['shipping'] !== false): ?>
				<li><a href="/positions/shipping">Shipping</a></li>
				<?php endif; ?>
			</ul>
		</li>
		<?php endif; ?>
		<?php if ($this->session->userdata('user') != ''): ?>
		<li><a href="#" class="dir">Information</a>
			<ul>
				<li><a href="/item/typeList">Part Listing</a></li>
				<li><a href="/item/fullList">Item Listing</a></li>
				<li><a href="/positions/viewQueue">View Work Queue</a></li>
			</ul>
		</li>
		<li><a href="/cnc/logout" class="dir">Logout</a>
			<ul>
				<li><a href="/cnc/manage">Manage Account</a></li>
				<?php if ($pos['boss'] !== false): ?>
				<li><a href="/cnc/acctmanage">User Privileges</a></li>
				<?php endif; ?>
			</ul>
		</li>
		<?php else: ?>
		<li><a href="/cnc/login" class="dir">Login</a>
			<ul>
				<li><a href="/cnc/account">Create Account</a></li>
			</ul>
		</li>
		<?php endif; ?>
	</ul>
</div>
</div>
<? endif; ?>
<div id="<?=(isset($load['skinny'])) ? 'skinny' : 'main' ?>"><!-- Main div -->