<?php
session_start();

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// redirects users who aren't logged in
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}/login.php?redirect={$_SERVER['REQUEST_URI']}");

// redirects users who aren't allowed to see payments
if (!hasPerms($conn, "payments", 1))
	header("Location: http://{$_SERVER['HTTP_HOST']}/permission_denied.php");

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
	// makes sure only users with payments::edit past this point
	if (!hasPerms($conn, "payments", 2))
		header("Location: http://{$_SERVER['HTTP_HOST']}/permission_denied.php");

	$employee = substr($_POST['submit'], 4);
	unset($_POST['submit']);

	// makes the payment
	insertRow($conn, "payment", [
		"date" => date("Y-m-d"),
		"amount" => array_sum($_POST),
		"payer" => $_SESSION['user'],
		"payee" => $employee
	]);

	// udpates the shifts
	$shifts = implode("','", array_keys($_POST));

	execute($conn, "UPDATE `shift`
	SET `wage` = LAST_INSERT_ID()
	WHERE `employee` = '$employee'
	AND `date` IN ('$shifts')");
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Payments</title>
		<link rel="stylesheet" href="/css/style.css">
	</head>
	<body>
		<h1><a href="/">Tempus</a></h1>
		<h2><a href="/home/">Home</a></h2>
		<h3><a href="/home/payments/">Payments</a></h3>

		<h2>Create Payment</h2>
		<table>
			<tr>
				<th>Employee</th>
				<th>Outstanding</th>
				<th>
					<table>
						<th>Date</th>
						<th>Amount</th>
						<th>Include</th>
					</table>
				</th>
				<th>Actions</th>
			</tr>
			<?php foreach (getTable($conn, "SELECT Name, Outstanding FROM view_employee WHERE Outstanding > 0") as $employee_row): ?>
				<tr>
				<form class="" action="<?="http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"?>" method="post">
					<td><?= $employee_row['Name'] ?></td>
					<td>£<?= $employee_row['Outstanding'] ?></td>
					<td>
						<table>
							<?php foreach(getTable($conn, "SELECT *, ROUND(rate*TIME_TO_SEC(length)/3600, 2) as total FROM shift WHERE wage IS NULL AND employee = ? AND length IS NOT NULL", "s", $employee_row['Name']) as $shift_row): ?>
								<tr>
								<td><?= $shift_row['date'] ?></td>
								<td>£<?= $shift_row['total'] ?></td>
								<td><input type="checkbox" name="<?= $shift_row['date'] ?>" value="<?= $shift_row['total'] ?>"></td>
								</tr>
							<?php endforeach ?>
						</table>
					</td>
					<td><input type="submit" name="submit" value="Pay <?= $employee_row['Name'] ?>"></td>
				</form>
				</tr>
			<?php endforeach ?>
		</table>

		<h2>Historic Payments</h2>
		<?php table2HTML($conn, "SELECT * FROM `view_payment`"); ?>
	</body>
</html>
