<?php
require_once '../../includes/header.php';
require_once '../../includes/mailer.php';

$success = '';
$error = '';

$mailer = new Mailer($pdo);

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_template') {
        $id = $_POST['id'];
        $subject = $_POST['subject'];
        $body = $_POST['body'];

        $stmt = $pdo->prepare("UPDATE email_templates SET subject = ?, body = ? WHERE id = ?");
        if($stmt->execute([$subject, $body, $id])) $success = "Template updated successfully.";
    } elseif ($action === 'test_send') {
        $key = $_POST['template_key'];
        $email = $_POST['test_email'];
        
        if($mailer->send($key, $email, ['user_name' => 'Admin Test', 'tour_name' => 'Example Mission', 'travel_date' => date('Y-m-d'), 'guests' => 1, 'price' => 'Rs 0'])) {
            $success = "Email generated successfully for $email! You can now preview it in the Delivery Logs below.";
        } else {
            $error = "Failed to generate test email. Please check your template settings.";
        }
    }
}

// Fetch Data
$templates = $pdo->query("SELECT * FROM email_templates ORDER BY template_key ASC")->fetchAll();
$logs = $pdo->query("SELECT * FROM notification_logs ORDER BY sent_at DESC LIMIT 50")->fetchAll();
?>

<div class="row g-4">
    <div class="col-12">
        <div class="p-4 rounded-4 mb-4" style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-1">Email Trigger Control</h2>
                    <p class="opacity-75 mb-0">Automate your traveler communications and monitor delivery status.</p>
                </div>
                <i class="bi bi-envelope-heart-fill display-4 opacity-50"></i>
            </div>
        </div>
    </div>

    <!-- Template Management -->
    <div class="col-lg-7">
        <h4 class="fw-bold mb-4 px-2">Active Email Templates</h4>
        <?php if($success): ?> <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4 mx-2"><?= $success ?></div> <?php endif; ?>
        <?php if($error): ?> <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4 mx-2"><?= $error ?></div> <?php endif; ?>
        
        <div class="row g-3">
            <?php foreach($templates as $t): ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-primary-subtle text-primary mb-1"><?= $t['template_key'] ?></span>
                            <h5 class="fw-bold mb-0"><?= htmlspecialchars($t['subject']) ?></h5>
                        </div>
                        <button class="btn btn-light btn-sm rounded-pill px-3 shadow-sm" onclick='editTemplate(<?= json_encode($t) ?>)'>
                            <i class="bi bi-pencil-square me-1"></i> Edit
                        </button>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="p-3 bg-light rounded-3 small text-muted border border-dashed mb-3" style="max-height: 100px; overflow-y: auto;">
                            <?= $t['body'] ?>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted italic">Variables: <code class="text-primary"><?= $t['variables'] ?></code></small>
                            <button class="btn btn-outline-primary btn-xs rounded-pill" onclick="testSend('<?= $t['template_key'] ?>')">
                                <i class="bi bi-send me-1"></i> Test
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Notification Logs -->
    <div class="col-lg-5">
        <h4 class="fw-bold mb-4 px-2">Delivery Logs</h4>
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light sticky-top">
                            <tr>
                                <th class="ps-4">Recipient / Event</th>
                                <th>Status</th>
                                <th class="pe-4 text-end">Sent At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($logs as $l): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold small"><?= htmlspecialchars($l['recipient_email']) ?></div>
                                    <small class="text-muted"><?= $l['template_key'] ?></small>
                                </td>
                                <td>
                                    <?php if($l['status'] == 'Failed'): ?>
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-2 small" title="<?= htmlspecialchars($l['error_message']) ?>" style="cursor:help">
                                            Failed <i class="bi bi-info-circle ms-1"></i>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success rounded-pill px-2 small">Sent</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-end">
                                    <small class="text-muted d-block"><?= date('M d, H:i', strtotime($l['sent_at'])) ?></small>
                                    <button class="btn btn-link btn-xs p-0 text-decoration-none" onclick='viewLog(<?= json_encode($l) ?>)'>View Message</button>
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

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" class="modal-content shadow-lg border-0 rounded-4">
            <input type="hidden" name="action" value="update_template">
            <input type="hidden" name="id" id="editId">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-primary">Edit Email Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Email Subject</label>
                    <input type="text" name="subject" id="editSubject" class="form-control rounded-pill" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Email Body (HTML Supported)</label>
                    <textarea name="body" id="editBody" class="form-control rounded-4" rows="10" required></textarea>
                </div>
                <div class="p-3 bg-light rounded-4 border border-dashed">
                    <small class="text-muted fw-bold d-block mb-1">Available Variables:</small>
                    <div id="editVars" class="text-primary fw-bold"></div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow">Save Template</button>
            </div>
        </form>
    </div>
</div>

<!-- Test Send Modal -->
<div class="modal fade" id="testModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content shadow-lg border-0 rounded-4">
            <input type="hidden" name="action" value="test_send">
            <input type="hidden" name="template_key" id="testKey">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-primary">Test Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small">Send a sample email with dummy data to verify the layout.</p>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Recipient Email</label>
                    <input type="email" name="test_email" class="form-control rounded-pill" placeholder="e.g. admin@example.com" required>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow">Send Test Email</button>
            </div>
        </form>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-primary">Email Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <div class="mb-3 border-bottom pb-2">
                        <small class="text-muted d-block text-uppercase fw-bold x-small">Subject:</small>
                        <h5 class="fw-bold mb-0" id="viewSubject"></h5>
                    </div>
                    <div id="viewBody" class="p-4 bg-white rounded-4 shadow-inner border" style="min-height: 200px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function editTemplate(t) {
        document.getElementById('editId').value = t.id;
        document.getElementById('editSubject').value = t.subject;
        document.getElementById('editBody').value = t.body;
        document.getElementById('editVars').innerText = t.variables.split(',').map(v => '{{' + v + '}}').join(', ');
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    function testSend(key) {
        document.getElementById('testKey').value = key;
        new bootstrap.Modal(document.getElementById('testModal')).show();
    }

    function viewLog(l) {
        document.getElementById('viewSubject').innerText = l.subject;
        document.getElementById('viewBody').innerHTML = l.body || '<p class="text-muted">No content recorded for this older log entry.</p>';
        new bootstrap.Modal(document.getElementById('viewModal')).show();
    }
</script>

<?php require_once '../../includes/footer.php'; ?>
