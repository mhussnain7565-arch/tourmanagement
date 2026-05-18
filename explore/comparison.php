<?php 
require_once '../includes/header.php'; 

// Fetch all active destinations for selection
$destinations = $pdo->query("SELECT * FROM destinations WHERE is_deleted = 0 ORDER BY name ASC")->fetchAll();

$dest1 = null;
$dest2 = null;

if (isset($_GET['id1']) && isset($_GET['id2'])) {
    $stmt = $pdo->prepare("SELECT * FROM destinations WHERE id = ? AND is_deleted = 0");
    $stmt->execute([$_GET['id1']]);
    $dest1 = $stmt->fetch();
    
    $stmt->execute([$_GET['id2']]);
    $dest2 = $stmt->fetch();
}
?>

<style>
    .comparison-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .compare-header {
        background: rgba(var(--bs-primary-rgb), 0.1);
        padding: 2rem;
        border-radius: 20px;
        margin-bottom: 3rem;
        border: 1px solid rgba(var(--bs-primary-rgb), 0.2);
        backdrop-filter: blur(10px);
    }

    .selector-box {
        display: flex;
        gap: 1.5rem;
        align-items: center;
        justify-content: center;
    }

    .vs-circle {
        width: 50px;
        height: 50px;
        background: var(--bs-primary);
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        box-shadow: 0 0 20px rgba(var(--bs-primary-rgb), 0.5);
    }

    .comparison-table {
        background: rgba(255, 255, 255, 0.02);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .comparison-table th {
        background: rgba(var(--bs-primary-rgb), 0.05);
        color: var(--bs-primary);
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1.5px;
        padding: 1.5rem;
        width: 25%;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .comparison-table td {
        padding: 1.5rem;
        width: 37.5%;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        transition: background 0.3s ease;
    }

    .comparison-table tr:hover td {
        background: rgba(255, 255, 255, 0.01);
    }

    .dest-head-card {
        text-align: center;
        padding-bottom: 1rem;
    }

    .dest-head-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 15px;
        margin-bottom: 1rem;
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }

    .diff-highlight {
        color: var(--bs-warning);
        font-weight: 600;
    }

    .val-match {
        color: var(--bs-success);
    }
</style>

<div class="comparison-container">
    <div class="compare-header text-center shadow-sm">
        <h2 class="mb-4 fw-bold">Tour Comparison Tool</h2>
        <p class="text-muted mb-4 text-uppercase small letter-spacing-1">Select two interstellar journeys to compare technical specifications</p>
        
        <form action="" method="GET" class="selector-box">
            <select name="id1" class="form-select form-select-lg rounded-pill border-primary bg-transparent text-center" style="max-width: 350px;" required>
                <option value="">Select Tour 1</option>
                <?php foreach($destinations as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= (isset($_GET['id1']) && $_GET['id1'] == $d['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="vs-circle">VS</div>

            <select name="id2" class="form-select form-select-lg rounded-pill border-primary bg-transparent text-center" style="max-width: 350px;" required>
                <option value="">Select Tour 2</option>
                <?php foreach($destinations as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= (isset($_GET['id2']) && $_GET['id2'] == $d['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow">Compare</button>
        </form>
    </div>

    <?php if ($dest1 && $dest2): ?>
        <div class="comparison-table shadow-lg">
            <table class="table mb-0 w-100">
                <thead>
                    <tr>
                        <th>Specifications</th>
                        <td>
                            <div class="dest-head-card">
                                <img src="<?= htmlspecialchars($dest1['hero_image']) ?>" alt="">
                                <h4 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($dest1['name']) ?></h4>
                                <small class="text-muted"><?= htmlspecialchars($dest1['type']) ?></small>
                            </div>
                        </td>
                        <td>
                            <div class="dest-head-card">
                                <img src="<?= htmlspecialchars($dest2['hero_image']) ?>" alt="">
                                <h4 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($dest2['name']) ?></h4>
                                <small class="text-muted"><?= htmlspecialchars($dest2['type']) ?></small>
                            </div>
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>Category</th>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($dest1['category']) ?></span></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($dest2['category']) ?></span></td>
                    </tr>
                    <tr>
                        <th>Trip Duration</th>
                        <td class="<?= ($dest1['duration'] != $dest2['duration']) ? 'diff-highlight' : 'val-match' ?>">
                            <?= htmlspecialchars($dest1['duration']) ?>
                        </td>
                        <td class="<?= ($dest1['duration'] != $dest2['duration']) ? 'diff-highlight' : 'val-match' ?>">
                            <?= htmlspecialchars($dest2['duration']) ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Gravity Level</th>
                        <td class="<?= ($dest1['gravity_level'] != $dest2['gravity_level']) ? 'diff-highlight' : 'val-match' ?>">
                            <i class="bi bi-box-arrow-in-down me-2"></i><?= htmlspecialchars($dest1['gravity_level']) ?>
                        </td>
                        <td class="<?= ($dest1['gravity_level'] != $dest2['gravity_level']) ? 'diff-highlight' : 'val-match' ?>">
                            <i class="bi bi-box-arrow-in-down me-2"></i><?= htmlspecialchars($dest2['gravity_level']) ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Adventure Level</th>
                        <td>
                            <?php $badge = ($dest1['intensity'] == 'High') ? 'danger' : (($dest1['intensity'] == 'Medium') ? 'warning' : 'success'); ?>
                            <span class="badge bg-<?= $badge ?>"><?= htmlspecialchars($dest1['intensity']) ?></span>
                        </td>
                        <td>
                            <?php $badge = ($dest2['intensity'] == 'High') ? 'danger' : (($dest2['intensity'] == 'Medium') ? 'warning' : 'success'); ?>
                            <span class="badge bg-<?= $badge ?>"><?= htmlspecialchars($dest2['intensity']) ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th>Standard Cost</th>
                        <td class="h4 fw-bold text-success">
                            <?= htmlspecialchars($dest1['price']) ?>
                        </td>
                        <td class="h4 fw-bold text-success">
                            <?= htmlspecialchars($dest2['price']) ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Activities</th>
                        <td class="small text-muted">
                            <?php foreach(json_decode($dest1['activities']) as $a): ?>
                                <div class="mb-1"><i class="bi bi-check2 text-primary me-2"></i><?= $a ?></div>
                            <?php endforeach; ?>
                        </td>
                        <td class="small text-muted">
                            <?php foreach(json_decode($dest2['activities']) as $a): ?>
                                <div class="mb-1"><i class="bi bi-check2 text-primary me-2"></i><?= $a ?></div>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div class="p-5 text-center bg-body-tertiary">
                <div class="row">
                    <div class="col-md-6">
                        <a href="destinations.php" class="btn btn-outline-primary rounded-pill px-4">View Full Spec for <?= $dest1['name'] ?></a>
                    </div>
                    <div class="col-md-6">
                        <a href="destinations.php" class="btn btn-outline-primary rounded-pill px-4">View Full Spec for <?= $dest2['name'] ?></a>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif(isset($_GET['id1'])): ?>
        <div class="alert alert-info text-center py-5 rounded-4">
            <i class="bi bi-arrow-repeat spin d-block mb-3" style="font-size: 3rem;"></i>
            <h4>Please select a second tour to begin comparison.</h4>
        </div>
    <?php else: ?>
        <div class="text-center py-5 mt-5">
            <i class="bi bi-layers-half text-muted" style="font-size: 5rem; opacity: 0.3;"></i>
            <h3 class="mt-4 text-muted">Ready for Comparison</h3>
            <p class="text-muted">Select two destinations from the cloud above to see side-by-side analytics.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
