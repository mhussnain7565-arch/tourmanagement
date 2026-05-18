<?php
require_once '../core/db.php';

header('Content-Type: application/json');

if (isset($_GET['destination_id'])) {
    $destId = $_GET['destination_id'];
    try {
        $stmt = $pdo->prepare("
            SELECT r.*, u.name as user_name 
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.destination_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$destId]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'reviews' => $reviews]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing destination ID.']);
}
?>
