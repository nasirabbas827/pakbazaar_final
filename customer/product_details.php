<?php
include('../config.php');
session_start();

// Access control
if (!isset($_SESSION["logged_in"]) || $_SESSION["role"] !== "customer") {
    header("Location: ../login.php");
    exit;
}

$customer_id = $_SESSION["user_id"];
$product_id  = (int)($_GET['id'] ?? 0);

if ($product_id <= 0) {
    header("Location: dashboard.php");
    exit;
}

// Fetch product details
$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name,
           AVG(r.rating) as avg_rating,
           COUNT(r.id) as review_count
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN reviews r ON p.id = r.product_id
    WHERE p.id = ?  
    GROUP BY p.id
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    die("<div class='alert alert-danger text-center m-5'>Product not found or unavailable.</div>");
}

// Check if already in cart
$cart_check = $conn->prepare("SELECT id FROM cart WHERE customer_id = ? AND product_id = ?");
$cart_check->bind_param("ii", $customer_id, $product_id);
$cart_check->execute();
$in_cart = $cart_check->get_result()->num_rows > 0;
$cart_check->close();

// Check if already reviewed
$review_check = $conn->prepare("SELECT id FROM reviews WHERE customer_id = ? AND product_id = ?");
$review_check->bind_param("ii", $customer_id, $product_id);
$review_check->execute();
$already_reviewed = $review_check->get_result()->num_rows > 0;
$review_check->close();

// Fetch all reviews for this product
$reviews = $conn->query("
    SELECT r.*, u.name as customer_name 
    FROM reviews r 
    JOIN users u ON r.customer_id = u.id 
    WHERE r.product_id = $product_id 
    ORDER BY r.created_at DESC 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - PakBazar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        .product-hero-img { max-height: 450px; object-fit: contain; background: #fff; }
        .review-card { border-left: 4px solid #0d6efd; }
        .star-gold { color: #ffc107; }
    </style>
</head>
<body class="bg-light">

<?php include('customer_sidebar.php'); ?>

<div class="container py-4" style="max-width: 1100px;">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
            <li class="breadcrumb-item"><a href="dashboard.php#<?= htmlspecialchars($product['category_name']) ?>"><?= htmlspecialchars($product['category_name']) ?></a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">

        <!-- Product Image & Actions -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <img src="../uploads/<?= htmlspecialchars($product['image'] ?? 'default-product.jpg') ?>" 
                     class="product-hero-img w-100" alt="<?= htmlspecialchars($product['name']) ?>">
            </div>

            <div class="mt-4 d-grid gap-2">
                <?php if ($in_cart): ?>
                    <button class="btn btn-outline-danger btn-lg" onclick="updateCart(<?= $product_id ?>, 'remove')">
                        <i class="fas fa-trash-alt me-2"></i> Remove from Cart
                    </button>
                <?php else: ?>
                    <button class="btn btn-primary btn-lg" onclick="updateCart(<?= $product_id ?>, 'add')">
                        <i class="fas fa-cart-plus me-2"></i> Add to Cart
                    </button>
                <?php endif; ?>

                <a href="wishlist.php" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-shopping-cart me-2"></i> View Cart
                </a>
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-lg-5">
                    <h2 class="fw-bold mb-3"><?= htmlspecialchars($product['name']) ?></h2>
                    
                    <div class="d-flex align-items-center mb-3">
                        <h4 class="text-success mb-0 me-3">Rs <?= number_format($product['price'], 0) ?></h4>
                        <?php if ($product['avg_rating']): ?>
                            <div class="ms-3">
                                <span class="star-gold fs-4">
                                    <?= str_repeat("★", round($product['avg_rating'])) ?>
                                    <?= str_repeat("☆", 5 - round($product['avg_rating'])) ?>
                                </span>
                                <span class="text-muted ms-2">(<?= $product['review_count'] ?> reviews)</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <p class="text-muted mb-4">
                        <strong>Category:</strong> <?= htmlspecialchars($product['category_name']) ?><br>
                        <strong>Stock:</strong> 
                        <?php if ($product['stock'] > 0): ?>
                            <span class="text-success"><?= $product['stock'] ?> available</span>
                        <?php else: ?>
                            <span class="text-danger">Out of stock</span>
                        <?php endif; ?>
                    </p>

                    <h5 class="fw-bold mb-3">Description</h5>
                    <p class="lead"><?= nl2br(htmlspecialchars($product['description'] ?? 'No description available.')) ?></p>

                    <?php if (!empty($product['short_description'])): ?>
                        <p class="text-muted fst-italic mt-3">
                            <?= htmlspecialchars($product['short_description']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Reviews Section -->
    <div class="card border-0 shadow-sm rounded-4 mt-5">
        <div class="card-header bg-white border-0">
            <h4 class="mb-0">Customer Reviews (<?= count($reviews) ?>)</h4>
        </div>
        <div class="card-body">
            <?php if (empty($reviews)): ?>
                <p class="text-center text-muted py-4">No reviews yet. Be the first to review!</p>
            <?php else: ?>
                <?php foreach ($reviews as $r): ?>
                    <div class="review-card p-3 mb-3 bg-light rounded">
                        <div class="d-flex justify-content-between">
                            <strong><?= htmlspecialchars($r['customer_name']) ?></strong>
                            <span class="star-gold">
                                <?= str_repeat("★", $r['rating']) . str_repeat("☆", 5 - $r['rating']) ?>
                            </span>
                        </div>
                        <small class="text-muted"><?= date('M d, Y', strtotime($r['created_at'])) ?></small>
                        <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!$already_reviewed && $product['stock'] > 0): ?>
                <div class="text-center mt-4">
                    <a href="add_review.php?product_id=<?= $product_id ?>" class="btn btn-outline-success">
                        <i class="fas fa-star me-2"></i> Write a Review
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Same cart action function as in dashboard
function updateCart(productId, action) {
    const fd = new FormData();
    fd.append('product_id', productId);
    fd.append('action', action);

    fetch('cart_action.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        alert(data.message || (data.success ? 'Success!' : 'Error'));
        if (data.success) location.reload();
    })
    .catch(() => alert('Request failed'));
}
</script>
</body>
</html>