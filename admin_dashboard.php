<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// جلب إحصائيات سريعة
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Jessbella</title>
    <link href="css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Lato', sans-serif; background: #f9f7f2; margin: 0; display: flex; }
        .sidebar { width: 280px; background: #56040e; color: #fff; height: 100vh; padding: 30px 20px; }
        .sidebar a { display: block; color: #dfcec0; padding: 12px; text-decoration: none; margin-bottom: 10px; }
        .sidebar a.active { background: rgba(255,255,255,0.1); color: #fff; }
        .main-content { flex: 1; padding: 40px; }
        .stats-container { display: flex; gap: 20px; }
        .stat-card { background: #fff; padding: 30px; border-radius: 8px; border: 1px solid #dfcec0; flex: 1; text-align: center; }
        .stat-card h3 { font-family: 'Playfair Display', serif; color: #56040e; }
        .stat-card p { font-size: 24px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>Jessbella Admin</h3>
        <a href="admin_dashboard.php" class="active">Dashboard (Stats)</a>
        <a href="admin_products.php">Manage Catalog</a>
        <a href="index.php">Live Website</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="main-content">
        <h1>Welcome back, Admin</h1>
        <div class="stats-container">
            <div class="stat-card">
                <h3>Total Products</h3>
                <p><?= $total_products ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Categories</h3>
                <p><?= $total_categories ?></p>
            </div>
        </div>
    </div>
</body>
</html>