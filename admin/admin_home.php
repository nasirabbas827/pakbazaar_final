<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// 2. Summary Statistics based on new tables
$stats = [
    'suppliers' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='supplier'"))['count'],
    'customers' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='customer'"))['count'],
    'products'  => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'],
    'revenue'   => mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE order_status='delivered'"))['total'] ?? 0
];

// 3. Chart Data Queries

// Chart 1: Order Trend (Last 7 Days)
$orderTrend = mysqli_query($conn, "SELECT DATE(created_at) as date, COUNT(*) as total FROM orders GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 7");
$chart1_labels = []; $chart1_data = [];
while($row = mysqli_fetch_assoc($orderTrend)) { 
    $chart1_labels[] = $row['date']; 
    $chart1_data[] = $row['total']; 
}
// Reverse to show chronological order
$chart1_labels = array_reverse($chart1_labels);
$chart1_data = array_reverse($chart1_data);

// Chart 2: User Role Distribution
$roleDist = mysqli_query($conn, "SELECT role, COUNT(*) as total FROM users GROUP BY role");
$chart2_labels = []; $chart2_data = [];
while($row = mysqli_fetch_assoc($roleDist)) { 
    $chart2_labels[] = ucfirst($row['role']); 
    $chart2_data[] = $row['total']; 
}

// Chart 3: Top Selling Categories
$catSales = mysqli_query($conn, "
    SELECT c.name, COUNT(oi.id) as sales_count 
    FROM categories c 
    JOIN products p ON c.id = p.category_id 
    JOIN order_items oi ON p.id = oi.product_id 
    GROUP BY c.id LIMIT 5
");
$chart3_labels = []; $chart3_data = [];
while($row = mysqli_fetch_assoc($catSales)) { 
    $chart3_labels[] = $row['name']; 
    $chart3_data[] = $row['sales_count']; 
}

// 4. Fetch Recent Orders (Platform Activity)
$recentOrders = mysqli_query($conn, "
    SELECT o.*, u.name as customer_name 
    FROM orders o 
    JOIN users u ON o.customer_id = u.id 
    ORDER BY o.created_at DESC LIMIT 10
");

// 5. Fetch Newest Users
$newUsers = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - PakBazar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f0f2f5; font-family: 'Inter', sans-serif; }
        .stat-card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .chart-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); height: 100%; }
        .table-card { background: #fff; border-radius: 12px; padding: 20px; margin-top: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        @media (max-width: 992px) { .sidebar-offset { margin-left: 0; } }
    </style>
</head>
<body>

<?php include('admin_sidebar.php'); ?>

<div class="sidebar-offset p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark">Platform Overview</h3>
        <div class="text-muted small">PakBazar Management Portal v2.0</div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card bg-white border-start border-primary border-4 p-3">
                <div class="d-flex justify-content-between">
                    <div><small class="text-muted">Total Suppliers</small><h4 class="fw-bold mb-0"><?= $stats['suppliers'] ?></h4></div>
                    <div class="text-primary"><i class="fas fa-truck fa-2x"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-white border-start border-info border-4 p-3">
                <div class="d-flex justify-content-between">
                    <div><small class="text-muted">Total Customers</small><h4 class="fw-bold mb-0"><?= $stats['customers'] ?></h4></div>
                    <div class="text-info"><i class="fas fa-users fa-2x"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-white border-start border-warning border-4 p-3">
                <div class="d-flex justify-content-between">
                    <div><small class="text-muted">Active Products</small><h4 class="fw-bold mb-0"><?= $stats['products'] ?></h4></div>
                    <div class="text-warning"><i class="fas fa-box fa-2x"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-white border-start border-success border-4 p-3">
                <div class="d-flex justify-content-between">
                    <div><small class="text-muted">Platform Revenue</small><h4 class="fw-bold mb-0">Rs <?= number_format($stats['revenue'], 0) ?></h4></div>
                    <div class="text-success"><i class="fas fa-wallet fa-2x"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="chart-card">
                <h6 class="fw-bold mb-3">Order Frequency (Last 7 Days)</h6>
                <canvas id="orderTrendChart" height="120"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-card">
                <h6 class="fw-bold mb-3">User Distribution</h6>
                <canvas id="userPieChart"></canvas>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="table-card">
                <h6 class="fw-bold mb-3 text-primary">Recent Orders</h6>
                <div class="table-responsive">
                    <table class="table table-hover small">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($order = mysqli_fetch_assoc($recentOrders)): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td>Rs <?= number_format($order['total_amount']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $order['order_status']=='delivered'?'success':'secondary' ?>">
                                        <?= ucfirst($order['order_status']) ?>
                                    </span>
                                </td>
                                <td><?= date('d M', strtotime($order['created_at'])) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="table-card">
                <h6 class="fw-bold mb-3 text-info">Newest Registrations</h6>
                <div class="table-responsive">
                    <table class="table table-sm small">
                        <tbody>
                            <?php while($user = mysqli_fetch_assoc($newUsers)): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($user['name']) ?></strong><br>
                                    <span class="text-muted" style="font-size: 10px;"><?= $user['role'] ?></span>
                                </td>
                                <td class="text-end">
                                    <span class="badge rounded-pill bg-light text-dark"><?= $user['status'] ?></span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Line Chart: Order Trends
new Chart(document.getElementById('orderTrendChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($chart1_labels) ?>,
        datasets: [{
            label: 'Orders',
            data: <?= json_encode($chart1_data) ?>,
            borderColor: '#4f46e5',
            backgroundColor: 'rgba(79, 70, 229, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: { plugins: { legend: { display: false } } }
});

// Pie Chart: User Roles
new Chart(document.getElementById('userPieChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($chart2_labels) ?>,
        datasets: [{
            data: <?= json_encode($chart2_data) ?>,
            backgroundColor: ['#4f46e5', '#06b6d4', '#f59e0b']
        }]
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>