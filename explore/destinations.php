<?php 
require_once '../includes/header.php'; 

// Fetch Destinations from DB with Average Ratings
$query = "
    SELECT d.*, 
    COALESCE(AVG(r.rating), 0) as avg_rating, 
    COUNT(r.id) as review_count 
    FROM destinations d 
    LEFT JOIN reviews r ON d.id = r.destination_id 
    WHERE d.is_deleted = 0
";
$params = [];

// Filter Logic
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $query .= " AND d.category = ?";
    $params[] = $_GET['category'];
}
if (isset($_GET['intensity']) && !empty($_GET['intensity'])) {
    $query .= " AND d.intensity = ?";
    $params[] = $_GET['intensity'];
}
if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $query .= " AND CAST(REPLACE(REPLACE(d.price, 'Rs ', ''), ',', '') AS UNSIGNED) <= ?";
    $params[] = $_GET['max_price'];
}

$query .= " GROUP BY d.id";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$destinations = $stmt->fetchAll();
?>

<style>
    /* Weightless Grid & Interactive Cards */
    .destinations-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
        padding: 1rem 0;
    }

    .destination-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        cursor: pointer;
        position: relative;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .destination-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        border-color: rgba(255, 255, 255, 0.3);
    }

    .card-hero {
        position: relative;
        height: 200px;
        overflow: hidden;
    }

    .card-hero img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }

    .destination-card:hover .card-hero img {
        transform: scale(1.1);
    }

    .status-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: rgba(0, 0, 0, 0.6);
        color: #fff;
        padding: 0.3rem 0.8rem;
        border-radius: 50px;
        font-size: 0.75rem;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .card-content {
        padding: 1.5rem;
    }

    .card-content h4 {
        margin: 0;
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    .card-content .type {
        font-size: 0.85rem;
        color: rgba(var(--bs-body-color-rgb), 0.6);
        margin-bottom: 1rem;
        display: block;
    }

    .card-footer-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .intensity-tag {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }

    /* Expanding Layout */
    .deep-dive-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(15px);
        z-index: 9999;
        display: none;
        opacity: 0;
        transition: opacity 0.3s ease;
        overflow-y: auto;
        padding: 2rem;
    }

    .deep-dive-content {
        max-width: 900px;
        margin: 2rem auto;
        background: var(--bs-body-bg);
        border-radius: 30px;
        overflow: hidden;
        box-shadow: 0 30px 60px rgba(0,0,0,0.5);
        position: relative;
        transform: translateY(50px);
        transition: transform 0.4s ease;
    }

    .deep-dive-overlay.active {
        display: block;
        opacity: 1;
    }

    .deep-dive-overlay.active .deep-dive-content {
        transform: translateY(0);
    }

    .close-dive {
        position: absolute;
        top: 1.5rem;
        right: 1.5rem;
        font-size: 2rem;
        color: #fff;
        cursor: pointer;
        z-index: 10;
        text-shadow: 0 2px 10px rgba(0,0,0,0.5);
    }

    .dive-hero {
        height: 400px;
        position: relative;
    }

    .dive-hero img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .dive-body {
        padding: 3rem;
    }

    .spec-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
        margin: 2rem 0;
    }

    .spec-item i {
        font-size: 1.5rem;
        color: var(--bs-primary);
        margin-bottom: 0.5rem;
        display: block;
    }

    .activity-chip {
        display: inline-block;
        padding: 0.5rem 1rem;
        background: rgba(var(--bs-primary-rgb), 0.1);
        border: 1px solid rgba(var(--bs-primary-rgb), 0.2);
        border-radius: 50px;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    /* Smart Sidebar */
    .filter-sidebar {
        background: rgba(var(--bs-body-bg-rgb), 0.5);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        padding: 1.5rem;
        position: sticky;
        top: 1rem;
    }

    .filter-section {
        margin-bottom: 2rem;
    }

    .filter-section label {
        display: block;
        margin-bottom: 0.8rem;
        font-weight: 600;
        color: var(--bs-primary);
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
    }

    /* Animations */
    .floating {
        animation: float 4s ease-in-out infinite;
    }

    /* Admin Action Buttons */
    .admin-actions {
        position: absolute;
        top: 1rem;
        left: 1rem;
        display: flex;
        gap: 0.5rem;
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 100;
    }

    .destination-card:hover .admin-actions {
        opacity: 1;
    }

    .action-btn {
        width: 35px;
        height: 35px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
        color: white;
        transition: all 0.2s;
    }

    .btn-edit { background: rgba(13, 110, 253, 0.6); }
    .btn-delete { background: rgba(220, 53, 69, 0.6); }

    .action-btn:hover {
        transform: scale(1.1);
        color: white;
    }

    /* Review Stars */
    .rating-star {
        cursor: pointer;
        transition: color 0.2s;
    }
    .rating-star:hover, .rating-star.active {
        color: var(--bs-warning);
    }
</style>

<div class="row">
    <!-- Sidebar Filters -->
    <div class="col-lg-3">
        <div class="filter-sidebar shadow-sm">
            <h5 class="mb-4">Plan Your Trip</h5>
            <form action="" method="GET">
                <div class="filter-section">
                    <label>Trip Category</label>
                    <select name="category" class="form-select bg-transparent border-secondary" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <option value="Adventure" <?= (isset($_GET['category']) && $_GET['category'] == 'Adventure') ? 'selected' : '' ?>>Adventure</option>
                        <option value="Relaxation" <?= (isset($_GET['category']) && $_GET['category'] == 'Relaxation') ? 'selected' : '' ?>>Relaxation</option>
                        <option value="Educational" <?= (isset($_GET['category']) && $_GET['category'] == 'Educational') ? 'selected' : '' ?>>Educational</option>
                    </select>
                </div>

                <div class="filter-section">
                    <label>Adventure Level</label>
                    <select name="intensity" class="form-select bg-transparent border-secondary" onchange="this.form.submit()">
                        <option value="">All Levels</option>
                        <option value="Low" <?= (isset($_GET['intensity']) && $_GET['intensity'] == 'Low') ? 'selected' : '' ?>>Low (Relaxing)</option>
                        <option value="Medium" <?= (isset($_GET['intensity']) && $_GET['intensity'] == 'Medium') ? 'selected' : '' ?>>Medium (Adventure)</option>
                        <option value="High" <?= (isset($_GET['intensity']) && $_GET['intensity'] == 'High') ? 'selected' : '' ?>>High (Extreme Trekking)</option>
                    </select>
                </div>

                <div class="filter-section">
                    <label>Budget Limit (PKR)</label>
                    <input type="range" name="max_price" class="form-range" min="5000" max="100000" step="5000" value="<?= $_GET['max_price'] ?? 100000 ?>" oninput="this.nextElementSibling.value = 'Rs ' + Number(this.value).toLocaleString()">
                    <output class="d-block text-center mt-2 fw-bold">Rs <?= number_format($_GET['max_price'] ?? 100000) ?></output>
                </div>
                
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary rounded-pill">Search Destinations</button>
                    <a href="destinations.php" class="btn btn-link btn-sm mt-2 text-decoration-none">Reset Filters</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Destinations Grid -->
    <div class="col-lg-9">
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'super_admin'): ?>
            <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded-4 bg-primary bg-opacity-10 border border-primary border-opacity-25">
                <div>
                    <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-gear-fill me-2"></i>Destination CRUD Panel</h5>
                    <small class="text-muted">Direct management of your tour registry</small>
                </div>
                <a href="../dashboards/super_admin/manage_destinations.php" class="btn btn-primary rounded-pill shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> Manage All Destinations
                </a>
            </div>
        <?php endif; ?>
        
        <div class="destinations-grid">
            <?php if(empty($destinations)): ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-search-heart font-lg text-muted" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">No destinations found in this nebula.</h3>
                    <p class="text-muted">Try adjusting your filters or mission intensity.</p>
                </div>
            <?php endif; ?>

            <?php foreach($destinations as $dest): 
                $activities = json_decode($dest['activities'], true);
                $imgSrc = (strpos($dest['hero_image'], 'http') === 0) ? $dest['hero_image'] : BASE_URL . $dest['hero_image'];
            ?>
                <div class="destination-card floating" 
                     data-id="<?= $dest['id'] ?>"
                     data-name="<?= htmlspecialchars($dest['name']) ?>"
                     data-type="<?= htmlspecialchars($dest['type']) ?>"
                     data-image="<?= $imgSrc ?>"
                     data-duration="<?= htmlspecialchars($dest['duration']) ?>"
                     data-price="<?= htmlspecialchars($dest['price']) ?>"
                     data-intensity="<?= htmlspecialchars($dest['intensity']) ?>"
                     data-category="<?= htmlspecialchars($dest['category']) ?>"
                     data-itinerary='<?= htmlspecialchars($dest['itinerary'] ?: "[]") ?>'
                     data-description="<?= htmlspecialchars($dest['description']) ?>"
                     data-env="<?= htmlspecialchars($dest['environment']) ?>"
                     data-acc="<?= htmlspecialchars($dest['accommodation']) ?>"
                     data-acts='<?= htmlspecialchars($dest['activities']) ?>'
                     onclick="openDeepDive(this)">
                    
                    <div class="card-hero">
                        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'super_admin'): ?>
                            <div class="admin-actions">
                                <a href="../dashboards/super_admin/manage_destinations.php?edit_id=<?= $dest['id'] ?>" class="action-btn btn-edit" title="Edit Full Details" onclick="event.stopPropagation();">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button class="action-btn btn-primary" title="Update Quick Photo" onclick="event.stopPropagation(); quickPhoto(<?= $dest['id'] ?>, '<?= htmlspecialchars($dest['hero_image']) ?>')">
                                    <i class="bi bi-image"></i>
                                </button>
                                <button class="action-btn btn-delete" title="Delete Destination" onclick="event.stopPropagation(); quickDelete(<?= $dest['id'] ?>, '<?= htmlspecialchars($dest['name']) ?>')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                        <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($dest['name']) ?>">
                        <div class="status-badge"><?= $dest['duration'] ?></div>
                    </div>
                    
                    <div class="card-content">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="type"><?= $dest['type'] ?></span>
                            <div class="text-warning small">
                                <i class="bi bi-star-fill"></i> 
                                <span class="fw-bold text-body ms-1"><?= number_format($dest['avg_rating'], 1) ?></span>
                                <span class="text-muted ms-1">(<?= $dest['review_count'] ?>)</span>
                            </div>
                        </div>
                        <h4><?= $dest['name'] ?></h4>
                        
                        <div class="card-footer-info">
                            <span class="fw-bold text-primary"><?= $dest['price'] ?></span>
                            <?php 
                                $badgeColor = 'success';
                                if($dest['intensity'] == 'Medium') $badgeColor = 'warning';
                                if($dest['intensity'] == 'High') $badgeColor = 'danger';
                            ?>
                            <span class="intensity-tag text-<?= $badgeColor ?>"><?= $dest['intensity'] ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Deep Dive Overlay -->
