<?php
require_once '../../includes/header.php';

// Check if super_admin or admin
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin')) {
    header("Location: ../../index.php");
    exit;
}

// Fetch all destinations
$query = "SELECT * FROM destinations WHERE is_deleted = 0 ORDER BY created_at DESC";
$destinations = $pdo->query($query)->fetchAll();

// Statistics
$totalDestinations = count($destinations);
$adventureTours = 0;
$relaxationTours = 0;
$educationalTours = 0;

foreach($destinations as $d) {
    if($d['category'] == 'Adventure') $adventureTours++;
    elseif($d['category'] == 'Relaxation') $relaxationTours++;
    elseif($d['category'] == 'Educational') $educationalTours++;
}
?>

<div class="row">
    <div class="col-12">
        <div class="p-4 rounded-4 mb-4" style="background: linear-gradient(135deg, var(--app-primary-blue) 0%, var(--app-accent-blue) 100%); color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-1">Current Status Overview</h2>
                    <p class="opacity-75 mb-0">Complete management console for all travel destinations and mission targets.</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-white text-primary px-3 py-2 rounded-pill fw-bold">Admin Only Access</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
            <i class="bi bi-geo-alt-fill text-primary h3"></i>
            <h5 class="fw-bold mb-0 mt-2"><?= $totalDestinations ?></h5>
            <small class="text-muted">Total Destinations</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
            <i class="bi bi-fire text-danger h3"></i>
            <h5 class="fw-bold mb-0 mt-2"><?= $adventureTours ?></h5>
            <small class="text-muted">Adventure Missions</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
            <i class="bi bi-water text-info h3"></i>
            <h5 class="fw-bold mb-0 mt-2"><?= $relaxationTours ?></h5>
            <small class="text-muted">Relaxation Gateways</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
            <i class="bi bi-mortarboard text-success h3"></i>
            <h5 class="fw-bold mb-0 mt-2"><?= $educationalTours ?></h5>
            <small class="text-muted">Educational Tours</small>
        </div>
    </div>
</div>

<div class="card card-outline card-primary shadow-lg border-0 rounded-4">
    <div class="card-header bg-transparent border-0 pt-4 px-4">
        <h3 class="card-title fw-bold"><i class="bi bi-table me-2"></i>Destination Inventory Status</h3>
        <div class="card-tools">
            <a href="manage_destinations.php" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Add New Destination
            </a>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 rounded-start">Destination</th>
                        <th class="border-0">Category</th>
                        <th class="border-0">Price / Duration</th>
                        <th class="border-0">Adventure Level</th>
                        <th class="border-0">Last Updated</th>
                        <th class="border-0 rounded-end text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($destinations as $dest): 
                        $imgSrc = (strpos($dest['hero_image'], 'http') === 0) ? $dest['hero_image'] : BASE_URL . $dest['hero_image'];
                        $badgeColor = 'success';
                        if($dest['intensity'] == 'Medium') $badgeColor = 'warning';
                        if($dest['intensity'] == 'High') $badgeColor = 'danger';
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?= $imgSrc ?>" class="rounded-3 me-3 shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">
                                <div>
                                    <h6 class="fw-bold mb-0"><?= htmlspecialchars($dest['name']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($dest['type']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 rounded-pill"><?= $dest['category'] ?></span>
                        </td>
                        <td>
                            <div class="fw-bold text-success"><?= $dest['price'] ?></div>
                            <small class="text-muted"><?= $dest['duration'] ?></small>
                        </td>
                        <td>
                            <span class="badge bg-<?= $badgeColor ?>-subtle text-<?= $badgeColor ?> border border-<?= $badgeColor ?>-subtle px-3 rounded-pill">
                                <?= $dest['intensity'] ?>
                            </span>
                        </td>
                        <td>
                            <small class="text-muted"><?= date('M d, Y', strtotime($dest['created_at'])) ?></small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="manage_destinations.php?edit_id=<?= $dest['id'] ?>" class="btn btn-outline-primary" title="Edit Full Details">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="../../explore/destinations.php" target="_blank" class="btn btn-outline-info" title="View on Website">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
