<?php
include('../config.php');
session_start();

if (!isset($_SESSION["logged_in"]) || $_SESSION["role"] !== "customer") {
    header("Location: ../login.php");
    exit;
}

$customer_id = $_SESSION["user_id"];

// Fetch cart items with product details and quantity
$cart_items = $conn->query("
    SELECT c.id as cart_id, c.product_id, c.quantity, p.name, p.price, p.image, p.stock
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.customer_id = $customer_id
    ORDER BY c.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$total = 0;
foreach ($cart_items as $item) {
    $total += ($item['price'] * $item['quantity']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Cart - PakBazar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        .cart-img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
        .qty-input { width: 70px; text-align: center; }
    </style>
</head>
<body class="bg-light">

<?php include('customer_sidebar.php'); ?>

<div class="container py-5">
    <h2 class="fw-bold mb-4"><i class="fas fa-shopping-cart me-2"></i> My Shopping Cart</h2>

    <?php if (empty($cart_items)): ?>
        <div class="text-center py-5 bg-white shadow-sm rounded-4">
            <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
            <h4 class="text-muted">Your cart is empty</h4>
            <a href="dashboard.php" class="btn btn-primary mt-3">Start Shopping</a>
        </div>
    <?php else: ?>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-0">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="p-3 border-bottom d-flex align-items-center">
                                <img src="../uploads/<?= htmlspecialchars($item['image'] ?? 'default.jpg') ?>" class="cart-img me-3">
                                
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold"><?= htmlspecialchars($item['name']) ?></h6>
                                    <p class="mb-0 text-success fw-bold">Rs <?= number_format($item['price'], 0) ?></p>
                                </div>

                                <div class="me-4 d-flex align-items-center">
                                    <label class="me-2 small fw-bold">Qty:</label>
                                    <input type="number" class="form-control qty-input form-control-sm" 
                                           value="<?= $item['quantity'] ?>" 
                                           min="1" max="<?= $item['stock'] ?>"
                                           onchange="updateQuantity(<?= $item['product_id'] ?>, this.value)">
                                </div>

                                <div class="text-end">
                                    <button class="btn btn-sm btn-outline-danger rounded-circle" 
                                            onclick="removeFromCart(<?= $item['product_id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 20px;">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4">Order Summary</h5>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Subtotal</span>
                            <span class="fw-bold">Rs <?= number_format($total, 0) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Delivery</span>
                            <span class="text-success fw-bold">Free</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <h5 class="fw-bold">Total</h5>
                            <h5 class="fw-bold text-primary">Rs <?= number_format($total, 0) ?></h5>
                        </div>
                        <a href="checkout.php" class="btn btn-success btn-lg w-100 rounded-pill">
                            <i class="fas fa-arrow-right me-2"></i> Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function updateQuantity(pid, newQty) {
    if (newQty < 1) return;
    
    const fd = new FormData();
    fd.append('product_id', pid);
    fd.append('quantity', newQty);
    fd.append('action', 'update_qty');

    fetch('cart_action.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Refresh to update all totals
        } else {
            alert(data.message);
        }
    });
}

function removeFromCart(productId) {
    if (!confirm('Remove this item?')) return;
    const fd = new FormData();
    fd.append('product_id', productId);
    fd.append('action', 'remove');

    fetch('cart_action.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
    });
}
</script>
</body>
</html>