<?php
include('../config.php');
session_start();

// Security check - only logged-in supplier
if (!isset($_SESSION["logged_in"]) || $_SESSION["role"] !== "supplier") {
    header("Location: ../login.php");
    exit;
}

$supplier_id = $_SESSION["user_id"];
$order_id    = (int)($_GET['id'] ?? 0);

if ($order_id <= 0) {
    die("<div class='alert alert-danger text-center m-5'>Invalid order ID.</div>");
}

// ────────────────────────────────────────────────
// Fetch order header information
// ────────────────────────────────────────────────
$order_stmt = $conn->prepare("
    SELECT o.*, u.name AS customer_name, u.phone, u.address, u.city
    FROM orders o
    JOIN users u ON o.customer_id = u.id
    WHERE o.id = ?
");
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();
$order_stmt->close();

if (!$order) {
    die("<div class='alert alert-danger text-center m-5'>Order not found.</div>");
}

// ────────────────────────────────────────────────
// Handle status update from this page
// ────────────────────────────────────────────────
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'] ?? '';

    $valid = ['pending','confirmed','shipped','delivered','cancelled'];
    if (in_array($new_status, $valid)) {
        $upd = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $upd->bind_param("si", $new_status, $order_id);
        if ($upd->execute()) {
            $msg = "<div class='alert alert-success alert-dismissible fade show'>
                      Order status updated to <strong>" . ucfirst($new_status) . "</strong>
                      <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>";
            $order['order_status'] = $new_status; // refresh display
        } else {
            $msg = "<div class='alert alert-danger'>Failed to update status.</div>";
        }
        $upd->close();
    }
}

// ────────────────────────────────────────────────
// Fetch only THIS supplier's items in this order
// ────────────────────────────────────────────────
$items_stmt = $conn->prepare("
    SELECT 
        oi.*, 
        p.name AS product_name,
        p.image AS product_image
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
      AND oi.supplier_id = ?
    ORDER BY oi.id
");
$items_stmt->bind_param("ii", $order_id, $supplier_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$items = [];
$supplier_total = 0;

while ($row = $items_result->fetch_assoc()) {
    $subtotal = $row['quantity'] * $row['price'];
    $supplier_total += $subtotal;
    $items[] = $row + ['subtotal' => $subtotal];
}
$items_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?= $order_id ?> Details - Supplier Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fc; font-family: 'Segoe UI', sans-serif; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 14px rgba(0,0,0,0.07); }
        .table th { background: #f1f5f9; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .product-img { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; }
        .status-pending    { background:#fef3c7; color:#92400e; }
        .status-confirmed  { background:#dbeafe; color:#1e40af; }
        .status-shipped    { background:#d1fae5; color:#065f46; }
        .status-delivered  { background:#dcfce7; color:#166534; }
        .status-cancelled  { background:#fee2e2; color:#991b1b; }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .container { width: 100%; margin: 0; padding: 10px; }
            .card { box-shadow: none; border: 1px solid #ccc; }
        }
    </style>
</head>
<body>

<?php include('sidebar.php'); // your supplier sidebar ?>

<div class="container py-4" style="max-width: 1100px;">

    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h3 class="fw-bold mb-1">Order #<?= $order_id ?> Details</h3>
            <p class="text-muted mb-0">Products sold by you in this order</p>
        </div>
        <div class="no-print">
            <a href="orders.php" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Orders
            </a>
            <button onclick="window.print()" class="btn btn-outline-dark">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>

    <?= $msg ?>

    <div class="row g-4">

        <!-- Order Information -->
        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold">Order Information</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Order ID</dt>
                        <dd class="col-sm-8 fw-bold">#<?= $order_id ?></dd>

                        <dt class="col-sm-4 text-muted">Date</dt>
                        <dd class="col-sm-8"><?= date('d M Y • h:i A', strtotime($order['created_at'])) ?></dd>

                        <dt class="col-sm-4 text-muted">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge fs-6 px-3 py-2 status-<?= $order['order_status'] ?>">
                                <?= ucfirst($order['order_status']) ?>
                            </span>
                        </dd>

                        <dt class="col-sm-4 text-muted">Payment</dt>
                        <dd class="col-sm-8"><?= strtoupper($order['payment_method']) ?></dd>

                        <dt class="col-sm-4 text-muted">Your Revenue</dt>
                        <dd class="col-sm-8 fs-5 fw-bold text-success">
                            Rs <?= number_format($supplier_total, 0) ?>
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card no-print">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold">Update Order Status</h6>
                </div>
                <div class="card-body">
                    <form method="POST" class="d-flex gap-2 flex-wrap">
                        <select name="status" class="form-select">
                            <option value="pending"    <?= $order['order_status']==='pending'?'selected':'' ?>>Pending</option>
                            <option value="confirmed"  <?= $order['order_status']==='confirmed'?'selected':'' ?>>Confirmed</option>
                            <option value="shipped"    <?= $order['order_status']==='shipped'?'selected':'' ?>>Shipped</option>
                            <option value="delivered"  <?= $order['order_status']==='delivered'?'selected':'' ?>>Delivered</option>
                            <option value="cancelled"  <?= $order['order_status']==='cancelled'?'selected':'' ?>>Cancelled</option>
                        </select>
                        <button type="submit" name="update_status" class="btn btn-success px-4">
                            Update
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Customer & Shipping -->
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold">Customer & Shipping Details</h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold mb-2"><?= htmlspecialchars($order['customer_name']) ?></h6>
                    <p class="mb-1"><i class="fas fa-phone me-2 text-muted"></i> <?= htmlspecialchars($order['phone'] ?? 'Not provided') ?></p>
                    <p class="mb-1"><i class="fas fa-map-marker-alt me-2 text-muted"></i>
                        <?= htmlspecialchars($order['address'] ?? 'No address provided') ?>
                        <?= $order['city'] ? ', ' . htmlspecialchars($order['city']) : '' ?>
                    </p>
                </div>
            </div>

            <!-- Supplier's Items in this Order -->
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Your Products in this Order (<?= count($items) ?> items)</h6>
                    <span class="text-success fw-bold">
                        Subtotal: Rs <?= number_format($supplier_total, 0) ?>
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        No products from your store in this order.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../uploads/<?= htmlspecialchars($item['product_image'] ?? 'default-product.jpg') ?>" 
                                                 class="product-img me-3" alt="">
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($item['product_name']) ?></div>
                                                <small class="text-muted">Product ID: <?= $item['product_id'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Rs <?= number_format($item['price'], 0) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td class="text-end fw-bold">
                                        Rs <?= number_format($item['subtotal'], 0) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>



</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>