
<?php
require __DIR__ . "/db.php";

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    try {
        if ($action === "add") {
            $name = trim($_POST["name"] ?? "");
            $email = trim($_POST["email"] ?? "");
            $course = trim($_POST["course"] ?? "");

            if ($name === "" || $email === "" || $course === "") {
                throw new Exception("Please fill in all fields.");
            }

            $stmt = $conn->prepare(
                "INSERT INTO students (name, email, course) VALUES (?, ?, ?)"
            );
            $stmt->bind_param("sss", $name, $email, $course);
            $stmt->execute();
            $stmt->close();

            $message = "Student added successfully.";
        }

        if ($action === "update") {
            $id = (int)($_POST["id"] ?? 0);
            $name = trim($_POST["name"] ?? "");
            $email = trim($_POST["email"] ?? "");
            $course = trim($_POST["course"] ?? "");

            if ($id <= 0 || $name === "" || $email === "" || $course === "") {
                throw new Exception("Please enter valid student details.");
            }

            $stmt = $conn->prepare(
                "UPDATE students SET name = ?, email = ?, course = ? WHERE id = ?"
            );
            $stmt->bind_param("sssi", $name, $email, $course, $id);
            $stmt->execute();

            $message = $stmt->affected_rows > 0
                ? "Student updated successfully."
                : "No changes made, or student not found.";

            $stmt->close();
        }

        if ($action === "delete") {
            $id = (int)($_POST["id"] ?? 0);

            if ($id <= 0) {
                throw new Exception("Invalid student ID.");
            }

            $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            $message = $stmt->affected_rows > 0
                ? "Student deleted successfully."
                : "Student not found.";

            $stmt->close();
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) {
            $error = "This email address already exists.";
        } else {
            $error = "Database operation failed. Please check your input.";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$editStudent = null;

if (isset($_GET["edit"])) {
    $editId = (int)$_GET["edit"];

    $stmt = $conn->prepare("SELECT id, name, email, course FROM students WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();

    $editStudent = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$search = trim($_GET["search"] ?? "");
$courseFilter = trim($_GET["course"] ?? "");

$page = max(1, (int)($_GET["page"] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$searchPattern = "%" . $search . "%";

$countStmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM students
     WHERE (? = '' OR name LIKE ? OR email LIKE ? OR course LIKE ?)
       AND (? = '' OR course = ?)"
);

$countStmt->bind_param(
    "ssssss",
    $search, $searchPattern, $searchPattern, $searchPattern,
    $courseFilter, $courseFilter
);
$countStmt->execute();

$totalStudents = $countStmt->get_result()->fetch_assoc()["total"];
$countStmt->close();

$totalPages = max(1, (int)ceil($totalStudents / $limit));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

$stmt = $conn->prepare(
    "SELECT id, name, email, course
     FROM students
     WHERE (? = '' OR name LIKE ? OR email LIKE ? OR course LIKE ?)
       AND (? = '' OR course = ?)
     ORDER BY id DESC
     LIMIT ? OFFSET ?"
);

$stmt->bind_param(
    "ssssssii",
    $search, $searchPattern, $searchPattern, $searchPattern,
    $courseFilter, $courseFilter, $limit, $offset
);
$stmt->execute();
$students = $stmt->get_result();

$courses = $conn->query(
    "SELECT DISTINCT course FROM students ORDER BY course"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>Student Management System</h1>

    <?php if ($message !== ""): ?>
        <p class="success"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <h2><?= $editStudent ? "Edit Student" : "Add Student" ?></h2>

    <form method="post">
        <input type="hidden" name="action"
               value="<?= $editStudent ? "update" : "add" ?>">

        <?php if ($editStudent): ?>
            <input type="hidden" name="id"
                   value="<?= (int)$editStudent["id"] ?>">
        <?php endif; ?>

        <label>Name</label>
        <input type="text" name="name" required
               value="<?= htmlspecialchars($editStudent["name"] ?? "") ?>">

        <label>Email</label>
        <input type="email" name="email" required
               value="<?= htmlspecialchars($editStudent["email"] ?? "") ?>">

        <label>Course</label>
        <input type="text" name="course" required
               value="<?= htmlspecialchars($editStudent["course"] ?? "") ?>">

        <button type="submit">
            <?= $editStudent ? "Update Student" : "Add Student" ?>
        </button>

        <?php if ($editStudent): ?>
            <a href="students.php">Cancel Edit</a>
        <?php endif; ?>
    </form>

    <h2>Search and Filter Students</h2>

    <form method="get" class="search-form">
        <input type="text" name="search"
               placeholder="Search name, email or course"
               value="<?= htmlspecialchars($search) ?>">

        <select name="course">
            <option value="">All Courses</option>

            <?php while ($c = $courses->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($c["course"]) ?>"
                    <?= $courseFilter === $c["course"] ? "selected" : "" ?>>
                    <?= htmlspecialchars($c["course"]) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Search</button>
        <a href="students.php">Reset</a>
    </form>

    <h2>Student Records</h2>

    <p>Total matching students: <?= (int)$totalStudents ?></p>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Course</th>
            <th>Actions</th>
        </tr>
        </thead>

        <tbody>
        <?php if ($students->num_rows > 0): ?>
            <?php while ($student = $students->fetch_assoc()): ?>
                <tr>
                    <td><?= (int)$student["id"] ?></td>
                    <td><?= htmlspecialchars($student["name"]) ?></td>
                    <td><?= htmlspecialchars($student["email"]) ?></td>
                    <td><?= htmlspecialchars($student["course"]) ?></td>
                    <td>
                        <a href="?edit=<?= (int)$student["id"] ?>">Edit</a>

                        <form method="post" class="inline-form"
                              onsubmit="return confirm('Are you sure you want to delete this student?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id"
                                   value="<?= (int)$student["id"] ?>">
                            <button type="submit" class="delete-button">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No students found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?search=<?= urlencode($search) ?>&course=<?= urlencode($courseFilter) ?>&page=<?= $page - 1 ?>">Previous</a>
        <?php endif; ?>

        <span>Page <?= $page ?> of <?= $totalPages ?></span>

        <?php if ($page < $totalPages): ?>
            <a href="?search=<?= urlencode($search) ?>&course=<?= urlencode($courseFilter) ?>&page=<?= $page + 1 ?>">Next</a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
