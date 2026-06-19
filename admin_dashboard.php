<?php
require_once 'config.php';
session_start();

// التحقق من صلاحية الأدمن
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Jessbella</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Lato', sans-serif; background: #f9f7f2; margin: 0; display: flex; min-height: 100vh; }
        
        /* Sidebar Styles */
        .sidebar { width: 280px; background: #56040e; color: #fff; padding: 30px 20px; flex-shrink: 0; }
        .sidebar h3 { text-align: center; margin-bottom: 30px; }
        .sidebar a { display: block; color: #dfcec0; padding: 12px; text-decoration: none; margin-bottom: 10px; border-radius: 4px; transition: 0.3s; }
        .sidebar a.active, .sidebar a:hover { background: rgba(255,255,255,0.1); color: #fff; }
        
        /* Main Content Styles */
        .main-content { flex: 1; padding: 40px; overflow-x: hidden; }
        .stats-container { display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px; }
        .stat-card { background: #fff; padding: 30px; border-radius: 8px; border: 1px solid #dfcec0; flex: 1; min-width: 200px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .stat-card h3 { font-family: 'Playfair Display', serif; color: #56040e; margin-bottom: 10px; }
        .stat-card p { font-size: 28px; font-weight: bold; color: #333; }

        /* التعديل الجوهري للتجاوب (Responsive) */
        @media (max-width: 768px) {
            body { flex-direction: column; } /* تحويل التصميم لعمودي في الجوال */
            .sidebar { width: 100%; height: auto; padding: 20px; }
            .main-content { padding: 20px; }
            .stats-container { flex-direction: column; }
            h1 { font-size: 24px; text-align: center; }
        }
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
                <p><?= htmlspecialchars($total_products) ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Categories</h3>
                <p><?= htmlspecialchars($total_categories) ?></p>
            </div>
        </div>
    </div>

</body>
</html>