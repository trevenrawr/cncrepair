<?php

/**
 * MAKE SURE YOU READ OUR QUICK-START GUIDE:
 * 	http://wiki.consolibyte.com/wiki/doku.php/quickbooks_integration_php_consolibyte_webconnector_quickstart
 * 	http://wiki.consolibyte.com/wiki/doku.php/quickbooks
 *
 * @thanks Keith Palmer <keith@consolibyte.com>
 */

if (function_exists('date_default_timezone_set')) {
	date_default_timezone_set('America/Vancouver');
}

// Include path for the QuickBooks library
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . './');
error_reporting(E_ALL | E_STRICT);


// If you're having trouble with performance or memory usage, you can tell the
//	framework to only include certain chunks of itself:
require_once 'QuickBooks.php';

// A username and password you'll use in:
//	a) Your .QWC file
//	b) The Web Connector
//	c) The QuickBooks framework
$user = 'cncrepair';
$pass = 'Vitamin-L';

// The next three parameters, $map, $errmap, and $hooks, are callbacks which
//	will be called when certain actions/events/requests/responses occur within
//	the framework. The examples below show how to register callback
//	*functions*, but you can actually register any of the following, using
//	these formats:

// Callback functions

// Map QuickBooks actions to handler functions
$map = array(
	QUICKBOOKS_QUERY_CUSTOMER => array( '_quickbooks_request', '_quickbooks_customer_get_response' ),
	QUICKBOOKS_ADD_CUSTOMER => array( '_quickbooks_request', '_quickbooks_customer_add_response' ),
	QUICKBOOKS_MOD_CUSTOMER => array( '_quickbooks_request', '_quickbooks_customer_mod_response' ),
	QUICKBOOKS_QUERY_ITEM => array( '_quickbooks_request', '_quickbooks_item_get_response' ),
	QUICKBOOKS_ADD_SERVICEITEM => array( '_quickbooks_request', '_quickbooks_item_add_response' ),
	QUICKBOOKS_MOD_SERVICEITEM => array( '_quickbooks_request', '_quickbooks_item_mod_response' ),
	QUICKBOOKS_ADD_INVENTORYITEM => array( '_quickbooks_request', '_quickbooks_item_add_response' ),
	QUICKBOOKS_MOD_INVENTORYITEM => array( '_quickbooks_request', '_quickbooks_item_mod_response' ),
	QUICKBOOKS_ADD_NONINVENTORYITEM => array( '_quickbooks_request', '_quickbooks_item_add_response' ),
	QUICKBOOKS_MOD_NONINVENTORYITEM => array( '_quickbooks_request', '_quickbooks_item_mod_response' ),
	QUICKBOOKS_QUERY_INVOICE => array( '_quickbooks_request', '_quickbooks_invoice_get_response' ),
	QUICKBOOKS_ADD_INVOICE => array( '_quickbooks_request', '_quickbooks_invoice_add_response' ),
	QUICKBOOKS_MOD_INVOICE => array( '_quickbooks_request', '_quickbooks_invoice_mod_response' )
);

// This is entirely optional, use it to trigger actions when an error is returned by QuickBooks
$errmap = array(
	3070 => '_quickbooks_error_stringtoolong',
	3100 => '_quickbooks_error_alreadyexists',
	500 => '_quickbooks_error_nosuchitem',
	1 => '_quickbooks_error_nosuchitem',
	'*' => '_quickbooks_error_catchall',
);

// An array of callback hooks
$hooks = array(
	// QUICKBOOKS_HANDLERS_HOOK_LOGINSUCCESS => '_quickbooks_hook_loginsuccess', 	// Run this function whenever a successful login occurs
);

/*
function _quickbooks_hook_loginsuccess($requestID, $user, $hook, &$err, $hook_data, $callback_config)
{
	// Do something whenever a successful login occurs...
}
*/

// Logging level
//$log_level = QUICKBOOKS_LOG_NORMAL;
//$log_level = QUICKBOOKS_LOG_VERBOSE;
// $log_level = QUICKBOOKS_LOG_DEBUG;
$log_level = QUICKBOOKS_LOG_DEVELOP;		// Use this level until you're sure everything works!!!

// What SOAP server you're using
//$soapserver = QUICKBOOKS_SOAPSERVER_PHP;		// The PHP SOAP extension, see: www.php.net/soap
$soapserver = QUICKBOOKS_SOAPSERVER_BUILTIN;		// A pure-PHP SOAP server (no PHP ext/soap extension required, also makes debugging easier)

