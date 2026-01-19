<?php
include('../config.php');
session_start();

if (!isset($_SESSION["logged_in"]) || $_SESSION["role"] !== "customer") {
    header("Location: ../login.php");
    exit;
}

$customer_id = $_SESSION["user_id"];
$filter_status = $_GET['status'] ?? 'all';

// 1. Fetch Orders based on filter
$query = "SELECT * FROM orders WHERE customer_id = ?";
if ($filter_status !== 'all') {
    $query .= " AND order_status = '" . $conn->real_escape_string($filter_status) . "'";
}
$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$orders = $stmt->get_result();

// 2. Handle Invoice Logic (if a specific order is clicked)
$view_order = null;
$order_items = [];
if (isset($_GET['view_id'])) {
    $order_id = (int)$_GET['view_id'];
    
    // Fetch order details
    $order_stmt = $conn->prepare("SELECT o.*, u.name, u.email, u.phone, u.address, u.city FROM orders o JOIN users u ON o.customer_id = u.id WHERE o.id = ? AND o.customer_id = ?");
    $order_stmt->bind_param("ii", $order_id, $customer_id);
    $order_stmt->execute();
    $view_order = $order_stmt->get_result()->fetch_assoc();

    // Fetch items for this order
    if ($view_order) {
        $items_stmt = $conn->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $items_stmt->bind_param("i", $order_id);
        $items_stmt->execute();
        $order_items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders - PakBazar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            body * { visibility: hidden; }
            .print-area, .print-area * { visibility: visible; }
            .print-area { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none; }
        }
        .status-badge { font-size: 0.8rem; padding: 5px 12px; border-radius: 20px; }
    </style>
</head>
<?php include('customer_sidebar.php'); ?>

<body class="bg-light">

<div class="container py-5 no-print">
 

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex gap-2 flex-wrap">
                <a href="my_orders.php?status=all" class="btn btn-sm <?= $filter_status == 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">All Orders</a>
                <a href="my_orders.php?status=pending" class="btn btn-sm <?= $filter_status == 'pending' ? 'btn-primary' : 'btn-outline-primary' ?>">Pending</a>
                <a href="my_orders.php?status=confirmed" class="btn btn-sm <?= $filter_status == 'confirmed' ? 'btn-primary' : 'btn-outline-primary' ?>">Confirmed</a>
                <a href="my_orders.php?status=shipped" class="btn btn-sm <?= $filter_status == 'shipped' ? 'btn-primary' : 'btn-outline-primary' ?>">Shipped</a>
                <a href="my_orders.php?status=delivered" class="btn btn-sm <?= $filter_status == 'delivered' ? 'btn-primary' : 'btn-outline-primary' ?>">Delivered</a>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders->num_rows > 0): ?>
                        <?php while($row = $orders->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold">#<?= $row['id'] ?></td>
                                <td><?= date('d M, Y', strtotime($row['created_at'])) ?></td>
                                <td>Rs <?= number_format($row['total_amount'], 0) ?></td>
                                <td>
                                    <?php 
                                        $cls = match($row['order_status']) {
                                            'pending' => 'bg-warning text-dark',
                                            'confirmed' => 'bg-info text-white',
                                            'shipped' => 'bg-primary text-white',
                                            'delivered' => 'bg-success text-white',
                                            'cancelled' => 'bg-danger text-white',
                                        };
                                    ?>
                                    <span class="badge <?= $cls ?> status-badge text-capitalize"><?= $row['order_status'] ?></span>
                                </td>
                                <td class="text-center">
                                    <a href="my_orders.php?status=<?= $filter_status ?>&view_id=<?= $row['id'] ?>#invoice" class="btn btn-sm btn-dark">
                                        <i class="fas fa-file-invoice me-1"></i> View Slip
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">No orders found in this category.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($view_order): ?>
<div class="container py-5 pt-0" id="invoice">
    <div class="card border-0 shadow-lg print-area">
        <div class="card-body p-5">
            <div class="d-flex justify-content-between mb-4">
                <div>
                    <h1 class="fw-bold text-primary mb-0">PAKBAZAR</h1>
                    <p class="text-muted">Official Order Receipt</p>
                </div>
                <div class="text-end">
                    <h4 class="mb-0">INVOICE</h4>
                    <p class="text-muted mb-0">Order #<?= $view_order['id'] ?></p>
                    <p class="text-muted"><?= date('d F, Y', strtotime($view_order['created_at'])) ?></p>
                </div>
            </div>

            <hr>

            <div class="row mb-4">
                <div class="col-6">
                    <h6 class="fw-bold">Billed To:</h6>
                    <p class="mb-0"><?= htmlspecialchars($view_order['name']) ?></p>
                    <p class="mb-0"><?= htmlspecialchars($view_order['phone']) ?></p>
                    <p class="mb-0"><?= htmlspecialchars($view_order['address']) ?>, <?= htmlspecialchars($view_order['city']) ?></p>
                </div>
                <div class="col-6 text-end">
                    <h6 class="fw-bold">Payment Status:</h6>
                    <p class="mb-0 text-uppercase"><?= $view_order['payment_method'] ?></p>
                    <p class="text-success fw-bold">PENDING ON DELIVERY</p>
                </div>
            </div>

            <table class="table table-bordered">
                <thead class="bg-light">
                    <tr>
                        <th>Product Description</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td class="text-center">Rs <?= number_format($item['price']) ?></td>
                        <td class="text-center"><?= $item['quantity'] ?></td>
                        <td class="text-end">Rs <?= number_format($item['price'] * $item['quantity']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Grand Total</th>
                        <th class="text-end text-primary">Rs <?= number_format($view_order['total_amount']) ?></th>
                    </tr>
                </tfoot>
            </table>

            <div class="mt-5 no-print text-center">
                <button onclick="window.print()" class="btn btn-success px-5 rounded-pill me-2">
                    <i class="fas fa-print me-2"></i> Print Order Slip
                </button>
                <a href="my_orders.php?status=<?= $filter_status ?>" class="btn btn-outline-secondary px-5 rounded-pill">Close Slip</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

</body>
</html>