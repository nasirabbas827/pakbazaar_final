<?php
include('../config.php');
session_start();

// Security: only suppliers
if (!isset($_SESSION["logged_in"]) || $_SESSION["role"] !== "supplier") {
    header("Location: ../login.php");
    exit;
}

$supplier_id = $_SESSION["user_id"];

// ────────────────────────────────────────────────
// Handle status update
// ────────────────────────────────────────────────
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id   = (int)$_POST['order_id'];
    $new_status = $_POST['status'] ?? '';

    $valid_statuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
    if (in_array($new_status, $valid_statuses)) {
        $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        if ($stmt->execute()) {
            $msg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                      Order #{$order_id} updated to <strong>" . ucfirst($new_status) . "</strong>.
                      <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
        } else {
            $msg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                      Failed to update order status.
                      <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
        }
        $stmt->close();
    }
}

// ────────────────────────────────────────────────
// Filters
// ────────────────────────────────────────────────
$status_filter = $_GET['status'] ?? 'all';
$start_date    = $_GET['start_date'] ?? date('Y-m-01');
$end_date      = $_GET['end_date']   ?? date('Y-m-d');

// Normalize dates
$start_date = date('Y-m-d', strtotime($start_date));
$end_date   = date('Y-m-d', strtotime($end_date));

// ────────────────────────────────────────────────
// Main query – orders containing supplier's products
// ────────────────────────────────────────────────
$sql = "
    SELECT DISTINCT 
        o.id, o.customer_id, o.total_amount, o.order_status, o.payment_method, o.created_at,
        u.name AS customer_name, u.phone, u.address, u.city
    FROM orders o
    INNER JOIN order_items oi ON o.id = oi.order_id
    INNER JOIN users u ON o.customer_id = u.id
    WHERE oi.supplier_id = ?
      AND DATE(o.created_at) BETWEEN ? AND ?
";

$params = [$supplier_id, $start_date, $end_date];
$types  = "iss";

