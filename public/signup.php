<?php
require_once '../config/db.php';

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hashed_pw = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, preference) VALUES (?, ?, ?, ?)");
    
    try {
        $stmt->execute([$_POST['full_name'], $_POST['email'], $hashed_pw, $_POST['preference']]);
        header("Location: login.php?msg=account_created");
        exit();
    } catch (PDOException $e) {
        $error = "This email is already registered!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Digital Tourism</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <style>
        body {
            background: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .auth-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        .auth-card h2 {
            margin-top: 0;
            color: #333;
            text-align: center;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 0.9rem;
            color: #666;
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box; /* Ensures padding doesn't affect width */
            font-size: 1rem;
        }

        input:focus {
            border-color: #ff5a5f;
            outline: none;
            box-shadow: 0 0 5px rgba(255, 90, 95, 0.2);
        }

        .btn-signup {
            width: 100%;
            background: #ff5a5f;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }

        .btn-signup:hover {
            background: #e0484e;
        }

        .error-msg {
            background: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            text-align: center;
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: #666;
        }

        .footer-text a {
            color: #ff5a5f;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="auth-card">
    <h2>Create Account</h2>

    <?php if ($error): ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" placeholder="John Doe" required>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="name@example.com" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <div class="form-group">
            <label>Travel Preference</label>
            <select name="preference">
                <option value="Nature">Nature & Outdoors</option>
                <option value="Culture">History & Culture</option>
                <option value="Luxury">Luxury & Shopping</option>
                <option value="Adventure">Adventure Sports</option>
            </select>
        </div>

        <button type="submit" class="btn-signup">Sign Up</button>
    </form>

    <div class="footer-text">
        Already have an account? <a href="login.php">Login</a>
        <br><br>
        <a href="index.php" style="color: #999; font-weight: normal;">← Back to Home</a>
    </div>
</div>

</body>
</html>