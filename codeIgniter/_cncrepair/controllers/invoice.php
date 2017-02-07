<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Invoice extends CI_Controller {
	function Invoice() {
		parent::__construct();
		$this->load->model('Invoice_model', 'invmodel');
	}

	function _validateUser() {
		$pos = $this->user_model->getPos();
		if ($pos['office'] === false) {
			$this->session->set_flashdata('message', 'You don\'t have priviliges to edit invoices.');
			$this->session->set_flashdata('referrer', 'invoice');
			header('Location: /cnc/login');
			return false;
		}
		return true;
	}

	// This function makes sure that there is post data where expected to keep from accidental submission issues.
	function _referred($k) {
		if (!isset($_POST[$k])) {
			header('Location: /invoice');
			return false;
		}
		return true;
	}

	function index($iid = 0) {
		if ($this->_validateUser()) {
			$data['title'] = 'Invoice View';
			$data['bodyOnload'] = "docReady('inv');";
			if ($iid > 0) $data['bodyOnload'] .= "getQuote(".$iid.");";
			$this->load->view('header', $data);

			$this->load->library('user_agent');
			$tarows = 2;
			if ($this->agent->browser() == "Firefox")
				$tarows = $tarows - 1;
			$data1['tarows'] = $tarows;
			$data1['inv_id'] = ($iid > 0) ? $iid : $this->invmodel->getID();
			$data1['qORi'] = 'invoice';
			$this->load->view('quote/create', $data1);
			$this->load->view('footer');
		}
	}

	// Creates invoice/quote pdf.
	function printPDF($inv_id, $view) {
		$data['tarows'] = 2;
		$data['view'] = $view;
		$data['inv_id'] = $inv_id;
		$data['info'] = $this->invmodel->getInvoice($inv_id);
		if ($view == 'customsinvoice') $view = 'inv';
		$message = $this->invmodel->getMessage($view);
		$data['message'] = $message['message'];
		$data['hours'] = $this->invmodel->getMessage('hours');
		$data['hours'] = $data['hours']['message'];
		$this->load->helper('to_pdf');
		$data['html'] = "set";
// 		log_message('error', print_r($data, true));
		$html = $this->load->view('quote/print', $data, true);
		pdf_create($html, $view.$data['info']['inv']['id']);
	}

	function create() {
		if ($this->_referred('qid') && $this->_validateUser()) {
			$qid = $this->input->post('qid');
			$data['filldata'] = $this->invmodel->create($qid);
			$this->load->view('suggestfill', $data);
		}
	}

	// Actually only saves, as invoices must be "created" from quotes, but "add" fits naming scheme
	function add() {
		if ($this->_referred('inv_id') && $this->_validateUser()) {
			$data['filldata'] = $this->invmodel->save();
			$data['text'] = true;
			$this->load->view('suggestfill', $data);
		}
	}

	function delete() {
		if ($this->_referred('id') && $this->_validateUser()) {
			$id = $this->input->post('id');
			$data['filldata'] = $this->invmodel->delete($id);
			$data['text'] = true;
			$this->load->view('suggestfill', $data);
		}
	}

	// THis function retrives invoices for index tool
	function get() {
		if ($this->_referred('id') && $this->_validateUser()) {
			$inv_id = $this->input->post('id');
			$data['filldata'] = $this->invmodel->getInvoice($inv_id);
// 			log_message('error', print_r($data['filldata'], true));
			$this->load->view('suggestfill', $data);
		}
	}

	// Emails invoice as a pdf.
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
			$data1['qORi'] = 'invoice';
			$data1['files'] = $filelist;
			$this->load->view("/quote/attachments", $data1);
		}
	}

	function sendPDF($inv_id, $view) {
		$data['tarows'] = 2;
		$data['view'] = $view;
		$data['inv_id'] = $inv_id;
		$data['info'] = $this->invmodel->getInvoice($inv_id);
		$email['id'] = $data['info']['inv']['email_id'];
		// find email for quote
		foreach ($data['info']['customer']['emails'] as $line) {
			if	($line['id'] == $email['id']) {
				$email['address'] = $line['email'];
				$email['name'] = $line['name'];
			}
		}
		unset($line);
		// grab database messages
		$data['row'] = $this->invmodel->getMessage($view);
		$data['message'] = $data['row']['message'];
		$data['hours'] = $this->invmodel->getMessage('hours');
		$data['hours'] = $data['hours']['message'];
		$email['text'] = $this->invmodel->getMessage('text_email');
		$email['html'] = $this->invmodel->getMessage('html_email');

		$this->load->helper('to_pdf');
		$html = $this->load->view('quote/print', $data, true);
		pdf_create($html, $data['row']['name'].'-'.$data['inv_id'], false);
		$this->load->model('Email_model', 'emailmodel');
		$debug = $this->emailmodel->email($data, $email, $view);
		echo $debug;
	}

	function queueAddInvoice($inv_id) {
		$this->invmodel->queueAddInvoice($inv_id);
	}
}
?>