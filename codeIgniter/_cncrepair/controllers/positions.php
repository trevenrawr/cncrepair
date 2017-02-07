<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Positions extends CI_Controller {
	function Positions() {
		parent::__construct();
		$this->load->model('Item_model', 'itemmodel');
	}
	
	function _validateUser($in) {
		$pos = $this->user_model->getPos();
		if ($pos[$in] === false) {
			$this->session->set_flashdata('message', 'You don\'t have priviliges to use the '.$in.' tool.');
			$this->session->set_flashdata('referrer', 'positions/'.$in);
			header('Location: /cnc/login');
			return false;
		}
		return true;
	}
	
	function _referred($from) {
		if (!isset($_POST['barcode']) && !isset($_POST['barcode0'])) {
			header('Location: /positions/'.$from);
			return false;
		}
		return true;
	}
	
	function index() {
		$this->repair();
	}
	
	function save($pos) {
		if ($this->_referred($pos) && $this->_validateUser($pos)) {
			$data['filldata'] = $this->itemmodel->positions($pos);
			$data['text'] = true;
			$this->load->view('suggestfill', $data);
		}
	}
	
	function viewQueued($pos) {
		$data['title'] = 'View '.$pos.' queue';
		$data['bodyOnload'] = '';
		$data['load']['nomenu'] = true;
		$data['load']['skinny'] = true;
		$this->load->view('header', $data);
		
		$data1['queue'] = $this->itemmodel->getQueue($pos);
		$data1['pos'] = $pos;
		$data1['footer'] = true;
		$this->load->view('/positions/queueview', $data1);
	}
	
	function viewQueue() {
		$data['title'] = 'View work queue';
		$data['bodyOnload'] = "document.getElementById('modelnum').focus();";
		$this->load->view('header', $data);
		
		$data1['queue'] = $this->itemmodel->getQueue();
		$data1['pos'] = '';
		$this->load->view('/positions/queueview', $data1);
	}
	
	function _index($title, $barcode, $onload = "suggestInstall('barcode');") {
		$data['title'] = $title.' Station';
		if ($barcode != '')
			$onload .= "goBarcode('".$barcode."');";
		$data['bodyOnload'] = $onload;
		$this->load->view('header', $data);
		$tarows = 22;
		if ($this->agent->browser() == "Firefox")
			$tarows = $tarows - 1;
		$data1['tarows'] = $tarows;
		$this->load->view('positions/positions', $data1);
		$this->load->view('footer');
	}
	
	/* ---UNPACKING--- */
	function unpacking($barcode = '') {
		if ($this->_validateUser('unpacking')) {
			switch ($barcode) {
				case 'incoming':
					$data['filldata'] = $this->itemmodel->incoming_quote_items();
					$this->load->view('suggestfill', $data);
					break;
				default:  // index
					$this->_index('Unpacking', $barcode, "suggestInstall('barcode');suggestInstall('serial');suggestInstall('name');");
			}
		}
	}
	
	/* ---RECEIVING--- */
	function receiving($barcode = '') {
		if ($this->_validateUser('receiving')) {
			switch ($barcode) {
				case 'invlist':
					$data['filldata'] = $this->itemmodel->rec_inv_list();
					$this->load->view('suggestfill', $data);
					break;
				default:  // index
					$this->_index('Receiving', $barcode);
			}
		}
	}
	
	/* ---CLEANING--- */
	function cleaning($barcode = '') {
		if ($this->_validateUser('cleaning')) {
			$this->_index('Cleaning', $barcode);
		}
	}

	/* ---REPAIR--- */
	function repair($barcode = '') {
		if ($this->_validateUser('repair')) {
			$this->_index('Repair', $barcode, "suggestInstall('barcode0');");
		}
	}
	
	/* ---ASEMBLING--- */
	function assembling($barcode = '') {
		if ($this->_validateUser('assembling')) {
			$this->_index('Assembling', $barcode, "suggestInstall('barcode');suggestInstall('assembly');");
		}
	}
	
	/* ---TESTING--- */
	function testing($barcode = '') {
		if ($this->_validateUser('testing')) {
			$this->_index('Testing', $barcode, "suggestInstall('barcode0');");
		}
	}
	
	/* ---SHIPPING--- */
	function shipping($barcode = '') {
		if ($this->_validateUser('shipping')) {
			switch ($barcode) {
				case 'invlist':
					$data['filldata'] = $this->itemmodel->ship_inv_list();
					$this->load->view('suggestfill', $data);
					break;
				default:  // index
					$this->_index('Shipping', $barcode, "suggestInstall('barcode0');");
			}
		}
	}
}
?>