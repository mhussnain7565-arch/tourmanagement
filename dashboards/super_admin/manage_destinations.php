<?php 
require_once '../../core/session.php'; 

$success = '';
$error = '';

// Handle Actions (Add, Edit, Delete, Restore)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $name = $_POST['name'];
        $type = $_POST['type'];
        
        // Handle File Upload or URL
        $hero_image = $_POST['hero_image']; // Default to URL field
        if (isset($_FILES['hero_image_file']) && $_FILES['hero_image_file']['error'] === 0) {
            $filename = time() . '_' . basename($_FILES['hero_image_file']['name']);
            $upload_path = '../../uploads/destinations/' . $filename;
            if (move_uploaded_file($_FILES['hero_image_file']['tmp_name'], $upload_path)) {
                $hero_image = 'uploads/destinations/' . $filename;
            }
        }

        $duration = $_POST['duration'];
        $price = $_POST['price'];
        $intensity = $_POST['intensity'];
        $category = $_POST['category'];
        $gravity = $_POST['gravity_level'];
        $itinerary = $_POST['itinerary'];
        $route_data = $_POST['route_data'];
        $description = $_POST['description'];
        $environment = $_POST['environment'];
        $accommodation = $_POST['accommodation'];
        $activities = $_POST['activities']; // Should be JSON string

        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO destinations (name, type, hero_image, duration, price, intensity, category, gravity_level, itinerary, route_data, description, environment, accommodation, activities) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $res = $stmt->execute([$name, $type, $hero_image, $duration, $price, $intensity, $category, $gravity, $itinerary, $route_data, $description, $environment, $accommodation, $activities]);
            if ($res) $success = "Destination added successfully!";
            else $error = "Failed to add destination.";
        } else {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("UPDATE destinations SET name=?, type=?, hero_image=?, duration=?, price=?, intensity=?, category=?, gravity_level=?, itinerary=?, route_data=?, description=?, environment=?, accommodation=?, activities=? WHERE id=?");
            $res = $stmt->execute([$name, $type, $hero_image, $duration, $price, $intensity, $category, $gravity, $itinerary, $route_data, $description, $environment, $accommodation, $activities, $id]);
            if ($res) $success = "Destination updated successfully!";
            else $error = "Failed to update destination.";
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("UPDATE destinations SET is_deleted = 1 WHERE id = ?");
        if ($stmt->execute([$id])) $success = "Destination moved to trash.";
    } elseif ($action === 'restore') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("UPDATE destinations SET is_deleted = 0 WHERE id = ?");
        if ($stmt->execute([$id])) $success = "Destination restored successfully!";
    } elseif ($action === 'update_photo') {
        $id = $_POST['id'];
        $hero_image = $_POST['hero_image'] ?? ''; // Fallback URL
        
        if (isset($_FILES['hero_image_file']) && $_FILES['hero_image_file']['error'] === 0) {
            $filename = time() . '_' . basename($_FILES['hero_image_file']['name']);
            $upload_path = '../../uploads/destinations/' . $filename;
            if (move_uploaded_file($_FILES['hero_image_file']['tmp_name'], $upload_path)) {
                $hero_image = 'uploads/destinations/' . $filename;
            }
        }

        $stmt = $pdo->prepare("UPDATE destinations SET hero_image = ? WHERE id = ?");
        if ($stmt->execute([$hero_image, $id])) {
            header("Location: ../../explore/destinations.php?success=Photo+updated!");
            exit;
        }
    }
}

// Fetch all destinations
$destinations = $pdo->query("SELECT * FROM destinations ORDER BY is_deleted ASC, id DESC")->fetchAll();

require_once '../../includes/header.php'; 
?>

