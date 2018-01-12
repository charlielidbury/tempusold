<?php
include("conf.php");

// Make connection
$conn = new mysqli($host, $user, $pass, $name);

// Check connection
if ($conn->connect_error)
{
	die ("Connection failed: " . $conn->connect_error);
}

function query2Table($conn, $query, $format="", $arg="")
{
	$stmt = $conn->prepare($query);

	if (!$stmt) die ("Statement failed to prepare: " . $mysqli->error);

	// execute query
	if ($format) $stmt->bind_param($format, $arg);
	$stmt->execute();

	// get result
	$result = $stmt->get_result();

	echo "<table>";
	// prints header
	echo "<tr>\n";
	foreach ($result->fetch_fields() as $field)
	{
		printf("\t<th>%s</th>\n", $field->name);
	}
	echo "</tr>\n";

	// prints body
	while ($row = $result->fetch_row())
	{
		echo "<tr>\n\t<td>";
		echo implode("</td>\n\t<td>", $row);
		echo "</td>\n</tr>\n";
	}
	echo "</table>";

	$stmt->close();
}

?>
