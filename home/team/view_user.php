<?php
session_start();

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// makes sure $_GET['user'] is set
if (!isset($_GET['user']))
	die("User not set in GET variables");

// redirects users who aren't logged in
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}/login.php?redirect={$_SERVER['REQUEST_URI']}");

// makes sure only people with correct perms can see the details
if ( (!hasPerms($conn, "team", 1)) && $_SESSION['user'] != $_GET['user'])
	header("Location: http://{$_SERVER['HTTP_HOST']}/permission_denied.php");

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Profile</title>
		<link rel="stylesheet" href="/css/style.css"/>
	</head>
	<body>
		<pre><?php //die(var_export($_SESSION['user_data'], true)) ?></pre>
		<h1><a href="/">Tempus</a></h1>
		<h2><a href="/home">Home</a></h2>
		<h3><a href="/home/team">Team</a></h3>
		<h3><a href="/home/team/view_user.php?user=<?= $_GET['user']; ?>">View User</a></h3>
		<p>Profile details:</p>
		<ul>
			<li><a href="<?= "change_password.php?user={$_GET['user']}&redirect=http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}" ?>">Change Password</a></li>
		</ul>
		<?php
		// adds permissions to the table
		$extra = '<tr><td>Permissions</td><td>';
		$extra .= row2HTML($conn, "role", "role", getCell($conn, "role", "employee", "name", $_GET['user']));
		$extra .= '</td></tr>';
		echo row2HTML($conn, "view_employee", "name", $_GET['user'], $extra);
		?>
	</body>
</html>
