<?php

// ===== Pop an alert message using javascript:alert() =====

function alert($message) {
   printJS('alert("'.str_replace("\"", "\\\"", $message).'");');
}

function datetime($syntax, $datetime) {
	if ($datetime==0) {
		return '';
	}
	else {
		$year = substr($datetime,0,4);
		$month = substr($datetime,5,2);
		$day = substr($datetime,8,2);
		$hour = substr($datetime,11,2);
		$min = substr($datetime,14,2);
		$sec = substr($datetime,17,2);
		return date($syntax, mktime($hour,$min,$sec,$month,$day,$year));
	}
}

function urlStr($str) {
	// Supprime les caractères non-ASCII, remplace les espaces par des underscores et retourne le string en caractères minuscules
	$extension = fileExtension($str);
	$str = substr($str, 0, strlen($str)-strlen($extension)-1);
	$url = strtolower( preg_replace('/[^\a-zA-Z0-9_]/', '', str_replace(' ', '_', stripslashes(trim($str)))) );
	return ($url.'.'.$extension);
}

function fileExtension($str) {
	$name_parts = explode('.', $str);
	return $name_parts[count($name_parts)-1];
}

function hashName($file, $origName, $length=22) {
	$hash = preg_replace('/[\/|\+]/', '-', substr(base64_encode(md5_file($file, true)), 0, $length));
	return $hash.'.'.fileExtension($origName);
}

function readSetting($id) {
	$content_setting = db_x('SELECT value FROM settings WHERE id="'.$id.'";', false);
	$row_setting = db_fetch($content_setting);
	return $row_setting['value'];
}

function summarize($text, $chars_num=100, $sharp=false) {
	$plain = strip_tags($text, ($sharp?'':'<br>'));
	$plain = str_replace("\n", ' ', $plain);
	$plain = str_replace("\r", ' ', $plain);
	$plain = str_replace('&nbsp;', ' ', $plain);
	if ($sharp) {
		$summary = substr($plain, 0, $chars_num);
		if (strlen($plain) > $chars_num) $summary.='...';
	}
	else {
		$summary = $plain;
		if (strlen($plain) > $chars_num) {
			$dot_pos = strpos($plain, '. ', $chars_num);
			if ($dot_pos === false) {
				$summary = $plain;
			}
			else {
				$summary = substr($plain, 0, $dot_pos+1).' [...]';
			}
		}
		else $summary = $plain;
	}
	return $summary;
}

function locStr($key) {
	if (array_key_exists($key, $GLOBALS)) {
		$loc = $GLOBALS[$key][$_SESSION['lang']];
	}
	else {
		$loc = @$GLOBALS['localized_strings'][$key];
	}
	$numargs = func_num_args();
	if ($numargs > 1) {
		for ($i=1; $i<$numargs; $i++) {
			$arg = func_get_arg($i);
			$loc = str_replace('%'.$i.'$s', $arg, $loc);
		}
	}
	return str_replace('& ', '&amp; ', $loc);
}

function generatePassword($length=9, $strength=0) {
	$password = '';
	$vowels = 'aeuy';
	$consonants = 'bdghjmnpqrstvz';
	if ($strength & 1) $consonants .= 'BDGHJLMNPQRSTVWXZ';
	if ($strength & 2) $vowels .= "AEUY";
	if ($strength & 4) $consonants .= '23456789';
	if ($strength & 8) $consonants .= '@#$%';
	$alt = time()%2;
	for ($i=0; $i<$length; $i++) {
		if ($alt == 1) {
			$password .= $consonants[(rand() % strlen($consonants))];
			$alt = 0;
		} else {
			$password .= $vowels[(rand() % strlen($vowels))];
			$alt = 1;
		}
	}
	return $password;
}

function unzip($f) /* file.zip */ {
	if (substr($f,-4)!='.zip'||!($z=zip_open($f))||$z==11) return false;
	$r=array();
	$p=strrev(strstr(strrev($f), '/'));
	while ($zf=zip_read($z)) {
		$n=preg_replace('/[^a-z0-9\-_\.\/]/i','_',zip_entry_name($zf));
		if (($s=zip_entry_filesize($zf))<=0||substr(basename($n),0,2)=='._') continue;
		$d=dirname($p.$n);
		if (!file_exists($d)) {
			$d=explode('/',$d);
			$c=count($d);
			$t='';
			for ($i=0;$i<$c;$i++) {
				$t.=$d[$i].'/';
				if(!file_exists($t)) mkdir($t,0777);
			}
		}
		if (zip_entry_open($z,$zf,'r')) {
			if ($h=fopen($p.$n,'w')) {
				fwrite($h,zip_entry_read($zf,$s));
				fclose($h);
				chmod($p.$n,0666); $r[]=$n;
			}
			zip_entry_close($zf);
		}
	}
	zip_close($z); unlink($f); return $r;
}

function unlink_r($dir) {
	if(!$dh = @opendir($dir)) return;
	while (false !== ($obj = readdir($dh))) {
		if($obj == '.' || $obj == '..') continue;
		if (!@unlink($dir . '/' . $obj)) {
			unlink_r($dir.'/'.$obj);
		}
	}
	closedir($dh);
	@rmdir($dir);
}

