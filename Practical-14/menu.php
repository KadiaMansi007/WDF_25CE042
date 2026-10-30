<?php
// Every menu item lists the roles that are allowed to see it.
$menuItems = [
    ["Dashboard",  "admin.php",      ["admin"]],
    ["Home",       "home.php",       ["staff", "student"]],
    ["Students",   "students.php",   ["admin", "staff"]],
    ["Events",     "events.php",     ["admin", "staff", "student"]],
    ["Users",      "users.php",      ["admin"]],
    ["Audit Logs", "audit_logs.php", ["admin"]],
    ["Logout",     "logout.php",     ["admin", "staff", "student"]]
];

function render_menu($items, $role)
{
    $current = basename($_SERVER["PHP_SELF"]);
    foreach ($items as $item) {
        if (!in_array($role, $item[2], true)) {
            continue;
        }
        $active = ($current === $item[1]) ? " active" : "";
        echo '<li class="nav-item"><a class="nav-link' . $active . '" href="' . $item[1] . '">' . $item[0] . '</a></li>';
    }
}
