<?php
	date_default_timezone_set('Europe/Zurich');
	include_once('_phptoolbox.php');
	header('Vary: Accept');
	header('Content-Type: text/html; charset=utf-8');

	function html($paper) {
		$authors = array();
		$html = '<html>';
			$html.= '<head>';
				$html.= '<title>'.$paper['title'].'</title>';
				$html.= '<base href="http://'.$_SERVER['SERVER_NAME'].'" />';
				$html.= '<link rel="stylesheet" type="text/css" href="/s/papers.css"></link>';
				$html.= '<meta name="description" content="'.$paper['abstract'].'" />';
				$a_r = db_s('authors', array('paper_id' => $paper['id']), array('disp_order' => 'ASC'));
				while ($author = db_fetch($a_r)) {
					$authors[] = array($author['last_name'].','.$author['first_name'], $author['affiliation'], $author['email']);
					$html.= '<meta name="author" content="'.$author['last_name'].','.$author['first_name'].';'.$author['affiliation'].';'.$author['email'].'" />';
				}
			$html.= '</head>';
			$clearHeader = '<h1>'.$paper['title'].'</h1><ul id="authors">';
			foreach ($authors as $a) {
				$clearHeader.= '<li>'.$a[0].'<br/>'.$a[1].'<br/>'.$a[2].'</li>';
			}
			$clearHeader.= '</ul><fieldset><legend>Abstract</legend>'.$paper['abstract'].'</fieldset>';
			$html.= str_replace('<body>', '<body id="canvas">'.$clearHeader, $paper['text']);
		$html.= '</html>';
		return $html;
	}

	if (isset($_GET['id'])&&isset($_GET['user_id'])&&isset($_GET['v'])&&isset($_GET['ts'])) {
		include('_db/_db.php');
		if ($paper = db_fetch(db_s('papers', array('user_id' => $_GET['user_id'], 'id' => $_GET['id'], 'date_updated' => date('Y-m-d H:i:s', $_GET['ts']), 'version' => $_GET['v'])))) {
			echo html($paper);
		}
		else echo '<h1>404 Not Found</h1>';
	}
	else {
		echo '<h1>403 Not Authorized</h1>';
		print_r($_GET);
	}
?>