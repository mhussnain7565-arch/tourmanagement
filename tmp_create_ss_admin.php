<?php
require_once 'core/db.php';
$pass = password_hash('admin123', PASSWORD_DEFAULT);
try {
    $pdo->prepare("INSERT IGNORE INTO users (name, email, password, role, is_active) VALUES ('Screenshot Admin', 'ss_admin@test.com', ?, 'super_admin', 1)")->execute([$pass]);
    echo "Temporary admin created: ss_admin@test.com / admin123";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
