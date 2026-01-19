<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}


// 2. Handle Product Deletion
if (isset($_GET['delete_id'])) {
    $del_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    
    // First, get the image path to delete the file from server
    $img_query = mysqli_query($conn, "SELECT image FROM products WHERE id = '$del_id'");
    $img_data = mysqli_fetch_assoc($img_query);
    
    if (mysqli_query($conn, "DELETE FROM products WHERE id = '$del_id'")) {
        if (!empty($img_data['image']) && file_exists("../uploads/" . $img_data['image'])) {
            unlink("../uploads/" . $img_data['image']);
        }
        echo "<script>alert('Product deleted successfully!'); window.location.href='view_products.php';</script>";
    } else {
        echo "<script>alert('Error deleting product.');</script>";
    }
}

// 3. Filtering & Search Logic
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$cat_filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$stock_filter = isset($_GET['stock_status']) ? $_GET['stock_status'] : '';

$query = "SELECT p.*, c.name as cat_name, u.name as supplier_name 
          FROM products p 
          JOIN categories c ON p.category_id = c.id 
          JOIN users u ON p.supplier_id = u.id 
          WHERE (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";

if ($cat_filter) {
    $query .= " AND p.category_id = '$cat_filter'";
}

if ($stock_filter == 'low') {
    $query .= " AND p.stock <= p.low_stock_threshold";
}

$query .= " ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);

// Fetch Categories for dropdown
$categories = mysqli_query($conn, "SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products | PakBazar Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7fe; font-family: 'Inter', sans-serif; margin-left: 280px; transition: 0.3s; }
        .main-content { padding: 2rem; }
        .product-img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
        .card-custom { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .badge-low-stock { background-color: #fff5f5; color: #e53e3e; border: 1px solid #feb2b2; }
        @media (max-width: 992px) { body { margin-left: 0; } }
    </style>
</head>
<body>

<?php include('admin_sidebar.php'); ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-boxes me-2 text-primary"></i>Product Inventory</h2>
        <span class="badge bg-primary px-3 py-2"><?= mysqli_num_rows($result) ?> Items Found</span>
    </div>

    <div class="card card-custom p-3 mb-4">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search product name..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($cat_filter == $cat['id']) ? 'selected' : '' ?>><?= $cat['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="stock_status" class="form-select">
                    <option value="">All Stock Levels</option>
                    <option value="low" <?= ($stock_filter == 'low') ? 'selected' : '' ?>>⚠️ Low Stock Alert</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>

    <div class="card card-custom overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Product</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): 
                            $is_low = ($row['stock'] <= $row['low_stock_threshold']);
                        ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <img src="../uploads/<?= $row['image'] ?: 'default-product.png' ?>" class="product-img me-3">
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($row['name']) ?></div>
                                            <small class="text-muted text-truncate d-inline-block" style="max-width: 150px;"><?= htmlspecialchars($row['description']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= $row['cat_name'] ?></span></td>
                                <td><small><?= htmlspecialchars($row['supplier_name']) ?></small></td>
                                <td class="fw-bold">Rs <?= number_format($row['price']) ?></td>
                                <td>
                                    <?php if($is_low): ?>
                                        <span class="badge badge-low-stock">
                                            <i class="fas fa-exclamation-triangle me-1"></i> <?= $row['stock'] ?> (Low)
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle"><?= $row['stock'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $row['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">No products found matching your criteria.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm("Are you sure you want to permanently delete this product? This action cannot be undone.")) {
        window.location.href = "view_products.php?delete_id=" + id;
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>