
<?php
require "db.php";

$message = "";
$error = "";
$editEvent = null;

$uploadDir = "uploads/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function deletePoster($filename) {
    if ($filename != "" && file_exists("uploads/" . basename($filename))) {
        unlink("uploads/" . basename($filename));
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $action = $_POST["action"];

    if ($action == "add" || $action == "update") {

        $id = $_POST["id"] ?? 0;
        $title = trim($_POST["title"]);
        $date = $_POST["event_date"];
        $venue = trim($_POST["venue"]);
        $status = $_POST["status"];

        $newPoster = "";
        $oldPoster = "";

        try {

            if ($title == "" || $date == "" || $venue == "") {
                throw new Exception("Please fill all required fields.");
            }

            if (!in_array($status, ["Open", "Closed"])) {
                throw new Exception("Invalid event status.");
            }

            if ($action == "update") {
                $stmt = $pdo->prepare("SELECT poster FROM events WHERE id = ?");
                $stmt->execute([$id]);
                $oldEvent = $stmt->fetch();

                if (!$oldEvent) {
                    throw new Exception("Event not found.");
                }

                $oldPoster = $oldEvent["poster"];
            }

            if (isset($_FILES["poster"]) &&
                $_FILES["poster"]["error"] != UPLOAD_ERR_NO_FILE) {

                if ($_FILES["poster"]["error"] != UPLOAD_ERR_OK) {
                    throw new Exception("Poster upload failed. Check the file size.");
                }

                if ($_FILES["poster"]["size"] > 2 * 1024 * 1024) {
                    throw new Exception("Error: Poster size must not exceed 2 MB.");
                }

                $type = mime_content_type($_FILES["poster"]["tmp_name"]);

                if (!in_array($type, ["image/jpeg", "image/png", "image/webp"])) {
                    throw new Exception("Only JPG, PNG and WEBP images are allowed.");
                }

                $extension = [
                    "image/jpeg" => "jpg",
                    "image/png" => "png",
                    "image/webp" => "webp"
                ];

                $newPoster = uniqid() . "." . $extension[$type];

                if (!move_uploaded_file(
                    $_FILES["poster"]["tmp_name"],
                    $uploadDir . $newPoster
                )) {
                    throw new Exception("Unable to upload poster.");
                }
            }

            if ($action == "add") {

                $stmt = $pdo->prepare(
                    "INSERT INTO events (title, event_date, venue, poster, status)
                     VALUES (?, ?, ?, ?, ?)"
                );

                $stmt->execute([
                    $title, $date, $venue,
                    $newPoster == "" ? null : $newPoster,
                    $status
                ]);

                $message = "Event added successfully.";

            } else {

                if ($newPoster != "") {

                    $stmt = $pdo->prepare(
                        "UPDATE events
                         SET title=?, event_date=?, venue=?, poster=?, status=?
                         WHERE id=?"
                    );

                    $stmt->execute([
                        $title, $date, $venue,
                        $newPoster, $status, $id
                    ]);

                    deletePoster($oldPoster);

                } else {

                    $stmt = $pdo->prepare(
                        "UPDATE events
                         SET title=?, event_date=?, venue=?, status=?
                         WHERE id=?"
                    );

                    $stmt->execute([
                        $title, $date, $venue, $status, $id
                    ]);
                }

                $message = "Event updated successfully.";
            }

        } catch (Exception $e) {

            if ($newPoster != "") {
                deletePoster($newPoster);
            }

            $error = $e->getMessage();

        } catch (PDOException $e) {

            if ($newPoster != "") {
                deletePoster($newPoster);
            }

            $error = "Database operation failed.";
        }

    } elseif ($action == "delete") {

        $id = $_POST["id"];

        $stmt = $pdo->prepare("SELECT poster FROM events WHERE id = ?");
        $stmt->execute([$id]);
        $event = $stmt->fetch();

        if ($event) {

            $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
            $stmt->execute([$id]);

            deletePoster($event["poster"]);

            $message = "Event deleted successfully.";

        } else {
            $error = "Event not found.";
        }
    }
}

if (isset($_GET["edit"])) {

    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$_GET["edit"]]);

    $editEvent = $stmt->fetch();

    if (!$editEvent) {
        $error = "Event not found.";
    }
}

