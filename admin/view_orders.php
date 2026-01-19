<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// 2. Filter & Report Logic
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$start_date = isset($_GET['start_date']) ? mysqli_real_escape_string($conn, $_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? mysqli_real_escape_string($conn, $_GET['end_date']) : '';

$query = "SELECT o.*, u.name as customer_name, u.email as customer_email 
          FROM orders o 
          JOIN users u ON o.customer_id = u.id 
          WHERE 1=1";

if ($status_filter) { $query .= " AND o.order_status = '$status_filter'"; }
if ($start_date) { $query .= " AND DATE(o.created_at) >= '$start_date'"; }
if ($end_date) { $query .= " AND DATE(o.created_at) <= '$end_date'"; }

$query .= " ORDER BY o.created_at DESC";
$result = mysqli_query($conn, $query);

// 3. Report Totals (Calculated from filtered results)
$total_orders = mysqli_num_rows($result);
$total_revenue = 0;
$results_array = [];
while($row = mysqli_fetch_assoc($result)) {
    $results_array[] = $row;
    if($row['order_status'] !== 'cancelled') {
        $total_revenue += $row['total_amount'];
    }
}

// 4. AJAX Order Details Handler
if (isset($_POST['fetch_order_id'])) {
    $oid = mysqli_real_escape_string($conn, $_POST['fetch_order_id']);
    $items_query = mysqli_query($conn, "SELECT oi.*, p.name as prod_name, s.name as supplier_name 
                                       FROM order_items oi 
                                       JOIN products p ON oi.product_id = p.id 
                                       JOIN users s ON oi.supplier_id = s.id 
                                       WHERE oi.order_id = '$oid'");
    echo '<table class="table table-sm"><thead><tr><th>Product</th><th>Supplier</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead><tbody>';
    while($item = mysqli_fetch_assoc($items_query)) {
        echo "<tr>
                <td>{$item['prod_name']}</td>
                <td>{$item['supplier_name']}</td>
                <td>{$item['quantity']}</td>
                <td>Rs ".number_format($item['price'])."</td>
                <td>Rs ".number_format($item['price'] * $item['quantity'])."</td>
              </tr>";
    }
    echo '</tbody></table>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Management | PakBazar Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7fe; font-family: 'Inter', sans-serif; margin-left: 280px; }
        .main-content { padding: 2rem; }
        .report-card { border: none; border-radius: 12px; background: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .status-badge { font-size: 0.75rem; padding: 5px 10px; border-radius: 50px; }
        @media (max-width: 992px) { body { margin-left: 0; } }
        @media print { .sidebar, .filter-section, .btn-action { display: none !important; } body { margin-left: 0; } }
    </style>
</head>
<body>

<?php include('admin_sidebar.php'); ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">Order System & Reports</h2>
        <button onclick="window.print()" class="btn btn-dark btn-sm btn-action"><i class="fas fa-print me-2"></i>Print Report</button>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="report-card p-3 d-flex align-items-center">
                <div class="bg-primary text-white p-3 rounded-circle me-3"><i class="fas fa-shopping-bag fa-lg"></i></div>
                <div><small class="text-muted">Filtered Orders</small><h4 class="mb-0 fw-bold"><?= $total_orders ?></h4></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="report-card p-3 d-flex align-items-center">
                <div class="bg-success text-white p-3 rounded-circle me-3"><i class="fas fa-money-bill-wave fa-lg"></i></div>
                <div><small class="text-muted">Total Revenue (Excl. Cancelled)</small><h4 class="mb-0 fw-bold">Rs <?= number_format($total_revenue) ?></h4></div>
            </div>
        </div>
    </div>

    <div class="card report-card p-4 mb-4 filter-section">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="small fw-bold">From Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
            </div>
            <div class="col-md-3">
                <label class="small fw-bold">To Date</label>
                <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
            </div>
            <div class="col-md-3">
                <label class="small fw-bold">Order Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= ($status_filter == 'pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="confirmed" <?= ($status_filter == 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
                    <option value="shipped" <?= ($status_filter == 'shipped') ? 'selected' : '' ?>>Shipped</option>
                    <option value="delivered" <?= ($status_filter == 'delivered') ? 'selected' : '' ?>>Delivered</option>
                    <option value="cancelled" <?= ($status_filter == 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-2"></i>Apply Filters</button>
            </div>
        </form>
    </div>

    <div class="card report-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end pe-4">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($results_array as $row): ?>
                    <tr>
                        <td class="ps-4 fw-bold">#<?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['customer_name']) ?><br><small class="text-muted"><?= $row['customer_email'] ?></small></td>
                        <td class="fw-bold text-primary">Rs <?= number_format($row['total_amount']) ?></td>
                        <td>
                            <?php
                                $c = ['pending'=>'bg-warning','confirmed'=>'bg-info','shipped'=>'bg-primary','delivered'=>'bg-success','cancelled'=>'bg-danger'];
                                $badge = $c[$row['order_status']] ?? 'bg-secondary';
                            ?>
                            <span class="badge status-badge <?= $badge ?> text-uppercase"><?= $row['order_status'] ?></span>
                        </td>
                        <td><?= date('d M Y, h:i A', strtotime($row['created_at'])) ?></td>
                        <td class="text-end pe-4">
                            <button class="btn btn-outline-primary btn-sm" onclick="viewDetails(<?= $row['id'] ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="orderDetailsBody">
                <div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div></div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function viewDetails(orderId) {
    $('#modalTitle').text('Order #' + orderId + ' Items');
    $('#orderDetailsBody').html('<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div></div>');
    var myModal = new bootstrap.Modal(document.getElementById('orderModal'));
    myModal.show();

    $.ajax({
        url: 'view_orders.php',
        method: 'POST',
        data: { fetch_order_id: orderId },
        success: function(data) {
            $('#orderDetailsBody').html(data);
        }
    });
}
</script>
</body>
</html>