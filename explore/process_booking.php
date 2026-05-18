<?php
require_once '../core/session.php';
require_once '../includes/mailer.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_booking') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in.']);
        exit;
    }

    $userId = $_SESSION['user_id'];
    $destId = $_POST['destination_id'];
    $travelDate = $_POST['travel_date'];
    $guests = $_POST['guests'];
    $totalPrice = $_POST['total_price'];
    $notes = $_POST['notes'] ?? '';

    try {
        // 1. Check Availability First
        $checkStmt = $pdo->prepare("SELECT id, total_slots, booked_slots FROM tour_availability WHERE destination_id = ? AND tour_date = ? AND status = 'Open'");
        $checkStmt->execute([$destId, $travelDate]);
        $avail = $checkStmt->fetch();

        if (!$avail) {
            echo json_encode(['success' => false, 'message' => 'This date is no longer open for missions.']);
            exit;
        }

        if ($avail['booked_slots'] + $guests > $avail['total_slots']) {
            echo json_encode(['success' => false, 'message' => 'Not enough slots remaining for the requested number of guests.']);
            exit;
        }

        // 2. Process Booking
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, destination_id, travel_date, guests, total_price, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$userId, $destId, $travelDate, $guests, $totalPrice, $notes]);

        if ($result) {
            $bookingId = $pdo->lastInsertId();
            
            // 3. Update Availability Slots
            $updateAvail = $pdo->prepare("UPDATE tour_availability SET booked_slots = booked_slots + ? WHERE id = ?");
            $updateAvail->execute([$guests, $avail['id']]);

            // Auto-close if full
            $pdo->query("UPDATE tour_availability SET status = 'Full' WHERE booked_slots >= total_slots");

            // 4. Trigger Email Notification
            $uStmt = $pdo->prepare("SELECT email, name FROM users WHERE id = ?");
            $uStmt->execute([$userId]);
            $user = $uStmt->fetch();

            $dStmt = $pdo->prepare("SELECT name FROM destinations WHERE id = ?");
            $dStmt->execute([$destId]);
            $dest = $dStmt->fetch();

            $mailer = new Mailer($pdo);
            $mailer->send('booking_confirmation', $user['email'], [
                'user_name' => $user['name'],
                'tour_name' => $dest['name'],
                'travel_date' => $travelDate,
                'guests' => $guests,
                'price' => $totalPrice
            ], $userId);

            echo json_encode(['success' => true, 'message' => 'Booking confirmed!', 'booking_id' => $bookingId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save booking.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
