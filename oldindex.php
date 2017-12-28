<?php
	// Defining
	require_once __DIR__.'/google/vendor/autoload.php';

	const CLIENT_ID = '182963280993-nklm7e68a1j1ulv4m90ni8ks814ebe88.apps.googleusercontent.com';
	const CLIENT_SECRET = 'ArpMkOh9thmgcJYvuM0L2Aqj';
	const REDIRECT_URI = 'localhost';

	session_start();

	// Initialisation
	$client = new Google_Client();
	$client->setClientId(CLIENT_ID);
	$client->setClientSecret(CLIENT_SECRET);
	$client->setRedirectUri(REDIRECT_URI);
	$client->setScopes('email');

	$plus = new Google_Service_Plus($client);

	// Actual Process

	if (isset($_REQUEST['logout']))
	{
		SESSION_UNSET();
	}

	if (isset($_get['code']))
	{
		$client->authenticate($_GET['code']);
		$_SESSION['access_token'] = $client->getAccessToken();
		$redirect='http://'.$_server['HTTP_HOST'].$_SERVER['PHP_SELF'];
		header('Location:'.filter_var($redirect,FILTER_SANITIZE_URL));
	}

	if (isset($_SESSION['access_token']))
	{
		$client->setAccessToken($_SESSION['access_token']);
		$me = $plus->people>get('me');

		$id = $me['id'];
		$name = $me['displayName'];
		$email = $me['emails'][0]['value'];
		$profile_image_url = $me['image']['url'];
		$cover_image_url = $me['cover']['coverPhoto']['url'];
		$profile_url = $me['url'];

	} else {
		$authUrl = $client->creatAuthUrl();
	}

 ?>

 <!-- MAIN HTML CODE W/ PHP -->
<!DOCTYPE html>
<html>
 	<head>
		<title>Tempus</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Bootstrap -->
		<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">

		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    	<!--[if lt IE 9]>
      	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      	<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    	<![endif]-->
  	</head>
	<body>

		<?php
			//if login url is there display login button
			//else print the retrieved data

			if (isset($authUrl))
			{
				echo "<a class='login' href'".$authUrl."'><img src='/google/signin_button.png' height='50px'/></a>";
			}
			else
			{
				print "ID: {$id} <br>";
				print "Name: {$name} <br>";
				print "Email: {$email} <br>";
				print "Image: {$profile_image_url} <br>";
				print "Cover: {$$cover_image_url} <br>";
				print "Url: {$profile_url} <br> <br>";
				echo "<a class='logout' href='?logout'><button>Logout</button></a>";
			}
		 ?>

    	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    	<script src="https://code.jquery.com/jquery.js"></script>
    	<!-- Include all compiled plugins (below), or include individual files as needed -->
    	<script src="js/bootstrap.min.js"></script>
  	</body>
</html>
