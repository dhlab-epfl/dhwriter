<?php
	define('PAGE', 'reg');

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
	if (isset($_REQUEST['submit'])&&@$_REQUEST['captcha']=='') {
		$datas = array(	'email' => $_REQUEST['email'],
						'username' => $_REQUEST['email'],
						'first_name' => $_REQUEST['first_name'],
						'last_name' => $_REQUEST['last_name'],
						'institution' => $_REQUEST['institution'],
						'password' => md5(PWD_SALT.md5(trim($_REQUEST['password1']))),
						'status' => 'active',
						'account' => 'visitor,author',
						);
		$_SESSION['user_id'] = db_i('users', $datas);
		$accountCreated = true;
	}

	if ($accountCreated) {
		$b = new OKBox();
		$b->addTitle('Registration successful.<br/><br/>You can now start editing your DH abstracts online.');
		$b->show();
	}
	else {
		include('_formutils.php');
		beginForm();
			printTextInput('e-Mail', 'email', @$_REQUEST['email'], 50);
			echo '<br/>';
			echo '<br/>';
			printTextInput('First Name', 'first_name', @$_REQUEST['first_name'], 50);
			echo '<br/>';
			printTextInput('Last Name', 'last_name', @$_REQUEST['last_name'], 50);
			echo '<br/>';
			printTextInput('Institute', 'institution', @$_REQUEST['institution'], 50);
			echo '<br/>';
			echo '<br/>';
			echo '<br/>';
			printPasswordInput('Password', 'password1', '', 15);
			echo '<br/>';
			printPasswordInput('Confirm', 'password2', '', 15);
			echo '<br/>';
			echo '<br/>';
				echo '<div style="margin-left:-1000%;">';
				printTextInput('', 'captcha', @$_REQUEST['captcha'], 5);
				echo '</div>';
			echo '<br/>';
			printSubmitInput('submit', 'Submit', true);
		endForm();
	}
	include_once('_pageend.php');
?>