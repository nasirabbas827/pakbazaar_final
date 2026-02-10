<style>
    :root {
        --primary-blue: #4f46e5;
        --text-dark: #1e293b;
    }

    .navbar {
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.8rem 0;
    }

    /* Logo & Brand Styling */
    .navbar-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 800;
        font-size: 1.5rem;
        color: var(--primary-blue) !important;
        font-family: 'Inter', sans-serif;
        letter-spacing: -0.5px;
    }

.brand-logo-img {
    height: 70px; /* Adjust this height based on your PNG size */
    width: auto;
    object-fit: contain;
    border-radius: 50%;
}


    /* Search Bar */
    .search-container {
        position: relative;
        width: 100%;
        max-width: 350px;
        margin: 0 2rem;
    }

    .search-container input {
        background: #f1f5f9;
        border: 1px solid transparent;
        border-radius: 20px;
        padding: 0.5rem 1rem 0.5rem 2.5rem;
        font-size: 0.9rem;
        width: 100%;
        transition: all 0.3s ease;
    }

    .search-container i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .search-container input:focus {
        background: #fff;
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        outline: none;
    }

    /* Nav Links */
    .nav-link {
        color: var(--text-dark) !important;
        font-weight: 600;
        font-size: 0.95rem;
        padding: 0.5rem 1.2rem !important;
    }

    .nav-link:hover {
        color: var(--primary-blue) !important;
    }

    .btn-login-outline {
        border: 2px solid var(--primary-blue);
        color: var(--primary-blue) !important;
        border-radius: 10px;
        margin-left: 8px;
        transition: all 0.3s;
    }

    .btn-login-outline:hover {
        background: var(--primary-blue);
        color: white !important;
        transform: translateY(-1px);
    }

    @media (max-width: 991px) {
        .search-container { margin: 1rem 0; max-width: 100%; }
        .navbar-brand { font-size: 1.3rem; }
        .brand-logo-img { height: 35px; }
    }
</style>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="Logo.jpg" alt="PakBazar Logo" class="brand-logo-img">
            <span>PakBazar</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navPakBazar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navPakBazar">
            <form class="search-container me-auto" action="search.php" method="GET">
                <i class="fas fa-search"></i>
                <input type="text" name="query" placeholder="What are you looking for?" required>
            </form>

            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="about.php">About</a>
                </li>
                
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">
                            <i class="fas fa-power-off me-1"></i> Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-login-outline" href="login.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>