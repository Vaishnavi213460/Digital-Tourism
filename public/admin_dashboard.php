<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); exit();
}

// Detect edit mode
$edit_id = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : null;
$edit_type = $_GET['edit_type'] ?? null;
$edit_data = null;

// FETCH DATA
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$destinations = $pdo->query("SELECT * FROM destinations ORDER BY id DESC")->fetchAll();
$packages = $pdo->query("SELECT p.*, d.name as dest_name FROM packages p JOIN destinations d ON p.destination_id = d.id")->fetchAll();
$accommodations = $pdo->query("SELECT a.*, d.name as dest_name FROM accommodations a JOIN destinations d ON a.destination_id = d.id")->fetchAll();
$bookings = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC")->fetchAll();
$messages = $pdo->query("SELECT * FROM support_messages ORDER BY created_at DESC")->fetchAll();

// Load edit data if needed
if ($edit_id && $edit_type) {
    switch ($edit_type) {
        case 'destinations':
            $stmt = $pdo->prepare("SELECT * FROM destinations WHERE id = ?");
            $stmt->execute([$edit_id]);
            $edit_data = $stmt->fetch();
            break;
        case 'packages':
            $stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
            $stmt->execute([$edit_id]);
            $edit_data = $stmt->fetch();
            break;
        case 'accommodations':
            $stmt = $pdo->prepare("SELECT * FROM accommodations WHERE id = ?");
            $stmt->execute([$edit_id]);
            $edit_data = $stmt->fetch();
            break;
    }
    // Redirect if not found
    if (!$edit_data) {
        header("Location: admin_dashboard.php?msg=Record not found");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>TravelEase Admin | CMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary: #ff5a5f; --dark: #2c3e50; --bg: #f4f7f6; --success: #27ae60; --info: #3498db; }
        body { font-family: 'Segoe UI', sans-serif; display: flex; margin: 0; background: var(--bg); color: var(--dark); }
        
        /* Sidebar */
        .sidebar { width: 260px; background: var(--dark); color: white; min-height: 100vh; position: fixed; box-shadow: 2px 0 5px rgba(0,0,0,0.1); }
        .sidebar h2 { padding: 25px 20px; text-align: center; background: #1a252f; margin: 0; font-size: 20px; letter-spacing: 1px; }
        .sidebar-menu { list-style: none; padding: 0; margin: 0; }
        .sidebar-menu li { padding: 15px 25px; cursor: pointer; border-left: 4px solid transparent; transition: 0.3s; display: flex; align-items: center; gap: 10px; }
        .sidebar-menu li:hover, .sidebar-menu li.active { background: #34495e; border-left-color: var(--primary); }
        
        /* Main Content */
        .main-content { margin-left: 260px; flex: 1; padding: 40px; }
        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeIn 0.3s ease; }
        
        /* UI Components */
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        table { width: 100%; background: white; border-collapse: collapse; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
        th { background: var(--primary); color: white; font-weight: 500; text-transform: uppercase; font-size: 12px; }
        tr:hover { background: #f9f9f9; }
        
        /* Buttons */
        .btn { padding: 8px 15px; border-radius: 5px; text-decoration: none; color: white; font-size: 12px; border: none; cursor: pointer; transition: 0.2s; margin-right: 5px; }
        .btn:last-child { margin-right: 0; }
        .btn-add { background: var(--success); font-weight: bold; }
        .btn-delete { background: #e74c3c; }
        .btn-view { background: var(--info); }
        .btn-edit { background: var(--info); }
        .btn-cancel { background: #95a5a6 !important; }
        .action-buttons { display: flex; gap: 8px; align-items: center; }
        
        /* Form Card */
        .card { background: white; padding: 30px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { display: flex; flex-direction: column; }
        label { font-weight: 600; margin-bottom: 5px; color: var(--dark); font-size: 14px; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; outline: none; box-sizing: border-box; }
        textarea { grid-column: span 2; height: 80px; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<div class="sidebar">
    <h2><i class="fa fa-plane-departure"></i> TRAVELEASE</h2>
    <ul class="sidebar-menu">
        <li class="tab-link active" onclick="openTab(event, 'dest-tab')"><i class="fa fa-map-marked-alt"></i> Destinations</li>
        <li class="tab-link" onclick="openTab(event, 'pkg-tab')"><i class="fa fa-box-open"></i> Packages</li>
        <li class="tab-link" onclick="openTab(event, 'hotel-tab')"><i class="fa fa-bed"></i> Accommodations</li>
        <li class="tab-link" onclick="openTab(event, 'user-tab')"><i class="fa fa-user-circle"></i> Users</li>
        <li class="tab-link" onclick="openTab(event, 'booking-tab')"><i class="fa fa-calendar-check"></i> Bookings</li>
        <li class="tab-link" onclick="openTab(event, 'msg-tab')"><i class="fa fa-envelope"></i> Support</li>
        <li style="margin-top:20px"><a href="logout.php" style="color:var(--primary); text-decoration:none;"><i class="fa fa-power-off"></i> Logout</a></li>
    </ul>
</div>

<div class="main-content">
    
    <div id="dest-tab" class="tab-content active">
        <div class="header-flex">
            <h1>Manage Destinations</h1>
            <button class="btn btn-add" onclick="toggleForm('dest-form')">+ Add Destination</button>
        </div>

        <div id="dest-form" class="card" style="<?php echo ($edit_type === 'destinations') ? 'display:block;' : 'display:none;'; ?>">
            <form action="process_admin.php" method="POST" enctype="multipart/form-data" class="grid-form">
                <input type="hidden" name="action" value="<?php echo ($edit_type === 'destinations') ? 'edit_destination' : 'add_destination'; ?>">
                <input type="hidden" name="id" value="<?php echo $edit_data['id'] ?? ''; ?>">
                <div class="form-group">
                    <label for="name">Destination Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($edit_data['name'] ?? ''); ?>" placeholder="Name" required>
                </div>
                <div class="form-group">
                    <label for="location">Location:</label>
                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($edit_data['location'] ?? ''); ?>" placeholder="Location" required>
                </div>
                <div class="form-group">
                    <label for="food_culture">Food &amp; Culture:</label>
                    <input type="text" id="food_culture" name="food_culture" value="<?php echo htmlspecialchars($edit_data['food_culture'] ?? ''); ?>" placeholder="Food Culture">
                </div>
                <div class="form-group">
                    <label for="attractions">Main Attractions:</label>
                    <input type="text" id="attractions" name="attractions" value="<?php echo htmlspecialchars($edit_data['attractions'] ?? ''); ?>" placeholder="Main Attractions">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label>Current Image:</label>
                    <?php if ($edit_data && isset($edit_data['image_url'])): ?>
                        <img src="assets/img/<?php echo htmlspecialchars($edit_data['image_url']); ?>" style="max-width: 100px; max-height: 60px; margin-bottom: 10px;">
                        <br>
                    <?php endif; ?>
                    <label for="image">Destination Image:</label>
                    <input type="file" id="image" name="image" <?php echo ($edit_type !== 'destinations') ? 'required' : ''; ?>>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" placeholder="Description"><?php echo htmlspecialchars($edit_data['description'] ?? ''); ?></textarea>
                </div>
                <div style="grid-column: span 2; display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-add" style="flex:1"><?php echo ($edit_type === 'destinations') ? 'Update Destination' : 'Save Destination'; ?></button>
                    <?php if ($edit_type === 'destinations'): ?>
                        <button type="button" class="btn" style="background: #95a5a6; flex:0.3" onclick="toggleForm('dest-form')">Cancel</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <table>
            <tr><th>Name</th><th>Location</th><th>Attractions</th><th>Actions</th></tr>
            <?php foreach($destinations as $d): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($d['name']?? ''); ?></strong></td>
                <td><?php echo htmlspecialchars($d['location']?? ''); ?></td>
                <td><?php echo htmlspecialchars($d['attractions']?? ''); ?></td>
                <td>
                    <div class="action-buttons">
                        <a href="?edit_id=<?php echo $d['id']; ?>&edit_type=destinations" class="btn btn-edit">
                            <i class="fa fa-edit"></i> Edit
                        </a>
                        <a href="?delete_id=<?php echo $d['id']; ?>&type=destinations" class="btn btn-delete" onclick="return confirm('Delete destination?')"><i class="fa fa-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div id="pkg-tab" class="tab-content">
        <div class="header-flex">
            <h1>Manage Packages</h1>
            <button class="btn btn-add" onclick="toggleForm('pkg-form')">+ Add Package</button>
        </div>
        <div id="pkg-form" class="card" style="<?php echo ($edit_type === 'packages') ? 'display:block;' : 'display:none;'; ?>">
            <form action="process_admin.php" method="POST" class="grid-form">
                <input type="hidden" name="action" value="<?php echo ($edit_type === 'packages') ? 'edit_package' : 'add_package'; ?>">
                <input type="hidden" name="id" value="<?php echo $edit_data['id'] ?? ''; ?>">
                <div class="form-group">
                    <label for="pkg_destination">Destination:</label>
                    <select id="pkg_destination" name="destination_id" required>
                        <option value="">Select Destination</option>
                        <?php foreach($destinations as $d) echo "<option value='{$d['id']}' " . (($edit_data['destination_id'] ?? '') == $d['id'] ? 'selected' : '') . ">{$d['name']}</option>"; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="package_name">Package Title:</label>
                    <input type="text" id="package_name" name="package_name" value="<?php echo htmlspecialchars($edit_data['package_name'] ?? ''); ?>" placeholder="Package Title" required>
                </div>
                <div class="form-group">
                    <label for="duration">Duration:</label>
                    <input type="text" id="duration" name="duration" value="<?php echo htmlspecialchars($edit_data['duration'] ?? ''); ?>" placeholder="Duration (e.g. 3 Days)">
                </div>
                <div class="form-group">
                    <label for="price">Price ($):</label>
                    <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($edit_data['price'] ?? ''); ?>" placeholder="Price ($)">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label for="inclusions">Inclusions:</label>
                    <textarea id="inclusions" name="inclusions" placeholder="Inclusions (Wi-Fi, Tour, etc)"><?php echo htmlspecialchars($edit_data['inclusions'] ?? ''); ?></textarea>
                </div>
                <div style="grid-column: span 2; display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-add" style="flex:1"><?php echo ($edit_type === 'packages') ? 'Update Package' : 'Save Package'; ?></button>
                    <?php if ($edit_type === 'packages'): ?>
                        <button type="button" class="btn" style="background: #95a5a6; flex:0.3" onclick="toggleForm('pkg-form')">Cancel</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <table>
            <tr><th>Title</th><th>Destination</th><th>Price</th><th>Duration</th><th>Action</th></tr>
            <?php foreach($packages as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['package_name']?? ''); ?></td>
                <td><?php echo $p['dest_name']; ?></td>
                <td>$<?php echo $p['price']; ?></td>
                <td><?php echo $p['duration']; ?></td>
                <td>
                    <div class="action-buttons">
                        <a href="?edit_id=<?php echo $p['id']; ?>&edit_type=packages" class="btn btn-edit">
                            <i class="fa fa-edit"></i> Edit
                        </a>
                        <a href="?delete_id=<?php echo $p['id']; ?>&type=packages" class="btn btn-delete" onclick="return confirm('Delete package?')"><i class="fa fa-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div id="hotel-tab" class="tab-content">
        <div class="header-flex">
            <h1>Accommodations</h1>
            <button class="btn btn-add" onclick="toggleForm('hotel-form')">+ Add Hotel</button>
        </div>
        <div id="hotel-form" class="card" style="<?php echo ($edit_type === 'accommodations') ? 'display:block;' : 'display:none;'; ?>">
            <form action="process_admin.php" method="POST" class="grid-form">
                <input type="hidden" name="action" value="<?php echo ($edit_type === 'accommodations') ? 'edit_hotel' : 'add_hotel'; ?>">
                <input type="hidden" name="id" value="<?php echo $edit_data['id'] ?? ''; ?>">
                <div class="form-group">
                    <label for="hotel_destination">Destination:</label>
                    <select id="hotel_destination" name="destination_id" required>
                        <option value="">Select Destination</option>
                        <?php foreach($destinations as $d) echo "<option value='{$d['id']}' " . (($edit_data['destination_id'] ?? '') == $d['id'] ? 'selected' : '') . ">{$d['name']}</option>"; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="hotel_name">Hotel Name:</label>
                    <input type="text" id="hotel_name" name="hotel_name" value="<?php echo htmlspecialchars($edit_data['hotel_name'] ?? ''); ?>" placeholder="Hotel Name" required>
                </div>
                <div class="form-group">
                    <label for="stars">Stars:</label>
                    <input type="number" id="stars" name="stars" min="1" max="5" value="<?php echo htmlspecialchars($edit_data['stars'] ?? ''); ?>" placeholder="Stars (1-5)">
                </div>
                <div class="form-group">
                    <label for="transport_options">Transport Options:</label>
                    <input type="text" id="transport_options" name="transport_options" value="<?php echo htmlspecialchars($edit_data['transport_options'] ?? ''); ?>" placeholder="Transport (Taxi, Shuttle, etc)">
                </div>
                <div style="grid-column: span 2; display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-add" style="flex:1"><?php echo ($edit_type === 'accommodations') ? 'Update Hotel' : 'Save Hotel'; ?></button>
                    <?php if ($edit_type === 'accommodations'): ?>
                        <button type="button" class="btn" style="background: #95a5a6; flex:0.3" onclick="toggleForm('hotel-form')">Cancel</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <table>
            <tr><th>Hotel Name</th><th>Destination</th><th>Stars</th><th>Transport</th><th>Action</th></tr>
            <?php foreach($accommodations as $a): ?>
            <tr>
                <td><?php echo htmlspecialchars($a['hotel_name']?? ''); ?></td>
                <td><?php echo $a['dest_name']; ?></td>
                <td><?php echo str_repeat("⭐", $a['stars']?? ''); ?></td>
                <td><?php echo htmlspecialchars($a['transport_options']?? ''); ?></td>
                <td>
                    <div class="action-buttons">
                        <a href="?edit_id=<?php echo $a['id']; ?>&edit_type=accommodations" class="btn btn-edit">
                            <i class="fa fa-edit"></i> Edit
                        </a>
                        <a href="?delete_id=<?php echo $a['id']; ?>&type=accommodations" class="btn btn-delete" onclick="return confirm('Delete accommodation?')">
                            <i class="fa fa-trash"></i> Delete
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div id="user-tab" class="tab-content">
        <h1>User Management</h1>
        <table>
            <tr><th>ID</th><th>Full Name</th><th>Email</th><th>Preference</th><th>Joined</th><th>Action</th></tr>
            <?php foreach($users as $u): ?>
            <tr>
                <td>#<?php echo $u['id']; ?></td>
                <td><?php echo htmlspecialchars($u['full_name']?? ''); ?></td>
                <td><?php echo $u['email']; ?></td>
                <td><?php echo $u['preference']; ?></td>
                <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                <td><a href="?delete_id=<?php echo $u['id']; ?>&type=users" class="btn btn-delete"><i class="fa fa-user-minus"></i></a></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div id="booking-tab" class="tab-content">
        <h1>Recent Bookings</h1>
        <table>
            <tr><th>Booking ID</th><th>User ID</th><th>Type</th><th>Item ID</th><th>Travel Date</th><th>Booked On</th></tr>
            <?php foreach($bookings as $b): ?>
            <tr>
                <td>#<?php echo $b['id']; ?></td>
                <td>User #<?php echo $b['user_id']; ?></td>
                <td><span class="btn btn-view"><?php echo strtoupper($b['item_type']); ?></span></td>
                <td>ID: <?php echo $b['item_id']; ?></td>
                <td><?php echo $b['travel_date']; ?></td>
                <td><?php echo $b['created_at']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div id="msg-tab" class="tab-content">
        <h1>Support Messages</h1>
        <?php foreach($messages as $m): ?>
            <div class="card">
                <div style="display:flex; justify-content: space-between">
                    <strong>Subject: <?php echo htmlspecialchars($m['subject']?? ''); ?></strong>
                    <span style="color:var(--primary)">Status: <?php echo $m['status']; ?></span>
                </div>
                <p><?php echo htmlspecialchars($m['message'] ?? ''); ?></p>
                <small>From User ID: <?php echo $m['user_id']; ?> | <?php echo $m['created_at']; ?></small>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    function openTab(evt, tabName) {
        var content = document.getElementsByClassName("tab-content");
        for (var i = 0; i < content.length; i++) content[i].classList.remove("active");
        var links = document.getElementsByClassName("tab-link");
        for (var i = 0; i < links.length; i++) links[i].classList.remove("active");
        document.getElementById(tabName).classList.add("active");
        evt.currentTarget.classList.add("active");
    }

    function toggleForm(formId) {
        var f = document.getElementById(formId);
        f.style.display = (f.style.display === "none") ? "block" : "none";
    }
</script>
</body>
</html>