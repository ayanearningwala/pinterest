<?php
session_start();
require 'db.php';

// Fetch all images (public access)
$stmt = $conn->prepare("SELECT * FROM images ORDER BY created_at DESC");
$stmt->execute();
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #ff0000;
            color: white;
            padding: 10px 20px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .search-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #f8f8f8;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .search-bar {
            flex-grow: 1;
            margin-right: 20px;
        }
        .search-bar input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .nav {
            display: flex;
            gap: 15px;
        }
        .nav a {
            color: #ff0000;
            text-decoration: none;
            font-size: 16px;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .nav a:hover {
            background-color: rgba(255, 0, 0, 0.1);
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            padding: 20px;
        }
        .grid img {
            width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .grid img:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Visual Content Sharing Platform</h1>
    </div>
    <div class="search-container">
        <div class="search-bar">
            <input type="text" placeholder="Search images...">
        </div>
        <div class="nav">
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Show these links if the user is logged in -->
                <a href="profile.php">Your Profile</a>
                <a href="upload.php">Add Post</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <!-- Show these links if the user is not logged in -->
                <a href="login.php">Login</a>
                <a href="signup.php">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="grid">
        <?php foreach ($images as $image): ?>
            <img src="<?php echo $image['image_url']; ?>" alt="<?php echo $image['title']; ?>">
        <?php endforeach; ?>
    </div>
</body>
</html>