$soap_options = array(		// See http://www.php.net/soap
	);

$handler_options = array(
	'map_application_identifiers' => false
	);		// See the comments in the QuickBooks/Server/Handlers.php file

$driver_options = array(		// See the comments in the QuickBooks/Driver/<YOUR DRIVER HERE>.php file ( i.e. 'Mysql.php', etc. )
	'max_log_history' => 512,	// Limit the number of quickbooks_log entries to 1024
	'max_queue_history' => 64, 	// Limit the number of *successfully processed* quickbooks_queue entries to 64
	);

$callback_options = array(
	);

$dsn = 'mysql://cncrepair:sausage13@localhost/quickbooks';

if (!QuickBooks_Utilities::initialized($dsn)) {
	// Initialize creates the neccessary database schema for queueing up requests and logging
	QuickBooks_Utilities::initialize($dsn);

	// This creates a username and password which is used by the Web Connector to authenticate
	QuickBooks_Utilities::createUser($dsn, $user, $pass);
}

// Create a new server and tell it to handle the requests
// __construct($dsn_or_conn, $map, $errmap = array(), $hooks = array(), $log_level = QUICKBOOKS_LOG_NORMAL, $soap = QUICKBOOKS_SOAPSERVER_PHP, $wsdl = QUICKBOOKS_WSDL, $soap_options = array(), $handler_options = array(), $driver_options = array(), $callback_options = array()
$Server = new QuickBooks_Server($dsn, $map, $errmap, $hooks, $log_level, $soapserver, QUICKBOOKS_WSDL, $soap_options, $handler_options, $driver_options, $callback_options);
$response = $Server->handle(true, true);


function log_soap_server() {
	global $response;
	$file = fopen('logs/soap_log'.date('m-d-y'), "a+");
	fwrite($file, $response."\n\n");
	fclose($file);
	if ( '0771' !== fileperms('logs/soap_log'.date('m-d-y')))
	{
		chmod('logs/soap_log'.date('m-d-y'), 0771);
	}
}
log_soap_server();

// If you wanted, you could do something with $response here for debugging

/**
 * @param string $requestID					You should include this in your qbXML request (it helps with debugging later)
 * @param string $action					The QuickBooks action being performed (CustomerAdd in this case)
 * @param mixed $ID							The unique identifier for the record (maybe a customer ID number in your database or something)
 * @param array $extra						Any extra data you included with the queued item when you queued it up
 * @param string $err						An error message, assign a value to $err if you want to report an error
 * @param integer $last_action_time			A unix timestamp (seconds) indicating when the last action of this type was dequeued (i.e.: for CustomerAdd, the last time a customer was added, for CustomerQuery, the last time a CustomerQuery ran, etc.)
 * @param integer $last_actionident_time	A unix timestamp (seconds) indicating when the combination of this action and ident was dequeued (i.e.: when the last time a CustomerQuery with ident of get-new-customers was dequeued)
 * @param float $version					The max qbXML version your QuickBooks version supports
 * @param string $locale
 * @return string							A valid qbXML request
 */


// The $extra is used to bring in the QBXML as generated by the model file
function _quickbooks_request($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale) {
	$qbxml = $extra[$action];

	$prexml = '<?xml version="1.0" encoding="utf-8"?>
		<?qbxml version="CA3.0"?>
		<QBXML>
			<QBXMLMsgsRq onError="continueOnError">';
	$postxml = '		</QBXMLMsgsRq>
		</QBXML>';

	qb_log_message('QBXML properly formatted, request ('.$requestID.') for '.$action.', target '.$extra['action'].', sent!');
	qb_log_message("qbxml request: \n".$qbxml);

	return $prexml.$qbxml.$postxml;
}

/*----------------------------------- Customer Response Handlers -------------------------------------------*/