<div class="deep-dive-overlay" id="deepDive">
    <span class="close-dive" onclick="closeDeepDive()">&times;</span>
    <div class="deep-dive-content">
        <div class="dive-hero">
            <img id="diveImage" src="" alt="">
            <div style="position: absolute; bottom: 2rem; left: 3rem; color: #fff; text-shadow: 0 5px 20px rgba(0,0,0,0.8);">
                <span id="diveType" class="badge bg-primary mb-2"></span>
                <h1 id="diveName" class="display-4 fw-bold"></h1>
            </div>
        </div>
        <div class="dive-body">
            <div class="row g-4">
                <!-- Timeline Section -->
                <div class="col-lg-7 card-body border-end">
                    <div class="p-3 mb-4 rounded-4" style="background: rgba(var(--bs-primary-rgb), 0.05); border-left: 5px solid var(--bs-primary);">
                        <h4 class="fw-bold mb-1"><i class="bi bi-clock-history me-2"></i>Day-by-Day Timeline</h4>
                        <small class="text-muted">Your curated itinerary from morning to night</small>
                    </div>
                    <div id="modalItinerary" class="itinerary-scroll pe-3" style="max-height: 550px; overflow-y: auto;">
                        <!-- Timeline injected here -->
                    </div>
                </div>
                
                <!-- Overview & Info Section -->
                <div class="col-lg-5">
                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted small fw-bold mb-3">Trip Overview</h6>
                        <p id="modalDesc" class="text-muted lh-lg"></p>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted small fw-bold mb-3">Activities & Experiences</h6>
                        <div id="diveActivities"></div>
                    </div>

                    <div class="bg-body-tertiary p-4 rounded-4 border">
                        <div class="mb-3">
                            <span class="text-muted small d-block mb-1">Stay Duration</span>
                            <span id="diveDuration" class="fw-bold h5"></span>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted small d-block mb-1">Estimated Budget</span>
                            <span id="divePrice" class="fw-bold text-success h4"></span>
                        </div>
                        <div>
                            <span class="text-muted small d-block mb-1">Adventure Level</span>
                            <span id="diveIntensity" class="badge"></span>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-5">

            <!-- Specs Grid -->
            <div class="spec-grid">
                <div class="spec-item">
                    <i class="bi bi-binoculars"></i>
                    <h6 class="fw-bold">Sightseeing</h6>
                    <p id="diveEnv" class="text-muted small"></p>
                </div>
                <div class="spec-item">
                    <i class="bi bi-house-door"></i>
                    <h6 class="fw-bold">Best Stays</h6>
                    <p id="diveAcc" class="text-muted small"></p>
                </div>
                <div class="spec-item">
                    <i class="bi bi-bus-front"></i>
                    <h6 class="fw-bold">Transport</h6>
                    <p class="text-muted small">Private premium cabs and local guides provided.</p>
                </div>
            </div>

            <hr class="my-5">

            <div class="row">
                <div class="col-lg-6 mb-5">
                    <h4 class="fw-bold mb-4"><i class="bi bi-chat-left-text me-2"></i>Mission Reviews</h4>
                    <div id="reviewsList" class="pe-3" style="max-height: 400px; overflow-y: auto;">
                        <!-- Reviews injected here -->
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="p-4 rounded-4 bg-body-tertiary border">
                        <h5 class="fw-bold mb-3">Leave a Review</h5>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <form id="reviewForm" enctype="multipart/form-data">
                                <input type="hidden" name="destination_id" id="reviewDestId">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Rating</label>
                                    <div class="star-rating h3 text-warning">
                                        <i class="bi bi-star rating-star" data-value="1"></i>
                                        <i class="bi bi-star rating-star" data-value="2"></i>
                                        <i class="bi bi-star rating-star" data-value="3"></i>
                                        <i class="bi bi-star rating-star" data-value="4"></i>
                                        <i class="bi bi-star rating-star" data-value="5"></i>
                                        <input type="hidden" name="rating" id="ratingValue" value="0" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Comment</label>
                                    <textarea name="comment" class="form-control rounded-3" rows="3" placeholder="Share your experience..." required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Upload Photo (Optional)</label>
                                    <input type="file" name="photo" class="form-control rounded-pill">
                                </div>
                                <button type="submit" class="btn btn-primary rounded-pill w-100 py-2 fw-bold">Submit Review</button>
                            </form>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <p class="text-muted">Please <a href="../login.php">Login</a> to share your feedback.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 col-md-6 mx-auto mt-5">
                <button class="btn btn-primary btn-lg rounded-pill shadow-lg py-3 fw-bold" onclick="openBookingModal()">Book This Journey</button>
            </div>
        </div>
    </div>
