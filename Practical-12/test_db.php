<?php
require "db.php";

echo "Database connection successful!<br>";

try {
    $stmt = $pdo->query("DESCRIBE events");

    echo "Events table found!<br><br>";

    while ($row = $stmt->fetch()) {
        echo $row["Field"] . " - " . $row["Type"] . "<br>";
    }

} catch (PDOException $e) {
    echo "Table Error: " . $e->getMessage();
}
?>