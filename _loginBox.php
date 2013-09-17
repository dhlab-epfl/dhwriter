<?php

	include_once('_formutils.php');
	echo '<div id="fLogin">';
		beginForm('post');
		 // Nom d'utilisateur; le message d'erreur provient de  _login.php
		 	printTextInput('eMail', 'username', @$_POST['username'], 20);
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
	 	echo '<p><a href="#" class="button" onclick="$(\'#fLogin\').slideUp();$(\'#fReg\').slideDown();">Not Yet a Member?</a></p>';
	echo '</div>';
	echo '<div id="fReg">';
		echo '<form id="signupForm" action="'.$_SERVER['PHP_SELF'].'" method="post">';
			echo '<label for="email">e-Mail</label><input type="text" name="email" id="email" /><br/>';
			echo '<label for="first_name">First Name</label><input type="text" name="first_name" id="first_name" /><br/>';
			echo '<label for="last_name">Last Name</label><input type="text" name="last_name" id="last_name" /><br/>';
			echo '<label for="institution">Institute</label><input type="text" name="institution" id="institution" /><br/>';
			echo '<br/>';
			echo '<br/>';
			echo '<label for="password1">Password</label><input type="password" name="password1" id="password1" /><br/>';
			echo '<label for="password2">Confirm</label><input type="password" name="password2" id="password2" /><br/>';
			echo '<br/>';
#				echo '<div style="margin-left:-1000%;">';
#				printTextInput('', 'captcha', @$_REQUEST['captcha'], 5);
#				echo '</div>';
			echo '<label></label><input class="submit" type="submit" name="signup" value="Submit" />';
		echo '</form>';
	 echo '</div>';