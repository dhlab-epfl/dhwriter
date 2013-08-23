<?php
	define('PAGE', 'account');

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

	$accountCreated = false;
	if (isset($_REQUEST['save'])) {
		$datas = array(	'email' => $_REQUEST['email'],
						'username' => $_REQUEST['email'],
						'first_name' => $_REQUEST['first_name'],
						'last_name' => $_REQUEST['last_name'],
						'institution' => $_REQUEST['institution'],
						'status' => 'active',
						'account' => 'visitor,author',
						);
		if ($_REQUEST['password1']!='' && $_REQUEST['password1']==$_REQUEST['password2']) {
			$datas['password'] = md5(PWD_SALT.md5(trim($_REQUEST['password1'])));
		}
		db_u('users', array('id' => $_SESSION['user_id']), $datas);
	}

	include('_formutils.php');
	$account = db_fetch(db_s('users', array('id' => $_SESSION['user_id'])));
	beginForm();
		printTextInput('e-Mail', 'email', @$account['email'], 50);
		echo '<br/>';
		echo '<br/>';
		printTextInput('First Name', 'first_name', @$account['first_name'], 50);
		echo '<br/>';
		printTextInput('Last Name', 'last_name', @$account['last_name'], 50);
		echo '<br/>';
		printTextInput('Institute', 'institution', @$account['institution'], 50);
		echo '<br/>';
		echo '<br/>';
		echo '<br/>';
		printPasswordInput('Password', 'password1', '', 15);
		echo '<br/>';
		printPasswordInput('Confirm', 'password2', '', 15);
		echo '<br/>';
		echo '<br/>';
			echo '<div style="margin-left:-1000%;">';
			printTextInput('', 'captcha', @$account['captcha'], 5);
			echo '</div>';
		echo '<br/>';
		printSubmitInput('save', 'Submit', true);
	endForm();
	include_once('_pageend.php');
?>