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
    <title>Dashboard</title>

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
        <?php include 'student_payment_modal.php'; ?>
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
                        <h3>Student Ledger</h3>
                    </div>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-success btn-s rounded-pill shadow-sm d-flex align-items-center gap-2"
                        data-bs-toggle="modal" data-bs-target="#addStudentModal">
                        <i class="bi bi-person-plus-fill"></i>
                        Add Student
                    </button>
                </div>
            </div>

            <!-- Filter Dropdown -->
            <div class="mb-2">
                <!-- <label for="gradeSectionStrandFilter" class="form-label">Filter by Grade &amp; Section / Strand</label> -->
                <select id="gradeSectionStrandFilter" class="form-select" style="width:auto;display:inline-block;">
                    <option value="">All</option>
                </select>
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
                                            <th style="display:none;">Student ID</th>
                                            <th>Full Name</th>
                                            <th>Grade &amp; Section / Strand</th>
                                            <th>Remaining Balance</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small-font-table">
                                        <?php
                                        $limit = 10;
                                        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                        $offset = ($page - 1) * $limit;

                                        // Only select students with status = 'active'
                                        $query = "SELECT SQL_CALC_FOUND_ROWS student_id, fname, mname, lname, grade_level, section, strand 
                                                  FROM students 
                                                  WHERE status = 'active'
                                                  LIMIT ?, ?";
                                        $stmt = $conn->prepare($query);
                                        $stmt->bind_param("ii", $offset, $limit);
                                        $stmt->execute();
                                        $result = $stmt->get_result();

                                        while ($row = $result->fetch_assoc()) {
                                            $student_id = $row['student_id'];
                                            $full_name = $row['lname'] . ', ' . $row['fname'] . ' ' . strtoupper(substr($row['mname'], 0, 1)) . '.';
                                            $grade = $row['grade_level'];
                                            $section = $row['section'];
                                            $strand = $row['strand'] ?? '';

                                            // Combine Grade, Section, Strand
                                            $grade_section_strand = "Grade " . htmlspecialchars($grade);
                                            if (!empty($section)) {
                                                $grade_section_strand .= " - " . htmlspecialchars($section);
                                            }
                                            if (!empty($strand)) {
                                                $grade_section_strand .= " | " . htmlspecialchars($strand);
                                            }

                                            // Get remaining balance (total fees - payments)
                                            $balance_query = "
    SELECT 
        IFNULL((
            SELECT SUM(p.amount)
            FROM payments p
            WHERE p.target_grade = ? OR p.student_id = ?
        ), 0)
        -
        IFNULL((
            SELECT SUM(sp.amount_paid)
            FROM student_payments sp
            WHERE sp.student_id = ?
        ), 0) AS balance
";
                                            $bal_stmt = $conn->prepare($balance_query);
                                            $bal_stmt->bind_param("sii", $grade, $student_id, $student_id);
                                            $bal_stmt->execute();
                                            $bal_result = $bal_stmt->get_result();
                                            $balance = $bal_result->fetch_assoc()['balance'] ?? 0;
                                            $bal_stmt->close();


                                            echo "<tr>";
                                            echo "<td style='display:none;'>" . htmlspecialchars($student_id) . "</td>";
                                            echo "<td>" . htmlspecialchars($full_name) . "</td>";
                                            echo "<td>" . $grade_section_strand . "</td>";
                                            echo "<td>₱" . number_format($balance, 2) . "</td>";
                                            echo "<td>
        <button type='button' class='btn btn-primary btn-sm view-student-btn' 
            data-id='" . htmlspecialchars($student_id) . "'
            data-name='" . htmlspecialchars($full_name) . "'
            data-grade='" . htmlspecialchars($grade) . "'
            data-section='" . htmlspecialchars($section) . "'
            data-balance='" . $balance . "'> <!-- NO number_format here -->
            <i class='bi bi-eye'></i> View
        </button>
      </td>";
                                            echo "</tr>";
                                        }
                                        $stmt->close();
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
                        var table = $('#table1').DataTable({
                            "paging": false,
                            "searching": true,
                            "ordering": true,
                            "info": false,
                            "responsive": true,
                            "autoWidth": false
                        });

                        // Populate the dropdown with unique values from the Grade/Section/Strand column (index 2)
                        var uniqueValues = {};
                        table.column(2).data().each(function(d) {
                            uniqueValues[d] = true;
                        });
                        $.each(Object.keys(uniqueValues).sort(), function(i, v) {
                            $('#gradeSectionStrandFilter').append('<option value="' + v + '">' + v + '</option>');
                        });

                        // Filter table when dropdown changes
                        $('#gradeSectionStrandFilter').on('change', function() {
                            var val = $(this).val();
                            table.column(2).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
                        });
                    });
                </script>

