<?php
header('Content-Type: text/json');
if (isset($text)) {
	echo $filldata;
// 	log_message('error', print_r($filldata, true));
} else {
	echo json_encode($filldata);
//  	$temp = json_encode($filldata);
//  	log_message('error', print_r($temp, TRUE));
}
?>