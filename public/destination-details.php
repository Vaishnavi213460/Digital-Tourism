<?php
session_start();
require_once '../config/db.php';
require_once '../includes/lang_loader.php';
$lang = loadLanguage();

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($destination['name']); ?> - Details</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <style>
        :root { --accent: #ff5a5f; --dark: #2d3436; --bg: #f8f9fa; }
        body { background: var(--bg); font-family: 'Segoe UI', Roboto, sans-serif; color: var(--dark); margin: 0; }
        
        .container { max-width: 1100px; margin: 0 auto; padding: 20px; }
        
        /* Floating Weather & Hero */
        .hero-wrapper { position: relative; border-radius: 20px; overflow: hidden; height: 450px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); margin-bottom: 30px; }
        .hero-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        
        .hero-overlay { 
            position: absolute; bottom: 0; left: 0; right: 0; 
            background: linear-gradient(transparent, rgba(0,0,0,0.8)); 
            padding: 40px; color: white; display: flex; justify-content: space-between; align-items: flex-end;
        }

        .weather-widget { 
            background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px);
            padding: 15px 25px; border-radius: 15px; text-align: center; border: 1px solid rgba(255,255,255,0.3);
        }

        /* Layout Grid */
        .main-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
        
        .card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .card h3 { margin-top: 0; border-left: 4px solid var(--accent); padding-left: 15px; font-size: 1.2rem; }

        /* Package & Hotel Styling */
        .item-row { border-bottom: 1px solid #eee; padding: 15px 0; display: flex; justify-content: space-between; align-items: center; }
        .item-row:last-child { border-bottom: none; }
        
        .price-tag { color: #27ae60; font-weight: bold; font-size: 1.1rem; }
        .btn { 
            padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; 
            font-size: 0.9rem; transition: 0.3s; display: inline-block;
        }
        .btn-primary { background: var(--accent); color: white; }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-2px); }
        .btn-outline { border: 1px solid #ddd; color: #666; }

        .back-link { text-decoration: none; color: #666; font-weight: bold; margin-bottom: 15px; display: block; }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" class="back-link">← <?php echo $lang['back_explore']; ?></a>

    <div class="hero-wrapper">
        <img src="./assets/img/<?php echo $destination['image_url']; ?>" alt="Destination">
        <div class="hero-overlay">
            <div>
                <h1 style="margin: 0; font-size: 3rem;"><?php echo htmlspecialchars($destination['name']); ?></h1>
                <p style="opacity: 0.9; font-size: 1.1rem;">📍 <?php echo htmlspecialchars($destination['location']); ?></p>
            </div>
            
            <div id="weather" class="weather-widget" style="display: none;">
                <div id="weather-icon" style="margin-bottom: -10px;"></div>
                <div id="weather-temp" style="font-size: 1.5rem; font-weight: bold;"></div>
                <div id="weather-desc" style="font-size: 0.8rem; text-transform: uppercase;"></div>
            </div>
        </div>
    </div>

    <div class="main-layout">
        <div class="content-side">
            <div class="card">
                <h3><?php echo $lang['attractions_header']; ?></h3>
                <p style="line-height: 1.6; color: #555;"><?php echo nl2br(htmlspecialchars($destination['attractions'])); ?></p>
                
                <h3 style="margin-top: 30px;"><?php echo $lang['food_culture_header']; ?></h3>
                <p style="line-height: 1.6; color: #555;"><?php echo nl2br(htmlspecialchars($destination['food_culture'])); ?></p>
            </div>

            <div class="card">
                <h3><?php echo $lang['location_map']; ?></h3>
                <div style="border-radius: 10px; overflow: hidden; margin-top: 15px;">
                    <iframe width="100%" height="300" frameborder="0" style="border:0" 
                        src="https://maps.google.com/maps?q=<?php echo urlencode($destination['name'] . ' ' . $destination['location']); ?>&output=embed">
                    </iframe>
                </div>
            </div>
        </div>

        <div class="sidebar">
            <div class="card">
                <h3><?php echo $lang['packages_header']; ?></h3>
                <?php foreach($packages as $pkg): ?>
                    <div class="item-row">
                        <div>
                            <div style="font-weight: bold;"><?php echo htmlspecialchars($pkg['package_name']); ?></div>
                            <small style="color: #888;"><?php echo $pkg['duration']; ?></small>
                        </div>
                        <div style="text-align: right;">
                            <div class="price-tag">$<?php echo $pkg['price']; ?></div>
                            <a href="booking.php?type=package&id=<?php echo $pkg['id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.75rem; margin-top: 5px;">Book</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <h3><?php echo $lang['hotels_header']; ?></h3>
                <?php foreach($hotels as $hotel): ?>
                    <div class="item-row" style="flex-direction: column; align-items: flex-start;">
                        <div style="width: 100%; display: flex; justify-content: space-between;">
                            <strong><?php echo htmlspecialchars($hotel['hotel_name']); ?></strong>
                            <span><?php echo str_repeat('★', $hotel['stars']); ?></span>
                        </div>
                        <p style="font-size: 0.8rem; color: #777; margin: 5px 0;">🚍 <?php echo $hotel['transport_options']; ?></p>
                        <a href="booking.php?type=hotel&id=<?php echo $hotel['id']; ?>" class="btn btn-outline" style="width: 100%; text-align: center; box-sizing: border-box;">Reserve Stay</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
    const API_KEY = '4395ae39747e4ea5ad1a1580cb0aa901';
    const city = '<?php echo addslashes($destination['name']); ?>';

    async function fetchWeather() {
        try {
            const response = await fetch(`https://api.openweathermap.org/data/2.5/weather?q=${encodeURIComponent(city)}&appid=${API_KEY}&units=metric`);
            const data = await response.json();
            if (data.cod === 200) {
                document.getElementById('weather-icon').innerHTML = `<img src="https://openweathermap.org/img/wn/${data.weather[0].icon}.png" width="50">`;
                document.getElementById('weather-temp').textContent = Math.round(data.main.temp) + '°C';
                document.getElementById('weather-desc').textContent = data.weather[0].description;
                document.getElementById('weather').style.display = 'block';
            }
        } catch (e) { console.error("Weather load failed"); }
    }
    fetchWeather();
</script>

</body>
</html>