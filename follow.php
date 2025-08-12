<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $followee_id = $_POST['followee_id'];

    $stmt = $conn->prepare("INSERT INTO followers (follower_id, followee_id) VALUES (:follower_id, :followee_id)");
    $stmt->bindParam(':follower_id', $_SESSION['user_id']);
    $stmt->bindParam(':followee_id', $followee_id);

    if ($stmt->execute()) {
        echo "Followed successfully!";
    } else {
        echo "Follow failed!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        form { max-width: 300px; margin: 0 auto; }
        input { width: 100%; padding: 10px; margin: 10px 0; }
        button { padding: 10px 20px; background-color: #007BFF; color: white; border: none; }
    </style>
</head>
<body>
    <form method="POST">
        <input type="text" name="followee_id" placeholder="User ID to Follow" required>
        <button type="submit">Follow</button>
    </form>
</body>
</html>
