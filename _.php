<?php
date_default_timezone_set('Europe/Zurich');
header('Content-Type: text/plain');
header('Cache-Control: no-cache, must-revalidate');

function execute($f) {
	switch ($f) {
		case 'getPaper':
			include('_db/_db.php');
			session_start();
			if (isset($_SESSION['user_id'])) {
				$paper = db_fetch(db_s('papers', array('id' => $_REQUEST['id'], 'user_id' => $_SESSION['user_id']), array('version' => 'DESC')));
			}
			else {
				$paper = db_fetch(db_s('papers', array('id' => 1), array('version' => 'DESC')));
			}
			echo $paper['text'];
			break;
		case 'savePaper':
			include('_db/_db.php');
			session_start();
			if (isset($_SESSION['user_id'])) {
				$currentVersion = db_fetch(db_s('papers', array('id' => $_REQUEST['id'], 'user_id' => $_SESSION['user_id']), array('version' => 'DESC')));
				$cat = explode('/', $_REQUEST['category']);
				$paperDatas = array(
									'text' => $_REQUEST['html'],
									'title' => trim($_REQUEST['title']),
									'abstract' => trim($_REQUEST['abstract']),
									'date_updated' => date('Y-m-d H:i:s'),
									'category' => $cat[0],
									'subcategory' => $cat[1],
									'keywords' => trim($_REQUEST['keywords']),
									'topics' => trim($_REQUEST['topics']),
									);
				db_u('papers', array('id' => $_REQUEST['id'], 'user_id' => $_SESSION['user_id'], 'version' => $currentVersion['version']), $paperDatas);
				$authorIds = (array)$_REQUEST['author'];
				foreach ($authorIds as $id) {
					db_u('authors', array('id' => $id), array('first_name' => $_REQUEST['first_name'.$id], 'last_name' => $_REQUEST['last_name'.$id], 'email' => $_REQUEST['email'.$id], 'affiliation' => $_REQUEST['affiliation'.$id]));
				}
			}
			break;
		case 'addAuthor':
			include('_db/_db.php');
			session_start();
			if ($last_author = db_fetch(db_s('authors', array('paper_id' => $_REQUEST['paper_id'], 'user_id' => $_SESSION['user_id']), array('disp_order' => 'DESC')))) {
				echo db_i('authors', array('paper_id' => $_REQUEST['paper_id'], 'disp_order' => $last_author['disp_order']+1));
			}
			else echo '0';
			break;
		case 'deleteAuthor':
			include('_db/_db.php');
			session_start();
			db_d('authors', array('id' => $_REQUEST['id']));
			break;
		case 'getCitations':
			include('_db/_db.php');
			session_start();
			$terms = explode(' ', strtolower($_REQUEST['term']));
			$citations = db_x('SELECT * FROM citations WHERE LOWER(label) LIKE "%'.implode('%" AND LOWER(label) LIKE "%', $terms).'%";', false);
			$out = array();
			while ($citation = db_fetch($citations)) {
				$label = strip_tags($citation['label']);
/*				foreach ($terms as $t) {
					$label = str_replace($t, '<b>'.$t.'</b>', $label);
				}*/
				$out[] = utf8_encode($label);
			}
			echo json_encode($out);
			break;
		case 'addCitation':
			include('_db/_db.php');
			session_start();
			if (@$_SESSION['user_id']>0) {
				db_i('citations', array('label' => $_REQUEST['label']));
			}
			break;
		default:break;
	}
}

execute($_REQUEST['f']);
?>