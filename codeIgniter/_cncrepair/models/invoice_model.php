<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

// Require the QB Queueing class
require_once './qb/QuickBooks/Frameworks.php';
if (!defined('QUICKBOOKS_FRAMEWORKS'))
	define('QUICKBOOKS_FRAMEWORKS', QUICKBOOKS_FRAMEWORK_QUEUE | QUICKBOOKS_FRAMEWORK_OBJECTS);
require_once './qb/QuickBooks.php';

class Invoice_model extends CI_Model {

	function Invoice_model() {
		parent::__construct();
	}

	function create($quote_id) {
		// Gets information regarding the quote to copy over.
		$query = $this->db->query('SELECT * FROM quotes WHERE id='.$quote_id);
		$d = $query->row_array();
		$query->free_result();

		unset($d['id']);
		unset($d['created']);

		// Unset certain items so that null values get stored as default (NULL)
		$unsets = array('phone_id', 'fax_id', 'email_id');
		foreach ($unsets as $unset) {
			if ($d[$unset] == '') unset($d[$unset]);
		}

		$this->db->trans_start();

		// Update the last activity of the customer
		$this->db->query('UPDATE customers SET qblastactivity=CURRENT_TIMESTAMP() WHERE id='.$d['billto']);

		// Create invoice
		$query = 'INSERT INTO invoices ('.implode(', ', array_keys($d)).') VALUES ("'.implode('", "', $d).'")';
		$this->db->query($query);
		$inv_id = $this->db->insert_id();

		// Copy quote items to new invoice items
		$this->db->query('INSERT INTO invoiceitems (inv_id, itemtype_id, print, type, quantity, rate, description, officenotes) SELECT '.$inv_id.
			', itemtype_id, print, type, quantity, rate, description, officenotes FROM quoteitems WHERE quoteitems.quote_id='.$quote_id);
		$rows_affected = $this->db->affected_rows();
		if ($rows_affected == 0)
			$this->trans_rollback();

		// We'll need a list of invoiceitems later to make some updates elsewhere in the database
		$iitems = $this->db->query('SELECT itemtype_id, id, type, quantity FROM invoiceitems WHERE inv_id='.$inv_id);
		$iitems = $iitems->result_array();

		// Update histories to reflect the invoiceitems
		$qitems = $this->db->query('SELECT * FROM quoteitems WHERE quote_id='.$quote_id);
		$qitems = $qitems->result_array();
		foreach ($qitems as $qitem) {
			$histories = $this->db->query(
				'SELECT quoteitem_items.history_id AS id, histories.item_id
				FROM quoteitem_items INNER JOIN histories ON quoteitem_items.history_id=histories.id
				WHERE quoteitem_items.quoteitem_id='.$qitem['id']
			);
			$histories = $histories->result_array();

			// There could be multiple qitem/history pairs and all of them will be relevant
			if (isset($histories[0])) {
				$ii_id = 'NULL';
				// Since, at quoting, the itemtype ids have to match, the itemtype_id from the history will be sufficient to find the invoiceitem we want
				foreach ($iitems as $iitem) {
					// Make sure that the itemtypes match, as well as the "service" performed
					if ($iitem['itemtype_id'] == $qitem['itemtype_id'] && $iitem['type'] == $qitem['type']) {
						$ii_id = $iitem['id'];
						break;
					}
				}
				// Loop through each history and update it with the relevant invoiceitem_id, and update that item's onhold status
				foreach ($histories as $history) {
					$old_iitem_id = $this->db->query('SELECT ship_invoiceitem_id, ship_inv_id FROM history_log WHERE id='.$history['id']);
					$old_iitem_id = $old_iitem_id->row_array();
					if ($old_iitem_id['ship_invoiceitem_id'] != '' && $old_iitem_id['ship_invoiceitem_id'] != $ii_id) {
						$mess['message'] = 'One of the items on this quote is already promised to Invoice #'.$old_iitem_id['ship_inv_id'].".\nInvoice NOT created.";
						$mess['ok'] = 0;
						return $mess;
					}
					$this->db->query('UPDATE histories SET ship_invoiceitem_id='.$ii_id.' WHERE id='.$history['id']);
					$this->db->query('UPDATE items SET onhold=2 WHERE id='.$history['item_id']);
				}
			}
		}

		// Invoiced items put a 'hold' on something in the shop
		foreach ($iitems as $itemtype_id) {
			$this->db->query('UPDATE itemtypes SET onhold=onhold+'.$itemtype_id['quantity'].' WHERE id='.$itemtype_id['itemtype_id']);
		}

		$this->db->query('UPDATE invoices SET createdby='.$this->session->userdata('id').', editedby='.$this->session->userdata('id').', lastedited=CURRENT_TIMESTAMP() WHERE id='.$inv_id);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$mess['message'] = 'Invoice creation FAILED.';
			$mess['ok'] = 0;
			return $mess;
		}

