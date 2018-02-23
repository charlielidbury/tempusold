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

	foreach ($_POST as $employee => $amount)
		insertRow($conn, "payment", [
			"date" => date("Y-m-d"),
			"amount" => $amount,
			"payer" => $_SESSION['user'],
			"payee" => $employee
		]);
}

$outstanding_query = "SELECT
	`name`,
	`total_shift`.`earnt` - `total_payment`.`paid` AS `outstanding`
FROM `employee`
	JOIN (SELECT `employee`, ROUND(SUM( TIME_TO_SEC(`length`)*`rate`/3600 ), 2) AS `earnt` FROM `shift` GROUP BY `employee`) `total_shift`
		ON `total_shift`.`employee` = `employee`.`name`
	JOIN (SELECT `payee`, SUM(`amount`) AS `paid` FROM `payment` GROUP BY `payee`) `total_payment`
		ON `total_payment`.`payee` = `employee`.`name`
WHERE `total_shift`.`earnt` - `total_payment`.`paid` != 0";

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
		<form action="<?= "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}" ?>" method="POST">
			<table>
				<tr>
					<th>Employee</th>
					<th>Outstanding</th>
					<th>Amount</th>
				</tr>
				<?php foreach(q($conn, $outstanding_query, ['force' => "TABLE"]) as $employee): ?>
					<tr>
						<td><?= $employee['name'] ?></td>
						<td><?= $employee['outstanding'] ?></td>
						<td><input type="number" step="0.01" name=<?= $employee['name'] ?>></td>
					</tr>
				<?php endforeach; ?>
			</table>
			<input type="submit" value="Pay All">
		</form>

		<h2>Historic Payments</h2>
		<?php table2HTML($conn, "SELECT * FROM `view_payment`"); ?>
	</body>
</html>
