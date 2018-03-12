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

function discoBot(...$cmds_or_args)
{
	/*
	action <= "sendInvite" OR [ ["sendInvite", "2018-02-25", "Charlie"] ]
	args <= "2018-23-25", "Charlie" OR NULL

	actions:
		~ sendAllInvites = (client) => { ... }
		~ sendInvite = (client, date, user) => { ... }
		~ createChannel = (client, date) => { ... }
		~ deleteChannel = (client, date) => { ... }
		~ populateChannel = (client, date) => { ... }
		~ addUser = (client, date, user) => { ... }
	*/

	if (gettype($cmds_or_args[0]) != "array")
		// if a single command is passed
		$cmds_or_args = [$cmds_or_args];

	$string = json_encode($cmds_or_args);

	$ch = curl_init("http://localhost:3000");
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 1);

	curl_exec($ch);
	curl_close($ch);
}

function format_args($args)
{
	/*
	Gets a list of args and turns it into a string of their types

	[1, 2, "test"] => "iis"
	*/
	$conversion = [
		"integer" => "i",
		"double"  => "d",
		"string"  => "s"
	];

	$str = "";
	foreach ($args as $value)
		$str .= $conversion[gettype($value)];

	return $str;
}

function format_result($result, $format)
{
	// Makes $format case insensitive
	$format = strtoupper($format);

	if ($format == "CELL")
		// RETURN THE CELL
		return $result->fetch_row()[0];
	elseif ($format == "ROW")
		// RETURN THE ROW
		return $result->fetch_assoc();
	else {
		$final = [];

		if ($format == "COLUMN")
			// RETURN THE COLUMN
			while ($row = $result->fetch_row())
				$final[] = $row[0];
		elseif ($format == "TABLE")
			// RETURN THE TABLE
			while ($row = $result->fetch_assoc())
				$final[] = $row;
		else die("Format not recognised");

		return $final;
	}
}

function execute($conn, $query, $options=[])
{
	/*
	Returns the result of the query as a CELL, ROW, COLUMN or entire TABLE

	$conn <= Connection object
	$query <= query string to be executed
	$options <= [
		"args" => [ array of args to replace the ?'s ]
		"force" => type to force; (CELL, ROW, COLUMN or TABLE)
	]

	*/

	// Allows users to add a string and it gets turned into array ("Charlie" => ["Charlie"])
	if (isset($options['args']) && gettype($options['args']) != "array") $options['args'] = [ $options['args'] ];

	// prepares query
	$stmt = $conn->prepare($query);
	if (!$stmt) die ("Statement failed to prepare: " . $conn->error);
	// execute query
	if (isset($options['args'])) $stmt->bind_param(format_args($options['args']), ...$options['args']);
	$stmt->execute();

	// gets result
	$result = $stmt->get_result();

	// FORCE TYPE
	if (isset($options['force'])) return format_result($result, $options['force']);
	// DON'T FORCE TYPE
	if ($result->num_rows == 1)
		if ($result->field_count == 1)
			// RETURN THE CELL
			return format_result($result, "CELL");
		else
			// RETURN THE ROW
			return format_result($result, "ROW");
	elseif ($result->num_rows != 0)
		if ($result->field_count == 1)
			// RETURN THE COLUMN
			return format_result($result, "COLUMN");
		else
			// RETURN THE TABLE
			return format_result($result, "TABLE");
}

function q(...$args) { return execute(...$args); } // alias of execute

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