$stmt = $pdo->query("SELECT * FROM events ORDER BY event_date, id");
$events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <h1>Event Management System</h1>

    <?php if ($message != ""): ?>
        <p class="success"><?= h($message) ?></p>
    <?php endif; ?>

    <?php if ($error != ""): ?>
        <p class="error"><?= h($error) ?></p>
    <?php endif; ?>

    <h2><?= $editEvent ? "Update Event" : "Add New Event" ?></h2>

    <form method="POST" enctype="multipart/form-data">

        <input type="hidden" name="action"
               value="<?= $editEvent ? "update" : "add" ?>">

        <?php if ($editEvent): ?>
            <input type="hidden" name="id"
                   value="<?= (int)$editEvent["id"] ?>">
        <?php endif; ?>

        <label>Event Title</label>
        <input type="text" name="title" maxlength="150" required
               value="<?= h($editEvent["title"] ?? "") ?>">

        <label>Event Date</label>
        <input type="date" name="event_date" required
               value="<?= h($editEvent["event_date"] ?? "") ?>">

        <label>Venue</label>
        <input type="text" name="venue" maxlength="120" required
               value="<?= h($editEvent["venue"] ?? "") ?>">

        <label>Event Status</label>
        <select name="status">
            <option value="Open"
                <?= ($editEvent["status"] ?? "Open") == "Open" ? "selected" : "" ?>>
                Open
            </option>

            <option value="Closed"
                <?= ($editEvent["status"] ?? "") == "Closed" ? "selected" : "" ?>>
                Closed
            </option>
        </select>

        <label>Event Poster (JPG, PNG, WEBP - Maximum 2 MB)</label>
        <input type="file" name="poster" id="poster"
               accept=".jpg,.jpeg,.png,.webp">

        <p id="poster-size"></p>
        <p id="poster-error" class="error"></p>

        <?php if (!empty($editEvent["poster"])): ?>
            <p>Current Poster:</p>

            <img src="uploads/<?= rawurlencode($editEvent["poster"]) ?>"
                 class="poster-preview" alt="Event Poster">

            <p>Leave empty to keep the current poster.</p>
        <?php endif; ?>

        <button type="submit">
            <?= $editEvent ? "Update Event" : "Add Event" ?>
        </button>

        <?php if ($editEvent): ?>
            <a href="events.php">Cancel</a>
        <?php endif; ?>

    </form>

    <h2>Events List</h2>

    <?php if (empty($events)): ?>

        <p>No events found.</p>

    <?php else: ?>

        <?php foreach ($events as $event): ?>

            <article class="event-card">

                <h3><?= h($event["title"]) ?></h3>

                <p><strong>Date:</strong> <?= h($event["event_date"]) ?></p>

                <p><strong>Venue:</strong> <?= h($event["venue"]) ?></p>

                <p><strong>Status:</strong> <?= h($event["status"]) ?></p>

                <?php if (!empty($event["poster"])): ?>
                    <img src="uploads/<?= rawurlencode($event["poster"]) ?>"
                         class="poster-preview" alt="Event Poster">
                <?php endif; ?>

                <p>
                    <a href="?edit=<?= (int)$event["id"] ?>">Edit Event</a>
                </p>

                <form method="POST"
                      onsubmit="return confirm('Are you sure you want to delete this event?');">

                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id"
                           value="<?= (int)$event["id"] ?>">

                    <button type="submit" class="delete-button">
                        Delete Event
                    </button>

                </form>

            </article>

        <?php endforeach; ?>

    <?php endif; ?>

</div>

<script>
document.getElementById("poster").addEventListener("change", function () {

    let file = this.files[0];
    let error = document.getElementById("poster-error");
    let size = document.getElementById("poster-size");

    error.textContent = "";
    size.textContent = "";

    if (file) {

        let mb = file.size / (1024 * 1024);

        if (file.size > 2 * 1024 * 1024) {
            error.textContent = "Error: Poster size must not exceed 2 MB.";
            this.value = "";
        } else {
            size.textContent = "Selected file size: " + mb.toFixed(2) + " MB";
        }
    }
});
</script>

</body>
</html>
