<?php
/*  GOOGLE LOGIN BASIC - Tutorial
 *  file            - index.php
 *  Developer       - Krishna Teja G S
 *  Website         - http://packetcode.com/apps/google-login/
 *  Date            - 28th Aug 2015
 *  license         - GNU General Public License version 2 or later
*/
// REQUIREMENTS - PHP v5.3 or later
// Note: The PHP client library requires that PHP has curl extensions configured.
/*
 * DEFINITIONS
 *
 * load the autoload file
 * define the constants client id,secret and redirect url
 * start the session
 */
require_once __DIR__.'/gplus-lib/vendor/autoload.php';
const CLIENT_ID = '182963280993-nklm7e68a1j1ulv4m90ni8ks814ebe88.apps.googleusercontent.com';
const CLIENT_SECRET = 'ArpMkOh9thmgcJYvuM0L2Aqj';
const REDIRECT_URI = 'http://www.google.com';
session_start();
/*
 * INITIALIZATION
 *
 * Create a google client object
 * set the id,secret and redirect uri
 * set the scope variables if required
 * create google plus object
 */
$client = new Google_Client();
$client->setClientId(CLIENT_ID);
$client->setClientSecret(CLIENT_SECRET);
$client->setRedirectUri(REDIRECT_URI);
$client->setScopes('email');
$plus = new Google_Service_Plus($client);
/*
 * PROCESS
 *
 * A. Pre-check for logout
 * B. Authentication and Access token
 * C. Retrive Data
 */
/*
 * A. PRE-CHECK FOR LOGOUT
 *
 * Unset the session variable in order to logout if already logged in
 */
if (isset($_REQUEST['logout'])) {
   session_unset();
}
/*
 * B. AUTHORIZATION AND ACCESS TOKEN
 *
 * If the request is a return url from the google server then
 *  1. authenticate code
 *  2. get the access token and store in session
 *  3. redirect to same url to eleminate the url varaibles sent by google
 */
if (isset($_GET['code'])) {
  $client->authenticate($_GET['code']);
  $_SESSION['access_token'] = $client->getAccessToken();
  $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}
/*
 * C. RETRIVE DATA
 *
 * If access token if available in session
 * load it to the client object and access the required profile data
 */
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
  $me = $plus->people->get('me');
  // Get User data
  $id = $me['id'];
  $name =  $me['displayName'];
  $email =  $me['emails'][0]['value'];
  $profile_image_url = $me['image']['url'];
  $cover_image_url = $me['cover']['coverPhoto']['url'];
  $profile_url = $me['url'];
} else {
  // get the login url
  $authUrl = $client->createAuthUrl();
}
?>

<!-- HTML CODE with Embeded PHP-->
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus Login</title>
	</head>
	<body>
		<div>
		    <?php
		    /*
		     * If login url is there then display login button
		     * else print the retieved data
		    */
		    if (isset($authUrl)) {
		        echo "<a class='login' href='" . $authUrl . "'><img src='gplus-lib/signin_button.png' height='50px'/></a>";
		    } else {
		        print "ID: {$id} <br>";
		        print "Name: {$name} <br>";
		        print "Email: {$email } <br>";
		        print "Image : {$profile_image_url} <br>";
		        print "Cover  :{$cover_image_url} <br>";
		        print "Url: {$profile_url} <br><br>";
		        echo "<a class='logout' href='?logout'><button>Logout</button></a>";
		    }
		    ?>
		</div>
	</body>
</html>
