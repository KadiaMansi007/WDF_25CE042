<?php
require_once "auth.php";
require_role($pdo, ["admin", "staff", "student"]);

$pageTitle = "Events";
$isAdmin = ($_SESSION["role"] === "admin");
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!$isAdmin) {
        audit($pdo, $_SESSION["user_id"], "Blocked: non-admin tried to change events");
        http_response_code(403);
        exit("Only admin can add or delete events.");
    }
    csrf_check();

    if (($_POST["action"] ?? "") === "add") {
        $title = trim($_POST["title"] ?? "");
        $date = trim($_POST["event_date"] ?? "");
        $venue = trim($_POST["venue"] ?? "");
        if ($title === "" || $date === "" || $venue === "") {
            $message = "All fields are required.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO events (title, event_date, venue) VALUES (?, ?, ?)");
            $stmt->execute([$title, $date, $venue]);
            audit($pdo, $_SESSION["user_id"], "Added event " . $title);
            $message = "Event added.";
        }
    }

    if (($_POST["action"] ?? "") === "delete") {
        $id = (int)($_POST["id"] ?? 0);
        $stmt = $pdo->prepare("SELECT title FROM events WHERE id = ?");
        $stmt->execute([$id]);
        $title = $stmt->fetchColumn();
        if ($title !== false) {
            $pdo->prepare("DELETE FROM events WHERE id = ?")->execute([$id]);
            audit($pdo, $_SESSION["user_id"], "Deleted event " . $title);
            $message = "Event deleted.";
        }
    }
}

$events = $pdo->query(
    "SELECT ev.id, ev.title, ev.event_date, ev.venue, COUNT(r.id) AS reg_count
     FROM events ev
     LEFT JOIN registrations r ON r.event_id = ev.id
     GROUP BY ev.id, ev.title, ev.event_date, ev.venue
     ORDER BY ev.event_date"
)->fetchAll();

require "header.php";
?>
<h2 class="mb-3">Events</h2>
<?php if ($message !== ""): ?>
  <div class="alert alert-info"><?= e($message) ?></div>
<?php endif; ?>

<?php if ($isAdmin): ?>
<form method="post" class="row g-2 mb-4">
  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
  <input type="hidden" name="action" value="add">
  <div class="col-md-4"><input type="text" name="title" class="form-control" placeholder="Event title" required></div>
  <div class="col-md-3"><input type="date" name="event_date" class="form-control" required></div>
  <div class="col-md-3"><input type="text" name="venue" class="form-control" placeholder="Venue" required></div>
  <div class="col-md-2"><button type="submit" class="btn btn-success w-100">Add Event</button></div>
</form>
<?php endif; ?>

<table class="table table-bordered bg-white">
  <tr><th>ID</th><th>Title</th><th>Date</th><th>Venue</th><th>Registrations</th><?php if ($isAdmin): ?><th>Action</th><?php endif; ?></tr>
  <?php foreach ($events as $row): ?>
  <tr>
    <td><?= (int)$row["id"] ?></td>
    <td><?= e($row["title"]) ?></td>
    <td><?= e($row["event_date"]) ?></td>
    <td><?= e($row["venue"]) ?></td>
    <td><?= (int)$row["reg_count"] ?></td>
    <?php if ($isAdmin): ?>
    <td>
      <form method="post" onsubmit="return confirm('Delete this event?');">
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
