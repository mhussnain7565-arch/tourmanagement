<?php
require_once '../../includes/header.php';

$success = '';
$error = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $name = $_POST['name'];
        $code = $_POST['code'];
        $flag = $_POST['flag_url'];
        $active = isset($_POST['is_active']) ? 1 : 0;
        $rtl = isset($_POST['is_rtl']) ? 1 : 0;

        try {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO languages (name, code, flag_url, is_active, is_rtl) VALUES (?,?,?,?,?)");
                if($stmt->execute([$name, $code, $flag, $active, $rtl])) $success = "Language $name added.";
            } else {
                $id = $_POST['id'];
                $stmt = $pdo->prepare("UPDATE languages SET name=?, code=?, flag_url=?, is_active=?, is_rtl=? WHERE id=?");
                if($stmt->execute([$name, $code, $flag, $active, $rtl, $id])) $success = "Language updated.";
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM languages WHERE id = ?");
        if($stmt->execute([$id])) $success = "Language removed.";
    }
}

// Fetch Languages
$languages = $pdo->query("SELECT * FROM languages ORDER BY name ASC")->fetchAll();
?>

<div class="row g-4">
    <div class="col-12">
        <div class="p-4 rounded-4 mb-4" style="background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%); color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-1">Global Language Manager</h2>
                    <p class="opacity-75 mb-0">Expand your system's reach by managing international tongue support.</p>
                </div>
                <i class="bi bi-globe display-4 opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0" id="formTitle">Add New Language</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" id="langForm">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="langId" value="">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Language Name</label>
                        <input type="text" name="name" id="field_name" class="form-control rounded-pill" placeholder="e.g. Italian" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Language Code (ISO)</label>
                        <input type="text" name="code" id="field_code" class="form-control rounded-pill" placeholder="e.g. it" required>
                        <small class="text-muted italic small">Must match Google Translate codes.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Flag URL</label>
                        <input type="url" name="flag_url" id="field_flag" class="form-control rounded-pill" placeholder="https://flagcdn.com/w80/it.png">
                    </div>

                    <div class="d-flex gap-3 mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="field_active" checked>
                            <label class="form-check-label small fw-bold text-muted text-uppercase">Active</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_rtl" id="field_rtl">
                            <label class="form-check-label small fw-bold text-muted text-uppercase">RTL Mode</label>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill py-2 fw-bold shadow" id="submitBtn">Save Language</button>
                        <button type="button" class="btn btn-light rounded-pill py-2 d-none" id="cancelBtn" onclick="resetForm()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <?php if($success): ?> <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4"><?= $success ?></div> <?php endif; ?>
        <?php if($error): ?> <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4"><?= $error ?></div> <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Language</th>
                            <th>Code</th>
                            <th>Direction</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($languages as $l): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <img src="<?= $l['flag_url'] ?>" class="rounded-1 me-3 shadow-sm" style="width: 30px; height: 20px; object-fit: cover;">
                                    <span class="fw-bold"><?= htmlspecialchars($l['name']) ?></span>
                                </div>
                            </td>
                            <td><code class="text-primary fw-bold"><?= $l['code'] ?></code></td>
                            <td>
                                <span class="badge bg-<?= $l['is_rtl'] ? 'warning' : 'info' ?>-subtle text-<?= $l['is_rtl'] ? 'warning' : 'info' ?> rounded-pill px-2 small">
                                    <?= $l['is_rtl'] ? 'RTL (Right-to-Left)' : 'LTR (Left-to-Right)' ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $l['is_active'] ? 'success' : 'secondary' ?>-subtle text-<?= $l['is_active'] ? 'success' : 'secondary' ?> rounded-pill px-2 small">
                                    <?= $l['is_active'] ? 'Available' : 'Disabled' ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick='prepareEdit(<?= json_encode($l) ?>)'><i class="bi bi-pencil"></i></button>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('Remove this language?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $l['id'] ?>">
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

<script>
    function prepareEdit(l) {
        document.getElementById('formAction').value = 'edit';
        document.getElementById('langId').value = l.id;
        document.getElementById('formTitle').innerText = 'Edit Language';
        document.getElementById('submitBtn').innerText = 'Update Language';
        document.getElementById('submitBtn').className = 'btn btn-warning rounded-pill py-2 fw-bold text-dark';
        document.getElementById('cancelBtn').classList.remove('d-none');
        
        document.getElementById('field_name').value = l.name;
        document.getElementById('field_code').value = l.code;
        document.getElementById('field_flag').value = l.flag_url;
        document.getElementById('field_active').checked = l.is_active == 1;
        document.getElementById('field_rtl').checked = l.is_rtl == 1;
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetForm() {
        document.getElementById('formAction').value = 'add';
        document.getElementById('langId').value = '';
        document.getElementById('formTitle').innerText = 'Add New Language';
        document.getElementById('submitBtn').innerText = 'Save Language';
        document.getElementById('submitBtn').className = 'btn btn-primary rounded-pill py-2 fw-bold shadow';
        document.getElementById('cancelBtn').classList.add('d-none');
        document.getElementById('langForm').reset();
    }
</script>

<?php require_once '../../includes/footer.php'; ?>
