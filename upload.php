<?php
/**
 * Easy example script to store uploaded files
 * in the filesystem, make sure that the folder is writeable.
 * Please don't use this example file in productive environment
 * It is just used to illustrate the upload funtionality and it may
 * contain security issues.
 */

define('FOLDER_STORAGE', 'data/figures/');

class upload {
	public function writeFile($rawContent) {
		$headers = getallheaders();
		$filename = $headers['X-File-Name'];
		$filecontent = $rawContent;

		$filePath = FOLDER_STORAGE.'DH2014_'.$_SESSION['user_id'].'_'.$filename;
		$fp = fopen($filePath, 'w');
		fwrite($fp, $filecontent);
		fclose($fp);

		return '/'.$filePath;
	}
}

if(!$HTTP_RAW_POST_DATA){
	$HTTP_RAW_POST_DATA = file_get_contents('php://input');
	if(empty($HTTP_RAW_POST_DATA)) {
		die('Error: no post data supplied');
	}
}

session_start();
$file = new upload();
echo json_encode(array('url' => $file->writeFile($HTTP_RAW_POST_DATA)));

?>