<?php
session_start();
include "../conn.php";

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, password, CONCAT(fname, ' ', LEFT(mname, 1), '. ', lname) AS fullname FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $hashed_password, $fullname); // <-- Add $fullname here
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['fullname'] = $fullname; // Store full name
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>School Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #e3f2fd, #bbdefb);
        }

        .login-card {
            background-color: #fff;
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }

        .school-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            image-rendering: auto;
        }

        .brand-color {
            color: #0d6efd;
        }

        .form-floating > .form-control {
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="login-card text-center" style="width: 100%; max-width: 420px;">
        <!-- School Branding -->
        <img src="assets/images/logo/malindig_logo.png" class="school-logo mb-3" alt="School Logo">

        <h4 class="brand-color fw-bold">Welcome to School Portal</h4>
        <p class="text-muted mb-4">Sign in to continue</p>

        <?php if ($error): ?>
            <div class="alert alert-danger text-start" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off" novalidate>
            <div class="form-floating mb-3">
                <input type="text" name="username" class="form-control" id="username" placeholder="Username" required>
                <label for="username">Student ID or Username</label>
            </div>

            <div class="form-floating mb-4">
                <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
                <label for="password">Password</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 rounded-pill">Login</button>
        </form>

        <div class="mt-4">
            <small class="text-muted">For assistance, contact your school admin.</small>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
