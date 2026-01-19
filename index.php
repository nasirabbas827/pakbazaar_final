<!DOCTYPE html>
<html lang="en">
<head>
    <title>PakBazar | Your Local Marketplace</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Hero Section */
        .hero {
            text-align: center;
            padding: 6rem 1rem;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #fff;
            clip-path: ellipse(150% 100% at 50% 0%);
        }

        .hero h1 {
            font-weight: 800;
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            margin-bottom: 1rem;
            letter-spacing: -2px;
        }

        .hero p {
            font-size: clamp(1rem, 3vw, 1.25rem);
            opacity: 0.9;
            max-width: 700px;
            margin: 0 auto 2.5rem;
        }

        .btn-main {
            padding: 0.9rem 2.2rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0.5rem;
            display: inline-flex;
            align-items: center;
        }

        .btn-customer {
            background-color: #fff;
            color: #4f46e5;
            border: none;
        }

        .btn-supplier {
            background-color: rgba(255, 255, 255, 0.15);
            color: #fff;
            border: 2px solid rgba(255, 255, 255, 0.4);
        }

        .btn-admin {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            margin-top: 1.5rem;
            display: inline-block;
            transition: color 0.3s;
        }

        .btn-admin:hover {
            color: #fff;
            text-decoration: underline;
        }

        .btn-main:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.2);
            filter: brightness(1.1);
        }

        /* Feature Cards */
        .feature-container {
            margin-top: -5rem;
            padding: 0 1rem 5rem;
        }

        .feature-card {
            background: #fff;
            border: none;
            border-radius: 24px;
            padding: 2.5rem;
            height: 100%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 60px rgba(0,0,0,0.1);
        }

        .icon-circle {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .bg-cust { background: #eef2ff; color: #4f46e5; }
        .bg-supp { background: #fff1f2; color: #e11d48; }
        .bg-anal { background: #f0fdf4; color: #16a34a; }

        .feature-card h5 {
            font-weight: 700;
            margin-bottom: 1.2rem;
            color: #0f172a;
        }

        .feature-card ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .feature-card ul li {
            margin-bottom: 1rem;
            color: #475569;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
        }

        .feature-card ul li i {
            font-size: 1rem;
            margin-right: 12px;
            color: #10b981;
        }

        footer {
            background: #0f172a;
            color: #94a3b8;
            padding: 4rem 1rem 3rem;
            text-align: center;
        }

        .social-links a {
            color: #f8fafc;
            font-size: 1.4rem;
            margin: 0 1rem;
            transition: transform 0.3s;
            display: inline-block;
        }

        .social-links a:hover { transform: scale(1.2); color: #6366f1; }

        @media (max-width: 768px) {
            .hero { padding: 5rem 1rem 7rem; }
            .feature-container { margin-top: -3rem; }
        }
    </style>
</head>
<body>

    <div class="hero">
        <div class="container text-center">
            <h1>PakBazar</h1>
            <p>Empowering local suppliers and providing a premium shopping experience for customers across the region.</p>
            
            <div class="d-flex flex-column flex-md-row justify-content-center align-items-center">
                <a class="btn btn-main btn-customer" href="login.php">
                    <i class="fas fa-shopping-cart me-2"></i> Shop as Customer
                </a>
                <a class="btn btn-main btn-supplier" href="login.php">
                    <i class="fas fa-store-alt me-2"></i> Join as Supplier
                </a>
            </div>
            
            <a href="admin/admin_login.php" class="btn-admin">
                <i class="fas fa-user-shield me-1"></i> Staff & Admin Portal
            </a>
        </div>
    </div>

    <div class="container feature-container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="icon-circle bg-cust">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h5>For Customers</h5>
                    <ul>
                        <li><i class="fas fa-check"></i> Secure Checkout with COD</li>
                        <li><i class="fas fa-check"></i> Advanced Product Search</li>
                        <li><i class="fas fa-check"></i> Quantity & Cart Controls</li>
                        <li><i class="fas fa-check"></i> Track Active Orders</li>
                    </ul>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card">
                    <div class="icon-circle bg-supp">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h5>For Suppliers</h5>
                    <ul>
                        <li><i class="fas fa-check"></i> Interactive Sales Charts</li>
                        <li><i class="fas fa-check"></i> Inventory Stock Alerts</li>
                        <li><i class="fas fa-check"></i> Manage Product Listings</li>
                        <li><i class="fas fa-check"></i> Sales Performance Stats</li>
                    </ul>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card">
                    <div class="icon-circle bg-anal">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h5>Platform Admin</h5>
                    <ul>
                        <li><i class="fas fa-check"></i> User Role Management</li>
                        <li><i class="fas fa-check"></i> Approve Category Additions</li>
                        <li><i class="fas fa-check"></i> System-Wide Reports</li>
                        <li><i class="fas fa-check"></i> Order Oversight</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="social-links mb-4">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
            <p class="mb-1 text-white">© 2026 PakBazar Commerce Hub.</p>
            <p class="small">Connecting Suppliers & Customers | Jampur, Pakistan</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>