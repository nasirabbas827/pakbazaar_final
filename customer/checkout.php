<?php
include('../config.php');
session_start();

if (!isset($_SESSION["logged_in"]) || $_SESSION["role"] !== "customer") {
    header("Location: ../login.php");
    exit;
}

$customer_id = $_SESSION["user_id"];

// 1. Fetch customer current info
$stmt = $conn->prepare("SELECT name, phone, address, city FROM users WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$user = $user_stmt_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 2. Fetch cart items with quantity and supplier info
$cart_query = "
    SELECT 
        c.product_id, 
        c.quantity,
        p.name, 
        p.price, 
        p.image,
        p.stock,
        p.supplier_id,
        u.name AS supplier_name
    FROM cart c
    JOIN products p ON c.product_id = p.id
    JOIN users u ON p.supplier_id = u.id
    WHERE c.customer_id = ?
";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($cart_items)) {
    header("Location: cart.php");
    exit;
}

// 3. Calculate total based on Quantity
$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += ($item['price'] * $item['quantity']);
}

// 4. Handle address/phone update
$update_msg = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_info'])) {
    $new_phone   = trim($_POST['phone'] ?? '');
    $new_address = trim($_POST['address'] ?? '');
    $new_city    = trim($_POST['city'] ?? '');

    $update_stmt = $conn->prepare("UPDATE users SET phone = ?, address = ?, city = ? WHERE id = ?");
    $update_stmt->bind_param("sssi", $new_phone, $new_address, $new_city, $customer_id);
    
    if ($update_stmt->execute()) {
        $update_msg = '<div class="alert alert-success">Profile updated successfully!</div>';
        $user['phone'] = $new_phone;
        $user['address'] = $new_address;
        $user['city'] = $new_city;
    }
    $update_stmt->close();
}

// 5. Handle place order (With Stock Deduction)
$order_msg = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['place_order'])) {
    $conn->begin_transaction();

    try {
        // A. Create main order
        $order_stmt = $conn->prepare("INSERT INTO orders (customer_id, total_amount, payment_method, order_status) VALUES (?, ?, 'COD', 'pending')");
        $order_stmt->bind_param("id", $customer_id, $total_amount);
        $order_stmt->execute();
        $order_id = $conn->insert_id;
        $order_stmt->close();

        // B. Insert order items & Deduct Stock
        $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, supplier_id, quantity, price) VALUES (?, ?, ?, ?, ?)");
        $stock_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

        foreach ($cart_items as $item) {
            // Check if enough stock exists one last time
            if ($item['stock'] < $item['quantity']) {
                throw new Exception("Insufficient stock for: " . $item['name']);
            }

            // Insert into order_items
            $item_stmt->bind_param("iiiid", $order_id, $item['product_id'], $item['supplier_id'], $item['quantity'], $item['price']);
            $item_stmt->execute();

            // DEDUCT STOCK
            $stock_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $stock_stmt->execute();
        }
        $item_stmt->close();
        $stock_stmt->close();

        // C. Clear cart
        $conn->query("DELETE FROM cart WHERE customer_id = $customer_id");

        $conn->commit();
        $order_msg = '
            <div class="alert alert-success text-center py-4 rounded-4 shadow-sm">
                <i class="fas fa-check-circle fa-3x mb-3"></i>
                <h4>Order Placed Successfully!</h4>
                <p>Order ID: <strong>#' . $order_id . '</strong> | Total: <strong>Rs ' . number_format($total_amount) . '</strong></p>
                <a href="dashboard.php" class="btn btn-primary px-4 rounded-pill">Back to Shop</a>
            </div>';

    } catch (Exception $e) {
        $conn->rollback();
        $order_msg = '<div class="alert alert-danger">Order failed: ' . $e->getMessage() . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - PakBazar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        .cart-img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
        .summary-card { position: sticky; top: 20px; border-radius: 15px; }
    </style>
</head>
<body class="bg-light">

<?php include('customer_sidebar.php'); ?>

<div class="container py-5">
    <?php if ($order_msg): ?>
        <div class="max-width: 600px; margin: auto;"><?= $order_msg ?></div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4 rounded-3">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Delivery Details</h5>
                        <?= $update_msg ?>
                        <form method="post" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">City</label>
                                <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($user['city']) ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Full Address</label>
                                <textarea name="address" class="form-control" rows="2" required><?= htmlspecialchars($user['address']) ?></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="update_info" class="btn btn-sm btn-primary">Update Delivery Info</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-0">
                        <div class="p-3 border-bottom"><h5 class="mb-0 fw-bold">Review Items</h5></div>
                        <?php foreach ($cart_items as $item): ?>
                            <div class="d-flex align-items-center p-3 border-bottom">
                                <img src="../uploads/<?= htmlspecialchars($item['image'] ?? 'default.jpg') ?>" class="cart-img me-3">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($item['name']) ?></h6>
                                    <small class="text-muted">Qty: <?= $item['quantity'] ?> × Rs <?= number_format($item['price']) ?></small>
                                </div>
                                <div class="fw-bold text-dark">Rs <?= number_format($item['price'] * $item['quantity']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm summary-card">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Order Summary</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Items</span>
                            <span><?= count($cart_items) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-4">
                            <h5 class="fw-bold">Grand Total</h5>
                            <h5 class="fw-bold text-success">Rs <?= number_format($total_amount) ?></h5>
                        </div>
                        <div class="bg-light p-3 rounded-3 mb-4">
                            <small class="text-muted d-block">Payment Method:</small>
                            <strong>Cash on Delivery</strong>
                        </div>
                        <form method="post">
                            <button type="submit" name="place_order" class="btn btn-success btn-lg w-100 rounded-pill shadow-sm">
                                Confirm Order
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>