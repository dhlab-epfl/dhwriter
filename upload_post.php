<?php
	header('Vary: Accept');
	header('Content-Type: text/html; charset=utf-8');
	define('FOLDER_TMP', 'tmp/');

	include('_phptoolbox.php');
	if (isset($_FILES['fileToUpload'])&&$_FILES['fileToUpload']['name']!='') {
		if (@$_REQUEST['n']=='auto') {
			$fileName = hashName($_FILES['fileToUpload']['tmp_name'], $_FILES['fileToUpload']['name']);
		}
		else {
			$fileName = preg_replace('/-[-+]/','-',preg_replace('/[\/|\s]/','_',$_FILES['fileToUpload']['name']));
		}
		move_uploaded_file($_FILES['fileToUpload']['tmp_name'],FOLDER_TMP.$fileName);
		print(json_encode(array('status' => 1, 'file' => $fileName)));
	}
	else {
		print(json_encode(array('status' => 0, 'msg' => 'File transfer aborted.')));
	}
?>