<?php

/*  -- DHWRITER.ORG PROJECT --
 *
 *  Description :   Prefix page to be included at the beginning of each generated XHTML page
 *
 *  Created :       2013-08-12, by Cyril Bornet [cyril dot bornet at gmail dot com]
 *  Last modified : -
 *
 */

// ===== SCRIPT TIMING ====================================================================================
$time_start = microtime(true);

// ===== INCLUDED LIBRARIES ===============================================================================
date_default_timezone_set('Europe/Zurich');
include_once('_session.php');
include_once('_phptoolbox.php');
include_once('_db/_db.php');

define('DATA_PATH', '/data/');

// ===== LANGUAGE SELECTOR ================================================================================
include('_lang.php');


// ===== DOCUMENT INFORMATIONS ============================================================================
header('Vary: Accept');
header('Content-Type: text/html; charset=utf-8');

echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$_SESSION['lang'].'">';

// ===== DOCUMENT HEADERS =================================================================================

echo '<head>';
	// === Metas ===
	if (isset($base)) { echo '<base href="'.$base.'" />'; }
	echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
	echo '<meta http-equiv="content-language" content="'.$_SESSION['lang'].'" />';
#	echo '<meta http-equiv="pragma" content="no-cache" />';
	if (isset($GLOBALS['page_title'])) {
		$title = readSetting('site_title').' - '.$GLOBALS['page_title']; }
	else {
		if (locStr('menu_'.PAGE)!='') {
			$GLOBALS['page_title'] = locStr('menu_'.PAGE);
			$title = readSetting('site_title').' - '.$GLOBALS['page_title']; }
		else {
			$GLOBALS['page_title'] = '';
			$title = readSetting('site_title'); }
	}
	echo '<title>'.$title.'</title>';
#	echo '<meta name="description" content="" />';
#	echo '<meta name="keywords" content="" />';
#	echo '<meta name="owner" content="" />';
	// === Global Style, used for common elements to all skins ===
	echo '<link rel="stylesheet" type="text/css" media="screen" href="/s/dhwriter.css" />';
	echo '<link rel="stylesheet" type="text/css" media="screen" href="http://fonts.googleapis.com/css?family=Oswald&subset=latin%2Clatin-ext&ver=3.7-alpha-25000" />';
	if (is_array(@$GLOBALS['s'])) foreach ($GLOBALS['s'] as $s) {
		if (substr($s, 0, 1)=='<') echo $s;
		else echo '<link rel="stylesheet" media="all" type="text/css" href="/s/'.$s.'" />';
	}
	echo '<script src="/Aloha-Editor/oerpub/js/jquery-1.7.1.min.js"></script><script src="/Aloha-Editor/oerpub/js/jquery-ui-1.9.0.custom-aloha.js"></script>';	// Not needed here actually, act as a preloader
	@$GLOBALS['js'][] = 'retina.min.js';
	if (is_array($GLOBALS['js'])) foreach (@$GLOBALS['js'] as $js) {
		if (substr($js, 0, 1)=='<') echo $js;
		else echo '<script src="/js/'.$js.'" type="text/javascript"></script>';
	}
	echo '<!--[if lt IE 9]><script src="/js/html5shiv.min.js" type="text/javascript"></script><![endif]-->';
echo '</head>';


// ===== DOCUMENT BODY ====================================================================================
echo '<body id="'.PAGE.'">';

	// Inner frame ___________________________________________________
	echo '<header>';
		echo '<a href="/"><img src="/i/logo-dhwriter.png" alt="DHwriter.org" id="logo" /></a>';
		echo '<nav>';
			echo '<a href="/"'.(PAGE=='index'?' class="current"':'').'>Home</a>';
			if (sessionAllows(array('author'))) {
				echo '<a href="papers.php"'.(PAGE=='papers'?' class="current"':'').'>Papers</a>';
				echo '<a href="citations.php"'.(PAGE=='citations'?' class="current"':'').'>Research Sources</a>';
				echo '<a href="account.php"'.(PAGE=='account'?' class="current"':'').'>My Account</a>';
				echo '<a href="?logout='.session_id().'">Logout</a>';
			}
			else {
				echo '<a href="papers.php">Login</a>';
			}
		echo '</nav>';
	echo '</header>';


	// Page content __________________________________________________
	echo '<section>';

?>