<!-- Student Payment Info Modal -->
<script>
    $(document).ready(function () {
        $('.view-student-btn').on('click', function () {
            const studentId = $(this).data('id');
            const studentName = $(this).data('name');
            const grade = $(this).data('grade');
            const section = $(this).data('section');
            const balance = parseFloat($(this).data('balance')) || 0;

            // Set modal header details
            $('#modalStudentId').text(studentId);
            $('#modalStudentName').text(studentName);
            $('#modalGradeSection').text(`Grade - ${grade} | ${section}`);
            $('#modalBalanceAmount').text(`₱${balance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`);


            const today = new Date();
            const dateString = today.toLocaleString('default', { month: 'long' }) + ' ' + today.getDate() + ', ' + today.getFullYear();
            $('#modalBalanceDate').text(dateString);

            // Clear previous rows while loading
            $('#modalPaymentTable').html('<tr><td colspan="6" class="text-center text-muted">Loading...</td></tr>');

            // AJAX request to get payment types
            $.ajax({
                url: 'php_functions/get_payment_types.php',
                method: 'GET',
                data: {
                    grade: grade,
                    student_id: studentId
                },
                dataType: 'json',
                success: function (data) {
                    let rows = '';
                    if (data.length > 0) {
                        data.forEach(item => {
                            const paymentAmount = parseFloat(item.amount) || 0;
                            const remainingAmount = parseFloat(item.remaining) || 0;

                            rows += `
                                <tr>
                                    <td style="display:none;">${studentId}</td>
                                    <td style="display:none;">${item.id}</td>
                                    <td>${item.payment_type}</td>
                                    <td>₱${paymentAmount.toLocaleString()}</td>
                                    <td>₱${remainingAmount.toLocaleString()}</td>
                                    <td>
                                        <button type="button"
                                            class="btn btn-success btn-sm pay-btn pay-row-btn"
                                            data-payment_type="${item.payment_type}"
                                            data-amount="${paymentAmount}"
                                            data-remaining="${remainingAmount}"
                                            data-student_id="${studentId}"
                                            data-payment_id="${item.id}">
                                            <i class="bi bi-cash"></i> Pay
                                        </button>
                                    </td>
                                </tr>`;
                        });
                    } else {
                        rows = `<tr><td colspan="6" class="text-center text-muted">No payment types found.</td></tr>`;
                    }

                    $('#modalPaymentTable').html(rows);
                },
                error: function () {
                    $('#modalPaymentTable').html('<tr><td colspan="6" class="text-center text-danger">Error loading payment types.</td></tr>');
                }
            });

            $('#studentModal').modal('show');
        });
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

            <script>
                $(function() {
                    $(document).on('click', '.pay-row-btn', function(e) {
                        var remaining = parseFloat($(this).data('remaining'));
                        var paymentType = $(this).data('payment_type');
                        if (remaining <= 0) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Fully Paid!',
                                text: 'The payment type "' + paymentType + '" is already fully paid.',
                                confirmButtonColor: '#3085d6'
                            });
                            e.preventDefault();
                            return false;
                        }
                        // Only show modal if not fully paid
                        $('#payType').text(paymentType);
                        $('#payTotal').text('₱' + parseFloat($(this).data('amount')).toLocaleString());
                        $('#payRemaining').text('₱' + remaining.toLocaleString());
                        $('#payAmount').val('');
                        $('#payModal').data('payment_id', $(this).data('payment_id')); // store payment_id in modal
                        $('#payModal').modal('show');
                    });
                });
            </script>

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

    <!-- Add Payment -->
    <script>
        // Show SweetAlert2 warning for overpayment
        function showOverpayWarning() {
            Swal.fire({
                icon: 'warning',
                title: 'Overpayment Warning',
                text: 'The amount entered exceeds the remaining balance. Please enter a valid amount.',
                confirmButtonColor: '#ffc107',
                confirmButtonText: 'Okay'
            });
        }

        $(document).on('click', '#confirmPayBtn', function(e) {
            var studentId = $('#modalStudentId').text();
            var amountToPay = $('#payAmount').val();
            var paymentId = $('#payModal').data('payment_id'); // get payment_id from modal
            var paidBy = $('#paidBy').val();

            // Overpayment check
            var payAmount = parseFloat(amountToPay) || 0;
            var remaining = parseFloat($('#payRemaining').text().replace(/[₱,]/g, '')) || 0;
            if (payAmount > remaining) {
                // Show the overpayment modal
                // var overpayModal = new bootstrap.Modal(document.getElementById('overpayModal'));
                // overpayModal.show();
                showOverpayWarning();
                return;
            }

            if (!amountToPay || parseFloat(amountToPay) <= 0) {
                Swal.fire('Invalid Amount', 'Please enter a valid amount.', 'warning');
                return;
            }
            if (!paidBy || paidBy.trim() === "") {
                Swal.fire('Required Field', 'Please enter who paid.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                html: 'You are about to pay <strong>₱' + parseFloat(amountToPay).toLocaleString() + '</strong>.<br>This action cannot be undone.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Pay',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#aaa'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'php_functions/save_student_payment.php',
                        type: 'POST',
                        data: {
                            student_id: studentId,
                            payment_id: paymentId,
                            amount_paid: amountToPay,
                            paid_by: paidBy
                        },
                        success: function(response) {
                            $('#payModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Payment Successful!',
                                text: 'The payment has been saved.',
                                confirmButtonColor: '#3085d6'
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function() {
                            Swal.fire('Error', 'Error saving payment.', 'error');
                        }
                    });
                }
            });
        });

        // Also support direct JS event for confirmPayBtn (in case of non-jQuery usage)
        document.getElementById("confirmPayBtn")?.addEventListener("click", function (e) {
            const payAmount = parseFloat(document.getElementById("payAmount").value) || 0;
            const remaining = parseFloat(document.getElementById("payRemaining").textContent.replace(/[₱,]/g, '')) || 0;

            if (payAmount > remaining) {
                // const overpayModal = new bootstrap.Modal(document.getElementById('overpayModal'));
                // overpayModal.show();
                showOverpayWarning();
                // Prevent further action
                e.preventDefault();
                return false;
            }
        });
    </script>

    <script>
        $(document).on('click', '.btn-outline-primary', function() {
            var studentId = $('#modalStudentId').text();
            $('#recentTransactionsTable').html('<tr><td colspan="3" class="text-center">Loading...</td></tr>');
            $('#recentTransactionsModal').modal('show');
            $.ajax({
                url: 'php_functions/get_recent_transactions.php',
                type: 'GET',
                data: {
                    student_id: studentId
                },
                dataType: 'json',
                success: function(data) {
                    if (data.length > 0) {
                        var rows = '';
                        data.forEach(function(item) {
                            // Format date_paid as "July 25, 2025 | 9:25am"
                            var dateObj = new Date(item.date_paid);
                            var options = {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            };
                            var datePart = dateObj.toLocaleDateString('en-US', options);
                            var hours = dateObj.getHours();
                            var minutes = dateObj.getMinutes().toString().padStart(2, '0');
                            var ampm = hours >= 12 ? 'pm' : 'am';
                            hours = hours % 12;
                            hours = hours ? hours : 12; // 0 => 12
                            var timePart = hours + ':' + minutes + ampm;
                            var formattedDate = datePart + ' | ' + timePart;

                            rows += '<tr>' +
                                '<td>' + formattedDate + '</td>' +
                                '<td>' + item.payment_type + '</td>' +
                                '<td>₱' + parseFloat(item.amount_paid).toLocaleString() + '</td>' +
                                '<td>' + (item.paid_by ? item.paid_by : '-') + '</td>' +
                                '</tr>';
                        });
                        $('#recentTransactionsTable').html(rows);
                    } else {
                        $('#recentTransactionsTable').html('<tr><td colspan="4" class="text-center text-muted">No transactions found.</td></tr>');
                    }
                },
                error: function() {
                    $('#recentTransactionsTable').html('<tr><td colspan="3" class="text-center text-danger">Error loading transactions.</td></tr>');
                }
            });
        });
    </script>

</body>

</html>