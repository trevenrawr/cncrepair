<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Quote extends CI_Controller {
	function Quote() {
		parent::__construct();
		$this->load->model('Quote_model', 'quotemodel');
	}

	function _validateUser($page = '') {
		$pos = $this->user_model->getPos();
		if ($pos['office'] === false) {
			$this->session->set_flashdata('message', 'You don\'t have priviliges to create quotes.');
			$this->session->set_flashdata('referrer', 'quote/'.$page);
			header('Location: /cnc/login');
			return false;
		}
		return true;
	}

	// This function makes sure that there is post data where expected to keep from accidental submission issues.
	function _referred($k) {
		if (!isset($_POST[$k])) {
			header('Location: /quote');
			return false;
		}
		return true;
	}

	function index($qid = 0) {
		if ($this->_validateUser('index/'.(($qid == 0) ? '' : $qid))) {
			$data['title'] = 'Build Quote';
			$data['bodyOnload'] = "docReady('quote');";
			if ($qid > 0) $data['bodyOnload'] = "docReady('quote'); getQuote(".$qid.");";
			$this->load->view('header', $data);

			$this->load->library('user_agent');
			$tarows = 2;
			if ($this->agent->browser() == "Firefox")
				$tarows = $tarows - 1;
			$data1['tarows'] = $tarows;
			$data1['quote_id'] = ($qid > 0) ? $qid : $this->quotemodel->getID();
			$data1['qORi'] = 'quote';
			$this->load->view('quote/create', $data1);
			$this->load->view('footer');
		}
	}

	function search() {
		if ($this->_validateUser('search')) {
			$data['title'] = "Customer History";
			$data['bodyOnload'] = 'onLoad();';
			$this->load->view('header', $data);
			$data1['custHistory'] = $this->quotemodel->custHistory();
			$this->load->view('quote/search', $data1);
			$this->load->view('footer');
		}
	}

	function custHistory() {
		if ($this->_referred('id') && $this->_validateUser('search')) {
			$data['custHistory'] = $this->quotemodel->custHistory();
			$this->load->view('quote/history', $data);
		}
	}

	function messages() {
		if ($this->_validateUser('messages')) {
			$data['title'] = "Edit Invoice Messages";
			$data['bodyOnload'] = '';
			$this->load->view('header', $data);
			$data1['messages'] = $this->quotemodel->getMessages();
			$this->load->view('quote/messages', $data1);
			$this->load->view('footer');
		}
	}

	function printPDF($quote_id, $view) {
		$data['tarows'] = 2;
		$data['view'] = $view;
		$data['inv_id'] = $quote_id;
		$data['info'] = $this->quotemodel->getQuote($quote_id);
		$message = $this->quotemodel->getMessage($view);
		$data['message'] = $message['message'];
		$data['hours'] = $this->quotemodel->getMessage('hours');
		$data['hours'] = $data['hours']['message'];
		// log_message('error', print_r($data, TRUE));
		$this->load->helper('to_pdf');
		$html = $this->load->view('quote/print', $data, true);
		pdf_create($html, 'Quote'.$data['info']['quote']['id']);
	}

	function savemessages() {
		if ($this->_validateUser('messages')) {
			$data['filldata'] = $this->quotemodel->saveMessages();
			$data['text'] = true;
			$this->load->view('suggestfill', $data);
		}
	}

	function assemblyItems() {
		if ($this->_referred('itemtype_id') && $this->_validateUser()) {
			$data['filldata'] = $this->quotemodel->assemblyItems();
			$this->load->view('suggestfill', $data);
		}
	}

	function add() {
		if ($this->_referred('quote_id') && $this->_validateUser()) {
			$data['filldata'] = $this->quotemodel->insert();
			$data['text'] = true;
			$this->load->view('suggestfill', $data);
// 			log_message('error', print_r($data, TRUE));
		}
	}

	function delete() {
		if ($this->_referred('id') && $this->_validateUser()) {
			$id = $this->input->post('id');
			$data['filldata'] = $this->quotemodel->delete($id);
			$data['text'] = true;
			$this->load->view('suggestfill', $data);
		}
	}

	function get() {
		if ($this->_referred('id') && $this->_validateUser()) {
			$quote_id = $this->input->post('id');
			$data['filldata'] = $this->quotemodel->getQuote($quote_id);
			$this->load->view('suggestfill', $data);


		}
	}

	// querie database for tax information.  used on to find customer tax.
	function getTax() {
		if ($this->_referred('province') && $this->_validateUser()) {
			$province = $this->input->post('province');
			$type = $this->input->post('type');
			// log_message('error', print_r($type, TRUE));
			switch ($type) {
				case 'GST':
					$temp = $this->db->query('SELECT * FROM salestax where (province = "Canada" AND name = "GST") ');
					$tax['filldata'] = $temp->row_array();
					$tax['filldata']['province'] = $province;
					break;
				case 'GSTPST':
					$temp = $this->db->query('SELECT * FROM salestax WHERE (province = "'.$province.'" AND name = "PST") '); // probably should change this to one query
					$temp1 = $this->db->query('SELECT tax FROM salestax where (province = "Canada" AND name = "GST") ');
					$tax1 = $temp1->row_array();
					$tax['filldata'] = $temp->row_array();
					$tax['filldata']['tax'] += $tax1['tax'];
					$tax['filldata']['name'] = 'GST + PST';
					break;
				default:
					$temp = $this->db->query('SELECT * FROM salestax WHERE (province = "'.$province.'" AND name = "'.$type.'") ');
					$tax['filldata'] = $temp->row_array();
			}
			$tax['filldata']['defined'] = (isset($tax['filldata']['tax'])) ? 'true' : 'false';
			// log_message('error', print_r ($tax, true) );
			$this->load->view('suggestfill', $tax);
		} else {log_message('error', print_r($_POST, TRUE) );}
	}

	// Controller funtion for Emailing pdf documents and quotes once the quote is created.
	function emailPDF($quote_id, $view) {
		if ($this->_validateUser('index/'.$quote_id)) {
			$dir = "docs/";
			$this->load->model('Email_model', 'mtree');
			$filelist = $this->mtree->mtree($dir);

			$data['title'] = 'Choose Attachments';
			$data['bodyOnload'] = '';
			$data['load']['nomenu'] = true;
			$data['load']['skinny'] = true;
			$this->load->view('header', $data);

			$data1['id'] = $quote_id;
			$data1['view'] = $view;
			$data1['qORi'] = 'quote';
			$data1['files'] = $filelist;
			$this->load->view("/quote/attachments", $data1);
		}
	}

	function sendPDF($quote_id, $view) {
		$data['tarows'] = 2;
		$data['view'] = $view;
		$data['inv_id'] = $quote_id;
		$data['info'] = $this->quotemodel->getQuote($quote_id); // grab quote info
		$email['id'] = $data['info']['quote']['email_id'];
		// find email for quote
		foreach ($data['info']['customer']['emails'] as $line) {
			if	($line['id'] == $email['id']) {
				$email['address'] = $line['email'];
				$email['name'] = $line['name'];
			}
		}
		unset($line);
		// grab database messages
		$data['row'] = $this->quotemodel->getMessage($view);
		$data['message'] = $data['row']['message'];
		$data['hours'] = $this->quotemodel->getMessage('hours');
		$data['hours'] = $data['hours']['message'];
		$email['text'] = $this->quotemodel->getMessage('text_email');
		$email['html'] = $this->quotemodel->getMessage('html_email');

		$this->load->helper('to_pdf');
		$html = $this->load->view('quote/print', $data, true);
// 		log_message('error', print_r($data, TRUE));
		pdf_create($html, $data['row']['name'].'-'.$data['inv_id'], false);
		$this->load->model('Email_model', 'emailmodel');
		$debug = $this->emailmodel->email($data, $email, $view);
		echo $debug;
	}

}
?>