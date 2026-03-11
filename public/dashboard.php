<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// FIXED QUERY: Uses LEFT JOINs to handle both packages and hotels correctly
$stmt = $pdo->prepare("
    SELECT b.*, 
           COALESCE(d_p.name, d_h.name) as dest_name,
           COALESCE(d_p.location, d_h.location) as dest_location
    FROM bookings b 
    LEFT JOIN packages p ON b.item_id = p.id AND b.item_type = 'package'
    LEFT JOIN destinations d_p ON p.destination_id = d_p.id
    LEFT JOIN accommodations a ON b.item_id = a.id AND b.item_type = 'hotel'
    LEFT JOIN destinations d_h ON a.destination_id = d_h.id
    WHERE b.user_id = ?
    ORDER BY b.travel_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$user_bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Dashboard | Digital Travel</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 40px; }
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .booking-card { 
            background: white; 
            border-radius: 15px; 
            margin-bottom: 25px; 
            display: flex; 
            overflow: hidden; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }
        .booking-card:hover { transform: scale(1.01); }
        .details-side { flex: 1; padding: 25px; border-left: 5px solid #ff5a5f; }
        .map-side { width: 350px; background: #eee; }
        .status-badge { 
            background: #e3f2fd; color: #1976d2; 
            padding: 5px 12px; border-radius: 20px; 
            font-size: 0.8rem; font-weight: bold; text-transform: uppercase;
        }
        .type-tag {
            display: inline-block;
            margin-top: 10px;
            font-size: 0.85rem;
            color: #666;
            background: #f8f9fa;
            padding: 2px 8px;
            border-radius: 4px;
        }
        .btn-home { text-decoration: none; color: #ff5a5f; font-weight: bold; }
    </style>
</head>
<body>

    <div class="dashboard-header">
        <h1>My Travel Dashboard</h1>
        <a href="index.php" class="btn-home">← Back to Explore</a>
    </div>
    
    <?php if(empty($user_bookings)): ?>
        <div style="text-align:center; padding: 50px; background:white; border-radius:15px;">
            <h3>No bookings found.</h3>
            <p>Time to plan your next adventure!</p>
            <a href="index.php" class="btn">Browse Destinations</a>
        </div>
    <?php endif; ?>

    <?php foreach($user_bookings as $b): ?>
        <div class="booking-card">
            <div class="details-side">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <h2 style="margin:0; color:#333;"><?php echo htmlspecialchars($b['dest_name']); ?></h2>
                    <span class="status-badge">Confirmed</span>
                </div>
                
                <div style="margin-top: 20px;">
                    <p><strong>📅 Travel Date:</strong> <?php echo date('F j, Y', strtotime($b['travel_date'])); ?></p>
                    <p><strong>👤 Traveler:</strong> <?php echo htmlspecialchars($b['full_name']); ?></p>
                    <div class="type-tag">
                        <?php echo $b['item_type'] == 'package' ? '✈️ Package Deal' : '🏨 Hotel Stay'; ?>
                    </div>
                </div>
            </div>
            
            <div class="map-side">
                <iframe 
                    width="100%" 
                    height="100%" 
                    frameborder="0" 
                    style="border:0"
                    src="https://maps.google.com/maps?q=<?php echo urlencode($b['dest_name'] . ' ' . $b['dest_location']); ?>&t=&z=13&ie=UTF8&iwloc=&output=embed" 
                    allowfullscreen>
                </iframe>
            </div>
        </div>
    <?php endforeach; ?>

</body>
</html>