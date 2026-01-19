<?php
include('../config.php');
session_start();

// 1. Access Control
if (!isset($_SESSION["logged_in"]) || $_SESSION["role"] !== "customer") {
    header("Location: ../login.php");
    exit;
}

$customer_id = $_SESSION["user_id"];

// 2. Fetch Customer basic info
$stmt = $conn->prepare("SELECT name, city FROM users WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 3. Get IDs of products already in cart (for button toggle)
$cart_stmt = $conn->prepare("SELECT product_id FROM cart WHERE customer_id = ?");
$cart_stmt->bind_param("i", $customer_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();
$in_cart = [];
while ($row = $cart_result->fetch_assoc()) {
    $in_cart[] = $row['product_id'];
}
$cart_stmt->close();

// 4. Fetch Categories & Products
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
    <style>
        body { background: #f5f7fa; font-family: 'Segoe UI', sans-serif; }
        .product-card { border: none; border-radius: 16px; overflow: hidden; transition: all 0.25s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.06); }
        .product-card:hover { transform: translateY(-6px); box-shadow: 0 12px 25px rgba(0,0,0,0.12); }
        .product-img { height: 200px; object-fit: cover; }
        .action-btn { font-size: 0.85rem; padding: 0.5rem 1rem; border-radius: 50px; }
        .rating-stars { color: #ffc107; }
        .wishlist-btn { position: absolute; top: 12px; right: 12px; background: rgba(255,255,255,0.9); border-radius: 50%; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; z-index: 10; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<?php include('customer_sidebar.php'); ?>

<div class="container py-4" style="max-width:1400px;">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold text-dark mb-1">Welcome, <?= htmlspecialchars($user_data['name'] ?? 'Customer') ?>!</h2>
            <p class="text-muted mb-0"><i class="fas fa-map-marker-alt me-2"></i> Shipping to: <?= htmlspecialchars($user_data['city'] ?? 'Your city') ?></p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="wishlist.php" class="btn btn-outline-danger rounded-pill px-4"><i class="fas fa-heart me-2"></i> Wishlist</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <input type="text" id="pSearch" class="form-control form-control-lg" placeholder="Search products..." onkeyup="filterProducts()">
                </div>
                <div class="col-md-4">
                    <select id="cFilter" class="form-select form-select-lg" onchange="filterProducts()">
                        <option value="all">All Categories</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <a href="cart.php" class="btn btn-primary w-100 h-100 d-flex align-items-center justify-content-center">
                        <i class="fas fa-shopping-cart me-2"></i> View Cart
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4" id="productGrid">
        <?php if(empty($products)): ?>
            <div class="col-12 text-center py-5"><h4 class="text-muted">No products available.</h4></div>
        <?php else: ?>
            <?php foreach($products as $p): 
                $is_in_cart = in_array($p['id'], $in_cart);
                $avg = $p['avg_rating'] ? round($p['avg_rating'], 1) : null;
            ?>
            <div class="col-6 col-md-4 col-lg-3 product-item" data-name="<?= strtolower(htmlspecialchars($p['name'])) ?>" data-cat="<?= htmlspecialchars($p['cat_name']) ?>">
                <div class="card h-100 product-card position-relative">
                    <button class="wishlist-btn" onclick="toggleWishlist(<?= $p['id'] ?>)"><i class="far fa-heart"></i></button>
                    <img src="../uploads/<?= htmlspecialchars($p['image'] ?? 'default-product.jpg') ?>" class="product-img card-img-top">
                    
                    <div class="card-body d-flex flex-column">
                        <small class="text-primary fw-bold"><?= htmlspecialchars($p['cat_name']) ?></small>
                        <h6 class="card-title mt-2 mb-2 fw-bold"><?= htmlspecialchars($p['name']) ?></h6>
                        
                        
                        <?php if($avg): ?>
                        <div class="mb-2 rating-stars">
                            <?= str_repeat("★", floor($avg)) . str_repeat("☆", 5 - floor($avg)) ?>
                            <small class="text-muted ms-1">(<?= $p['review_count'] ?>)</small>
                        </div>
                        <?php endif; ?>

                                            <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="h5 fw-bold text-primary mb-0">Rs <?= number_format($p['price']) ?></span>
                        <small class="text-<?= $p['stock'] > 0 ? 'success' : 'danger' ?> fw-bold">
                            <?= $p['stock'] > 0 ? $p['stock'].' in stock' : 'Out of Stock' ?>
                        </small>
                    </div>
                    </div>

                    <div class="card-footer bg-white border-0 pt-0 pb-4 px-3">
                        <div class="d-grid gap-2">
                            <?php if($is_in_cart): ?>
                                <button class="btn btn-outline-danger action-btn" onclick="updateCart(<?= $p['id'] ?>, 'remove')">
                                    <i class="fas fa-trash-alt me-1"></i> Remove from Cart
                                </button>
                            <?php else: ?>
                                <button class="btn btn-primary action-btn" onclick="updateCart(<?= $p['id'] ?>, 'add')" <?= $p['stock'] <= 0 ? 'disabled' : '' ?>>
                                    <i class="fas fa-cart-plus me-1"></i> Add to Cart
                                </button>
                            <?php endif; ?>

                            <a href="product_details.php?id=<?= $p['id'] ?>" class="btn btn-outline-secondary action-btn">
                                <i class="fas fa-eye me-1"></i> View Details
                            </a>

                            <?php if($p['stock'] > 0): ?>
                                <a href="add_review.php?product_id=<?= $p['id'] ?>" class="btn btn-outline-success action-btn">
                                    <i class="fas fa-star me-1"></i> Add Review
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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

function updateCart(productId, action) {
    const fd = new FormData();
    fd.append('product_id', productId);
    fd.append('action', action);

    fetch('cart_action.php', { method: 'POST', body: fd })
    .then(response => response.json())
    .then(data => {
        if (data.success) { location.reload(); } 
        else { alert('Error: ' + data.message); }
    })
    .catch(err => alert('Request failed. Ensure cart_action.php exists.'));
}

function toggleWishlist(id) {
    const fd = new FormData();
    fd.append('product_id', id);
    fetch('wishlist_action.php', { method: 'POST', body: fd })
    .then(res => res.json())
    .then(data => { alert(data.message); location.reload(); })
    .catch(err => alert('Wishlist action failed.'));
}
</script>
</body>
</html>

 



