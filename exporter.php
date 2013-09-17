<?php
	date_default_timezone_set('Europe/Zurich');
	include('_session.php');
	include_once('_phptoolbox.php');
	require('lib/pdf/fpdf.php');

	// =========================================================================================================================================================
	function xslt($xml, $xsl_file) {
		$xsl = new XSLTProcessor();
		$xsldoc = new DOMDocument();
		$xsldoc->load($xsl_file);
		$xsl->importStyleSheet($xsldoc);

		$xmldoc = new DOMDocument();
		$xmldoc->loadXML($xml);
		return $xsl->transformToXML($xmldoc);
	}

	// =========================================================================================================================================================
	function html($paper) {
		$authors = array();
		$html = '<html>';
			$html.= '<head>';
				$html.= '<title>'.$paper['title'].'</title>';
				$html.= '<link rel="stylesheet" type="text/css" href="http://dhwriter.org/s/papers.css"></link>';
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

	// =========================================================================================================================================================
	define('kOutputMode_none', 0);
	define('kOutputMode_inline', 1);
	define('kOutputMode_block', 2);
	define('kOutputMode_reference', 3);
	class PDF extends FPDF {
		var $HREF;
		var $defaultStyle = array('tag' => 'body', 'size'=>12, 'font' => 'Helvetica', 'style'=>'', 'lineHeight' => 5, 'color'=>array(0,0,0));			// Initial (=default) styles
		var $outputMode, $styleStack, $lineHeight;
		var $leftMargin = 25, $topMargin = 15;
		var $references = array();

		function PDF($orientation='P', $unit='mm', $size='A4') {
			$this->FPDF($orientation,$unit,$size); 								// Appel au constructeur parent
			$this->SetLeftMargin($this->leftMargin);
			$this->SetRightMargin($this->topMargin);
			$this->styleStack = array($this->defaultStyle);
			$this->HREF = '';
			$this->lineHeight = $this->defaultStyle['lineHeight'];
			$this->outputMode = kOutputMode_inline;
		}
		function Footer() {
			$this->SetY(-15);													// Positionnement à 1,5 cm du bas
			$this->SetFont($this->defaultStyle['font'],'I',8);									//
			$this->Cell(0, 10, $this->PageNo().' / {nb}', 0, 0,'R');			// Numéro de page
		}

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
							if (preg_match('/([^=]+)="([^>]+)/',$v,$a3))
								$attributes[strtolower($a3[1])] = $a3[2];
						}

						/*
						preg_match('/^([a-zA-Z]+)\s*(.*)/',$e,$fullTag);
						$tag = $fullTag[1];
						$attrPart = $fullTag[2];

						if (preg_match('/([^=]+="[^"]+")+/',$e,$parts)) {
							print_r($parts);
						}
						$parts = explode('"', $e);
						$tag = strtolower(array_shift($parts));
						$attributes = array();
						for ($i=0; $i<(count($parts)-(count($parts)%2)); $i+=2) {
							$attributes[$parts[$i]] = $parts[$i+1];
						}
						*/
						$this->OpenTag($tag,$attributes);
					}
				}
			}
		}

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


	function pdf($paper) {
		$pdf = new PDF();
		$pdf->AliasNbPages();
// Première page
		$pdf->AddPage();
		$pdf->SetFont('Helvetica','',20);
		$pdf->MultiCell(0,8,utf8_decode($paper['title']),0,'C');
		$pdf->ln();
		//___________________________________
		$pdf->SetFont('Helvetica','',14);
		$authors = db_s('authors', array('paper_id' => $paper['id']), array('disp_order' => 'ASC'));
		while ($author = db_fetch($authors)) {
			$pdf->Cell(75, 7, utf8_decode($author['last_name'].', '.$author['first_name']), 0, 1);
			$pdf->Cell(75, 7, utf8_decode($author['affiliation']), 0, 1);
			$pdf->Link($pdf->getX(), $pdf->getY(), 75, 7, 'mailto:'.$author['email']);
			$pdf->Cell(75, 7, utf8_decode($author['email']), 0, 1);
			$pdf->ln();
		}
		//___________________________________
		$pdf->SetFont('Helvetica','',16);
		$link = str_replace('www.', '', $_SERVER['SERVER_NAME']).'/paper/'.datetime('U', $paper['date_updated']).'.'.$paper['user_id'].'.'.$paper['id'].'.'.$paper['version'].'.html';
		$pdf->SetXY(25, 200);
		$pdf->Link($pdf->getX(), $pdf->getY(), 170, 10, 'http://'.$link);
		$pdf->Rect($pdf->getX(), $pdf->getY(), 170, 10);
		$pdf->Cell(0, 10, 'Online version:  '.$link,0,1,'C');
// Seconde page
		$pdf->AddPage();
		$pdf->SetFontSize(12);
		$pdf->WriteHTML(utf8_decode(str_replace('’', "'", html_entity_decode($paper['text']))));
		$pdf->WriteReferences();
		$pdf->Output();
	}

	if (isset($_REQUEST['id'])&&isset($_REQUEST['ext'])) {
		header('Content-Disposition: attachment; filename="dh2014-abstract'.$_REQUEST['id'].'.'.$_REQUEST['ext'].'"');
		$paper = db_fetch(db_s('papers', array('id' => $_REQUEST['id'], 'user_id' => isset($_SESSION['user_id'])?$_SESSION['user_id']:'1'), array('version' => 'DESC')));
		switch ($_REQUEST['ext']) {
			case 'tei':
				header('Content-Type: application/tei+xml');
				$source = preg_replace('/<(br|hr|img)([^>]*)>/', '<\1\2/>', str_replace('&nbsp;', ' ', html($paper)));
				echo xslt($source, 'data/tei.xsl');
				break;
			case 'html':
				header('Content-Type: text/html');
				echo html($paper);
				break;
			case 'pdf':
				header('Content-Type: application/pdf');
				if (isset($_SESSION['user_id'])) {
					$paper['version'] = $paper['version']+1;
					db_i('papers', $paper);
				}
				echo pdf($paper);
				break;
			default:
				break;
		}
	}
?>