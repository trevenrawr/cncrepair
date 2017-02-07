<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

// Require the QB Queueing class
require_once './qb/QuickBooks/Frameworks.php';
if (!defined('QUICKBOOKS_FRAMEWORKS'))
	define('QUICKBOOKS_FRAMEWORKS', QUICKBOOKS_FRAMEWORK_QUEUE | QUICKBOOKS_FRAMEWORK_OBJECTS);
require_once './qb/QuickBooks.php';

class Item_model extends CI_Model {

	function Item_model() {
		parent::__construct();
	}

	// updates or inserts new item information to the database
	function insert_update() {
		// Make $_POST into a MySQL escaped string
		foreach ($_POST as &$value) {
			$value = $this->db->escape_str($value);
		} unset($value);

		$d = array (
			'id' => $this->input->post('itemtype_id'),
			'modelnum' => strtoupper($_POST['modelnum']),
			'make' => $_POST['make'],
			'exchrate' => $_POST['exchrate'],
			'repairrate' => $_POST['repairrate'],
			'salerate' => $_POST['salerate'],
			'value' => $_POST['value'],
			'onhand' => $_POST['onhand'],
			'weight' => $_POST['weight'],
			'dimensions' => $_POST['dimensions'],
			'description' => $_POST['description'],
			'details' => $_POST['details'],
			'cleaningprocs' => $_POST['cleaningprocs'],
			'repairprocs' => $_POST['repairprocs'],
			'testingprocs' => $_POST['testingprocs'],
			'packing' => $_POST['packing'],
			'exch' => ($_POST['exch'] == 'true') ? 1 : 0,
			'repair' => ($_POST['repair'] == 'true') ? 1 : 0,
			'sale' => ($_POST['sale'] == 'true') ? 1 : 0,
			'assembly' => ($_POST['assembly'] == 'true') ? 1 : 0,
			'hts' => $_POST['hts'],
			'madein' => $_POST['madein']
		);

		$update = array();

		// Some items must be NULL for foreign key constraints\
		$unsets = array('hts');
		foreach ($unsets as $unset) {
			if ($d[$unset] == '') unset($d[$unset]);
		}


		foreach ($d as $k => $v)
			$update[$k] = $k.'="'.$v.'"';
		unset($k); unset($v);
// 		log_message('error', print_r($update, TRUE));
		// Generate query to INSERT if doesn't exist, or to UPDATE if does.
		$query = 'INSERT INTO itemtypes ('.implode(', ', array_keys($d)).') '.
			'VALUES ("'.implode('", "', $d).'") '.
			'ON DUPLICATE KEY UPDATE '.implode(', ', array_values($update));
		$this->db->trans_start();
		$this->db->query($query);
		$rows_affected = $this->db->affected_rows();

		$id = $this->db->insert_id();
		if ($id == 0) $id = $d['id'];

		// remove assembly children if assembly not set
		if ($d['assembly'] == 0) {
			$this->db->query('DELETE FROM assemblies WHERE parent='.$this->db->insert_id());
		}

		// update appropiate time stamps.
		if ($rows_affected == 1) { // Created
			$this->db->query('UPDATE itemtypes SET createdby='.$this->session->userdata('id').', editedby='.$this->session->userdata('id').', created=CURRENT_TIMESTAMP() WHERE id='.$id);
		} else if ($rows_affected == 2) {
			$this->db->query('UPDATE itemtypes SET editedby='.$this->session->userdata('id').' WHERE id='.$id);
		}

		$this->db->trans_complete();

		switch ($rows_affected) {
			case 2:
				$mess = 'Item updated successfully!';
				break;
			case 1:
				$mess = 'Item created successfully!';
				break;
			case 0:
			default:
				$mess = 'Item information not changed.';
		}

		$quickbooks = $_POST['quickbooks'];

		// Add item to quickbooks only if we explicicty allow for this.  Most items show up in quickbooks as a gneric US PART, or CANADA PART.
		if ( $quickbooks == "true" && ($rows_affected == 2 || $rows_affected == 1) ) {
			$this->queueAddItem($id);
		}

		if ($this->db->trans_status() === FALSE)
			$mess = 'Error processing item insertion.';

		$information = Array( "message" => $mess,
					"id"	=> $id );
		return $information;
	}

	// Deletes an item or itemtypes
	function delete($specific = false) {
		$id = $this->input->post('itemtype_id');
		$table = 'itemtypes';
		if ($specific)
			$table = 'items';

		$this->db->query('DELETE FROM '.$table.' WHERE id='.$id);

		return 'Item deleted successfully.';
	}

	function save_specific($forPos = false) {
		if (!$forPos) { // Positions controller already escaped the strings
			foreach ($_POST as &$value) {
				$value = $this->db->escape_str($value);
			} unset($value);
		}

		$d = array (
			'id' => $this->input->post('item_id'),
			'itemtype_id' => $this->input->post('itemtype_id'),
			'barcode' => strtoupper($this->input->post('barcode')),
			'serial' => strtoupper($this->input->post('serial')),
			'status' => ($this->input->post('status')) ? $this->input->post('status') : 'needs work',
			'atcustomer' => $this->input->post('cust_id'),
			'owner' => $this->input->post('owner_id'),
			'rack' => $this->input->post('rack'),
			'shelf' => $this->input->post('shelf'),
			'readyfor' => $this->input->post('readyfor'),
			'priority' => $this->input->post('priority'),
			'stock' => $this->input->post('stock')
		);

		// Unset items which have no value so that NULL values get stored as default (where applicable)
		foreach ($d as $k => $t) {
			if ($d[$k] == '') unset($d[$k]);
		} unset($k); unset($t);

		$update = array();
		foreach ($d as $k => $v)
			$update[$k] = $k.'="'.$v.'"';
		unset($k); unset($v);

		if (!$forPos)
			$this->db->trans_start();

		// Check to see if the itemtype_id changed and if it did, alter the item onhand counts
		if ($d['id'] != '') {
			$query = $this->db->query('SELECT itemtype_id FROM items WHERE id='.$d['id']);
			$query = $query->row_array();
			if (isset($query['itemtype_id']) && ($d['itemtype_id'] != $query['itemtype_id'])) {
				$this->db->query('UPDATE itemtypes SET onhand=onhand-1 WHERE id='.$query['itemtype_id']);
				$this->db->query('UPDATE itemtypes SET onhand=onhand+1 WHERE id='.$d['itemtype_id']);
			}
		}

		// Generate query to INSERT if doesn't exist, or to UPDATE if does.
		// Whether it INSERTs or UPDATEs is due to the existance of that item_id...
			// ... if the item was identified by serial number, the item_id will arrive here
			// and a new barcode will be associated with it.
		$query = 'INSERT INTO items ('.implode(', ', array_keys($d)).') '.
			'VALUES ("'.implode('", "', $d).'") '.
			'ON DUPLICATE KEY UPDATE '.implode(', ', array_values($update));
		$this->db->query($query);
		$ra = $this->db->affected_rows();
		// On update, insert_id == 0, so use the one obtained from the form
		$id = $this->db->insert_id();
		if ($id == 0) $id = $d['id'];

		// The unpacking position uses this to insert/update items when they come in, but it only needs the insert id
		if ($forPos) {
			$this->db->trans_complete();
			return $id;
		}

		// If an item is created on the specific item screen, it needs a history entry and onhand count updated for it as well
		if ($ra == 1) {
			$this->db->query('UPDATE itemtypes SET onhand=onhand+1 WHERE id='.$d['itemtype_id']);
			$this->db->query('INSERT INTO histories (item_id, camefrom) VALUES ('.$id.', 0)');
		}

		$this->db->trans_complete();

		switch ($ra) {
			case 2:
				$mess = 'Item updated successfully!';
				break;
			case 1:
				$mess = 'Item created successfully!';
				break;
			case 0:
			default:
				$mess = 'Item information not changed.';
		}
		return $mess;
	}

