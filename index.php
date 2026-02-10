<?php
include('config.php');
session_start();

// Fetch 4 featured products for the landing page
$featured_query = "SELECT p.*, c.name as cat_name FROM products p 
                   JOIN categories c ON p.category_id = c.id 
                   ORDER BY RAND() LIMIT 4";
$featured_result = $conn->query($featured_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>PakBazar | Best Online Marketplace in Pakistan</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --pb-indigo: #4f46e5;
            --pb-dark: #0f172a;
            --pb-light: #f8fafc;
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--pb-light); color: #334155; }

        /* Top Contact Bar */
        .top-bar {
            background-color: var(--pb-dark);
            color: #94a3b8;
            padding: 8px 0;
            font-size: 0.85rem;
        }
        .top-bar a { color: #f8fafc; text-decoration: none; transition: 0.3s; }
        .top-bar a:hover { color: var(--pb-indigo); }

        /* Navbar Styling */
        .navbar {
            background: white !important;
            padding: 15px 0;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        .navbar-brand img { height: 40px; border-radius: 8px; margin-right: 10px; }
        .navbar-brand span { font-weight: 800; font-size: 1.4rem; color: var(--pb-dark); }
        .nav-link { font-weight: 600; color: var(--pb-dark) !important; margin: 0 10px; }
        .nav-link:hover { color: var(--pb-indigo) !important; }

        /* Hero Section */
        .hero {
            padding: 100px 0 160px;
            background: linear-gradient(135deg, var(--pb-indigo) 0%, #7c3aed 100%);
            color: white;
            text-align: center;
            clip-path: polygon(0 0, 100% 0, 100% 90%, 0% 100%);
        }
        .hero h1 { font-weight: 800; font-size: 4rem; letter-spacing: -2px; margin-bottom: 20px; }
        .hero p { font-size: 1.25rem; opacity: 0.9; max-width: 600px; margin: 0 auto 40px; }

        /* Product Cards */
        .section-title { font-weight: 800; margin-bottom: 40px; position: relative; display: inline-block; }
        .section-title::after { content: ''; width: 50px; height: 4px; background: var(--pb-indigo); position: absolute; bottom: -10px; left: 0; border-radius: 2px; }

        .product-card {
            background: white;
            border-radius: 20px;
            border: none;
            overflow: hidden;
            transition: 0.3s;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            height: 100%;
        }
        .product-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .product-card img { height: 200px; object-fit: cover; }
        .price-tag { color: var(--pb-indigo); font-weight: 800; font-size: 1.2rem; }

        /* Action Buttons */
        .btn-pb { padding: 12px 30px; border-radius: 12px; font-weight: 700; transition: 0.3s; text-transform: uppercase; letter-spacing: 1px; }
        .btn-pb-white { background: white; color: var(--pb-indigo); border: none; }
        .btn-pb-outline { background: transparent; color: white; border: 2px solid rgba(255,255,255,0.4); }
        .btn-pb:hover { transform: scale(1.05); filter: brightness(1.1); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }

        footer { background: var(--pb-dark); color: #94a3b8; padding: 60px 0 30px; }
        .footer-logo { font-weight: 800; color: white; font-size: 1.5rem; margin-bottom: 20px; display: block; }
    </style>
</head>
<body>

    <div class="top-bar d-none d-md-block">
        <div class="container d-flex justify-content-between">
            <div>
                <span class="me-3"><i class="fas fa-envelope me-1"></i> support@pakbazar.pk</span>
                <span><i class="fas fa-phone-alt me-1"></i> +92 300 1234567</span>
            </div>
            <div>
                <a href="#" class="me-3"><i class="fab fa-facebook"></i></a>
                <a href="#" class="me-3"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>
    </div>

<?php include 'navbar.php' ?>


    <header class="hero">
        <div class="container">
            <h1 class="display-3">Your Local Marketplace.</h1>
            <p>Experience the finest shopping and supply chain management platform in Pakistan. Fast, secure, and reliable.</p>
            <div class="d-flex flex-column flex-md-row justify-content-center gap-3">
                <a href="login.php" class="btn btn-pb btn-pb-white"><i class="fas fa-shopping-bag me-2"></i>Start Shopping</a>
                <a href="login.php" class="btn btn-pb btn-pb-outline"><i class="fas fa-store me-2"></i>Sell on PakBazar</a>
            </div>
            <div class="mt-4">
                <a href="admin/admin_login.php" class="text-white opacity-75 text-decoration-none small">
                    <i class="fas fa-user-shield me-1"></i> Admin & Staff Login
                </a>
            </div>
        </div>
    </header>

    <section class="container my-5 py-5">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h2 class="section-title">Featured Products</h2>
                <p class="text-muted">Handpicked top-quality items just for you.</p>
            </div>
            <a href="login.php" class="btn btn-link text-indigo fw-bold text-decoration-none">View All <i class="fas fa-arrow-right ms-1"></i></a>
        </div>

        <div class="row g-4">
            <?php while($product = $featured_result->fetch_assoc()): ?>
            <div class="col-6 col-md-3">
                <div class="product-card">
                    <img src="uploads/<?= $product['image'] ? $product['image'] : 'default.jpg' ?>" class="card-img-top" alt="<?= $product['name'] ?>">
                    <div class="p-3">
                        <small class="text-primary fw-bold text-uppercase" style="font-size: 0.7rem;"><?= $product['cat_name'] ?></small>
                        <h6 class="fw-bold mt-1 text-truncate"><?= $product['name'] ?></h6>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="price-tag">Rs <?= number_format($product['price']) ?></span>
                            <a href="login.php" class="btn btn-outline-primary btn-sm rounded-circle"><i class="fas fa-plus"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <section id="features" class="py-5" style="background: #edf2f7;">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="p-4">
                        <i class="fas fa-shipping-fast fa-3x text-primary mb-3"></i>
                        <h4 class="fw-bold">Fast Delivery</h4>
                        <p class="text-muted">Quick shipping across Jampur and surrounding regions.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4">
                        <i class="fas fa-user-check fa-3x text-success mb-3"></i>
                        <h4 class="fw-bold">Verified Suppliers</h4>
                        <p class="text-muted">Every supplier on PakBazar is vetted for quality and trust.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4">
                        <i class="fas fa-headset fa-3x text-warning mb-3"></i>
                        <h4 class="fw-bold">24/7 Support</h4>
                        <p class="text-muted">We are here to help you with your orders anytime, anywhere.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="row g-4 border-bottom border-secondary pb-5">
                <div class="col-md-4">
                    <span class="footer-logo">PakBazar</span>
                    <p>PakBazar is a modern e-commerce solution bridging the gap between local suppliers and quality-seeking customers.</p>
                </div>
                <div class="col-md-4">
                    <h5 class="text-white fw-bold mb-4">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="login.php" class="text-decoration-none text-muted d-block mb-2">Customer Login</a></li>
                        <li><a href="login.php" class="text-decoration-none text-muted d-block mb-2">Supplier Portal</a></li>
                        <li><a href="admin/admin_login.php" class="text-decoration-none text-muted d-block mb-2">Admin Dashboard</a></li>
                    </ul>
                </div>
                <div class="col-md-4 text-md-end">
                    <h5 class="text-white fw-bold mb-4">Contact Us</h5>
                    <p class="mb-1 text-white">Main Bazaar, Jampur</p>
                    <p class="mb-1">Punjab, Pakistan</p>
                    <div class="mt-4">
                        <a href="#" class="btn btn-outline-light btn-sm me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
            <div class="pt-4 text-center">
                <p class="small mb-0">© 2026 PakBazar Commerce Hub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>