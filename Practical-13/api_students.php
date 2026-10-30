
<?php
header("Content-Type: application/json; charset=utf-8");

require "db.php";

$method = $_SERVER["REQUEST_METHOD"];

try {

    if ($method == "GET") {

        $stmt = $pdo->query("SELECT id, name, email, course FROM students ORDER BY id");
        $students = $stmt->fetchAll();

        echo json_encode($students);
        exit;
    }

    $input = json_decode(file_get_contents("php://input"), true) ?? [];

    if ($method == "POST" || $method == "PUT") {

        $name = trim($input["name"] ?? "");
        $email = trim($input["email"] ?? "");
        $course = trim($input["course"] ?? "");

        if ($name == "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || $course == "") {
            http_response_code(400);
            echo json_encode(["error" => "Valid name, email and course are required."]);
            exit;
        }

        if ($method == "POST") {

            $stmt = $pdo->prepare(
                "INSERT INTO students (name, email, course) VALUES (?, ?, ?)"
            );

            $stmt->execute([$name, $email, $course]);

            http_response_code(201);

            echo json_encode([
                "message" => "Student added successfully.",
                "id" => (int)$pdo->lastInsertId()
            ]);

            exit;
        }

        if ($method == "PUT") {

            $id = (int)($input["id"] ?? 0);

            if ($id < 1) {
                http_response_code(400);
                echo json_encode(["error" => "Valid student ID is required."]);
                exit;
            }

            $stmt = $pdo->prepare(
                "UPDATE students SET name=?, email=?, course=? WHERE id=?"
            );

            $stmt->execute([$name, $email, $course, $id]);

            if ($stmt->rowCount() == 0) {

                $check = $pdo->prepare("SELECT id FROM students WHERE id=?");
                $check->execute([$id]);

                if (!$check->fetch()) {
                    http_response_code(404);
                    echo json_encode(["error" => "Student not found."]);
                    exit;
                }
            }

            echo json_encode(["message" => "Student updated successfully."]);
            exit;
        }
    }

    if ($method == "DELETE") {

        $id = (int)($input["id"] ?? 0);

        if ($id < 1) {
            http_response_code(400);
            echo json_encode(["error" => "Valid student ID is required."]);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM students WHERE id=?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() == 0) {
            http_response_code(404);
            echo json_encode(["error" => "Student not found."]);
            exit;
        }

        echo json_encode(["message" => "Student deleted successfully."]);
        exit;
    }

    http_response_code(405);
    echo json_encode(["error" => "Method not allowed."]);

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);
    echo json_encode(["error" => "Database operation failed."]);
}
?>
