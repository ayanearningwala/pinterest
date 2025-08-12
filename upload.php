<?php
session_start();
require 'db.php';

// Restrict access to logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $tags = $_POST['tags'];
    $user_id = $_SESSION['user_id'];

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/'; // Directory to store uploaded images
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true); // Create the directory if it doesn't exist
        }

        $file_name = basename($_FILES['image']['name']);
        $file_path = $upload_dir . uniqid() . '_' . $file_name; // Unique file name to avoid conflicts

        if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
            // Insert image details into the database
            $stmt = $conn->prepare("INSERT INTO images (user_id, title, image_url, tags) VALUES (:user_id, :title, :image_url, :tags)");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':image_url', $file_path);
            $stmt->bindParam(':tags', $tags);

            if ($stmt->execute()) {
                echo "<p style='color: green; text-align: center;'>Image uploaded successfully!</p>";
            } else {
                echo "<p style='color: red; text-align: center;'>Image upload failed!</p>";
            }
        } else {
            echo "<p style='color: red; text-align: center;'>File upload failed!</p>";
        }
    } else {
        echo "<p style='color: red; text-align: center;'>No file selected or upload error!</p>";
    }
}
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
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        .form-container h2 {
            color: #ff0000;
            margin-bottom: 20px;
        }
        .form-container input, .form-container textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .form-container button {
            width: 100%;
            padding: 10px;
            background-color: #ff0000;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #cc0000;
        }
        .image-preview {
            margin-top: 20px;
            text-align: center;
        }
        .image-preview img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
    <script>
        function previewImage(event) {
            const input = event.target;
            const preview = document.getElementById('image-preview');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };

                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }
        }
    </script>
</head>
<body>
    <div class="form-container">
        <h2>Upload Image</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Title" required>
            <textarea name="tags" placeholder="Tags (comma separated)"></textarea>
            <input type="file" name="image" id="image" onchange="previewImage(event)" required>
            <div class="image-preview">
                <img id="image-preview" src="#" alt="Image Preview" style="display: none;">
            </div>
            <button type="submit">Upload Image</button>
        </form>
    </div>
</body>
</html>
