<?php
/* _session
 * Script à inclure au début d'un fichier pour le sécuriser; ne pas oublier d'inclure également "_login.php" là où se situera le login
 * Version 1.1
 * Copyright (c) 2006-2009 Cyril Bornet, all rights reserved
 */

session_start();
include_once('_structure.php');
include_once('_db/_db.php');

$GLOBALS['SESSION_STATUS_OK'] = 0;
$GLOBALS['SESSION_STATUS_EXPIRED'] = 1;
$GLOBALS['SESSION_STATUS_DENIED'] = 2;

$drawLogin = true;

if (isset($_GET['logout'])) {
	if ($_GET['logout']==session_id()) sessionLogout();
}

if (!isset($grant_access)) { $grant_access = array('visitor'); }
if (isset($_POST['processLogin'])) {							 											//  Si un formulaire de login a été utilisé,
	if (trim($_POST['username']) == '') {																	//  \
		$err_login_no_username = "You didn't type in any login name!"; }									//   si le visiteur a oublié d'indiquer quelque chose,
	elseif (!isset($password_crypted) && trim($_POST['password']) == '') {									//   on stocke l'erreur en mémoire
		$err_login_no_password = "You didn't type in any password!"; }										//  /
	else {																									//
		$username = db_escape(strtolower(trim($_POST['username'])));								//
		if (isset($password_crypted)) {																		//
			$password = trim($password_crypted); }															//  Needed for auto-login
		else {
			$password = md5(PWD_SALT.md5(trim($_POST['password']))); }															//
		$access = implode('%" OR account LIKE "%', $grant_access);
		$content_user = db_x('SELECT * FROM users WHERE username="'.$username.'" AND password="'.$password.'" AND (account LIKE "%'.$access.'%");');
		if (db_count($content_user) > 0) {															//  Authentification du visiteur
			$user = db_fetch($content_user);
			$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];														// 	Stocke l'adresse IP du visiteur dans la session
			$_SESSION['browser'] = $_SERVER['HTTP_USER_AGENT'];												//  Stocke la description du navigateur du visiteur
			$_SESSION['user_id'] = $user['id'];																//  Stocke le user-id pour un usage ultérieur (logs,...)
			$_SESSION['user_granted'] = explode(',', $user['account']);										//
			$_SESSION['account_status'] = $user['status'];													//
			$_SESSION['last_doc'] = db_fetch(db_s('papers', array('user_id' => $_SESSION['user_id']), array('date_updated' => 'DESC')));
			db_x('UPDATE users SET date_last_login="'.date('Y-m-d H:i:s').'" WHERE id="'.$user['id'].'";');
		}																									//  Comme la session est maintenant ouverte, le reste sera traité plus bas...
		else {																								//
			$content_test = db_x('SELECT * FROM users WHERE username="'.$username.'";');
			if (db_count($content_test) == 0) {
				$err_login = "This login name does not exist.<br/>Hint: use your e-mail address.";
			}
			else {
				$err_login = "Wrong password.";
			}
		}																									//
	}
}
if (isset($_SESSION['user_id'])) {												//  Si le visiteur a une session ouverte,
	$session_status = checkSession($grant_access);
	$drawLogin = ($session_status == $GLOBALS['SESSION_STATUS_DENIED']);
	if (isset($_REQUEST['terms'])) {
		db_u('users', $_SESSION['user_id'], array('status' => 'active'));
		$_SESSION['account_status'] = 'active';
	}
}

// Vérifie si la session est valide
function checkSession($grant) {
	if (!isset($_SESSION['ip']) || !isset($_SESSION['browser'])) { sessionExpired(); return $GLOBALS['SESSION_STATUS_EXPIRED']; }	// Session invalide si l'IP ou la description du navigateur ne sont pas stockés à cette étape
	else {																								//
		$stored_ip = $_SESSION['ip'];																	// \
		$actual_ip = $_SERVER['REMOTE_ADDR'];															//  Si le visiteur a subitement changé d'IP, on peut douter de la validité de la session...
#		if ($stored_ip != $actual_ip) { sessionExpired(); return $GLOBALS['SESSION_STATUS_EXPIRED']; }								// /
		$stored_browser = $_SESSION['browser'];															// \
		$actual_browser = $_SERVER['HTTP_USER_AGENT'];													//  Même chose le visiteur a changé de navigateur
		if ($stored_browser != $actual_browser) { sessionExpired(); return $GLOBALS['SESSION_STATUS_EXPIRED']; }						// /
	}
	if (sessionAllows($grant)&&$_SESSION['account_status']!='disabled') {																			// Dans tous les autres cas, vérifie qu'on ait suffisamment de privilèges
		return $GLOBALS['SESSION_STATUS_OK'];
	}
	else {
		return $GLOBALS['SESSION_STATUS_DENIED'];
	}
}

function sessionLogout() {
	$lang = $_SESSION['lang'];
	session_destroy();													// Ferme la session
	unset($_SESSION);													// Détruit les variables encore en mémoire
	session_start();													// Démarre une nouvelle session
	$_SESSION['lang'] = $lang;
}

// Ferme la session actuelle pour des raisons de sécurité
function sessionExpired() {
	sessionLogout();
	alert('Your session ended.');										// Affiche un message d'erreur
#	print('<a href="support.php?tab=login">Go to login page</a>');		// |
	include('_pageend.php');											// Dessine le bas de page
	exit;																// Et on arrête tout...
}

function sessionAllows($grant) {
	return isset($_SESSION['user_granted'])&&!(count(array_intersect($grant, $_SESSION['user_granted'])) == 0);
}

?>