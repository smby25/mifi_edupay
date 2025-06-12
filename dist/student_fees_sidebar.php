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
    <title>Student Fees</title>

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


<!-- Add this in your <head> or before </body> -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<body>
    <div id="app">
        <?php include 'sidebar.php'; ?>
        <?php include 'php_functions/add_fees_modal.php'; ?>
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
                        <h3>Student Fees</h3>
                    </div>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-success btn-s rounded-pill shadow-sm d-flex align-items-center gap-2"
                        data-bs-toggle="modal" data-bs-target="#addFeesModal">
                        <i class="bi bi-person-plus-fill"></i>
                        Add Fees
                    </button>
                </div>
            </div>

            <!-- Datatable -->
            <div class="table-responsive">
                <section class="section">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="paymentsTable" style="width:100%;">
                                    <thead>
                                        <tr>
                                            <th style="display:none;">Payment ID</th>
                                            <th>Batch Name</th>
                                            <th>Grade</th>
                                            <th>Payment Type</th>
                                            <th>Amount</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small-font-table">
                                        <?php
                                        $limit = 10;
                                        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                        $offset = ($page - 1) * $limit;

                                        $query = "
                            SELECT SQL_CALC_FOUND_ROWS 
                                id, batch_name, target_grade, payment_type, amount 
                            FROM 
                                payments
                            WHERE
                                status = 'active'
                            ORDER BY 
                                id DESC
                            LIMIT ?, ?
                        ";

                                        $stmt = $conn->prepare($query);
                                        $stmt->bind_param("ii", $offset, $limit);
                                        $stmt->execute();
                                        $result = $stmt->get_result();

                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td style='display:none;'>" . htmlspecialchars($row['id']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['batch_name']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['target_grade']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['payment_type']) . "</td>";
                                            echo "<td>₱" . number_format($row['amount'], 2) . "</td>";
                                            echo "<td>
                                <button type='button' 
                                    class='btn btn-warning btn-sm edit-payment-btn' 
                                    data-id='" . htmlspecialchars($row['id']) . "'
                                    data-batch_name='" . htmlspecialchars($row['batch_name']) . "'
                                    data-target_grade='" . htmlspecialchars($row['target_grade']) . "'
                                    data-payment_type='" . htmlspecialchars($row['payment_type']) . "'
                                    data-amount='" . htmlspecialchars($row['amount']) . "'>
                                    <i class='bi bi-pencil-square'></i>
                                </button>
                                <button type='button' class='btn btn-danger btn-sm delete-payment-btn ms-1' data-id='" . htmlspecialchars($row['id']) . "'>
                                    <i class='bi bi-trash'></i>
                                </button>
                            </td>";
                                            echo "</tr>";
                                        }

                                        $stmt->close();

                                        // Pagination
                                        $resultTotal = $conn->query("SELECT FOUND_ROWS() AS total");
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
                                "autoWidth": false
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
                        <p>2021 &copy; Mazer</p>
                    </div>
                    <div class="float-end">
                        <p>Developed by <a
                                href="https://smby25.github.io/MyPortfolioV1/">Sonny Louise Rogelio</a></p>
                    </div>
                </div>
            </footer>

            <!-- Success Modal
            <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="successModalLabel">Success</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Student record has been successfully saved!
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
    <script src="assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <script src="assets/vendors/apexcharts/apexcharts.js"></script>
    <script src="assets/js/pages/dashboard.js"></script>

    <script src="assets/js/main.js"></script>


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



    <!-- Update Fees -->
    <script>
        $(document).ready(function() {
            $('.edit-payment-btn').on('click', function() {
                // Get data from button
                var id = $(this).data('id');
                var batchName = $(this).data('batch_name');
                var targetGrade = $(this).data('target_grade');
                var paymentType = $(this).data('payment_type');
                var amount = $(this).data('amount');

                // Set modal fields
                $('#batchName').val(batchName);
                $('#targetGrade').val(targetGrade);

                // Remove all payment rows except the first
                $('#payment-list').html('');
                // Add the payment row with values
                $('#payment-list').append(`
                    <div class="row mb-2 payment-item">
                    <div class="col-md-6">
                        <input type="text" name="payment_type[]" class="form-control" placeholder="Payment Type (e.g. Tuition)" required value="${paymentType}">
                    </div>
                    <div class="col-md-4">
                        <input type="number" name="amount[]" class="form-control" placeholder="Amount" step="0.01" required value="${amount}">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger w-100 remove-payment-btn" onclick="removePaymentRow(this)">Remove</button>
                    </div>
                    </div>
                `);

                // Optionally, set a hidden input for ID if you want to update
                if ($('#editPaymentId').length === 0) {
                    $('<input>').attr({
                        type: 'hidden',
                        id: 'editPaymentId',
                        name: 'payment_id'
                    }).appendTo('#addFeesModal form');
                }
                $('#editPaymentId').val(id);

                // Hide add and remove buttons
                $('#addPaymentTypeBtn').hide();
                $('.remove-payment-btn').hide();

                // Set hidden input for ID
                $('#editPaymentId').val(id);

                // Open the modal
                $('#addFeesModal').modal('show');
            });

            // When modal is closed, show the buttons again for add mode
            $('#addFeesModal').on('hidden.bs.modal', function() {
                $('#addPaymentTypeBtn').show();
                $('.remove-payment-btn').show();
                $('#editPaymentId').val('');
            });
        });
    </script>

</body>

</html>