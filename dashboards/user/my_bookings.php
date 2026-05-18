<?php
require_once '../../includes/header.php';

$userId = $_SESSION['user_id'];

// Fetch user's bookings
$query = "
    SELECT b.*, d.name as dest_name, d.hero_image, d.type as dest_type
    FROM bookings b
    JOIN destinations d ON b.destination_id = d.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);
$myBookings = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="p-4 rounded-4 mb-4" style="background: linear-gradient(135deg, var(--app-primary-blue) 0%, var(--app-accent-blue) 100%); color: white;">
            <h2 class="fw-bold mb-1">My Booked Journeys</h2>
            <p class="opacity-75 mb-0">Track your upcoming adventures and mission status.</p>
        </div>
    </div>

    <?php if(empty($myBookings)): ?>
        <div class="col-12 text-center py-5">
            <div class="display-1 text-muted mb-4"><i class="bi bi-calendar-x"></i></div>
            <h3>No missions booked yet.</h3>
            <p class="text-muted">Explore our destinations and start your first adventure!</p>
            <a href="../../explore/destinations.php" class="btn btn-primary rounded-pill px-5 py-2 mt-3 shadow-lg">Browse Destinations</a>
        </div>
    <?php endif; ?>

    <?php foreach($myBookings as $b): 
        $imgSrc = (strpos($b['hero_image'], 'http') === 0) ? $b['hero_image'] : BASE_URL . $b['hero_image'];
        $statusClass = 'bg-warning-subtle text-warning border-warning';
        if($b['status'] == 'Confirmed') $statusClass = 'bg-success-subtle text-success border-success';
        if($b['status'] == 'Cancelled') $statusClass = 'bg-danger-subtle text-danger border-danger';
    ?>
    <div class="col-md-6 col-xl-4 mb-4">
        <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden" style="transition: transform 0.3s ease;">
            <div style="height: 150px; position: relative;">
                <img src="<?= $imgSrc ?>" class="w-100 h-100 object-fit-cover">
                <div class="position-absolute top-0 end-0 m-3">
                    <span class="badge border py-2 px-3 rounded-pill <?= $statusClass ?>">
                        <i class="bi bi-circle-fill me-1 small"></i> <?= $b['status'] ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <small class="text-primary fw-bold text-uppercase"><?= $b['dest_type'] ?></small>
                        <h4 class="fw-bold mb-0"><?= htmlspecialchars($b['dest_name']) ?></h4>
                    </div>
                </div>
                
                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <div class="p-2 rounded-3 bg-light border">
                            <small class="text-muted d-block">Travel Date</small>
                            <span class="fw-bold"><?= date('M d, Y', strtotime($b['travel_date'])) ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded-3 bg-light border">
                            <small class="text-muted d-block">Guests</small>
                            <span class="fw-bold"><?= $b['guests'] ?> Person<?= $b['guests'] > 1 ? 's' : '' ?></span>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Total Paid/Due</small>
                        <span class="h5 fw-bold text-success mb-0"><?= $b['total_price'] ?></span>
                    </div>
                    <?php if($b['payment_status'] !== 'Paid'): ?>
                        <a href="../../explore/checkout.php?booking_id=<?= $b['id'] ?>" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">Pay Now</a>
                    <?php else: ?>
                        <span class="badge bg-success-subtle text-success">Paid</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if($b['notes']): ?>
                <div class="card-footer bg-transparent border-top-0 px-4 pb-4">
                    <div class="p-3 rounded-3 bg-light-subtle border border-dashed">
                        <small class="text-muted fw-bold d-block mb-1"><i class="bi bi-sticky me-1"></i> My Notes:</small>
                        <small class="text-muted italic">"<?= htmlspecialchars($b['notes']) ?>"</small>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<style>
    .card:hover { transform: translateY(-5px); }
</style>

<?php require_once '../../includes/footer.php'; ?>
