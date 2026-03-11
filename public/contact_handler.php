<?php
session_start();
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $message = $_POST['message'];

    $stmt = $pdo->prepare("INSERT INTO support_messages (user_id, message) VALUES (?, ?)");
    if ($stmt->execute([$user_id, $message])) {
        echo "<script>alert('Support team notified! We will get back to you.'); window.location.href='index.php';</script>";
    }
}
?>