// Called to update the customer information after it is returned from QB
function _updateCustomer($action, $xml, $ID) {
	qb_log_message('Saving received customer data to DB...');
	$xml = substr($xml, strpos($xml, '<CustomerRet>'));
	$xml = substr($xml, 0, strpos($xml, '</'.$action.'Rs>'));

	qb_log_message('Creating QB_Object_Customer from input...');
	$cust = QuickBooks_Object_Customer::fromQBXML($xml);
	$bal = ($cust->getTotalBalance()) ? $cust->getTotalBalance() : '0.00';
	$creditlimit = ($cust->getCreditLimit()) ? $cust->getCreditLimit() : '0.00';

	qb_log_message('Opening MySQL link to update customers...');
	$link = mysql_connect('localhost', 'cncrepair', 'sausage13');
	if (!$link) {die(qb_log_message('Could not connect: ' . mysql_error()));}
	$db_selected = mysql_select_db('cncrepair');

	// The ID may be seeded with a random string so as to make sure each queued item has a unique $requestID
	$ID = preg_replace('/[a-z]/', '', strtolower($ID));

	$query = 'UPDATE customers SET qbrefnum="'.$cust->getListID().
		'", qbeditsequence="'.$cust->getEditSequence().
		'", balance="'.$bal.'", creditlimit="'.$creditlimit.
		'", qblastupdate=CURRENT_TIMESTAMP(), qbqueued=0 WHERE id='.$ID;
	qb_log_message('MySQL link opened!  Running query: '.$query);

	mysql_query($query);
	mysql_close($link);

	qb_log_message('Customer qbrefnum, qbeditsequence, and balance updated!');
	return $cust;
}

// Handles the response from an add requests
function _quickbooks_customer_add_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
	qb_log_message($action.' (add) response ('.$requestID.') received!');
	// qb_log_message("qbxml:\n".$xml);
	_updateCustomer($action, $xml, $ID);
}

// Handle the response from a query request; also queues a mod action if noted in $extra as the 'action' target.
function _quickbooks_customer_get_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
	// qb_log_message("qbxml:\n".$xml);
	qb_log_message($action.' (query) response received ('.$requestID.')! Target action = '.$extra['action']);
	$custIn = _updateCustomer($action, $xml, $ID);

	// If a MOD was the 'action' queued and the QUERY was just run
	qb_log_message($action.' completed, '.$extra['action'].' was originally requested...');

	if ($action != $extra['action']) {
		$extra[$extra['action']] = preg_replace('/\<EditSequence\>.*\<\/EditSequence\>/', '<EditSequence>'.$custIn->getEditSequence().'</EditSequence>', $extra[$extra['action']], 1);
		$extra[$extra['action']] = preg_replace('/\<ListID\>.*\<\/ListID\>/', '<ListID>'.$custIn->getListID().'</ListID>', $extra[$extra['action']], 1);

		$queue = new QuickBooks_Queue('mysql://cncrepair:sausage13@localhost/quickbooks');

		$queue->enqueue($extra['action'], $ID, 5, $extra);
		qb_log_message($extra['action'].' request queued!');
	}
}

function _quickbooks_customer_mod_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
	qb_log_message($action.' (mod) response ('.$requestID.') received!');
	// qb_log_message("qbxml:\n".$xml);
	_updateCustomer($action, $xml, $ID);
}

/*----------------------------------- Item(type) Response Handlers -------------------------------------------*/

// Called to update the item information after it is returned from QB
function _updateItem($action, $xml, $ID) {
	qb_log_message('Saving received item data to DB...');

	// Determine the quickbooks item type (service, inventory, noninventory):
	$qbtype = substr($xml, strpos($xml, 'Ret>')-17, 17);
	$qbtype = substr($qbtype, strpos($qbtype, '<')+1);
	qb_log_message('Item qbtype determined to be: '.$qbtype.'!  Trimming QBXML for object creation...');

	$xml = substr($xml, strpos($xml, '<'.$qbtype.'Ret>'));
	$xml = substr($xml, 0, strpos($xml, '</'.$action.'Rs>'));
	$qbtype = str_replace('item', '', strtolower($qbtype));

	qb_log_message('Creating QB_Object ('.$qbtype.') from input...');
	// qb_log_message("qbxml:\n".$xml);
	switch ($qbtype) {
		case ('inventory'):
			$item = QuickBooks_Object_InventoryItem::fromQBXML($xml);
			break;
		case ('noninventory'):
			$item = QuickBooks_Object_NonInventoryItem::fromQBXML($xml);
			break;
		case ('service'):
		default:
			$item = QuickBooks_Object_ServiceItem::fromQBXML($xml);
			break;
	}
	qb_log_message('Item created as '.$item->object().'!');

	qb_log_message('Opening MySQL link to update itemtypes and make_map...');
	$link = mysql_connect('localhost', 'cncrepair', 'sausage13');
	if (!$link) {die(qb_log_message('Could not connect: ' . mysql_error()));}
	$db_selected = mysql_select_db('cncrepair');
// 	$acctref = '';
	// The ID is seeded with a random string so as to make sure each queued item has a unique $requestID
	$ID = preg_replace('/[a-z]/', '', strtolower($ID));

	qb_log_message('MySQL link opened!  Running itemtypes query...');
	if ($qbtype != 'inventory') {
		$acctref = $item->getAccountName();			// use to be .=  .  not sure why.  the variable isn't declared before.
	} else {
		$acctref = $item->getIncomeAccountName();
	}
	$query = 'UPDATE itemtypes SET qbrefnum="'.$item->getListID().
		'", qbeditsequence="'.$item->getEditSequence().
		'", qbaccountref="'.$acctref.
		'", qbtype="'.$qbtype.
		'" WHERE id='.$ID;
	qb_log_message('Query : '.$query);
	mysql_query($query);

	qb_log_message('MySQL link opened!  Running make_map query...');
	$query = 'INSERT INTO make_map (make, qbparentref) VALUES ("'.$item->getParentName().'", "'.$item->getParentListID().'")
		ON DUPLICATE KEY UPDATE qbparentref="'.$item->getParentListID().'"';
	qb_log_message('Query : '.$query);
	mysql_query($query);

	mysql_close($link);
	qb_log_message('Item qbrefnum, qbeditsequence, qbaccountref, and qbtype updated!');
	return $item;
}

