<?php
session_start();

// Checks if logged on
if (!isset($_SESSION['user'])) header("Location: {$_SERVER['DOCUMENT_ROOT']}");

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

$row = getRow("role", "role", getCell("role", "employee", "name", "Charlie"));
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
		<h3>Logged in as <?= $_SESSION["user"]; ?></h3>
		<ul>
			<li><a href="/home/team/view_user.php?user=<?= $_SESSION['user']; ?>">Profile</a></li>
			<li><a href="/home/my_sessions.php">Sessions</a></li>
			<li><a href="/home/payments/">Payments</a></li>
			<li><a href="/src/logout.php">Logout</a></li>
		</ul>
		<?php if (count($perms) > 0): ?>
		<h3>Admin Actions:</h3>
		<ul>
			<?php
			foreach ($perms as $perm)
				printf("<li><a href='/home/%s'>%s</a></li>", $perm, ucwords($perm));
			?>
		</ul>
		<?php endif ?>
	</body>
</html>
