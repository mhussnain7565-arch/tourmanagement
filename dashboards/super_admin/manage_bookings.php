<?php
require_once '../../includes/header.php';

// Handle Actions (Approve, Cancel, Delete, Edit)
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? '';

    if ($action === 'update_status') {
        $status = $_POST['status'];
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $id])) $success = "Booking status updated to $status.";
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        if ($stmt->execute([$id])) $success = "Booking removed from system.";
    } elseif ($action === 'edit') {
        $travel_date = $_POST['travel_date'];
        $guests = $_POST['guests'];
        $stmt = $pdo->prepare("UPDATE bookings SET travel_date = ?, guests = ? WHERE id = ?");
        if ($stmt->execute([$travel_date, $guests, $id])) $success = "Booking details updated.";
    }
}

// Fetch all bookings with user and destination info
$query = "
    SELECT b.*, u.name as user_name, u.email as user_email, d.name as dest_name 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN destinations d ON b.destination_id = d.id
    ORDER BY b.created_at DESC
";
$bookings = $pdo->query($query)->fetchAll();
?>

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Booking Management</h3>
    </div>
    <div class="card-body">
        <?php if($success): ?> <div class="alert alert-success"><?= $success ?></div> <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>User</th>
                        <th>Destination</th>
                        <th>Travel Date</th>
                        <th>Guests</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($bookings as $b): ?>
                    <tr>
                        <td>#<?= $b['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($b['user_name']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($b['user_email']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($b['dest_name']) ?></td>
                        <td><?= date('M d, Y', strtotime($b['travel_date'])) ?></td>
                        <td><?= $b['guests'] ?></td>
                        <td class="fw-bold text-success"><?= $b['total_price'] ?></td>
                        <td>
                            <?php 
                                $statusClass = 'bg-warning';
                                if($b['status'] == 'Confirmed') $statusClass = 'bg-success';
                                if($b['status'] == 'Cancelled') $statusClass = 'bg-danger';
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= $b['status'] ?></span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal" onclick='prepareEdit(<?= json_encode($b) ?>)'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                
                                <button class="btn btn-outline-success" onclick="updateStatus(<?= $b['id'] ?>, 'Confirmed')" title="Confirm">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                                
                                <button class="btn btn-outline-warning" onclick="updateStatus(<?= $b['id'] ?>, 'Cancelled')" title="Cancel">
                                    <i class="bi bi-x-circle"></i>
                                </button>

                                <form method="POST" style="display:inline" onsubmit="return confirm('Delete this booking permanently?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="editId">
            <div class="modal-header">
                <h5 class="modal-title">Edit Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Travel Date</label>
                    <input type="date" name="travel_date" id="editDate" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Number of Guests</label>
                    <input type="number" name="guests" id="editGuests" class="form-control" min="1" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function prepareEdit(b) {
        document.getElementById('editId').value = b.id;
        document.getElementById('editDate').value = b.travel_date;
        document.getElementById('editGuests').value = b.guests;
    }

    function updateStatus(id, status) {
        if(confirm(`Set booking status to ${status}?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'update_status';
            form.appendChild(actionInput);
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            form.appendChild(idInput);
            
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = status;
            form.appendChild(statusInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<?php require_once '../../includes/footer.php'; ?>
