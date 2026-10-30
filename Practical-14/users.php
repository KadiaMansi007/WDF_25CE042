<?php
require_once "auth.php";
require_role($pdo, ["admin"]);

$pageTitle = "Users";
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_check();
    $id = (int)($_POST["id"] ?? 0);

    if ($id === (int)$_SESSION["user_id"]) {
        $message = "You cannot change your own status.";
    } else {
        $stmt = $pdo->prepare("SELECT email, is_active FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $target = $stmt->fetch();
        if ($target) {
            $newStatus = ((int)$target["is_active"] === 1) ? 0 : 1;
            $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?")->execute([$newStatus, $id]);
            $word = ($newStatus === 1) ? "Activated" : "Deactivated";
            audit($pdo, $_SESSION["user_id"], $word . " user " . $target["email"]);
            $message = $word . " " . $target["email"];
        }
    }
}

$users = $pdo->query("SELECT id, name, email, role, is_active, created_at FROM users ORDER BY id")->fetchAll();

require "header.php";
?>
<h2 class="mb-3">User Management</h2>
<?php if ($message !== ""): ?>
  <div class="alert alert-info"><?= e($message) ?></div>
<?php endif; ?>

<table class="table table-bordered bg-white">
  <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Action</th></tr>
  <?php foreach ($users as $row): ?>
  <tr>
    <td><?= (int)$row["id"] ?></td>
    <td><?= e($row["name"]) ?></td>
    <td><?= e($row["email"]) ?></td>
    <td><?= e($row["role"]) ?></td>
    <td>
      <?php if ((int)$row["is_active"] === 1): ?>
        <span class="badge bg-success">Active</span>
      <?php else: ?>
        <span class="badge bg-secondary">Inactive</span>
      <?php endif; ?>
    </td>
    <td>
      <?php if ((int)$row["id"] !== (int)$_SESSION["user_id"]): ?>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="id" value="<?= (int)$row["id"] ?>">
        <button type="submit" class="btn btn-sm btn-warning">
          <?= ((int)$row["is_active"] === 1) ? "Deactivate" : "Activate" ?>
        </button>
      </form>
      <?php else: ?>
        (you)
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php require "footer.php"; ?>
