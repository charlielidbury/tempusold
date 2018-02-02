<?php
session_start();

// redirects users who aren't logged in
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}/login.php?redirect={$_SERVER['REQUEST_URI']}");

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
	foreach ($_POST as $reply => $session) // misleading loop, will only ever go around once
		updateRow($conn, "invite", [
			"employee" => $_SESSION['user'],
			"session" => $session
		], [
			"accepted" => ($reply == 'accept') ? "1" : "0",
			"received" => "2018-02-02 23:08:39"
		]);
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Inbox</title>
		<link rel="stylesheet" href="/css/style.css">
	</head>
	<body>
		<h1><a href="/">Tempus</a></h1>
		<h2><a href="/home/">Home</a></h2>
		<h3><a href="/home/inbox.php">Inbox</a></h3>

		<form action="<?="http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"?>" method="post">
			<?php table2HTML($conn, "CALL userInvites(?)", "s", $_SESSION['user']); ?>
		</form>
	</body>
</html>
