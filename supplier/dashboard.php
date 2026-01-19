<?php
include('../config.php');
session_start();

// 1. Security & Role Check
if (!isset($_SESSION["logged_in"]) || $_SESSION["role"] !== "supplier") {
    header("Location: ../login.php");
    exit;
}

$supplier_id = $_SESSION["user_id"];
$supplier_name = $_SESSION["name"] ?? "Supplier";

// 2. Fetch Stat Cards Data
$sales_res = $conn->query("SELECT SUM(price * quantity) as total FROM order_items WHERE supplier_id = $supplier_id");
$total_revenue = $sales_res->fetch_assoc()['total'] ?? 0;

$order_count_res = $conn->query("SELECT COUNT(DISTINCT order_id) as total FROM order_items WHERE supplier_id = $supplier_id");
$total_orders = $order_count_res->fetch_assoc()['total'] ?? 0;

$stock_res = $conn->query("SELECT COUNT(*) as low FROM products WHERE supplier_id = $supplier_id AND stock <= low_stock_threshold");
$low_stock_count = $stock_res->fetch_assoc()['low'] ?? 0;

// 3. Analytics (Last 7 Days)
$chart_labels = []; 
$chart_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[] = date('D', strtotime($date));
    $daily_query = "SELECT COALESCE(SUM(oi.price * oi.quantity), 0) as daily_total 
                    FROM order_items oi 
                    JOIN orders o ON oi.order_id = o.id 
                    WHERE oi.supplier_id = $supplier_id AND DATE(o.created_at) = '$date'";
    $daily_res = $conn->query($daily_query);
    $chart_data[] = (float)$daily_res->fetch_assoc()['daily_total'];
}

// 4. Fetch Notifications (Recent 5 Orders from last 24 hours)
$notif_orders = $conn->query("
    SELECT o.id, u.name as customer_name, o.created_at 
    FROM orders o 
    JOIN order_items oi ON o.id = oi.order_id 
    JOIN users u ON o.customer_id = u.id 
    WHERE oi.supplier_id = $supplier_id 
    AND o.created_at >= NOW() - INTERVAL 1 DAY
    GROUP BY o.id 
    ORDER BY o.created_at DESC LIMIT 5
");

// 5. Main Recent Orders Table
$recent_orders = $conn->query("
    SELECT o.id, o.created_at, o.order_status, u.name as customer_name, 
    SUM(oi.price * oi.quantity) as supplier_subtotal
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN users u ON o.customer_id = u.id
    WHERE oi.supplier_id = $supplier_id
    GROUP BY o.id
    ORDER BY o.created_at DESC LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Supplier Dashboard | PakBazar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --sidebar-width: 260px; --primary-color: #4f46e5; }
        body { background: #f8fafc; font-family: 'Inter', sans-serif; margin-left: var(--sidebar-width); transition: 0.3s; }
        .dashboard-container { padding: 2.5rem; }
        .stat-card { border: none; border-radius: 16px; background: white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .icon-box { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .table-card { border: none; border-radius: 16px; background: white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .notif-badge { font-size: 0.75rem; padding: 2px 8px; border-radius: 10px; }
        @media (max-width: 992px) { body { margin-left: 0; } }
    </style>
</head>
<body>

<?php include('sidebar.php'); ?>

<div class="dashboard-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Supplier Overview</h2>
            <p class="text-muted mb-0">Hello, <strong><?= htmlspecialchars($supplier_name) ?></strong>. Here is your business status.</p>
        </div>
        <div class="text-end d-none d-md-block">
            <span class="badge bg-white text-dark border px-3 py-2 rounded-3 shadow-sm">
                <i class="far fa-calendar-alt text-primary me-2"></i><?= date('l, M j, Y') ?>
            </span>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <?php if ($low_stock_count > 0): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-2" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-3 fa-lg"></i>
                    <div><strong>Inventory Alert!</strong> <?= $low_stock_count ?> of your products are below the stock threshold.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <?php while($n = $notif_orders->fetch_assoc()): ?>
            <div class="alert alert-primary alert-dismissible fade show border-0 shadow-sm mb-2" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-shopping-cart me-3 fa-lg"></i>
                    <div>
                        <strong>New Order Received!</strong> Order #<?= $n['id'] ?> placed by <?= htmlspecialchars($n['customer_name']) ?>.
                        <small class="text-muted ms-2"><?= date('h:i A', strtotime($n['created_at'])) ?></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card stat-card p-3 border-start border-4 border-primary">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-primary text-white me-3"><i class="fas fa-coins"></i></div>
                    <div>
                        <small class="text-muted fw-bold">TOTAL REVENUE</small>
                        <h4 class="fw-bold mb-0">Rs <?= number_format($total_revenue) ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card p-3 border-start border-4 border-success">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-success text-white me-3"><i class="fas fa-receipt"></i></div>
                    <div>
                        <small class="text-muted fw-bold">TOTAL ORDERS</small>
                        <h4 class="fw-bold mb-0"><?= $total_orders ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card p-3 border-start border-4 border-danger">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-danger text-white me-3"><i class="fas fa-layer-group"></i></div>
                    <div>
                        <small class="text-muted fw-bold">LOW STOCK ITEMS</small>
                        <h4 class="fw-bold mb-0"><?= $low_stock_count ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-5 g-4">
        <div class="col-lg-12">
            <div class="card p-4 border-0 shadow-sm rounded-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0">Revenue Trend (Last 7 Days)</h6>
                    <i class="fas fa-ellipsis-v text-muted"></i>
                </div>
                <div style="height: 300px;">
                    <canvas id="supplierSalesChart"></canvas>
                </div>
            </div>
        </div>
        

    </div>

    <div class="card table-card border-0">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-white rounded-top-4">
            <h6 class="fw-bold mb-0">Recent Order History</h6>
            <a href="supplier_orders.php" class="text-primary text-decoration-none small fw-bold">View Detailed List <i class="fas fa-chevron-right ms-1"></i></a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-muted small">
                        <th class="ps-3">ORDER ID</th>
                        <th>CUSTOMER</th>
                        <th>EARNED AMOUNT</th>
                        <th>STATUS</th>
                        <th class="text-end pe-3">DATE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($recent_orders->num_rows > 0): ?>
                        <?php while($row = $recent_orders->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-3 fw-bold">#<?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['customer_name']) ?></td>
                            <td class="fw-bold text-dark">Rs <?= number_format($row['supplier_subtotal']) ?></td>
                            <td>
                                <span class="badge rounded-pill bg-<?= $row['order_status'] == 'pending' ? 'warning text-dark' : ($row['order_status'] == 'cancelled' ? 'danger' : 'success') ?>">
                                    <?= ucfirst($row['order_status']) ?>
                                </span>
                            </td>
                            <td class="text-end pe-3 text-muted small"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No orders recorded for your products yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('supplierSalesChart').getContext('2d');
    const labels = <?= json_encode($chart_labels) ?>;
    const salesData = <?= json_encode($chart_data) ?>;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue (Rs)',
                data: salesData,
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.08)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: '#4f46e5'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: '#f1f5f9' },
                    ticks: { callback: value => 'Rs ' + value.toLocaleString() }
                },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
</body>
</html>