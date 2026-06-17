<?php
// 1. الاتصال بقاعدة البيانات
$conn = new mysqli("localhost", "root", "", "ecommerce_db"); // تأكدي من اسم قاعدة بياناتكِ هنا

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. جلب رقم المنتج (ID) من الرابط (URL)
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 3. الاستعلام عن بيانات هذا المنتج المحدد
$sql = "SELECT * FROM products WHERE product_id = $product_id";
$result = $conn->query($sql);

// إذا لم يتم العثور على المنتج، نعود للصفحة الرئيسية
if ($result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - Jessbella</title>
    <link href="css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lobster&display=swap');
        body {
           font-family: 'Lobster', cursive !important;
            background-color: #ffffff;
        }
    </style>
</head>
<body class="flex flex-col min-height-screen justify-between">

    <header class="bg-[#4A0E17] text-white py-4 px-8 flex justify-between items-center shadow-md">
        <div class="flex gap-6 text-sm italic font-medium">
            <a href="index.php" class="hover:underline">Home</a>
            <a href="cart.php" class="hover:underline">My Cart</a>
            <a href="#" class="hover:underline">Contact Us</a>
        </div>
        <div class="text-3xl font-bold tracking-widest italic">
            <a href="index.php">Jessbella</a>
        </div>
        <div class="flex gap-4 text-sm font-medium">
            <a href="#" class="hover:underline">Login</a>
            <a href="#" class="hover:underline">Register</a>
        </div>
    </header>

    <main class="container mx-auto px-6 py-12 flex-grow">
        <div class="mb-8">
            <a href="index.php" class="text-[#4A0E17] hover:underline flex items-center gap-2 font-medium">
                ← Back to Collections
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div class="bg-[#f9f6f0] p-6 rounded-xl shadow-sm flex justify-center items-center overflow-hidden border border-stone-100">
               
                <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" class="max-h-[500px] object-contain hover:scale-105 transition-transform duration-300">
            </div>

            <div class="flex flex-col justify-center">
                <h1 class="text-4xl font-bold text-[#4A0E17] mb-4 tracking-wide"><?php echo $product['name']; ?></h1>
                
                <div class="text-2xl font-semibold text-stone-700 mb-6">
                    $<?php echo number_format($product['price'], 2); ?>
                </div>

                <div class="border-t border-b border-stone-200 py-6 mb-8">
                    <h3 class="text-sm uppercase tracking-widest text-stone-400 font-bold mb-3">Description</h3>
                    <p class="text-stone-600 leading-relaxed text-lg italic">
                        <?php echo $product['description']; ?>
                    </p>
                </div>

                <form action="cart.php" method="POST" class="flex flex-col gap-4">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    
                    <div class="flex items-center gap-4">
                        <label for="quantity" class="text-stone-500 font-medium">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" class="w-20 p-2 border border-stone-300 rounded-md text-center focus:outline-none focus:border-[#4A0E17]">
                    </div>

                    <button type="submit" name="add_to_cart" class="mt-4 bg-[#4A0E17] text-white text-center py-4 px-8 rounded-lg shadow-md hover:bg-[#320a10] transition-colors font-semibold tracking-wider uppercase text-sm">
                        Add To Cart 🛒
                    </button>
                </form>
            </div>
        </div>
    </main>

    <footer class="bg-[#4A0E17] text-white py-12 px-8 mt-12 border-t border-[#320a10]">
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-3 gap-8 text-center md:text-left">
            <div>
                <h3 class="text-xl font-bold italic mb-4">Jessbella</h3>
                <p class="text-xs text-stone-300 leading-relaxed italic">An elite destination for high-fashion handcrafted women's luxury leather handbags, statement accessories, and seasonal masterwork edits.</p>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                <ul class="text-xs space-y-2 text-stone-300">
                    <li><a href="index.php" class="hover:underline">Home Directory</a></li>
                    <li><a href="cart.php" class="hover:underline">Shopping Bag</a></li>
                    <li><a href="#" class="hover:underline">Help & Support</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-4">Customer Care</h3>
                <ul class="text-xs space-y-2 text-stone-300">
                    <li>Email: boutiques@jessbella.com</li>
                    <li>Phone: +1 (800) 555-BAGS</li>
                    <li>Hours: Mon - Fri | 9 AM - 6 PM</li>
                </ul>
            </div>
        </div>
        <div class="text-center text-[10px] text-stone-400 mt-12 border-t border-[#320a10] pt-4">
            &copy; 2026 Jessbella Luxury Brands Global Ltd. All rights reserved.
        </div>
    </footer>

</body>
</html>