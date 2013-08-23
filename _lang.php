<?php

	// ===== LANGUAGE SELECTOR ================================================================================

	$avail_languages = array('en');
	if (isset($_GET['hl'])&&in_array(strtolower($_GET['hl']), $avail_languages)) {
		$_SESSION['lang'] = strtolower($_GET['hl']);
		setcookie('lang', strtolower($_GET['hl']), time()+3600*24*365);
	}
	elseif (!isset($_SESSION['lang'])) {
		if (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], $avail_languages)) {
			$_SESSION['lang'] = $_COOKIE['lang'];
		}
		else {
			$_SESSION['lang'] = $avail_languages[0];
		}
	}

	$loc_content = db_x('SELECT id, str FROM localizable WHERE lang="'.$_SESSION['lang'].'";');
	while ($locstr = db_fetch($loc_content)) {
		$GLOBALS['localized_strings'][$locstr['id']] = $locstr['str'];
	}