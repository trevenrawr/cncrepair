<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Suggest extends CI_Controller {
	function Suggest() {
		parent::__construct();
		$this->load->model('Item_model', 'itemmodel');
		$this->load->model('Customer_model', 'custmodel');
	}

	function modelnum($rank = '') {
		$data['suggestions'] = $this->itemmodel->suggest('modelnum', 'itemtypes');
		$data['suggType'] = 'modelnum';
		$data['suggName'] = 'modelnum' . $rank;
// 		log_message('error', print_r ($rank));
		$this->load->view('suggestlist', $data);
	}

	function assembly() {
		$data['suggestions'] = $this->itemmodel->suggest('assembly', 'itemtypes');
		$data['suggType'] = 'modelnum';
		$data['suggName'] = 'assembly';
		$this->load->view('suggestlist', $data);
	}

	function make() {
		$data['suggestions'] = $this->itemmodel->suggest('make', 'itemtypes');
		$data['suggType'] = 'make';
		$this->load->view('suggestlist', $data);
	}

	function name() {
		$data['suggestions'] = $this->custmodel->suggest();
		$data['suggType'] = 'name';
		$this->load->view('suggestlist', $data);
	}
	function owner() {
		$data['suggestions'] = $this->custmodel->suggest();
		$data['suggType'] = 'name';
		$data['suggName'] = 'owner';
		$this->load->view('suggestlist', $data);
	}

	function serial() {
		$data['suggestions'] = $this->itemmodel->suggest('serial', 'specific_items');
		$data['suggType'] = 'serial';
		$this->load->view('suggestlist', $data);
	}

	function barcode($rank = '') {
		$data['suggestions'] = $this->itemmodel->suggest('barcode', 'specific_items');
		$data['suggType'] = 'barcode';
		$data['suggName'] = 'barcode'.$rank;
		$this->load->view('suggestlist', $data);
	}

	function filldata($type = '') {
		switch (preg_replace('/[0-9]/', '', $type)) {
			case 'modelnum':
			case 'assembly':
				$data['filldata'] = $this->itemmodel->filldata(preg_replace('/[^a-z]/i', '', $type), 'itemtypes');
				$rowNum = preg_replace('/[a-z]/i', '', $type);
				if ($rowNum != '') $data['filldata']['row'] = $rowNum;
				$this->load->view('suggestfill', $data);
				break;
			case 'name':
			case 'owner':
				$data['filldata'] = $this->custmodel->filldata();
				$this->load->view('suggestfill', $data);
				break;
			case 'serial':
			case 'barcode':
				$data['filldata'] = $this->itemmodel->filldata(preg_replace('/[^a-z]/i', '', $type), 'specific_items');
				$rowNum = preg_replace('/[a-z]/i', '', $type);
				if ($rowNum != '') $data['filldata']['row'] = $rowNum;
				$this->load->view('suggestfill', $data);
				break;
			case 'make':
			default:
				//Do nothing
		}
	}
}
?>