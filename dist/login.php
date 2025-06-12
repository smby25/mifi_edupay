<?php
session_start();
include "../conn.php";

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, password, CONCAT(fname, ' ', LEFT(mname, 1), '. ', lname) AS fullname FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $hashed_password, $fullname);
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['fullname'] = $fullname;
            $success = true; // 🔑 set success flag
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
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            background: url('assets/images/bg/background.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.96);
            padding: 2.5rem 2rem;
            border-radius: 1.5rem;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.15);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        .school-logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
            margin-bottom: 1rem;
        }
        .brand-text {
            color: #0d6efd;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .subtext {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 1.2rem;
        }
        .form-floating .form-control {
            border-radius: 0.6rem;
            background: #f1f3f5;
            border: 1px solid #ced4da;
        }
        .form-floating .form-control:focus {
            background: #fff;
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.2);
        }
        .btn-primary {
            background: linear-gradient(to right, #0d6efd, #6f42c1);
            border: none;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 1rem;
        }
        .btn-primary:hover {
            background: linear-gradient(to right, #0b5ed7, #5936a9);
        }
        .alert {
            font-size: 0.9rem;
            border-radius: 0.5rem;
            text-align: left;
        }
        @media (max-width: 480px) {
            .login-card {
                margin: 1rem;
                padding: 2rem 1.2rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-card shadow-lg">
        <img src="assets/images/logo/malindig_logo.png" class="school-logo" alt="School Logo">
        <div class="brand-text" style="font-size: 0.95rem;">MALINDIG INSTITUTE FOUNDATION INC.</div>
        <div class="subtext">The First and Only DE LA SALLE Consultancy School in the Entire Province of Marinduque — Since 1922</div>
        <!-- <h5 class="mb-3">Welcome to School Portal</h5> -->
        <p class="text-muted mb-4">Sign in to continue</p>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off" novalidate>
            <div class="form-floating mb-3">
                <input type="text" name="username" class="form-control" id="username" placeholder="Username" required autofocus>
                <label for="username">Student ID or Username</label>
            </div>

            <div class="form-floating mb-4">
                <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
                <label for="password">Password</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
        </form>

        <small class="text-muted">Need help? Contact your developer.</small>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php if ($success): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Login Successful',
            html: `Welcome, <b><?= htmlspecialchars($_SESSION['fullname'] ?? '') ?></b>!<br><span class="text-muted small">Redirecting to your dashboard...</span>`,
            showConfirmButton: false,
            timer: 2000,
            customClass: {
                popup: 'rounded-4'
            }
        }).then(() => {
            window.location.href = 'index.php';
        });
        setTimeout(() => {
            window.location.href = 'index.php';
        }, 2000);
    </script>
    <?php endif; ?>

<?php if ($success): ?>
<script>
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    successModal.show();
    setTimeout(() => {
        window.location.href = 'index.php';
    }, 2000); // Redirect after 2 seconds
</script>
<?php endif; ?>

</body>


</html>