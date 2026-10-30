<?php
require_once "auth.php";
require_role($pdo, ["admin"]);

$pageTitle = "Audit Logs";

$logs = $pdo->query(
    "SELECT a.id, COALESCE(u.name, 'Unknown') AS user_name, a.action, a.created_at
     FROM audit_logs a
     LEFT JOIN users u ON u.id = a.user_id
     ORDER BY a.id DESC
     LIMIT 100"
)->fetchAll();

require "header.php";
?>
<h2 class="mb-3">Audit Logs (latest 100)</h2>
<table class="table table-bordered table-striped bg-white">
  <tr><th>ID</th><th>User</th><th>Action</th><th>Time</th></tr>
  <?php foreach ($logs as $row): ?>
  <tr>
    <td><?= (int)$row["id"] ?></td>
    <td><?= e($row["user_name"]) ?></td>
    <td><?= e($row["action"]) ?></td>
    <td><?= e($row["created_at"]) ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php require "footer.php"; ?>
