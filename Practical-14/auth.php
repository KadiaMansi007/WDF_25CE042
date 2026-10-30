<?php
session_start();
require_once "db.php";

function e($text)
{
    return htmlspecialchars((string)$text, ENT_QUOTES, "UTF-8");
}

function audit($pdo, $userId, $action)
{
    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action) VALUES (?, ?)");
    $stmt->execute([(int)$userId, $action]);
}

function csrf_token()
{
    if (empty($_SESSION["csrf"])) {
        $_SESSION["csrf"] = bin2hex(random_bytes(16));
    }
    return $_SESSION["csrf"];
}

function csrf_check()
{
    $sent = $_POST["csrf"] ?? "";
    if (!hash_equals($_SESSION["csrf"] ?? "", $sent)) {
        http_response_code(400);
        exit("Invalid request token.");
    }
}

function require_login($pdo)
{
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }
    // check again in the database so a deactivated user is thrown out immediately
    $stmt = $pdo->prepare("SELECT role, is_active FROM users WHERE id = ?");
    $stmt->execute([$_SESSION["user_id"]]);
    $row = $stmt->fetch();
    if (!$row || (int)$row["is_active"] !== 1) {
        session_unset();
        session_destroy();
        header("Location: login.php?msg=inactive");
        exit;
    }
    $_SESSION["role"] = $row["role"];
}

function require_role($pdo, $allowedRoles)
{
    require_login($pdo);
    if (!in_array($_SESSION["role"], $allowedRoles, true)) {
        audit($pdo, $_SESSION["user_id"], "Access denied to " . basename($_SERVER["PHP_SELF"]));
        http_response_code(403);
        exit("Access denied. You do not have permission to open this page.");
    }
}
