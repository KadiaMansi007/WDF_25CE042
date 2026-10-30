
<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "ce1_mansi";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed.");
}

$conn->set_charset("utf8mb4");
?>
