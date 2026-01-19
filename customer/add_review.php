<?php
include('../config.php');
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login.php");
    exit;
}

$customer_id = $_SESSION['user_id'];
$product_id  = (int)($_GET['product_id'] ?? 0);

// 1. Fetch Product Details
$p_stmt = $conn->prepare("SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$p_stmt->bind_param("i", $product_id);
$p_stmt->execute();
$product = $p_stmt->get_result()->fetch_assoc();

if (!$product) {
    die("Product not found.");
}

// 2. Handle New Review Submission
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $rating  = (int)$_POST['rating'];
    $comment = trim($_POST['comment'] ?? '');

    if ($rating >= 1 && $rating <= 5) {
        $stmt = $conn->prepare("INSERT INTO reviews (product_id, customer_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $product_id, $customer_id, $rating, $comment);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Review posted successfully!</div>";
        }
    }
}

// 3. Fetch All Reviews for this product
$rev_stmt = $conn->prepare("
    SELECT r.*, u.name as user_name 
    FROM reviews r 
    JOIN users u ON r.customer_id = u.id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
");
$rev_stmt->bind_param("i", $product_id);
$rev_stmt->execute();
$all_reviews = $rev_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reviews - <?= htmlspecialchars($product['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        .star-rating { color: #ffc107; }
        .review-card { border-left: 4px solid #0d6efd; background: #fff; }
        .product-img-header { width: 100px; height: 100px; object-fit: cover; border-radius: 10px; }
    </style>
</head>
<body class="bg-light">
<?php include('customer_sidebar.php'); ?>


<div class="container py-5">
 
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <img src="../uploads/<?= htmlspecialchars($product['image'] ?? 'default.jpg') ?>" class="product-img-header me-3">
                        <div>
                            <span class="badge bg-primary mb-1"><?= htmlspecialchars($product['cat_name']) ?></span>
                            <h4 class="fw-bold mb-0"><?= htmlspecialchars($product['name']) ?></h4>
                            <p class="text-success fw-bold mb-0">Rs <?= number_format($product['price'], 0) ?></p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h5 class="fw-bold mb-3">Write a Review</h5>
                    <?= $message ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Your Rating</label>
                            <select name="rating" class="form-select border-primary" required>
                                <option value="5">5 Stars - Excellent</option>
                                <option value="4">4 Stars - Very Good</option>
                                <option value="3">3 Stars - Good</option>
                                <option value="2">2 Stars - Fair</option>
                                <option value="1">1 Star - Poor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Your Experience</label>
                            <textarea name="comment" class="form-control" rows="4" placeholder="What did you like or dislike?" required></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="btn btn-primary w-100 py-2 rounded-pill fw-bold">
                            Post Review
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <h4 class="fw-bold mb-4">Customer Reviews (<?= $all_reviews->num_rows ?>)</h4>
            
            <?php if ($all_reviews->num_rows > 0): ?>
                <?php while($row = $all_reviews->fetch_assoc()): ?>
                    <div class="card shadow-sm border-0 mb-3 review-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0"><?= htmlspecialchars($row['user_name']) ?></h6>
                                <small class="text-muted"><?= date('M d, Y', strtotime($row['created_at'])) ?></small>
                            </div>
                            <div class="star-rating mb-2">
                                <?= str_repeat("★", $row['rating']) . str_repeat("☆", 5 - $row['rating']) ?>
                            </div>
                            <p class="card-text text-dark"><?= nl2br(htmlspecialchars($row['comment'])) ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                    <i class="far fa-comment-dots fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No reviews yet. Be the first to review!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>