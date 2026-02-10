<style>
    :root {
        --pb-indigo: #4f46e5;
        --pb-dark: #0f172a;
        --pb-bg: #f8fafc;
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--pb-bg);
        padding-top: 75px; /* Space for fixed navbar */
    }

    .navbar-customer {
        background: white;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.7rem 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }

    .navbar-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 800;
        font-size: 1.4rem;
        color: var(--pb-indigo) !important;
        letter-spacing: -0.5px;
    }

    .navbar-brand img {
        height: 35px;
        width: auto;
        border-radius: 6px;
    }

    /* Central Search Bar */
    .nav-search-container {
        position: relative;
        width: 100%;
        max-width: 400px;
        margin: 0 2rem;
    }

    .nav-search-container input {
        background: #f1f5f9;
        border: 1px solid transparent;
        border-radius: 12px;
        padding: 0.6rem 1rem 0.6rem 2.8rem;
        font-size: 0.9rem;
        width: 100%;
        transition: all 0.3s ease;
    }

    .nav-search-container i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .nav-search-container input:focus {
        background: white;
        border-color: var(--pb-indigo);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        outline: none;
    }

    /* Nav Links Styling */
    .nav-link-custom {
        color: #475569 !important;
        font-weight: 600;
        font-size: 0.95rem;
        padding: 0.5rem 1rem !important;
        border-radius: 10px;
        transition: 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .nav-link-custom:hover {
        color: var(--pb-indigo) !important;
        background: #f5f7ff;
    }

    .nav-link-custom.active {
        color: var(--pb-indigo) !important;
        background: #eef2ff;
    }

    .logout-link {
        color: #ef4444 !important;
        border: 1px solid #fee2e2;
        background: #fef2f2;
    }

    .logout-link:hover {
        background: #fee2e2;
    }

    @media (max-width: 991px) {
        .nav-search-container { margin: 1rem 0; max-width: 100%; }
        .navbar-collapse { background: white; padding: 1rem; border-radius: 15px; margin-top: 10px; border: 1px solid #eee; }
    }
</style>

<nav class="navbar navbar-expand-lg navbar-customer fixed-top">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <img src="../Logo.jpg" alt="PakBazar Logo">
            <span>PakBazar</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#customerNav">
            <i class="fas fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse" id="customerNav">
            <form class="nav-search-container mx-auto" action="dashboard.php" method="GET">
                <i class="fas fa-search"></i>
                <input type="text" id="pSearch" placeholder="Search products in PakBazar..." onkeyup="filterProducts()">
            </form>

            <ul class="navbar-nav align-items-center gap-1">
                <li class="nav-item">
                    <a class="nav-link-custom <?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : '' ?>" href="dashboard.php">
                        <i class="fas fa-th-large"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link-custom <?= (basename($_SERVER['PHP_SELF']) == 'wishlist.php') ? 'active' : '' ?>" href="wishlist.php">
                        <i class="fas fa-heart"></i> Wishlist
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link-custom <?= (basename($_SERVER['PHP_SELF']) == 'my_orders.php') ? 'active' : '' ?>" href="my_orders.php">
                        <i class="fas fa-shopping-bag"></i> Orders
                    </a>
                </li>
                <li class="nav-item ms-lg-2">
                    <a class="nav-link-custom logout-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>