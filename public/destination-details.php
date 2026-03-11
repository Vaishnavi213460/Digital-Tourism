<?php
session_start(); // START SESSION TO CHECK LOGIN STATUS
require_once '../config/db.php';

$dest_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM destinations WHERE id = ?");
$stmt->execute([$dest_id]);
$destination = $stmt->fetch();

if (!$destination) {
    die("Destination not found!");
}

$pkg_stmt = $pdo->prepare("SELECT * FROM packages WHERE destination_id = ?");
$pkg_stmt->execute([$dest_id]);
$packages = $pkg_stmt->fetchAll();

$acc_stmt = $pdo->prepare("SELECT * FROM accommodations WHERE destination_id = ?");
$acc_stmt->execute([$dest_id]);
$hotels = $acc_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($destination['name']); ?> - Details</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <style>
        .details-container { max-width: 1000px; margin: auto; padding: 20px; font-family: sans-serif; }
        .hero-banner { width: 100%; height: 350px; overflow: hidden; border-radius: 15px; margin-bottom: 25px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .hero-banner img { width: 100%; height: 100%; object-fit: cover; }
        .grid-section { display: flex; gap: 20px; margin-top: 30px; }
        .info-box { flex: 1; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px #ccc; }
        .badge { background: #2ecc71; color: white; padding: 5px 10px; border-radius: 4px; font-size: 0.8rem; }
        .map-container { margin-top: 20px; border-radius: 8px; overflow: hidden; border: 1px solid #ddd; }
        .btn-book { display: inline-block; background: #ff5a5f; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; font-size: 0.85rem; margin-top: 10px; }
        .btn-hotel { background: #3498db; }
        .btn-login-req { background: #555; }
    </style>
</head>
<body>
    <div class="details-container">
        <p><a href="index.php">← Back to Explore</a></p>
        <h1>Welcome to <?php echo htmlspecialchars($destination['name']); ?></h1>
        
        <div class="hero-banner">
            <img src="./assets/img/<?php echo $destination['image_url']; ?>" alt="Destination Banner">
        </div>
        
        <div class="grid-section">
            <div class="info-box">
                <h3>🏛️ Nearby Attractions</h3>
                <p><?php echo nl2br(htmlspecialchars($destination['attractions'])); ?></p>
                <hr style="opacity: 0.3; margin: 20px 0;">
                <h3>🍲 Local Food Culture</h3>
                <p><?php echo nl2br(htmlspecialchars($destination['food_culture'])); ?></p>
                
                <div class="map-container">
                    <h3>📍 Location Map</h3>
                    <iframe width="100%" height="250" frameborder="0" style="border:0" 
                        src="https://www.google.com/maps?q=<?php echo urlencode($destination['name'] . ' ' . $destination['location']); ?>&output=embed" 
                        allowfullscreen>
                    </iframe>
                </div>
            </div>

            <div class="info-box">
                <h3>✈️ Available Packages</h3>
                <?php foreach($packages as $pkg): ?>
                    <div style="border-bottom: 1px solid #eee; padding: 15px 0;">
                        <strong><?php echo htmlspecialchars($pkg['package_name']); ?></strong> 
                        <span class="badge">$<?php echo $pkg['price']; ?></span>
                        <p style="margin: 5px 0;"><small><?php echo $pkg['duration']; ?></small></p>
                        
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="booking.php?type=package&id=<?php echo $pkg['id']; ?>" class="btn-book">Book Package</a>
                        <?php else: ?>
                            <a href="login.php" class="btn-book btn-login-req">Login to Book</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="info-box" style="margin-top:20px;">
            <h3>🏨 Recommended Hotels</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                <?php foreach($hotels as $hotel): ?>
                    <div style="border: 1px solid #eee; padding: 15px; border-radius: 8px;">
                        <strong><?php echo htmlspecialchars($hotel['hotel_name']); ?> (<?php echo $hotel['stars']; ?>★)</strong>
                        <p style="font-size: 0.85rem; margin: 10px 0;">🚍 <?php echo $hotel['transport_options']; ?></p>
                        
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="booking.php?type=hotel&id=<?php echo $hotel['id']; ?>" class="btn-book btn-hotel">Book Hotel</a>
                        <?php else: ?>
                            <a href="login.php" class="btn-book btn-login-req">Login to Book</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>