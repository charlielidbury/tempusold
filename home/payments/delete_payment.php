<?php
session_start();
include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// redirects users without payments perms
if (!hasPerms($conn, "payments", 2))
	header("Location: http://{$_SERVER['HTTP_HOST']}/permission_denied.php");

// does the deleting
if (isset($_GET['payment']))
	deleteRow($conn, "payment", [
		"id" => $_GET['payment']
	]);

// redirects if is set
if (isset($_GET['redirect']))
	header("Location: {$_GET['redirect']}");

?>