function getRow($conn, $table, $conditions)
{
	/*
	GETS A ROW FROM A table
	WARNING: ONLY THE $value IS SAFE, DO NOT USE USER INPUT FOR ANY OTHER ARGUMENT
	$conn <= connection object
	$table <= name of table data is to be extracted from
	$conditions <= array where the key is the column and the value is the value it should be (WHERE $key = $value)
	returns => the single row from the table (associative array)
	*/
	// creates query
	$conditions_str = implode(array_keys($conditions), "` = ? AND `"); // puts $conditions into a SQL statement
	$qformat = "SELECT * FROM `%s` WHERE `%s` = ?"; // string to format the SQL statement
	$query = sprintf($qformat, $table, $conditions_str); // brings together the statement

	$stmt = $conn->prepare($query);
	if (!$stmt) die ("Statement failed to prepare: " . $conn->error);

	// makes the format string
	$conversion = array(
		"integer" => "i",
		"double"  => "d",
		"string"  => "s"
	);
	$format = "";
	foreach ($conditions as $value) // adds the conditions to the format string
		$format .= $conversion[gettype($value)];
	$con_vals = array_values($conditions);
	// execute query
	$stmt->bind_param($format, ...$con_vals);
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
function getTable($conn, $query, $format="", $arg)
{
	/*
	RETURNS RESULT OF QUERY
	$conn <= connection object
	$query <= query with ?'s as variable names ("SELECT * FROM `employee` WHERE `name` = ?")
	$format <= data type of $arg (s=string, i=int, d=double, b=blob)
	$arg <= arg to be passed to the query in place of ?
	returns  => 2D array with table result in
	*/
	$stmt = $conn->prepare($query);
	if (!$stmt) die ("Statement failed to prepare: " . $conn->error);
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
function table2HTML($conn, $query, ...$args)
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
	if (count($args) > 0) $stmt->bind_param(format_args($args), ...$args);
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
	$perm <= name for permission ("team", "sessions", "payments")
	$level <= level of perm (0, 1, 2) (for none, view & edit respectively)
	*/
	$query = "SELECT * FROM `role`
	WHERE `role` = (SELECT `role` FROM `employee` WHERE `name` = ?)";

	return q($conn, $query, ['args' => $_SESSION['user']])[$perm] >= $level;
}
function updateRow($conn, $table, $conditions, $changes)
{
	/*
	MAKES A LIST OF CHANGES TO A ROW IN A TABLE
	WARNING: DOES NOT WORK WHEN UPDATING CELLS TO NULL VALUE
	$conn <= connection object
	$table <= table the row is in
	$conditions <= array where the key is the column and the value is the value it should be (WHERE $key = $value)
	$changes <= accociative array where the key is the field and the value is the new value
		~ i.e: array( 'name' => "CharlieLidbury" ) changes the name to Charlie of selected rows
	*/
	// creates query
	$changes_str = implode(array_keys($changes), "` = ? , `"); // puts $changes into an SQL statement
	$conditions_str = implode(array_keys($conditions), "` = ? AND `"); // puts $conditions into a SQL statement
	$qformat = "UPDATE `%s` SET `%s` = ? WHERE `%s` = ? "; // string to format the SQL statement
	$query = sprintf($qformat, $table, $changes_str, $conditions_str); // brings together the statement
	// prepares statement
	$stmt = $conn->prepare($query);
	if (!$stmt) die ("Statement failed to prepare: " . $conn->error);
	// makes the format string
	$conversion = array(
		"integer" => "i",
		"double"  => "d",
		"string"  => "s"
	);
	$format = "";
	foreach ($conditions as $value) // adds the conditions to the format string
		$format .= $conversion[gettype($value)];
	foreach ($changes as $value) // adds the changes to the format string
		$format .= $conversion[gettype($value)];
	$con_vals = array_values($conditions);
	$cha_vals = array_values($changes);
	/*
	var_dump($query);
	var_dump($format);
	var_dump(...$con_vals);
	var_dump(...$cha_vals);
	*/
	// execute query
	$stmt->bind_param($format, ...$cha_vals, ...$con_vals);
	$stmt->execute();
	$stmt->close();
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
	//if ($conn->query($query) !== true)
		//printf("Error: %s<br>", $conn->error);
}
function deleteRow($conn, $table, $conditions)
{
	/*
	DELETES SPEICIFED ROW FROM TABLE
	$conn <= connection object
	$table <= table the row is in
	$conditions <= array where the key is the column and the value is the value it should be (WHERE $key = $value)
	*/
	// creates query
	$conditions_str = implode(array_keys($conditions), "` = ? AND `"); // puts $conditions into a SQL statement
	$qformat = "DELETE FROM `%s` WHERE `%s` = ? "; // string to format the SQL statement
	$query = sprintf($qformat, $table, $conditions_str); // brings together the statement
	// prepares statement
	$stmt = $conn->prepare($query);
	if (!$stmt) die ("Statement failed to prepare: " . $conn->error);
	// makes the format string
	$conversion = array(
		"integer" => "i",
		"double"  => "d",
		"string"  => "s"
	);
	$format = "";
	foreach ($conditions as $value) // adds the conditions to the format string
		$format .= $conversion[gettype($value)];
	$con_vals = array_values($conditions);
	// execute query
	$stmt->bind_param($format, ...$con_vals);
	$stmt->execute();
}

function quickVal($conn, $query, $format="", $arg)
{
	/*
	RETURNS RESULT OF QUERY

	$conn <= connection object
	$query <= query with ?'s as variable names ("SELECT * FROM `employee` WHERE `name` = ?")
	$format <= data type of $arg (s=string, i=int, d=double, b=blob)
	$arg <= arg to be passed to the query in place of ?
	returns  => first value of first row fetched

	*/
	$stmt = $conn->prepare($query);
	if (!$stmt) die ("Statement failed to prepare: " . $conn->error);
	// execute query
	if ($format) $stmt->bind_param($format, $arg);
	$stmt->execute();
	// get result
	$result = $stmt->get_result();

	$stmt->close();
	return $result->fetch_row()[0];
}
?>
