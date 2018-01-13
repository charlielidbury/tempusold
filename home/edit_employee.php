<?php
include "/src/db.php";

$user_data = getRow($conn, "employee", "name", $_GET['user']);
?>

<table>
	<tr>
		<th>Aspect</th>
		<th>Value</th>
	</tr>
	<tr>
		<td>Name</td>
		<td><?= $user_data['name']; ?></td>
	</tr>
	<tr>
		<td>Password</td>
		<td><a href="change_password.php?user=<?= $user_data['name'] ?>">Change</a></td>
	</tr>
	<tr>
		<td>Email</td>
		<td></td>
	</tr>
	<tr>
		<td>Discord</td>
		<td></td>
	</tr>
	<tr>
		<td>Icon</td>
		<td></td>
	</tr>
	<tr>
		<td>Rate</td>
		<td></td>
	</tr>
	<tr>
		<td>Rank</td>
		<td></td>
	</tr>
	<tr>
		<td>Role</td>
		<td></td>
	</tr>
</table>
