<?php
include("conf.php");

// Make connection
$conn = new mysqli($host, $user, $pass, $name);
$conn->set_charset("utf-8");

// Check connection
if ($conn->connect_error)
{
	die ("Connection failed: " . $conn->connect_error);
}

function getCell($conn, $column, $table, $key, $value)
{
	/*
	GETS A CELL FROM A table
	WARNING: ONLY THE $value IS SAFE, DO NOT USE USER INPUT FOR ANY OTHER ARGUMENT

	$conn <= connection object
	$table <= name of table data is to be extracted from
	$column <= which field the data is stored in
	$key <= field for data to be recognised with (WHERE $key = "Charlie")
	$value <= value for data to be recognised with (WHERE "name" = $value)

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

function getRow($conn, $table, $key, $value)
{
	/*
	GETS A ROW FROM A table
	WARNING: ONLY THE $value IS SAFE, DO NOT USE USER INPUT FOR ANY OTHER ARGUMENT

	$conn <= connection object
	$table <= name of table data is to be extracted from
	$key <= field for data to be recognised with (WHERE $key = "Charlie")
	$value <= value for data to be recognised with (WHERE "name" = $value)

	returns => the single row from the table (associative array)
	*/

	$stmt = $conn->prepare("SELECT * FROM `$table` WHERE `$key` = ?");

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
	$row = $result->fetch_assoc();

	$stmt->close();

	return $row;

}

function getColumn($conn, $table, $field)
{
	/*
	GETS A COLUMN FROM A table
	WARNING: ONLY THE $value IS SAFE, DO NOT USE USER INPUT FOR ANY OTHER ARGUMENT

	$conn <= connection object
	$table <= name of table data is to be extracted from
	$field <= column to return an array of

	returns => the single row from the table (associative array)
	*/

	$stmt = $conn->prepare("SELECT `$field` FROM `$table`");

	if (!$stmt) die ("Statement failed to prepare: " . $conn->error);

	// execute query
	$stmt->execute();

	// get result
	$result = $stmt->get_result();

	// gets result
	$final = [];
	while ($row = $result->fetch_row())
		$final[] = $row[0];

	$stmt->close();

	return $final;

}

function getTable()
{

}

function row2HTML($conn, $table, $key, $value, $extra="")
{
	/*
	RETURNS HTML REPRESENTATION OF ROW IN TABLE
	WARNING: ONLY THE $value IS SAFE, DO NOT USE USER INPUT FOR ANY OTHER ARGUMENT

	$conn <= connection object
	$table <= name of table data is to be extracted from
	$key <= field for data to be recognised with (WHERE $key = "Charlie")
	$value <= value for data to be recognised with (WHERE "name" = $value)
	$extra_rows <= array of extra rows of data to be appended at the end

	returns => HTML representation of the row
	*/

	$stmt = $conn->prepare("SELECT * FROM `$table` WHERE `$key` = ?");

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
	$row = $result->fetch_assoc();

	$stmt->close();

	$row_string = "<tr>\n\t<td>%s</td>\n\t<td>%s</td>\n</tr>\n";

	// ACTUAL PRINTING
	$final = "<table>\n";

	// main data
	foreach ($result->fetch_fields() as $field)
	{
		$final .= sprintf($row_string, $field->name, $row[$field->name]);
	}

	// extra_rows
	$final .= $extra;

	$final .= "</table>";

	return $final;
}

function table2HTML($conn, $query, $format, $arg)
{
	/*
	PRINTS HTML REPRESENTATION OF A QUERY

	$conn <= connection object
	$query <= query with ?'s as variable names ("SELECT * FROM `employee` WHERE `name` = ?")
	$format <= data type of $arg (s=string, i=int, d=double, b=blob)
	$arg <= arg to be passed to the query in place of ?
	*/

	$stmt = $conn->prepare($query);

	if (!$stmt) die ("Statement failed to prepare: " . $conn->error);

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

function hasPerms($conn, $perm, $level)
{
	/*
	CHECKS IF USER HAS PERMISSIONS

	$perm <= name for permission ("members", "sessions", ... )
	$level <= level of perm (0, 1, 2) (for none, view & edit respectively)
	*/

	$users_role = getCell($conn, "role", "employee", "name", $_SESSION['user']);

	return getCell($conn, $perm, "role", "role", $users_role) >= $level;
}

function updateRow($conn, $table, $key, $value, $changes)
{
	/*
	MAKES A LIST OF CHANGES TO A ROW IN A TABLE
	WARNING: DOES NOT WORK WHEN UPDATING CELLS TO NULL VALUE

	$conn <= connection object
	$table <= table the row is in
	$key <= key used to identify that row (WHERE ? = "Charlie")
	$value <= value the key should be at row (WHERE `name` = ?)
	$changes <= accociative array where the key is the field and the value is the new value
		~ i.e: array( 'name' => "CharlieLidbury" ) changes the name to Charlie of selected rows
	*/

	// creates query
	$query = "UPDATE `$table` SET\n "; // "UPDATE `employee` SET "
	foreach ($changes as $field => $new)
		if ($new == "")
			$query .= "\t`$field` = NULL,\n"; // "`email` = NULL, "
		else
			$query .= "\t`$field` = '$new',\n"; // "`email` = 'charlie.lidbury@icloud.com', "
	$query = substr($query, 0, -2); // removes trailing comma and newline
	$query .= "\nWHERE `$key` = ?\n"; // "WHERE `name` = 'Charlie'"

	// prepares statement
	$stmt = $conn->prepare($query);
	if (!$stmt) die ("Statement failed to prepare: " . $conn->error);

	// makes the format string
	$conversion = array(
		"integer" => "i",
		"double"  => "d",
		"string"  => "s"
	);
	$format = $conversion[gettype($value)];

	// execute query
	$stmt->bind_param($format, $value);
	$stmt->execute();
}

function insertRow($conn, $table, $row)
{
	/*
	INSERTS A ROW INTO THE SPECIFIED TABLE

	$conn <= connection object
	$table <= table to insert into
	$row <= accociative array where the key is the field and the value is the new value
		~ i.e: array( 'name' => "CharlieLidbury" ) sets name to CharlieLidbury
	*/

	// creates query
	$fields = "";
	$values = "";
	foreach ($row as $field => $value)
	{
		if ($value == "") continue;
		$fields .= "`$field`, "; // (`name`, `hash`)
		$values .= "'{$conn->escape_string($value)}', "; // ('Charlie', 'akl;hwdjnshjdawbjadwbjkl')
	}
	$fields = substr($fields, 0, -2);
	$values = substr($values, 0, -2);

	$query = "INSERT INTO `$table` ($fields) VALUES ($values)";

	// execute!
	$conn->query($query);
}

function deleteRow($conn, $table, $key, $value)
{
	/*
	DELETES SPEICIFED ROW FROM TABLE

	$conn <= connection object
	$table <= table the row is in
	$key <= key used to identify that row (WHERE ? = "Charlie")
	$value <= value the key should be at row (WHERE `name` = ?)
	*/

	$query = "DELETE FROM `$table` WHERE `$key` = ?";

	// prepares statement
	$stmt = $conn->prepare($query);
	if (!$stmt) die ("Statement failed to prepare: " . $conn->error);

	// makes the format string
	$conversion = array(
		"integer" => "i",
		"double"  => "d",
		"string"  => "s"
	);
	$format = $conversion[gettype($value)];

	// execute query
	$stmt->bind_param($format, $value);
	$stmt->execute();
}

?>
