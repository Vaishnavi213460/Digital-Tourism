<?php
session_start();
require_once '../config/db.php';

// SECURITY: Only allow Admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all Data
$users = $pdo->query("SELECT * FROM users WHERE role_type = 'user'")->fetchAll();
$bookings = $pdo->query("
    SELECT b.*, u.full_name as user_name 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id
")->fetchAll();
$messages = $pdo->query("SELECT m.*, u.full_name FROM support_messages m JOIN users u ON m.user_id = u.id ORDER BY m.created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: sans-serif; display: flex; margin: 0; background: #f4f7f6; }
        .sidebar { width: 250px; background: #333; color: white; min-height: 100vh; padding: 20px; }
        .main-content { flex: 1; padding: 30px; }
        table { width: 100%; border-collapse: collapse; background: white; margin-bottom: 30px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #ff5a5f; color: white; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <p>Welcome, Admin</p>
    <hr>
    <a href="logout.php" style="color: #ff5a5f; text-decoration: none;">Logout</a>
</div>

<div class="main-content">
    <h1>Platform Overview</h1>

    <h3>👥 Registered Users</h3>
    <table>
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Preference</th></tr>
        <?php foreach($users as $u): ?>
        <tr>
            <td><?php echo $u['id']; ?></td>
            <td><?php echo htmlspecialchars($u['full_name']); ?></td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
            <td><?php echo $u['preference']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h3>📅 All Bookings (Packages & Hotels)</h3>
    <table>
        <tr><th>User</th><th>Type</th><th>Item ID</th><th>Travel Date</th><th>Status</th></tr>
        <?php foreach($bookings as $b): ?>
        <tr>
            <td><?php echo htmlspecialchars($b['user_name']); ?></td>
            <td><?php echo ucfirst($b['item_type']); ?></td>
            <td><?php echo $b['item_id']; ?></td>
            <td><?php echo $b['travel_date']; ?></td>
            <td><span style="color: green;">Confirmed</span></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h3>💬 Support Queries</h3>
    <?php foreach($messages as $m): ?>
        <div class="stat-card" style="margin-bottom:10px;">
            <strong><?php echo htmlspecialchars($m['full_name']); ?>:</strong>
            <p><?php echo htmlspecialchars($m['message']); ?></p>
            <small><?php echo $m['created_at']; ?></small>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>