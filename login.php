<?php
require_once 'config.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = htmlspecialchars(trim($_POST['email']));
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        try {
            // جلب بيانات المستخدم بناءً على البريد الإلكتروني
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // التحقق من وجود المستخدم وصحة كلمة المرور (سواء مشفرة أو نص عادي للتسهيل)
            if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                // التوجيه الذكي بناءً على الرتبة في قاعدة البيانات
                if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Back | Jessbella</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lobster&display=swap');
        
        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
            font-family: 'Lobster', cursive !important; 
        }
        body { background: #FCFBF9; color: #333; display: flex; flex-direction: column; min-height: 100vh; }
        
        /* Fixed Header Style */
        nav { 
            background: rgba(255, 255, 255, 0.95); 
            box-shadow: 0 2px 20px rgba(86, 4, 14, 0.08); 
            padding: 15px 60px; 
            display: grid; grid-template-columns: 1fr auto 1fr; align-items: center;
            position: sticky; top: 0; z-index: 1000; 
        }
        nav .nav-links-right { display: flex; list-style: none; gap: 30px; }
        nav .logo-center { display: flex; justify-content: center; align-items: center; text-decoration: none; gap: 10px; }
        nav .logo-text { font-size: 36px; color: #56040e; }
        nav .nav-links-left { display: flex; list-style: none; gap: 25px; justify-content: flex-end; }
        nav a { color: #56040e; text-decoration: none; font-weight: 600; font-size: 18px; position: relative; padding-bottom: 5px; }
        nav a::after { content: ''; position: absolute; width: 100%; transform: scaleX(0); height: 2px; bottom: 0; left: 0; background-color: #56040e; transition: transform 0.25s ease-out; }
        nav a:hover::after { transform: scaleX(1); }
        
        .login-container { max-width: 500px; width: 100%; margin: 60px auto; padding: 0 20px; flex-grow: 1; }
        .login-box { background: #fff; border: 1px solid #dfcec0; padding: 40px 30px; box-shadow: 0 4px 15px rgba(86, 4, 14, 0.02); text-align: center; }
        .login-box h2 { color: #56040e; font-size: 36px; margin-bottom: 30px; }
        
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; font-size: 14px; color: #56040e; margin-bottom: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #dfcec0; font-size: 16px; color: #333; background: #eaf2f8 !important; }
        .form-group input:focus { outline: 2px solid #56040e; }
        
        .btn-login { display: block; width: 100%; background: #56040e; color: #fff; padding: 14px; border: none; font-size: 18px; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 25px; text-transform: uppercase; }
        .btn-login:hover { background: #320207; }
        
        .alert-error { background: #f8d7da; color: #721c24; padding: 12px; border: 1px solid #f5c6cb; margin-bottom: 20px; font-size: 16px; font-weight: bold; }
        .switch-text { margin-top: 20px; font-size: 16px; color: #555; }
        .switch-text a { color: #56040e; text-decoration: none; font-weight: bold; }

        footer { background: #56040e; color: #dfcec0; padding: 25px 60px; text-align: center; font-size: 15px; margin-top: auto; }
    </style>
</head>
<body>

    <nav>
        <ul class="nav-links-right">
            <li><a href="index.php">Home</a></li>
        </ul>
        <a href="index.php" class="logo-center">
            <span class="logo-text">Jessbella</span>
        </a>
        <ul class="nav-links-left">
            <li><a href="register.php">Register</a></li>
        </ul>
    </nav>

    <div class="login-container">
        <div class="login-box">
            <h2>Welcome Back</h2>

            <?php if(!empty($error)): ?>
                <div class="alert-error"><?= $error ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="yourname@example.com">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="••••••••••••">
                </div>
                <button type="submit" class="btn-login">Sign In</button>
            </form>

            <p class="switch-text">Don't have a luxury account? <a href="register.php">Register Here</a></p>
        </div>
    </div>

    <footer>
        &copy; 2026 Jessbella Luxury Brands Global Ltd. All rights reserved.
    </footer>

</body>
</html>