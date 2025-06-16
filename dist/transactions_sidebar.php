<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "../conn.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.css">

    <link rel="stylesheet" href="assets/vendors/iconly/bold.css">

    <link rel="stylesheet" href="assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon">
    <link rel="icon" type="image/png" href="assets/css/img/malindig_logo.png">
</head>
<!-- Bootstrap 5 CSS -->
<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->
<!-- Bootstrap 5 JS Bundle (includes Popper) -->

<!-- Add this in your <head> or before </body> -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<body>
    <div id="app">
        <?php include 'sidebar.php'; ?>
        <?php include 'add_highschool_form.php'; ?>
        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            <div class="row">
                <!-- All your dashboard content here -->
            </div>
            <br>
            <div class="row align-items-center mb-3 mt-2">
                <div class="col">
                    <div class="page-heading">
                        <h3>Transactions</h3>
                    </div>
                </div>

            </div>

            <!-- Datatable -->
            <div class="table-responsive">
                <section class="section">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table1" style="width:100%;">
                                    <thead>
                                        <tr>
                                            <th style="display:none;">ID</th>
                                            <th style="display:none;">Student ID</th>
                                            <th>Student Info</th>
                                            <th>Payment Type</th>
                                            <th>Paid Date &amp; By</th>
                                            <th>Amount</th>
                                            <!-- <th>Action</th> -->
                                        </tr>
                                    </thead>
                                    <tbody class="small-font-table">
                                        <?php
                                        $limit = 10;
                                        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                        $offset = ($page - 1) * $limit;

                                        $query = "SELECT sp.id, s.student_id, s.fname, s.mname, s.lname, s.lrn, s.grade_level, s.section, s.strand, p.payment_type, sp.amount_paid, sp.date_paid, sp.paid_by
              FROM student_payments sp
              JOIN students s ON sp.student_id = s.student_id
              JOIN payments p ON sp.payment_id = p.id
              ORDER BY sp.date_paid DESC
              LIMIT ?, ?";
                                        $stmt = $conn->prepare($query);
                                        $stmt->bind_param("ii", $offset, $limit);
                                        $stmt->execute();
                                        $result = $stmt->get_result();

                                        while ($row = $result->fetch_assoc()) {
                                            $full_name = $row['lname'] . ', ' . $row['fname'] . ' ' . strtoupper(substr($row['mname'], 0, 1)) . '.';
                                            $student_info =
                                                "<strong>" . htmlspecialchars($full_name) . "</strong><br>" .
                                                "LRN: " . htmlspecialchars($row['lrn']) . "<br>" .
                                                "Grade: " . htmlspecialchars($row['grade_level']) . " | " .
                                                "Section: " . htmlspecialchars($row['section']) . "<br>" .
                                                "Strand: " . htmlspecialchars($row['strand']);
                                            $paid_date_by = date('M d, Y h:ia', strtotime($row['date_paid'])) .
                                                '<br><span class="text-muted small">by: ' .
                                                (!empty($row['paid_by']) ? htmlspecialchars($row['paid_by']) : '-') .
                                                '</span>';
                                            echo "<tr>";
                                            echo "<td style='display:none;'>" . htmlspecialchars($row['id']) . "</td>"; // Transaction ID
                                            echo "<td style='display:none;'>" . htmlspecialchars($row['student_id']) . "</td>"; // Student ID
                                            echo "<td>" . $student_info . "</td>";
                                            echo "<td>" . htmlspecialchars($row['payment_type']) . "</td>";
                                            echo "<td>" . $paid_date_by . "</td>";
                                            echo "<td>₱" . number_format($row['amount_paid'], 2) . "</td>";
                                            // echo "<td>
                                            //     <button type='button' class='btn btn-primary btn-sm view-student-btn' data-id='" . htmlspecialchars($row['student_id']) . "'>
                                            //         <i class='bi bi-eye'></i>
                                            //     </button>
                                            // </td>";
                                            echo "</tr>";
                                        }

                                        $stmt->close();

                                        // Pagination
                                        $resultTotal = $conn->query("SELECT COUNT(*) AS total FROM student_payments");
                                        $totalRows = $resultTotal->fetch_assoc()['total'];
                                        $totalPages = ceil($totalRows / $limit);
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Pagination -->
                            <ul class="pagination pagination-primary">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Prev</a>
                                </li>
                                <?php for ($j = 1; $j <= $totalPages; $j++): ?>
                                    <li class="page-item <?php echo ($page == $j) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $j; ?>"><?php echo $j; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>
                <!-- Optional: DataTables for search/sort only, no paging -->
                <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
                <script>
                    $(document).ready(function() {
                        if (!$.fn.DataTable.isDataTable('#table1')) {
                            $('#table1').DataTable({
    "paging": false,
    "searching": true,
    "ordering": true,
    "info": false,
    "responsive": true,
    "autoWidth": false,
    "order": [[4, "desc"]] // This sets default sort to 'Paid Date & By' column (index 4)
});

                        }
                    });
                </script>
                <style>
                    /* Ensure table text wraps and doesn't overflow on small screens */
                    .table td,
                    .table th {
                        white-space: normal !important;
                        word-break: break-word;
                        vertical-align: middle;
                    }

                    @media (max-width: 575.98px) {
                        .card-body {
                            padding: 0.5rem !important;
                        }

                        .table-responsive {
                            margin: 0 -10px;
                        }

                        .pagination {
                            flex-wrap: wrap;
                        }
                    }
                </style>
            </div>

            <!-- Student Info Modal -->
            <div class="modal fade" id="studentInfoModal" tabindex="-1" aria-labelledby="studentInfoModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content rounded-4 shadow">
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold" id="studentInfoModalLabel">
                                <i class="bi bi-person-badge me-2 text-primary"></i>Student Information
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div id="studentInfoBody" class="pt-3 px-4">
                                <!-- Student info will be loaded here -->
                                <div class="text-center text-muted">Loading...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bootstrap 5 Form Validation -->
            <script>
                (function() {
                    'use strict';
                    var forms = document.querySelectorAll('.needs-validation');
                    Array.prototype.slice.call(forms).forEach(function(form) {
                        form.addEventListener('submit', function(event) {
                            if (!form.checkValidity()) {
                                event.preventDefault();
                                event.stopPropagation();
                            }
                            form.classList.add('was-validated');
                        }, false);
                    });
                })();
            </script>


            <footer>
                <div class="footer clearfix mb-0 text-muted">
                    <div class="float-start">
                        <p>2025 &copy; EduPay</p>
                    </div>
                    <div class="float-end">
                        <p>Developed by <a
                                href="https://smby25.github.io/MyPortfolioV1/">Sonny Louise Rogelio</a></p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <script src="assets/vendors/apexcharts/apexcharts.js"></script>
    <script src="assets/js/pages/dashboard.js"></script>

    <script src="assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('incomeChart').getContext('2d');
        const incomeChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['0', '1', '2', '3', '4', '5'],
                datasets: [{
                    label: 'Income',
                    data: [150000, 200000, 250000, 300000, 280000, 100000],
                    backgroundColor: '#4CAF50'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            $('.view-student-btn').on('click', function() {
                var studentId = $(this).data('id');
                $('#studentInfoBody').html('<div class="text-center text-muted">Loading...</div>');
                $('#studentInfoModal').modal('show');
                $.ajax({
                    url: 'php_functions/get_student_info.php',
                    type: 'GET',
                    data: {
                        id: studentId
                    },
                    success: function(response) {
                        $('#studentInfoBody').html(response);
                    },
                    error: function() {
                        $('#studentInfoBody').html('<div class="text-danger">Failed to load student info.</div>');
                    }
                });
            });
        });
    </script>
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Student record has been successfully saved!',
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    // Remove ?success=1 from the URL without reloading the page
                    if (window.history.replaceState) {
                        const url = new URL(window.location);
                        url.searchParams.delete('success');
                        window.history.replaceState({}, document.title, url.pathname + url.search);
                    }
                });
            });
        </script>
    <?php endif; ?>

    <script>
        $(document).ready(function() {
            // Existing view-student-btn code...

            // Delete button handler
            $('.delete-student-btn').on('click', function() {
                var studentId = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will permanently delete the student record.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // AJAX request to delete
                        $.ajax({
                            url: 'php_functions/delete_student.php',
                            type: 'POST',
                            data: {
                                student_id: studentId
                            },
                            success: function(response) {
                                // Optionally, check response for success
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'Student record has been deleted.',
                                    confirmButtonColor: '#3085d6'
                                }).then(() => {
                                    location.reload(); // Refresh page or remove row from table
                                });
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: 'Failed to delete student record.',
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>

</body>

</html>