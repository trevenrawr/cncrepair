<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Announce extends CI_Controller {

	function Announce() {
		parent::__construct();
	}
	
	function index() {
		$this->load->library('user_agent');
		log_message('error', 'announce request ip: '.$this->input->ip_address());
		log_message('error', 'announce request useragent: '.$this->agent->agent_string());
		echo 'What are you looking for?';
	}
	
}