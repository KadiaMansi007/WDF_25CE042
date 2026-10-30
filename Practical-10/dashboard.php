<?php
session_start();
$timeout = 900;
 
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
if (isset($_SESSION["last_activity"]) && time() - $_SESSION["last_activity"] > $timeout) {
    $_SESSION = [];
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}
$_SESSION["last_activity"] = time();
 
if ($_SESSION["role"] === "admin") {
    $message = "Welcome to the Admin Dashboard";
} else {
    $message = "Welcome to the Student Dashboard";
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Dashboard</title></head>
<body>
<h1><?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?></h1>
<p>Hello, <?= htmlspecialchars($_SESSION["name"], ENT_QUOTES, "UTF-8") ?></p>
<?php if ($_SESSION["role"] === "admin"): ?>
    <a href="admin.php">Open Admin Area</a>
<?php endif; ?>
<a href="logout.php">Logout</a>
</body>
</html>
