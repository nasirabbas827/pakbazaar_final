<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// 2. Handle Review Deletion
if (isset($_GET['delete_id'])) {
    $del_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    
    if (mysqli_query($conn, "DELETE FROM reviews WHERE id = '$del_id'")) {
        echo "<script>alert('Review removed successfully!'); window.location.href='view_reviews.php';</script>";
    } else {
        echo "<script>alert('Error: Could not delete review.');</script>";
    }
}

// 3. Fetch Reviews with Related Data
$query = "SELECT r.*, p.name as product_name, u.name as customer_name 
          FROM reviews r 
          JOIN products p ON r.product_id = p.id 
          JOIN users u ON r.customer_id = u.id 
          ORDER BY r.created_at DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Reviews | PakBazar Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7fe; font-family: 'Inter', sans-serif; margin-left: 280px; }
        .main-content { padding: 2.5rem; }
        .review-card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); background: #fff; }
        .star-rating { color: #ffc107; font-size: 0.85rem; }
        .table thead { background: #f8f9fa; }
        .table th { border: none; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: #6c757d; }
        @media (max-width: 992px) { body { margin-left: 0; } }
    </style>
</head>
<body>

<?php include('admin_sidebar.php'); ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-star-half-alt me-2 text-warning"></i>User Reviews</h2>
        <span class="badge bg-white text-dark shadow-sm border px-3 py-2">Total Reviews: <?= mysqli_num_rows($result) ?></span>
    </div>

    <div class="card review-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Product</th>
                        <th>Customer</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Date</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($row['product_name']) ?></span>
                                </td>
                                <td>
                                    <div class="small fw-bold"><?= htmlspecialchars($row['customer_name']) ?></div>
                                </td>
                                <td>
                                    <div class="star-rating">
                                        <?php 
                                        for($i=1; $i<=5; $i++) {
                                            echo ($i <= $row['rating']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                        }
                                        ?>
                                        <span class="ms-1 text-muted">(<?= $row['rating'] ?>)</span>
                                    </div>
                                </td>
                                <td>
                                    <p class="small text-muted mb-0" style="max-width: 300px; line-height: 1.4;">
                                        <?= htmlspecialchars($row['comment']) ?>
                                    </p>
                                </td>
                                <td>
                                    <small class="text-muted"><?= date('d M Y', strtotime($row['created_at'])) ?></small>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger border-0" onclick="confirmDelete(<?= $row['id'] ?>)">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">No reviews found yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm("Are you sure you want to delete this review? This will remove it from the product page permanently.")) {
        window.location.href = "view_reviews.php?delete_id=" + id;
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>