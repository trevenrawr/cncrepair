<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
	class Databasecleanup extends CI_Model {

	function Databasecleanup() {
		parent::__construct();
	}

	function cleanup() {
		// Go line by line through the item list and rip out '\n'
		// tags?

		$query = 'SELECT id, description from `itemtypes` where 1';
		$data = $this->db->query ( $query );
		$data = $data->result_array();

		$final = 0;
		foreach($data as &$value) {

			$value['description'] = preg_replace ( '#\Q\n\E#', '', $value['description'], -1, $count);
 			$final += $count;

		} unset ($value); unset ($descriptions);
		$infostr = $this->db->affected_rows();
		// log_message('error', print_r($final, TRUE));
		// log_message('error', print_r($data, TRUE));
		// log_message('error', print_r($infostr, TRUE));

		$infostr = 0;
		foreach($data as &$line) {

			$query = 'UPDATE `itemtypes` SET DESCRIPTION="'.$this->db->escape_str($line['description']).'" WHERE id = "'.$line['id'].'" AND description <> "'.$this->db->escape_str($line['description']).'"  ' ;
			$this->db->query ($query);
			$infostr = $infostr + $this->db->affected_rows();
		}

		log_message('error', print_r($infostr, TRUE));
	}

	// fullname tag remover for the quickbooks customers in que.  INCOMPLETE
// 	function fullname() {
// 		$dsn = 'mysql://cncrepair:sausage13@localhost/quickbooks';
// 		$quickbooks = $this->load->database($dsn, TRUE);
//
// 		$result = $quickbooks->query("SELECT * FROM `quickbooks_queue` WHERE qb_action like 'Customer%'");
// 		$array = $result->result_array;
// 		log_message('error', print_r($array, TRUE));
// 	}
//
	function removezz() {

		$info = $this->db->query ('SELECT id FROM `itemtypes` WHERE make = "ZZ"');
		$result = $info->result_array();
		$string = '';
		$count = 0;
		foreach ($result as $key => &$value) {
			if($count == 0) {
				$string = $value['id'];
				$count = 12;
			} else {
				$string .= ' OR itemtype_id = '.$value['id'];
			}
		}
// 		$query = $this->db->query('SELECT quote_id from quoteitems WHERE itemtype_id = '.$string);
// 		$result = $query->result_array;

		log_message('error', print_r($string, TRUE));

// 		$quote = $this->db->query('select quote_id from quoteitems where ');

	}
	function popularitems() {

		$query = 'SELECT itemtype_id FROM quoteitems WHERE print = "regular"';
		$info = $this->db->query($query);
// 		$item = array_count_values($info->result_array());\
		$info = $info->result_array();
		for ($i = 0; $i < count($info); $i++) {
			$item[] = $info[$i]['itemtype_id'];
		}

		$item = array_count_values($item);


		arsort( $item, SORT_NUMERIC);
		$amount = $item;
// 		$item = $blah;
		$quoteitems = "";
// 		$previous = -2;
// 		log_message('error', print_r($item, true ));
		foreach( $item as $items => $value ) {

			$data = $this->db->query('select modelnum, make from itemtypes where id = '.$items);
			$data = $data->result_array();
// 			log_message('error', print_r ($data, true));

			// filter items
			$findme = 'OFFICE USE';

			if ( !(strrpos($data[0]['make'], $findme) === false) ) {
				continue;
			}


			$quoteitems .= $data[0]['make'].' '.$data[0]['modelnum'].', ';


		}

// 		log_message('error', print_r($quoteitems, true));
		$map = Array ( 'amount' => $amount,
				'items' => $quoteitems);

		return $map;
	}



}