<?php
require_once '../core/db.php';

header('Content-Type: application/json');

if (isset($_GET['destination_id']) && isset($_GET['date'])) {
    $destId = $_GET['destination_id'];
    $date = $_GET['date'];

    try {
        $stmt = $pdo->prepare("
            SELECT * FROM tour_availability 
            WHERE destination_id = ? AND tour_date = ? AND status = 'Open'
        ");
        $stmt->execute([$destId, $date]);
        $avail = $stmt->fetch();

        if ($avail) {
            $remaining = $avail['total_slots'] - $avail['booked_slots'];
            echo json_encode([
                'success' => true, 
                'available' => ($remaining > 0), 
                'remaining' => $remaining,
                'total' => $avail['total_slots']
            ]);
        } else {
            // Check if there are ANY slots for this destination to give a better message
            $stmt2 = $pdo->prepare("SELECT count(*) FROM tour_availability WHERE destination_id = ?");
            $stmt2->execute([$destId]);
            $hasAny = $stmt2->fetchColumn();

            if ($hasAny > 0) {
                echo json_encode(['success' => false, 'message' => 'This specific date is not open for bookings.']);
            } else {
                // If no date-specific availability is set, maybe allow it but show "Standard Availability"
                // But for "Dynamic Calendar", we should probably enforce defined dates.
                echo json_encode(['success' => false, 'message' => 'No scheduled missions for this destination yet.']);
            }
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing parameters.']);
}
?>