// If a QUERY was run before a MOD, this will then queue the MOD request
function _quickbooks_item_get_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
	qb_log_message($action.' (query) response ('.$requestID.') received!');
	// qb_log_message("qbxml:\n".$xml);
	$itemIn = _updateItem($action, $xml, $ID);

	if ($action != $extra['action']) {
		$extra[$extra['action']] = preg_replace('/\<EditSequence\>.*\<\/EditSequence\>/', '<EditSequence>'.$itemIn->getEditSequence().'</EditSequence>', $extra[$extra['action']], 1);
		$extra[$extra['action']] = preg_replace('/\<ListID\>.*\<\/ListID\>/', '<ListID>'.$itemIn->getListID().'</ListID>', $extra[$extra['action']], 1);

		$queue = new QuickBooks_Queue('mysql://cncrepair:sausage13@localhost/quickbooks');

		$queue->enqueue($extra['action'], $ID, 5, $extra);
		qb_log_message($extra['action'].' request queued!');
	}
}

function _quickbooks_item_add_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
	qb_log_message($action.' (add) response ('.$requestID.') received!');
	// qb_log_message("qbxml:\n".$xml);
	_updateItem($action, $xml, $ID);
}

function _quickbooks_item_mod_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
	qb_log_message($action.' (mod) response ('.$requestID.') received!');
	// qb_log_message("qbxml:\n".$xml);
	_updateItem($action, $xml, $ID);
}

/*----------------------------------- Invoice Response Handlers -------------------------------------------*/

function _updateInvoice($action, $xml, $ID, $idents) {
	qb_log_message('Opening MySQL link to update invoices...');
	$link = mysql_connect('localhost', 'cncrepair', 'sausage13');
	if (!$link) {die(qb_log_message('Could not connect: ' . mysql_error()));}
	$db_selected = mysql_select_db('cncrepair');

	$ID = preg_replace('/[a-z]/', '', strtolower($ID));

	qb_log_message('MySQL link opened!  Running update query on invoices...');
	$query = 'UPDATE invoices SET qbrefnum="'.$idents['TxnID'].'", qbeditsequence="'.$idents['EditSequence'].'" WHERE id='.$ID;
	qb_log_message('query: '.$query);
	mysql_query($query);
	mysql_close($link);

	qb_log_message('Invoice qbrefnum and qbeditsequence updated!');
}

function _quickbooks_invoice_get_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
	qb_log_message($action.' (query) response ('.$requestID.') received! Target action = '.$extra['action']);
	_updateInvoice($action, $xml, $ID, $idents);
	qb_log_message('Comparing '.$action.' with '.$extra['action']);
	if ($action != $extra['action']) {
		$extra[$extra['action']] = preg_replace('/\<EditSequence\>.*\<\/EditSequence\>/', '<EditSequence>'.$idents['EditSequence'].'</EditSequence>', $extra[$extra['action']], 1);

		$queue = new QuickBooks_Queue('mysql://cncrepair:sausage13@localhost/quickbooks');

		$queue->enqueue($extra['action'], $ID, 2, $extra);
		qb_log_message($extra['action'].' request queued!');
	}
}

function _quickbooks_invoice_add_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
	qb_log_message($action.' (add) response ('.$requestID.') received!');
	_updateInvoice($action, $xml, $ID, $idents);
}

