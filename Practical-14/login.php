<?php
require_once "auth.php";

if (isset($_SESSION["user_id"])) {
    header("Location: " . ($_SESSION["role"] === "admin" ? "admin.php" : "home.php"));
    exit;
}

$error = "";
if (($_GET["msg"] ?? "") === "inactive") {
    $error = "Your session ended because the account is inactive.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user["password"])) {
        if ($user) {
            audit($pdo, $user["id"], "Failed login attempt");
        }
        $error = "Invalid email or password.";

    } elseif ((int)$user["is_active"] !== 1) {
        audit($pdo, $user["id"], "Login blocked: inactive account");
        $error = "This account is inactive. Contact the administrator.";

    } else {
        session_regenerate_id(true);
        $_SESSION["user_id"] = (int)$user["id"];
        $_SESSION["name"] = $user["name"];
        $_SESSION["role"] = $user["role"];

        audit($pdo, $user["id"], "Logged in");

        header("Location: " . ($user["role"] === "admin" ? "admin.php" : "home.php"));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudentHub Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container" style="max-width:420px; margin-top:80px;">
  <div class="card shadow-sm">
    <div class="card-body p-4">
      <h3 class="text-center mb-4">StudentHub Login</h3>
      <?php if ($error !== ""): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
      <?php endif; ?>
      <form method="post" action="login.php">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
