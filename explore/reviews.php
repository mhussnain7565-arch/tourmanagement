<?php
require_once '../includes/header.php';

// Fetch Latest Reviews
$query = "
    SELECT r.*, u.name as user_name, d.name as dest_name, d.hero_image, d.type as dest_type
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN destinations d ON r.destination_id = d.id
    ORDER BY r.created_at DESC
    LIMIT 20
";
$reviews = $pdo->query($query)->fetchAll();

// Calculate Global Stats
$stats = $pdo->query("
    SELECT 
    COUNT(*) as total_reviews, 
    AVG(rating) as avg_rating,
    COUNT(DISTINCT user_id) as total_reviewers
    FROM reviews
")->fetch();
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="text-center py-5">
            <div class="display-1 text-primary mb-4"><i class="bi bi-chat-heart"></i></div>
            <h1 class="fw-bold">Community Missions Feedback</h1>
            <p class="text-muted lead">Real experiences from travelers exploring the nebula.</p>
        </div>

        <!-- Global Stats -->
        <div class="row g-3 mb-5">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100">
                    <h2 class="fw-bold text-primary mb-0"><?= number_format($stats['avg_rating'] ?: 0, 1) ?></h2>
                    <div class="text-warning mb-2">
                        <?php for($i=1; $i<=5; $i++): ?>
                            <i class="bi bi-star<?= $i <= round($stats['avg_rating'] ?: 0) ? '-fill' : '' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <small class="text-muted text-uppercase fw-bold small">Global Rating</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100">
                    <h2 class="fw-bold text-success mb-0"><?= $stats['total_reviews'] ?></h2>
                    <i class="bi bi-chat-square-text-fill text-success opacity-50 mb-2 h4"></i>
                    <small class="text-muted text-uppercase fw-bold small">Total Reviews</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100">
                    <h2 class="fw-bold text-info mb-0"><?= $stats['total_reviewers'] ?></h2>
                    <i class="bi bi-people-fill text-info opacity-50 mb-2 h4"></i>
                    <small class="text-muted text-uppercase fw-bold small">Active Reviewers</small>
                </div>
            </div>
        </div>

        <h4 class="fw-bold mb-4">Latest Community Feed</h4>
        
        <?php if(empty($reviews)): ?>
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-body-tertiary">
                <h4 class="fw-bold mb-3">Nebula Insights Coming Soon!</h4>
                <p class="text-muted mb-0">We are currently collecting feedback from our latest tours. Check back soon to see what others are saying.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach($reviews as $r): 
                    $imgSrc = (strpos($r['hero_image'], 'http') === 0) ? $r['hero_image'] : BASE_URL . $r['hero_image'];
                ?>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                        <div class="row g-0 h-100">
                            <div class="col-4">
                                <img src="<?= $imgSrc ?>" class="w-100 h-100 object-fit-cover">
                            </div>
                            <div class="col-8 p-4">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="fw-bold mb-0"><?= htmlspecialchars($r['user_name']) ?></h6>
                                        <small class="text-muted">on <?= htmlspecialchars($r['dest_name']) ?></small>
                                    </div>
                                    <div class="text-warning small">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <i class="bi bi-star<?= $i <= $r['rating'] ? '-fill' : '' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="text-muted small mb-3">"<?= htmlspecialchars($r['comment']) ?>"</p>
                                <?php if($r['photo_path']): ?>
                                    <img src="<?= BASE_URL . $r['photo_path'] ?>" class="rounded-3 shadow-sm" style="height: 60px; width: 60px; object-fit: cover; cursor:pointer" onclick="window.open('<?= BASE_URL . $r['photo_path'] ?>')">
                                <?php endif; ?>
                                <div class="mt-3 text-end">
                                    <small class="text-muted italic small"><?= date('M d, Y', strtotime($r['created_at'])) ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['role']) && ($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'admin')): ?>
            <div class="mt-5 p-4 rounded-4 border border-primary border-dashed bg-primary bg-opacity-10 text-center">
                <h6 class="fw-bold text-primary mb-2">Admin Control Panel</h6>
                <p class="small text-muted mb-3">As an administrator, you can manage the current status of all destinations.</p>
                <a href="../dashboards/super_admin/current_status.php" class="btn btn-primary rounded-pill px-4">View Current Status</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
