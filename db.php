<?php
$host = 'localhost';
$dbname = 'dbuoh1ekgf0psu';
$username = 'uvvz2l07csxzm';
$password = 'cibzov8beo6n';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