<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Destination Management</h3>
                <button class="btn btn-primary btn-sm float-end" data-bs-toggle="modal" data-bs-target="#destModal" onclick="prepareAdd()">
                    <i class="bi bi-plus-circle"></i> Add New Destination
                </button>
            </div>
            <div class="card-body">
                <?php if($success): ?> <div class="alert alert-success"><?= $success ?></div> <?php endif; ?>
                <?php if($error): ?> <div class="alert alert-danger"><?= $error ?></div> <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Preview</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($destinations as $d): ?>
                            <tr class="<?= $d['is_deleted'] ? 'table-light text-muted' : '' ?>">
                                <td>
                                    <img src="<?= htmlspecialchars($d['hero_image']) ?>" class="rounded" style="width: 50px; height: 50px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/50'">
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($d['name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($d['duration']) ?></small>
                                </td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($d['category']) ?></span></td>
                                <td><?= htmlspecialchars($d['type']) ?></td>
                                <td><?= htmlspecialchars($d['price']) ?></td>
                                <td>
                                    <?= $d['is_deleted'] ? 
                                        '<span class="badge bg-danger">Deleted</span>' : 
                                        '<span class="badge bg-success">Active</span>' 
                                    ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php if(!$d['is_deleted']): ?>
                                            <button class="btn btn-outline-primary" onclick='prepareEdit(<?= json_encode($d) ?>)'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" style="display:inline" onsubmit="return confirm('Move to trash?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" style="display:inline">
                                                <input type="hidden" name="action" value="restore">
                                                <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                                <button type="submit" class="btn btn-outline-success"><i class="bi bi-arrow-counterclockwise"></i> Restore</button>
                                            </form>
                                        <?php endif; ?>
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
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="destModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" class="modal-content" enctype="multipart/form-data">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="destId" value="">
            
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add New Destination</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mt-2">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Destination Name</label>
                        <input type="text" name="name" id="field_name" class="form-control" placeholder="e.g. Hunza Valley" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Type</label>
                        <input type="text" name="type" id="field_type" class="form-control" placeholder="e.g. Natural Paradise" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Gravity Level</label>
                        <input type="text" name="gravity_level" id="field_gravity" class="form-control" placeholder="e.g. 1.0g" required>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Hero Image</label>
                        <div class="input-group">
                            <input type="file" name="hero_image_file" class="form-control" id="field_image_file">
                            <span class="input-group-text">OR</span>
                            <input type="url" name="hero_image" id="field_image" class="form-control" placeholder="https:// external URL">
                        </div>
                        <small class="text-muted">Upload a file from your PC or paste an external link.</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Duration</label>
                        <input type="text" name="duration" id="field_duration" class="form-control" placeholder="e.g. 5 Days" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Price</label>
                        <input type="text" name="price" id="field_price" class="form-control" placeholder="e.g. Rs 50,000" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Adventure Level</label>
                        <select name="intensity" id="field_intensity" class="form-select" required>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" id="field_category" class="form-select" required>
                            <option value="Adventure">Adventure</option>
                            <option value="Relaxation">Relaxation</option>
                            <option value="Educational">Educational</option>
                        </select>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="field_desc" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Sightseeing / Environment</label>
                        <textarea name="environment" id="field_env" class="form-control" placeholder="Specific details..."></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Best Stays / Accommodation</label>
                        <textarea name="accommodation" id="field_acc" class="form-control" placeholder="Specific details..."></textarea>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Activities (JSON format)</label>
                        <input type="text" name="activities" id="field_acts" class="form-control" value='["Hiking", "Sightseeing", "Food Tour"]' required>
                        <small class="text-muted">Enter activities as a JSON array: ["Act 1", "Act 2"]</small>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Itinerary (Day-by-Day JSON)</label>
                        <textarea name="itinerary" id="field_itin" class="form-control" rows="4" placeholder='[{"day": 1, "morning": "...", "afternoon": "...", "night": "..."}]' required></textarea>
                        <small class="text-muted">Enter itinerary as a JSON array of objects with day, morning, afternoon, and night properties.</small>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Flight Path & POIs (JSON)</label>
                        <textarea name="route_data" id="field_route" class="form-control" rows="3" placeholder='{"path": [[lat, lng]], "pois": [{"name": "...", "lat": ..., "lng": ...}]}'></textarea>
                        <small class="text-muted">Enter coordinates for the flight path and clickable points of interest.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveBtn">Save Destination</button>
            </div>
        </form>
    </div>
</div>

<script>
    function prepareAdd() {
        document.getElementById('formAction').value = 'add';
        document.getElementById('modalTitle').innerText = 'Add New Destination';
        document.getElementById('saveBtn').innerText = 'Save Destination';
        document.getElementById('destId').value = '';
        // Clear fields
        ['name', 'type', 'image', 'duration', 'price', 'intensity', 'category', 'gravity', 'itin', 'route', 'desc', 'env', 'acc', 'acts'].forEach(f => {
            document.getElementById('field_' + f).value = (f === 'intensity' ? 'Low' : (f === 'category' ? 'Adventure' : (f === 'gravity' ? '1.0g' : (f === 'itin' ? '[]' : (f === 'route' ? '{}' : (f === 'acts' ? '["Hiking", "Sightseeing"]' : ''))))));
        });
    }

    function prepareEdit(d) {
        document.getElementById('formAction').value = 'edit';
        document.getElementById('modalTitle').innerText = 'Edit Destination: ' + d.name;
        document.getElementById('saveBtn').innerText = 'Update Destination';
        document.getElementById('destId').value = d.id;
        
        document.getElementById('field_name').value = d.name;
        document.getElementById('field_type').value = d.type;
        document.getElementById('field_image').value = d.hero_image;
        document.getElementById('field_duration').value = d.duration;
        document.getElementById('field_price').value = d.price;
        document.getElementById('field_intensity').value = d.intensity;
        document.getElementById('field_category').value = d.category;
        document.getElementById('field_gravity').value = d.gravity_level;
        document.getElementById('field_itin').value = d.itinerary;
        document.getElementById('field_route').value = d.route_data;
        document.getElementById('field_desc').value = d.description;
        document.getElementById('field_env').value = d.environment;
        document.getElementById('field_acc').value = d.accommodation;
        document.getElementById('field_acts').value = d.activities;

        new bootstrap.Modal(document.getElementById('destModal')).show();
    }

    // Auto-open edit modal if edit_id is in URL
    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const editId = urlParams.get('edit_id');
        if (editId) {
            const allDests = <?= json_encode($destinations) ?>;
            const target = allDests.find(d => d.id == editId);
            if (target) prepareEdit(target);
        }
    });
</script>

<?php require_once '../../includes/footer.php'; ?>
