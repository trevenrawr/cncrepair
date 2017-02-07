<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Quote_model extends CI_Model {

	function Quote_model() {
		parent::__construct();
		$this->load->library('email');
	}

	// Creates and Updates quote information
	function insert() {
		// Make $_POST into a MySQL escaped string
		foreach ($_POST as &$value) {
			$value = $this->db->escape_str($value);
		} unset($value);

		$d = array (
			'id' => $this->input->post('quote_id'),
			'billto' => $this->input->post('cust_id'),
			'shipname'=> $this->input->post('shipname'),
			'shipaddress' => $this->input->post('shipaddress'),
			'shipaddress1' => $this->input->post('shipaddress1'),
			'shipcity' => $this->input->post('shipcity'),
			'shipstate' => $this->input->post('shipstate'),
			'shipcountry' => $this->input->post('shipcountry'),
			'shipzip' => $this->input->post('shipzip'),
			'phone_id' => $this->input->post('phone_id'),
			'fax_id' => $this->input->post('fax_id'),
			'email_id' => $this->input->post('email_id'),
			'total' => $this->input->post('total'),
			'notes' => $this->input->post('notes'),
			'terms' => $this->input->post('terms'),
			'publicnotes' => $this->input->post('publicnotes'),
			'itemtotal' => $this->input->post('itemtotal'),
			'purchaseorder' => $this->input->post('purchaseorder'),
			'caller' => $this->input->post('caller'),
			'taxrate' => $this->input->post('taxrate'),
			'taxtype' => $this->input->post('taxtype'),
			'taxid'	=> $this->input->post('taxid'),
			'taxamnt' => $this->input->post('taxamnt')
		);

		// Unset certain items so that null values get stored as default (NULL)
		$unsets = array('phone_id', 'fax_id', 'email_id');
		foreach ($unsets as $unset) {
			if ($d[$unset] == '') unset($d[$unset]);
		}

		$update = array();
		foreach ($d as $k => $v)
			$update[$k] = $k.'="'.$v.'"';
		unset($k); unset($v);

		$this->db->trans_start();

		// Update the last activity of the customer
		$this->db->query('UPDATE customers SET qblastactivity=CURRENT_TIMESTAMP() WHERE id='.$d['billto']);

		$query = 'INSERT INTO quotes ('.implode(', ', array_keys($d)).') '.
			 'VALUES ("'.implode('", "', $d).'") '.
			 'ON DUPLICATE KEY UPDATE '.implode(', ', array_values($update));
		$this->db->query($query);

		if (($id = $this->db->insert_id()) == 0) { // If the row was updated, the id from LAST_INSERT_ID() is 0.
			$id = $d['id'];
		}

		// Update/create timestamps
		if ($this->db->affected_rows() == 1) {
			$this->db->query('UPDATE quotes SET createdby='.$this->session->userdata('id').', editedby='.$this->session->userdata('id').', lastedited=CURRENT_TIMESTAMP() WHERE id='.$id);
		} else {
			$this->db->query('UPDATE quotes SET editedby='.$this->session->userdata('id').', lastedited=CURRENT_TIMESTAMP() WHERE id='.$id);
		}

		if (!isset($d['phone_id'], $d['fax_id'], $d['email_id'])) {
			$pid = (isset($d['phone_id'])) ? $d['phone_id'] : 'NULL';
			$fid = (isset($d['fax_id'])) ? $d['fax_id'] : 'NULL';
			$eid = (isset($d['email_id'])) ? $d['email_id'] : 'NULL';
			$this->db->query('UPDATE quotes SET phone_id='.$pid.', fax_id='.$fid.', email_id='.$eid.' WHERE id='.$id);
		}

		if ($this->input->post('itemsDel') != '') {
			$toDels = explode('|', $this->input->post('itemsDel'));
			foreach ($toDels as $toDel) {
				// Only delete if it's marked for deletion and if it is associated with the current quote (just in case)
				if ($toDel != '')
					$this->db->query('DELETE FROM quoteitems WHERE id='.$toDel.' AND quote_id='.$id);
			}
		}

		$items = $this->_readItems($id);
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

				$update = array();
				foreach ($item as $k => $v)
					$update[$k] = $k.'="'.$v.'"';
				unset($k); unset($v);

				$query = 'INSERT INTO quoteitems ('.implode(', ', array_keys($item)).') '.
					'VALUES ("'.implode('", "', $item).'") '.
					'ON DUPLICATE KEY UPDATE '.implode(', ', array_values($update));
				$this->db->query($query);
				if (($qi_id = $this->db->insert_id()) == 0) $qi_id = $item['id'];

				// Get a current list of quoteitems which have histories attached
				$qi_items = $this->db->query('SELECT quoteitem_items.*, histories.item_id FROM quoteitem_items INNER JOIN histories ON quoteitem_items.history_id=histories.id WHERE quoteitem_id='.$qi_id);
				$qi_items = $qi_items->result_array();
				foreach ($qi_items as $qi_item) {
					foreach ($old_item_ids as $old_item_id) {
						// Match the old item with a history/qi pair for deletion
						if ($qi_item['item_id'] == $old_item_id) {
							$this->db->query('DELETE FROM quoteitem_items WHERE quoteitem_id='.$qi_item['quoteitem_id'].' AND history_id='.$qi_item['history_id']);
							$items = $this->db->query('SELECT COUNT(*) AS items FROM quoteitem_items WHERE history_id='.$qi_item['history_id']);
							$items = $items->row_array();
							// If there aren't other quotes pointing at this item, remove onhold status, but only if it is "held" not locked.
							if ($items['items'] < 1) {
								$this->db->query('UPDATE items SET onhold=0 WHERE id='.$old_item_id.' AND onhold=1');
							}
						}
					}
				}
				foreach ($item_ids as $item_id) {
					if ($item_id != '') {
						$query = $this->db->query('SELECT id FROM histories WHERE item_id='.$item_id.' ORDER BY id');
						$history_id = $query->result_array();
						$query->free_result();
						$history_id = $history_id[count($history_id)-1]['id'];
						$this->db->query('INSERT INTO quoteitem_items (quoteitem_id, history_id) VALUES ('.$qi_id.', '.$history_id.')');
						// Set as onhold only if it isn't held or locked already.
						$this->db->query('UPDATE items SET onhold=1 WHERE id='.$item_id.' AND onhold=0');
					}
				}
			}
		} unset($item);

		$this->db->trans_complete();

		$mess = 'Quote saved successfully!';
		if ($this->db->trans_status() === FALSE)
			$mess = 'Quote not saved.';
		return $mess.$id;
	}

	function delete($id) {
		$this->db->query('DELETE FROM quotes WHERE id='.$id);
		if ($this->db->affected_rows() > 0) {
			return 'Quote deleted successfully!';
		}
		else return 'Quote not deleted (perhaps did not exist).';
	}

	function _readItems($quote_id) {
		$result = array();
		$j = 0; // This variable becomes the index in the result array, as the item "numbers" might not be consecutive
		for ($i = 0; $i < $_POST['currRow']; $i++) {
			if (isset($_POST['itemtype_id'.$i])) {
				$item = array(
					'quote_id' => $quote_id,
					'id' => $this->input->post('quoteitem_id'.$i),
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
				$result[$j++] = $item;
			}
		}
		return $result;
	}

	function assemblyItems() {
		$id = $this->input->post('itemtype_id');
		$query = $this->db->query('SELECT * FROM assembly_view WHERE parent='.$id);
		return $query->result_array();
	}

	function getMessage($view) {
		$query = $this->db->query('SELECT * FROM messages WHERE view="'.$view.'"');
		$query = $query->row_array();
		return $query;
	}

	function getMessages() {
		$query = $this->db->query('SELECT * FROM messages');
		$messages = $query->result_array();
		return $messages;
	}

	function saveMessages() {
		foreach ($_POST as &$value) {
			$value = $this->db->escape_str($value);
		} unset($value);

		$this->db->trans_start();

		foreach ($_POST as $view => $message) {
			$this->db->query('UPDATE messages SET message="'.$message.'" WHERE view="'.$view.'"');
		}

		$this->db->trans_complete();

		$mess = 'Messages updated successfully!';
		if ($this->db->trans_status() == false)
			$mess = 'Messages not changed.';
		return $mess;
	}

	function getQuote($id = 0) {
		if ($id == 0)
			$id = $this->input->post('quote_id');

		$query = $this->db->query(
			'SELECT quotes.*, DATE_FORMAT(quotes.created, "%b %d, %Y") AS date,
			DATE_FORMAT(quotes.lastedited, "%b %d %T") AS datelastedited,
			DATE_FORMAT(quotes.sent, "%b %d, %Y") AS emaildate,
			createusers.name AS createdbyname, editedusers.name AS editedbyname
			FROM (quotes LEFT JOIN users AS createusers ON createusers.id=quotes.createdby)
			LEFT JOIN users AS editedusers ON editedusers.id=quotes.editedby WHERE quotes.id='.$id
		);
		$result['quote'] = $query->row_array();
		$query->free_result();

		if (!isset($result['quote']['billto'])) {
			return $result;
		}

		$query = $this->db->query('SELECT * FROM quote_view WHERE quote_id='.$id);
		$result['items'] = $query->result_array();
		$query->free_result();

		foreach ($result['items'] as &$item) {
			// Collect barcodes for display
			$barcodes = $this->db->query(
				'SELECT quoteitem_items.*, histories.item_id, items.barcode
				FROM (quoteitem_items INNER JOIN histories ON quoteitem_items.history_id=histories.id)
				INNER JOIN items ON histories.item_id=items.id
				WHERE quoteitem_items.quoteitem_id='.$item['id']
			);
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

		$query = $this->db->query('SELECT * FROM customers WHERE id='.$result['quote']['billto']);
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

	function custHistory($full = false) {
		$id = $this->db->escape_str($this->input->post('id'));
		$type = $this->input->post('type');

		if ($id == '') $full = true;

		if ($full) {
			$where = "1";
		} else if ($type == "modelnum") { // We need to do a special search for quotes/invoices with those items, then do a quote/invoice search
			$query = $this->db->query('SELECT DISTINCT quote_id AS id FROM quote_view WHERE modelnum LIKE "'.$id.'%"');
			$quotes = $query->result_array();
			$query->free_result();

			if (count($quotes) > 0) {
				foreach ($quotes as &$tid) {
					$tid = 'refnum='.$tid['id'];
				} unset($tid);
				$qids = implode($quotes, ' OR ');
			} else {
				$qids = '0';
			}

			$query = $this->db->query('SELECT DISTINCT inv_id AS id FROM invoice_view WHERE modelnum LIKE "'.$id.'%"');
			$invoices = $query->result_array();
			$query->free_result();

			if (count($invoices) > 0) {
				foreach ($invoices as &$tid) {
					$tid = 'refnum='.$tid['id'];
				} unset($tid);
				$iids = implode($invoices, ' OR ');
			} else {
				$iids = '0';
			}

			$where = '(type="Quote" AND ('.$qids.')) OR (type="Invoice" AND ('.$iids.'))';
		} else {
			$where = $type.' LIKE "'.$id;

			if ($type != 'refnum') $where .= '%"';
			else $where .= '"';
		}
		$query = 'SELECT * FROM quote_search WHERE '.$where.' ORDER BY ts DESC LIMIT 100';
		$query = $this->db->query($query);
		return $query->result_array();
	}

	function getID() {
		$query = $this->db->query('SELECT Auto_increment FROM information_schema.tables WHERE table_name="quotes"');
		$data = $query->row_array();
		$query->free_result();
		return $data['Auto_increment'];
	}
}