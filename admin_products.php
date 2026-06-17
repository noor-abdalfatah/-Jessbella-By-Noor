<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_category'])) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute([$_POST['category_name'], $_POST['category_desc']]);
        $message = "Category added successfully!";
    }
    
    if (isset($_POST['add_product'])) {
        // تم إضافة الحقل الجديد هنا في المصفوفة
        $stmt = $pdo->prepare("INSERT INTO products (name, category_id, price, stock_quantity, image_url, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['name'], 
            $_POST['category_id'], 
            $_POST['price'], 
            $_POST['stock'], 
            $_POST['image_url'], 
            $_POST['description'] // جلب القيمة من النموذج الجديد
        ]);
        $message = "Product added successfully!";
    }
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.product_id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="css/all.min.css" rel="stylesheet">
    <title>Admin Dashboard - Jessbella</title>
    <style>
        body { font-family: 'Lato', sans-serif; background: #f9f7f2; color: #333; margin: 0; display: flex; }
        h1, h2, h3 { font-family: 'Playfair Display', serif; color: #4a0404; }
        .sidebar { width: 250px; background: #4a0404; color: #fff; height: 100vh; padding: 20px; }
        .sidebar a { display: block; color: #fff; padding: 15px; text-decoration: none; border-bottom: 1px solid #6d0606; }
        .sidebar a:hover { background: #6d0606; }
        .main-content { flex: 1; padding: 30px; }
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #4a0404; color: #fff; }
        .btn-save { background: #4a0404; color: #fff; border: none; padding: 10px 20px; cursor: pointer; width: 100%; border-radius: 5px; font-weight: bold; }
        .btn-save:hover { background: #6d0606; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Jessbella Admin</h2>
        <a href="admin_products.php">Manage Products</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="main-content">
        <h1>Catalog & Inventory Control</h1>
        <?php if($message): ?><div class="alert"><?= $message ?></div><?php endif; ?>
        
        <div style="display: flex; gap: 20px;">
            <div class="card" style="width: 40%;">
                <h3>Create New Category</h3>
                <form method="POST">
                    <input type="text" name="category_name" placeholder="Category Name" required style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:4px;">
                    <textarea name="category_desc" placeholder="Description" style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:4px;"></textarea>
                    <button type="submit" name="add_category" class="btn-save">Save Category</button>
                </form>
            </div>
            
            <div class="card" style="width: 60%;">
                <h3>Add Premium Product</h3>
                <form method="POST">
                    <input type="text" name="name" placeholder="Product Title" required style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:4px;">
                    <select name="category_id" style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:4px;">
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>"><?= $cat['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="price" placeholder="Price ($)" required style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:4px;">
                    <input type="number" name="stock" value="1" style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:4px;">
                    <textarea name="description" placeholder="Product Description"  style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:4px;"></textarea>
                    <input type="text" name="image_url" placeholder="Image URL (e.g., images/bag1.jpg)" style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:4px;">
                    <button type="submit" name="add_product" class="btn-save">Deploy Product</button>
                </form>
            </div>
        </div>

        <div class="card">
            <h3>Active Collections Catalog</h3>
            <table>
                <tr><th>Title</th><th>Category</th><th>Price</th><th>Stock</th></tr>
                <?php foreach($products as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['category_name']) ?></td>
                        <td>$<?= number_format($p['price'], 2) ?></td>
                        <td><?= $p['stock_quantity'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>