function rename2x($source, $targetFolder, $targetFileName) {
	$pict2x = imagecreatefromstring(file_get_contents($source));
	$w = imagesx($pict2x);
	$h = imagesy($pict2x);
	$pict = imagecreatetruecolor($w/2, $h/2);
	imagecopyresampled($pict, $pict2x, 0,0, 0,0, $w/2, $h/2, $w, $h);
	$ok1 = imagejpeg($pict, $targetFolder.$targetFileName, 95);
	imagedestroy($pict);

	$ok2 = rename($source, $targetFolder.str_replace('.', '@2x.', $targetFileName));

	return ($ok1&&$ok2);
}

// ===== XML Object-oriented Elements =====
class XMLElement {
	var $beginTag;
	var $content = array();
	var $objects = array();
	var $endTag;
	function string() {
		$s = $this->beginTag;
		for ($i=0; $i<count($this->content); $i++) {
			if ($this->objects[$i]) {
				$s.= $this->content[$i]->string(); }
			else {
				$s.= $this->content[$i]; }
		}
		$s.= $this->endTag;
		return $s;
	}
	public function show() {
		print(self::string());
	}
	public function addObj($element) {
		$this->content[] = $element;
		$this->objects[] = true;
	}
	public function addStr($element) {
		$this->content[] = $element;
		$this->objects[] = false;
	}
}

// ===== <div> element with rounded corners =====
class Box extends XMLElement {
	var $padding = 0;
	function addPadding() {
		$this->beginTag.= '<div style="padding:'.$this->padding.'px;">';
		$this->endTag = '</div>'.$this->endTag;
		$padding = 0;
	}
	function string() {
		if ($this->padding > 0) $this->addPadding();
		return parent::string();
	}
	function partial() {
		if ($this->padding > 0) $this->addPadding();
		$saved_end = $this->endTag;
		$this->endTag = '';
		parent::show();
		$this->beginTag = '';
		$this->content = array();
		$this->endTag = $saved_end;
	}
	public function show() {
		print(self::string());
	}
	function addSeparator($margin=0) {
		$this->addStr('<div class="separator"'.($margin==0?'':'style="margin-top:'.$margin.'px; margin-bottom:'.$margin.'px;"').'></div>');
	}
	function addSection($name, $content) {
		$this->addStr('<hr/><h3>'.$name.'</h3>');
		$this->addStr(''.$content.'<br/>');
	}
}

class ErrorBox extends Box {
	function ErrorBox() {
		$this->padding = 0;
		$this->beginTag = '<div class="msg error">';
		$this->endTag = '</div>';
	}
	function addTitle($content) {
		$this->beginTag.= '<div style="border-bottom:#CC0000; text-align:center; font-weight:bold;">'.$content.'</div>';
	}
}

class OKBox extends Box {
	function OKBox() {
		$this->padding = 0;
		$this->beginTag = '<div class="msg ok">';
		$this->endTag = '</div>';
	}
	function addTitle($content) {
		$this->beginTag.= '<div style="border-bottom:#009900; text-align:center; font-weight:bold;">'.$content.'</div>';
	}
}


function def($var, $defValue='') {
	return (isset($var))?$var:$defValue;
}

function JSTag($code, $debugMode=false) {
	if (!$debugMode) {
		preg_replace('/(^[\/]{2}[^\n]*)¦([\n]{1,}[\/]{2}[^\n]*)/', '', $code);		// Strip comments
		$code = str_replace("\t", '', $code);								// Strip formatting
		$code = str_replace("\n", ' ', $code);								// Strip line breaks
	}
	return '<script type="text/javascript" charset="utf-8"> // <![CDATA[ '."\n".$code."\n".'// ]]></script>';
}

function printJS($code, $debugMode=false) {
	print(JSTag($code, $debugMode));
}

function fetchFile($host, $file) {
	$fp = fsockopen($host, 80);
	if ($fp) {
		$out = 'GET /'.$file." HTTP/1.1\r\n";
		$out .= 'Host: '.$host."\r\n";
		$out .= "Connection: Close\r\n\r\n";
		fwrite($fp, $out);
		$r = '';
		$checkdispHeader = true; // header check flag
		$header_end = 0;
		while (!feof($fp)) {
			$r .= fgets($fp); // reading response
			if ($checkdispHeader) {
				$header_end = strpos($r, "\r\n\r\n");		// HTTP header boundary
				if ($header_end !== false) $checkdispHeader = false;
			}
		}
		fclose($fp);
		if (strpos($r, '<Code>NoSuchKey</Code>')!==false) return false;		// Amazon S3 "No Such Key" files count as Not Found...
		return substr($r, $header_end+4);	 // 4 is length of "\r\n\r\n"
	}
	else return false;
}

function printFile($host, $file) {
	$f = fetchFile($host, $file);
	if ($f !== false) {
		print($f);
		return true;
	}
	else return false;
}

