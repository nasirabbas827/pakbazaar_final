<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet" />

  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #e6effc;
      margin-left: 280px;
    }

    nav.sidebar {
      width: 280px;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      background-color: #212529;
      color: white;
      padding: 1rem;
      z-index: 1000;
      overflow-y: auto;
    }

    .nav-link {
      font-size: 0.95rem;
      transition: background-color 0.3s;
      color: white;
      padding: 0.55rem 1rem;
      border-radius: 0.25rem;
    }

    .nav-link:hover,
    .nav-link.active {
      background-color: #0d6efd;
    }

    .toggle-btn {
      display: none;
      position: fixed;
      top: 1rem;
      left: 1rem;
      z-index: 1100;
    }

    @media (max-width: 992px) {
      body {
        margin-left: 0 !important;
      }

      nav.sidebar {
        display: none !important;
      }

      .toggle-btn {
        display: block;
      }
    }

    .offcanvas {
      width: 280px;
      background-color: #212529;
    }

    .offcanvas .nav-link {
      color: white;
    }

    .offcanvas .nav-link:hover,
    .offcanvas .nav-link.active {
      background-color: #0d6efd;
    }
  </style>
</head>
<body>

<!-- Mobile Toggle -->
<button class="btn btn-primary toggle-btn"
        data-bs-toggle="offcanvas"
        data-bs-target="#sidebarOffcanvas">
  <i class="fas fa-bars"></i>
</button>

<!-- Desktop Sidebar -->
<nav class="sidebar">
  <a href="admin_home.php" class="d-flex align-items-center mb-3 text-white text-decoration-none">
    <i class="fas fa-user-shield me-2"></i>
    <span class="fs-4">Admin Panel</span>
  </a>
  <hr>

  <?php $page = basename($_SERVER['PHP_SELF']); ?>

  <ul class="nav nav-pills flex-column gap-1">

    <li class="nav-item">
      <a class="nav-link <?= $page=='admin_home.php'?'active':'' ?>" href="admin_home.php">
        <i class="fas fa-gauge-high me-2"></i> Dashboard
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link <?= $page=='view_orders.php'?'active':'' ?>" href="view_orders.php">
        <i class="fas fa-shopping-cart me-2"></i> Orders
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link <?= $page=='view_categories.php'?'active':'' ?>" href="view_categories.php">
        <i class="fas fa-list me-2"></i> Categories
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link <?= $page=='view_products.php'?'active':'' ?>" href="view_products.php">
        <i class="fas fa-box me-2"></i> Products
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link <?= $page=='view_reviews.php'?'active':'' ?>" href="view_reviews.php">
        <i class="fas fa-star me-2"></i> Reviews
      </a>
    </li>

    <li class="nav-item mt-2">
      <a class="nav-link text-danger" href="logout.php">
        <i class="fas fa-right-from-bracket me-2"></i> Logout
      </a>
    </li>

  </ul>
</nav>

<!-- Mobile Offcanvas Sidebar -->
<div class="offcanvas offcanvas-start text-white" id="sidebarOffcanvas">
  <div class="offcanvas-header">
    <h5><i class="fas fa-user-shield me-2"></i>Admin Panel</h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>

  <div class="offcanvas-body">
    <ul class="nav nav-pills flex-column gap-1">
      <li><a class="nav-link" href="admin_home.php"><i class="fas fa-gauge-high me-2"></i> Dashboard</a></li>
      <li><a class="nav-link" href="view_orders.php"><i class="fas fa-shopping-cart me-2"></i> Orders</a></li>
      <li><a class="nav-link" href="view_categories.php"><i class="fas fa-list me-2"></i> Categories</a></li>
      <li><a class="nav-link" href="view_products.php"><i class="fas fa-box me-2"></i> Products</a></li>
      <li><a class="nav-link" href="view_reviews.php"><i class="fas fa-star me-2"></i> Reviews</a></li>
      <li><a class="nav-link text-danger" href="logout.php"><i class="fas fa-right-from-bracket me-2"></i> Logout</a></li>
    </ul>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
