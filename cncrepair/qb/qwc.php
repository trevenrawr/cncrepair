<?php

/**
 * @thanks Keith Palmer <keith@consolibyte.com>
 */

ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . '/Users/kpalmer/Projects/QuickBooks/');
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

/**
 * Require the utilities class
 */
require_once 'QuickBooks/Frameworks.php';
define('QUICKBOOKS_FRAMEWORKS', QUICKBOOKS_FRAMEWORK_WEBCONNECTOR);
require_once 'QuickBooks.php';

$name = 'CNC Repair Database';		// A name for your server (make it whatever you want)
$descrip = 'Self Explanitory';		// A description of your server 

$appurl = 'https://cncdb/qb/index.php';		// This *must* be httpS:// (path to your QuickBooks SOAP server)
$appsupport = 'https://cncdb/'; 		// This *must* be httpS:// and the domain name must match the domain name above

$username = 'cncrepair';		// This is the username you stored in the 'quickbooks_user' table by using QuickBooks_Utilities::createUser()

$fileid = '6904A826-7368-11DC-8317-F7AD55D89593';		// Just make this up, but make sure it keeps that format
$ownerid = '6904A826-7368-11DC-8317-F7AD55D89513';		// Just make this up, but make sure it keeps that format

$qbtype = QUICKBOOKS_TYPE_QBFS;	// You can leave this as-is unless you're using QuickBooks POS

$readonly = false; // No, we want to write data to QuickBooks

$run_every_n_seconds = 300; // Run every n seconds

// Generate the XML file
$QWC = new QuickBooks_QWC($name, $descrip, $appurl, $appsupport, $username, $fileid, $ownerid, $qbtype, $readonly, $run_every_n_seconds);
$xml = $QWC->generate();

// Send as a file download
header('Content-type: text/xml');
header('Content-Disposition: attachment; filename="cncdb.qwc"');
print($xml);
exit;