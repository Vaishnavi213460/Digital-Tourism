<?php
session_start();
require_once '../config/db.php';

// 1. SECURITY: If not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$type = $_GET['type'] ?? 'package';
$id = (int)($_GET['id'] ?? 0);

if ($type == 'package') {
    $stmt = $pdo->prepare("SELECT package_name as name FROM packages WHERE id = ?");
} else {
    $stmt = $pdo->prepare("SELECT hotel_name as name FROM accommodations WHERE id = ?");
}
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) { die("Item not found."); }

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id']; // LINK TO LOGGED IN USER
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $travel_date = $_POST['travel_date'];

    // FIXED: Added user_id to the query
    $ins = $pdo->prepare("INSERT INTO bookings (user_id, full_name, email, item_type, item_id, travel_date) VALUES (?, ?, ?, ?, ?, ?)");
    $ins->execute([$user_id, $full_name, $email, $type, $id, $travel_date]);

    echo "<script>alert('Booking Confirmed for " . addslashes($item['name']) . "!'); window.location.href='dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Confirm Booking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="background:#f4f7f6; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0;">
    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); width: 100%; max-width: 400px;">
        <h2 style="margin-top: 0;">Confirm Your Booking</h2>
        <p>Booking as: <strong><?php echo $_SESSION['user_name']; ?></strong></p>
        <p>Item: <strong><?php echo htmlspecialchars($item['name']); ?></strong></p>
        
        <form method="POST">
            <input type="hidden" name="full_name" value="<?php echo $_SESSION['user_name']; ?>">
            <input type="hidden" name="email" value="<?php echo $_SESSION['user_email']; ?>">
            
            <label style="display:block; margin-bottom: 5px;">Travel Date</label>
            <input type="date" name="travel_date" required style="width:100%; padding:10px; margin-bottom:20px; border:1px solid #ddd; border-radius:5px; box-sizing:border-box;">
            
            <button type="submit" style="width:100%; background:#ff5a5f; color:white; border:none; padding:12px; border-radius:5px; font-weight:bold; cursor:pointer;">
                Submit Booking Request
            </button>
        </form>
        <p style="text-align:center;"><a href="index.php" style="color:#666; font-size:0.9rem;">Cancel</a></p>
    </div>
</body>
</html>