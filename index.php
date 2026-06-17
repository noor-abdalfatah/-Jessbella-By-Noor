<?php
require_once 'config.php';
session_start();

try {
    $categories = $pdo->query("SELECT * FROM categories")->fetchAll();
    
    $category_filter = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
    $search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    if ($category_filter > 0 && !empty($search_query)) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND (name LIKE ? OR description LIKE ?) ORDER BY product_id DESC");
        $stmt->execute([$category_filter, "%$search_query%", "%$search_query%"]);
        $products = $stmt->fetchAll();
    } elseif ($category_filter > 0) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? ORDER BY product_id DESC");
        $stmt->execute([$category_filter]);
        $products = $stmt->fetchAll();
    } elseif (!empty($search_query)) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ? ORDER BY product_id DESC");
        $stmt->execute(["%$search_query%", "%$search_query%"]);
        $products = $stmt->fetchAll();
    } else {
        $products = $pdo->query("SELECT * FROM products ORDER BY product_id DESC")->fetchAll();
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jessbella | Premium Handbags</title>
    <style>
        /* استدعاء الخط المطلوب من قبلكِ */
       @import url('https://fonts.googleapis.com/css2?family=Lobster&display=swap');
        
        /* تطبيق الخط على جميع عناصر الموقع بلا استثناء */
        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
            font-family: 'Lobster', cursive !important; 
        }
        
        body { background: #FCFBF9; color: #333; scroll-behavior: smooth; }
        
        /* 1. Sticky Navbar Layout with Centered Logo */
        nav { 
            background: rgba(255, 255, 255, 0.95); 
            box-shadow: 0 2px 20px rgba(86, 4, 14, 0.08); 
            padding: 15px 60px; 
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center; 
            position: sticky; 
            top: 0; 
            z-index: 1000; 
            backdrop-filter: blur(5px);
        }
        
        /* Right Side Links */
        nav .nav-links-right { display: flex; list-style: none; gap: 30px; justify-content: flex-start; }
        
        /* Center Logo */
        nav .logo-center { display: flex; justify-content: center; align-items: center; text-decoration: none; gap: 15px; }
        
        /* صورة اللوجو - يمكنكِ تغيير اسم الملف logo.jpg إلى اسم صورتكِ الحقيقية */
        nav .logo-center img { height: 55px; width: 55px; object-fit: cover; border-radius: 50%; border: 2px solid #dfcec0; }
        nav .logo-text { font-size: 36px; color: #56040e; letter-spacing: 1px; }
        
        /* Left Side Auth/Logout Links */
        nav .nav-links-left { display: flex; list-style: none; gap: 25px; justify-content: flex-end; align-items: center; }
        
        /* Premium Underline Hover Animation */
        nav a { color: #56040e; text-decoration: none; font-weight: 600; font-size: 18px; letter-spacing: 0.5px; position: relative; padding-bottom: 5px; transition: color 0.3s; }
        nav a::after { content: ''; position: absolute; width: 100%; transform: scaleX(0); height: 2px; bottom: 0; left: 0; background-color: #56040e; transform-origin: bottom right; transition: transform 0.25s ease-out; }
        nav a:hover::after { transform: scaleX(1); transform-origin: bottom left; }
        
        nav .user-badge { font-size: 16px; background: #dfcec0; color: #56040e; padding: 6px 14px; border-radius: 20px; font-weight: bold; }
        nav .btn-logout { color: #b33939 !important; }
        nav .btn-logout::after { background-color: #b33939 !important; }

        /* 2. Hero Section with Luxury Background and Dark Transparent Overlay */
        .hero { 
            position: relative; 
            height: 75vh; 
            background: url('images/background6.jpg') center/cover no-repeat; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            text-align: center; 
            color: #fff;
            padding: 0 20px;
        }
        .hero-overlay { 
            position: absolute; 
            top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(0, 0, 0, 0.4); 
            z-index: 1;
        }
        .hero-content { position: relative; z-index: 2; max-width: 800px; }
        .hero-content h1 { font-size: 72px; margin-bottom: 15px; color: #dfcec0; text-shadow: 2px 2px 4px rgba(0,0,0,0.4); }
        .hero-content p { font-size: 26px; font-weight: 400; letter-spacing: 1px; color: #f5f5f5; }

        /* Search Area Component */
        .search-area { max-width: 1200px; margin: 40px auto 10px auto; padding: 0 20px; display: flex; justify-content: flex-end; }
        .search-form { display: flex; width: 100%; max-width: 400px; gap: 10px; background: #fff; padding: 5px; border-radius: 30px; border: 1px solid #dfcec0; }
        .search-input { flex-grow: 1; border: none; padding: 10px 15px; font-size: 16px; border-radius: 25px; outline: none; color: #56040e; }
        .btn-search { background: #56040e; color: #fff; border: none; padding: 10px 22px; border-radius: 25px; font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 16px; }
        .btn-search:hover { background: #dfcec0; color: #56040e; }

        /* Main Container Content */
        .container { display: flex; max-width: 1200px; margin: 20px auto 60px auto; padding: 0 20px; gap: 40px; }
        
        /* Sidebar Filter */
        .categories-sidebar { width: 280px; background: #fff; padding: 30px; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); height: fit-content; border: 1px solid #dfcec0; }
        .categories-sidebar h3 { font-size: 28px; margin-bottom: 20px; color: #56040e; padding-bottom: 10px; border-bottom: 1px solid #dfcec0; }
        .categories-sidebar ul { list-style: none; }
        .categories-sidebar ul li { margin-bottom: 10px; }
        .categories-sidebar ul li a { display: block; padding: 12px 15px; color: #56040e; text-decoration: none; font-weight: 500; font-size: 18px; border-radius: 4px; transition: 0.3s; }
        .categories-sidebar ul li a:hover, .categories-sidebar ul li a.active { background: #dfcec0; font-weight: bold; padding-left: 20px; }

        /* Products Grid Layout Section */
        .products-section { flex-grow: 1; }
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px; }
        
        /* تصميم كرت المنتج الاحترافي المخصص بناءً على طلبكِ */
        .product-card { 
            background: #fff; 
            border: 1px solid #dfcec0; 
            border-radius: 4px; 
            overflow: hidden; 
            display: flex; 
            flex-direction: column; 
            transition: transform 0.3s, box-shadow 0.3s; 
            padding: 15px;
        }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(86, 4, 14, 0.12); }
        
        /* مكان مخصص لعرض صورة المنتج */
        .product-card .product-image-holder { 
            width: 100%; 
            height: 300px; 
            overflow: hidden; 
            background: #fdfdfd;
            border-bottom: 1px solid rgba(223, 206, 192, 0.5);
            margin-bottom: 15px;
        }
        .product-card .product-image-holder img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            transition: transform 0.5s;
        }
        .product-card:hover .product-image-holder img { transform: scale(1.05); }
        
        .product-info { display: flex; flex-direction: column; text-align: center; gap: 8px; flex-grow: 1; }
        
        /* مكان اسم المنتج */
        .product-info .product-name { 
            font-size: 22px; 
            color: #56040e; 
            font-weight: 600; 
            letter-spacing: 0.5px; 
        }
        
        /* مكان سعر المنتج وتحت الاسم مباشرة */
        .product-info .product-price { 
            font-size: 20px; 
            font-weight: bold; 
            color: #333; 
        }
        
        /* أزرار التحكم والإضافة إلى السلة */
        .btn-action-container { display: flex; flex-direction: column; gap: 10px; margin-top: auto; padding-top: 10px; }
        
        .btn-view { text-align: center; background: #dfcec0; color: #56040e; padding: 10px; text-decoration: none; font-size: 16px; font-weight: bold; transition: 0.3s; border-radius: 2px; border: none; }
        .btn-view:hover { background: #cbb9ab; }
        
        /* زر إضافة إلى سلة التسوق أسفل السعر والاسم والصورة */
        .btn-add-cart-submit { width: 100%; background: #56040e; color: #fff; border: none; padding: 12px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.3s; border-radius: 2px; }
        .btn-add-cart-submit:hover { background: #3d030a; }
        
        .no-products { text-align: center; color: #777; padding: 60px; font-size: 18px; width: 100%; background: #fff; border: 1px solid #dfcec0; }

        /* 3. Global Footer Layout */
        footer { background: #56040e; color: #dfcec0; padding: 50px 60px 25px 60px; margin-top: auto; }
        .footer-grid { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 40px; }
        .footer-col h3 { font-size: 28px; margin-bottom: 20px; color: #fff; }
        .footer-col p { font-size: 16px; line-height: 1.8; color: #f5f5f5; }
        .footer-col ul { list-style: none; }
        .footer-col ul li { margin-bottom: 10px; }
        .footer-col ul li a { color: #dfcec0; text-decoration: none; font-size: 16px; transition: color 0.3s; }
        .footer-col ul li a:hover { color: #fff; padding-left: 5px; }
        .footer-bottom { max-width: 1200px; margin: 40px auto 0 auto; padding-top: 20px; border-top: 1px solid rgba(223,206,192,0.2); text-align: center; font-size: 15px; color: #f5f5f5; }

        /* Responsive Breakpoints */
        @media (max-width: 992px) {
            nav { padding: 15px 30px; grid-template-columns: 1fr; gap: 15px; text-align: center; }
            nav .nav-links-right { justify-content: center; }
            nav .logo-center { order: -1; }
            nav .nav-links-left { justify-content: center; }
            .container { flex-direction: column; }
            .categories-sidebar { width: 100%; }
        }
    </style>
</head>
<body>

    <!-- 1. Fixed / Sticky Top Header Layout -->
    <nav>
        <ul class="nav-links-right">
            <li><a href="index.php">Home</a></li>
            <li><a href="cart.php">My Cart</a></li>
            <li><a href="contact.php">Contact Us</a></li>
        </ul>
        
        <!-- هنا اللوجو بالمنتصف تماماً والصورة بجانبه في السطر التالي مباشرة -->
        <a href="index.php" class="logo-center">
            <!-- <img src="logo.jpg" alt="Jessbella Logo"> -->
            <span class="logo-text">Jessbella</span>
        </a>
        
        <ul class="nav-links-left">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="user-badge">Hi, <?= sanitize($_SESSION['full_name']) ?></span>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li><a href="admin_dashboard.php">Dashboard</a></li>
                <?php endif; ?>
                <li><a href="logout.php" class="btn-logout">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- 2. Dark Overlay Luxury Banner -->
    <div class="hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Jessbella Collections</h1>
            <p>Empowering your style with high-end, elegant, and timeless statement handbags.</p>
        </div>
    </div>

    <!-- Interactive Search Integration -->
    <div class="search-area">
        <form action="index.php" method="GET" class="search-form">
            <?php if($category_filter > 0): ?>
                <input type="hidden" name="cat" value="<?= $category_filter ?>">
            <?php endif; ?>
            <input type="text" name="search" class="search-input" placeholder="Search our signature styles..." value="<?= sanitize($search_query) ?>">
            <button type="submit" class="btn-search">Search</button>
        </form>
    </div>

    <!-- Main Dynamic Section Layout -->
    <div class="container">
        <!-- Sidebar Filter System matching categories -->
        <div class="categories-sidebar">
            <h3>Collections</h3>
            <ul>
                <li><a href="index.php<?= !empty($search_query) ? '?search='.urlencode($search_query) : '' ?>" class="<?= $category_filter == 0 ? 'active' : '' ?>">All Products</a></li>
                <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="index.php?cat=<?= $cat['category_id'] ?><?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?>" class="<?= $category_filter == $cat['category_id'] ? 'active' : '' ?>">
                            <?= sanitize($cat['name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Products Showcase Grid Area -->
        <div class="products-section">
            <div class="products-grid">
                <?php if (count($products) == 0): ?>
                    <div class="no-products">No gorgeous handbags found in this specific selection.</div>
                <?php else: ?>
                    <?php foreach ($products as $prod): ?>
                        <div class="product-card">
                            
                            <!-- أولاً: مكان مخصص لعرض صورة المنتج -->
                            <div class="product-image-holder">
                                <img src="<?= sanitize($prod['image_url']) ?>" alt="Jessbella Luxury Bag">
                            </div>
                            
                            <div class="product-info">
                                <!-- ثانياً: اسم المنتج -->
                                <div class="product-name"><?= sanitize($prod['name']) ?></div>
                                
                                <!-- ثالثاً: سعر المنتج -->
                                <div class="product-price">$<?= number_format($prod['price'], 2) ?></div>
                                
                                <div class="btn-action-container">
                                    <a href="product_details.php?id=<?= $prod['product_id'] ?>" class="btn-view">Details</a>
                                    
                                    <!-- رابعاً: زر إضافة إلى سلة التسوق -->
                                    <form action="cart.php" method="POST" style="width: 100%;">
                                        <input type="hidden" name="product_id" value="<?= $prod['product_id'] ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" name="add_to_cart" class="btn-add-cart-submit">Add To Cart 🛒</button>
                                    </form>
                                </div>
                            </div>
                            
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 3. Global Luxury Footer Panel -->
    <footer>
        <div class="footer-grid">
            <div class="footer-col">
                <h3>Jessbella</h3>
                <p>An elite destination for high-fashion handcrafted women's luxury leather handbags, statement accessories, and seasonal masterwork edits.</p>
            </div>
            <div class="footer-col">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home Directory</a></li>
                    <li><a href="cart.php">Shopping Bag</a></li>
                    <li><a href="contact.php">Help & Support</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Customer Care</h3>
                <p>Email: boutique@jessbella.com<br>Phone: +1 (800) 555-BAGS<br>Hours: Mon - Fri | 9 AM - 6 PM</p>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; 2026 Jessbella Luxury Brands Global Ltd. All rights reserved.
        </div>
    </footer>

</body>
</html>