		$mess['ok'] = 1;
		$mess['target'] = '/invoice/index/'.$inv_id;
		if ($rows_affected == 0) {
			$mess['message'] = 'Quote contained no items, invoice not created.';
			$mess['ok'] = 0;
		} else if ($rows_affected == 1) {
			$mess['message'] = 'Invoice #'.$inv_id.' created containing 1 line item!';
			$this->queueAddInvoice($inv_id);
		} else if ($rows_affected > 1) {
			$mess['message'] = 'Invoice #'.$inv_id.' created containing '.$rows_affected.' line items!';
			$this->queueAddInvoice($inv_id);
		}
		return $mess;
	}

	function getID() {
		$query = $this->db->query('SELECT Auto_increment FROM information_schema.tables WHERE table_name="invoices"');
		$data = $query->row_array();
		$query->free_result();
		return $data['Auto_increment'];
	}

	function save() {
		// Make $_POST into a MySQL escaped string
		foreach ($_POST as &$value) {
			$value = $this->db->escape_str($value);
		} unset($value);

		$inv_id = $_POST['inv_id'];

		if ($inv_id == $this->getID()) {
			return 'You must create invoices from quotes.';
		}

		$d = array (
			'shipaddress' => $this->input->post('shipaddress'),
			'shipaddress1' => $this->input->post('shipaddress1'),
			'shipcity' => $this->input->post('shipcity'),
			'shipstate' => $this->input->post('shipstate'),
			'shipcountry' => $this->input->post('shipcountry'),
			'shipzip' => $this->input->post('shipzip'),
			'billto' => $this->input->post('cust_id'),
			'purchaseorder' => $this->input->post('purchaseorder'),
			'terms' => $this->input->post('terms'),
			'caller' => $this->input->post('caller'),
			'phone_id' => $this->input->post('phone_id'),
			'fax_id' => $this->input->post('fax_id'),
			'email_id' => $this->input->post('email_id'),
			'total' => $this->input->post('total'),
			'notes' => $this->input->post('notes'),
			'publicnotes' => $this->input->post('publicnotes'),
			'itemtotal' => $this->input->post('itemtotal')
		);

		foreach ($d as $k => $t) {
			if ($d[$k] == '')
				unset($d[$k]);
		} unset($k); unset($t);

		$update = array();
		foreach ($d as $k => $v)
			$update[$k] = $k.'="'.$v.'"';
		unset($k); unset($v);

		$items = $this->_readInvitems($inv_id);

		$this->db->trans_start();

		// Update the last activity of the customer
		$this->db->query('UPDATE customers SET qblastactivity=CURRENT_TIMESTAMP() WHERE id='.$d['billto']);

		// Remove deleted invoiceitems
		if ($this->input->post('itemsDel') != '') {
			$toDels = explode('|', $this->input->post('itemsDel'));
			foreach ($toDels as $toDel) {
				if ($toDel != '') {
					// Remove the onhold count from the itemtype
					$this->db->query('UPDATE itemtypes SET onhold=onhold-1 WHERE id=(SELECT itemtype_id FROM invoiceitems WHERE id='.$toDel.')');
					$this->db->query('UPDATE histories SET ship_invoiceitem_id=NULL WHERE ship_invoiceitem_id='.$toDel);
					// Only delete if it's marked for deletion and if it is associated with the current invoice (just in case)
					$this->db->query('DELETE FROM invoiceitems WHERE id='.$toDel.' AND inv_id='.$inv_id);
				}
			}
		}

		// Make sure that NULL phone, fax, and email ids get set as such
		if (!isset($d['phone_id'], $d['fax_id'], $d['email_id'])) {
			$pid = (isset($d['phone_id'])) ? $d['phone_id'] : 'NULL';
			$fid = (isset($d['fax_id'])) ? $d['fax_id'] : 'NULL';
			$eid = (isset($d['email_id'])) ? $d['email_id'] : 'NULL';
			$this->db->query('UPDATE invoices SET phone_id='.$pid.', fax_id='.$fid.', email_id='.$eid.' WHERE id='.$inv_id);
		}

		// Insert/Update invoiceitems
		foreach ($items as $item) {
			if ($item['itemtype_id'] != '') {
				$old_item_ids = $item['old_item_ids']; unset($item['old_item_ids']);
				$item_ids = $item['item_ids']; unset($item['item_ids']);

				// Get rid of any item ids that haven't changed as the old_item_ids will get purged later and item_ids don't need to be re-set.
				foreach ($old_item_ids as $old_item_key => $old_item_id) {
					foreach ($item_ids as $item_key => $item_id) {
						if ($item_id == $old_item_id) {
							unset($old_item_ids[$old_item_key]);
							unset($item_ids[$item_key]);
							break;
						}
					}
				}

				$u = array();
				foreach ($item as $k => $v)
					$u[$k] = $k.'="'.$v.'"';
				unset($k); unset($v);

				$query = 'INSERT INTO invoiceitems ('.implode(', ', array_keys($item)).') '.
					'VALUES ("'.implode('", "', $item).'") '.
					'ON DUPLICATE KEY UPDATE '.implode(', ', array_values($u));
				$this->db->query($query);

				$ra = $this->db->affected_rows();
				if (($ii_id = $this->db->insert_id()) == 0) $ii_id = $item['id'];

				// Update the histories to reflect the items being locked into shipping on this invoice
				foreach ($old_item_ids as $old_item_id) {
					if ($old_item_id != '') {
						$this->db->query('UPDATE histories SET ship_invoiceitem_id=NULL WHERE ship_invoiceitem_id='.$ii_id.' AND item_id='.$old_item_id);
						$qi_items = $this->db->query('SELECT quoteitem_items.*, COUNT(*) as items, histories.item_id FROM quoteitem_items INNER JOIN histories ON quoteitem_items.history_id=histories.id WHERE item_id='.$old_item_id);
						$qi_items = $qi_items->row_array();
						// If there are quotes pointing at this item, return it to held status
						if ($qi_items['items'] > 0) {
							$this->db->query('UPDATE items SET onhold=1 WHERE id='.$old_item_id);
						} else {
							$this->db->query('UPDATE items SET onhold=0 WHERE id='.$old_item_id);
						}
					}
				}
				foreach ($item_ids as $item_id) {
					if ($item_id != '') {
						$history_id = $this->db->query('SELECT id, ship_invoiceitem_id FROM histories WHERE item_id='.$item_id.' ORDER BY id');
						$history_id = $history_id->result_array();
						$old_ship_invoiceitem_id = $history_id[count($history_id)-1]['ship_invoiceitem_id'];
						$history_id = $history_id[count($history_id)-1]['id'];
						// if the old invoice was set and is NOT the one we are currently saving...
						if ($old_ship_invoiceitem_id != '' && $old_ship_invoiceitem_id != $ii_id) {
							return('Invoice NOT saved; one of the items used is already promised to another invoice.');
						} else { // Good to go
							$this->db->query('UPDATE histories SET ship_invoiceitem_id='.$ii_id.' WHERE id='.$history_id);
							$this->db->query('UPDATE items SET onhold=2 WHERE id='.$item_id);
						}
					}
				}
				// if it is a new invoice lineitem, add to the hold count.
				if ($ra == 1)
					$this->db->query('UPDATE itemtypes SET onhold=onhold+1 WHERE id='.$item['itemtype_id']);
			}
		} unset($item);

		$this->db->query('UPDATE invoices SET '.implode(', ', array_values($update)).',
			editedby='.$this->session->userdata('id').', lastedited=CURRENT_TIMESTAMP() WHERE id='.$inv_id);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
			return 'Invoice saving FAILED.';

		$mess = 'Invoice saved successfully!';

		$this->queueAddInvoice($inv_id);
		return $mess.$inv_id;
	}

	function delete($id) {
		$histories = $this->db->query('SELECT id, item_id FROM history_log WHERE ship_inv_id='.$id);
		$histories = $histories->result_array();
		foreach ($histories as $history) {
			$qi_items = $this->db->query('SELECT quoteitem_items.*, COUNT(*) as items, histories.item_id FROM quoteitem_items INNER JOIN histories ON quoteitem_items.history_id=histories.id WHERE item_id='.$history['item_id']);
			$qi_items = $qi_items->row_array();
			// If there are quotes pointing at this item, return it to held status
			if ($qi_items['items'] > 0) {
				$this->db->query('UPDATE items SET onhold=1 WHERE id='.$history['item_id']);
			} else {
				$this->db->query('UPDATE items SET onhold=0 WHERE id='.$history['item_id']);
			}
		}
		$this->db->query('DELETE FROM invoices WHERE id='.$id);
		if ($this->db->affected_rows() > 0) {
			return 'Invoice deleted successfully!';
		}
		else return 'Invoice not deleted (perhaps did not exist).';
	}

	function _readInvitems($inv_id) {
		$result = array();
		$j = 0; // This variable becomes the index in the result array, as the item rows might not be consecutive
		for ($i = 0; $i < $_POST['currRow']; $i++) {
			if (isset($_POST['itemtype_id'.$i]) && $_POST['itemtype_id'.$i] != '') {
				$t = array(
					'id' => $this->input->post('invitem_id'.$i),
					'inv_id' => $inv_id,
					'itemtype_id' => $this->input->post('itemtype_id'.$i),
					'item_ids' => explode('|', $this->input->post('item_id'.$i)),
					'old_item_ids' => explode('|', $this->input->post('old_item_id'.$i)),
					'quantity' => $this->input->post('quantity'.$i),
					'type' => $this->input->post('type'.$i),
					'rate' => $this->input->post('rate'.$i),
					'print' => $this->input->post('print'.$i),
					'description' => $this->input->post('description'.$i),
					'officenotes' => $this->input->post('officenotes'.$i)
				);
				$result[$j++] = $t;
			}
		}
		return $result;
	}

	// Construct Invoice data array.
	function getInvoice($id = 0) {
		if ($id == 0)
			$id = $this->input->post('inv_id');

		$query = $this->db->query(
			'SELECT invoices.*, DATE_FORMAT(invoices.created, "%b %d, %Y") AS date,
			DATE_FORMAT(invoices.lastedited, "%b %d %T") AS datelastedited,
			DATE_FORMAT(invoices.sent, "%b %d, %Y") AS emaildate,
			createusers.name AS createdbyname, editedusers.name AS editedbyname
			FROM (invoices LEFT JOIN users AS createusers ON createusers.id=invoices.createdby)
			LEFT JOIN users AS editedusers ON editedusers.id=invoices.editedby WHERE invoices.id='.$id
		);
		$result['inv'] = $query->row_array();
		$query->free_result();

		if (!isset($result['inv']['billto'])) {
			return false;
		}

		$query = $this->db->query('SELECT * FROM invoice_view WHERE inv_id='.$id.' ORDER BY id');
		$result['items'] = $query->result_array();
		$query->free_result();

		$parent_id = 0;
		foreach ($result['items'] as &$item) {
			// Collect the barcodes for each invoiceitem
			$barcodes = $this->db->query('SELECT histories.item_id, items.barcode FROM histories INNER JOIN items ON histories.item_id=items.id WHERE histories.ship_invoiceitem_id='.$item['id']);
			$barcodes = $barcodes->result_array();
			$item['barcodes'] = $barcodes;
			$item['assemqty'] = 1;

			// Store the itemtype_id for looking up quantities for subitems
			if ($item['print'] == 'assembly') {
				$parent_id = $item['itemtype_id'];
			} else if ($item['print'] == 'subitem') {
				$assemqty = $this->db->query('SELECT quantity FROM assemblies WHERE parent='.$parent_id.' AND child='.$item["itemtype_id"]);
				$assemqty = $assemqty->row_array();
				$item['assemqty'] = $assemqty['quantity'];
			}
		}

		$query = $this->db->query('SELECT * FROM customers WHERE id='.$result['inv']['billto']);
		$result['customer'] = $query->row_array();
		$query->free_result();

		$query = $this->db->query('SELECT * FROM phones WHERE cust_id='.$result['customer']['id']);
		$result['customer']['phones'] = $query->result_array();
		$query->free_result();

		$query = $this->db->query('SELECT * FROM emails WHERE cust_id='.$result['customer']['id']);
		$result['customer']['emails'] = $query->result_array();
		$query->free_result();

		return $result;
	}

	function getMessage($view) {
		$query = $this->db->query('SELECT * FROM messages WHERE view="'.$view.'"');
		$query = $query->row_array();
		return $query;
	}

	function queueAddInvoice($inv_id) {
		if ($inv_info = $this->getInvoice($inv_id)) {
// 			log_message('error', print_r($inv_info, TRUE));
			$cust_info = $inv_info['customer'];
			$inv_items = $inv_info['items'];
			$inv_info = $inv_info['inv'];


			// Create the invoice object
			$inv = new QuickBooks_Object_Invoice();
			$cust = new QuickBooks_Object_Customer();

			// All the customers and the items will be queued by AJAX when they get quoted, so we SHOULD be alright...
			// But if any of the ListIDs are not set, we can use the full name because the queue priority will be much lower
			if (isset($inv_info['qbrefnum']) && isset($inv_info['qbeditsequence'])) {
				$inv->setTxnID($inv_info['qbrefnum']);
				$inv->setEditSequence($inv_info['qbeditsequence']);
			} else {
				// Set QB Invoice#
				$inv->setRefNumber('Tea-'.$inv_id);
			}

			if (isset($cust_info['qbrefnum']) && $cust_info['qbrefnum'] != '') {
				$inv->setCustomerListID($cust_info['qbrefnum']);
				$cust->setListID($cust_info['qbrefnum']);
			} else {
				$inv->setCustomerName($cust_info['name']);
				$cust->setFullName($cust_info['name']);
			}

			$inv->setBillAddress($cust_info['address'], $cust_info['address1'], '', '', '', $cust_info['city'], '', $cust_info['state'], $cust_info['zip'], $cust_info['country'], '');
			$inv->setShipAddress($cust_info['shipaddress'], $cust_info['shipaddress1'], '', '', '', $cust_info['shipcity'], '', $cust_info['shipstate'], $cust_info['shipzip'], $cust_info['shipcountry'], '');
			if (isset($inv_info['purchaseorder'])) $inv->setPONumber($inv_info['purchaseorder']);

			if ($cust_info['currency'] == 'United States of America Dollar') {
				$inv->setTemplateName('USD INVOICE');
				$itemname = 'ZZ OFFICE USE:GENERAL PART';
			} else {
				$inv->setTemplateName('CDN INVOICE');
				$itemname = 'ZZ OFFICE USE:GENERAL PART';
			}

			if (isset($inv_info['notes'])) $inv->setMemo($inv_info['notes']);

			// invoice items
			foreach ($inv_items as $inv_item) {
				if ($inv_item['print'] != 'subitem') {
					$invLine = new QuickBooks_Object_Invoice_InvoiceLine();
// 					$invLine->setItemName($inv_item['make'].':'.$inv_item['modelnum']);
					$invLine->setItemName($itemname);
					$invLine->setDescription($inv_item['description']);
					$invLine->setRate($inv_item['rate']);
					$invLine->setQuantity($inv_item['quantity']);
					$invLine->setTxnLineID('-1'); // A -1 TxnLineID will add it to an existing invoice

					// Add those invoice line to the invoice
					$inv->addInvoiceLine($invLine);
					unset($invLine);
				}
			}

			// invoice tax line
			$taxamnt = Array( 'name' => 'ZZ OFFICE USE:TAXAMNT',
					  'description' => 'This is the tax line for '.$inv_info['taxtype'].' at '.$inv_info['taxrate']	);
			if ( $inv_info['taxamnt'] !=  0 ) {
				$invLine = new QuickBooks_Object_Invoice_InvoiceLine();
				$invLine->setItemName($taxamnt['name']);
				$invLine->setDescription($taxamnt['description']);
				$invLine->setRate($inv_info['taxamnt']);
				$invLine->setTxnLineID('-1');

				$inv->addInvoiceLine($invLine);
				unset($invLine);
			}

			// invoice information line.
			if ( !($inv_info['caller'] ==  '' ) ) {
				$invLine = new QuickBooks_Object_Invoice_InvoiceLine();
				$invLine->setItemFullName('ZZ OFFICE USE:INFO');
				$invLine->setDescription('Purchased by '.$inv_info['caller']);
				$invLine->setTxnLineID('-1');
				$inv->addInvoiceLine($invLine);
				unset($invLine);
			}

// 			log_message('error', print_r($inv, TRUE));
			// $value = '56 Cowles Road';
			// $xpath = 'ShipAddress/Addr1';
			// Makes sure the $value will fit in a QuickBooks Customer ShipAddress/Addr1 field
			// $ShipAddress_Addr1 = QuickBooks_Cast::cast(QUICKBOOKS_OBJECT_CUSTOMER, $xpath, $value);

			$queue = new QuickBooks_Queue('mysql://cncrepair:sausage13@localhost/quickbooks');

			if (isset($inv_info['qbrefnum']) && $inv_info['qbrefnum'] != '') {
				// Manually change the 'Add's to 'Mod' and add the TxnLineID of each line item.
				$modXML = $inv->asQBXML(QUICKBOOKS_ADD_INVOICE, 'CA3.0', 'CA');
				$modXML = str_replace('InvoiceAdd', 'InvoiceMod', $modXML);
				$modXML = str_replace('<InvoiceMod>',
					"<InvoiceMod>\n\t\t<TxnID>".$inv->getTxnID()."</TxnID>\n\t\t<EditSequence>".$inv->getEditSequence().'</EditSequence>',
					$modXML);
				$invLines = $inv->listInvoiceLines();
				foreach ($invLines as $invLine) {
					$modXML = preg_replace('/\<InvoiceLineAdd\>/', "<InvoiceLineMod>\n\t\t\t<TxnLineID>".$invLine->getTxnLineID().'</TxnLineID>', $modXML, 1);
					$modXML = preg_replace('/\/InvoiceLineAdd/', '/InvoiceLineMod', $modXML, 1);
				}

				$extra = array(
					'action' => QUICKBOOKS_MOD_INVOICE,
					QUICKBOOKS_MOD_INVOICE => $modXML,
					QUICKBOOKS_QUERY_INVOICE => $inv->asQBXML(QUICKBOOKS_QUERY_INVOICE, 'CA3.0', 'CA')
				);
				// log_message('error', print_r($extra, true));
				$queue->enqueue(QUICKBOOKS_QUERY_INVOICE, $inv_id.$this->_getPin(), 2, $extra);
			} else {
				$extra = array('action' => QUICKBOOKS_ADD_INVOICE, QUICKBOOKS_ADD_INVOICE => $inv->asQBXML(QUICKBOOKS_ADD_INVOICE, 'CA3.0', 'CA'));
				// log_message('error', print_r($extra, true));
				$queue->enqueue(QUICKBOOKS_ADD_INVOICE, $inv_id.$this->_getPin(), 2, $extra);
			}

			// Queue up a lower-priority customer query to get the updated customer balance
			$extra2 = array('action' => QUICKBOOKS_QUERY_CUSTOMER, QUICKBOOKS_QUERY_CUSTOMER => $cust->asQBXML(QUICKBOOKS_QUERY_CUSTOMER, 'CA3.0', 'CA'));
			$queue->enqueue(QUICKBOOKS_QUERY_CUSTOMER, $cust_info['id'], 1, $extra2);
		}
	}

	// This function is used to return a random string to append on the IDs passed to the QB queue.
	// If the same customer is being queued a lot (and processed) in a short time then the queue information
	// becomes unreliable.  Uncommenting the for($c..... line will allow for overwriting of that stale data.
	// The reason it is commented is because for all intents and purposes, it's better not to write to the queue
	// EVERY TIME that something gets saved (in case it gets saved 10 times) and matching IDs prevents requeueing.
	function _getPin() {
		// makes a random alpha string of a given lenth
		$aZ09 = array_merge(range('A', 'Z'), range('a', 'z'));
		$pin = '';
		// for($c = 0; $c < 13; $c++) $pin .= $aZ09[mt_rand(0, count($aZ09)-1)];
		return $pin;
	}
}