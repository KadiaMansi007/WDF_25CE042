
<?php
session_start();
require __DIR__ . "/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $pdo->prepare(
        "SELECT id, name, email, password_hash, role
         FROM users
         WHERE email = ?"
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = "Email not found. Please register first.";
    } elseif (!password_verify($password, $user["password_hash"])) {
        $error = "Password does not match.";
    } else {
        session_regenerate_id(true);
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["name"] = $user["name"];
        $_SESSION["role"] = $user["role"];
        $_SESSION["last_activity"] = time();

        header("Location: dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <p><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></p>
    <form method="post" action="login.php">
        <label>Email
            <input type="email" name="email" required>
        </label>
        <br><br>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <br><br>
        <button type="submit">Login</button>
    </form>
    <p><a href="register.html">Create an account</a></p>
</body>
</html>
