<?php

include "db.php";

$result = $conn->query("SELECT * FROM employee");

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch()) {
        var_dump($row);
    }
} else {
    echo "0 results";
}
