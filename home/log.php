<?php
session_start();

// redirects users who aren't logged in
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}/login.php?redirect={$_SERVER['REQUEST_URI']}");

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

q($conn, "CALL toggleLog(?)", ['args'=>$_SESSION['user']]);

if (isset($_GET['redirect']))
	header("Location: {$_GET['redirect']}");

?>
