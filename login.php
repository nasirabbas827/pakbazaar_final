<?php
session_start();
include('config.php');

$email = $password = $role = "";
$email_err = $password_err = $role_err = $login_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate role
    if (empty($_POST["role"]) || !in_array($_POST["role"], ['supplier', 'customer'])) {
        $role_err = "Please select a valid role.";
    } else {
        $role = $_POST["role"];
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Proceed only if no validation errors
    if (empty($email_err) && empty($password_err) && empty($role_err)) {

        $sql = "SELECT id, name, email, password, role, status 
                FROM users 
                WHERE email = ? AND role = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $email, $role);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) === 1) {

            mysqli_stmt_bind_result($stmt, $id, $name, $db_email, $hashed_password, $db_role, $status);
            mysqli_stmt_fetch($stmt);

            // Check account status
            if ($status !== 'active') {
                $login_msg = "Your account is not active. Status: " . htmlspecialchars($status);
            }
            // Verify password
            elseif (password_verify($password, $hashed_password)) {

                // Login successful → set session
                $_SESSION["user_id"]   = $id;
                $_SESSION["name"]      = $name;
                $_SESSION["email"]     = $db_email;
                $_SESSION["role"]      = $db_role;
                $_SESSION["logged_in"] = true;

                // Redirect based on role
                if ($role === 'supplier') {
                    header("Location: supplier/dashboard.php");
                } else {
                    header("Location: customer/dashboard.php");
                }
                exit();
            } 
            else {
                $password_err = "Incorrect password.";
            }
        } 
        else {
            $email_err = "No account found with this email and role.";
        }

        mysqli_stmt_close($stmt);
    }

    // Optional: close connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online PakBazar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #e6f0ff, #d6e4ff);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            max-width: 480px;
            margin: 4rem auto;
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }
        .card-header {
            background: #0d6efd;
            color: white;
            text-align: center;
            padding: 1.75rem;
            font-size: 1.6rem;
            font-weight: 600;
        }
        .form-control, .form-select {
            border-radius: 8px;
        }
        .input-group-text {
            border-radius: 8px 0 0 8px;
            background-color: #f8f9fa;
        }
        .btn-login {
            padding: 0.8rem;
            font-size: 1.1rem;
            border-radius: 8px;
        }
        .alert {
            border-radius: 8px;
        }
        .text-muted-small {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card login-card">
        <div class="card-header">
            Sign In to PakBazar
        </div>
        <div class="card-body p-4 p-md-5">

            <?php if ($login_msg): ?>
                <div class="alert alert-danger text-center"><?= htmlspecialchars($login_msg) ?></div>
            <?php endif; ?>

            <form method="post" class="row g-3">

                <!-- Role -->
                <div class="col-12">
                    <label class="form-label">Login as</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user-shield"></i></span>
                        <select name="role" class="form-select <?php echo $role_err ? 'is-invalid' : ''; ?>" required>
                            <option value="">Select your role</option>
                            <option value="customer" <?= $role === 'customer' ? 'selected' : '' ?>>Customer</option>
                            <option value="supplier"  <?= $role === 'supplier'  ? 'selected' : '' ?>>Supplier (Seller)</option>
                        </select>
                    </div>
                    <div class="invalid-feedback"><?= $role_err ?></div>
                </div>

                <!-- Email -->
                <div class="col-12">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control <?php echo $email_err ? 'is-invalid' : ''; ?>" 
                               value="<?= htmlspecialchars($email) ?>" placeholder="yourname@gmail.com" required>
                    </div>
                    <div class="invalid-feedback"><?= $email_err ?></div>
                </div>

                <!-- Password -->
                <div class="col-12">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control <?php echo $password_err ? 'is-invalid' : ''; ?>" 
                               placeholder="••••••••" required>
                    </div>
                    <div class="invalid-feedback"><?= $password_err ?></div>
                </div>

                <!-- Submit -->
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg w-100 btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i> Sign In
                    </button>
                </div>

                <div class="col-12 text-center mt-3">
                    Don't have an account? 
                    <a href="register.php" class="text-primary fw-bold">Create one now</a>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>