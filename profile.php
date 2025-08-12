<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php'; // Ensure this file exists and has the correct database credentials

// Restrict access to logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$profile_user_id = $_GET['id'] ?? $_SESSION['user_id']; // Get the profile user ID from the URL or default to the logged-in user
$logged_in_user_id = $_SESSION['user_id'];

// Fetch profile user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
if (!$stmt) {
    die("Prepare failed: " . $conn->errorInfo()[2]); // Debug database prepare error
}
$stmt->bindParam(':user_id', $profile_user_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->errorInfo()[2]); // Debug database execute error
}
$profile_user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile_user) {
    die("User not found."); // Debug if the user does not exist
}

// Check if the logged-in user is following the profile user
$stmt = $conn->prepare("SELECT * FROM followers WHERE follower_id = :follower_id AND followee_id = :followee_id");
if (!$stmt) {
    die("Prepare failed: " . $conn->errorInfo()[2]); // Debug database prepare error
}
$stmt->bindParam(':follower_id', $logged_in_user_id);
$stmt->bindParam(':followee_id', $profile_user_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->errorInfo()[2]); // Debug database execute error
}
$is_following = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch follower count
$stmt = $conn->prepare("SELECT COUNT(*) AS follower_count FROM followers WHERE followee_id = :followee_id");
if (!$stmt) {
    die("Prepare failed: " . $conn->errorInfo()[2]); // Debug database prepare error
}
$stmt->bindParam(':followee_id', $profile_user_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->errorInfo()[2]); // Debug database execute error
}
$follower_count = $stmt->fetch(PDO::FETCH_ASSOC)['follower_count'];

// Fetch profile user's images
$stmt = $conn->prepare("SELECT * FROM images WHERE user_id = :user_id ORDER BY created_at DESC");
if (!$stmt) {
    die("Prepare failed: " . $conn->errorInfo()[2]); // Debug database prepare error
}
$stmt->bindParam(':user_id', $profile_user_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->errorInfo()[2]); // Debug database execute error
}
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
        .profile-container {
            padding: 20px;
            text-align: center;
        }
        .profile-picture {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }
        .profile-picture img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ff0000;
        }
        .profile-picture .change-text {
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(255, 0, 0, 0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            display: none;
        }
        .profile-picture:hover .change-text {
            display: block;
        }
        .follow-button {
            background-color: #ff0000;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .follow-button:hover {
            background-color: #cc0000;
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
        }
    </style>
    <script>
        function toggleFollow(followeeId) {
            fetch('toggle_follow.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ followee_id: followeeId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload(); // Refresh the page to update the follow button and follower count
                } else {
                    alert('Failed to toggle follow status.');
                }
            });
        }
    </script>
</head>
<body>
    <div class="header">
        <h1><?php echo $profile_user['username']; ?>'s Profile</h1>
    </div>
    <div class="profile-container">
        <div class="profile-picture">
            <img src="<?php echo $profile_user['profile_picture'] ?? 'default-profile.jpg'; ?>" alt="Profile Picture">
            <?php if ($profile_user_id == $logged_in_user_id): ?>
                <div class="change-text" onclick="document.getElementById('profile-picture-input').click()">Change Profile Picture</div>
                <input type="file" id="profile-picture-input" style="display: none;" onchange="uploadProfilePicture(event)">
            <?php endif; ?>
        </div>
        <h2>Welcome, <?php echo $profile_user['username']; ?>!</h2>
        <p>Followers: <?php echo $follower_count; ?></p>
        <?php if ($profile_user_id != $logged_in_user_id): ?>
            <button class="follow-button" onclick="toggleFollow(<?php echo $profile_user_id; ?>)">
                <?php echo $is_following ? 'Unfollow' : 'Follow'; ?>
            </button>
        <?php endif; ?>
        <div class="grid">
            <?php foreach ($images as $image): ?>
                <img src="<?php echo $image['image_url']; ?>" alt="<?php echo $image['title']; ?>">
            <?php endforeach; ?>
        </div>
    </div>
    <script>
        function uploadProfilePicture(event) {
            const file = event.target.files[0];
            if (file) {
                const formData = new FormData();
                formData.append('profile_picture', file);

                fetch('update_profile_picture.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload(); // Refresh the page to show the new profile picture
                    } else {
                        alert('Failed to update profile picture.');
                    }
                });
            }
        }
    </script>
</body>
</html>
