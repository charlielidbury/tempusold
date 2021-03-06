<?php

session_start();

// redirects users who aren't logged in
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}/login.php?redirect={$_SERVER['REQUEST_URI']}");

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// if custom user the logged in user must have perms to edit member's details
if (!hasPerms($conn, "sessions", 2))
	header("Location: {$_SERVER['HTTP_HOST']}/permission_denied.php");

// ACTUALLY DELETES THE ROW AND CHANNEL
if (isset($_GET['session'])) {
	deleteRow($conn, "session", ["date" => $_GET['session']]);
	discoBot("deleteChannel", $_GET['session']);
}

// redirect back
if (isset($_GET['redirect']))
	header("Location: {$_GET['redirect']}");

?>
