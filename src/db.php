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
	/*
	PRINTS HTML REPRESENTATION OF A QUERY

	$conn <= connection object
	$query <= query with ?'s as variable names ("SELECT * FROM `employee` WHERE `name` = ?")
	$format <= data type of $arg (s=string, i=int, d=double, b=blob)
	$arg <= arg to be passed to the query in place of ?
	*/

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

function getCell($conn, $column, $table, $key, $value)
{
	/*
	GETS A CELL FROM A table

	$conn <= connection object
	$table <= name of table data is to be extracted from
	$column <= which field the data is stored in
	$key <= field for data to be recognised with (WHERE $key = "Charlie")
	$key <= value for data to be recognised with (WHERE "name" = $value)

	returns => the single value from the table
	*/

	$stmt = $conn->prepare("SELECT `$column` FROM `$table` WHERE `$key` = ?");

	if (!$stmt) die ("Statement failed to prepare: " . $conn->error);

	// makes the format string 'sssi' for instance
	$conversion = array(
		"integer" => "i",
		"double"  => "d",
		"string"  => "s"
	);

	$format = $conversion[gettype($value)];

	// binds things together
	$stmt->bind_param($format, $value);
	// execute query
	$stmt->execute();

	// get result
	$result = $stmt->get_result();

	// gets result
	if (!( $row = $result->fetch_row() ))
		die("No results!");

	$stmt->close();

	return $row[0];

}

?>
