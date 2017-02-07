<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Item extends CI_Controller {
	function Item() {
		parent::__construct();
		$this->load->model('Item_model', 'itemmodel');
	}

	function _validateUser($loc = '') {
		$pos = $this->user_model->getPos();
		if ($pos['office'] === false) {
			$this->session->set_flashdata('message', 'You don\'t have priviliges to edit items.');
			$this->session->set_flashdata('referrer', 'item/'.$loc);
			header('Location: /cnc/login');
			return false;
		}
		return true;
	}

	function _referred() {
		if (!isset($_POST['itemtype_id'])) {
			header('Location: /item');
			return false;
		}
		return true;
	}

	function index($itemtype_id = '') {
		// User validation section
		if ($this->_validateUser()) {
			$data['title'] = 'Item Management';
			$data['bodyOnload'] = "primDocReady('".$itemtype_id."');";
			$this->load->view('header', $data);

			$tarows = 8;
			if ($this->agent->browser() == "Firefox")
				$tarows = $tarows - 1;
			$data1['tarows'] = $tarows;
			$this->load->view('item/addform', $data1);
			$this->load->view('footer');
		}
	}

	// Gets item information for an itemtype_id and returns it as JSON
	function itemInfo($itemtype_id) {
		$data['filldata'] = $this->itemmodel->itemInfo($itemtype_id);
		$this->load->view('suggestfill', $data);
	}

	function delete($specific = false) {
		if ($this->_referred()) {
			$data['filldata'] = $this->itemmodel->delete($specific);
			$data['text'] = true;
			$this->load->view('suggestfill', $data);
		}
	}

	function specific($barcode = '') {
		switch ($barcode) {
			case 'add':
				if ($this->_referred() && $this->_validateUser('specfic')) {
					$data['filldata'] = $this->itemmodel->save_specific();
					$data['text'] = true;
					$this->load->view('suggestfill', $data);
				}
				break;
			default:
				if ($this->_validateUser('specific')) {
					$data['title'] = 'Item Management';
					$data['bodyOnload'] = "docReady('".$barcode."');";
					$this->load->view('header', $data);

					$tarows = 12;
					if ($this->agent->browser() == "Firefox")
						$tarows = $tarows - 1;
					$data1['tarows'] = $tarows;
					$this->load->view('item/specificitem', $data1);
					$this->load->view('footer');
				}
		}
	}

	function add() {
		if ($this->_referred() && $this->_validateUser()) {
			$data['filldata'] = $this->itemmodel->insert_update();
// 			$data['text'] = true;
			$this->load->view('suggestfill', $data);
		}
	}

	// Gets notes, histories, and procedures for items.
	function view($id, $nORh, $table = '') {
		$data['title'] = 'View ' . $nORh;
		$data['bodyOnload'] = '';
		$data['load']['nomenu'] = true;
		$data['load']['skinny'] = true;
		$this->load->view('header', $data);
		if ($table == '') {
			$notes['notes'] = $this->itemmodel->getNotes($id, $nORh);
		} else {
			$notes['notes'] = $this->itemmodel->getProcs($id, $nORh, $table);
		}
		$this->load->view('item/notes', $notes);
	}

	function get($type = '') {
		if ($type == 'assem') {
			$assem = $this->input->post('id');
			if ($assem != '') {
				$data['filldata'] = $this->itemmodel->getAssem($assem);
				$this->load->view('suggestfill', $data);
			}
		} else if ($type == 'specificassem') {
			$item_id = $this->input->post('item_id');
			if ($item_id != 0) {
				$data['filldata'] = $this->itemmodel->getSpecificAssem($item_id);
				$this->load->view('suggestfill', $data);
			}
		}
	}

	function edit($type) {
		if ($this->_validateUser()) {
			if ($type == 'assem') {
				$data['title'] = 'Edit Assembly';
				$data['bodyOnload'] = 'onReady();';
				$data['load']['nomenu'] = true;
				$data['load']['skinny'] = true;
				$this->load->view('header', $data);

				$tarows = 3;
				if ($this->agent->browser() == "Firefox")
					$tarows = $tarows - 1;
				$data1['tarows'] = $tarows;
				$this->load->view('item/editassem', $data1);
			} else {
				$data['title'] = 'Edit ' . $type;
				$data['bodyOnload'] = 'docReady(\''.$type.'\');';
				$data['load']['nomenu'] = true;
				$data['load']['skinny'] = true;
				$this->load->view('header', $data);
				$notes['type'] = $type;
				$this->load->view('item/enotes', $notes);
			}
		}
	}

	// Saves the assembly in the master item menu
	function save($type = '') {
		if ($this->_referred() && $this->_validateUser()) {
			$data['filldata'] = $this->itemmodel->save($type);
			$data['text'] = true;
			$this->load->view('suggestfill', $data);
		}
	}

	function quoteBarcodeSearch($itemtype_id = 0, $row = 0) {
		$data['title'] = 'Barcode Search';
		$data['bodyOnload'] = '';
		$data['load']['nomenu'] = true;
		$data['load']['skinny'] = true;
		$this->load->view('header', $data);
		$data1['items'] = $this->itemmodel->quoteBarcodeSearch($itemtype_id);
		$data1['row'] = $row;
		$this->load->view('item/barcodelist', $data1);
	}

	function quoteBarcodeList($item_id, $row) {
		$data['filldata']['items'] = $this->itemmodel->assemBarcodeList($item_id);
		$data['filldata']['row'] = $row;
		$this->load->view('suggestfill', $data);
	}

	function oweditemlist() {
		$data['title'] = 'Owed Items List';
		$data['bodyOnload'] = '';
		$this->load->view('header', $data);
		$items = $this->itemmodel->owedItemsList();
		$this->load->view('item/oweditemlist', $items);
		$this->load->view('footer');
	}

	function fullList() {
		$data['title'] = 'Item List';
		$data['bodyOnload'] = "document.getElementById('modelnum').focus();";
		$this->load->view('header', $data);
		$items = $this->itemmodel->itemList();
		$this->load->view('item/itemlist', $items);
		$this->load->view('footer');
	}

	function typeList() {
		$data['title'] = 'Parts List';
		$data['bodyOnload'] = "suggestInstall('make');suggestInstall('modelnum');";
		$this->load->view('header', $data);
		$items = $this->itemmodel->itemtypeList();
		$this->load->view('item/itemtypelist', $items);
		$this->load->view('footer');
	}
	// Controller for the hts popup editor on the edit items page
	function htscode() {
		$data['title'] = 'HTS List';
		$data['bodyOnload'] = '';
		$data['load']['nomenu'] = true;
		$data['load']['skinny'] = true;
		$this->load->view('header', $data);
		$htscodes['htscodes'] = $this->itemmodel->htscodes();
// 		log_message('error', print_r($htscodes, TRUE));
		$this->load->view('item/htscodes', $htscodes);
		$this->load->view('footer');
	}

	function queueAddItem($itemtype_id) {
		$this->itemmodel->queueAddItem($itemtype_id);
	}

	function queueGetItem($itemtype_id) {
		$this->itemmodel->queueGetItem($itemtype_id);
	}

	function fullItemListIDGrab() {
		$items = $this->db->query('SELECT id FROM itemtypes');
		$items = $items->result_array();
		foreach ($items as $item) {
			$this->itemmodel->queueGetItem($item['id']);
		}
	}

	function fullItemListPush() {
		$items = $this->db->query('SELECT id FROM itemtypes');
		$items = $items->result_array();
		foreach ($items as $item) {
			$this->itemmodel->queueAddItem($item['id']);
		}
	}
}
?>