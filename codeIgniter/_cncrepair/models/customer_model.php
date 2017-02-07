<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

// Require the QB Queueing class
require_once './qb/QuickBooks/Frameworks.php';
if (!defined('QUICKBOOKS_FRAMEWORKS'))
	define('QUICKBOOKS_FRAMEWORKS', QUICKBOOKS_FRAMEWORK_QUEUE | QUICKBOOKS_FRAMEWORK_OBJECTS);
require_once './qb/QuickBooks.php';

class Customer_model extends CI_Model {

	function Customer_model() {
		parent::__construct();
	}

	function insert_update() {
		// Make $_POST into a MySQL escaped string
		foreach ($_POST as &$value) {
			$value = $this->db->escape_str($value);
		} unset($value);

		// Pull all the information from the $_POST array
		$d = array (
			'id' => $this->input->post('cust_id'),
			'name' => $this->input->post('name'),
			'address' => $this->input->post('address'),
			'address1' => $this->input->post('address1'),
			'city' => $this->input->post('city'),
			'state' => $this->input->post('state'),
			'country' => $this->input->post('country'),
			'zip' => $this->input->post('zip'),
			'terms' => $this->input->post('terms'),
			'shipname' => $this->input->post('shipname'),
			'shipaddress' => $this->input->post('shipaddress'),
			'shipaddress1' => $this->input->post('shipaddress1'),
			'shipcity' => $this->input->post('shipcity'),
			'shipstate' => $this->input->post('shipstate'),
			'shipcountry' => $this->input->post('shipcountry'),
			'shipzip' => $this->input->post('shipzip'),
			'carrier' => $this->input->post('carrier'),
			'notes' => $this->input->post('notes'),
			'currency' => $this->input->post('currency'),
			'tax' => $this->input->post('tax'),
			'taxid' => $this->input->post('taxid')
		);
		//log_message('error', print_r($d, true));

		// Generate the 'update' array with ""-escaped values
		$update = array();
		foreach ($d as $k => $v)
			$update[$k] = $k.'="'.$v.'"';
		unset($k); unset($v);
		//log_message('error', print_r($update, true));

		// Add/update databse.
		$check = $this->db->query('SELECT * FROM `customers` WHERE name = "'.$d['name'].'"');
// 		log_message('error', print_r($check->row('name'), TRUE));
// 		log_message('error', print_r($d['id'], TRUE));
		if($d['id'] == '' && $check->row('name')) {
			$mess = 'Error, Customer name already exists in database.';
			return $mess;
		} else {

			$this->db->trans_start();

			// Generate query to INSERT if doesn't exist, or to UPDATE if does.
			$query = 'INSERT INTO customers ('.implode(', ', array_keys($d)).') '.
				'VALUES ("'.implode('", "', $d).'") '.
				'ON DUPLICATE KEY UPDATE '.implode(', ', array_values($update));
			$this->db->query($query);
			$cra = $this->db->affected_rows();

			// On update, insert_id == 0, so use the one from the form
			$id = $this->db->insert_id();
			if ($id == 0) $id = $d['id'];

			// Timestamp Database changes
			if ($cra == 1) { // Created
				$this->db->query('UPDATE customers SET createdby='.$this->session->userdata('id').', editedby='.$this->session->userdata('id').', created=CURRENT_TIMESTAMP() WHERE id='.$id);
			} else if ($cra == 2) {
				$this->db->query('UPDATE customers SET editedby='.$this->session->userdata('id').' WHERE id='.$id);
			}

			// Calls for the delete of deleted phones and emails
			$pORera = $this->_deletePorE('phone', $id);

			$ra = $this->_deletePorE('email', $id);
			$pORera = ($ra > $pORera) ? $ra : $pORera;

			// Read in the phones, empty phone numbers get ignored
			// The JS on the page formats the phones to ###-###-####(x####) if in Canada or US
			$phones = $this->_readPorE('phone', $id);
			$ra = $this->_insertPorE('phones', $phones, $id);
			$pORera = ($ra > $pORera) ? $ra : $pORera;

			// Read in the emails, in much the same way the phones
			// Emails verified before entry submitted
			$emails = $this->_readPorE('email', $id);
			$ra = $this->_insertPorE('emails', $emails, $id);
			$pORera = ($ra > $pORera) ? $ra : $pORera;

			$this->db->trans_complete();

			switch ($cra){
				case 2:
					$mess = 'Customer updated successfully!';
					$this->queueAddCustomer($id);
					break;
				case 1:
					$mess = 'Customer created successfully!';
					$this->queueAddCustomer($id);
					break;
				case 0:
				default:
					switch ($pORera) {
						case 2:
						case 1:
							$mess = 'Customer updated successfully!';
							$this->queueAddCustomer($id);
							break;
						case 0:
						default:
							$mess = 'Customer information not changed.';
					}
			}

			$this->_updateCheck();

			if ($this->db->trans_status() === FALSE)
				$mess = 'Customer creation FAILED.';
			return $mess;
		}
	}

