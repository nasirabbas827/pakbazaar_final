<?php
include('../config.php');
session_start();

// 1. Access Control
if (!isset($_SESSION["logged_in"]) || $_SESSION["role"] !== "supplier") {
    header("Location: ../login.php");
    exit;
}

$supplier_id = $_SESSION["user_id"];

// 2. Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND supplier_id = ?");
        $stmt->bind_param("ii", $id, $supplier_id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Product deleted!']);
        }
        exit;
    }

    if ($action === 'upsert') {
        $id = $_POST['id'] ?? null;
        $name = $_POST['name'];
        $category_id = $_POST['category_id'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $threshold = $_POST['low_stock_threshold'];
        $description = $_POST['description'];
        
        $image_name = $_POST['existing_image'] ?? '';
        if (!empty($_FILES['image']['name'])) {
            $target_dir = "../uploads/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $image_name = time() . "_" . basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image_name);
        }

if ($id) {

            // Update

            $stmt = $conn->prepare("UPDATE products SET name=?, category_id=?, price=?, stock=?, low_stock_threshold=?, description=?, image=? WHERE id=? AND supplier_id=?");

            $stmt->bind_param("sssssssss", $name, $category_id, $price, $stock, $threshold, $description, $image_name, $id, $supplier_id);

        } else {

            // Insert

            $stmt = $conn->prepare("INSERT INTO products (supplier_id, category_id, name, description, price, stock, low_stock_threshold, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->bind_param("ssssssss", $supplier_id, $category_id, $name, $description, $price, $stock, $threshold, $image_name);

        }

        if ($stmt->execute()) {
            $_SESSION['msg'] = "Product saved successfully!";
            header("Location: products.php");
            exit;
        }
    }
}

// 3. Fetch Data
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.supplier_id = ? ORDER BY p.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $supplier_id);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 4. Calculate Alerts
$low_stock_items = 0;
$out_of_stock_items = 0;
foreach($products as $p) {
    if($p['stock'] <= 0) $out_of_stock_items++;
    elseif($p['stock'] <= $p['low_stock_threshold']) $low_stock_items++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Product Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        body { background-color: #f4f7f6; }
        .product-img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
        .stock-badge { min-width: 90px; }
        .alert-summary { cursor: pointer; transition: 0.3s; }
        .alert-summary:hover { opacity: 0.8; }
    </style>
</head>
<body>

<?php include('sidebar.php'); ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4"><i class="fas fa-boxes me-2 text-primary"></i>Inventory Manager</h2>
        <button class="btn btn-primary" onclick="openModal()">
            <i class="fas fa-plus me-1"></i> Add Product
        </button>
    </div>

    <div class="row mb-4">
        <?php if($out_of_stock_items > 0): ?>
        <div class="col-md-6 mb-2">
            <div class="alert alert-danger d-flex align-items-center mb-0 shadow-sm alert-summary" onclick="document.getElementById('stockFilter').value='out'; runFilters();">
                <i class="fas fa-times-circle fs-4 me-3"></i>
                <div>
                    <strong>Critical:</strong> You have <b><?= $out_of_stock_items ?></b> products out of stock!
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if($low_stock_items > 0): ?>
        <div class="col-md-6 mb-2">
            <div class="alert alert-warning d-flex align-items-center mb-0 shadow-sm alert-summary" onclick="document.getElementById('stockFilter').value='low'; runFilters();">
                <i class="fas fa-exclamation-triangle fs-4 me-3"></i>
                <div>
                    <strong>Warning:</strong> <b><?= $low_stock_items ?></b> products are reaching low stock limits.
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if(isset($_SESSION['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= $_SESSION['msg']; unset($_SESSION['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="small text-muted fw-bold">Search Name</label>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by product name..." onkeyup="runFilters()">
                </div>
                <div class="col-md-4">
                    <label class="small text-muted fw-bold">Filter Category</label>
                    <select id="catFilter" class="form-select" onchange="runFilters()">
                        <option value="all">All Categories</option>
                        <?php foreach($categories as $c): ?>
                            <option value="<?= htmlspecialchars($c['name']) ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small text-muted fw-bold">Stock Status</label>
                    <select id="stockFilter" class="form-select" onchange="runFilters()">
                        <option value="all">Show All</option>
                        <option value="low">Low Stock Only</option>
                        <option value="out">Out of Stock Only</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 80px;">Image</th>
                        <th>Product Details</th>
                        <th>Price</th>
                        <th>Stock Level</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="productTable">
                    <?php foreach($products as $p): 
                        $is_out = ($p['stock'] <= 0);
                        $is_low = (!$is_out && $p['stock'] <= $p['low_stock_threshold']);
                        $status = $is_out ? 'out' : ($is_low ? 'low' : 'normal');
                    ?>
                        <tr data-cat="<?= htmlspecialchars($p['category_name']) ?>" data-stock-status="<?= $status ?>">
                            <td><img src="../uploads/<?= $p['image'] ?: 'default.png' ?>" class="product-img"></td>
                            <td>
                                <div class="fw-bold product-name"><?= htmlspecialchars($p['name']) ?></div>
                                <span class="badge bg-light text-dark border small"><?= htmlspecialchars($p['category_name']) ?></span>
                            </td>
                            <td class="fw-bold text-primary">RS <?= number_format($p['price'], 2) ?></td>
                            <td>
                                <?php if($is_out): ?>
                                    <span class="badge bg-danger stock-badge p-2"><i class="fas fa-times me-1"></i> Out of Stock</span>
                                <?php elseif($is_low): ?>
                                    <span class="badge bg-warning text-dark stock-badge p-2"><i class="fas fa-exclamation me-1"></i> <?= $p['stock'] ?> (Low)</span>
                                <?php else: ?>
                                    <span class="badge bg-success stock-badge p-2"><i class="fas fa-check me-1"></i> <?= $p['stock'] ?> In Stock</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary me-1" onclick='openModal(<?= json_encode($p) ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(<?= $p['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="pModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="products.php" method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="mTitle">Add Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="upsert">
                <input type="hidden" name="id" id="pId">
                <input type="hidden" name="existing_image" id="pOldImg">

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="name" id="pName" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <select name="category_id" id="pCat" class="form-select" required>
                            <?php foreach($categories as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Price (RS)</label>
                        <input type="number" step="0.01" name="price" id="pPrice" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Current Stock</label>
                        <input type="number" name="stock" id="pStock" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Low Stock Alert</label>
                        <input type="number" name="low_stock_threshold" id="pLimit" class="form-control" value="5">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="pDesc" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Product Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const pModal = new bootstrap.Modal(document.getElementById('pModal'));

function openModal(data = null) {
    document.getElementById('mTitle').innerText = data ? 'Edit Product' : 'Add New Product';
    document.getElementById('pId').value = data ? data.id : '';
    document.getElementById('pName').value = data ? data.name : '';
    document.getElementById('pCat').value = data ? data.category_id : '';
    document.getElementById('pPrice').value = data ? data.price : '';
    document.getElementById('pStock').value = data ? data.stock : '';
    document.getElementById('pLimit').value = data ? data.low_stock_threshold : '5';
    document.getElementById('pDesc').value = data ? data.description : '';
    document.getElementById('pOldImg').value = data ? data.image : '';
    pModal.show();
}

function runFilters() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const category = document.getElementById('catFilter').value;
    const stock = document.getElementById('stockFilter').value;
    const rows = document.querySelectorAll('#productTable tr');

    rows.forEach(row => {
        const name = row.querySelector('.product-name').innerText.toLowerCase();
        const rowCat = row.getAttribute('data-cat');
        const rowStock = row.getAttribute('data-stock-status');

        const matchSearch = name.includes(search);
        const matchCat = (category === 'all' || rowCat === category);
        const matchStock = (stock === 'all' || rowStock === stock);

        row.style.display = (matchSearch && matchCat && matchStock) ? "" : "none";
    });
}

function deleteProduct(id) {
    if(!confirm('Permanently delete this product?')) return;
    
    fetch('products.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=delete&id=${id}`
    })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'success') location.reload();
    });
}
</script>
</body>
</html>