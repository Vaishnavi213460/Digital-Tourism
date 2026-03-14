<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

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
    <title>My Dashboard | TravelEase</title>
    <style>
        :root { --primary: #ff5a5f; --secondary: #008489; --bg: #f7f9fc; }
        body { background: var(--bg); font-family: 'Inter', 'Segoe UI', sans-serif; margin: 0; padding: 40px; }
        
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; max-width: 1100px; margin: 0 auto 30px; }
        .dashboard-header h1 { font-size: 24px; color: #333; }
        
        .booking-card { 
            background: white; border-radius: 16px; max-width: 1100px; margin: 0 auto 25px; 
            display: flex; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border: 1px solid #eee;
        }

        .details-side { flex: 1.5; padding: 30px; position: relative; }
        .map-side { flex: 1; background: #e5e5e5; min-height: 300px; }

        .header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .dest-title { font-size: 28px; margin: 0; color: #1a1a1a; }
        
        .status-badge { 
            background: #e6f7ed; color: #2ecc71; padding: 6px 14px; 
            border-radius: 50px; font-size: 12px; font-weight: 700; text-transform: uppercase;
        }

        .weather-badge {
            display: flex; align-items: center; background: #f0f7ff;
            padding: 8px 16px; border-radius: 12px; margin-bottom: 20px; width: fit-content;
            border: 1px solid #d0e7ff;
        }
        .weather-temp { font-size: 18px; font-weight: bold; color: #0056b3; margin-right: 10px; }
        .weather-desc { font-size: 13px; color: #555; text-transform: capitalize; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 20px; }
        .info-item { display: flex; align-items: center; font-size: 15px; color: #444; }
        .info-item span { margin-right: 10px; font-size: 18px; }

        .type-tag {
            margin-top: 20px; padding: 4px 10px; background: #f1f1f1;
            border-radius: 6px; font-size: 12px; font-weight: 600; color: #666; width: fit-content;
        }

        .btn-home { color: var(--primary); text-decoration: none; font-weight: 600; font-size: 14px; }
        
        iframe { filter: grayscale(0.2); transition: 0.3s; }
        iframe:hover { filter: grayscale(0); }
    </style>
</head>
<body>

    <div class="dashboard-header">
        <h1>My Travel Dashboard</h1>
        <a href="index.php" class="btn-home">← Back to Explore</a>
    </div>

    <?php foreach($user_bookings as $b): ?>
        <div class="booking-card">
            <div class="details-side">
                <div class="header-row">
                    <h2 class="dest-title"><?php echo htmlspecialchars($b['dest_name']); ?></h2>
                    <span class="status-badge">Confirmed</span>
                </div>
                
                <div id="weather-<?php echo $b['id']; ?>" class="weather-badge" style="display: none;">
                    <div id="weather-icon-<?php echo $b['id']; ?>"></div>
                    <div>
                        <div id="weather-temp-<?php echo $b['id']; ?>" class="weather-temp"></div>
                        <div id="weather-desc-<?php echo $b['id']; ?>" class="weather-desc"></div>
                    </div>
                </div>
                
                <div class="info-grid">
                    <div class="info-item"><span>📅</span> <?php echo date('M j, Y', strtotime($b['travel_date'])); ?></div>
                    <div class="info-item"><span>👤</span> <?php echo htmlspecialchars($b['full_name']); ?></div>
                </div>

                <div class="type-tag">
                    <?php echo $b['item_type'] == 'package' ? '✈️ PACKAGE DEAL' : '🏨 HOTEL ACCOMMODATION'; ?>
                </div>
            </div>
            
            <div class="map-side">
                <iframe 
                    width="100%" height="100%" frameborder="0" style="border:0"
                    src="https://maps.google.com/maps?q=<?php echo urlencode($b['dest_name'] . ' ' . $b['dest_location']); ?>&t=&z=13&ie=UTF8&iwloc=&output=embed" 
                    allowfullscreen>
                </iframe>
            </div>
        </div>
    <?php endforeach; ?>

    <script>
    const WEATHER_API_KEY = '4395ae39747e4ea5ad1a1580cb0aa901';

    async function fetchWeather(bookingId, city) {
        try {
            const resp = await fetch(`https://api.openweathermap.org/data/2.5/weather?q=${encodeURIComponent(city)}&appid=${WEATHER_API_KEY}&units=metric`);
            const data = await resp.json();
            if(data.cod === 200) {
                document.getElementById('weather-icon-' + bookingId).innerHTML = `<img src="https://openweathermap.org/img/wn/${data.weather[0].icon}.png" width="40">`;
                document.getElementById('weather-temp-' + bookingId).textContent = Math.round(data.main.temp) + '°C';
                document.getElementById('weather-desc-' + bookingId).textContent = data.weather[0].description;
                document.getElementById('weather-' + bookingId).style.display = 'flex';
            }
        } catch(e) { console.error("Weather load failed"); }
    }

    window.onload = () => {
        <?php foreach($user_bookings as $b): ?>
            fetchWeather(<?php echo $b['id']; ?>, '<?php echo addslashes($b['dest_name']); ?>');
        <?php endforeach; ?>
    };
    </script>
</body>
</html>