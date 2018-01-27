<?php

session_start();

// redirects users who aren't logged in
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}/login.php?redirect={$_SERVER['REQUEST_URI']}");

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// if custom user the logged in user must have perms to edit member's details
if (!hasPerms("team", 2) && $_SESSION['user'] != $_GET['user'])
	header("Location: {$_SERVER['HTTP_HOST']}/permission_denied.php");

// ACTUALLY DELETES THE ROW
if (isset($_GET['user']))
	deleteRow("employee", ["name" => $_GET['user']]);

// redirect back
if (isset($_GET['redirect']))
	header("Location: {$_GET['redirect']}");

?>
