<?php
session_start();
require_once '../config/db.php';
require_once '../includes/lang_loader.php';
$lang = loadLanguage();

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}

// 1. Handle Search Query
$search = isset($_GET['search']) ? $_GET['search'] : '';
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM destinations WHERE name LIKE ? OR location LIKE ?");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM destinations");
}
$destinations = $stmt->fetchAll();

// 2. Get Personalized Recommendations
$recommended = [];
if (isset($_SESSION['user_id']) && isset($_SESSION['user_preference'])) {
    $pref = $_SESSION['user_preference'];
    $stmt_pref = $pdo->prepare("SELECT * FROM destinations WHERE (description LIKE ? OR attractions LIKE ?) AND name NOT LIKE ? LIMIT 3");
    $stmt_pref->execute(["%$pref%", "%$pref%", "%$search%"]);
    $recommended = $stmt_pref->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $lang['login_title'] ?? 'Digital Tourism'; ?> | <?php echo $lang['hero_explore_world'] ?? 'Explore the World'; ?></title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <style>
        /* Hero Section Styling */
        .hero {
            height: 60vh;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), 
                        url('./assets/img/hero-bg.jpg') center/cover no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            padding: 0 20px;
        }

        .search-box {
            margin-top: 20px;
            background: white;
            padding: 10px;
            border-radius: 50px;
            display: flex;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .search-box input {
            flex: 1;
            border: none;
            padding: 10px 20px;
            outline: none;
            font-size: 1rem;
            border-radius: 50px 0 0 50px;
        }

        .search-box button {
            background: #ff5a5f;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: bold;
        }

        .nav-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 5%;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .section-title { padding: 40px 5% 10px; }
        .destination-container { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
            gap: 25px; 
            padding: 20px 5%; 
        }
        .card { border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); background: white; transition: 0.3s; }
        .card:hover { transform: translateY(-5px); }
        .card img { width: 100%; height: 200px; object-fit: cover; }
        .card-content { padding: 15px; }
    </style>
</head>
<body>

    <nav class="nav-bar">
        <h2 style="color: #ff5a5f; margin:0;">DigitalTravel</h2>
        <div style="display: flex; align-items: center; gap: 10px;">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span>Hello, <strong><?php echo $_SESSION['user_name']; ?></strong></span>
                    <a href="dashboard.php" style="margin-left:15px; text-decoration:none; color:#333;"><?php echo $lang['nav_my_bookings']; ?></a>
                    <a href="logout.php" style="margin-left:15px; color:#ff5a5f; font-weight:bold;"><?php echo $lang['nav_logout']; ?></a>
                <?php else: ?>
                    <a href="login.php" class="btn" style="padding: 8px 20px; text-decoration:none;"><?php echo $lang['nav_login']; ?></a>
                    <a href="signup.php" style="margin-left:15px; color:#333;"><?php echo $lang['nav_signup']; ?></a>
                <?php endif; ?>
<select onchange="changeLang(this.value)" style="padding: 5px; border-radius: 4px; border: 1px solid #ddd;">
                    <option value="en" <?php echo ($_SESSION['lang'] ?? 'en') == 'en' ? 'selected' : ''; ?>>English</option>
                    <option value="es" <?php echo ($_SESSION['lang'] ?? 'en') == 'es' ? 'selected' : ''; ?>>Español</option>
                    <option value="fr" <?php echo ($_SESSION['lang'] ?? 'en') == 'fr' ? 'selected' : ''; ?>>Français</option>
                </select>
            </div>
    </nav>

    <header class="hero">
        <h1 style="font-size: 3rem; margin: 0;"><?php echo $lang['hero_explore_world']; ?></h1>
        <p style="font-size: 1.2rem;"><?php echo $lang['hero_subtitle']; ?></p>
        
        <form action="index.php" method="GET" class="search-box">
            <input type="text" name="search" placeholder="<?php echo $lang['search_placeholder']; ?>" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit"><?php echo $lang['search_btn']; ?></button>
        </form>
    </header>

    <?php if (!empty($recommended) && empty($search)): ?>
    <section class="personalized">
        <h2 class="section-title"><?php echo $lang['section_recommended']; ?></h2>
        <div class="destination-container">
            <?php foreach($recommended as $dest): ?>
                <div class="card" style="border: 2px solid #3498db;">
                    <img src="./assets/img/<?php echo $dest['image_url']; ?>" alt="<?php echo $dest['name']; ?>">
                    <div class="card-content">
                        <h3><?php echo htmlspecialchars($dest['name']); ?> <small class="badge" style="background:#3498db; font-size:0.6rem;">Match</small></h3>
                        <a href="destination-details.php?id=<?php echo $dest['id']; ?>" class="btn"><?php echo $lang['explore_now']; ?></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <hr style="margin: 0 5%; opacity: 0.2;">
    <?php endif; ?>

<h2 class="section-title"><?php echo !empty($search) ? sprintf($lang['search_results_for'], $search) : $lang['section_popular']; ?></h2>
    <div class="destination-container">
        <?php if(empty($destinations)): ?>
            <p>No destinations found matching your search.</p>
        <?php endif; ?>

        <?php foreach($destinations as $dest): ?>
            <div class="card">
                <img src="./assets/img/<?php echo $dest['image_url']; ?>" alt="<?php echo $dest['name']; ?>">
                <div class="card-content">
                    <h3><?php echo htmlspecialchars($dest['name']); ?></h3>
                    <p style="color: #666; font-size: 0.9rem;"><strong>Top Food:</strong> <?php echo htmlspecialchars($dest['food_culture']); ?></p>
                    <p><?php echo substr(htmlspecialchars($dest['description']), 0, 100); ?>...</p>
                    <a href="destination-details.php?id=<?php echo $dest['id']; ?>" class="btn">Explore Now</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div id="chat-bubble" onclick="toggleChat()" style="position:fixed; bottom:20px; right:20px; background:#ff5a5f; color:white; padding:15px; border-radius:50%; cursor:pointer; box-shadow:0 4px 10px rgba(0,0,0,0.3); z-index:1000;">
        💬
    </div>

    <div id="chat-box" style="display:none; position:fixed; bottom:80px; right:20px; width:300px; background:white; border-radius:10px; box-shadow:0 5px 20px rgba(0,0,0,0.2); z-index:1000; overflow:hidden;">
        <div style="background:#ff5a5f; color:white; padding:15px; font-weight:bold;"><?php echo $lang['chat_support']; ?></div>
        <form action="contact_handler.php" method="POST" style="padding:15px;">
            <?php if(isset($_SESSION['user_id'])): ?>
                <label style="font-size:0.8rem; color:#666;">How can we help, <?php echo $_SESSION['user_name']; ?>?</label>
                <textarea name="message" placeholder="<?php echo $lang['chat_message_placeholder']; ?>" required style="width:100%; height:80px; margin-top:10px; padding:5px; border:1px solid #ddd;"></textarea>
                <button type="submit" style="width:100%; background:#ff5a5f; color:white; border:none; padding:10px; margin-top:10px; border-radius:5px; cursor:pointer;">Send Message</button>
            <?php else: ?>
                <p style="font-size:0.9rem; text-align:center;">Please <a href="login.php">Login</a> to chat with support.</p>
            <?php endif; ?>
        </form>
    </div>

    <script>
        function toggleChat() {
            var box = document.getElementById('chat-box');
            box.style.display = (box.style.display === 'none') ? 'block' : 'none';
        }
        function changeLang(lang) {
            window.location.href = '?lang=' + lang;
        }
    </script>
</body>
</html>