	// This removes phones and emails stored in the 'phonesDel' or 'emailsDel' hidden input
	// The phone/email ids are separated by '|' and exploded into an array
	function _deletePorE($pORe, $cust_id) {
		$ra = 0;
		if ($this->input->post($pORe.'sDel') != '') {
			$toDels = explode('|', $this->input->post($pORe.'sDel'));
			foreach ($toDels as $toDel) {
				// Only delete if it's marked for deletion and if it is associated with the current customer (just in case)
				if ($toDel != '') {
					$query = $this->db->query('SELECT id FROM '.$pORe.'s WHERE id='.$toDel.' AND cust_id='.$cust_id);
					$query = $query->result_array();
					$ra = (count($query) > $ra) ? count($query) : $ra;
					$this->db->query('DELETE FROM '.$pORe.'s WHERE id='.$toDel.' AND cust_id='.$cust_id);
				}
			}
		}
		return ($ra * 2); // $ra is 1 if something was deleted but we need it to be an update code.
	}

	// Reads the phones and emails from the submitted data.
	function _readPorE($pORe, $cust_id) {
		$result = array();
		for ($i = 0; $i < (int)$this->input->post($pORe.'currRow'); $i++) {
			if (isset($_POST[$pORe.'_id'.$i])) { // If a row exists, xxxxx_id will be there, albeit = ''
				$id = $this->input->post($pORe.'_id'.$i);
				if ($pORe == 'phone' && $this->input->post('number'.$i) != '') {
					$phone = array(
						'id' => ($this->input->post('phonecust_id'.$i) != $cust_id) ? '' : $id, // If the phone already exists for another customer, copy it
						'cust_id' => $cust_id,
						'type' => $this->input->post('type'.$i),
						'num' => $this->input->post('number'.$i),
						'contact' => $this->input->post('contact'.$i)
					);
					$result[] = $phone;
				} else if ($pORe == 'email' && $this->input->post('email'.$i) != '') {
					$email = array(
						'id' => ($this->input->post('emailcust_id'.$i) != $cust_id) ? '' : $id,
						'cust_id' => $cust_id,
						'email' => $this->input->post('email'.$i),
						'name' => $this->input->post('name'.$i)
					);
					$result[] = $email;
				}
			}
		}
		return $result;
	}

	// Runs the query to insert or update a phone or email entry
	function _insertPorE($table, $pORes, $cust_id) {
		$ra = 0;
		foreach ($pORes as $pORe) {
			if ($pORe['cust_id'] === $cust_id) {
				$update = array();
				foreach ($pORe as $k => $v) {
					$update[$k] = $k.'="'.$v.'"';
				} unset($k); unset($v);
				$query = 'INSERT INTO '.$table.' ('.implode(', ', array_keys($pORe)).') VALUES ("'.implode('", "', $pORe).'") '.
				'ON DUPLICATE KEY UPDATE '.implode(', ', array_values($update));
				$this->db->query($query);
				$ra = ($this->db->affected_rows() > $ra) ? $this->db->affected_rows() : $ra;
			}
		} unset($pORe);
		return ($ra > 0) ? 2 : 0;
	}