</div>

<!-- Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" style="z-index: 11000;">
    <div class="modal-dialog modal-dialog-centered">
        <form id="bookingForm" class="modal-content shadow-lg border-0 rounded-4">
            <input type="hidden" name="destination_id" id="bookDestId">
            <input type="hidden" name="total_price" id="bookPriceField">
            
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-primary">Finalize Your Journey</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4 text-center">
                    <h3 id="bookDestName" class="fw-bold mb-0"></h3>
                    <small id="bookDestPrice" class="text-success fw-bold"></small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Preferred Travel Date</label>
                    <input type="date" name="travel_date" id="bookDate" class="form-control rounded-pill" required min="<?= date('Y-m-d') ?>" onchange="checkAvailability()">
                    <div id="availabilityStatus" class="small mt-1 px-2"></div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Number of Guests</label>
                    <input type="number" name="guests" class="form-control rounded-pill" value="1" min="1" max="20" required onchange="calculateTotal()">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Special Notes (Optional)</label>
                    <textarea name="notes" class="form-control rounded-4" rows="3" placeholder="Any dietary requirements or special requests?"></textarea>
                </div>

                <div class="p-3 rounded-4 bg-light text-center">
                    <span class="text-muted small d-block">Estimated Total</span>
                    <h3 id="totalPriceDisplay" class="fw-bold text-primary mb-0"></h3>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow">Confirm Booking</button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Photo Modal -->
