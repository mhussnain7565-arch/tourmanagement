<?php
require_once '../core/session.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in.']);
        exit;
    }

    $userId = $_SESSION['user_id'];
    $destId = $_POST['destination_id'];
    $rating = (int)$_POST['rating'];
    $comment = $_POST['comment'] ?? '';
    
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $filename = time() . '_review_' . basename($_FILES['photo']['name']);
        $upload_dir = '../uploads/reviews/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $filename)) {
            $photo_path = 'uploads/reviews/' . $filename;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO reviews (user_id, destination_id, rating, comment, photo_path) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$userId, $destId, $rating, $comment, $photo_path])) {
            echo json_encode(['success' => true, 'message' => 'Review submitted! Thank you for your feedback.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save review.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
