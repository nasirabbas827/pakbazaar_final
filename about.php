<!DOCTYPE html>
<html lang="en">
<head>
    <title>About Us | Online PakBazar</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
            color: #334155;
        }

        .about-hero {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 5rem 0;
            margin-bottom: -3rem;
            clip-path: ellipse(150% 100% at 50% 0%);
        }

        .section-card {
            background: #ffffff;
            border: none;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .feature-badge {
            background: #eef2ff;
            color: #4f46e5;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .req-list {
            list-style: none;
            padding: 0;
        }

        .req-list li {
            padding: 0.8rem 0;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: start;
        }

        .req-list li i {
            color: #10b981;
            margin-right: 15px;
            margin-top: 5px;
        }

        .tech-pill {
            display: inline-block;
            padding: 0.4rem 1.2rem;
            background: #f1f5f9;
            border-radius: 8px;
            margin: 0.3rem;
            font-weight: 600;
            font-size: 0.9rem;
            color: #475569;
        }

        footer {
            background: #0f172a;
            color: #94a3b8;
            padding: 3rem 0;
            margin-top: 4rem;
        }
    </style>
</head>
<body>

    <?php include('navbar.php'); ?>

    <div class="about-hero">
        <div class="container text-center">
            <h1 class="fw-800 display-4 mb-3">About Online PakBazar</h1>
            <p class="lead opacity-90 mx-auto" style="max-width: 700px;">
                Empowering local businesses through a sophisticated, secure, and user-friendly digital marketplace.
            </p>
        </div>
    </div>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <div class="section-card">
                    <span class="feature-badge">Overview</span>
                    <h2 class="fw-bold mb-4">Project Abstract</h2>
                    <p class="text-muted leading-relaxed">
                        <strong>Online PakBazar</strong> is a web-based digital marketplace that connects suppliers and customers under the supervision of an admin. The system provides a user-friendly platform where suppliers can upload and manage their products, customers can browse and purchase them, and the admin can manage the overall platform.
                    </p>
                    <p class="text-muted">
                        This application aims to promote local businesses, simplify the buying/selling process, and ensure secure transactions in an online environment. By providing dedicated views for Admins, Suppliers, and Customers, we ensure every user has the tools they need to succeed.
                    </p>
                    
                    <div class="mt-4">
                        <h5 class="fw-bold mb-3">Technologies We Use:</h5>
                        <span class="tech-pill">HTML5 / CSS3</span>
                        <span class="tech-pill">JavaScript (ES6+)</span>
                        <span class="tech-pill">Bootstrap 5</span>
                        <span class="tech-pill">PHP / Laravel</span>
                        <span class="tech-pill">MySQL Database</span>
                    </div>
                </div>

                
                <div class="section-card">
                    <span class="feature-badge">Capabilities</span>
                    <h2 class="fw-bold mb-4">Functional Requirements</h2>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="req-list">
                                <li><i class="fas fa-check-circle"></i> <div><strong>User Management:</strong> Secure registration and profile management for Admins, Suppliers, and Customers.</div></li>
                                <li><i class="fas fa-check-circle"></i> <div><strong>Product Database:</strong> Organized listings with details on Price, Stock, and Descriptions.</div></li>
                                <li><i class="fas fa-check-circle"></i> <div><strong>Smart Categories:</strong> Grocery, Electronics, Home Essentials, and Fashion.</div></li>
                                <li><i class="fas fa-check-circle"></i> <div><strong>Search & Filter:</strong> Find products easily by Name, Category, or specific Attributes.</div></li>
                                <li><i class="fas fa-check-circle"></i> <div><strong>Order Tracking:</strong> Real-time updates from placement to delivery.</div></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="req-list">
                                <li><i class="fas fa-check-circle"></i> <div><strong>Secure Payments:</strong> Integration with reliable options including Cash-on-Delivery.</div></li>
                                <li><i class="fas fa-check-circle"></i> <div><strong>Cart & Wishlist:</strong> Flexible shopping experience for every customer.</div></li>
                                <li><i class="fas fa-check-circle"></i> <div><strong>Ratings & Reviews:</strong> Build trust through community-driven feedback.</div></li>
                                <li><i class="fas fa-check-circle"></i> <div><strong>Analytics:</strong> Detailed performance reports for Suppliers and Admins.</div></li>
                                <li><i class="fas fa-check-circle"></i> <div><strong>Smart Alerts:</strong> Notifications for orders, stock levels, and platform updates.</div></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="text-center py-4">
                    <h3 class="fw-bold">Promoting Local Business, One Click at a Time.</h3>
                    <p class="text-muted">Based in Jampur, Pakistan - Serving the digital needs of our community.</p>
                </div>

            </div>
        </div>
    </div>

    <footer>
        <div class="container text-center">
            <p class="mb-0 text-white">&copy; 2026 Online PakBazar. All Rights Reserved.</p>
            <p class="small">Developed for Modern Commerce | Jampur, Pakistan</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>