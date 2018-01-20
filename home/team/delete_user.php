<?php

session_start();

// makes sure only logged in users get here
if (!isset($_SESSION['user']))
	header("Location: {$_SERVER['HTTP_HOST']}");

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// if custom user the logged in user must have perms to edit member's details
$user = $_GET['user'];
<<<<<<< HEAD
if (!hasPerms("team", 2) && $_SESSION['user'] != $_GET['user'])
=======
if (!hasPerms($conn, "members", "edit") && $_SESSION['user'] != $_GET['user'])
>>>>>>> parent of 8f19b50... Added viewing, editing and adding employees to sessions. Woot!
	header("Location: {$_SERVER['HTTP_HOST']}/permission_denied.php");

// ACTUALLY DELETES THE ROW
if (isset($_GET['user']))
<<<<<<< HEAD
	deleteRow("employee", ["name" => $_GET['user']]);
=======
	deleteRow($conn, "employee", "name", $_GET['user']);
>>>>>>> parent of 8f19b50... Added viewing, editing and adding employees to sessions. Woot!

// redirect back
if (isset($_GET['redirect']))
	header("Location: {$_GET['redirect']}");

?>
