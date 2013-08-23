<?php
	include('_session.php');

	function xslt($xml, $xsl_file) {
		$xsl = new XSLTProcessor();
		$xsldoc = new DOMDocument();
		$xsldoc->load($xsl_file);
		$xsl->importStyleSheet($xsldoc);

		$xmldoc = new DOMDocument();
		$xmldoc->loadXML($xml);
		return $xsl->transformToXML($xmldoc);
	}

	function html($paper) {
		$html = '<html>';
			$html.= '<head>';
				$html.= '<title>'.$paper['title'].'</title>';
				$html.= '<meta name="description" content="'.$paper['abstract'].'" />';
				$a_r = db_s('authors', array('paper_id' => $paper['id']), array('disp_order' => 'ASC'));
				while ($author = db_fetch($a_r)) {
					$html.= '<meta name="author" content="'.$author['last_name'].','.$author['first_name'].';'.$author['affiliation'].';'.$author['email'].'" />';
				}
			$html.= '</head>';
			$html.= $paper['text'];
		$html.= '</html>';
		return $html;
	}

	if (isset($_REQUEST['id'])&&isset($_REQUEST['ext'])) {
	#	header('Content-Disposition: attachment; filename="dh2014-abstract'.$_REQUEST['id'].'.tei"');
		$paper = db_fetch(db_s('papers', array('id' => $_REQUEST['id'], 'user_id' => $_SESSION['user_id'])));
		switch ($_REQUEST['ext']) {
			case 'tei':
	#			header('Content-Type: application/tei+xml');
				header('Content-Type: text/plain');
				$source = preg_replace('/<(br|hr|img)([^>]*)>/', '<\1\2/>', str_replace('&nbsp;', ' ', html($paper)));
				echo xslt($source, 'data/tei.xsl');
				break;
			case 'html':
				header('Content-Type: text/plain');
				echo html($paper);
				break;
			default:
				break;
		}
	}
?>