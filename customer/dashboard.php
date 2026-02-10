<?php
include('../config.php');
session_start();

// 1. Access Control
if (!isset($_SESSION["logged_in"]) || $_SESSION["role"] !== "customer") {
    header("Location: ../login.php");
    exit;
}

$customer_id = $_SESSION["user_id"];

// 2. Fetch Customer Info
$stmt = $conn->prepare("SELECT name, city FROM users WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 3. Stats Summary
$order_count = $conn->query("SELECT COUNT(*) as total FROM orders WHERE customer_id = $customer_id")->fetch_assoc()['total'];
$cart_count = $conn->query("SELECT COUNT(*) as total FROM cart WHERE customer_id = $customer_id")->fetch_assoc()['total'];

// 4. Products in Cart
$in_cart = [];
$cart_res = $conn->query("SELECT product_id FROM cart WHERE customer_id = $customer_id");
while($r = $cart_res->fetch_assoc()) { $in_cart[] = $r['product_id']; }

// 5. Fetch Data for Slider (Latest 5 Products)
$slider_products = $conn->query("SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// 6. Fetch All Categories & All Products
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$products_query = "
    SELECT p.*, c.name as cat_name,
           (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id) as review_count,
           (SELECT AVG(rating) FROM reviews r WHERE r.product_id = p.id) as avg_rating
    FROM products p
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
";
$products = $conn->query($products_query)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - PakBazar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root { --pb-indigo: #4f46e5; --pb-bg: #f8fafc; }
        body { background: var(--pb-bg); font-family: 'Inter', sans-serif; color: #1e293b; }
        
        /* Slider Styling */
        .slider-section { border-radius: 25px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 40px; }
        .carousel-item { height: 350px; background: #0f172a; }
        .slider-content { height: 100%; display: flex; align-items: center; padding: 0 10%; color: white; background: linear-gradient(90deg, rgba(15,23,42,1) 0%, rgba(79,70,229,0.8) 100%); }
        .slider-img { height: 280px; width: auto; object-fit: contain; border-radius: 15px; }

        /* Stats & Cards */
        .stat-card { border: none; border-radius: 15px; background: white; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); transition: 0.3s; }
        .product-card { border: none; border-radius: 18px; background: white; transition: 0.3s; border: 1px solid #edf2f7; overflow: hidden; height: 100%; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08); }
        .product-img { height: 180px; width: 100%; object-fit: cover; }
        .badge-cat { font-size: 0.7rem; background: #eef2ff; color: var(--pb-indigo); padding: 4px 10px; border-radius: 50px; font-weight: 700; }
        .price-text { font-size: 1.2rem; font-weight: 800; color: var(--pb-indigo); }
    </style>
</head>
<body>

<?php include('customer_sidebar.php'); ?>

<div class="container py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-800 mb-0">Welcome, <?= htmlspecialchars($user_data['name']) ?>!</h2>
            <p class="text-muted small"><i class="fas fa-map-marker-alt me-1"></i> Shipping to <?= htmlspecialchars($user_data['city']) ?></p>
        </div>
        <div class="d-flex gap-2">
            <div class="stat-card py-2 px-3 d-flex align-items-center">
                <i class="fas fa-shopping-bag text-primary me-2"></i>
                <span class="fw-bold"><?= $order_count ?> Orders</span>
            </div>
        </div>
    </div>

    <div id="latestProductsSlider" class="carousel slide slider-section" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php foreach($slider_products as $key => $sp): ?>
            <div class="carousel-item <?= $key == 0 ? 'active' : '' ?>">
                <div class="slider-content">
                    <div class="row w-100 align-items-center">
                        <div class="col-md-7">
                            <span class="badge bg-warning text-dark mb-2">NEW ARRIVAL</span>
                            <h1 class="fw-800 display-5"><?= htmlspecialchars($sp['name']) ?></h1>
                            <p class="lead opacity-75"><?= substr(htmlspecialchars($sp['description']), 0, 100) ?>...</p>
                            <h3 class="fw-bold mb-4">Rs <?= number_format($sp['price']) ?></h3>
                            <a href="product_details.php?id=<?= $sp['id'] ?>" class="btn btn-light btn-lg rounded-pill px-5 fw-bold">Shop Now</a>
                        </div>
                        <div class="col-md-5 text-center d-none d-md-block">
                            <img src="../uploads/<?= htmlspecialchars($sp['image'] ?? 'default.jpg') ?>" class="slider-img shadow-lg">
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#latestProductsSlider" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#latestProductsSlider" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>

    <div class="bg-white p-3 rounded-4 shadow-sm mb-5">
        <div class="row g-3">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="pSearch" class="form-control border-0 shadow-none" placeholder="Search for products..." onkeyup="filterProducts()">
                </div>
            </div>
            <div class="col-md-4">
                <select id="cFilter" class="form-select border-0 shadow-none fw-600" onchange="filterProducts()">
                    <option value="all">All Categories</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <h4 class="fw-800 mb-4">Explore Our Collection</h4>
    <div class="row g-4" id="productGrid">
        <?php foreach($products as $p): 
            $in_my_cart = in_array($p['id'], $in_cart);
            $rating = $p['avg_rating'] ? round($p['avg_rating'], 1) : 0;
        ?>
        <div class="col-6 col-md-4 col-lg-3 product-item" data-name="<?= strtolower(htmlspecialchars($p['name'])) ?>" data-cat="<?= htmlspecialchars($p['cat_name']) ?>">
            <div class="product-card d-flex flex-column">
                <img src="../uploads/<?= htmlspecialchars($p['image'] ?? 'default.jpg') ?>" class="product-img">
                <div class="p-3 flex-grow-1">
                    <span class="badge-cat"><?= htmlspecialchars($p['cat_name']) ?></span>
                    <h6 class="fw-bold mt-2 text-truncate"><?= htmlspecialchars($p['name']) ?></h6>
                    <div class="text-warning small mb-2">
                        <?= str_repeat("★", floor($rating)) . str_repeat("☆", 5 - floor($rating)) ?>
                        <span class="text-muted ms-1">(<?= $p['review_count'] ?>)</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="price-text">Rs <?= number_format($p['price']) ?></span>
                    </div>
                </div>
                <div class="p-3 pt-0">
                    <div class="d-grid gap-2">
                        <?php if($in_my_cart): ?>
                            <button class="btn btn-outline-danger btn-sm rounded-pill" onclick="manageCart(<?= $p['id'] ?>, 'remove')">Remove</button>
                        <?php else: ?>
                            <button class="btn btn-primary btn-sm rounded-pill" onclick="manageCart(<?= $p['id'] ?>, 'add')" <?= $p['stock'] <= 0 ? 'disabled' : '' ?>>Add to Cart</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function filterProducts() {
    const search = document.getElementById('pSearch').value.toLowerCase();
    const cat = document.getElementById('cFilter').value;
    document.querySelectorAll('.product-item').forEach(item => {
        const name = item.getAttribute('data-name');
        const itemCat = item.getAttribute('data-cat');
        item.style.display = (name.includes(search) && (cat === 'all' || itemCat === cat)) ? '' : 'none';
    });
}

function manageCart(id, action) {
    const fd = new FormData();
    fd.append('product_id', id);
    fd.append('action', action);
    fetch('cart_action.php', { method: 'POST', body: fd })
    .then(res => res.json()).then(data => {
        if(data.success) location.reload();
        else alert(data.message);
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>