<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Customer extends CI_Controller {
	function Customer() {
		parent::__construct();
		$this->load->model('Customer_model', 'custmodel');
	}

	function _validateUser() {
		$pos = $this->user_model->getPos();
		if ($pos['office'] === false) {
			$this->session->set_flashdata('message', 'You don\'t have priviliges to edit customers.');
			$this->session->set_flashdata('referrer', 'customer');
			header('Location: /cnc/login');
			return false;
		}
		return true;
	}

	function _referred() {
		if (!isset($_POST['name'])) {
			header('Location: /customer');
			return false;
		}
		return true;
	}

	function index($cust_id = '') {
		if ($this->_validateUser()) {
			$data['title'] = 'Customer Add/Update';
			$data['bodyOnload'] = "docReady('".$cust_id."');";
			$this->load->view('header', $data);

			$tarows = 8;
			if ($this->agent->browser() == "Firefox")
				$tarows = $tarows - 1;
			$data1['tarows'] = $tarows;
			$data1['addtype'] = 'add';
			$this->load->view('customer/addform', $data1);
			$this->load->view('footer');
		}
	}

	function customerInfo($cust_id) {
		$data['filldata'] = $this->custmodel->customerInfo($cust_id);
		$this->load->view('suggestfill', $data);
	}

	function add() {
		if ($this->_referred() && $this->_validateUser()) {
			$data['filldata'] = $this->custmodel->insert_update();
			$data['text'] = true;
			$this->load->view('suggestfill', $data);
		}
	}

	function edit() {
		if ($this->_referred() && $this->_validateUser()) {
			$data['filldata'] = $this->custmodel->insert_update();
			$data['text'] = true;
			$this->load->view('suggestfill', $data);
		}
	}

	// Controller for the customer popup form.  Parameter name is grabbed from the add-quote form, name box.
	function nomenu($name = '') {
		if ($this->_validateUser()) {
			$data['title'] = 'Customer Add/Update';
			$data['bodyOnload'] = 'docReady();';
			$data['load']['nomenu'] = true;
			$this->load->view('header', $data);
			$name = urldecode($name);
			$data1['custname'] = $name;
// 			log_message('error', print_r($name, TRUE));
			$tarows = 8;
			if ($this->agent->browser() == "Firefox")
				$tarows = $tarows - 1;
			$data1['tarows'] = $tarows;
			$data1['addtype'] = 'edit';
			$data1['showResult'] = 'false';
			$this->load->view('customer/addform', $data1);
			$this->load->view('footer');
		}
	}

	function view($id, $nORh) {
		$data['title'] = 'View ' . $nORh;
		$data['bodyOnload'] = '';
		$data['load']['nomenu'] = true;
		$data['load']['skinny'] = true;
		$this->load->view('header', $data);
		$notes['notes'] = $this->custmodel->getNotes($id, $nORh);
		$this->load->view('item/notes', $notes);
	}

	function queueAddCustomer($cust_id) {
		$this->custmodel->queueAddCustomer($cust_id);
	}

	function queueGetCustomer($cust_id) {
		$this->custmodel->queueGetCustomer($cust_id);
	}

	function fullCustomerListPush() {
		$custs = $this->db->query('SELECT * FROM customers');
		$custs = $custs->result_array();
		foreach ($custs as $cust) {
			$this->custmodel->queueAddCustomer($cust['id']);
		}
	}

/*	function fullList() {
		$data['title'] = 'Customer List';
		$data['bodyOnload'] = '';
		$this->load->view('header', $data);
		$items['items'] = $this->custmodel->customerList();
		$this->load->view('item/itemlist', $items);
		$this->load->view('footer');
	} */
}
?>