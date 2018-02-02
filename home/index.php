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
		$perms[] = [
			"team" => "<a href='/home/team'>Team</a>",
			"sessions" => "<a href='/home/sessions'>Manage Sessions</a>",
			"payments" => "<a href='/home/payments'>Manage Payments</a>"
		][$perm];

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
			<li><a href="/home/my_sessions.php">Personal Sessions</a></li>
			<li><a href="/home/my_payments.php">Personal Payments</a></li>
			<li><a href="/src/logout.php">Logout</a></li>
		</ul>
		<?php if (count($perms) > 0): ?>
		<h3>Admin Actions:</h3>

		<ul><?php
			foreach ($perms as $perm)
				printf("<li>%s</li>", $perm);
		?></ul>
		<?php endif ?>
	</body>
</html>
