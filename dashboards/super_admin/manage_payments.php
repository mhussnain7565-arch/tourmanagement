<?php
require_once '../../includes/header.php';

$success = '';
$error = '';

// Handle Actions for Payment Methods
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_method' || $action === 'edit_method') {
        $name = $_POST['method_name'];
        $bank = $_POST['bank_name'] ?? '';
        $acc_name = $_POST['account_name'] ?? '';
        $acc_num = $_POST['account_number'] ?? '';
        $iban = $_POST['iban'] ?? '';
        $instr = $_POST['instructions'] ?? '';

        if ($action === 'add_method') {
            $stmt = $pdo->prepare("INSERT INTO payment_methods (method_name, bank_name, account_name, account_number, iban, instructions) VALUES (?,?,?,?,?,?)");
            if($stmt->execute([$name, $bank, $acc_name, $acc_num, $iban, $instr])) $success = "Payment method added.";
        } else {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("UPDATE payment_methods SET method_name=?, bank_name=?, account_name=?, account_number=?, iban=?, instructions=? WHERE id=?");
            if($stmt->execute([$name, $bank, $acc_name, $acc_num, $iban, $instr, $id])) $success = "Payment method updated.";
        }
    } elseif ($action === 'delete_method') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM payment_methods WHERE id = ?");
        if($stmt->execute([$id])) $success = "Payment method removed.";
    } elseif ($action === 'verify_payment') {
        $id = $_POST['id'];
        $status = $_POST['status']; // Verified or Rejected
        $stmt = $pdo->prepare("UPDATE payments SET status = ? WHERE id = ?");
        if($stmt->execute([$status, $id])) {
            // If verified, maybe update booking payment status too?
            if($status === 'Verified') {
                $p = $pdo->query("SELECT booking_id FROM payments WHERE id = $id")->fetch();
                $pdo->query("UPDATE bookings SET payment_status = 'Paid' WHERE id = {$p['booking_id']}");
            }
            $success = "Payment status updated to $status.";
        }
    }
}

// Fetch Data
$methods = $pdo->query("SELECT * FROM payment_methods ORDER BY id DESC")->fetchAll();
$userPayments = $pdo->query("
    SELECT p.*, u.name as user_name, b.destination_id, d.name as dest_name 
    FROM payments p
    JOIN users u ON p.user_id = u.id
    JOIN bookings b ON p.booking_id = b.id
    JOIN destinations d ON b.destination_id = d.id
    ORDER BY p.created_at DESC
")->fetchAll();
?>

<div class="row">
    <!-- Payment Methods Section -->
    <div class="col-md-5">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Bank Details & Methods</h3>
                <button class="btn btn-primary btn-sm float-end" data-bs-toggle="modal" data-bs-target="#methodModal" onclick="prepareAdd()">
                    <i class="bi bi-plus-circle"></i> Add New
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Method / Bank</th>
                                <th>Details</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($methods as $m): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($m['method_name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($m['bank_name']) ?></small>
                                </td>
                                <td>
                                    <small>
                                        Name: <?= htmlspecialchars($m['account_name']) ?><br>
                                        Acc: <?= htmlspecialchars($m['account_number']) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick='prepareEdit(<?= json_encode($m) ?>)'><i class="bi bi-pencil"></i></button>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('Remove this method?')">
                                            <input type="hidden" name="action" value="delete_method">
                                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
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
    </div>

    <!-- Verify User Payments Section -->
    <div class="col-md-7">
        <div class="card card-success card-outline">
            <div class="card-header"><h3 class="card-title">User Payment Verifications</h3></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>User / Booking</th>
                                <th>Amount / Method</th>
                                <th>Proof</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($userPayments as $up): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($up['user_name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($up['dest_name']) ?> (#<?= $up['booking_id'] ?>)</small>
                                </td>
                                <td>
                                    <span class="fw-bold text-success"><?= $up['amount'] ?></span><br>
                                    <small class="badge bg-secondary"><?= $up['payment_method_type'] ?></small>
                                </td>
                                <td>
                                    <?php if($up['proof_file']): ?>
                                        <a href="<?= BASE_URL . $up['proof_file'] ?>" target="_blank" class="btn btn-xs btn-outline-info">View Proof</a>
                                    <?php else: ?>
                                        <small class="text-muted">No file</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                        $sCls = 'bg-warning';
                                        if($up['status'] == 'Verified') $sCls = 'bg-success';
                                        if($up['status'] == 'Rejected') $sCls = 'bg-danger';
                                    ?>
                                    <span class="badge <?= $sCls ?>"><?= $up['status'] ?></span>
                                </td>
                                <td>
                                    <?php if($up['status'] == 'Pending'): ?>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-success" onclick="verifyPayment(<?= $up['id'] ?>, 'Verified')" title="Approve"><i class="bi bi-check-lg"></i></button>
                                        <button class="btn btn-danger" onclick="verifyPayment(<?= $up['id'] ?>, 'Rejected')" title="Reject"><i class="bi bi-x-lg"></i></button>
                                    </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Method Modal -->
<div class="modal fade" id="methodModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <input type="hidden" name="action" id="methodAction" value="add_method">
            <input type="hidden" name="id" id="methodId" value="">
            <div class="modal-header">
                <h5 class="modal-title" id="methodTitle">Add Payment Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Method Name (e.g. Bank Transfer)</label>
                    <input type="text" name="method_name" id="field_method_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Bank Name</label>
                    <input type="text" name="bank_name" id="field_bank_name" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Account Name</label>
                    <input type="text" name="account_name" id="field_account_name" class="form-control">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Account Number</label>
                        <input type="text" name="account_number" id="field_account_number" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>IBAN</label>
                        <input type="text" name="iban" id="field_iban" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label>Instructions for User</label>
                    <textarea name="instructions" id="field_instructions" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" id="methodBtn">Save Method</button>
            </div>
        </form>
    </div>
</div>

<script>
    function prepareAdd() {
        document.getElementById('methodAction').value = 'add_method';
        document.getElementById('methodTitle').innerText = 'Add Payment Method';
        document.getElementById('methodBtn').innerText = 'Save Method';
        document.getElementById('methodId').value = '';
        ['method_name', 'bank_name', 'account_name', 'account_number', 'iban', 'instructions'].forEach(f => {
            document.getElementById('field_' + f).value = '';
        });
    }

    function prepareEdit(m) {
        document.getElementById('methodAction').value = 'edit_method';
        document.getElementById('methodTitle').innerText = 'Edit Method';
        document.getElementById('methodBtn').innerText = 'Update Method';
        document.getElementById('methodId').value = m.id;
        document.getElementById('field_method_name').value = m.method_name;
        document.getElementById('field_bank_name').value = m.bank_name;
        document.getElementById('field_account_name').value = m.account_name;
        document.getElementById('field_account_number').value = m.account_number;
        document.getElementById('field_iban').value = m.iban;
        document.getElementById('field_instructions').value = m.instructions;
        new bootstrap.Modal(document.getElementById('methodModal')).show();
    }

    function verifyPayment(id, status) {
        if(confirm(`Set payment status to ${status}?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'verify_payment';
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
