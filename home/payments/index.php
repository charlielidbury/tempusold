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

$historic_query = <<<EOT
SELECT
	DATE_FORMAT(`date`, "%d/%m/%y") AS `Date`,
	`amount` AS `Amount`,
	CONCAT("<a href='../team/view_user.php?user=", `payment`.`payer`, "'>", `payment`.`payer`, "</a>") AS `Payer`,
	CONCAT("<a href='../team/view_user.php?user=", `payment`.`payee`, "'>", `payment`.`payee`, "</a>") AS `Payee`,
	CONCAT("<a href='delete_payment.php?payment=", `payment`.`id`, "&redirect=index.php'>Delete</a>") AS `Actions`
FROM `payment`
EOT;

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<link rel="stylesheet" href="/style.css">

		<title>Tempus - Payments</title>
	</head>
	<body>
		<div class="container">
		    <?php include "{$_SERVER['DOCUMENT_ROOT']}/header.php"; ?>

			<h1>Create Payment</h1>
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

			<h1>Historic Payments</h1>
			<?php table2HTML($conn, $historic_query); ?>
		</div>

		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	</body>
</html>
