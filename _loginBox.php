<?php

	include_once('_formutils.php');
	beginForm('post', 'papers.php');
	 // Nom d'utilisateur; le message d'erreur provient de  _login.php
	 	printTextInput('User', 'username', @$_POST['username'], 20);
	 	echo '<br/>';
		if (isset($err_login_no_username)) { echo '<div class="error" style="text-align:center;margin:10px;">'.$err_login_no_username.'</div>'; }
	 // Mot de passe; le message d'erreur provient de  _login.php
	 	printPasswordInput('Password', 'password', '', 20);
		if (isset($err_login_no_password)) { echo '<div class="error" style="text-align:center;margin:10px;">'.$err_login_no_password.'</div>'; }
	 // Message d'erreur de login
	 	if (isset($err_login)) { echo '<div class="error" style="text-align:center;margin:10px;">'.$err_login.'</div>'; }
	 // Bouton d'envoi du formulaire
	 	echo '<br/>';
	 	printSubmitInput('processLogin', 'Login', true);
	endForm();
 	echo '<p><a href="reg.php" class="button">Not Yet a Member?</a></p>';
