<?php
session_start();

// makes sure only logged in users get here
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}/login.php?redirect={$_SERVER['REQUEST_URI']}");

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// if custom user the logged in user must have perms to edit payment's details
die(hasPerms($conn, ))
if (!hasPerms($conn, "payments", 2))
	header("Location: http://{$_SERVER['HTTP_HOST']}/permission_denied.php");

// ----- SAFE AREA -----

if ($_SERVER['REQUEST_METHOD'] == 'POST') // update has been pressed
{
	// INSERTS ROW INTO `PAYMENT`
	$date = $_POST['date'];
	unset($_POST['date']);
	unset($_POST['submit']);

	// gets selected shifts
	$shifts = array_keys($_POST);
	$shifts = implode(",", $shifts);

	// calculates total
	$total = array_sum(array_map("intval", array_values($_POST)));

	insertRow($conn, "payment", [
		"date" => $date,
		"amount" => $total,
		"payer" => $_SESSION['user'],
		"payee" => $_GET['user']
	]);

	// UPDATES CELLS IN `SHIFT`
	q($conn, "UPDATE `shift`
	SET `wage` = LAST_INSERT_ID()
	WHERE `employee` = ?
	AND `date` IN ('$shifts')", "s", $_GET['user']);

	header("Location: {$_GET['redirect']}");
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Create Payment</title>
		<link rel="stylesheet" href="/css/style.css"/>
	</head>
	<body>
		<h1><a href="/">Tempus</a></h1>
		<h2><a href="/home">Home</a></h2>
		<h3><a href="/home/payments">Payments</a></h3>
		<h3><a href="<?="http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"?>">Create Payment</a></h3>

		<ul> <?php
			foreach ($errors as $error) printf("<li>%s</li>\n", $error);
		?>	</ul>

		<p id="required">Required Fields</p>

		<form action="<?="http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"?>" method="POST">
			<table>
				<tr>
					<td id="required">Date</td>
					<td><input type="date" name="date" value="<?= date("Y-m-d") ?>"></td>
				</tr>
			</table>
			<?= table2HTML($conn, "CALL paymentShifts(?)", "s", $_GET['user']); ?>
			<input type="submit" value="Create Payment" name="submit" />
		</form>
	</body>
</html>
