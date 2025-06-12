<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About the System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #f6f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Blue and Gold Theme Colors */
        :root {
            --primary-blue: #0d47a1;
            --secondary-gold: #ffd700;
            --primary-blue-dark: #08306b;
            --secondary-gold-dark: #c9a100;
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-gold) 100%);
            color: #fff;
            padding: 5rem 1rem;
            text-align: center;
            position: relative;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: linear-gradient(to bottom, rgba(255,255,255,0.05), #f6f7fa);
            z-index: 1;
        }

        .hero-section h1 {
            font-weight: 800;
            font-size: 3rem;
            position: relative;
            z-index: 2;
        }

        .hero-section p {
            font-size: 1.25rem;
            max-width: 700px;
            margin: auto;
            position: relative;
            z-index: 2;
        }

        .content-section {
            padding: 4rem 1rem;
            z-index: 2;
            position: relative;
        }

        .info-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            border: 2px solid var(--secondary-gold);
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(13, 71, 161, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(13, 71, 161, 0.13);
            border-color: var(--secondary-gold-dark);
        }

        .info-card .card-body {
            padding: 2rem;
        }

        .info-card h5 {
            font-weight: 600;
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: var(--primary-blue);
        }

        .info-card ul {
            padding-left: 1.2rem;
        }

        .close-btn {
            margin-top: 3rem;
            text-align: center;
        }

        .btn-modern {
            background: var(--secondary-gold);
            color: var(--primary-blue);
            border: none;
            padding: 0.75rem 2rem;
            font-size: 1rem;
            border-radius: 2rem;
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.18);
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .btn-modern:hover {
            background: var(--secondary-gold-dark);
            color: #fff;
            box-shadow: 0 6px 16px rgba(255, 215, 0, 0.28);
        }

        footer {
            text-align: center;
            padding: 1rem;
            font-size: 0.9rem;
            color: var(--primary-blue-dark);
        }

        a.dev-link {
            color: var(--primary-blue);
            text-decoration: none;
        }

        a.dev-link:hover {
            text-decoration: underline;
            color: var(--secondary-gold-dark);
        }
    </style>
    </style>
</head>
<body>

<section class="hero-section">
    <div class="container">
        <h1><i class="bi bi-info-circle-fill me-2"></i>About the System</h1>
        <p>This platform streamlines operations at Malindig Institute Foundation Inc. through modern, efficient, and secure digital solutions.</p>
    </div>
</section>

<section class="content-section container">
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-bullseye me-2"></i>System Purpose</h5>
                    <p>This system helps manage student records, administrative data, and transactions through a secure and intuitive interface.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-code-slash me-2"></i>Technologies Used</h5>
                    <p>Developed using <strong>PHP</strong>, <strong>MySQL</strong>, and <strong>Bootstrap 5</strong> for responsive, scalable, and high-performance execution.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-ui-checks-grid me-2"></i>System Features</h5>
                    <ul>
                        <li>Student Information Management</li>
                        <li>Payment and Ledger Monitoring</li>
                        <li>Transactions History</li>
                        <li>Reports & Data Analytics</li>
                        <li>Modern Responsive Design</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card info-card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-person-badge me-2"></i>Developer</h5>
                    <p>
                        Created by 
                        <strong>
                            <a href="https://smby25.github.io/MyPortfolioV1/" target="_blank" class="dev-link">
                                Sonny Louise P. Rogelio
                            </a>
                        </strong>, founder of <strong>DevExecute</strong> — a group that builds tech solutions for schools and communities.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Close Button -->
    <div class="close-btn">
        <a href="index.php" class="btn btn-modern"><i class="bi bi-arrow-left"></i> Back to Home</a>
    </div>
</section>

<footer>
    &copy; <?= date("Y") ?> Development Execute. All rights reserved.
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
