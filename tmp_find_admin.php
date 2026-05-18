<?php
require_once 'core/db.php';
$u = $pdo->query("SELECT email FROM users WHERE role='super_admin' LIMIT 1")->fetch();
echo $u['email'] ?? 'No super admin found';
?>
