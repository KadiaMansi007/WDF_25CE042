<?php
require_once "auth.php";
require_role($pdo, ["staff", "student"]);

$pageTitle = "Home";

$myRegs = [];
if ($_SESSION["role"] === "student") {
    $stmt = $pdo->prepare(
        "SELECT ev.title, ev.event_date, r.registered_at
         FROM students s
         JOIN registrations r ON r.student_id = s.id
         JOIN events ev ON ev.id = r.event_id
         WHERE s.user_id = ?
         ORDER BY r.registered_at DESC"
    );
    $stmt->execute([$_SESSION["user_id"]]);
    $myRegs = $stmt->fetchAll();
}

require "header.php";
?>
<h2>Welcome, <?= e($_SESSION["name"]) ?></h2>
<p>You are logged in as <b><?= e($_SESSION["role"]) ?></b>. The menu above shows only the pages allowed for your role.</p>

<?php if ($_SESSION["role"] === "student"): ?>
  <h4 class="mt-4">My Event Registrations</h4>
  <table class="table table-bordered bg-white">
    <tr><th>Event</th><th>Event Date</th><th>Registered On</th></tr>
    <?php foreach ($myRegs as $row): ?>
    <tr>
      <td><?= e($row["title"]) ?></td>
      <td><?= e($row["event_date"]) ?></td>
      <td><?= e($row["registered_at"]) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (count($myRegs) === 0): ?>
    <tr><td colspan="3">No registrations yet.</td></tr>
    <?php endif; ?>
  </table>
<?php else: ?>
  <div class="alert alert-info">Staff can view the Students and Events lists. Only admin can add or delete records.</div>
<?php endif; ?>
<?php require "footer.php"; ?>
