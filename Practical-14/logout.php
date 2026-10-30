<?php
require_once "auth.php";

if (isset($_SESSION["user_id"])) {
    audit($pdo, $_SESSION["user_id"], "Logged out");
}
session_unset();
session_destroy();
header("Location: login.php");
exit;
