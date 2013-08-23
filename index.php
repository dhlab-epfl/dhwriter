<?php
	define('PAGE', 'index');

	// General errors display
	if (isset($_GET['error'])) {
		define('PAGE', 'error');
		$base = 'http://www.dhwriter.org';
		include_once('_pageprefix.php');
		include('_error.php');
		die();
	}
	else {
		include_once('_pageprefix.php');
	}

	echo '<aside class="loginPanel">';
	include('_loginBox.php');
	echo '</aside>';

	echo '<article>';
		echo '<h1>Welcome to DHwriter!</h1>';
		echo '<p>Aliquipsum corero felis iliquipisl tionulla, rilisit dio iliquat acipisl mus hent augiat nonsequam auguer. Aliquipsum coreetue elesto, irilla lutat lan luptatum illaor vullam, quamet nostissi do. Enibh hent nummolorem, et dignissequis nullam exer, alisi iliquat loborper auguero. Quatums lamconullaor porta feui.</p>';
		echo '<p></p>';
	echo '</article>';

	include_once('_pageend.php');
?>