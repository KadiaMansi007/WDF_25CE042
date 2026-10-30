<?php
require_once "auth.php";
require_role($pdo, ["admin", "staff"]);

$pageTitle = "Students";
$isAdmin = ($_SESSION["role"] === "admin");
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!$isAdmin) {
        audit($pdo, $_SESSION["user_id"], "Blocked: non-admin tried to change students");
        http_response_code(403);
        exit("Only admin can add or delete students.");
    }
    csrf_check();

    if (($_POST["action"] ?? "") === "add") {
        $name = trim($_POST["name"] ?? "");
        $roll = trim($_POST["roll_no"] ?? "");
        $course = trim($_POST["course"] ?? "");
        if ($name === "" || $roll === "" || $course === "") {
            $message = "All fields are required.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO students (name, roll_no, course) VALUES (?, ?, ?)");
                $stmt->execute([$name, $roll, $course]);
                audit($pdo, $_SESSION["user_id"], "Added student " . $roll);
                $message = "Student added.";
            } catch (PDOException $ex) {
                $message = "Roll number already exists.";
            }
        }
    }

    if (($_POST["action"] ?? "") === "delete") {
        $id = (int)($_POST["id"] ?? 0);
        $stmt = $pdo->prepare("SELECT roll_no FROM students WHERE id = ?");
        $stmt->execute([$id]);
        $roll = $stmt->fetchColumn();
        if ($roll !== false) {
            $pdo->prepare("DELETE FROM students WHERE id = ?")->execute([$id]);
            audit($pdo, $_SESSION["user_id"], "Deleted student " . $roll);
            $message = "Student deleted.";
        }
    }
}

$students = $pdo->query(
    "SELECT s.id, s.name, s.roll_no, s.course, COUNT(r.id) AS reg_count
     FROM students s
     LEFT JOIN registrations r ON r.student_id = s.id
     GROUP BY s.id, s.name, s.roll_no, s.course
     ORDER BY s.id"
)->fetchAll();

require "header.php";
?>
<h2 class="mb-3">Students</h2>
<?php if ($message !== ""): ?>
  <div class="alert alert-info"><?= e($message) ?></div>
<?php endif; ?>

<?php if ($isAdmin): ?>
<form method="post" class="row g-2 mb-4">
  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
  <input type="hidden" name="action" value="add">
  <div class="col-md-3"><input type="text" name="name" class="form-control" placeholder="Name" required></div>
  <div class="col-md-2"><input type="text" name="roll_no" class="form-control" placeholder="Roll No" required></div>
  <div class="col-md-4"><input type="text" name="course" class="form-control" placeholder="Course" required></div>
  <div class="col-md-3"><button type="submit" class="btn btn-success w-100">Add Student</button></div>
</form>
<?php endif; ?>

<table class="table table-bordered bg-white">
  <tr><th>ID</th><th>Name</th><th>Roll No</th><th>Course</th><th>Registrations</th><?php if ($isAdmin): ?><th>Action</th><?php endif; ?></tr>
  <?php foreach ($students as $row): ?>
  <tr>
    <td><?= (int)$row["id"] ?></td>
    <td><?= e($row["name"]) ?></td>
    <td><?= e($row["roll_no"]) ?></td>
    <td><?= e($row["course"]) ?></td>
    <td><?= (int)$row["reg_count"] ?></td>
    <?php if ($isAdmin): ?>
    <td>
      <form method="post" onsubmit="return confirm('Delete this student?');">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= (int)$row["id"] ?>">
        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
      </form>
    </td>
    <?php endif; ?>
  </tr>
  <?php endforeach; ?>
</table>
<?php require "footer.php"; ?>
