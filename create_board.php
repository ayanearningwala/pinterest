<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];

    $stmt = $conn->prepare("INSERT INTO boards (user_id, name) VALUES (:user_id, :name)");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->bindParam(':name', $name);

    if ($stmt->execute()) {
        echo "Board created successfully!";
    } else {
        echo "Board creation failed!";
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
        <input type="text" name="name" placeholder="Board Name" required>
        <button type="submit">Create Board</button>
    </form>
</body>
</html>
