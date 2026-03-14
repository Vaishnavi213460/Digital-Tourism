<?php
session_start();
require_once '../config/db.php';
require_once '../includes/lang_loader.php';
$lang = loadLanguage();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$_POST['email']]);
    $user = $stmt->fetch();

    if ($user && password_verify($_POST['password'], $user['password'])) {
        // SETTING ALL NECESSARY SESSION DATA
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_preference'] = $user['preference']; 
        $_SESSION['role'] = $user['role_type'];
        
        if ($_SESSION['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
    $error = $lang['invalid_credentials'] ?? "Invalid email or password!";
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $lang['login_title']; ?></title>
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body style="background:#f4f7f6; display:flex; justify-content:center; align-items:center; height:100vh; margin:0;">
    <div style="background:white; padding:40px; border-radius:10px; box-shadow:0 4px 15px rgba(0,0,0,0.1); width:350px;">
        <h2 style="text-align:center;"><?php echo $lang['login_header']; ?></h2>
        
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'account_created'): ?>
            <p style="color:green; text-align:center;"><?php echo $lang['account_created']; ?></p>
        <?php endif; ?>

        <?php if(isset($error)) echo "<p style='color:red; text-align:center;'>" . ($lang['invalid_credentials'] ?? $error) . "</p>"; ?>
        
        <form method="POST">
            <input type="email" name="email" placeholder="<?php echo $lang['email_placeholder']; ?>" required style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ddd; border-radius:5px; box-sizing:border-box;">
            <input type="password" name="password" placeholder="<?php echo $lang['password_placeholder']; ?>" required style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ddd; border-radius:5px; box-sizing:border-box;">
            <button type="submit" class="btn" style="width:100%; cursor:pointer;"><?php echo $lang['login_btn']; ?></button>
        </form>
        <p style="margin-top:20px; text-align:center;">
            <?php echo $lang['no_account']; ?> <a href="signup.php" style="color:#ff5a5f;"><?php echo $lang['signup_link']; ?></a>
        </p>
    </div>
</body>
</html>