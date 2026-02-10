<style>
    /* PakBazar Design System */
    :root {
        --pb-indigo: #4f46e5;
        --pb-dark: #0f172a; 
        --pb-text-muted: #94a3b8;
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
        margin-left: 280px;
    }

    nav.sidebar {
        width: 280px;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        background-color: var(--pb-dark);
        color: white;
        padding: 1.5rem 1.2rem;
        z-index: 1000;
        overflow-y: auto;
        border-right: 1px solid rgba(255,255,255,0.05);
    }

    .sidebar-logo-img {
        height: 35px;
        width: auto;
        margin-right: 12px;
        border-radius: 6px;
        object-fit: cover;
    }

    .brand-name {
        font-weight: 800;
        font-size: 1.25rem;
        letter-spacing: -0.5px;
    }

    .nav-link {
        font-size: 0.95rem;
        color: var(--pb-text-muted) !important;
        padding: 0.8rem 1rem;
        border-radius: 12px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        font-weight: 500;
        margin-bottom: 4px;
    }

    .nav-link i {
        width: 24px;
        font-size: 1.1rem;
    }

    .nav-link:hover {
        background-color: rgba(255,255,255,0.05);
        color: white !important;
    }

    .nav-link.active {
        background-color: var(--pb-indigo) !important;
        color: white !important;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
    }

    .toggle-btn {
        display: none;
        position: fixed;
        top: 1rem;
        left: 1rem;
        z-index: 1100;
        background-color: white;
        color: var(--pb-dark);
        border: 1px solid #e2e8f0;
        padding: 0.5rem 0.8rem;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }

    @media (max-width: 992px) {
        body { margin-left: 0 !important; }
        nav.sidebar { display: none !important; }
        .toggle-btn { display: block; }
    }

    .offcanvas {
        width: 280px;
        background-color: var(--pb-dark);
    }

    hr {
        border-top: 1px solid rgba(255,255,255,0.1);
        margin: 1.5rem 0;
    }
</style>

<button class="btn toggle-btn" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
    <i class="fas fa-bars"></i>
</button>

<nav class="sidebar">
    <a href="dashboard.php" class="d-flex align-items-center mb-4 text-white text-decoration-none px-2">
        <img src="../Logo.jpg" alt="Logo" class="sidebar-logo-img">
        <span class="brand-name">PakBazar Supplier</span>
    </a>
    <hr>

    <?php $page = basename($_SERVER['PHP_SELF']); ?>

    <ul class="nav nav-pills flex-column gap-1">
        <li class="nav-item">
            <a class="nav-link <?= $page=='dashboard.php'?'active':'' ?>" href="dashboard.php">
                <i class="fas fa-chart-line me-2"></i> Dashboard
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?= $page=='supplier_orders.php'?'active':'' ?>" href="supplier_orders.php">
                <i class="fas fa-shopping-bag me-2"></i> Orders
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?= $page=='categories.php'?'active':'' ?>" href="categories.php">
                <i class="fas fa-tags me-2"></i> Categories
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?= $page=='products.php'?'active':'' ?>" href="products.php">
                <i class="fas fa-box-open me-2"></i> Products
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?= $page=='reviews.php'?'active':'' ?>" href="reviews.php">
                <i class="fas fa-star-half-alt me-2"></i> Reviews
            </a>
        </li>

        <hr>

        <li class="nav-item">
            <a class="nav-link text-danger" href="logout.php">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </li>
    </ul>
</nav>

<div class="offcanvas offcanvas-start text-white" id="sidebarOffcanvas">
    <div class="offcanvas-header border-bottom border-secondary">
        <h5 class="offcanvas-title d-flex align-items-center">
            <img src="../Logo.jpg" alt="Logo" class="sidebar-logo-img"> PakBazar
        </h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body">
        <ul class="nav nav-pills flex-column gap-1">
            <li><a class="nav-link <?= $page=='dashboard.php'?'active':'' ?>" href="dashboard.php"><i class="fas fa-chart-line me-2"></i> Dashboard</a></li>
            <li><a class="nav-link <?= $page=='supplier_orders.php'?'active':'' ?>" href="supplier_orders.php"><i class="fas fa-shopping-bag me-2"></i> Orders</a></li>
            <li><a class="nav-link <?= $page=='categories.php'?'active':'' ?>" href="categories.php"><i class="fas fa-tags me-2"></i> Categories</a></li>
            <li><a class="nav-link <?= $page=='products.php'?'active':'' ?>" href="products.php"><i class="fas fa-box-open me-2"></i> Products</a></li>
            <li><a class="nav-link <?= $page=='reviews.php'?'active':'' ?>" href="reviews.php"><i class="fas fa-star-half-alt me-2"></i> Reviews</a></li>
            <hr>
            <li><a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
        </ul>
    </div>
</div>