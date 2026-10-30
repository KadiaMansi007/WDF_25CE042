<?php
session_start();
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Access denied.");
}
?>
<h1>Admin-only page</h1>
