<?php
require_once "auth.php";
require_role($pdo, ["admin"]);

$pageTitle = "Admin Dashboard";

// statistics using COUNT()
$userCount = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeUsers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
$studentCount = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$eventCount = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$registrationCount = (int)$pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();

// latest 5 registrations
$latest = $pdo->query(
    "SELECT r.id, s.name AS student_name, ev.title AS event_title, r.registered_at
     FROM registrations r
     JOIN students s ON s.id = r.student_id
     JOIN events ev ON ev.id = r.event_id
     ORDER BY r.registered_at DESC
     LIMIT 5"
)->fetchAll();

// registrations per month for the chart
$monthly = $pdo->query(
    "SELECT DATE_FORMAT(registered_at, '%Y-%m') AS ym, COUNT(*) AS total
     FROM registrations
     GROUP BY DATE_FORMAT(registered_at, '%Y-%m')
     ORDER BY ym"
)->fetchAll();
$labels = array_column($monthly, "ym");
$totals = array_map("intval", array_column($monthly, "total"));

// record this visit first so it shows in the list below
audit($pdo, $_SESSION["user_id"], "Viewed admin dashboard");

// last 10 audit records
$recent = $pdo->query(
    "SELECT a.id, COALESCE(u.name, 'Unknown') AS user_name, a.action, a.created_at
     FROM audit_logs a
     LEFT JOIN users u ON u.id = a.user_id
     ORDER BY a.id DESC
     LIMIT 10"
)->fetchAll();

require "header.php";
?>
<h2 class="mb-3">System Statistics</h2>
<div class="row g-3 mb-4">
  <div class="col-6 col-md">
    <div class="card text-center"><div class="card-body">
      <h6 class="text-muted">Total Users</h6><h2><?= $userCount ?></h2>
    </div></div>
  </div>
  <div class="col-6 col-md">
    <div class="card text-center"><div class="card-body">
      <h6 class="text-muted">Active Users</h6><h2><?= $activeUsers ?></h2>
    </div></div>
  </div>
  <div class="col-6 col-md">
    <div class="card text-center"><div class="card-body">
      <h6 class="text-muted">Students</h6><h2><?= $studentCount ?></h2>
    </div></div>
  </div>
  <div class="col-6 col-md">
    <div class="card text-center"><div class="card-body">
      <h6 class="text-muted">Events</h6><h2><?= $eventCount ?></h2>
    </div></div>
  </div>
  <div class="col-6 col-md">
    <div class="card text-center"><div class="card-body">
      <h6 class="text-muted">Registrations</h6><h2><?= $registrationCount ?></h2>
    </div></div>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-lg-6">
    <div class="card"><div class="card-body">
      <h5 class="card-title">Registrations by Month</h5>
      <canvas id="regChart" height="200"></canvas>
    </div></div>
  </div>
  <div class="col-lg-6">
    <div class="card"><div class="card-body">
      <h5 class="card-title">Latest Registrations</h5>
      <table class="table table-sm table-striped">
        <tr><th>Student</th><th>Event</th><th>Date</th></tr>
        <?php foreach ($latest as $row): ?>
        <tr>
          <td><?= e($row["student_name"]) ?></td>
          <td><?= e($row["event_title"]) ?></td>
          <td><?= e($row["registered_at"]) ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div></div>
  </div>
</div>

<h2 class="mb-3">Recent Audit Activity</h2>
<table class="table table-bordered bg-white">
  <tr><th>ID</th><th>User</th><th>Action</th><th>Time</th></tr>
  <?php foreach ($recent as $row): ?>
  <tr>
    <td><?= (int)$row["id"] ?></td>
    <td><?= e($row["user_name"]) ?></td>
    <td><?= e($row["action"]) ?></td>
    <td><?= e($row["created_at"]) ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById("regChart"), {
    type: "bar",
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: "Registrations",
            data: <?= json_encode($totals) ?>,
            backgroundColor: "#0d6efd"
        }]
    },
    options: {
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
</script>
<?php require "footer.php"; ?>
