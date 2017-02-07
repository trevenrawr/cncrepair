<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
	class Parser_model extends CI_Model {

	function Parser_model() {
		parent::__construct();
	}

	// function updates table with new data from table2
	function updatedb ($table = customers) {

		// pull table columns
		if ($query = $this->db->query('show columns from '.$table)) {
			$column_names = array();
			$result = $query->result_array();

			foreach($result as $field) {
				$column_names[] = $field['Field'];
			}
			// unset id column to perserve informations unique id
			unset($column_names[0]);
			$test = implode(', ',$column_names);
			// log_message('error', print_r($column_names, TRUE));

			$update = array();
			// generate update syntax for the mysql query
			foreach ($column_names as $k)
				$update[$k] = $k.'='.$table.'2.'.$k.'';
			unset($k); unset($v);

			// Generate query to INSERT if doesn't exist, or to UPDATE if does.
			$query = 'INSERT INTO '.$table.' ('.$test.') '.
				'SELECT '.$test.' '.
				'FROM '.$table.'2 '.
				'ON DUPLICATE KEY UPDATE '.implode(', ', array_values($update));
			$this->db->query($query);

		// log_message('error', print_r($update, TRUE ));
		echo 'success';
		}
	}

	// function converts xml data structure to an associative array then adds data to customers2 table
	function file_xml($url='simple.xml', $get_attributes = 1, $priority = 'tag') {
		$contents = "";

		$parser = xml_parser_create('');

		if (!($fp = @ fopen($url, 'rb'))) {
			return array ();
		}
		while (!feof($fp)) {
			$contents .= fread($fp, 8192);
		}
		fclose($fp);

		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, trim($contents), $xml_values);
		xml_parser_free($parser);

		if (!$xml_values)
			return; //Hmm...
		$xml_array = array ();
		$parents = array ();
		$opened_tags = array ();
		$arr = array ();
		$current = & $xml_array;
		$repeated_tag_index = array ();

		foreach ($xml_values as $data) {

			unset ($attributes, $value);
			extract($data);
			$result = array ();
			$attributes_data = array ();

			if (isset ($value)) {
				if ($priority == 'tag')
					$result = $value;
				else
					$result['value'] = $value;
			}
			if (isset ($attributes) and $get_attributes) {

				foreach ($attributes as $attr => $val) {
					if ($priority == 'tag')
						$attributes_data[$attr] = $val;
					else
						$result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
				}
			}

			if ($type == "open") {
				$parent[$level -1] = & $current;

				if (!is_array($current) or (!in_array($tag, array_keys($current)))) {
					$current[$tag] = $result;
					if ($attributes_data)
					$current[$tag . '_attr'] = $attributes_data;
					$repeated_tag_index[$tag . '_' . $level] = 1;
					$current = & $current[$tag];
				}
				else {
					if (isset ($current[$tag][0])) {
						$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
						$repeated_tag_index[$tag . '_' . $level]++;
					}
					else {
						$current[$tag] = array ($current[$tag], $result);
						$repeated_tag_index[$tag . '_' . $level] = 2;
						if (isset ($current[$tag . '_attr'])) {
							$current[$tag]['0_attr'] = $current[$tag . '_attr'];
							unset ($current[$tag . '_attr']);
						}
					}

					$last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
					$current = & $current[$tag][$last_item_index];
				}
			}
			elseif ($type == "complete") {

				if (!isset ($current[$tag])) {
					$current[$tag] = $result;
					$repeated_tag_index[$tag . '_' . $level] = 1;
					if ($priority == 'tag' and $attributes_data)
						$current[$tag . '_attr'] = $attributes_data;
				}
				else {
					if (isset ($current[$tag][0]) and is_array($current[$tag])) {
						$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
						if ($priority == 'tag' and $get_attributes and $attributes_data) {
							$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
						}
					$repeated_tag_index[$tag . '_' . $level]++;
					}
					else {
						$current[$tag] = array ($current[$tag],	$result);
						$repeated_tag_index[$tag . '_' . $level] = 1;
						if ($priority == 'tag' and $get_attributes) {
							if (isset ($current[$tag . '_attr'])) {
								$current[$tag]['0_attr'] = $current[$tag . '_attr'];
								unset ($current[$tag . '_attr']);
							}
							if ($attributes_data) {
								$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
							}
						}
						$repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
					}
				}
			}
			elseif ($type == 'close')
				$current = & $parent[$level -1];
		}

		// delete current table information
		$this->db->query('DELETE FROM customers2');
		$termslist = Array ( 'CREDIT CARD' => 'credit',
				'NET 30' => 'net30',
				'wire' => 'wire',
				'NET 20' => 'net30',
				'NET 15' => 'net30',
				'COD' => 'cod');
		// parse associative array by customer and add information to database
		foreach ($xml_array['QBXML']['QBXMLMsgsRs']['CustomerQueryRs']['CustomerRet'] as &$value) {

			$qbrefnum = (isset ($value['ListID'])) ? $this->db->escape_str($value['ListID']) : '';
			$qbeditsequence = (isset ($value['EditSequence'])) ? $this->db->escape_str($value['EditSequence']) : '';
			$name = $shipname = (isset ($value['Name'])) ? $this->db->escape_str($value['Name']) : '';
			$address = (isset ($value['BillAddress']['Addr1'])) ? $this->db->escape_str($value['BillAddress']['Addr1']) : '';
			$address1 = (isset ($value['BillAddress']['Addr2'])) ? $this->db->escape_str($value['BillAddress']['Addr2']) : '';
			$address1 .= (isset($value['BillAddress']['Addr3'])) ? $this->db->escape_str($value['BillAddress']['Addr3']) : '';
			$city = (isset ($value['BillAddress']['City']) ) ? $this->db->escape_str($value['BillAddress']['City']) : '';
			$state = (isset ($value['BillAddress']['Province'])) ? $this->db->escape_str($value['BillAddress']['Province']) : '';
			$zip = (isset ($value['BillAddress']['PostalCode'])) ? $this->db->escape_str($value['BillAddress']['PostalCode']) : '';
			// $country = (isset ($value // not stored in quickbooks
			$index = (isset ($value['TermsRef']['FullName'])) ? $value['TermsRef']['FullName']: '';
			$terms = (isset ($termslist[$index])) ? $this->db->escape_str($termslist[$index]) : '';
			if ( isset ($value['TaxCodeRef']['FullName']) ) {
				switch ($value['TaxCodeRef']['FullName']) {
					case '.':
						$tax = '';
						break;
					case 'h':
						$tax = 'HST';
						break;
					case 'g':
						$tax = 'GST';
						break;
					case 's':
						$tax = 'GSTPST';
						break;
					default:
						$tax = '';
				}
			}
			$shipaddress = (isset ($value['ShipAddress']['Addr1'])) ? $this->db->escape_str($value['ShipAddress']['Addr1']) : '';
			$shipaddress1 = (isset ($value['ShipAddress']['Addr2']))? $this->db->escape_str($value['ShipAddress']['Addr2']) : '';
			$shipaddress1 .= (isset ($value['ShipAddress']['Addr3'])) ? $this->db->escape_str($value['ShipAddress']['Addr3']) : '';
			$shipcity = (isset ($value['ShipAddress']['City'])) ? $this->db->escape_str($value['ShipAddress']['City']) : '';
			$shipstate = (isset ($value['ShipAddress']['Province'])) ? $this->db->escape_str($value['ShipAddress']['Province']) : '';
			$shipzip = (isset ($value['ShipAddress']['PostalCode'])) ? $this->db->escape_str($value['ShipAddress']['PostalCode']) : '';
			//$shipcoutry = (isset ($value // not stored in quickbooks
			$balance  = (isset ($value['TotalBalance'])) ? $this->db->escape_str($value['TotalBalance']) : '';
			//$carrier = (isset ($value // not stored in quickbooks
			$currency = (isset($value['CurrencyRef']['FullName'])) ? $this->db->escape_str($value['CurrencyRef']['FullName']) : '';
			// credit limit?
			$email = (isset($value['Email'])) ? $this->db->escape_str($value['Email']) : '';
			$fax = (isset($value['Fax'])) ? $this->db->escape_str($value['Fax']) : '';
			$num = (isset($value['Phone'])) ? $this->db->escape_str($value['Phone']) : '';
			$contact = (isset($value['Contact'])) ? $this->db->escape_str($value['Contact']) : '';

			// update sql
			$this->db->query('INSERT INTO customers2 (balance, qbeditsequence, terms, currency, shipzip, shipstate, shipcity, shipaddress, shipaddress1, shipname, zip, address, address1, state, city, name, qbrefnum)
					VALUES ("'.$balance.'", "'.$qbeditsequence.'", "'.$terms.'", "'.$currency.'", "'.$shipzip.'", "'.$shipstate.'", "'.$shipcity.'", "'.$shipaddress.'", "'.$shipaddress1.'",
						"'.$shipname.'", "'.$zip.'", "'.$address.'", "'.$address1.'", "'.$state.'", "'.$city.'", "'.$name.'","'.$qbrefnum.'")');
			//$id_r = $this->db->query('SELECT id	 FROM customers WHERE name = "'.$name.'"');
			//$id = $id_r->result_array();

			//	 log_message('error', print_r($id, true));
			//	 log_message('error', print_r($name, true));
			//$this->db->query(' INSERT INTO phones (num, cust_id, type, contact) VALUES ("'.$num.'", "'.$id[0]["id"].'", "primary", "'.$contact.'")');
			//$this->db->query(' INSERT INTO phones (num, cust_id, type) VALUES ("'.$fax.'", "'.$id[0]["id"].'", "fax")');
			//$this->db->query(' INSERT INTO emails (email, cust_id) VALUES ("'.$email.'","'.$id[0]['id'].'")');
		}
		unset ($xml_array);
		echo 'success';
	}

	// this function parses the quickbook item iif file
	function file_itemtypes () {
		// delete existing table entries

		$this->db->query('DELETE FROM itemtypes');

		// parse quickbooks iif file line by line
		$quickbooks = file ( 'items.iif' );
		foreach ( $quickbooks as $line ) {
			$tempLine = explode ( "\t" , $line );
			$tempName = explode ( ':', $tempLine[1], 2 );

			// if statement checks whether the first information is acutally an item entry or category
			if ( isset ($tempName[1]) )
			{
				// cut out quotes from the line
				$pattern = array('/\,/', '/\"/', '/\'/','/\\n/', '/<.*>/');
				$tempLine = preg_replace ( $pattern, '', $tempLine);

				// assign filed information to the appropiate variables
				$modelnum = $this->db->escape_str($tempName[1]);
				$make = $this->db->escape_str($tempName[0]);
				$repairrate = $tempLine[12];

				// seperate description and detail by '[' and ']' delemeters
				$description = explode( '[', $tempLine[5], 2);
				$detail = '';

				if (isset ($description[1]) ) {
					$detail = explode ( ']', $description[1], 2);
					if ( isset ($detail[1]) ) {
						$description = $this->db->escape_str($description[0].$detail[1]);
						$detail = $this->db->escape_str($detail[0]);
					}
					else {
						$description = $this->db->escape_str($description[0]);
						$detail = $this->db->escape_str($detail[0]);
					}
				}
				else
					$description = $this->db->escape_str($description[0]);


				// if item is exchange or sale then update db
				$pattern = array ('/\ Sale/', '/\ EXCH/');
				$modelnum_temp = preg_replace ( $pattern, '', $modelnum);

				$condition = $this->db->query('select id from itemtypes where modelnum = "'.$modelnum_temp.'"');
				$condition = $condition->row_array();
				// log_message('error', print_r($condition, true));

				if ( array_key_exists ( 'id', $condition) ) {
					if ( strpos ( $tempName[1], "Sale") ) {
						$modelnum = preg_replace ( '/\ Sale/', '', $modelnum);
						$this->db->query('
										Update itemtypes
										Set sale = "1",
										salerate = "'.$repairrate.'"
										Where modelnum = "'.$modelnum.'"');
					}
					elseif ( strpos ( $tempName[1], "EXCH" ) ) {
						$modelnum = preg_replace ( '/\ EXCH/', '', $modelnum);
						// log_message('error', print_r($repairrate, true));
						$this->db->query('
										Update itemtypes
										Set exch = "1",
										exchrate = "'.$repairrate.'"
										Where modelnum = "'.$modelnum.'"');
					}
				}
				else { // ADD NEW INFORMATION TO DATABASE
					if ( strpos ($modelnum, "Sale") ) {
						$this->db->query('
										insert into itemtypes
										(modelnum, make, description, details, salerate, sale)
										values
										("'.$modelnum_temp.'","'.$make.'","'.$description.'","'.$detail.'","'.$repairrate.'", "1")');
					}
					elseif ( strpos ($modelnum, "EXCH") ) {
						$this->db->query('
										insert into itemtypes
										(modelnum, make, description, details, exchrate, exch)
										values
										("'.$modelnum_temp.'","'.$make.'","'.$description.'","'.$detail.'","'.$repairrate.'", "1")');
					}
					else {
					$this->db->query('
									INSERT INTO itemtypes
									(modelnum, make, description, details, repairrate, repair)
									Values
									("'.$modelnum_temp.'","'.$make.'","'.$description.'","'.$detail.'","'.$repairrate.'","1")');
					}
				}
			}
		}
	echo "You have finished.  Go eat a pluptosaurus";
	}

	// this function parses the customer iif file
	function file_customers () {

		// empty database tables
		// $this->db->query('DELETE FROM customers');
		// $this->db->query('DELETE FROM emails');
		// $this->db->query('DELETE FROM phones');

		// parse quickbooks iif file line by line
		$quickbooks = file ( 'customers.IIF' );
		foreach ( $quickbooks as $line ) {
			$tempLine = explode ( "\t" , $line );

			// remove quotes from each line
			$pattern = array('/\"/');
			$tempLine = preg_replace ( $pattern, '', $tempLine);

			// import data to appropiate fields for the table
			$name = $this->db->escape_str($tempLine[1]);
			$ship_city_state = explode( ',', $tempLine[11], '2');
			$city_state = explode( ',', $tempLine[6], '2');
			$city_state = $this->db->escape_str($city_state);
			$address = $this->db->escape_str($tempLine[5]);
			$zip = $this->db->escape_str($tempLine[7]);
			$shipname = $this->db->escape_str($tempLine[9]);
			$shipaddress = $this->db->escape_str($tempLine[10]);
			$ship_city_state = $this->db->escape_str($ship_city_state);
			$shipzip = $this->db->escape_str($tempLine[12]);
			$terms = $this->db->escape_str($tempLine[22]);
			$currency = $this->db->escape_str($tempLine[50]);
			//log_message('error', print_r($currency, true));
			// determine currency
			// if ( stripos ( $currency, "i" ) )
				// $currency = 'United States Dollar';
			// else
				// $currency = 'Canada Dollar';
			//log_message('error', print_r($currency, true));
			$num = $this->db->escape_str($tempLine[35]);
			$fax = $this->db->escape_str($tempLine[36]);
			$email = $this->db->escape_str($tempLine[38]);
			$refnum = $this->db->escape_str($tempLine[2]);

			// overwrite data for incorrect field positions relating to address data residing in field 7 instead of 6
			$condition_column_seven = explode ( ',', $tempLine[7], 2);
			$condition_column_twelve = explode ( ',', $tempLine[12], 2);
			if ( isset( $condition_column_seven[1])) {
				$address = $this->db->escape_str($tempLine[5] + '\ ' + $tempLine[6]);
				$zip = $this->db->escape_str($tempLine[8]);
				$city_state = $this->db->escape_str($condition_column_seven);
				//log_message('error', print_r($city_state, true));
			}
			if ( isset ($condition_column_twelve[1])) {
				$ship_city_state = $this->db->escape_str($condition_column_twelve);
				$shipzip = $this->db->escape_str($tempLine[13]);
				$shipaddress =$this->db->escape_str($tempLine[10] + '\ ' + $tempLine[11]);
			}

			//  Insert data into database tables
			$this->db->query('INSERT INTO customers (terms, currency, shipzip, shipstate, shipcity, shipaddress, shipname, zip, address, state, city, name, qbrefnum)
							VALUES ("'.$terms.'", "'.$currency.'", "'.$shipzip.'", "'.$ship_city_state[1].'", "'.$ship_city_state[0].'", "'.$shipaddress.'","'.
							$shipname.'", "'.$zip.'", "'.$address.'", "'.$city_state[1].'", "'.$city_state[0].'", "'.$name.'","'.$refnum.'")');
			$id_r = $this->db->query('SELECT id FROM customers WHERE name = "'.$name.'"');
			$id = $id_r->result_array();
			 // log_message('error', print_r($id, true));
			 // log_message('error', print_r($name, true));
			$this->db->query(' INSERT INTO phones (num, cust_id, type) VALUES ("'.$num.'", "'.$id[0]["id"].'", "primary")');
			$this->db->query(' INSERT INTO phones (num, cust_id, type) VALUES ("'.$fax.'", "'.$id[0]["id"].'", "fax")');
			$this->db->query(' INSERT INTO emails (email, cust_id) VALUES ("'.$email.'","'.$id[0]['id'].'")');
		}
		// $this->db->query("INSERT INTO customers VALUES (0, 'CNC Repair', '1770 Front St. #142', '', 'Lynden', 'WA', '98264', 'United States', '', '', '0', 0.00, 'CNC Repair', '1770 Front St. #142', '', 'Lynden', 'WA', '98624', 'United States', '', '')");
		// $this->db->query("UPDATE customers SET id=0 WHERE name='CNC Repair'");
	}
}