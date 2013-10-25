<?php
	date_default_timezone_set('Europe/Zurich');
	include('_session.php');
	include_once('_phptoolbox.php');
	require('lib/mpdf/mpdf.php');

	// =========================================================================================================================================================
	function xslt($xml, $xsl_file) {
		$xsl = new XSLTProcessor();
		$xsldoc = new DOMDocument();
		$xsldoc->load($xsl_file);
		$xsl->importStyleSheet($xsldoc);
		$xsl->setParameter('', 'FOLDER', '');
		$xsl->setParameter('', 'DATE_CREATED', date('Ymd'));
		$xsl->setParameter('', 'TIME_CREATED', date('H:i:s'));

		$xmldoc = new DOMDocument();
		$xmldoc->loadXML($xml);
		return $xsl->transformToXML($xmldoc);
	}

	// =========================================================================================================================================================
	function html($paper, $authors_r, $standalone=false) {
		$authors = array();
		$pageHeader.= '<head>';
			$pageHeader.= '<title>'.$paper['title'].'</title>';
			$pageHeader.= '<link rel="stylesheet" type="text/css" href="http://dhwriter.org/s/papers.css"></link>';
			$pageHeader.= '<meta name="description" content="'.$paper['abstract'].'" />';
			foreach ($authors_r as $author) {
				$authors[] = array($author['last_name'].','.$author['first_name'], $author['affiliation'], $author['email']);
				$pageHeader.= '<meta name="author" content="'.$author['last_name'].','.$author['first_name'].';'.$author['affiliation'].';'.$author['email'].'" />';
			}
		$pageHeader.= '</head>';
		$clearHeader = '<h1>'.$paper['title'].'</h1><ul id="authors">';
		foreach ($authors as $a) {
			$clearHeader.= '<li>'.$a[0].'<br/>'.$a[1].'<br/>'.$a[2].'</li>';
		}
		$clearHeader.= '</ul><fieldset><legend>Summary</legend>'.$paper['abstract'].'</fieldset>';

		// Move references contents to the end of the document
		$body = preg_replace('/<(br|hr|img)([^>]*)>/', '<\1\2/>', $paper['text']).'</section><section id="references"><h2>References</h2><ol>';
		$i=0;
		$count = 1;
		while ($count > 0) {
			$i++;
			$body = preg_replace('/<cite([^>]+)><span>([^\[][^<]*)<\/span><\/cite>(.*)/si', '<cite>['.$i.']</cite>$3<li>$2</li>', $body, 1, &$count);
		}
		// Manual numbering of the figures (CSS rules not supported here)
		$i=0;
		$count = 1;
		while ($count > 0) {
			$i++;
			$body = preg_replace('/<figcaption>([^<]*)<\/figcaption>/si', '<figcaption class="nonum">Fig. '.$i.': \1</figcaption>', $body, 1, &$count);
		}
		$body.= '</ol>';
		$pageBody = '<body><section id="header">'.$clearHeader.'</section><section id="article">'.$body.'</section></body>';

		if ($standalone) {
			return '<html>'.$pageHeader.$pageBody.'</html>';			// xmlns="http://www.w3.org/1999/xhtml"  (declaring xmlns here breaks xslt pipeline)
		}
		else {
			return $pageBody;
		}
	}

	// =========================================================================================================================================================
	define('kOutputMode_none', 0);
	define('kOutputMode_inline', 1);
	define('kOutputMode_block', 2);
	define('kOutputMode_reference', 3);
	class PDF extends mPDF {
		var $HREF;
		var $defaultStyle = array('tag' => 'body', 'size'=>12, 'font' => 'DejaVuSans', 'style'=>'', 'lineHeight' => 5, 'color'=>array(0,0,0));			// Initial (=default) styles
		var $outputMode, $styleStack, $lineHeight;
		var $leftMargin = 25, $topMargin = 15;
		var $references = array();

		function PDF($orientation='P', $unit='mm', $size='A4') {
#			$this->AddFont('DejaVuSans','','DejaVuSans.ttf', true);
			$this->mPDF($orientation,$size); 								// Appel au constructeur parent
			$this->useAdobeCJK = true;
			$this->SetAutoFont(AUTOFONT_ALL);
			$this->SetLeftMargin($this->leftMargin);
			$this->SetRightMargin($this->topMargin);
			$this->styleStack = array($this->defaultStyle);
			$this->HREF = '';
			$this->lineHeight = $this->defaultStyle['lineHeight'];
			$this->outputMode = kOutputMode_inline;
		}
		function Footer() {
			$this->SetY(-15);													// Positionnement à 1,5 cm du bas
			$this->SetFont($pdf->default_font,'I',8);									//
			$this->Cell(0, 10, $this->PageNo().' / {nb}', 0, 0,'R');			// Numéro de page
		}
/*
		function WriteHTML($html) {
    // Parseur HTML
			$html = str_replace("\n",' ',$html);
			$a = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
			foreach ($a as $i=>$e) {
				if($i%2==0) {                     // Texte ______________________________________________________
					if (trim($e)!='') {
						switch ($this->outputMode) {
							case kOutputMode_inline:
								$this->Write($this->lineHeight,$e);
								break;
							case kOutputMode_block:
								$this->ln();
								$this->MultiCell(0,$this->lineHeight,$e);
								break;
							case kOutputMode_reference:
								$this->references[] = $e;
								$this->Write($this->lineHeight, '['.count($this->references).']');
							break;
							default:break;
						}
					}
				}
				else {                           // Balise ______________________________________________________
					if($e[0]=='/')
						$this->CloseTag(strtolower(substr($e,1)));
					else {                       // Extraction des attributs ____________________________________
						$a2 = explode(' ',$e);
						$tag = strtolower(array_shift($a2));
						$attributes = array();
						foreach ($a2 as $v) {
							if (preg_match('/([^=]+)="([^>]+)"/',$v,$a3))
								$attributes[strtolower($a3[1])] = $a3[2];
						}

						$this->OpenTag($tag,$attributes);
					}
				}
			}
		}
*/
		function OpenTag($tag, $attr) {
    // Balise ouvrante
			if ($tag=='br') {
				$this->Ln(5);
			}
			elseif ($tag=='img' && @$attr['src']!='') {
				$src = substr($attr['src'],1);
				$pict = imagecreatefromstring(file_get_contents($src));
				$w = imagesx($pict);
				$h = imagesy($pict);
				$mmH = $h*120/$w;
				$this->ln();
				$this->Cell(0,$mmH,$this->Image($src,$this->leftMargin+25,$this->getY(), 120, $mmH));
			}
			else {
				$localStyle = array();
				$localStyle['tag'] = $tag;
				if (in_array($tag, array('b', 'strong', 'h1', 'h2', 'h3', 'h4'))) {
					$localStyle['style'] = 'B';
					if (substr($tag, 0, 1)=='h') {
						$localStyle['size'] = 12+2*(4-substr($tag, 1, 2));
						$localStyle['color'] = array(127, 0, 0);
						$localStyle['outputMode'] = kOutputMode_block;
						$localStyle['lineHeight'] = $localStyle['size']/1.5;
					}
				}
				elseif (in_array($tag, array('i', 'em', 'q'))) {
					$localStyle['style'] = 'I';
				}
				elseif ($tag=='a' && @$attr['href']!='') {
					$this->HREF = $attr['href'];
					$localStyle['style'] = 'U';
					$localStyle['color'] = array(0, 64, 127);
				}
				elseif ($tag=='li') {
					$localStyle['outputMode'] = kOutputMode_block;
				}
				elseif ($tag=='span' && @$attr['class']=='MathJax') {
					$localStyle['outputMode'] = kOutputMode_none;
				}
				elseif ($tag=='cite') {
					$localStyle['outputMode'] = kOutputMode_reference;
					$localStyle['color'] = array(127, 0, 0);
					$localStyle['size'] = 9;
					$localStyle['lineHeight'] = 3;
				}
				else return;							// Ignore others/unsupported tags
				$this->styleStack[] = $localStyle;
				$this->applyStyle();
			}
		}

		function CloseTag($tag) {
			/*
			if(in_array($tag, array('b', 'i', 'strong', 'em', 'h1','h2','h3','h4')))
				$this->SetStyle($tag,false);
			if($tag=='a')
				$this->HREF = '';
			if ($tag=='span' && @$attr['class']=='MathJax')
				$this->offscreen = true;
				*/
			$removedStyle = array_pop($this->styleStack);
			$this->applyStyle();
		}

		function applyStyle() {
			$outputMode = kOutputMode_inline;
			$size = $this->defaultStyle['size'];
			$font = $this->defaultStyle['font'];
			$color = $this->defaultStyle['color'];
			$styles = array($this->defaultStyle['style']);
			$lineHeight = $this->defaultStyle['lineHeight'];
			foreach ($this->styleStack as $s) {
				if (isset($s['size'])) {
					$size = $s['size'];
				}
				if (isset($s['style'])) {
					$styles[] = $s['style'];
				}
				if (isset($s['outputMode'])) {
					$outputMode = $s['outputMode'];
				}
				if (isset($s['font'])) {
					$font = $s['font'];
				}
				if (isset($s['color'])) {
					$color = $s['color'];
				}
				if (isset($s['lineHeight'])) {
					$lineHeight = $s['lineHeight'];
				}
			}
			$this->SetTextColor($color[0], $color[1], $color[2]);
			$this->SetFont($font, trim(implode('', array_unique($styles))), $size);
			$this->outputMode = $outputMode;
			$this->lineHeight = $lineHeight;
		}

		function WriteReferences() {
			$i=0;
			$html = '<h2>References</h2><ul>';
			foreach ($this->references as $ref) {
				$html.= '<li>'.(++$i).'. '.$ref.'</li>';
			}
			$html.='</ul>';
			$this->WriteHTML($html);
		}
/*
		function SetStyle($tag, $enable) {
    // Modifie le style et sélectionne la police correspondante
			if (substr($tag, 0, 1)=='h') {
				if ($enable) {
					$this->SetFontSize(12+(2*substr($tag, 1, 2)));
					$this->SetTextColor(127, 0, 0);
				}
				else {
					$this->Ln(5);
					$this->SetFontSize(12);
					$this->SetTextColor(0, 0, 0);
				}
			}
			$this->$tag += ($enable ? 1 : -1);
			$style = '';
			foreach(array('B', 'I', 'U') as $s) {
				if($this->$s>0)
					$style .= $s;
			}
			$this->SetFont('',$style);
		}
*/
	}

	function pdf($paper, $authors, $reviewMode=false) {
		$pdf = new mPDF();
		$pdf->AliasNbPages();
		if ($reviewMode) {
			// Première page
			$pdf->AddPage();
			// Add a Unicode font (uses UTF-8)
			$pdf->SetFontSize(20);
			$pdf->MultiCell(0,8,$paper['title'],0,'C');
			$pdf->ln();
			//___________________________________
			$pdf->SetFontSize(14);
			foreach ($authors as $author) {
				$pdf->Cell(75, 7, $author['last_name'].', '.$author['first_name'], 0, 1);
				$pdf->Cell(75, 7, $author['affiliation'], 0, 1);
	#			$pdf->Link($pdf->getX(), $pdf->getY(), 75, 7, 'mailto:'.$author['email']);
				$pdf->WriteHTML('<a href="mailto:'.$author['email'].'">'.$author['email'].'</a>');
				$pdf->ln();
			}
			$pdf->ln();
			$pdf->Cell(75, 7, @array_pop(array_filter(explode('/', $paper['category'].'/'.$paper['subcategory']))), 0, 1);
			//___________________________________
			$pdf->SetFontSize(16);
			$link = str_replace('www.', '', $_SERVER['SERVER_NAME']).'/paper/'.datetime('U', $paper['date_updated']).'.'.$paper['user_id'].'.'.$paper['id'].'.'.$paper['version'].'.html';
			$pdf->SetXY(25, 200);
	#		$pdf->Link($pdf->getX(), $pdf->getY(), 170, 10, 'http://'.$link);
	#		$pdf->Rect($pdf->getX(), $pdf->getY(), 170, 10);
	#		$pdf->Cell(0, 10, 'URL:  '.$link,0,1,'C');
			$pdf->WriteHTML('<a href="http://'.$link.'">URL: '.$link.'</a>');
		}
		// Pages suivantes (article)
		$pdf->AddPage();
		$pdf->SetFontSize(12);

		$stylesheet = file_get_contents('s/pdf.css');
		$pdf->WriteHTML($stylesheet,1);	// The parameter 1 tells that this is css/style only and no body/html/text
		$pdf->WriteHTML(html($paper, $authors, false));
		$pdf->Output();
		exit;
	}

	function process($fileName, $paper, $authors=array(), $ext='tei', $reviewMode=false) {
		header('Content-Disposition: attachment; filename="'.$fileName.'.'.$_REQUEST['ext'].'"');
		switch ($_REQUEST['ext']) {
			case 'tei':
				header('Content-Type: application/tei+xml');
				header('Content-Type: text/plain');
				$source = str_replace('><', ">\n<", str_replace('&nbsp;', ' ', html($paper, $authors, true)));
				echo xslt($source, 'data/tei.xsl');
				break;
			case 'html':
				header('Content-Type: text/html');
				echo html($paper, $authors, true);
				break;
			case 'pdf':
				header('Content-Type: application/pdf');
				if ($reviewMode && isset($_SESSION['user_id']) && isset($paper['version'])) {
					$paper['version'] = $paper['version']+1;
					db_i('papers', $paper);
				}
				echo pdf($paper, $authors, $reviewMode);
				break;
			default:
				break;
		}
	}

	if (isset($_REQUEST['ext'])) {
		$authors = array();
		$reviewMode = (@$_REQUEST['rev']>0);
		if (isset($_REQUEST['id'])) {
			// database mode
			$paper = db_fetch(db_s('papers', array('id' => $_REQUEST['id'], 'user_id' => isset($_SESSION['user_id'])?$_SESSION['user_id']:'1'), array('version' => 'DESC')));
			$a_r = db_s('authors', array('paper_id' => $paper['id']), array('disp_order' => 'ASC'));
			while ($a = db_fetch($a_r)) {
				$authors[] = $a;
			}
			process('dh2014-abstract'.$_REQUEST['id'], $paper, $authors, $_REQUEST['ext'], $reviewMode);
		}
		elseif (isset($_REQUEST['src'])) {
			// source provided (typically unsaved document)
			$cat = explode('/', $_REQUEST['category']);
			$paper = array(
							'text' => $_REQUEST['src'],
							'title' => $_REQUEST['title'],
							'abstract' => $_REQUEST['abstract'],
							'date_updated' => date('Y-m-d H:i:s'),
							'category' => $cat[0],
							'subcategory' => $cat[1],
							'keywords' => $_REQUEST['keywords'],
							'topics' => $_REQUEST['topics'],
						);
			$a_ids = (array)$_REQUEST['author'];
			foreach ($a_ids as $id) {
				$authors[] = array(
									'first_name' => $_REQUEST['first_name'.$id],
									'last_name' => $_REQUEST['last_name'.$id],
									'email' => $_REQUEST['email'.$id],
									'affiliation' => $_REQUEST['affiliation'.$id],
									);
			}
			process('abstract', $paper, $authors, $_REQUEST['ext'], $reviewMode);
		}
		else die('Error: no source data provided');
	}
	else die('Error: no valid format provided');
?>