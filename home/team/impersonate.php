<?php

session_start();

include  "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

if (!hasPerms($conn, "team", 2))
	header("Location: http://{$_SERVER['HTTP_HOST']}/permission_denied.php");

if (isset($_GET['user'])) {
	$_SESSION['real_identity'] = $_SESSION['user'];
	$_SESSION['user'] = $_GET['user'];
}

header("Location: http://{$_SERVER['HTTP_HOST']}");

?>
