<?php
define('PAGE', 'citations');
include_once('_session.php');

include_once "oauth-php/library/OAuthStore.php";
include_once "oauth-php/library/OAuthRequester.php";

define('APP_URL', 'http://www.dhwriter.org/');
define("APP_CALLBACK_URL", APP_URL.'citations.php');
define('ZOTERO_APP_KEY', 'c76660e560e22f971571'); //
define('ZOTERO_APP_SECRET', '95fbf43369f410eaf838'); //

define('ZOTERO_OAUTH_HOST', 'https://www.zotero.org');
define('ZOTERO_REQUEST_TOKEN_URL', ZOTERO_OAUTH_HOST . '/oauth/request');
define('ZOTERO_AUTHORIZE_URL', ZOTERO_OAUTH_HOST . '/oauth/authorize');
define('ZOTERO_ACCESS_TOKEN_URL', ZOTERO_OAUTH_HOST . '/oauth/access');
define('ZOTERO_API_HOST', 'https://api.zotero.org');

define('OAUTH_TMP_DIR', function_exists('sys_get_temp_dir') ? sys_get_temp_dir() : realpath($_ENV["TMP"]));



$oauthOptions = array(
	'consumer_key' => ZOTERO_APP_KEY,
	'consumer_secret' => ZOTERO_APP_SECRET,
	'server_uri' => ZOTERO_API_HOST,
	'request_token_uri' => ZOTERO_REQUEST_TOKEN_URL,
	'authorize_uri' => ZOTERO_AUTHORIZE_URL,
	'access_token_uri' => ZOTERO_ACCESS_TOKEN_URL,
	'server' => '127.0.0.1',
	'username' => 'dhwriter',
	'password' => 'gcd2386*',
	'database' => 'dhwriter',
);
OAuthStore::instance('MySQL', $oauthOptions);


// =============================================================================================================================================================


function oauth($oauthOptions) {
	// Note: do not use "Session" storage in production. Prefer a database
	// storage, such as MySQL.
	$store = OAuthStore::instance();
	// The server description
	$serverOptions = array(
		'consumer_key' => ZOTERO_APP_KEY,
		'consumer_secret' => ZOTERO_APP_SECRET,
		'server_uri' => ZOTERO_API_HOST,
		'signature_methods' => array('HMAC-SHA1', 'PLAINTEXT'),
		'request_token_uri' => ZOTERO_REQUEST_TOKEN_URL,
		'authorize_uri' => ZOTERO_AUTHORIZE_URL,
		'access_token_uri' => ZOTERO_ACCESS_TOKEN_URL,
	);

	$existing = $store->listServers('', $_SESSION['user_id']);
	if (count($existing)>0) {
		$consumer_key = $existing[0]['consumer_key'];
	}
	else {
		// Save the server in the the OAuthStore
		$consumer_key = $store->updateServer($serverOptions, $_SESSION['user_id']);
	}
	try {
		//  STEP 1:  If we do not have an OAuth token yet, go get one
		if (empty($_GET['oauth_token'])) {
			$getAuthTokenParams = array('oauth_callback' => APP_CALLBACK_URL.'?consumer_key='.rawurlencode($consumer_key).'&user_id='.intval($_SESSION['user_id']));

			// get a request token
			$tokenResultParams = OAuthRequester::requestRequestToken($consumer_key, $_SESSION['user_id'], $getAuthTokenParams);

			//  redirect to the google authorization page, they will redirect back
			header('Location: '.ZOTERO_AUTHORIZE_URL.'?oauth_token='.$tokenResultParams['token']);
		}
		else {
			//  STEP 2:  Get an access token

			$tokenResultParams = $_GET;
			$oauthToken = $_GET['oauth_token'];
			$user_id = $_GET['user_id'];
			$consumer_key = $_GET['consumer_key'];

			try {
			    $accessToken = OAuthRequester::requestAccessToken($consumer_key, $oauthToken, $user_id, 'POST', $_GET);
			}
			catch (OAuthException2 $e) {
				var_dump($e);
			    // Something wrong with the oauth_token.
			    // Could be:
			    // 1. Was already ok
			    // 2. We were not authorized
			    return;
			}

			$accessToken = $store->getSecretsForSignature(ZOTERO_API_HOST, $user_id);

			$_SESSION['zoteroTokenResultParams'] = $tokenResultParams;
			$_SESSION['zoteroAccessToken'] = $store->getServerTokenSecrets($consumer_key, $accessToken['token'], 'access', $user_id);
			print_r($_SESSION['zoteroAccessToken']);

	#		$zoteroUserID = $oauthToken['userID'];
	#		$zoteroApiKey = $oauthToken['oauth_token_secret'];

		}
	}
	catch(OAuthException2 $e) {
		echo "OAuthException:  " . $e->getMessage();
		var_dump($e);
	}
}


// =============================================================================================================================================================

if (isset($_GET['doLogin'])||isset($_GET['oauth_token'])) {
	oauth($oauthOptions);
}

if (isset($_SESSION['zoteroAccessToken'])) {
	$request = new OAuthRequester(ZOTERO_API_HOST.'/users/'.$_SESSION['zoteroAccessToken']['token_userid'].'/items?format=versions&key='.$_SESSION['zoteroAccessToken']['token_secret'], 'GET', $_SESSION['zoteroTokenResultParams']);
	$result = $request->doRequest($_SESSION['user_id']);
	if ($result['code'] == 200) {
		include('_pageprefix.php');
		echo '<article>';
			echo '<h1>My Library</h1>';
			$keys = json_decode($result['body'], true);
			foreach ($keys as $key => $version) {
				$request = new OAuthRequester(ZOTERO_API_HOST.'/users/'.$_SESSION['zoteroAccessToken']['token_userid'].'/items/'.$key.'?content=json&key='.$_SESSION['zoteroAccessToken']['token_secret'], 'GET', $_SESSION['zoteroTokenResultParams']);
				$result = $request->doRequest($_SESSION['user_id']);
			    $doc = new DOMDocument();
			    $doc->loadXml($result['body']);
			    $r = $doc->getElementsByTagName("content")->item(0)->nodeValue;
				$item = json_decode($r, true);
				echo '<br/>'.$item['title'].'<br/>';
			}
		echo '</article>';
		include('_pageend.php');
	}
	else {
		// oauth session expired (or something) : auto log-in
		unset($_SESSION['zoteroAccessToken']);
		oauth($oauthOptions);
	}
}
else {
	// No existing session : display login link
	include('_pageprefix.php');
	echo '<a href="?doLogin=">Connect to Zotero</a>';
	include('_pageend.php');
}

?>