	function _updateCheck($limit = 25) {
		// Get customers who have had activity (quote or invoice) in the past 60 days or who have outstanding balances
		$customers = $this->db->query('SELECT id FROM customers WHERE qbqueued = 0 AND
				(TIMESTAMPDIFF(DAY, qblastactivity, CURRENT_TIMESTAMP()) < 30 OR balance <> 0)
			AND (TIMESTAMPDIFF(DAY, qblastupdate, CURRENT_TIMESTAMP()) > 1 OR qblastupdate = 0)
			LIMIT '.$limit);
		$customers = $customers->result_array();
		foreach ($customers as $customer) {
			$this->queueGetCustomer($customer['id']);
		}
	}

	// Provides the suggestion list for customers
	function suggest() {
		$input = $this->db->escape_like_str($this->input->post('q'));
		$query = $this->db->query('SELECT name FROM customers WHERE name LIKE "'.$input.'%"');
		$ret['suggs'] = $query->result_array();
		$ret['input'] = $input;

		return $ret;
	}

	// Collects customer notes
	function getNotes($id, $nORh) {
		$query = $this->db->query('SELECT ' . $nORh . ', balance FROM customers WHERE id='.$id);
		$notes = $query->row_array();
		$owedItems = $this->db->query('SELECT * FROM owed_item_list WHERE cust_id='.$id);
		$owedItems = $owedItems->result_array();
		$balance = $notes['balance'];
		$notes = $notes[$nORh];

		if ($balance != '') $notes .= "\n\nCurrent Balance: $".$balance;
		if (count($owedItems) > 0) {
			$notes .= "\n\nOwed Items:\n";
			foreach ($owedItems as $item) {
				$notes .= '-  '.$item['modelnum'].'   Due: '.$item['due_date']."\n";
			}
		}

		return $notes;
	}

	// Uses a customer id to find the customer data
	function customerInfo($cust_id) {
		$name = $this->db->query('SELECT name FROM customers WHERE id='.$cust_id);
		$name = $name->row_array();
		if (!isset($name['name'])) return false;
		return $this->filldata($name['name']);
	}

	// Returns data from the customer name sent either via $_POST or from a controller
	function filldata($input = '') {
		$this->_updateCheck(10);

		if (isset($_POST['name'])) {
			$input = $this->db->escape_str($_POST['name']);
		} else if (isset($_POST['owner'])) {
			$input = $this->db->escape_str($_POST['owner']);
		}

// 		$query = $this->db->query('SELECT * FROM customers WHERE name="'.$input.'"');
		$query = $this->db->query('SELECT customers.*, createusers.name AS createdbyname, editedusers.name AS editedbyname
					FROM customers LEFT JOIN users AS createusers ON createusers.id=customers.createdby
					LEFT JOIN users AS editedusers ON editedusers.id=customers.editedby
					WHERE customers.name="'.$input.'"');

		$customerdata = $query->row_array();

		$customerdata['inname'] = $input;
		if ($query->num_rows() == 1) {
			$cust_id = $customerdata['id'];
			$phones = $this->db->query('SELECT * FROM phones WHERE cust_id='.$cust_id.' ORDER BY type');
			$phones = $phones->result_array();
			$customerdata['phones'] = $phones;

			$emails = $this->db->query('SELECT * FROM emails WHERE (cust_id='.$cust_id.')');
			$emails = $emails->result_array();
			$customerdata['emails'] = $emails;

			if (isset($_POST['owner']))
				$customerdata['owner'] = true;

			return $customerdata;
		}
		$query->free_result();

		return $customerdata;
	}

	// Add customer to the quickbooks que
	function queueAddCustomer($cust_id) {
		// Grab the customer info from the local DB
		if ($cust_info = $this->customerInfo($cust_id)) {
			// Create the customer object
			$cust = new QuickBooks_Object_Customer();

			if ($cust_info['qbrefnum'] != '') { // If the QB ListID is in the DB
				$cust->setListID($cust_info['qbrefnum']);
				$cust->setEditSequence($cust_info['qbeditsequence']);
// 				log_message('error', print_r($cust, TRUE));
			} else {
				$cust->setFullName($cust_info['name']);
// 				log_message('error', print_r($cust), TRUE);
			}

			$cust->setName($cust_info['name']);
// 			log_message('error', print_r($cust, TRUE));
			$cust->setCompanyName($cust_info['name']);
			$cust->setBillAddress($cust_info['address'], $cust_info['address1'], '', '', '', $cust_info['city'], '', $cust_info['state'], $cust_info['zip'], $cust_info['country'], '');
			$cust->setShipAddress($cust_info['shipaddress'], $cust_info['shipaddress1'], '', '', '', $cust_info['shipcity'], '', $cust_info['shipstate'], $cust_info['shipzip'], $cust_info['shipcountry'], '');
			if (isset($cust_info['phones'][0])) {
				$cust->setPhone($cust_info['phones'][0]['num']);
				$cust->setContact($cust_info['phones'][0]['contact']);
			}
			foreach ($cust_info['phones'] as $phone) {
				$altSet = false;
				$faxSet = false;
				if ($phone['type'] == 'fax' && !$faxSet) {
					$cust->setFax($phone['num']);
				} else if ($phone['type'] != 'fax' && !$altSet) {
					$cust->setAltPhone($phone['num']);
				}
			}
			if (isset($cust_info['emails'][0]))
				$cust->setEmail($cust_info['emails'][0]['email']);
			$cust->setCurrencyName($cust_info['currency']);
			switch ($cust_info['terms']) {
				case 'net30':
					$cust->setTermsName('NET 30'); // ListID = '20000-1209163811'
					break;
				case 'wire':
					$cust->setTermsName('wire'); // ListID = '80000-1209163812'
					break;
				case 'cod':
					$cust->setTermsName('COD'); // ListID = '40000-1209163811'
					break;
				case 'credit':
				default:
					$cust->setTermsName('CREDIT CARD'); // ListID = '10000-1209163811'
			}
			switch ($cust_info['tax']) {
				case 'GSTPST':
					$cust->setSalesTaxCodeName('S');
					break;
				case 'GST':
					$cust->setSalesTaxCodeName('G');
					break;
				case 'HST':
					$cust->setSalesTaxCodeName('H');
					break;
				case 'none':
				default:
					$cust->setSalesTaxCodeName('.');
			}

			$queue = new QuickBooks_Queue('mysql://cncrepair:sausage13@localhost/quickbooks');

			$extra = array(
				'action' => QUICKBOOKS_ADD_CUSTOMER,
				QUICKBOOKS_ADD_CUSTOMER => $cust->asQBXML(QUICKBOOKS_ADD_CUSTOMER, 'CA3.0', 'CA'),
				QUICKBOOKS_MOD_CUSTOMER => $cust->asQBXML(QUICKBOOKS_MOD_CUSTOMER, 'CA3.0', 'CA'),
				QUICKBOOKS_QUERY_CUSTOMER => $cust->asQBXML(QUICKBOOKS_QUERY_CUSTOMER, 'CA3.0', 'CA')
			);
// 			$extra[QUICKBOOKS_ADD_CUSTOMER] = preg_replace('/Name\>/', 'FullName>', $extra[QUICKBOOKS_ADD_CUSTOMER], 2);
			$act = QUICKBOOKS_ADD_CUSTOMER;

			if (isset($cust_info['qbrefnum']) && $cust_info['qbrefnum'] != '') {
				$extra['action'] = QUICKBOOKS_MOD_CUSTOMER;
				$act = QUICKBOOKS_QUERY_CUSTOMER;
			}
// 			log_message('error', print_r($extra, TRUE));
			$queue->enqueue($act, $cust_id.$this->_getPin(), 7, $extra);
		}
	}

	function queueGetCustomer($cust_id) {
		if ($cust_id == 0) return;
		$name = $this->db->query('SELECT name FROM customers WHERE id='.$cust_id);
		$name = $name->row_array();
		if (!isset($name['name']))
			return false;
		$cust = new QuickBooks_Object_Customer();
		$cust->setFullName($name['name']);
		$queue = new QuickBooks_Queue('mysql://cncrepair:sausage13@localhost/quickbooks');
		$extra = array('action' => QUICKBOOKS_QUERY_CUSTOMER, QUICKBOOKS_QUERY_CUSTOMER => $cust->asQBXML(QUICKBOOKS_QUERY_CUSTOMER, 'CA3.0', 'CA'));
		// log_message('error', print_r($extra, true));
		$queue->enqueue(QUICKBOOKS_QUERY_CUSTOMER, $cust_id.$this->_getPin(), 8, $extra);
		$this->db->query('UPDATE customers SET qbqueued=1 WHERE id='.$cust_id);
	}

	function syncQBCustomers() {

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