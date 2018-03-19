<?php
include  "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// Only people with team::edit can delete roles
if (!hasPerms($conn, "team", 2))
	header("Location: http://{$_SERVER['HTTP_HOST']}/permission_denied.php");

// Actually does the deleting
if (isset($_GET['role']))
	q($conn, "DELETE FROM role WHERE role = ?", ['args'=>$_GET['role']]);

// Redirect back
if (isset($_GET['redirect']))
	header("Location: {$_GET['redirect']}");

?>