function _quickbooks_invoice_mod_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents) {
	qb_log_message($action.' (mod) response ('.$requestID.') received!');
	_updateInvoice($action, $xml, $ID, $idents);
}

/**
 * @param string $requestID
 * @param string $action
 * @param mixed $ID
 * @param mixed $extra
 * @param string $err
 * @param string $xml
 * @param mixed $errnum
 * @param string $errmsg
 * @return void
 */
function _quickbooks_error_stringtoolong($requestID, $user, $action, $ID, $extra, &$err, $xml, $errnum, $errmsg) {
	qb_log_message('QuickBooks thinks that ' . $action . ': ' . $ID . ' has a value which will not fit in a QuickBooks field...');
}

function _quickbooks_error_alreadyexists($requestID, $user, $action, $ID, $extra, &$err, $xml, $errnum, $errmsg) {
	qb_log_message($action.' attempted to (probably) add something that already exists.  Queueing a QUERY/MOD operation instead...');

	$ID = preg_replace('/[a-z]/', '', strtolower($ID));

	$queue = new QuickBooks_Queue('mysql://cncrepair:sausage13@localhost/quickbooks');

	if ($action == QUICKBOOKS_ADD_CUSTOMER) {
		// Change the target operation, and queue the QUERY action
		$extra['action'] = QUICKBOOKS_MOD_CUSTOMER;
		qb_log_message('Queueing for QUERY->MOD with $extra: '.print_r($extra, true));
		$queue->enqueue(QUICKBOOKS_QUERY_CUSTOMER, $ID, 5, $extra);

	// If it's one of the many item ADD requests
	} else if ($action == QUICKBOOKS_ADD_SERVICEITEM || $action == QUICKBOOKS_ADD_INVENTORYITEM || $action == QUICKBOOKS_ADD_NONINVENTORYITEM) {
		switch ($action) {
		case (QUICKBOOKS_ADD_SERVICEITEM):
			$act = QUICKBOOKS_MOD_SERVICEITEM;
			break;
		case (QUICKBOOKS_ADD_INVENTORYITEM):
			$act = QUICKBOOKS_MOD_INVENTORYITEM;
			break;
		case (QUICKBOOKS_ADD_NONINVENTORYITEM):
			$act = QUICKBOOKS_MOD_NONINVENTORYITEM;
			break;
		}

		$extra['action'] = $act;
		qb_log_message('Queueing for QUERY->MOD with $extra: '.print_r($extra, true));
		$queue->enqueue(QUICKBOOKS_QUERY_ITEM, $ID, 5, $extra);
	}

	// Continue to process requests
	return true;
}

function _quickbooks_error_nosuchitem($requestID, $user, $action, $ID, $extra, &$err, $xml, $errnum, $errmsg) {
	qb_log_message($action.' returned no results...');

	$queue = new QuickBooks_Queue('mysql://cncrepair:sausage13@localhost/quickbooks');

	// If this was a query before something else, and not just a query to query:
	//For customers:
	if ($action != QUICKBOOKS_QUERY_CUSTOMER && $extra['action'] != $action) {
		$extra['action'] = QUICKBOOKS_ADD_CUSTOMER;
		qb_log_message('Queueing for ADD with $extra: '.print_r($extra, true));
		$queue->enqueue(QUICKBOOKS_ADD_CUSTOMER, $ID, 5, $extra);

	// For items:
	} else if ($action == QUICKBOOKS_QUERY_ITEM && $extra['action'] != QUICKBOOKS_QUERY_ITEM) {
		switch ($extra['action']) {
		case (QUICKBOOKS_MOD_SERVICEITEM):
			$act = QUICKBOOKS_ADD_SERVICEITEM;
			break;
		case (QUICKBOOKS_MOD_INVENTORYITEM):
			$act = QUICKBOOKS_ADD_INVENTORYITEM;
			break;
		case (QUICKBOOKS_MOD_NONINVENTORYITEM):
			$act = QUICKBOOKS_ADD_NONINVENTORYITEM;
			break;
		}

		$extra['action'] = $act;
		qb_log_message('Queueing for ADD with $extra: '.print_r($extra, true));
		$queue->enqueue($act, $ID, 5, $extra);
	}

	// Continue to process requests
	return true;
}

function _quickbooks_error_catchall($requestID, $user, $action, $ID, $extra, &$err, $xml, $errnum, $errmsg) {
	qb_log_message('Error number '.dump($errnum).': '.dump($errmsg));
}

