<?php
require_once 'config.php';
session_start();

// التحقق من تسجيل دخول المستخدم
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = false;
$error_message = '';

// جلب العناصر لحساب الإجمالي وعرضها وتأكيدها قبل الدفع والطلب
try {
    $stmt = $pdo->prepare("SELECT c.quantity, p.product_id, p.price, p.name FROM cart_items c JOIN products p ON c.product_id = p.product_id WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

if (count($cart_items) == 0 && !isset($_POST['place_order'])) {
    header("Location: index.php");
    exit();
}

// حساب المجموع الكلي للطلب
$grand_total = 0;
foreach($cart_items as $item) {
    $grand_total += ($item['price'] * $item['quantity']);
}

// معالجة استقبال البيانات وتأكيد الطلب
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $customer_name = htmlspecialchars(trim($_POST['customer_name']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $shipping_address = htmlspecialchars(trim($_POST['shipping_address']));
    
    if (!empty($customer_name) && !empty($phone) && !empty($shipping_address)) {
        try {
            // بدء المعاملة لضمان حفظ كل جداول الفواتير معاً بنجاح
            $pdo->beginTransaction();
            
            // 1. إدخال الطلب الرئيسي في جدول orders
            $order_stmt = $pdo->prepare("INSERT INTO orders (user_id, customer_name, phone, address, total_amount) VALUES (?, ?, ?, ?, ?)");
            $order_stmt->execute([$user_id, $customer_name, $phone, $shipping_address, $grand_total]);
            $order_id = $pdo->lastInsertId();
            
            // 2. تكرار لحفظ تفاصيل المنتجات في جدول order_items
            $item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $update_stock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
            
            foreach ($cart_items as $item) {
                $item_stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
                
                // تحديث كمية المخزون إن وُجدت
                try {
                    $update_stock->execute([$item['quantity'], $item['product_id']]);
                } catch (PDOException $ex) {
                    // يتجاوز الخطأ بسلاسة إذا لم يكن عمود المخزون متوفراً
                }
            }
            
            // 3. تفريغ سلة المشتريات بعد نجاح التثبيت
            $clear_cart = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $clear_cart->execute([$user_id]);
            
            $pdo->commit();
            $success = true;
            $cart_items = []; // تفريغ العرض
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_message = "فشلت عملية حفظ الطلب في النظام: " . $e->getMessage();
        }
    } else {
        $error_message = "الرجاء تعبئة جميع الحقول المطلوبة لضمان التوصيل.";
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout | Jessbella</title>
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
        nav .logo-center img { height: 55px; width: 55px; object-fit: cover; border-radius: 50%; border: 2px solid #dfcec0; }
        nav .logo-text { font-size: 36px; color: #56040e; }
        nav .nav-links-left { display: flex; list-style: none; gap: 25px; justify-content: flex-end; }
        nav a { color: #56040e; text-decoration: none; font-weight: 600; font-size: 18px; position: relative; padding-bottom: 5px; }
        nav a::after { content: ''; position: absolute; width: 100%; transform: scaleX(0); height: 2px; bottom: 0; left: 0; background-color: #56040e; transition: transform 0.25s ease-out; }
        nav a:hover::after { transform: scaleX(1); }
        
        .checkout-container { max-width: 1100px; width: 100%; margin: 40px auto; padding: 0 20px; flex-grow: 1; }
        h2 { color: #56040e; margin-bottom: 30px; font-size: 38px; text-align: center; }
        
        .checkout-grid { display: grid; grid-template-columns: 1fr 400px; gap: 40px; }
        
        .checkout-form-panel, .order-summary-panel { background: #fff; border: 1px solid #dfcec0; padding: 30px; box-shadow: 0 4px 15px rgba(86, 4, 14, 0.02); }
        .panel-title { color: #56040e; font-size: 24px; margin-bottom: 20px; border-bottom: 1px solid #dfcec0; padding-bottom: 10px; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 18px; color: #56040e; margin-bottom: 8px; font-weight: bold; }
        .form-group input, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #dfcec0; font-size: 16px; color: #333; }
        .form-group input:focus, .form-group textarea:focus { outline: 2px solid #56040e; }
        
        .summary-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px dashed #dfcec0; font-size: 18px; }
        .total-box { font-size: 26px; font-weight: bold; color: #56040e; margin-top: 20px; display: flex; justify-content: space-between; }
        
        .btn-order { display: block; width: 100%; background: #56040e; color: #fff; padding: 15px; border: none; font-size: 20px; font-weight: bold; cursor: pointer; transition: 0.3s; text-align: center; margin-top: 25px; text-decoration: none; }
        .btn-order:hover { background: #320207; }
        
        .alert-error { background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; margin-bottom: 25px; font-size: 18px; font-weight: bold; text-align: center; }
        .success-box { text-align: center; padding: 40px; background: #fff; border: 1px solid #dfcec0; box-shadow: 0 4px 15px rgba(86, 4, 14, 0.02); }
        .success-box h1 { color: #56040e; font-size: 36px; margin-bottom: 15px; }
        .success-box p { font-size: 18px; color: #555; margin-bottom: 25px; }

        footer { background: #56040e; color: #dfcec0; padding: 50px 60px 25px 60px; margin-top: auto; }
        .footer-grid { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 40px; }
        .footer-col h3 { font-size: 28px; margin-bottom: 20px; color: #fff; }
        .footer-col p { font-size: 16px; line-height: 1.8; color: #f5f5f5; }
        .footer-bottom { max-width: 1200px; margin: 40px auto 0 auto; padding-top: 20px; border-top: 1px solid rgba(223,206,192,0.2); text-align: center; font-size: 15px; color: #f5f5f5; }

        @media (max-width: 768px) {
            .checkout-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <nav>
        <ul class="nav-links-right">
            <li><a href="index.php">Home</a></li>
            <li><a href="cart.php">My Cart</a></li>
        </ul>
        <a href="index.php" class="logo-center">
            <!-- <img src="logo.jpg" alt="Jessbella Logo"> -->
            <span class="logo-text">Jessbella</span>
        </a>
        <ul class="nav-links-left">
            <li><a href="cart.php">← Return to Bag</a></li>
        </ul>
    </nav>

    <div class="checkout-container">
        <h2>Secure Luxury Checkout</h2>

        <?php if(!empty($error_message)): ?>
            <div class="alert-error"><?= $error_message ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="success-box">
                <h1>✓ Your Order Placed Successfully!</h1>
                <p>Thank you for shopping with us. Your luxury statement pieces are being prepared for shipment.</p>
                <a href="index.php" class="btn-order" style="display:inline-block; width:auto; padding: 12px 40px;">Continue Shopping</a>
            </div>
        <?php else: ?>

            <div class="checkout-grid">
                <div class="checkout-form-panel">
                    <div class="panel-title">Shipping & Delivery Details</div>
                    <form action="checkout.php" method="POST">
                        <div class="form-group">
                            <label for="customer_name">Full Name *</label>
                            <input type="text" id="customer_name" name="customer_name" required placeholder="Enter your full name">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="text" id="phone" name="phone" required placeholder="e.g., +970 59XXXXXXXX">
                        </div>
                        <div class="form-group">
                            <label for="shipping_address">Detailed Shipping Address *</label>
                            <textarea id="shipping_address" name="shipping_address" rows="4" required placeholder="City, District, Street, Landmark..."></textarea>
                        </div>
                        <p style="font-size: 14px; color: #777; font-style: italic;">* Supported Payment: Cash on Delivery (COD).</p>
                        
                        <button type="submit" name="place_order" class="btn-order">Confirm & Place Order 🛒</button>
                    </form>
                </div>

                <div class="order-summary-panel">
                    <div class="panel-title">Your Order Summary</div>
                    <div style="max-height: 250px; overflow-y: auto; margin-bottom: 15px;">
                        <?php foreach($cart_items as $item): ?>
                            <div class="summary-item">
                                <span style="max-width: 70%; text-align: left;"><strong><?= htmlspecialchars($item['name']) ?></strong> (x<?= $item['quantity'] ?>)</span>
                                <span>$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="summary-item" style="color: green; font-weight: bold;">
                        <span>Shipping & Delivery</span>
                        <span>Complimentary</span>
                    </div>
                    <div class="total-box">
                        <span>Grand Total:</span>
                        <span>$<?= number_format($grand_total, 2) ?></span>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <footer>
        <div class="footer-grid">
            <div class="footer-col">
                <h3>Jessbella</h3>
                <p>An elite destination for high-fashion handcrafted women's luxury leather handbags.</p>
            </div>
            <div class="footer-col">
                <h3>Customer Care</h3>
                <p>Email: boutique@jessbella.com<br>Hours: Mon - Fri | 9 AM - 6 PM</p>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; 2026 Jessbella Luxury Brands Global Ltd. All rights reserved.
        </div>
    </footer>

</body>
</html>