if ($status_filter !== 'all') {
    $sql .= " AND o.order_status = ?";
    $params[] = $status_filter;
    $types   .= "s";
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$orders_result = $stmt->get_result();

// ────────────────────────────────────────────────
// Chart 1: Sales Trend – Last 30 days (Bar + Line combo)
// ────────────────────────────────────────────────
$trend_sql = "
    SELECT DATE(o.created_at) AS day, 
           SUM(oi.price * oi.quantity) AS daily_total
    FROM orders o
    INNER JOIN order_items oi ON o.id = oi.order_id
    WHERE oi.supplier_id = ?
      AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY day
    ORDER BY day ASC
";

$trend_stmt = $conn->prepare($trend_sql);
$trend_stmt->bind_param("i", $supplier_id);
$trend_stmt->execute();
$trend_res = $trend_stmt->get_result();

$trend_labels = [];
$trend_data   = [];

while ($r = $trend_res->fetch_assoc()) {
    $trend_labels[] = date('d M', strtotime($r['day']));
    $trend_data[]   = (float)$r['daily_total'];
}
$trend_stmt->close();

// ────────────────────────────────────────────────
// Chart 2: Order Status Distribution (Doughnut)
// ────────────────────────────────────────────────
$status_sql = "
    SELECT o.order_status, COUNT(DISTINCT o.id) AS count
    FROM orders o
    INNER JOIN order_items oi ON o.id = oi.order_id
    WHERE oi.supplier_id = ?
    GROUP BY o.order_status
";

$status_stmt = $conn->prepare($status_sql);
$status_stmt->bind_param("i", $supplier_id);
$status_stmt->execute();
$status_res = $status_stmt->get_result();

$status_labels = [];
$status_data   = [];

$status_colors = [
    'pending'    => '#f59e0b',
    'confirmed'  => '#3b82f6',
    'shipped'    => '#10b981',
    'delivered'  => '#059669',
    'cancelled'  => '#ef4444'
];

while ($r = $status_res->fetch_assoc()) {
    $status_labels[] = ucfirst($r['order_status']);
    $status_data[]   = (int)$r['count'];
}
$status_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - Supplier Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <style>
        body {
            background: #f8f9fc;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .card-header {
            background: white;
            border-bottom: 1px solid #e5e7eb;
        }
        .status-pending    { background:#fef3c7; color:#92400e; }
        .status-confirmed  { background:#dbeafe; color:#1e40af; }
        .status-shipped    { background:#d1fae5; color:#065f46; }
        .status-delivered  { background:#dcfce7; color:#166534; }
        .status-cancelled  { background:#fee2e2; color:#991b1b; }
        .table th {
            text-transform: uppercase;
            font-size: 0.82rem;
            letter-spacing: 0.4px;
            color: #6b7280;
        }
        .badge-status {
            font-size: 0.9rem;
            padding: 0.5em 1em;
            font-weight: 500;
        }
        @media print {
            .no-print, .sidebar-space { display: none !important; }
            .container-fluid { padding: 0; margin: 0; width: 100%; }
            body { background: white; }
            .card { box-shadow: none; border: 1px solid #ddd; }
        }
    </style>
</head>
<body>

<?php include('sidebar.php'); // ← your supplier sidebar file ?>

<div class="container-fluid py-4">

    <?php if ($msg) echo $msg; ?>

    <!-- Header + Print -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 no-print gap-3">
        <div>
            <h3 class="fw-bold mb-1">Order Management</h3>
            <p class="text-muted mb-0">View, filter and update orders containing your products</p>
        </div>
        <button onclick="window.print()" class="btn btn-outline-secondary px-4 no-print">
            <i class="fas fa-print me-2"></i> Print Report
        </button>
    </div>

    <!-- Two Charts Row -->
    <div class="row g-4 mb-4 no-print">
        <!-- Chart 1: Sales Trend (30 days) -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="fw-bold mb-0 text-muted text-uppercase small">
                        Sales Trend – Last 30 Days
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div style="height: 320px;">
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart 2: Order Status Distribution -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="fw-bold mb-0 text-muted text-uppercase small">
                        Order Status Breakdown
                    </h6>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center" style="height: 320px;">
                    <?php if (array_sum($status_data) > 0): ?>
                        <canvas id="statusDoughnutChart"></canvas>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-chart-pie fa-3x mb-3 opacity-50"></i><br>
                            No orders yet
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4 no-print">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-bold small text-muted">Order Status</label>
                    <select name="status" class="form-select">
                        <option value="all">All Orders</option>
                        <option value="pending"    <?= $status_filter==='pending'?'selected':'' ?>>Pending</option>
                        <option value="confirmed"  <?= $status_filter==='confirmed'?'selected':'' ?>>Confirmed</option>
                        <option value="shipped"    <?= $status_filter==='shipped'?'selected':'' ?>>Shipped</option>
                        <option value="delivered"  <?= $status_filter==='delivered'?'selected':'' ?>>Delivered</option>
                        <option value="cancelled"  <?= $status_filter==='cancelled'?'selected':'' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-bold small text-muted">From Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-bold small text-muted">To Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
                </div>
                <div class="col-md-3 col-sm-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">Orders Found: <?= $orders_result->num_rows ?></h6>
            <small class="text-muted">Showing only orders that include your products</small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date & Time</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="no-print text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders_result->num_rows === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-3 opacity-50"></i><br>
                                No orders found matching your filters.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-bold">#<?= $order['id'] ?></td>
                            <td>
                                <?= htmlspecialchars($order['customer_name']) ?><br>
                                <small class="text-muted"><?= htmlspecialchars($order['phone'] ?? '—') ?></small>
                            </td>
                            <td><?= date('d M Y • h:i A', strtotime($order['created_at'])) ?></td>
                            <td class="fw-bold">Rs <?= number_format($order['total_amount'], 0) ?></td>
                            <td>
                                <span class="badge badge-status status-<?= $order['order_status'] ?>">
                                    <?= ucfirst($order['order_status']) ?>
                                </span>
                            </td>
                            <td class="no-print text-end">
                                <div class="d-flex gap-2 justify-content-end flex-wrap">
                                    <form method="POST" class="d-flex gap-1 align-items-center">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <select name="status" class="form-select form-select-sm" style="width:145px;">
                                            <option value="pending"    <?= $order['order_status']==='pending'?'selected':'' ?>>Pending</option>
                                            <option value="confirmed"  <?= $order['order_status']==='confirmed'?'selected':'' ?>>Confirmed</option>
                                            <option value="shipped"    <?= $order['order_status']==='shipped'?'selected':'' ?>>Shipped</option>
                                            <option value="delivered"  <?= $order['order_status']==='delivered'?'selected':'' ?>>Delivered</option>
                                            <option value="cancelled"  <?= $order['order_status']==='cancelled'?'selected':'' ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-sm btn-success px-2">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>

                                    <a href="order_details.php?id=<?= $order['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary px-3">
                                        <i class="fas fa-eye"></i> Details
                                    </a>

                                    <a href="order_slip.php?id=<?= $order['id'] ?>" target="_blank" 
                                       class="btn btn-sm btn-outline-secondary px-3">
                                        <i class="fas fa-file-invoice"></i> Slip
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Chart 1 – Sales Trend (Bar + Gradient Line)
const trendCtx = document.getElementById('salesTrendChart')?.getContext('2d');
if (trendCtx) {
    new Chart(trendCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($trend_labels) ?>,
            datasets: [
                {
                    type: 'bar',
                    label: 'Daily Sales',
                    data: <?= json_encode($trend_data) ?>,
                    backgroundColor: 'rgba(99, 102, 241, 0.58)',
                    borderColor: 'rgba(99, 102, 241, 0.9)',
                    borderWidth: 1,
                    borderRadius: 6,
                    order: 2
                },
                {
                    type: 'line',
                    label: 'Trend',
                    data: <?= json_encode($trend_data) ?>,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.12)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#6366f1',
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', align: 'end', labels: { boxWidth: 12 } },
                tooltip: {
                    backgroundColor: 'rgba(30, 41, 59, 0.92)',
                    titleFont: { size: 14 },
                    bodyFont: { size: 13 },
                    callbacks: {
                        label: (ctx) => ` ${ctx.dataset.label}: Rs ${ctx.parsed.y.toLocaleString()}`
                    }
                }
            },
            scales: {
                x: { grid: { display: false } },
                y: {
                    beginAtZero: true,
                    grid: { color: '#e5e7eb' },
                    ticks: {
                        callback: (v) => 'Rs ' + v.toLocaleString()
                    }
                }
            }
        }
    });
}

// Chart 2 – Order Status Doughnut
const statusCtx = document.getElementById('statusDoughnutChart')?.getContext('2d');
if (statusCtx && <?= count($status_data) ?> > 0) {
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($status_labels) ?>,
            datasets: [{
                data: <?= json_encode($status_data) ?>,
                backgroundColor: [
                    '#f59e0b', '#3b82f6', '#10b981', '#059669', '#ef4444'
                ],
                borderColor: '#ffffff',
                borderWidth: 3,
                hoverOffset: 12
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        font: { size: 13 },
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(30, 41, 59, 0.94)',
                    callbacks: {
                        label: (ctx) => ` ${ctx.label}: ${ctx.parsed} orders`
                    }
                }
            }
        }
    });
}
</script>
</body>
</html>