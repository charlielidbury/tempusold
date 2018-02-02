<?php
session_start();

// redirects users who aren't logged in
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}/login.php?redirect={$_SERVER['REQUEST_URI']}");

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// if custom user the logged in user must have perms to edit member's details
if (!hasPerms($conn, "sessions", 2))
	header("Location: {$_SERVER['DOCUMENT_ROOT']}/permission_denied.php");

// ----- SAFE AREA -----
if ($_SERVER['REQUEST_METHOD'] == 'POST') // update has been pressed
{
	// INSERT ROW
	$employees = getColumn($conn, "employee", "name");
	$invites = [];
	$row = [];

	// sorts $_POST into $invites (invite details) and $row (session details)
	unset($_POST['submit']);
	foreach($_POST as $field => $value)
		if (in_array($value, $employees))
			// field is an employee
			$invites[] = $value;
		elseif ($value !== "")
			// field is date/start/end
			$row[$field] = $value;

	// Adds organiser & inserts the session
	$row['organiser'] = $_SESSION['user'];
	insertRow($conn, "session", $row);

	// invites the poeple
	foreach ($invites as $employee)
		insertRow($conn, "invite", [
			"session" => $row['date'],
			"employee" => $employee
		]);

	// Redirects
	if (isset($_GET['redirect']))
		header("Location: {$_GET['redirect']}");
	else
		header("Location: {$_SERVER['HTTP_HOST']}");
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Create Session</title>
		<link rel="stylesheet" href="/css/style.css"/>
	</head>
	<body>
		<h1><a href="/">Tempus</a></h1>
		<h2><a href="/home">Home</a></h2>
		<h3><a href="/home/sessions">Session</a></h3>
		<h3><a href="/home/sessions/create_session.php">Create Session</a></h3>

		<ul> <?php
			foreach ($errors as $error) printf("<li>%s</li>\n", $error);
		?>	</ul>

		<p id="required">Required Fields</p>

		<form action="create_session.php?redirect=<?= $_GET['redirect']; ?>" method="POST">
			<table>
				<tr>
					<td id="required">Date</td>
					<td><input type="date" name="date"></td>
				</tr>
				<tr>
					<td id="required">Start</td>
					<td><input type="time" name="start"></td>
				</tr>
				<tr>
					<td id="required">End</td>
					<td><input type="time" name="end"></td>
				</tr>
			</table>

			<h3>Invite People</h3>
			<?php
			foreach(getColumn($conn, "employee", "name") as $employee)
				printf('<input type="checkbox" name="%1$s" value="%1$s">%1$s<br>', $employee);
			?>

			<input type="submit" value="Create Session" name="submit" />
		</form>
	</body>
</html>
