<?php
require_once '../core/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'];
    $userId = $_SESSION['user_id'];
    $amount = $_POST['amount'];
    $methodId = $_POST['method_id'] != '0' ? $_POST['method_id'] : null;
    $methodType = $_POST['method_type'];
    
    $proof_file = '';
    if (isset($_FILES['proof_file']) && $_FILES['proof_file']['error'] === 0) {
        $filename = time() . '_proof_' . basename($_FILES['proof_file']['name']);
        $upload_path = '../uploads/payments/' . $filename;
        
        if (!is_dir('../uploads/payments')) {
            mkdir('../uploads/payments', 0777, true);
        }

        if (move_uploaded_file($_FILES['proof_file']['tmp_name'], $upload_path)) {
            $proof_file = 'uploads/payments/' . $filename;
        }
    }

    // Logic for digital payment redirect would go here
    if($methodType === 'Stripe' || $methodType === 'PayPal') {
        // Placeholder for redirection
        // For now, we'll just record it as a pending digital payment
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO payments (booking_id, user_id, amount, payment_method_id, payment_method_type, proof_file, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $status = ($methodType === 'Stripe') ? 'Verified' : 'Pending'; // Auto-verify digital (simulated)
        $stmt->execute([$bookingId, $userId, $amount, $methodId, $methodType, $proof_file, $status]);

        if($status === 'Verified') {
            $pdo->query("UPDATE bookings SET payment_status = 'Paid' WHERE id = $bookingId");
        }

        header("Location: ../dashboards/user/my_bookings.php?success=Payment+Submitted");
        exit;
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit;
}
?>
