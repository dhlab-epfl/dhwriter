<?php
date_default_timezone_set('Europe/Zurich');
header('Content-Type: text/plain');
header('Cache-Control: no-cache, must-revalidate');

function execute($f) {
	switch ($f) {
		case 'getPaper':
			include('_db/_db.php');
			session_start();
			$paper = db_fetch(db_s('papers', array('id' => $_REQUEST['id'], 'user_id' => $_SESSION['user_id'])));
			echo $paper['text'];
			break;
		case 'savePaper':
			include('_db/_db.php');
			session_start();
			$paperDatas = array(
								'text' => $_REQUEST['html'],
								'date_updated' => date('Y-m-d H:i:s'),
								);
			db_u('papers', array('id' => $_REQUEST['id'], 'user_id' => $_SESSION['user_id']), $paperDatas);
		default:break;
	}
}

execute($_REQUEST['f']);
?>