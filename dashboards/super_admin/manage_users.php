<?php 
require_once '../../includes/header.php'; 


// Fetch Roles for Dropdown
$roles = $pdo->query("SELECT * FROM sys_roles")->fetchAll();

// Handle Add User
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $pass = password_hash('123456', PASSWORD_DEFAULT); // Default pass
    
    $stmt = $pdo->prepare("INSERT INTO users (name, email, role, password) VALUES (?, ?, ?, ?)");
    try {
        $stmt->execute([$name, $email, $role, $pass]);
    } catch(Exception $e) { $error = "Email already exists."; }
}
?>

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Users List</h3>
        <button class="btn btn-primary btn-sm float-end" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus"></i> Add User
        </button>
    </div>
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $users = $pdo->query("SELECT u.*, r.role_name FROM users u JOIN sys_roles r ON u.role = r.role_key");
                while($u = $users->fetch()):
                    // Deterministic Color based on Role Name
                    $badgeClass = getRoleBadgeColor($u['role_name']); 
                ?>
                <tr>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($u['role_name']) ?></span></td>
                    <td><?= $u['is_active'] ? '<span class="text-success"><i class="bi bi-check-circle"></i> Active</span>' : '<span class="text-danger">Suspended</span>' ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Role</label>
                    <select name="role" class="form-select" required>
                        <?php foreach($roles as $r): ?>
                            <option value="<?= $r['role_key'] ?>"><?= $r['role_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save User</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>