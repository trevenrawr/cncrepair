<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Cnc extends CI_Controller {

	function Cnc() {
		parent::__construct();
	}

	// Used to check for post data with login stuff
	function _referred($p = 'user') {
		if (!isset($_POST[$p])) {
			header('Location: /');
			return false;
		}
		return true;
	}

	function _validateUser() {
		$pos = $this->user_model->getPos();
		if ($pos['boss'] === false) {
			$this->session->set_flashdata('message', 'You don\'t have priviliges to edit user privileges.');
			$this->session->set_flashdata('referrer', 'cnc/acctmanage');
			header('Location: /cnc/login');
			return false;
		}
		return true;
	}

	// 'header' and 'footer' views wrap around the 'main' div of the page
	// anything loaded in between them is subject to removal on ajaxLoadMain calls

	function index() {
		$data['title'] = 'Home';
		$data['bodyOnload'] = '';
		$this->load->view('header', $data);
		$this->load->view('main/home');
		$this->load->view('footer');
	}

	function login() {
		$data['title'] = 'Login';
		$data['bodyOnload'] = "document.getElementById('user').focus();";
		$this->load->view('header', $data);
		$this->load->view('main/login');
		$this->load->view('footer');
	}

	function account() {
		$data['title'] = 'Account Creation';
		$data['bodyOnload'] = "document.getElementById('user').focus();";
		$this->load->view('header', $data);
		$this->load->view('main/login');
		$this->load->view('footer');
	}

	function addaccount() {
		if ($this->_referred())
			$result = $this->user_model->addaccount();
	}
	function updateaccount() {
		if ($this->_referred())
			$result = $this->user_model->updateaccount();
	}
	function acctmanagesave() {
		if ($this->_referred($this->session->userdata('user').'b') && $this->_validateUser()) {
			$data['filldata'] = $this->user_model->acctmanagesave();
			$data['text'] = true;
			$this->load->view('suggestfill', $data);
		}
	}

	function manage() {
		$data['title'] = 'Manage Account';
		$data['bodyOnload'] = "document.getElementById('user').focus();";
		$this->load->view('header', $data);
		$this->load->view('main/login');
		$this->load->view('footer');
	}

	function acctmanage() {
		if ($this->_validateUser()) {
			$data['title'] = 'Manage User Privileges';
			$data['bodyOnload'] = "";
			$this->load->view('header', $data);
			$data1['users'] = $this->user_model->userList();
			$this->load->view('main/acctmanage', $data1);
			$this->load->view('footer');
		}
	}

	function logout() {
		// $userdata = array('user' => '', 'password' => '', 'name' => '', 'position' => '', 'id' => '', 'locked' => '');
		// $this->session->unset_userdata($userdata);
		$this->session->sess_destroy();
		header('Location: /');
	}

	function userlogin($check = false) {
		if ($this->_referred()) {
			if ($check) { // Just check to see if the credentials are OK
				$data['filldata'] = $this->user_model->chkcreds();
				$this->load->view('suggestfill', $data);
			} else {
				$data['filldata'] = $this->user_model->login();
				$this->load->view('suggestfill', $data);
			}
		}
	}

	function nojs() {
		$data['title'] = 'Enable JavaScript';
		$data['bodyOnload'] = '';
		$this->load->view('header', $data);
		$this->load->view('main/nojs');
		$this->load->view('footer');
	}

	// AJAX Calls for this data, needs no header
	function home() {
		$this->load->view('main/home');
	}
	// End AJAX-called views

	function getPHPinfo() {
		phpinfo();
	}

	// the next four functions parse different quickbooks data.  The first two parse iif files, the second two parse xml files.
	function _itemtype_list_parser() {
		$this->load->model ('parser_model', 'nothing');
		$this->nothing->file_itemtypes();
	}
	function _customer_list_parser() {
		$this->load->model ('parser_model', 'nothing');
		$this->nothing->file_customers();
	}
	function _customer_xml() {
		$this->load->model ('parser_model', 'nothing');
		$this->nothing->file_xml();
	}
	function _databaseupdate(){
		$this->load->model ('parser_model', 'nothing');
		$this->nothing->updatedb('customers');
	}
	function _databasecleanup() {
		$this->load->model ('databasecleanup');
		$this->databasecleanup->cleanup();
	}
	// this function returns a string in order of the most popular itemtypes
	function popularitems() {
		$this->load->model('databasecleanup');
		$items = $this->databasecleanup->popularitems();
// 		log_message('error', print_r($items, true));
		print_r($items, false);
	}

	/*
	 * This function removes zz prefixed customers from the database
	 *
	 */
	function fullname() {
		$this->load->model('databasecleanup');
		$this->databasecleanup->removezz();
	}

}