	/* -*-*-*-*-*-*-*-*-*-*-ASSEMBLY SAVING-*-*-*-*-*-*-*-*-*-*- */

	// Gets the assembly information when loading the itemtypes edit assembly
	function getAssem($assem) {
		$query = $this->db->query('SELECT id, modelnum, description, quantity FROM assembly_view WHERE parent='.$assem);
		$items['items'] = $query->result_array();
		$query->free_result();

		$query = $this->db->query('SELECT id, modelnum, description, make FROM itemtypes WHERE id='.$assem);
		$items['parent'] = $query->row_array();
		$query->free_result();

		return $items;
	}

	// Gets the information which describes a specific assembly (on the item level)
	function getSpecificAssem($item_id, $parent = false) {
		if (!$parent) {
			$assem = $this->db->query(
				'SELECT specificassemblies.parent,
				phantomitems.itemtype_id
				FROM specificassemblies INNER JOIN phantomitems on specificassemblies.parent=phantomitems.id
				WHERE specificassemblies.child='.$item_id);
		} else {
			$assem = $this->db->query('SELECT id AS parent, itemtype_id FROM phantomitems WHERE id='.$item_id);
		}
		$parent = $assem->row_array();
		if (!isset($parent['itemtype_id'])) {
			return '';
		}
		$itemtype_id = $parent['itemtype_id'];
		$parent = $parent['parent'];

		$items['itemList'] = $this->getAssem($itemtype_id);

		$assem = $this->db->query('SELECT * FROM specificassembly_view WHERE parent='.$parent);
		$items['items'] = $assem->result_array();
		foreach ($items['items'] as &$item) {
			$item = array_merge($item, $this->_get_history($item['id']));
		}
		$items['parent'] = $parent;
		return $items;
	}

	// Reads in assembly information for saving in $this->save()
	function _readItems($parent) {
		$result = array();
		$j = 0;
		for ($i = 0; $i < $_POST['currRow']; $i++) {
			if (isset($_POST['itemtype_id'.$i]) && $_POST['itemtype_id'.$i] != '') {
				$result[$j]['parent'] = $parent;
				$result[$j]['child'] = $_POST['itemtype_id'.$i];
				$result[$j++]['quantity'] = $_POST['quantity'.$i];
			}
		}
		return $result;
	}

	// Saves an assembly configuration on the itemtype level
	function save($type) {
		if ($type == 'assem') {
			$assem = $this->input->post('itemtype_id');
			$aItems = $this->_readItems($assem);

			$this->db->trans_start();

			// We don't know which items were deleted and what was added,
			// so we delete all the current ones and add the new ones back in.
			// Since this table uses a natural key (parent, child), there's no worry of
			// artificially inflating the auto_increment column.
			$this->db->query('DELETE FROM assemblies WHERE parent='.$assem);
			foreach ($aItems as $aItem)
				$this->db->query('INSERT INTO assemblies ('.implode(', ', array_keys($aItem)).') VALUES ('.implode(', ', $aItem).')');
			// Just in case someone forgets to save the item after the assembly is saved.
			$this->db->query('UPDATE itemtypes SET assembly=1 WHERE id='.$assem);

			$this->db->trans_complete();

			$mess = 'Assembly saved successfully.';
			if ($this->db->trans_status() === FALSE)
				$mess = 'Assembly not saved.';
			return $mess;
		}
	}

	/* -*-*-*-*-*-*-*-*-*-*-END ASSEMBLY-*-*-*-*-*-*-*-*-*-*- */

	// Collects the notes for a particular item and formats them
	function _get_notes($id, $full = false, $type = 'repair') {
		if ($full) {
			$query = $this->db->query('SELECT * FROM notes_view WHERE type="'.$type.'" AND item_id='.$id);
		} else {
			$history_id = $this->db->query('SELECT id FROM histories WHERE item_id='.$id);
			$history_id = $history_id->result_array();
			if (count($history_id) > 0) { // Not sure if the item has a history!
				$history_id = $history_id[count($history_id)-1]['id'];
				$query = $this->db->query('SELECT * FROM notes_view WHERE type="'.$type.'" AND history_id='.$history_id);
			}
		}

		$result = '';

		if (isset($query)) {
			$notes = $query->result_array();
			$query->free_result();
			foreach ($notes as $note) {
				$result .= $note['note']."\n      -".$note['name'].' ('.$note['note_date'].")\n\n";
			}
		}

		return $result;
	}

	/* -*-*-*-*-*-*-*-*-*-*-BEGIN POSITIONS-*-*-*-*-*-*-*-*-*-*- */

	// The master "positions" function delegates to individual positions after completing common tasks
	function positions($type) {
		// Make $_POST into a MySQL escaped string
		foreach ($_POST as &$value) {
			$value = $this->db->escape_str(trim($value));
		} unset($value);

		$rte = $this->input->post('route');
		$id = $this->input->post('item_id');
		$notes = $this->input->post('newnotes');

		if ($type != 'unpacking' && $type != 'assembling' && $type != 'shipping') {
			$query = $this->db->query('SELECT id FROM histories WHERE item_id='.$id.' ORDER BY id');
			$history_id = $query->result_array();
			$query->free_result();
			$history_id = $history_id[count($history_id)-1]['id'];
		}

		$location['rack'] = $this->input->post('rack');
		$location['shelf'] = $this->input->post('shelf');

		switch ($type) {
			case 'unpacking':
				$r = $this->_unpacking($notes);
				break;
			case 'receiving':
				$r = $this->_receiving($rte, $id, $location, $history_id);
				break;
			case 'cleaning':
				$r = $this->_cleaning($rte, $id, $notes, $location, $history_id);
				break;
			case 'repair':
				$r = $this->_repair($rte, $id, $notes, $location, $history_id);
				break;
			case 'assembling':
				$r = $this->_assembling($rte, $location);
				break;
			case 'testing':
				$r = $this->_testing($rte, $id, $notes, $location, $history_id);
				break;
			case 'shipping':
				$r = $this->_shipping($notes);
				break;
			default:
				$r = 'Improper position';
		}
		return $r;
	}

	/* -*-*-*-*-*-*-*-*-*-*-UNPACKING/RECEIVING-*-*-*-*-*-*-*-*-*-*- */

	// When an item is 'unpacked' it begins its stay in the shop.
	// This means that it gets a new history entry, and a new queue entry.
	// If the item is an assembly, it gets disassembled here so each part can be barcoded.
	function _unpacking($notes) {

		$barcode = $this->input->post('barcode');
		$itemtype_id = $this->input->post('itemtype_id');
		$camefrom = $this->input->post('cust_id');

		$query = $this->db->query('SELECT atcustomer FROM items WHERE barcode="'.$barcode.'"');
		$alreadyhere = $query->row_array();
		$query->free_result();
		if (isset($alreadyhere['atcustomer'])) {
			if ($alreadyhere['atcustomer'] == 0)
				return "You just unpacked an item which is already here...\nNo changes were logged.\nCheck item history for more information.";
		}

		$this->db->trans_start();

		$id = $this->save_specific(true);

		// If the item coming in is logged as an assembly, that assembly should be disassembled now for two reasons:
			// 1. All items should get disassembled "at unpacking"
			// 2. It could have been moved to another assembly at the customer, meaning its old assem. info is already invalid.
		$currAssem = $this->getSpecificAssem($id);
		if (isset($currAssem["items"])) {
			foreach ($currAssem["items"] as $item) {
				$this->db->query('UPDATE itemtypes SET inuse=inuse-1 WHERE id='.$item['itemtype_id']);
			} unset($item);
			// Deleting from phantomitems also removes all specificassembly entries with that item as parent
			$this->db->query('DELETE FROM phantomitems WHERE id='.$currAssem['parent']);
		}

		$this->db->query('UPDATE items SET atcustomer=0, readyfor="receiving" WHERE barcode="'.$barcode.'"');

		// Update the inhouse item count
		$this->db->query('UPDATE itemtypes SET onhand=onhand+1 WHERE id='.$itemtype_id);

		// Create the history entry for this stay in the shop
		$this->db->query('INSERT INTO histories (item_id, camefrom) VALUES ('.$id.', "'.$camefrom.'")');
		$history_id = $this->db->insert_id();

		// If there are new notes, make a notes entry for them.
		if ($notes != '')
			$this->db->query('INSERT INTO notes (type, history_id, user_id, note) VALUES ("ship", '.$history_id.', '.$this->session->userdata('id').', "'.$notes.'")');

		$this->db->trans_complete();

		$result = 'Item unpacked successfully and waiting to be recieved!';
		if ($this->db->trans_status() === FALSE)
			$result = 'Error logging item status.';
		return $result;
	}

	// Collects and returns any relevant quote items for receiving, filtered by customer name
	function incoming_quote_items() {
		$input = $this->db->escape_str($this->input->post('name'));
		$query = $this->db->query('SELECT DISTINCT modelnum, itemtype_id, print, description FROM incoming WHERE name="'.$input.'"');
		$expected = $query->result_array();
		return $expected;
	}

	// Called from $this->positions(), this takes care of saving the "receiving" position data.
	// After unpacked items have been invoiced, they are received upon a particular invoice
	function _receiving($rte, $id, $location, $history_id) {
		$inv_id = $this->input->post('inv_id');
		$invoiceitem_id = $this->input->post('invoiceitem_id');
		$itemtype_id = $this->input->post('itemtype_id');
		$priority = $this->input->post('priority');

		$query = $this->db->query('SELECT total FROM invoices WHERE id='.$inv_id);
		$query = $query->row_array();
		if (!isset($query['total'])) {
			return 'Invalid invoice number.';
		}

		$oldinv_id = $this->db->query('SELECT invoiceitem_id, camefrom FROM histories WHERE id='.$history_id);
		$oldinv_id = $oldinv_id->row_array();
		$camefrom = $oldinv_id['camefrom'];
		$oldinv_id = $oldinv_id['invoiceitem_id'];

		$this->db->trans_start();

		// If the item has already been noted as on an invoice, undo the received count for it.
		if ($oldinv_id != '') {
			$this->db->query('UPDATE invoiceitems SET qtyreceived=qtyreceived-1 WHERE id='.$invoiceitem_id);
		}
		$notes = $this->db->query('SELECT officenotes, type FROM invoiceitems WHERE id='.$invoiceitem_id);
		$notes = $notes->row_array();
		$this->db->query('UPDATE invoiceitems SET qtyreceived=qtyreceived+1 WHERE id='.$invoiceitem_id);

		$this->db->query('UPDATE histories SET received=CURRENT_TIMESTAMP(), invoiceitem_id='.$invoiceitem_id.' WHERE id='.$history_id);

		if ($notes['type'] == 'repair') {
			// If it's a repair item, it should go out on the same invoice it came in on.
			$this->db->query('UPDATE histories SET ship_invoiceitem_id='.$invoiceitem_id);
			$this->db->query('UPDATE items SET owner=(SELECT cust_id FROM invoices WHERE id='.$inv_id.') WHERE id='.$id);
		} else if ($notes['type'] == 'exch') {
			// If it's an exch item, look for and delete ONE phantom item which is waiting for it.
			$pitems = $this->db->query('SELECT * FROM oweditems WHERE inv_id='.$inv_id.' AND itemtype_id='.$itemtype_id);
			$pitems = $pitems->row_array(); // Just take the first one.
			if (isset($pitems['id'])) {
				$this->db->query('DELETE FROM oweditems WHERE id='.$pitems['id']);
			}
			$this->db->query('UPDATE items SET owner=0 WHERE id='.$id);
		}

		$this->db->query('UPDATE items SET lastseen=CURRENT_TIMESTAMP(), priority="'.$priority.'", rack="'.$location['rack'].'", shelf="'.$location['shelf'].'", readyfor="'.$rte.'" WHERE id='.$id);

		if (isset($notes['officenotes']) && $notes['officenotes'] != '')
			$this->db->query('INSERT INTO notes (type, history_id, user_id, note) VALUES ("repair", '.$history_id.', '.$this->session->userdata('id').', "'.$notes['officenotes'].'")');

		$this->db->trans_complete();

		$result = 'Item successfully received and queued for '.$rte.'!';
		if ($this->db->trans_status() === FALSE)
			$result = 'Error logging item status.';
		return $result;
	}

	// Collects and returns a list of relevant invoices for the "receiving" position
	function rec_inv_list() {
		$id = $this->input->post('id');

		$query = $this->db->query('SELECT * FROM rec_inv_list WHERE item_id='.$id);
		$result['invoices'] = $query->result_array();
		$result['pos'] = 'receiving';
		return $result;
	}

	/* -*-*-*-*-*-*-*-*-*-*-END UNPACKING/RECEIVING-*-*-*-*-*-*-*-*-*-*- */

	// Called from $this->positions(), this takes care of saving the "cleaning" position data.
	function _cleaning($rte, $id, $notes, $location, $history_id) {

		$this->db->trans_start();

		if ($rte != 'cleaning') {
			$this->db->query('UPDATE histories SET cleaned=CURRENT_TIMESTAMP() WHERE id='.$history_id);
		}
		$this->db->query('UPDATE items SET lastseen=CURRENT_TIMESTAMP(), rack="'.$location['rack'].'", shelf="'.$location['shelf'].'", readyfor="'.$rte.'" WHERE id='.$id);
		if ($notes != '')
			$this->db->query('INSERT INTO notes (type, history_id, user_id, note) VALUES ("repair", '.$history_id.', '.$this->session->userdata('id').', "'.$notes.'")');

		$this->db->trans_complete();

		$result = 'Item successfully noted as cleaned and queued for '.$rte.'!';
		if ($rte == 'cleaning') $result = 'Notes saved for later.';
		if ($this->db->trans_status() === FALSE)
			$result = 'Error logging item status.';
		return $result;
	}

	// Called from $this->positions(), this takes care of saving the "repair" position data.
	function _repair($rte, $id, $notes, $location) {
		$status = $this->input->post('status');
		$stock = $this->input->post('stock');

		// Get the list of barcodes (in case of assembly)
		$items = array();
		$j = 0;
		for ($i = 0; $i < $this->input->post('currRow'); $i++) {
			$item_id = $this->input->post('item_id'.$i);
			if ($item_id != '') {
				// Get the most current history, old shipping invoice
				$query = $this->db->query('SELECT id FROM histories WHERE item_id='.$item_id.' ORDER BY id');
				$query = $query->result_array();
				$size = count($query)-1;
				$items[$j]['history_id'] = $query[$size]['id'];
				$items[$j++]['id'] = $item_id;
			}
		}

		$this->db->trans_start();

		foreach ($items as $item) {
			if ($rte != 'repair') {
				$this->db->query('UPDATE histories SET repaired=CURRENT_TIMESTAMP() WHERE id='.$item['history_id']);
			}
			$this->db->query('UPDATE items SET lastseen=CURRENT_TIMESTAMP(), status='.$status.', rack="'.$location['rack'].
				'", shelf="'.$location['shelf'].'", readyfor="'.$rte.'", stock="'.$stock.'" WHERE id='.$item['id']);
			if ($notes != '')
				$this->db->query('INSERT INTO notes (type, history_id, user_id, note) VALUES ("repair", '.$item['history_id'].', '.$this->session->userdata('id').', "'.$notes.'")');
		}

		$this->db->trans_complete();

		$result = 'Item successfully noted as repaired and queued for '.$rte.'!';
		if ($rte == 'repair') $result = 'Notes saved for later.';
		if ($this->db->trans_status() === FALSE)
			$result = 'Error logging item status.';
		return $result;
	}

	// Called from $this->positions(), this takes care of saving the "assembling" position data.
	function _assembling($rte, $location) {
		$parent = $this->input->post('parent_id');
		$itemtype_id = $this->input->post('itemtype_id');
		$priority = $this->input->post('priority');
		$stock = $this->input->post('stock');

		// Get the list of item_ids in the assembly
		$items = array();
		$j = 0;
		for ($i = 0; $i < $this->input->post('currRow'); $i++) {
			$item_id = $this->input->post('item_id'.$i);
			if ($item_id != '') {
				$query = $this->db->query('SELECT parent FROM specificassemblies WHERE child='.$item_id);
				$query = $query->row_array();
				if (count($query) > 0 && $query['parent'] != $parent)
					return 'Item with barcode "'.$this->input->post('barcode'.$i).'" is in use in another assembly.';
				$items[$j]['id'] = $item_id;
				$items[$j++]['itemtype_id'] = $this->input->post('itemtype_id'.$i);
			}
		}

		$this->db->trans_start();

		if (count($items) > 1) { // Assemblies containing only one item don't make sense

			if ($parent == '') { // Create a parent (if one doesn't exist already)
				$this->db->query('INSERT INTO phantomitems (itemtype_id) VALUES ('.$itemtype_id.')');
				$parent = $this->db->insert_id();
			} else { // Update an existing parent if the type of assembly changed.
				$parent_type = $this->db->query('SELECT itemtype_id FROM phantomitems WHERE id='.$parent);
				$parent_type = $parent_type->row_array();
				if ($parent_type["itemtype_id"] != $itemtype_id) {
					$this->db->query('UPDATE phantomitems SET itemtype_id='.$itemtype_id.' WHERE id='.$parent);
				}
			}

			// Drop previous definition of assembly, including inuse counts
			$currAssem = $this->getSpecificAssem($parent, true);
			foreach ($currAssem["items"] as $item) {
				$this->db->query('UPDATE itemtypes SET inuse=inuse-1 WHERE id='.$item['itemtype_id']);
			} unset($item);
			$this->db->query('DELETE FROM specificassemblies WHERE parent='.$parent);

			// Set up the new definition of an assembly, alter inuse counts
			foreach ($items as $item) {
				$this->db->query('INSERT INTO specificassemblies (parent, child) VALUES ('.$parent.', '.$item['id'].')');
				$this->db->query('UPDATE items SET lastseen=CURRENT_TIMESTAMP(), rack="'.$location['rack'].
					'", shelf="'.$location['shelf'].'", readyfor="'.$rte.'", priority="'.$priority.'", stock="'.$stock.'" WHERE id='.$item['id']);
				$this->db->query('UPDATE itemtypes SET inuse=inuse+1 WHERE id='.$item['itemtype_id']);
			}

			$result = 'Item successfully assembled and queued for '.$rte.'!';
		} else { // Assembly contains one or fewer items
			if ($parent != '') {
				$this->db->query('DELETE FROM phantomitems WHERE id='.$parent);
				$result = 'Assembly only contained one item and was deleted.';
			} else {
				$result = 'Assembly only contained one item and was not saved.';
			}
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
			$result = 'Error logging item status.';
		return $result;
	}

	// Called from $this->positions(), this takes care of saving the "testing" position data.
	function _testing($rte, $id, $notes, $location) {
		$ok = ($rte == 'shipping') ? 1 : 0;
		$status = $this->input->post('status');
		$stock = $this->input->post('stock');

		// Get the list of barcodes (in case of assembly)
		$items = array();
		$j = 0;
		for ($i = 0; $i < $this->input->post('currRow'); $i++) {
			$item_id = $this->input->post('item_id'.$i);
			if ($item_id != '') {
				// Get the most current history, old shipping invoice
				$query = $this->db->query('SELECT id FROM histories WHERE item_id='.$item_id.' ORDER BY id');
				$query = $query->result_array();
				$size = count($query)-1;
				$items[$j]['history_id'] = $query[$size]['id'];
				$items[$j++]['id'] = $item_id;
			}
		}

		$this->db->trans_start();

		foreach($items as $item) {
			if ($rte != 'testing') {
				$this->db->query('UPDATE histories SET tested=CURRENT_TIMESTAMP(), testedok='.$ok.' WHERE id='.$item['history_id']);
			}
			$this->db->query('UPDATE items SET lastseen=CURRENT_TIMESTAMP(), status='.$status.', rack="'.$location['rack'].
				'", shelf="'.$location['shelf'].'", readyfor="'.$rte.'", stock="'.$stock.'" WHERE id='.$item['id']);
			if ($notes != '')
				$this->db->query('INSERT INTO notes (type, history_id, user_id, note) VALUES ("repair", '.$item['history_id'].', '.$this->session->userdata('id').', "'.$notes.'")');
		}

		$this->db->trans_complete();

		$result = 'Item successfully noted as tested and queued for '.$rte.'!';
		if ($rte == 'testing') $result = 'Notes saved for later.';
		if ($this->db->trans_status() === FALSE)
			$result = 'Error logging item status.';
		return $result;
	}

	// Called from $this->positions(), this takes care of saving the "shipping" position data.
	function _shipping($notes) {
		$ship_inv_id = $this->input->post('ship_inv_id');
		$tnum = $this->input->post('trackingnum');
		$carrier = $this->input->post('carrier');

		$shippedto = $this->db->query('SELECT shipname, billto FROM invoices WHERE id='.$ship_inv_id);
		$shippedto = $shippedto->row_array();
		$cust_id = $shippedto['billto'];
		$shippedto = $shippedto['shipname'];

		// Get the list of barcodes (in case of assembly)
		$items = array();
		$j = 0;
		for ($i = 0; $i < $this->input->post('currRow'); $i++) {
			$item_id = $this->input->post('item_id'.$i);
			if ($item_id != '') {
				// Get the most current history, old shipping invoice
				$query = $this->db->query('SELECT id, ship_inv_id, ship_invoiceitem_id, shipped FROM history_log WHERE item_id='.$item_id.' ORDER BY id');
				$query = $query->result_array();
				$size = count($query)-1;
				$items[$j]['history_id'] = $query[$size]['id'];
				$items[$j]['shipped'] = $query[$size]['shipped'];
				$items[$j]['ship_inv_id'] = $query[$size]['ship_inv_id'];
				$items[$j]['ship_ii_id'] = $query[$size]['ship_invoiceitem_id'];
				$items[$j]['id'] = $item_id;
				$items[$j++]['itemtype_id'] = $this->input->post('itemtype_id'.$i);
			}
		}

		$this->db->trans_start();

		foreach ($items as $item) {
			// If the item has already been noted as shipped on an invoice, undo the shipped count for it.
			if ($item['shipped'] != '') {
				$this->db->query('UPDATE invoiceitems SET shipped=shipped-1 WHERE id='.$item['ship_ii_id']);
			}
			$this->db->query('UPDATE invoiceitems SET shipped=shipped+1 WHERE id='.$item['ship_ii_id']);

			// Update the owner of the item.  If it is changing from CNC Repair and is a repair item it must be an in-house exchange...
			$transType = $this->db->query('SELECT type FROM invoiceitems WHERE id='.$item['ship_ii_id']);
			$transType = $transType->row_array();
			if ($transType == 'repair') {
				// The item which was received on the invoice that this repair item is being sent on should become CNC's
				$this->db->query('UPDATE items SET owner=0 WHERE id=(SELECT item_id FROM histories WHERE invoiceitem_id='.$item['ship_ii_id'].' LIMIT 1)');
			}

			$this->db->query('UPDATE histories SET shipped=CURRENT_TIMESTAMP(), shippedto="'.$shippedto.'", carrier="'.$carrier.'", trackingnum="'.strtoupper($tnum).'" WHERE id='.$item['history_id']);
			$this->db->query('UPDATE items SET lastseen=CURRENT_TIMESTAMP(), atcustomer="'.$cust_id.'", owner='.$cust_id.', readyfor="" WHERE id='.$item['id']);

			if ($notes != '')
				$this->db->query('INSERT INTO notes (type, history_id, user_id, note) VALUES ("ship", '.$item['history_id'].', '.$this->session->userdata('id').', "'.$notes.'")');

			$this->db->query('UPDATE itemtypes SET onhand=onhand-1, onhold=onhold-1 WHERE id='.$item['itemtype_id']);

			// Create a phantomitem to log the fact that an item has been shipped and none returned yet:
			$owed = $this->db->query('SELECT * FROM oweditems WHERE itemtype_id='.$item['itemtype_id'].' AND inv_id='.$item['ship_inv_id'].' AND item_sent='.$item['id']);
			$owed = $owed->row_array();
			if (!isset($owed['id'])) {
				$this->db->query('INSERT INTO oweditems (itemtype_id, inv_id, item_sent) VALUES ('.$item['itemtype_id'].', '.$item['ship_inv_id'].', '.$item['id'].')');
			}
		}

		$this->db->trans_complete();

		$result = 'Item successfully logged and ready to ship!';
		if ($this->db->trans_status() === FALSE)
			$result = 'Error logging item status.';
		return $result;
	}

	// Collects and returns a list of relevant invoices for the "shipping" position
	function ship_inv_list() {
		$id = $this->input->post('id');
		$query = $this->db->query('SELECT * FROM ship_inv_list WHERE item_id='.$id);
		$result['invoices'] = $query->result_array();
		$result['pos'] = 'shipping';
		return $result;
	}

	/* -*-*-*-*-*-*-*-*-*-*-END POSITIONS-*-*-*-*-*-*-*-*-*-*- */

	// Filters and returns a list of items in the work queue
	function getQueue($pos = '') {
		foreach ($_POST as &$value) {
			$value = $this->db->escape_like_str($value);
		} unset($value);

		$readyfor = ($this->input->post('readyfor')) ? $this->input->post('readyfor') : '';
		$d = array (
			'modelnum' => ($this->input->post('modelnum')) ? $this->input->post('modelnum') : '',
			'priority' => ($this->input->post('priority')) ? $this->input->post('priority') : '',
			'readyfor' => ($pos != '') ? $pos : $readyfor
		);

		$d['modelnum'] = preg_replace('/[\-\s]/', '', $d['modelnum']);

		$query = $this->db->query('SELECT * FROM specific_items WHERE '.
			'REPLACE(REPLACE(modelnum, "-", ""), " ", "") LIKE "%'.$d['modelnum'].'%" AND '.
			'priority LIKE "'.$d['priority'].'%" AND '.
			'readyfor LIKE "'.$d['readyfor'].'%" AND '.
			'atcustomer=0 AND stock=0 AND assembly=0 ORDER BY priority DESC LIMIT 100');
		$items = $query->result_array();
		$items['queue'] = $items;
		if ($query->num_rows() == 0) {
			if ($pos == '')
				$pos = 'any positions, provided your filters';
			$items['queue'] = 'There is nothing in the queue for '.$pos.'.';
		}
		$query->free_result();
		$items['filters'] = $d;
		return $items;
	}

	// Gets the history, notes, or complete notes for a particular item
	function getNotes($id, $nhp) {
		if ($id == '0') {
			return "You must input a barcode or serial first.";
		}
		if ($nhp == 'history') {
			$notes = $this->_get_history($id);
			$notes = $notes['history'];
		} else if ($nhp == 'oldnotes') {
			$notes = $this->_get_notes($id, true);
			$nhp = 'old notes';
		} else {
			$notes = $this->_get_notes($id);
		}
		if ($notes == '')
			$notes = 'No '.$nhp.' available for this item.';
		return $notes;
	}

	// Gets procedures for the positions
	function getProcs($id, $nhp, $table) {
		$query = $this->db->query('SELECT '.$nhp.' FROM '.$table.' WHERE id='.$id);
		$query = $query->row_array();
		$notes = $query[$nhp];
		if ($notes == '')
			$notes = 'No procedures on file for this item at this position.';
		return $notes;
	}

	// Gets and formats the history (including notes) for an item.
	function _get_history($item_id) {
		$history = '';
		$query = $this->db->query('SELECT * FROM history_log WHERE item_id='.$item_id.' ORDER BY id');
		$histories = $query->result_array();

		foreach ($histories as $h) {
			if (isset($h['unpacked'])) {
				$history .= $h['unpacked'].' - Unpacked from '.$h['camefrom']."\n";
			}
			if (isset($h['received'])) {
				$invoiceNum = ($h['inv_id'] != '') ? ' on Invoice #'.$h['inv_id']."\n" : "\n";
				$history .= $h['received'].' - Received'.$invoiceNum;
			}
			if (isset($h['cleaned'])) {
				$history .= $h['cleaned']." - Cleaned\n";
			}
			if (isset($h['repaired'])) {
				$history .= $h['repaired']." - Repaired\n";
			}
			if (isset($h['tested'])) {
				$testedok = ($h['testedok'] == 1) ? 'Happy :)' : 'Unhappy :(';
				$history .= $h['tested'].' - Tested '.$testedok."\n";
			}
			if (isset($h['shipped'])) {
				$tracking = ($h['trackingnum'] != '') ? ' on Tracking#: '.$h['trackingnum'] : '';
				$history .= $h['shipped'].' - Shipped to '.$h['shippedto'].' on Invoice #'.$h['ship_inv_id']."\n";
				$history .= '                 via '.$h['carrier'].$tracking."\n";
			}
			$history .= "\n";
		} unset($h);
		$extras['history'] = $history;

		$extras['shipnotes'] = $this->_get_notes($item_id, false, 'ship');
		$extras['notes'] = $this->_get_notes($item_id, false);
		$size = count($histories)-1;

		$extras['inv_id'] = ($size >= 0) ? $histories[$size]['inv_id'] : '';
		$extras['invoiceitem_id'] = ($size >= 0) ? $histories[$size]['invoiceitem_id'] : '';
		$extras['ship_inv_id'] = ($size >= 0) ? $histories[$size]['ship_inv_id'] : '';
		$extras['shipped'] = ($size >= 0) ? $histories[$size]['shipped'] : '';

		return $extras;
	}

	// Creates a list of suggestions for a particular suggestion "entry"
	function suggest($suggType, $suggTable) {
		$input = $this->db->escape_like_str($this->input->post('q'));
		if ($suggType == 'modelnum' || $suggType == 'assembly')
			$sugg = 'modelnum, make';
		else if ($suggType == 'serial')
			$sugg = 'serial, modelnum';
		else
			$sugg = $suggType;

		$input = preg_replace('/[\-\s]/', '', $input);
		$addendum = '';
		if ($suggType == 'assembly') {
			$addendum = ' AND assembly=1 ';
			$suggType = 'modelnum';
		} else if ($suggType == 'modelnum') {
			$suggType = 'CONCAT(make, modelnum)';
		} else if ($suggType == 'barcode') {
			// $addendum = ' AND atcustomer=0 ';
		}

		$query = $this->db->query('SELECT DISTINCT '.$sugg.' FROM '.$suggTable.' WHERE REPLACE(REPLACE('.$suggType.', "-", ""), " ", "") LIKE "%'.$input.'%" '.$addendum.'ORDER BY '.$suggType.' ASC');
		$ret['suggs'] = $query->result_array();
		$ret['input'] = $input;
// 		log_message('error', print_r($ret, TRUE));
		return $ret;
	}

	// Loads item information based on an itemtype_id
	function itemInfo($itemtype_id) {
		$itemdata = $this->db->query('select itemtypes.*, createusers.name AS createdbyname, editedusers.name AS editedbyname,
						codes.id AS hts, codes.hts AS htsview, codes.description AS htsdescription
						FROM itemtypes
						LEFT JOIN users AS createusers ON createusers.id=itemtypes.createdby
						LEFT JOIN users AS editedusers ON editedusers.id=itemtypes.editedby
						LEFT JOIN htscodes AS codes ON codes.id=itemtypes.hts
						WHERE itemtypes.id ='.$itemtype_id);
		$itemdata = $itemdata->row_array();

		if (!isset($itemdata['id'])) return false;

		$itemdata['items'] = $this->quoteBarcodeSearch($itemdata['id'], false);
		return $itemdata;
	}

	// hts database query for item hts information
	function htscodes() {
		$data = $this->db->query('Select * from htscodes where 1');
		return $data->result_array();
	}

	// Grabs relevant data after a suggestion is picked.
	function filldata($suggType, $suggTable) {
		$input = $this->input->post($suggType);
		if ($suggType == 'modelnum' || $suggType == 'serial' || $suggType == 'assembly') {
			// modelnum comes in as 'modelnum  <span class="make">[make]</span>'
			$input = explode(' <span class="make">[', $input);
			$input[1] = str_replace(']</span>', '', $input[1]);
			foreach ($input as &$i)
				$i = $this->db->escape_str($i);

			if ($suggType == 'modelnum' || $suggType == 'assembly') {
				$query = 'select itemtypes.*, createusers.name AS createdbyname, editedusers.name AS editedbyname,
					codes.id AS hts, codes.hts AS htsview, codes.description AS htsdescription
					FROM itemtypes
					LEFT JOIN users AS createusers ON createusers.id=itemtypes.createdby
					LEFT JOIN users AS editedusers ON editedusers.id=itemtypes.editedby
					LEFT JOIN htscodes AS codes ON codes.id=itemtypes.hts
					WHERE  modelnum="'.$input[0].'" AND make="'.$input[1].'"';
			} else
				$query = 'SELECT * FROM '.$suggTable.' WHERE serial="'.$input[0].'" AND modelnum="'.$input[1].'"';
		} else {
			$query = 'SELECT * FROM '. $suggTable.' WHERE '.$suggType.'="'.$this->db->escape_str($input).'"';
		}
		$query = $this->db->query($query);
		$itemdata = $query->row_array();
		$query->free_result();
		if ($suggType == 'modelnum') {
			$itemdata['items'] = $this->quoteBarcodeSearch($itemdata['id'], false);
// 			log_message('error', print_r($itemdata, TRUE));
		} else if ($suggType == 'serial' || $suggType == 'barcode') {
			$itemdata = array_merge($itemdata, $this->_get_history($itemdata['id']));
		} else if ($suggType == 'assembly') {
			$itemdata = $this->getAssem($itemdata['id']);
		}
		return $itemdata;
	}

	// Filters and returns a list of items (serial'd) for browsing
	function itemList() {
		foreach ($_POST as &$value) {
			$value = $this->db->escape_like_str($value);
		} unset($value);

		$d = array (
			'modelnum' => ($this->input->post('modelnum')) ? $this->input->post('modelnum') : '',
			'atcust' => ($this->input->post('atcust')) ? $this->input->post('atcust') : '',
			'status' => ($this->input->post('status')) ? $this->input->post('status') : '',
			'owner' => ($this->input->post('owner')) ? $this->input->post('owner') : ''
		);

		$d['modelnum'] = preg_replace('/[\-\s]/', '', $d['modelnum']);

		$query = $this->db->query('SELECT * FROM specific_items WHERE '.
			'REPLACE(REPLACE(modelnum, "-", ""), " ", "") LIKE "%'.$d['modelnum'].'%" AND '.
			'atcust LIKE "%'.$d['atcust'].'%" AND '.
			'txtstatus LIKE "%'.$d['status'].'%" AND '.
			'owner LIKE "%'.$d['owner'].'%" '.
			'LIMIT 100');
		$items = $query->result_array();
		$query->free_result();
		$items['items'] = $items;
		$items['filters'] = $d;
		return $items;
	}

	// Gets a list of barcodes for a particular itemtype for the quote screen
	function quoteBarcodeSearch($itemtype_id, $onlyHere = true) {
		if ($onlyHere) $add = ' AND atcustomer=0';
		else $add = '';
		$result = array();
		$items = $this->db->query('SELECT * FROM specific_items WHERE itemtype_id='.$itemtype_id.$add);
		$first = $items->result_array();
		if (isset($first[0]) && $first[0]['assembly'] == 1) {
			foreach($first as $item) {
				$query = $this->db->query('SELECT * FROM specific_items WHERE parent='.$item['parent'].$add);
				$query = $query->result_array();
				$result = array_merge($result, $query);
			}
		} else {
			$result = $first;
		}
		return $result;
	}

	// Gets a list of assembly info for a particular barcode
	function assemBarcodeList($id) {
		$query = $this->db->query('SELECT * FROM specific_items WHERE assembly=0 AND parent=(SELECT parent FROM specific_items WHERE id="'.$id.'")');
		return $query->result_array();
	}

	// Filters and returns a list of itemtypes for browsing.
	function itemtypeList() {
		foreach ($_POST as &$value) {
			$value = $this->db->escape_like_str($value);
		} unset($value);

		$d = array (
			'modelnum' => ($this->input->post('modelnum')) ? $this->input->post('modelnum') : '',
			'make' => ($this->input->post('make')) ? $this->input->post('make') : ''
		);

		$d['modelnum'] = preg_replace('/[\-\s]/', '', $d['modelnum']);

		$query = $this->db->query('SELECT * FROM itemtypes WHERE '.
			'REPLACE(REPLACE(modelnum, "-", ""), " ", "") LIKE "%'.$d['modelnum'].'%" AND '.
			'make LIKE "%'.$d['make'].'%" '.
			'ORDER BY modelnum LIMIT 100');
		$items = $query->result_array();
		$query->free_result();
		$items['items'] = $items;
		$items['filters'] = $d;
		return $items;
	}

	function owedItemsList() {
		foreach ($_POST as &$value) {
			$value = $this->db->escape_like_str($value);
		} unset($value);

		$d = array (
			'name' => ($this->input->post('name')) ? $this->input->post('name') : '',
			'inv_id' => ($this->input->post('inv_id')) ? $this->input->post('inv_id') : '',
			'modelnum' => ($this->input->post('modelnum')) ? $this->input->post('modelnum') : ''
		);

		$d['modelnum'] = preg_replace('/[\-\s]/', '', $d['modelnum']);

		$query = $this->db->query('SELECT * FROM owed_item_list
			WHERE name LIKE "'.$d['name'].'%"
			AND inv_id LIKE "'.$d['inv_id'].'%"
			AND REPLACE(REPLACE(modelnum, "-", ""), " ", "") LIKE "%'.$d['modelnum'].'%" ORDER BY due_date');
		$items = $query->result_array();
		$query->free_result();
		$items['items'] = $items;
		$items['filters'] = $d;
		return $items;
	}

	function queueAddItem($itemtype_id) {
		// Grab the item info from the local DB
		if ($item_info = $this->itemInfo($itemtype_id)) {
			// Create the item object

			$actGet = QUICKBOOKS_QUERY_ITEM;
			switch ($item_info['qbtype']) {
				case ('inventory'):
					$item = new QuickBooks_Object_InventoryItem;
					$actAdd = QUICKBOOKS_ADD_INVENTORYITEM;
					$actMod = QUICKBOOKS_MOD_INVENTORYITEM;
					break;
				case ('noninventory'):
					$item = new QuickBooks_Object_NonInventoryItem;
					$actAdd = QUICKBOOKS_ADD_NONINVENTORYITEM;
					$actMod = QUICKBOOKS_MOD_NONINVENTORYITEM;
					break;
				case ('service'):
				default:
					$item = new QuickBooks_Object_ServiceItem;
					$actAdd = QUICKBOOKS_ADD_SERVICEITEM;
					$actMod = QUICKBOOKS_MOD_SERVICEITEM;
					break;
			}

			if ($item_info['qbrefnum'] != '') { // If the QB ListID is in the DB
				$item->setListID($item_info['qbrefnum']);
				$item->setEditSequence($item_info['qbeditsequence']);
				if ($item_info['qbtype'] != 'inventory') {
					$item->setAccountName($item_info['qbaccountref']);
				} else {
					$item->setIncomeAccountName($item_info['qbaccountref']);
				}
			} else {  // If item has just been added or not updated to QBs yet.
				$item->setFullName($item_info['make'].':'.$item_info['modelnum']);
				if ($item_info['qbtype'] != 'inventory') {	// all items default to type servie and account sales.
					$item->setAccountName('Sales');
				} else {
					$item->setIncomeAccountName('Sales');
				}
			}
			$item->setName($item_info['modelnum']);
			$item->setParentName($item_info['make']);
			if ($item_info['qbtype'] == 'inventory') {
				$item->setSalesDescription($item_info['description']);
				$item->setSalesPrice($item_info['repairrate']);
			} else {
				$item->setDescription($item_info['description']);
				$item->setPrice($item_info['repairrate']);
			}


			$queue = new QuickBooks_Queue('mysql://cncrepair:sausage13@localhost/quickbooks');

			$extra = array(
				'action' => $actAdd,
				$actAdd => $item->asQBXML($actAdd, 'CA3.0', 'CA'),
				$actMod => $item->asQBXML($actMod, 'CA3.0', 'CA'),
				$actGet => $item->asQBXML($actGet, 'CA3.0', 'CA')
			);
			$act = $actAdd;

			if (isset($item_info['qbrefnum']) && $item_info['qbrefnum'] != '') {
				$extra['action'] = $actMod;
				$act = $actGet;
			}

			// If the ListID tag doesn't exist in the mod XML, add a placeholder so that after a query, it'll get updated correctly.
			if (strpos($extra[$actMod], 'ListID') == false) {
				$extra[$actMod] = str_replace('<'.$actMod.'>', '<'.$actMod.">\n\t\t<ListID>PLACEHOLDER</ListID>\n\t\t<EditSequence>PLACEHOLDER</EditSequence>", $extra[$actMod]);
			}

			$queue->enqueue($act, $itemtype_id.$this->_getPin(), 7, $extra);
		}
	}

	function queueGetItem($itemtype_id) {
		$item = $this->db->query('SELECT itemtypes.*, make_map.qbparentref FROM itemtypes LEFT JOIN make_map ON itemtypes.make=make_map.make WHERE itemtypes.id='.$itemtype_id);
		$item = $item->row_array();
		if (isset($item['id'])) {
			$queue = new QuickBooks_Queue('mysql://cncrepair:sausage13@localhost/quickbooks');
			$item['make'] = htmlentities($item['make'], 'ENT_COMPAT' | 'ENT_HTML401', 'ISO-8859-1', false);
			$item['modelnum'] = htmlentities($item['modelnum'], 'ENT_COMPAT' | 'ENT_HTML401', 'ISO-8859-1', false);
			$qbxml =
			'<ItemQueryRq>
				<FullName>'.$item['make'].':'.$item['modelnum'].'</FullName>
			</ItemQueryRq>';
			$extra = array('action' => QUICKBOOKS_QUERY_ITEM, QUICKBOOKS_QUERY_ITEM => $qbxml);
			// log_message('error', print_r($extra, true));
			$queue->enqueue(QUICKBOOKS_QUERY_ITEM, $item['id'].$this->_getPin(), 8, $extra);
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