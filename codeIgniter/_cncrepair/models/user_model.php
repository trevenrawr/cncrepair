<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class User_model extends CI_Model {

	var $user = '';
	var $password = '';
	var $position = '';

	function User_model() {
		parent::__construct();
	}

	function login() {
		$creds = $this->chkcreds();
		if ($creds !== false && $creds['locked'] != 1) {
			unset($creds['password']);
			$this->session->set_userdata($creds);
		}
		return $creds;
	}

	function chkcreds() {
		$user = $this->input->post('user');
		$password = $this->input->post('password');
		$query = $this->db->query('SELECT * FROM users WHERE user="' . $user . '" AND password="' . $password . '"');
		$r = $query->num_rows();
		$userdata = $query->row_array();
		if ($r == 1) {
			return $userdata;
		} else {
			return false;
		}
	}

	function updateaccount() {
		// Make $_POST into a MySQL escaped string
		foreach ($_POST as &$value) {
			$value = $this->db->escape_str($value);
		} unset($value);

		$user = $this->input->post('user');
		$oldpass = $this->input->post('oldpassword');
		// d41d8cd98f00b204e9800998ecf8427e is the MD5 hash for an empty string
		$pass = ($this->input->post('password') != 'd41d8cd98f00b204e9800998ecf8427e') ? $this->input->post('password') : $oldpass;
		$name = $this->input->post('name');

		$this->db->query('UPDATE users SET password="'.$pass.'", name="'.$name.'" WHERE user="'.$user.'" AND password="'.$oldpass.'"');
		if ($this->db->affected_rows() == 1) {
			$this->session->set_flashdata('message', 'Name and/or password updated successfully.');
			$this->session->set_userdata('name', $name);
		} else {
			$this->session->set_flashdata('message', 'Incorrect current password (or nothing changed).');
			$this->session->set_flashdata('name', $name);
		}
	}

	function acctmanagesave() {
		foreach ($_POST as $k => $v) {
			$pos[substr($k, 0, -1)][substr($k, -1)] = ($v == "true") ? 1 : 0;
		}

		foreach ($pos as &$user) {
			$privs = '';
			foreach ($user as $priv => $yes) {
				if ($priv != 'l') {
					if ($yes == 1) $privs .= $priv;
				}
			}
			$user['position'] = $privs;
			unset($user);
		}

		$this->db->trans_start();
		foreach ($pos as $user => $userdata) {
			$this->db->query('UPDATE users SET position="'.$userdata['position'].'", locked='.$userdata['l'].' WHERE user="'.$user.'"');
		}
		$this->db->trans_complete();

		$status = "ok";
		if ($this->db->trans_status() === false)
			$status = "not ok";
		return $status;
	}

	function addaccount() {
		foreach ($_POST as &$value) {
			$value = $this->db->escape_str($value);
		} unset($value);

		$user = $this->input->post('user');
		$password = $this->input->post('password');
		$name = $this->input->post('name');

		$this->db->query('INSERT INTO users (user, password, name) VALUES ("'.$user.'", "'.$password. '", "'.$name.'") ON DUPLICATE KEY UPDATE user="'.$user.'"');
		if ($this->db->affected_rows() == 1) {
			$this->session->set_flashdata('message', 'Your account was created successfully, but you will have to wait for an administrator to assign you privileges before you can do anything.');
		} else {
			$this->session->set_flashdata('message', 'The username you chose already exists.');
			$this->session->set_flashdata('name', $name);
		}
	}

	function getPos($p = '0') {
		/*	Possible positions:
		 *	b (boss)			-> manage items, inventory checks, change privs on users
		 *	o (office)		-> customers, quotes, invoices, q/i history
		 *	i (receiving)	-> recieving, unpacking, shipping
		 *	c (cleaning)	-> cleaning
		 *	r (repair)		-> repair, testing
		 * a (assembling)	-> assembling
		 */

		$udata = $this->session->userdata;
		// log_message('error', $this->uri->uri_string());
		// log_message('error', $this->uri->uri_string()."\n".print_r($udata, true));

		if ($p == '0')
			$p = $this->session->userdata('position');

		// These privileges are split up on purpose.  Just change the particular letters HERE to change the privilege layout.  Nowhere else needs changing.
		$pos = array (
			'boss' => (strpos($p, 'b') === false) ? false : true,
			'office' => (strpos($p, 'o') === false) ? false : true,
			'unpacking' => (strpos($p, 'i') === false) ? false : true,
			'receiving' => (strpos($p, 'i') === false) ? false : true,
			'cleaning' => (strpos($p, 'c') === false) ? false : true,
			'repair' => (strpos($p, 'r') === false) ? false : true,
			'assembling' => (strpos($p, 'a') === false) ? false : true,
			'testing' => (strpos($p, 'r') === false) ? false : true,
			'shipping' => (strpos($p, 'i') === false) ? false : true
		);
		return $pos;
	}

	function userList() {
		$query = $this->db->query('SELECT * FROM users');
		return $query->result_array();
	}
}