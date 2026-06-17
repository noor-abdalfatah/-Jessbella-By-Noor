<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    if ($quantity <= 0) { $quantity = 1; }

    try {
        $check_stmt = $pdo->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
        $check_stmt->execute([$user_id, $product_id]);
        $existing_item = $check_stmt->fetch();

        if ($existing_item) {
            $new_quantity = $existing_item['quantity'] + $quantity;
            $update_stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
            $update_stmt->execute([$new_quantity, $existing_item['cart_item_id']]);
        } else {
            $insert_stmt = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert_stmt->execute([$user_id, $product_id, $quantity]);
        }
        $message = "Item added to your shopping bag successfully!";
    } catch (PDOException $e) {
        $message = "Error adding item to cart.";
    }
}

if (isset($_POST['update_quantity'])) {
    $cart_item_id = intval($_POST['cart_item_id']);
    $new_qty = intval($_POST['quantity']);
    if ($new_qty > 0) {
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ? AND user_id = ?");
        $stmt->execute([$new_qty, $cart_item_id, $user_id]);
    }
    header("Location: cart.php");
    exit();
}

if (isset($_GET['remove'])) {
    $cart_item_id = intval($_GET['remove']);
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_item_id = ? AND user_id = ?");
    $stmt->execute([$cart_item_id, $user_id]);
    header("Location: cart.php");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT c.cart_item_id, c.quantity, p.name, p.price, p.image_url, p.product_id FROM cart_items c JOIN products p ON c.product_id = p.product_id WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Bag | Jessbella</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lobster&display=swap');
        
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Lobster', cursive !important; }
        body { background: #FCFBF9; color: #333; display: flex; flex-direction: column; min-height: 100vh; }
        
        nav { background: rgba(255, 255, 255, 0.95); box-shadow: 0 2px 20px rgba(86, 4, 14, 0.08); padding: 15px 60px; display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; position: sticky; top: 0; z-index: 1000; }
        nav .nav-links-right { display: flex; list-style: none; gap: 30px; }
        nav .logo-center { display: flex; justify-content: center; align-items: center; text-decoration: none; gap: 10px; }
        nav .logo-text { font-size: 36px; color: #56040e; }
        nav .nav-links-left { display: flex; list-style: none; gap: 25px; justify-content: flex-end; }
        nav a { color: #56040e; text-decoration: none; font-weight: 600; font-size: 18px; }

        .cart-container { max-width: 1100px; width: 100%; margin: 40px auto; padding: 0 20px; flex-grow: 1; }
        
        /* التعديل الجوهري هنا: الحاوية المسؤولة عن التمرير */
        .table-responsive { width: 100%; overflow-x: auto; margin-bottom: 40px; border: 1px solid #dfcec0; }
        
        .cart-table { width: 100%; min-width: 600px; background: #fff; border-collapse: collapse; text-align: center; }
        .cart-table th, .cart-table td { padding: 18px; border-bottom: 1px solid #dfcec0; font-size: 18px; }
        .cart-table th { background: #dfcec0; color: #56040e; }
        .prod-img { width: 60px; height: 60px; object-fit: contain; }
        
        .qty-form { display: flex; gap: 5px; justify-content: center; }
        .btn-update { background: #dfcec0; border: none; padding: 5px 10px; cursor: pointer; }

        .btn-delete { 
    color: #56040e;           /* اللون الخمري الخاص بـ Jessbella */
    text-decoration: none;    /* لإزالة الخط من تحت الكلمة */
    font-weight: bold; 
    font-size: 16px;
    cursor: pointer;
}

.btn-delete:hover { 
    text-decoration: none;    /* التأكد من عدم ظهور خط عند التمرير */
    color: #a83232;           /* لون مختلف قليلاً عند مرور الماوس */
}
        
        .cart-summary { background: #fff; padding: 30px; border: 1px solid #dfcec0; text-align: right; max-width: 450px; margin-left: auto; }
        .btn-checkout { display: inline-block; background: #56040e; color: #fff; padding: 14px 40px; text-decoration: none; }

        @media (max-width: 992px) {
            nav { grid-template-columns: 1fr; text-align: center; gap: 10px; }
            .nav-links-right, .nav-links-left { justify-content: center; }
        }
    </style>
</head>
<body>

    <nav>
        <ul class="nav-links-right">
            <li><a href="index.php">Home</a></li>
            <li><a href="cart.php">My Cart</a></li>
        </ul>
        <a href="index.php" class="logo-center"><span class="logo-text">Jessbella</span></a>
        <ul class="nav-links-left">
            <li><a href="index.php">← Back</a></li>
        </ul>
    </nav>

    <div class="cart-container">
        <h2>Your Shopping Bag</h2>
        
        <?php if(!empty($message)): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>

        <?php if(count($cart_items) == 0): ?>
            <div class="empty-cart"><h3>Your bag is empty!</h3><a href="index.php">Discover Collections</a></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Preview</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grand_total = 0;
                        foreach($cart_items as $item): 
                            $subtotal = $item['price'] * $item['quantity'];
                            $grand_total += $subtotal;
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><img src="<?= htmlspecialchars($item['image_url']) ?>" class="prod-img"></td>
                                <td>$<?= number_format($item['price'], 2) ?></td>
                                <td>
                                    <form action="cart.php" method="POST" class="qty-form">
                                        <input type="hidden" name="cart_item_id" value="<?= $item['cart_item_id'] ?>">
                                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" style="width: 50px;">
                                        <button type="submit" name="update_quantity" class="btn-update">Update</button>
                                    </form>
                                </td>
                                <td>$<?= number_format($subtotal, 2) ?></td>
                                <td><a href="cart.php?remove=<?= $item['cart_item_id'] ?>" class="btn-delete" onclick="return confirm('Remove?')">Remove</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-summary">
                <h3>Total: $<?= number_format($grand_total, 2) ?></h3>
                <a href="checkout.php" class="btn-checkout">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>