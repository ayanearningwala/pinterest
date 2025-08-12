<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $user_id = $_SESSION['user_id'];

    // Handle file upload
    if ($_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/'; // Directory to store uploaded images
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true); // Create the directory if it doesn't exist
        }

        $file_name = basename($_FILES['profile_picture']['name']);
        $file_path = $upload_dir . uniqid() . '_' . $file_name; // Unique file name to avoid conflicts

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $file_path)) {
            // Update profile picture in the database
            $stmt = $conn->prepare("UPDATE users SET profile_picture = :profile_picture WHERE id = :user_id");
            $stmt->bindParam(':profile_picture', $file_path);
            $stmt->bindParam(':user_id', $user_id);

            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database update failed']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'File upload failed']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No file selected or upload error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
