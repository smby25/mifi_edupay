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
            overflow: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            background: linear-gradient(135deg, #002855, #ffd700);
        }
        .bg-3d {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            width: 100vw; height: 100vh;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }
        .sphere {
            position: absolute;
            border-radius: 50%;
            opacity: 0.35;
            filter: blur(2px);
            animation: float 8s infinite ease-in-out alternate;
        }
        .sphere1 {
            width: 350px; height: 350px;
            background: radial-gradient(circle at 30% 30%, #ffd700 70%, #002855 100%);
            left: -120px; top: -80px;
            animation-delay: 0s;
        }
        .sphere2 {
            width: 220px; height: 220px;
            background: radial-gradient(circle at 70% 70%, #002855 60%, #ffd700 100%);
            right: -80px; bottom: -60px;
            animation-delay: 2s;
        }
        .sphere3 {
            width: 120px; height: 120px;
            background: radial-gradient(circle at 50% 50%, #fff 40%, #002855 100%);
            left: 60vw; top: 70vh;
            opacity: 0.2;
            animation-delay: 1s;
        }
        @keyframes float {
            0% { transform: translateY(0) scale(1);}
            100% { transform: translateY(-40px) scale(1.08);}
        }

        .login-card {
            position: relative;
            z-index: 2;
            background: #ffffffee;
            padding: 2.5rem 2rem;
            border-radius: 1.75rem;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
            max-width: 420px;
            width: 100%;
            text-align: center;
        }
        .school-logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
            margin-bottom: 1rem;
            filter: drop-shadow(0 3px 10px #00285544);
        }
        .brand-text {
            color: #002855;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .subtext {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 1.5rem;
        }
        .form-floating .form-control {
            border-radius: 0.75rem;
            background: #f8f9fa;
            border: 1px solid #ced4da;
        }
        .form-floating .form-control:focus {
            background: #fff;
            border-color: #ffd700;
            box-shadow: 0 0 0 0.18rem rgba(255, 215, 0, 0.35);
        }
        .btn-primary {
            background: linear-gradient(45deg, #002855, #ffd700);
            color: white;
            border: none;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #001c3d, #e6c200);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
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
            .sphere1, .sphere2, .sphere3 {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="bg-3d">
        <div class="sphere sphere1"></div>
        <div class="sphere sphere2"></div>
        <div class="sphere sphere3"></div>
    </div>
    <div class="login-card shadow-lg">
        <img src="assets/images/logo/malindig_logo.png" class="school-logo" alt="School Logo">
        <div class="brand-text">MALINDIG INSTITUTE FOUNDATION INC.</div>
        <div class="subtext">The First and Only DE LA SALLE Consultancy School in Marinduque — Since 1922</div>
        <p class="text-muted mb-4">Sign in to continue</p>

        <?php if (isset($error) && $error): ?>
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