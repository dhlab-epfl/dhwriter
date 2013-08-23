<?php


if ($drawLogin) {
	echo '<div id="loginModal" class="loginPanel">';
	include('_loginBox.php');
	echo '</div>';
	include('_pageend.php');
	exit;
}

?>