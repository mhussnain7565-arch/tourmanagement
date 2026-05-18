<?php
require_once '../../includes/header.php';

$success = '';
$error = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $dest_id = $_POST['destination_id'];
        $date = $_POST['tour_date'];
        $slots = $_POST['total_slots'];
        $status = $_POST['status'];

        try {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO tour_availability (destination_id, tour_date, total_slots, status) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE total_slots=?, status=?");
                if($stmt->execute([$dest_id, $date, $slots, $status, $slots, $status])) $success = "Availability set for $date.";
            } else {
                $id = $_POST['id'];
                $stmt = $pdo->prepare("UPDATE tour_availability SET destination_id=?, tour_date=?, total_slots=?, status=? WHERE id=?");
                if($stmt->execute([$dest_id, $date, $slots, $status, $id])) $success = "Availability updated.";
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM tour_availability WHERE id = ?");
        if($stmt->execute([$id])) $success = "Availability removed.";
    }
}

// Fetch Destinations for Dropdown
$destinations = $pdo->query("SELECT id, name, type FROM destinations WHERE is_deleted = 0 ORDER BY name")->fetchAll();

// Fetch Availability Data
$availability = $pdo->query("
    SELECT ta.*, d.name as dest_name, d.type as dest_type, d.hero_image 
    FROM tour_availability ta
    JOIN destinations d ON ta.destination_id = d.id
    ORDER BY ta.tour_date ASC
")->fetchAll();
?>

<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.3);
    }
    .availability-card {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .availability-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
    }
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .dest-thumb {
        width: 45px;
        height: 45px;
        object-fit: cover;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .date-box {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 15px;
        text-align: center;
        min-width: 80px;
    }
    .date-box .month { font-size: 0.7rem; color: #6c757d; font-weight: 700; text-transform: uppercase; }
    .date-box .day { font-size: 1.2rem; font-weight: 800; color: var(--app-primary-blue); line-height: 1; }
    
    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label {
        color: var(--app-primary-blue);
    }
</style>

<div class="row g-4">
    <div class="col-12">
        <div class="p-5 rounded-5 mb-4 position-relative overflow-hidden shadow-lg" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
            <div class="position-relative z-index-1 text-white">
                <h1 class="display-5 fw-bold mb-2">Tour Availability Engine</h1>
                <p class="lead opacity-75 mb-0">Control your mission parameters, seasonal tracking, and slot allocation with precision.</p>
            </div>
            <i class="bi bi-calendar3-range position-absolute opacity-10" style="font-size: 15rem; right: -2rem; bottom: -4rem; color: white;"></i>
        </div>
    </div>

    <!-- Quick Action Bar -->
    <div class="col-lg-4">
        <div class="availability-card card shadow-sm rounded-5 border-0 overflow-hidden">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h4 class="fw-bold mb-0" id="formTitle">Launch New Slot</h4>
                <p class="text-muted small">Open specific dates for travelers.</p>
            </div>
            <div class="card-body p-4">
                <form method="POST" id="availabilityForm">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="availId" value="">
                    
                    <div class="form-floating mb-3">
                        <select name="destination_id" id="field_dest_id" class="form-select border-0 bg-light rounded-4" required>
                            <option value="">Choose Target</option>
                            <?php foreach($destinations as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?> (<?= $d['type'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <label>Select Destination</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="date" name="tour_date" id="field_date" class="form-control border-0 bg-light rounded-4" required min="<?= date('Y-m-d') ?>">
                        <label>Target Mission Date</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="number" name="total_slots" id="field_slots" class="form-control border-0 bg-light rounded-4" value="10" min="1" required>
                        <label>Total Traveler Capacity</label>
                    </div>

                    <div class="form-floating mb-4">
                        <select name="status" id="field_status" class="form-select border-0 bg-light rounded-4">
                            <option value="Open">Operational (Open)</option>
                            <option value="Closed">Deactivated (Closed)</option>
                            <option value="Full">At Capacity (Full)</option>
                        </select>
                        <label>Mission Status</label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg rounded-pill w-100 py-3 fw-bold shadow-lg" id="submitBtn">
                        <i class="bi bi-rocket-takeoff me-2"></i> Deploy Slots
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill w-100 mt-3 border-0 d-none" id="cancelBtn" onclick="resetForm()">
                        Abort Edit
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Availability Feed -->
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4 px-2">
            <h4 class="fw-bold mb-0">Scheduled Missions</h4>
            <div class="btn-group">
                <button class="btn btn-sm btn-light border rounded-pill px-3 shadow-sm active">All Dates</button>
                <button class="btn btn-sm btn-light border rounded-pill px-3 shadow-sm ms-2">This Month</button>
            </div>
        </div>

        <?php if($success): ?> <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4 mx-2"><i class="bi bi-check-circle-fill me-2"></i><?= $success ?></div> <?php endif; ?>
        <?php if($error): ?> <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4 mx-2"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?></div> <?php endif; ?>

        <div class="row g-3">
            <?php if(empty($availability)): ?>
                <div class="col-12 text-center py-5">
                    <img src="https://illustrations.popsy.co/white/calendar.svg" style="width: 200px;" class="mb-4 opacity-50">
                    <h5 class="text-muted">No scheduled missions found. Start by adding one.</h5>
                </div>
            <?php endif; ?>

            <?php foreach($availability as $a): 
                $remaining = $a['total_slots'] - $a['booked_slots'];
                $progress = ($a['booked_slots'] / $a['total_slots']) * 100;
                $pColor = 'primary';
                if($progress > 70) $pColor = 'warning';
                if($progress >= 100) $pColor = 'danger';
                
                $imgSrc = (strpos($a['hero_image'], 'http') === 0) ? $a['hero_image'] : BASE_URL . $a['hero_image'];
            ?>
            <div class="col-12">
                <div class="availability-card card shadow-sm rounded-4 border-0 p-3">
                    <div class="row align-items-center g-3">
                        <div class="col-auto">
                            <div class="date-box shadow-sm border border-light">
                                <div class="month"><?= date('M', strtotime($a['tour_date'])) ?></div>
                                <div class="day"><?= date('d', strtotime($a['tour_date'])) ?></div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="d-flex align-items-center mb-1">
                                <img src="<?= $imgSrc ?>" class="dest-thumb me-3">
                                <div>
                                    <h6 class="fw-bold mb-0"><?= htmlspecialchars($a['dest_name']) ?></h6>
                                    <small class="text-muted small"><?= $a['dest_type'] ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="fw-bold small text-muted">SLOTS: <?= $a['booked_slots'] ?>/<?= $a['total_slots'] ?></small>
                                <small class="text-<?= $remaining > 0 ? 'success' : 'danger' ?> fw-bold small"><?= $remaining ?> FREE</small>
                            </div>
                            <div class="progress rounded-pill shadow-sm" style="height: 8px;">
                                <div class="progress-bar bg-<?= $pColor ?>" role="progressbar" style="width: <?= $progress ?>%"></div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <?php 
                                $sBadge = 'bg-success-subtle text-success border-success-subtle';
                                if($a['status'] == 'Closed') $sBadge = 'bg-secondary-subtle text-secondary border-secondary-subtle';
                                if($a['status'] == 'Full' || $remaining <= 0) $sBadge = 'bg-danger-subtle text-danger border-danger-subtle';
                            ?>
                            <span class="status-badge border <?= $sBadge ?>"><?= $a['status'] ?></span>
                        </div>
                        <div class="col-auto">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm rounded-circle shadow-sm" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 p-2">
                                    <li><button class="dropdown-item rounded-3" onclick='prepareEdit(<?= json_encode($a) ?>)'><i class="bi bi-pencil me-2"></i> Edit Capacity</button></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" onsubmit="return confirm('Abort this mission schedule?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                            <button type="submit" class="dropdown-item text-danger rounded-3"><i class="bi bi-trash me-2"></i> Remove</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    function prepareEdit(a) {
        document.getElementById('formAction').value = 'edit';
        document.getElementById('availId').value = a.id;
        document.getElementById('formTitle').innerText = 'Modify Mission';
        document.getElementById('submitBtn').innerText = 'Update Parameters';
        document.getElementById('submitBtn').className = 'btn btn-warning btn-lg rounded-pill w-100 py-3 fw-bold shadow-lg';
        document.getElementById('cancelBtn').classList.remove('d-none');
        
        document.getElementById('field_dest_id').value = a.destination_id;
        document.getElementById('field_date').value = a.tour_date;
        document.getElementById('field_slots').value = a.total_slots;
        document.getElementById('field_status').value = a.status;
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetForm() {
        document.getElementById('formAction').value = 'add';
        document.getElementById('availId').value = '';
        document.getElementById('formTitle').innerText = 'Launch New Slot';
        document.getElementById('submitBtn').innerText = 'Deploy Slots';
        document.getElementById('submitBtn').className = 'btn btn-primary btn-lg rounded-pill w-100 py-3 fw-bold shadow-lg';
        document.getElementById('cancelBtn').classList.add('d-none');
        document.getElementById('availabilityForm').reset();
    }
</script>

<?php require_once '../../includes/footer.php'; ?>
