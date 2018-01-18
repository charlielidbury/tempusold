<?php

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

unset($_POST['submit']);

foreach($_POST as $employee)
{
	echo "$employee\n";
}

header("Location: http://{$_SERVER['HTTP_HOST']}/home/sessions/edit_session.php?session=$_GET['session']");

?>
