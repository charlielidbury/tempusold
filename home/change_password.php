<?php
session_start();

include "../src/db.php";

// makes sure only logged in users get here
if (!isset($_SESSION['user'])) header("Location: ..");

if (isset($_GET['user']))
{
	// if custom user the logged in user must have perms to edit member's details
	$user = $_GET['user'];
	if (!hasPerms($conn, "members", "edit") && $_SESSION['user'] != $_GET['user']) header("Location: ../permission_denied.php");
} else {
	// otherwise just use the logged in user
	$user = $_SESSION['user'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') // login has been pressed
{
	$errors = array();

	// CHECK: PASSWORDS ARE THE SAME
	if ($_POST['new'] !== $_POST['verify'])
		$errors[] = "Passwords must match";

	// ACTUAL PASSWORD CHANGE
	if (!count($errors))
	{
		$new_hash = password_hash($_POST['new'], PASSWORD_DEFAULT);

		$stmt = $conn->prepare("UPDATE `employee` SET `hash` = ? WHERE `name` = ?");
		if (!$stmt) die ("Statement failed to prepare: " . $mysqli->error);

		$stmt->bind_param("ss", $new_hash, $user);
		$stmt->execute();

		header("Location: .");
	}
}
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Change Password</title>
		<link rel="stylesheet" href="styles.css">
	</head>
	<body>
		<h1><a href="/">Tempus</a></h1>
		<h2><a href="change_password.php">Change Password</a></h2>

		<p>Do you see https:// in the top left? No. Nothing about this site is secure. So please use a unique password for it.</p>

		<ul> <?php
			foreach ($errors as $error) echo "<li>$error</li>";
		?> </ul>

		<form action="change_password.php<?php if (isset($_GET['user'])) echo '?user=' . $_GET['user']; ?>" method="POST">
			<table>
				<tr>
					<td>New Password:</td>
					<td><input type="password" name="new" /> <br/></td>
				</tr>
				<tr>
					<td>New Password Verify:</td>
					<td><input type="password" name="verify" /> <br/></td>
				</tr>
			</table>
			<input type="submit" value="Change" name="submit" />
		</form>
	</body>
</html>
