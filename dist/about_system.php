<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>About the System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .hero-section {
            background: linear-gradient(to right, #0d6efd, #6f42c1);
            color: white;
            padding: 5rem 1rem;
            text-align: center;
        }

        .hero-section h1 {
            font-weight: 700;
            font-size: 2.5rem;
        }

        .hero-section p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: auto;
        }

        .content-section {
            padding: 3rem 1rem;
        }

        .info-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
        }

        .info-card .card-body {
            padding: 2rem;
        }

        footer {
            text-align: center;
            padding: 1rem;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .close-btn {
            margin-top: 2rem;
            text-align: center;
        }

        .btn-modern {
            background: #0d6efd;
            color: #fff;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            border-radius: 2rem;
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.10);
            transition: background 0.3s, box-shadow 0.3s;
        }

        .btn-modern:hover {
            background:rgb(130, 142, 184);
            box-shadow: 0 4px 16px rgba(111, 66, 193, 0.12);
        }
    </style>
</head>

<body>

    <section class="hero-section">
        <div class="container">
            <h1>About the System</h1>
            <p>This system is designed to streamline and improve the operations of Malindig Institute Foundation Inc.</p>
        </div>
    </section>

    <section class="content-section container">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card info-card">
                    <div class="card-body">
                        <h5 class="card-title">System Purpose</h5>
                        <p class="card-text">This system provides students, faculty, and administrators with an efficient way to manage data, track student records, and simplify administrative tasks in a secure and user-friendly platform.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card info-card">
                    <div class="card-body">
                        <h5 class="card-title">Technologies Used</h5>
                        <p class="card-text">Built with PHP, MySQL, and Bootstrap 5, the system is optimized for performance, security, and scalability.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card info-card">
                    <div class="card-body">
                        <h5 class="card-title">System Features</h5>
                        <ul class="mb-0">
                            <li>Student Information Management</li>
                            <li>Student Payment Management</li>
                            <li>Student Ledger</li>
                            <li>Transactions</li>
                            <li>Reports and Analytics</li>
                            <li>Responsive Interface</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card info-card">
                    <div class="card-body">
                        <h5 class="card-title">Developer Information</h5>
                        <p class="card-text">
                            This system was developed by 
                            <strong>
                                <a href="https://smby25.github.io/MyPortfolioV1/" target="_blank" style="color: inherit; text-decoration: underline;">
                                    Sonny Louise P. Rogelio
                                </a>
                            </strong>, founder of <strong>Development Execute (DevExecute)</strong>, a tech-driven group dedicated to developing software and websites for schools and communities.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Close Button -->
        <div class="close-btn">
            <a href="index.php" class="btn btn-modern">Close</a>
        </div>
    </section>

    <footer>
        &copy; <?= date("Y") ?> Development Execute. All rights reserved.
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
