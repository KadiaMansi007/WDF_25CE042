
<?php
require "db.php";

$stmt = $pdo->query("SELECT id, name, email, course FROM students");
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Test</title>
</head>
<body>
    <h1>Student Records</h1>

    <?php foreach ($students as $student): ?>
        <p>
            <?= htmlspecialchars($student["name"], ENT_QUOTES, "UTF-8") ?> -
            <?= htmlspecialchars($student["email"], ENT_QUOTES, "UTF-8") ?> -
            <?= htmlspecialchars($student["course"], ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endforeach; ?>
</body>
</html>
