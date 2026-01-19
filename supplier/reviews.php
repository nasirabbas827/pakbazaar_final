<?php
include('../config.php');
session_start();

// Security: only logged-in supplier
if (!isset($_SESSION["logged_in"]) || $_SESSION["role"] !== "supplier") {
    header("Location: ../login.php");
    exit;
}

$supplier_id = $_SESSION["user_id"];

// ────────────────────────────────────────────────
// Filters
// ────────────────────────────────────────────────
$rating_filter = $_GET['rating'] ?? 'all';
$start_date    = $_GET['start_date'] ?? '';
$end_date      = $_GET['end_date']   ?? '';

// Build WHERE clause
$where = ["p.supplier_id = ?"];
$params = [$supplier_id];
$types  = "i";

if ($rating_filter !== 'all' && in_array($rating_filter, ['1','2','3','4','5'])) {
    $where[] = "r.rating = ?";
    $params[] = $rating_filter;
    $types .= "i";
}

if ($start_date) {
    $where[] = "DATE(r.created_at) >= ?";
    $params[] = $start_date;
    $types .= "s";
}

if ($end_date) {
    $where[] = "DATE(r.created_at) <= ?";
    $params[] = $end_date;
    $types .= "s";
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// ────────────────────────────────────────────────
// Main query - all reviews on supplier's products
// ────────────────────────────────────────────────
$sql = "
    SELECT 
        r.id,
        r.rating,
        r.comment,
        r.created_at,
        p.name AS product_name,
        p.id AS product_id,
        u.name AS customer_name,
        u.email AS customer_email
    FROM reviews r
    INNER JOIN products p ON r.product_id = p.id
    INNER JOIN users u ON r.customer_id = u.id
    $where_clause
    ORDER BY r.created_at DESC
";

$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $reviews = $stmt->get_result();
} else {
    die("Query preparation failed: " . $conn->error);
}

// ────────────────────────────────────────────────
// Statistics for summary cards
// ────────────────────────────────────────────────
$stats_query = "
    SELECT 
        COUNT(*) AS total_reviews,
        AVG(rating) AS avg_rating,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) AS five_star,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) AS four_star,
        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) AS three_star,
        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) AS two_star,
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) AS one_star
    FROM reviews r
    INNER JOIN products p ON r.product_id = p.id
    WHERE p.supplier_id = ?
";

$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("i", $supplier_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
$stats_stmt->close();

$avg = $stats['avg_rating'] ? round($stats['avg_rating'], 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>My Product Reviews - Supplier Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fc; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); }
        .star-gold { color: #f59e0b; }
        .review-card {
            border-left: 4px solid #6366f1;
            background: #f8f9fa;
            border-radius: 0 8px 8px 0;
        }
        .rating-badge {
            font-size: 1.1rem;
            min-width: 2.4rem;
            text-align: center;
        }
        .empty-state {
            min-height: 50vh;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #6b7280;
        }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<?php include('sidebar.php'); // ← your supplier sidebar ?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h3 class="fw-bold mb-1">Product Reviews</h3>
            <p class="text-muted mb-0">All customer reviews on your products</p>
        </div>
        <button onclick="window.print()" class="btn btn-outline-secondary px-4 no-print">
            <i class="fas fa-print me-2"></i> Print Reviews
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4 no-print">
        <div class="col-md-3 col-sm-6">
            <div class="card text-center p-3">
                <h6 class="text-muted small fw-bold mb-1">TOTAL REVIEWS</h6>
                <h3 class="fw-bold"><?= number_format($stats['total_reviews']) ?></h3>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card text-center p-3">
                <h6 class="text-muted small fw-bold mb-1">AVERAGE RATING</h6>
                <h3 class="fw-bold">
                    <?= $avg ?> <span class="star-gold">★</span>
                </h3>
            </div>
        </div>
        <div class="col-md-6 col-sm-12">
            <div class="card p-3">
                <h6 class="text-muted small fw-bold mb-2 text-center">RATING DISTRIBUTION</h6>
                <div class="d-flex justify-content-between align-items-center small">
                    <div>5 ★</div>
                    <div class="progress flex-grow-1 mx-2" style="height:10px;">
                        <div class="progress-bar bg-warning" style="width: <?= $stats['total_reviews'] ? ($stats['five_star'] / $stats['total_reviews']) * 100 : 0 ?>%"></div>
                    </div>
                    <div><?= $stats['five_star'] ?></div>
                </div>
                <div class="d-flex justify-content-between align-items-center small mt-1">
                    <div>4 ★</div>
                    <div class="progress flex-grow-1 mx-2" style="height:10px;">
                        <div class="progress-bar bg-success" style="width: <?= $stats['total_reviews'] ? ($stats['four_star'] / $stats['total_reviews']) * 100 : 0 ?>%"></div>
                    </div>
                    <div><?= $stats['four_star'] ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4 no-print">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-bold small text-muted">Minimum Rating</label>
                    <select name="rating" class="form-select">
                        <option value="all">All Ratings</option>
                        <option value="5" <?= $rating_filter==='5'?'selected':'' ?>>5 Stars</option>
                        <option value="4" <?= $rating_filter==='4'?'selected':'' ?>>4+ Stars</option>
                        <option value="3" <?= $rating_filter==='3'?'selected':'' ?>>3+ Stars</option>
                        <option value="2" <?= $rating_filter==='2'?'selected':'' ?>>2+ Stars</option>
                        <option value="1" <?= $rating_filter==='1'?'selected':'' ?>>1+ Star</option>
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
                        <i class="fas fa-filter me-2"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reviews List -->
    <?php if ($reviews->num_rows === 0): ?>
        <div class="empty-state">
            <i class="far fa-comment-dots fa-4x mb-3 opacity-50"></i>
            <h5 class="text-muted">No reviews found</h5>
            <p class="text-muted">No customer reviews match your current filters.</p>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php while ($review = $reviews->fetch_assoc()): ?>
                <div class="col-12">
                    <div class="review-card p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1 fw-bold"><?= htmlspecialchars($review['product_name']) ?></h6>
                                <small class="text-muted">
                                    by <?= htmlspecialchars($review['customer_name']) ?> • 
                                    <?= date('d M Y', strtotime($review['created_at'])) ?>
                                </small>
                            </div>
                            <span class="badge bg-dark rating-badge">
                                <?= $review['rating'] ?> <i class="fas fa-star star-gold"></i>
                            </span>
                        </div>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($review['comment'] ?: 'No comment provided.')) ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>