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
				$html.= '<script type="text/javascript" src="/MathJax/MathJax.js?config=TeX-MML-AM_HTMLorMML-full&amp;delayStartupUntil=configured"></script>';
				$html.= '<script type="text/x-mathjax-config">MathJax.Hub.Config({
		jax: ["input/MathML", "input/TeX", "input/AsciiMath", "output/NativeMML", "output/HTML-CSS"],
		extensions: ["asciimath2jax.js","tex2jax.js","mml2jax.js","MathMenu.js","MathZoom.js","MathEvents.js","toMathML.js"],
		tex2jax: { inlineMath: [["[TEX_START]","[TEX_END]"], ["\\(", "\\)"]] },
			  TeX: {
			  	extensions: ["AMSmath.js","AMSsymbols.js","noErrors.js","noUndefined.js"], noErrors: { disabled: true }
			  },
			  AsciiMath: { noErrors: { disabled: true } }
			});</script>';
				$html.= '<meta name="description" content="'.$paper['abstract'].'" />';
				$a_r = db_s('authors', array('paper_id' => $paper['id']), array('disp_order' => 'ASC'));
				while ($author = db_fetch($a_r)) {
					$authors[] = array($author['last_name'].','.$author['first_name'], $author['affiliation'], $author['email']);
					$html.= '<meta name="author" content="'.$author['last_name'].','.$author['first_name'].';'.$author['affiliation'].';'.$author['email'].'" />';
				}
			$html.= '</head>';
			$html.= '<body id="canvas">';
				$html.= '<h1>'.$paper['title'].'</h1><ul id="authors">';
				foreach ($authors as $a) {
					$html.= '<li>'.$a[0].'<br/>'.$a[1].'<br/>'.$a[2].'</li>';
				}
				$html.= '</ul><fieldset><legend>Abstract</legend>'.$paper['abstract'].'</fieldset>';
				$html.= $paper['text'];
				$html.= '<h2>References</h2><ol>';
				$refs = array();
				preg_match_all('|<cite[^>]+><span>([^<]*)</span></cite>|u', $paper['text'], $refs);
				foreach ($refs[1] as $ref) {
					$html.= '<li>'.$ref.'</li>';
				}
				$html.= '</ol>';
				$html.= '<script>MathJax.Hub.Configured();</script>';
			$html.= '</body>';
		$html.= '</html>';
		return $html;
	}

	if (isset($_GET['id'])&&isset($_GET['user_id'])&&isset($_GET['v'])&&isset($_GET['ts'])) {
		include('_db/_db.php');
		if ($paper = db_fetch(db_s('papers', array('user_id' => $_GET['user_id'], 'id' => $_GET['id'], 'version' => $_GET['v'])))) {
			echo html($paper);
		}
		else echo '<h1>404 Not Found</h1>';
	}
	else {
		echo '<h1>403 Not Authorized</h1>';
		print_r($_GET);
	}
?>