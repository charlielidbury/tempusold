<?php
session_start();

if (!isset($_SESSION['dbconn']))
{
	include("conf.php");

	// Make connection
	$conn = new mysqli($host, $user, $pass, $name);
	$conn->set_charset("utf-8");

	// Check connection
	if ($conn->connect_error)
		die ("Connection failed: " . $conn->connect_error);

	$_SESSION['dbconn'] = $conn;
}

function getCell($column, $table, $key, $value)
{
	/*
	GETS A CELL FROM A table
	WARNING: ONLY THE $value IS SAFE, DO NOT USE USER INPUT FOR ANY OTHER ARGUMENT

	$table <= name of table data is to be extracted from
	$column <= which field the data is stored in
	$key <= field for data to be recognised with (WHERE $key = "Charlie")
	$value <= value for data to be recognised with (WHERE "name" = $value)

	returns => the single value from the table
	*/

	$stmt = $_SESSION['dbconn']->prepare("SELECT `$column` FROM `$table` WHERE `$key` = ?");

	if (!$stmt) die ("Statement failed to prepare: " . $_SESSION['dbconn']->error);

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

function getRow($table, $key, $value)
{
	/*
	GETS A ROW FROM A table
	WARNING: ONLY THE $value IS SAFE, DO NOT USE USER INPUT FOR ANY OTHER ARGUMENT

	$table <= name of table data is to be extracted from
	$key <= field for data to be recognised with (WHERE $key = "Charlie")
	$value <= value for data to be recognised with (WHERE "name" = $value)

	returns => the single row from the table (associative array)
	*/

	$stmt = $_SESSION['dbconn']->prepare("SELECT * FROM `$table` WHERE `$key` = ?");

	if (!$stmt) die ("Statement failed to prepare: " . $_SESSION['dbconn']->error);

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

function getColumn($table, $field)
{
	/*
	GETS A COLUMN FROM A table
	WARNING: ONLY THE $value IS SAFE, DO NOT USE USER INPUT FOR ANY OTHER ARGUMENT

	$table <= name of table data is to be extracted from
	$field <= column to return an array of

	returns => the single row from the table (associative array)
	*/

	$stmt = $_SESSION['dbconn']->prepare("SELECT `$field` FROM `$table`");

	if (!$stmt) die ("Statement failed to prepare: " . $_SESSION['dbconn']->error);

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

function getTable($query, $format="", $arg)
{
	/*
	RETURNS RESULT OF QUERY

	$query <= query with ?'s as variable names ("SELECT * FROM `employee` WHERE `name` = ?")
	$format <= data type of $arg (s=string, i=int, d=double, b=blob)
	$arg <= arg to be passed to the query in place of ?
	*/

	$stmt = $_SESSION['dbconn']->prepare($query);

	if (!$stmt) die ("Statement failed to prepare: " . $_SESSION['dbconn']->error);

	// execute query
	if ($format) $stmt->bind_param($format, $arg);
	$stmt->execute();

	// get result
	$result = $stmt->get_result();

	$final = array();

	// prints body
	while ($row = $result->fetch_assoc())
		$final[] = $row;

	$stmt->close();

	return $final;

}

function row2HTML($table, $key, $value, $extra="")
{
	/*
	RETURNS HTML REPRESENTATION OF ROW IN TABLE
	WARNING: ONLY THE $value IS SAFE, DO NOT USE USER INPUT FOR ANY OTHER ARGUMENT

	$table <= name of table data is to be extracted from
	$key <= field for data to be recognised with (WHERE $key = "Charlie")
	$value <= value for data to be recognised with (WHERE "name" = $value)
	$extra_rows <= array of extra rows of data to be appended at the end

	returns => HTML representation of the row
	*/

	$stmt = $_SESSION['dbconn']->prepare("SELECT * FROM `$table` WHERE `$key` = ?");

	if (!$stmt) die ("Statement failed to prepare: " . $_SESSION['dbconn']->error);

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

function table2HTML($query, $format, $arg)
{
	/*
	PRINTS HTML REPRESENTATION OF A QUERY

	$query <= query with ?'s as variable names ("SELECT * FROM `employee` WHERE `name` = ?")
	$format <= data type of $arg (s=string, i=int, d=double, b=blob)
	$arg <= arg to be passed to the query in place of ?
	*/

	$stmt = $_SESSION['dbconn']->prepare($query);

	if (!$stmt) die ("Statement failed to prepare: " . $_SESSION['dbconn']->error);

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

function hasPerms($perm, $level)
{
	/*
	CHECKS IF USER HAS PERMISSIONS

	$perm <= name for permission ("members", "sessions", ... )
	$level <= level of perm (0, 1, 2) (for none, view & edit respectively)
	*/

	$users_role = getCell("role", "employee", "name", $_SESSION['user']);

	return getCell($perm, "role", "role", $users_role) >= $level;
}

<<<<<<< HEAD
function updateRow($table, $conditions, $changes)
=======
function updateRow($conn, $table, $key, $value, $changes)
>>>>>>> parent of 8f19b50... Added viewing, editing and adding employees to sessions. Woot!
{
	/*
	MAKES A LIST OF CHANGES TO A ROW IN A TABLE
	WARNING: DOES NOT WORK WHEN UPDATING CELLS TO NULL VALUE

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
	$stmt = $_SESSION['dbconn']->prepare($query);
	if (!$stmt) die ("Statement failed to prepare: " . $_SESSION['dbconn']->error);

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

function insertRow($table, $row)
{
	/*
	INSERTS A ROW INTO THE SPECIFIED TABLE

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
		$values .= "'{$_SESSION['dbconn']->escape_string($value)}', "; // ('Charlie', 'akl;hwdjnshjdawbjadwbjkl')
	}
	$fields = substr($fields, 0, -2);
	$values = substr($values, 0, -2);

	$query = "INSERT INTO `$table` ($fields) VALUES ($values)";

	// execute!
	$_SESSION['dbconn']->query($query);
}

<<<<<<< HEAD
function deleteRow($table, $conditions)
=======
function deleteRow($conn, $table, $key, $value)
>>>>>>> parent of 8f19b50... Added viewing, editing and adding employees to sessions. Woot!
{
	/*
	DELETES SPEICIFED ROW FROM TABLE

	$table <= table the row is in
	$key <= key used to identify that row (WHERE ? = "Charlie")
	$value <= value the key should be at row (WHERE `name` = ?)
	*/

	$query = "DELETE FROM `$table` WHERE `$key` = ?";

	// prepares statement
	$stmt = $_SESSION['dbconn']->prepare($query);
	if (!$stmt) die ("Statement failed to prepare: " . $_SESSION['dbconn']->error);

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
