<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}


// Search & Filter Logic (Optional but helpful)
$role_filter = isset($_GET['role']) ? mysqli_real_escape_string($conn, $_GET['role']) : '';
$query = "SELECT id, name, email, phone, role, status, city, created_at FROM users";
if ($role_filter) {
    $query .= " WHERE role = '$role_filter'";
}
$query .= " ORDER BY created_at DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Manage Users | PakBazar Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f4f7fe;
            font-family: 'Inter', sans-serif;
            margin-left: 280px;
            transition: 0.3s;
        }

        .main-content { padding: 2rem; }

        .user-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: none;
            overflow: hidden;
        }

        .table thead {
            background-color: #f8f9fa;
            border-bottom: 2px solid #edf2f7;
        }

        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: #718096;
            padding: 1.2rem;
        }

        .table td {
            vertical-align: middle;
            padding: 1rem 1.2rem;
            color: #4a5568;
        }

        .badge-role {
            padding: 0.5em 1em;
            border-radius: 50px;
            font-weight: 500;
        }

        .role-supplier { background: #e0e7ff; color: #4338ca; }
        .role-customer { background: #fef3c7; color: #92400e; }
        
        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        @media (max-width: 992px) {
            body { margin-left: 0; }
        }
    </style>
</head>
<body>

    <?php include('admin_sidebar.php'); ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">User Directory</h2>
                <p class="text-muted">Manage all registered Suppliers and Customers</p>
            </div>
            <div class="btn-group">
                <a href="view_users.php" class="btn btn-outline-secondary btn-sm <?= !$role_filter ? 'active' : '' ?>">All</a>
                <a href="view_users.php?role=supplier" class="btn btn-outline-secondary btn-sm <?= $role_filter == 'supplier' ? 'active' : '' ?>">Suppliers</a>
                <a href="view_users.php?role=customer" class="btn btn-outline-secondary btn-sm <?= $role_filter == 'customer' ? 'active' : '' ?>">Customers</a>
            </div>
        </div>

        <div class="card user-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>User Info</th>
                            <th>Contact</th>
                            <th>Location</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3 bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="fas fa-user text-secondary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($row['name']) ?></div>
                                                <div class="small text-muted"><?= htmlspecialchars($row['email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="small"><i class="fas fa-phone me-1 text-muted"></i> <?= $row['phone'] ?: 'N/A' ?></span>
                                    </td>
                                    <td>
                                        <span class="small"><i class="fas fa-map-marker-alt me-1 text-muted"></i> <?= $row['city'] ?: 'N/A' ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-role <?= $row['role'] == 'supplier' ? 'role-supplier' : 'role-customer' ?>">
                                            <?= ucfirst($row['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                            $status_class = [
                                                'active' => 'bg-success',
                                                'inactive' => 'bg-warning',
                                                'banned' => 'bg-danger'
                                            ];
                                        ?>
                                        <span class="status-indicator <?= $status_class[$row['status']] ?>"></span>
                                        <span class="small text-capitalize"><?= $row['status'] ?></span>
                                    </td>
                                    <td class="small">
                                        <?= date('M d, Y', strtotime($row['created_at'])) ?>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="edit_user.php?id=<?= $row['id'] ?>"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                                <li><a class="dropdown-item text-danger" href="delete_user.php?id=<?= $row['id'] ?>" onclick="return confirm('Ban this user?')"><i class="fas fa-ban me-2"></i>Ban User</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No users found in the database.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>