<div class="modal fade" id="photoModal" tabindex="-1" style="z-index: 10000;">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="../dashboards/super_admin/manage_destinations.php" class="modal-content shadow-lg border-0 rounded-4" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_photo">
            <input type="hidden" name="id" id="photoId">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-primary">Change Destination Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small text-muted text-uppercase fw-bold">Current Photo Preview</label>
                    <img id="photoPreview" class="w-100 rounded-4 mb-3" style="height: 150px; object-fit: cover; border: 1px solid rgba(0,0,0,0.1);">
                    
                    <label class="form-label fw-bold">Upload from PC</label>
                    <input type="file" name="hero_image_file" class="form-control rounded-pill mb-3">
                    
                    <div class="text-center my-2 text-muted small">--- OR ---</div>
                    
                    <label class="form-label fw-bold">External Image URL</label>
                    <input type="url" name="hero_image" id="photoUrl" class="form-control rounded-pill" placeholder="https://...">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-5">Save New Picture</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openDeepDive(card) {
        const data = card.dataset;
        const activities = JSON.parse(data.acts);
        
        document.getElementById('diveName').innerText = data.name;
        document.getElementById('diveType').innerText = data.type;
        document.getElementById('diveImage').src = data.image;
        document.getElementById('modalDesc').innerText = data.description; 
        document.getElementById('diveDuration').innerText = data.duration;
        document.getElementById('divePrice').innerText = data.price;
        document.getElementById('diveIntensity').innerText = data.intensity;
        
        // Prepare Booking Data
        document.getElementById('bookDestId').value = data.id;
        document.getElementById('bookDestName').innerText = data.name;
        document.getElementById('bookDestPrice').innerText = data.price;
        document.getElementById('bookPriceField').value = data.price;
        calculateTotal();

        // Fetch Reviews
        fetchReviews(data.id);
        if(document.getElementById('reviewDestId')) {
            document.getElementById('reviewDestId').value = data.id;
        }
        
        // Itinerary Timeline
        let itinHtml = '';
        try {
            const itin = JSON.parse(data.itinerary || '[]'); 
            itin.forEach(day => {
                itinHtml += `
                    <div class="timeline-day mb-4">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-calendar3 me-2"></i>Day ${day.day} Plan</h6>
                        <div class="ps-4 border-start border-primary border-2">
                            <div class="mb-2">
                                <span class="badge bg-warning-subtle text-warning me-2"><i class="bi bi-sun"></i> Morning</span>
                                <span class="text-muted">${day.morning}</span>
                            </div>
                            <div class="mb-2">
                                <span class="badge bg-info-subtle text-info me-2"><i class="bi bi-cloud-sun"></i> Afternoon</span>
                                <span class="text-muted">${day.afternoon}</span>
                            </div>
                            <div class="mb-2">
                                <span class="badge bg-indigo-subtle text-white me-2" style="background-color: #6610f222;"><i class="bi bi-moon-stars text-indigo"></i> Night</span>
                                <span class="text-muted">${day.night}</span>
                            </div>
                        </div>
                    </div>
                `;
            });
        } catch (e) {
            console.error("Invalid itinerary JSON", e);
            itinHtml = '<p class="text-muted italic">Timeline currently being updated.</p>';
        }
        document.getElementById('modalItinerary').innerHTML = itinHtml;

        document.getElementById('diveEnv').innerText = data.env; // Use data.env
        document.getElementById('diveAcc').innerText = data.acc;
        
        const intensityBadge = document.getElementById('diveIntensity');
        intensityBadge.className = 'badge';
        if(data.intensity === 'Low') intensityBadge.classList.add('bg-success');
        else if(data.intensity === 'Medium') intensityBadge.classList.add('bg-warning');
        else intensityBadge.classList.add('bg-danger');

        const actsContainer = document.getElementById('diveActivities');
        actsContainer.innerHTML = '';
        activities.forEach(act => {
            const chip = document.createElement('span');
            chip.className = 'activity-chip';
            chip.innerText = act;
            actsContainer.appendChild(chip);
        });

        document.getElementById('deepDive').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeDeepDive() {
        document.getElementById('deepDive').classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    // Close on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeDeepDive();
    });

    // Close on click outside content
    document.getElementById('deepDive').addEventListener('click', (e) => {
        if (e.target.id === 'deepDive') closeDeepDive();
    });

    // Quick Photo Logic
    function quickPhoto(id, currentUrl) {
        document.getElementById('photoId').value = id;
        document.getElementById('photoUrl').value = currentUrl;
        document.getElementById('photoPreview').src = currentUrl;
        new bootstrap.Modal(document.getElementById('photoModal')).show();
    }

    // Quick Delete Logic
    function quickDelete(id, name) {
        if(confirm(`Are you sure you want to delete "${name}"? This will move it to the trash.`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '../dashboards/super_admin/manage_destinations.php';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete';
            form.appendChild(actionInput);
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            form.appendChild(idInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Booking System Logic
    function openBookingModal() {
        // Check if logged in (simple check for now)
        <?php if(!isset($_SESSION['user_id'])): ?>
            alert("Please login to book a journey.");
            window.location.href = "../login.php";
            return;
        <?php endif; ?>
        
        new bootstrap.Modal(document.getElementById('bookingModal')).show();
    }

    function calculateTotal() {
        const basePriceStr = document.getElementById('bookPriceField').value;
        const guests = document.querySelector('input[name="guests"]').value;
        
        // Extract number from "Rs 15,000" or "$5,000"
        const basePrice = parseInt(basePriceStr.replace(/[^0-9]/g, ''));
        const total = basePrice * guests;
        
        const currency = basePriceStr.includes('Rs') ? 'Rs ' : '$';
        document.getElementById('totalPriceDisplay').innerText = currency + total.toLocaleString();
    }

    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'create_booking');

        fetch('process_booking.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert("Booking Successful! Proceeding to Payment...");
                window.location.href = "checkout.php?booking_id=" + data.booking_id;
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An unexpected error occurred.");
        });
    });

    // Review System Logic
    function fetchReviews(destId) {
        const reviewsList = document.getElementById('reviewsList');
        reviewsList.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>';

        fetch(`fetch_reviews.php?destination_id=${destId}`)
            .then(response => response.json())
            .then(data => {
                if(data.success && data.reviews.length > 0) {
                    let html = '';
                    data.reviews.forEach(r => {
                        let stars = '';
                        for(let i=1; i<=5; i++) {
                            stars += `<i class="bi bi-star${i <= r.rating ? '-fill' : ''} text-warning"></i>`;
                        }
                        
                        html += `
                            <div class="mb-4 pb-3 border-bottom">
                                <div class="d-flex justify-content-between mb-2">
                                    <h6 class="fw-bold mb-0">${r.user_name}</h6>
                                    <small class="text-muted">${new Date(r.created_at).toLocaleDateString()}</small>
                                </div>
                                <div class="mb-2">${stars}</div>
                                <p class="text-muted small mb-2">${r.comment}</p>
                                ${r.photo_path ? `<img src="../${r.photo_path}" class="rounded-3 shadow-sm mt-2" style="max-width: 150px; cursor:pointer" onclick="window.open('../${r.photo_path}')">` : ''}
                            </div>
                        `;
                    });
                    reviewsList.innerHTML = html;
                } else {
                    reviewsList.innerHTML = '<p class="text-muted text-center py-4">No reviews yet. Be the first to share your journey!</p>';
                }
            });
    }

    // Star Rating Interaction
    document.querySelectorAll('.rating-star').forEach(star => {
        star.addEventListener('mouseover', function() {
            const val = this.dataset.value;
            highlightStars(val);
        });
        
        star.addEventListener('click', function() {
            const val = this.dataset.value;
            document.getElementById('ratingValue').value = val;
            setActiveStars(val);
        });
    });

    document.querySelector('.star-rating')?.addEventListener('mouseleave', function() {
        const activeVal = document.getElementById('ratingValue').value;
        setActiveStars(activeVal);
    });

    function highlightStars(val) {
        document.querySelectorAll('.rating-star').forEach(s => {
            s.classList.remove('bi-star-fill');
            s.classList.add('bi-star');
            if(s.dataset.value <= val) {
                s.classList.remove('bi-star');
                s.classList.add('bi-star-fill');
            }
        });
    }

    function setActiveStars(val) {
        document.querySelectorAll('.rating-star').forEach(s => {
            s.classList.remove('active', 'bi-star-fill');
            s.classList.add('bi-star');
            if(s.dataset.value <= val) {
                s.classList.add('active', 'bi-star-fill');
                s.classList.remove('bi-star');
            }
        });
    }

    // Availability Check
    function checkAvailability() {
        const date = document.getElementById('bookDate').value;
        const destId = document.getElementById('bookDestId').value;
        const statusDiv = document.getElementById('availabilityStatus');
        const submitBtn = document.querySelector('#bookingForm button[type="submit"]');

        if(!date || !destId) return;

        statusDiv.innerHTML = '<span class="text-muted"><i class="bi bi-hourglass-split me-1"></i> Checking availability...</span>';
        
        fetch(`check_availability.php?destination_id=${destId}&date=${date}`)
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    if(data.available) {
                        statusDiv.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> ${data.remaining} slots remaining</span>`;
                        submitBtn.disabled = false;
                    } else {
                        statusDiv.innerHTML = '<span class="text-danger fw-bold"><i class="bi bi-x-circle-fill me-1"></i> No slots available for this date</span>';
                        submitBtn.disabled = true;
                    }
                } else {
                    statusDiv.innerHTML = `<span class="text-warning fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> ${data.message}</span>`;
                    // For now, we'll allow bookings if no specific availability is set, 
                    // OR you can choose to block them. Let's block them to enforce the new system.
                    submitBtn.disabled = true; 
                }
            });
    }

    // Review Form Submission
    document.getElementById('reviewForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'submit_review');

        if(formData.get('rating') == '0') {
            alert("Please select a star rating.");
            return;
        }

        fetch('submit_review.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                this.reset();
                setActiveStars(0);
                fetchReviews(formData.get('destination_id'));
            } else {
                alert("Error: " + data.message);
            }
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>
