<?php
require "db.php";
$result = "Please submit the registration form.";
 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
 
    if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $result = "Enter a name, valid email and password of at least 8 characters.";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
 
        if ($check->fetch()) {
            $result = "This email is already registered.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            try {
                $insert = $pdo->prepare(
                    "INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)"
                );
                $insert->execute([$name, $email, $hash]);
                $result = "Registration successful.";
            } catch (PDOException $e) {
                $result = "Registration could not be completed.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Registration Result</title></head>
<body>
<h1>Registration Result</h1>
<p><?= htmlspecialchars($result, ENT_QUOTES, "UTF-8") ?></p>
<a href="register.html">Back</a>
</body>
</html>
