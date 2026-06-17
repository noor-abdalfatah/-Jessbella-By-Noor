<?php
require_once 'config.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are strictly required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        try {
            // التحقق من عدم تكرار الحساب
            $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                $error = "This email is already registered to an account.";
            } else {
                // إدراج الحساب الجديد ودور المستخدم الافتراضي هو زبون (customer)
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'customer')");
                $stmt->execute([$full_name, $email, $password]); // أو استخدمي password_hash($password, PASSWORD_DEFAULT) إن رغبتِ بتشفيره
                
                $success = "Your account has been created! Moving to login...";
                header("refresh:2; url=login.php");
            }
        } catch (PDOException $e) {
            $error = "Registration failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Jessbella</title>
    <style>
       @import url('https://fonts.googleapis.com/css2?family=Lobster&display=swap');
        
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Montserrat', sans-serif; }
        body { background: #FCFBF9; color: #333; display: flex; flex-direction: column; min-height: 100vh; }
        
        /* Sticky Header */
        nav { 
            background: rgba(255, 255, 255, 0.95); 
            box-shadow: 0 2px 20px rgba(86, 4, 14, 0.08); 
            padding: 15px 60px; 
            display: grid; grid-template-columns: 1fr auto 1fr; align-items: center;
            position: sticky; top: 0; z-index: 1000; 
        }
        nav .nav-links-right { display: flex; list-style: none; gap: 30px; }
        nav .logo-center { display: flex; justify-content: center; align-items: center; text-decoration: none; gap: 10px; }
        nav .logo-text { font-family: 'Lobster', cursive; font-size: 32px; color: #56040e; }
        nav .nav-links-left { display: flex; list-style: none; gap: 25px; justify-content: flex-end; }
        nav a { color: #56040e; text-decoration: none; font-weight: 600; font-size: 14px; position: relative; padding-bottom: 5px; }
        nav a::after { content: ''; position: absolute; width: 100%; transform: scaleX(0); height: 2px; bottom: 0; left: 0; background-color: #56040e; transition: transform 0.25s ease-out; }
        nav a:hover::after { transform: scaleX(1); }

        /* Form Container */
        .auth-container { max-width: 500px; width: 100%; margin: 50px auto; background: #fff; border: 1px solid #dfcec0; padding: 40px; border-radius: 4px; box-shadow: 0 4px 15px rgba(86, 4, 14, 0.03); }
        .auth-container h2 { font-family: 'Lobster', cursive; font-size: 36px; color: #56040e; text-align: center; margin-bottom: 25px; }
        
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #56040e; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-group input { width: 100%; padding: 12px 15px; border: 1px solid #dfcec0; border-radius: 2px; font-size: 14px; color: #333; outline: none; transition: border-color 0.3s; }
        .form-group input:focus { border-color: #56040e; }
        
        .btn-auth { width: 100%; background: #56040e; color: #fff; border: none; padding: 14px; font-size: 14px; font-weight: bold; cursor: pointer; transition: 0.3s; border-radius: 2px; text-transform: uppercase; letter-spacing: 1px; margin-top: 10px; }
        .btn-auth:hover { background: #320207; }
        
        .auth-switch { text-align: center; margin-top: 20px; font-size: 14px; color: #666; }
        .auth-switch a { color: #56040e; font-weight: bold; text-decoration: none; }
        .auth-switch a:hover { text-decoration: underline; }
        
        .error-box { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 2px; border: 1px solid #f5c6cb; font-size: 14px; margin-bottom: 20px; text-align: center; }
        .success-box { background: #d4edda; color: #155724; padding: 12px; border-radius: 2px; border: 1px solid #c3e6cb; font-size: 14px; margin-bottom: 20px; text-align: center; font-weight: 500; }

        footer { background: #56040e; color: #dfcec0; padding: 30px 60px; margin-top: auto; text-align: center; font-size: 13px; }
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
            <li><a href="login.php">Login</a></li>
        </ul>
    </nav>

    <div class="auth-container">
        <h2>Create Account</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error-box"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success-box"><?= $success ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" placeholder="Enter your full name" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Create a strong password" required>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="Repeat your password" required>
            </div>
            
            <button type="submit" class="btn-auth">Register Now</button>
        </form>
        
        <div class="auth-switch">
            Already have a signature account? <a href="login.php">Sign In</a>
        </div>
    </div>

    <footer>
        &copy; 2026 Jessbella Luxury Brands Global Ltd. All rights reserved.
    </footer>

</body>
</html>