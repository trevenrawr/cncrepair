<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Email_model extends CI_Model {

	function email_model () {
		parent::__construct();
		$this->load->library('email');
	}

	// This is used to read the attachments directory and create an array of the available files
	function mtree($dir) {
		$files = array ();
		$odir = opendir($dir);
		while (false !== ($file = readdir($odir))) {
			$folder[] = $file;
		}
		if (count($folder) > 0) {
			foreach ($folder as $line) {
				if ($line !== '.' && $line !== '..' && $line !== '.svn') {
					if (is_dir($dir.$line))
						$files[$line] = $this->mtree($dir.$line.'/');
					elseif (is_file($dir.$line) ) {
						$files[$line] = array( 'path' => $dir.$line, 'name' => $line );
					}
				}
			}
		}
	return $files;
	}

	// This... well... emails.
	function email ($data, $email, $view) {
		// Create subject header for the upcoming email
		$subject = '';
		for($index=0; $index < sizeof($data['info']['items']); $index++) {
			if ($data['info']['items'][$index]['print'] != 'subitem') {
				$subject.= $data['info']['items'][$index]['modelnum'];
				if ($index == sizeof($data['info']['items'])-1) {
					$subject.= '.';
					break;
				} elseif ($index == 2) {
					$subject.=', etc.';
					break;
				} else {
					$subject .=', ';
				}
			}
		}

		// codeigniter configuration array for the email library
		$config = Array(
				'protocol'  => 'smtp',
				'smtp_host' => 'ssl://mail.cncrepair.com',
				'smtp_port' => 465,
				'smtp_user' => 'no-reply@cncrepair.com',
				'smtp_pass' => '3165zap7491',
				'mailtype' => 'html',
				'charset'   => 'utf-8',
				'newline'   => "\r\n" );
		$this->load->library('email');
		$this->email->initialize($config);
		$this->email->from('no-reply@cncrepair.com', 'CNC REPAIR & SALES INC');
		$this->email->to($email['address']);
		$this->email->reply_to('office@cncrepair.com', 'Lindsay Thorley');
		$this->email->subject($data['row']['name'].' for '.$subject);
		// Important header/footer information for the HTML email
		$htmlLead =
		'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html><head><meta http-equiv="Content-type" content="text/html;charset=UTF-8" /></head><body>';
		$htmlTail =
		'</body></html>';
		$this->email->message($htmlLead.$email['html']['message'].$htmlTail);
		$this->email->set_alt_message($email['text']['message']);
		$this->email->attach($data['row']['name'].'-'.$data['inv_id'].'.pdf');
		if (isset($_POST['attachments'])) {
			foreach ($_POST['attachments'] as $attach) {
				$this->email->attach($attach);
			}
		}
		$this->email->send();
		unlink($data['row']['name'].'-'.$data['inv_id'].'.pdf');
		if (strpos($view, 'quote') !== false) { // Is a quote
			$table = 'quotes';
		} else { // Invoice
			$table = 'invoices';
		}
		$debug = $this->email->print_debugger();
		if (strpos($debug, 'successfully')) {
// 			log_message('error', print_r('testing condition', TRUE));
			$this->db->query('UPDATE '.$table.' SET sent=CURRENT_TIMESTAMP WHERE id='.$data['inv_id']);
		}
// 		$this->db->query('UPDATE '.$table.' SET sent=CURRENT_TIMESTAMP WHERE id='.$data['inv_id']);
		return $debug;

	}
}