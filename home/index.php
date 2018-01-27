<?php
session_start();

// redirects users who aren't logged in
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}/login.php?redirect={$_SERVER['REQUEST_URI']}");

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

$row = getRow($conn, "role", "role", getCell($conn, "role", "employee", "name", $_SESSION['user']));
unset($row['role']);

$perms = [];
foreach ($row as $perm => $level)
	if ($level >= 1)
		$perms[] = $perm;

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Home</title>
		<link rel="stylesheet" href="../css/style.css">
	</head>
	<body>
		<h1><a href="/">Tempus</a></h1>
		<h2><a href="/home/">Home</a></h2>
		<h3>Logged in as <?= $_SESSION["user"]; ?>:</h3>
		<ul>
			<li><a href="/home/team/view_user.php?user=<?= $_SESSION['user']; ?>">Profile</a></li>
			<li><a href="/home/my_sessions.php">Sessions</a></li>
			<li><a href="/home/payments/">Payments</a></li>
			<li><a href="/src/logout.php">Logout</a></li>
		</ul>
		<?php if (count($perms) > 0): ?>
		<h3>Admin Actions:</h3>
		<ul><?php
			foreach ($perms as $perm)
				printf("<li><a href='/home/%s'>%s</a></li>", $perm, ucwords($perm));
		?></ul>
		<?php endif ?>
	</body>
</html>
