<?php
require_once '../includes/header.php';

if(!isset($_GET['booking_id'])) {
    header("Location: ../index.php");
    exit;
}

$bookingId = $_GET['booking_id'];
$userId = $_SESSION['user_id'];

// Fetch Booking Info
$stmt = $pdo->prepare("
    SELECT b.*, d.name as dest_name, d.hero_image, d.type as dest_type 
    FROM bookings b
    JOIN destinations d ON b.destination_id = d.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$bookingId, $userId]);
$booking = $stmt->fetch();

if(!$booking) {
    die("Invalid booking reference.");
}

// Check if already paid
$paymentCheck = $pdo->prepare("SELECT * FROM payments WHERE booking_id = ? AND status = 'Verified'");
$paymentCheck->execute([$bookingId]);
if($paymentCheck->fetch()) {
    header("Location: ../dashboards/user/my_bookings.php?msg=Already+Paid");
    exit;
}

// Fetch Active Payment Methods
$methods = $pdo->query("SELECT * FROM payment_methods WHERE is_active = 1")->fetchAll();
?>

<style>
    .checkout-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .payment-option {
        cursor: pointer;
        transition: all 0.3s;
        border: 2px solid transparent;
        border-radius: 15px;
        padding: 1.5rem;
        background: rgba(var(--bs-body-bg-rgb), 0.5);
    }
    .payment-option:hover {
        background: rgba(var(--bs-primary-rgb), 0.05);
        border-color: rgba(var(--bs-primary-rgb), 0.2);
    }
    .payment-option.active {
        background: rgba(var(--bs-primary-rgb), 0.1);
        border-color: var(--bs-primary);
    }
    .method-icon {
        font-size: 2rem;
        color: var(--bs-primary);
        margin-bottom: 1rem;
        display: block;
    }
</style>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="checkout-card card bg-body">
            <div class="row g-0">
                <!-- Summary Sidebar -->
                <div class="col-md-4 bg-primary text-white p-4">
                    <h5 class="fw-bold mb-4">Trip Summary</h5>
                    <div class="mb-4">
                        <img src="<?= (strpos($booking['hero_image'], 'http') === 0) ? $booking['hero_image'] : BASE_URL . $booking['hero_image'] ?>" class="w-100 rounded-3 mb-3 shadow">
                        <h4 class="fw-bold mb-0"><?= $booking['dest_name'] ?></h4>
                        <small class="opacity-75"><?= $booking['dest_type'] ?></small>
                    </div>
                    <hr class="opacity-25">
                    <div class="mb-3">
                        <small class="d-block opacity-75">Travel Date</small>
                        <span class="fw-bold"><?= date('M d, Y', strtotime($booking['travel_date'])) ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="d-block opacity-75">Guests</small>
                        <span class="fw-bold"><?= $booking['guests'] ?> Person<?= $booking['guests'] > 1 ? 's' : '' ?></span>
                    </div>
                    <div class="mt-5 p-3 rounded-3" style="background: rgba(255,255,255,0.1);">
                        <small class="d-block opacity-75">Total Amount</small>
                        <span class="h3 fw-bold mb-0"><?= $booking['total_price'] ?></span>
                    </div>
                </div>

                <!-- Payment Form -->
                <div class="col-md-8 p-5">
                    <h2 class="fw-bold mb-1">Checkout</h2>
                    <p class="text-muted mb-5">Select your preferred payment method below.</p>

                    <form id="paymentForm" action="process_payment.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="booking_id" value="<?= $bookingId ?>">
                        <input type="hidden" name="amount" value="<?= $booking['total_price'] ?>">
                        
                        <div class="row g-3 mb-5">
                            <?php foreach($methods as $m): ?>
                            <div class="col-6">
                                <div class="payment-option text-center h-100" onclick="selectMethod(<?= $m['id'] ?>, '<?= $m['method_name'] ?>', this)">
                                    <i class="bi bi-bank method-icon"></i>
                                    <h6 class="fw-bold mb-0"><?= $m['method_name'] ?></h6>
                                    <small class="text-muted d-block mt-1"><?= $m['bank_name'] ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <div class="col-6">
                                <div class="payment-option text-center h-100" onclick="selectMethod(0, 'Digital', this)">
                                    <i class="bi bi-credit-card method-icon"></i>
                                    <h6 class="fw-bold mb-0">Stripe / Card</h6>
                                    <small class="text-muted d-block mt-1">Instant Activation</small>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="method_id" id="selectedMethodId" required>
                        <input type="hidden" name="method_type" id="selectedMethodType" required>

                        <!-- Dynamic Bank Details Section -->
                        <div id="bankDetailsSection" class="p-4 rounded-4 border bg-light-subtle mb-4 d-none">
                            <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>Bank Transfer Instructions</h6>
                            <div id="bankInfoContent" class="mb-4"></div>
                            
                            <label class="form-label fw-bold small">Upload Payment Proof (Screenshot/Receipt)</label>
                            <input type="file" name="proof_file" class="form-control rounded-pill">
                        </div>

                        <!-- Dynamic Digital Section -->
                        <div id="digitalSection" class="p-4 rounded-4 border bg-light-subtle mb-4 d-none">
                            <div class="text-center py-4">
                                <i class="bi bi-shield-lock-fill text-success h1 d-block mb-3"></i>
                                <h5 class="fw-bold">Secure Digital Payment</h5>
                                <p class="text-muted small">You will be redirected to our secure payment gateway to complete the transaction.</p>
                                <div class="d-flex justify-content-center gap-3 mt-3">
                                    <i class="bi bi-stripe h3 text-primary"></i>
                                    <i class="bi bi-paypal h3 text-info"></i>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill py-3 fw-bold shadow">Finalize Mission Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const methodsData = <?= json_encode($methods) ?>;

    function selectMethod(id, type, element) {
        // Update selection
        document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('active'));
        element.classList.add('active');
        
        document.getElementById('selectedMethodId').value = id;
        document.getElementById('selectedMethodType').value = (id === 0 ? 'Stripe' : 'Bank Transfer');

        // Toggle sections
        const bankSection = document.getElementById('bankDetailsSection');
        const digitalSection = document.getElementById('digitalSection');

        if(id > 0) {
            bankSection.classList.remove('d-none');
            digitalSection.classList.add('d-none');
            
            const method = methodsData.find(m => m.id == id);
            document.getElementById('bankInfoContent').innerHTML = `
                <div class="row g-2 small">
                    <div class="col-4 text-muted">Bank:</div><div class="col-8 fw-bold">${method.bank_name}</div>
                    <div class="col-4 text-muted">Account:</div><div class="col-8 fw-bold">${method.account_name}</div>
                    <div class="col-4 text-muted">Number:</div><div class="col-8 fw-bold">${method.account_number}</div>
                    <div class="col-4 text-muted">IBAN:</div><div class="col-8 fw-bold">${method.iban}</div>
                </div>
                <div class="mt-3 p-2 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded small">
                    ${method.instructions}
                </div>
            `;
            document.querySelector('input[name="proof_file"]').required = true;
        } else {
            bankSection.classList.add('d-none');
            digitalSection.classList.remove('d-none');
            document.querySelector('input[name="proof_file"]').required = false;
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>
