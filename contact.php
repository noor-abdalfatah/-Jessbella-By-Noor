<?php
session_start();
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // محاكاة إرسال رسالة تواصل بنجاح للتوافق مع متطلبات العرض الأكاديمي
    $success_msg = "Thank you for reaching out to Jessbella. Our support curators will respond shortly.";
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Jessbella</title>
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

        .contact-container { max-width: 600px; width: 100%; margin: 50px auto; background: #fff; border: 1px solid #dfcec0; padding: 40px; box-shadow: 0 4px 15px rgba(86, 4, 14, 0.02); flex-grow: 1; }
        .contact-container h2 { font-size: 38px; color: #56040e; text-align: center; margin-bottom: 15px; }
        .contact-container p { text-align: center; color: #666; font-size: 18px; margin-bottom: 30px; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 16px; font-weight: bold; color: #56040e; margin-bottom: 8px; }
        .form-group input, .form-group textarea { width: 100%; padding: 12px 15px; border: 1px solid #dfcec0; font-size: 16px; color: #333; outline: none; transition: border-color 0.3s; }
        .form-group input:focus, .form-group textarea:focus { border-color: #56040e; }
        
        .btn-submit { width: 100%; background: #56040e; color: #fff; border: none; padding: 14px; font-size: 18px; font-weight: bold; cursor: pointer; transition: 0.3s; border-radius: 2px; }
        .btn-submit:hover { background: #320207; }
        
        .success-box { background: #dfcec0; color: #56040e; padding: 15px; border: 1px solid #56040e; text-align: center; margin-bottom: 25px; font-size: 18px; font-weight: bold; }

        /* Global Footer Layout */
        footer { background: #56040e; color: #dfcec0; padding: 50px 60px 25px 60px; margin-top: auto; }
        .footer-grid { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 40px; }
        .footer-col h3 { font-size: 28px; margin-bottom: 20px; color: #fff; }
        .footer-col p { font-size: 16px; line-height: 1.8; color: #f5f5f5; }
        .footer-bottom { max-width: 1200px; margin: 40px auto 0 auto; padding-top: 20px; border-top: 1px solid rgba(223,206,192,0.2); text-align: center; font-size: 15px; color: #f5f5f5; }

        @media (max-width: 992px) {
            nav { padding: 15px 30px; grid-template-columns: 1fr; gap: 15px; text-align: center; }
            nav .nav-links-right, nav .nav-links-left { justify-content: center; }
            nav .logo-center { order: -1; }
        }
    </style>
</head>
<body>

    <nav>
        <ul class="nav-links-right">
            <li><a href="index.php">Home</a></li>
            <li><a href="cart.php">My Cart</a></li>
            <li><a href="contact.php">Contact Us</a></li>
        </ul>
        <a href="index.php" class="logo-center">
            <!-- <img src="logo.jpg" alt="Jessbella Logo"> -->
            <span class="logo-text">Jessbella</span>
        </a>
        <ul class="nav-links-left">
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="logout.php" style="color: #b33939;">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="contact-container">
        <h2>Get In Touch</h2>
        <p>Have any questions regarding our exclusive signature collections? Leave us a message.</p>
        
        <?php if (!empty($success_msg)): ?>
            <div class="success-box"><?= $success_msg ?></div>
        <?php endif; ?>

        <form action="contact.php" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" placeholder="Enter your name" required>
            </div>
            
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" placeholder="Enter your email" required>
            </div>
            
            <div class="form-group">
                <label>Your Message</label>
                <textarea rows="5" placeholder="How can our boutique help you?" required></textarea>
            </div>
            
            <button type="submit" class="btn-submit">Send Message</button>
        </form>
    </div>

    <footer>
        <div class="footer-grid">
            <div class="footer-col">
                <h3>Jessbella</h3>
                <p>An elite destination for high-fashion handcrafted women's luxury leather handbags, statement accessories, and seasonal masterwork edits.</p>
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