<?php
include('config.php');

$name = $email = $password = $confirm_password = $phone = $address = $city = $role = "";
$name_err = $email_err = $password_err = $confirm_password_err = $phone_err = $role_err = "";
$registration_msg = "";

function isValidName($name) {
    return preg_match('/^[a-zA-Z\s\-_]+$/', $name);
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) && str_ends_with($email, '@gmail.com');
}

function isValidPassword($password) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Name
    if (empty(trim($_POST["name"])) || !isValidName($_POST["name"])) {
        $name_err = "Only letters, spaces, underscores and dashes allowed.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Email
    if (empty(trim($_POST["email"])) || !isValidEmail($_POST["email"])) {
        $email_err = "Please enter a valid Gmail address (must end with @gmail.com).";
    } else {
        $email = trim($_POST["email"]);
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $email_err = "This email is already registered.";
        }
        mysqli_stmt_close($stmt);
    }

    // Phone (optional but validated if provided)
    $phone = trim($_POST["phone"] ?? "");
    if (!empty($phone) && !preg_match('/^\+?[1-9]\d{1,14}$/', $phone)) {
        $phone_err = "Invalid phone number format.";
    }

    // Address & City (optional)
    $address = trim($_POST["address"] ?? "");
    $city     = trim($_POST["city"] ?? "");

    // Role (required)
    if (empty($_POST["role"]) || !in_array($_POST["role"], ['supplier','customer'])) {
        $role_err = "Please select your role.";
    } else {
        $role = $_POST["role"];
    }

    // Password
    if (empty(trim($_POST["password"])) || !isValidPassword($_POST["password"])) {
        $password_err = "Password must be 8+ chars with uppercase, lowercase, number & special character.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Confirm Password
    $confirm_password = trim($_POST["confirm_password"] ?? "");
    if (empty($confirm_password)) {
        $confirm_password_err = "Please confirm your password.";
    } elseif ($password !== $confirm_password) {
        $confirm_password_err = "Passwords do not match.";
    }

    // If no errors → register
    if (empty($name_err) && empty($email_err) && empty($password_err) && 
        empty($confirm_password_err) && empty($phone_err) && empty($role_err)) {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (name, email, password, phone, address, city, role, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'active')";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssss", $name, $email, $hashed_password, $phone, $address, $city, $role);

        if (mysqli_stmt_execute($stmt)) {
            $registration_msg = '<div class="alert alert-success text-center">Registration successful! You can now <a href="login.php">login</a>.</div>';
        } else {
            $registration_msg = '<div class="alert alert-danger text-center">Error: ' . mysqli_error($conn) . '</div>';
        }

        mysqli_stmt_close($stmt);
    }

    // Optional: close connection here or at end of script
    // mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Online PakBazar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/css/intlTelInput.css"/>

    <style>
        body {
            background: linear-gradient(135deg, #e6f0ff, #d6e4ff);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .register-card {
            max-width: 580px;
            margin: 3rem auto;
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }
        .card-header {
            background: #0d6efd;
            color: white;
            text-align: center;
            padding: 1.5rem;
            font-size: 1.5rem;
            font-weight: 600;
        }
        .form-control, .form-select {
            border-radius: 8px;
        }
        .input-group-text {
            border-radius: 8px 0 0 8px;
            background-color: #f8f9fa;
        }
        .btn-register {
            padding: 0.75rem;
            font-size: 1.1rem;
            border-radius: 8px;
        }
        .password-meter {
            height: 6px;
            border-radius: 3px;
            margin-top: 4px;
            transition: width 0.3s;
        }
        .weak    { background: #dc3545; width: 30%; }
        .medium  { background: #ffc107; width: 60%; }
        .strong  { background: #198754; width: 100%; }
    </style>
</head>
<body>
<?php include 'navbar.php' ?>

<div class="container">
    <div class="card register-card">
        <div class="card-header">
            Create Your Account
        </div>
        <div class="card-body p-4 p-md-5">

            <?php if($registration_msg) echo $registration_msg; ?>

            <form method="post" class="row g-3" novalidate>

                <!-- Full Name -->
                <div class="col-md-6">
                    <label class="form-label">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="name" class="form-control <?php echo $name_err?'is-invalid':''; ?>" 
                               value="<?=htmlspecialchars($name)?>" required>
                    </div>
                    <div class="invalid-feedback"><?= $name_err ?></div>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                    <label class="form-label">Email (must be Gmail)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control <?php echo $email_err?'is-invalid':''; ?>" 
                               value="<?=htmlspecialchars($email)?>" required>
                    </div>
                    <div class="invalid-feedback"><?= $email_err ?></div>
                </div>

                <!-- Phone -->
                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control <?php echo $phone_err?'is-invalid':''; ?>" 
                           value="<?=htmlspecialchars($phone)?>" placeholder="+92 300 1234567">
                    <div class="invalid-feedback"><?= $phone_err ?></div>
                </div>

                <!-- Role -->
                <div class="col-md-6">
                    <label class="form-label">I want to register as</label>
                    <select name="role" class="form-select <?php echo $role_err?'is-invalid':''; ?>" required>
                        <option value="">Select role...</option>
                        <option value="customer" <?= $role==='customer'?'selected':'' ?>>Customer</option>
                        <option value="supplier"  <?= $role==='supplier'?'selected':'' ?>>Supplier</option>
                    </select>
                    <div class="invalid-feedback"><?= $role_err ?></div>
                </div>

                <!-- City -->
                <div class="col-md-12">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" 
                           value="<?=htmlspecialchars($city)?>" placeholder="e.g. Karachi, Lahore...">
                </div>

                <!-- Address -->
                <div class="col-12">
                    <label class="form-label">Address (optional)</label>
                    <textarea name="address" class="form-control" rows="2" 
                              placeholder="House #, Street, Area..."><?=htmlspecialchars($address)?></textarea>
                </div>

                <!-- Password -->
                <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password" name="password" 
                               class="form-control <?php echo $password_err?'is-invalid':''; ?>" required>
                    </div>
                    <div class="password-meter" id="meter"></div>
                    <div class="invalid-feedback d-block"><?= $password_err ?></div>
                    <small class="text-muted">8+ chars with upper, lower, number & symbol</small>
                </div>

                <!-- Confirm Password -->
                <div class="col-md-6">
                    <label class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="confirm_password" class="form-control <?php echo $confirm_password_err?'is-invalid':''; ?>" required>
                    </div>
                    <div class="invalid-feedback"><?= $confirm_password_err ?></div>
                </div>

                <!-- Submit -->
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg w-100 btn-register">
                        <i class="fas fa-user-plus me-2"></i> Register
                    </button>
                </div>

                <div class="col-12 text-center mt-3">
                    Already have an account? <a href="login.php" class="text-primary fw-bold">Login here</a>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Optional: password strength meter -->
<script>
document.getElementById('password')?.addEventListener('input', function() {
    const pass = this.value;
    const meter = document.getElementById('meter');
    let strength = 0;
    
    if (pass.length >= 8) strength++;
    if (/[a-z]/.test(pass)) strength++;
    if (/[A-Z]/.test(pass)) strength++;
    if (/\d/.test(pass)) strength++;
    if (/[\W_]/.test(pass)) strength++;
    
    meter.className = 'password-meter';
    if (strength <= 2) meter.classList.add('weak');
    else if (strength <= 4) meter.classList.add('medium');
    else meter.classList.add('strong');
});
</script>
</body>
</html>