<?php

/**
 * Example of building qbXML requests using the QuickBooks_Object_* classes
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 *
 * @package QuickBooks
 * @subpackage Documentation
 */ 

// include path
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . '/Users/kpalmer/Projects/QuickBooks');

// error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL | E_STRICT);

// QuickBooks framework classes
require_once 'QuickBooks.php';

// 
require_once 'QuickBooks/API.php';

// Create the new invoice object
$inv = new QuickBooks_Object_Invoice();

// We need to assign this invoice to a customer. There are a few ways you can 
// refer to this customer in your qbXML requests. You can refer to them by 
// 	their Name/FullName, by their ListID (a primary key within QuickBooks) or, 
//	if you've created a mapping between the customer's primary key within your 
//	application and the customer in QuickBooks *and* you are using the 
//	QuickBooks_API classes, you can refer to them by the primary key within 
//	your application, and the framework will map this value to a ListID for 
//	you. (For an example of this last case you should see 
//	example_api_client.php and example_api_client_canadian.php) 
// 
$inv->setCustomerName('The Company Name Here');
// $inv->setCustomerListID($ListID_from_QuickBooks);

// Invoice #A-123
$inv->setRefNumber('A-123');

// Set some other fields... 
$inv->setMemo('This invoice was created using the QuickBooks PHP API!');

// Now, we need to build each invoice line for the invoice. Each invoice line 
//	will contain at least a reference to an item, and probably a quantity or 
//	the item ordered, and either a total amount or a rate (price per item). 
// 
// As with customers above, the items need to be present in QuickBooks before 
//	we can add an invoice that depends on them. You can again refer to the item 
//	in three different ways: 
//	- Name/FullName 
//	- ListID
//	- a mapped primary key from your application
// 
// 	For this example, we're going to refer to the items by name, so the items 
//	must already be present in QuickBooks for this invoice to be added. 

// 3 items of type "Item Type 1" at $10.00 per item
$invLine1 = new QuickBooks_Object_Invoice_InvoiceLine();
$invLine1->setItemName('Item Type 1');
$invLine1->setRate(10.00);
$invLine1->setQuantity(3);

// 5 items of type "Item Type 2", for a total amount of $225.00 ($45.00 each)
$invLine2 = new QuickBooks_Object_Invoice_InvoiceLine();
$invLine2->setItemName('Item Type 2');
$invLine2->setAmount(225.00);
$invLine2->setQuantity(5);

// Make sure you add those invoice lines on to the invoice
$inv->addInvoiceLine($invLine1);
$inv->addInvoiceLine($invLine2);

// NOTE: These next few lines *only work for QuickBooks Online Edition* 

// Let's add a discount also  
//$DiscountLine = new QuickBooks_Object_Invoice_DiscountLine();
//$DiscountLine->setAmount(-125.0);
//$inv->addDiscountLine($DiscountLine);

// And a shipping charge
//$ShippingLine = new QuickBooks_Object_Invoice_ShippingLine();
//$ShippingLine->setAmount(5.0);
//$inv->addShippingLine($ShippingLine);

// Convert that object to qbXML
//
// It is important to note that the ->asQBXML method *does not* currently take 
//	into account field length limits or data types from the qbXML 
//	specification, you'll need to do that yourself. Support for this may be 
//	added in the future. For now, you can use the QuickBooks_Cast::cast() 
//	method to cast values to the correct type and length before setting them 
//	in the object. 
// 
// $value = 'Keith Palmer';
// $xpath = 'Name';
// // Makes sure the $value will fit in a QuickBooks Customer Name field
// $Name = QuickBooks_Cast::cast(QUICKBOOKS_OBJECT_CUSTOMER, $xpath, $value);
// 
// $value = '56 Cowles Road';
// $xpath = 'ShipAddress/Addr1';
// Makes sure the $value will fit in a QuickBooks Customer ShipAddress/Addr1 field
// $ShipAddress_Addr1 = QuickBooks_Cast::cast(QUICKBOOKS_OBJECT_CUSTOMER, $xpath, $value);

$qbxml = $inv->asQBXML(QUICKBOOKS_ADD_INVOICE);
print($qbxml);

// Prints the following XML: 
/*
<InvoiceAddRq>
	<InvoiceAdd>
		<CustomerRef>
			<FullName>The Company Name Here</FullName>
		</CustomerRef>
		<RefNumber>A-123</RefNumber>
		<Memo>This invoice was created using the QuickBooks PHP API!</Memo>
		<InvoiceLineAdd>
			<ItemRef>
				<FullName>Item Type 1</FullName>
			</ItemRef>
			<Quantity>3</Quantity>
			<Rate>10</Rate>
		</InvoiceLineAdd>
		<InvoiceLineAdd>
			<ItemRef>
				<FullName>Item Type 2</FullName>
			</ItemRef>
			<Quantity>5</Quantity>
			<Amount>225.00</Amount>
		</InvoiceLineAdd>
	</InvoiceAdd>
</InvoiceAddRq>
*/ 

//
//print_r($Invoice->asList(QUICKBOOKS_ADD_CUSTOMER));
