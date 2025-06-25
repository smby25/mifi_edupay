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
                <!-- <div class="col-auto">
                    <button type="button" class="btn btn-success btn-s rounded-pill shadow-sm d-flex align-items-center gap-2"
                        data-bs-toggle="modal" data-bs-target="#addStudentModal">
                        <i class="bi bi-person-plus-fill"></i>
                        Add Student
                    </button>
                </div> -->
            </div>

            <!-- Filter Dropdown (left) and Export Button (right) in 1 Row -->
            <div class="mb-2 d-flex align-items-center justify-content-between flex-wrap">
                <div>
                    <select id="gradeSectionStrandFilter" class="form-select" style="width:auto;display:inline-block;">
                        <option value="">All</option>
                    </select>
                </div>
                <div>
                    <button class="btn btn-outline-success d-flex align-items-center gap-2 rounded-pill shadow-sm px-3"
                        data-bs-toggle="modal" data-bs-target="#exportModal">
                        <i class="bi bi-download"></i>
                        Export by Grade
                    </button>
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
                                            <th style="display:none;">Student ID</th>
                                            <th>Full Name</th>
                                            <th>Grade &amp; Section / Strand</th>
                                            <th>Remaining Balance</th>
                                            <th style="display:none;">ESC Status</th>
                                            <th style="display:none;">Scholar</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small-font-table">
                                        <?php

                                        $query = "SELECT student_id, 
                                                         CONCAT(UCASE(LEFT(lname,1)), LCASE(SUBSTRING(lname,2))) AS lname, 
                                                         CONCAT(UCASE(LEFT(fname,1)), LCASE(SUBSTRING(fname,2))) AS fname, 
                                                         CONCAT(UCASE(LEFT(mname,1)), LCASE(SUBSTRING(mname,2))) AS mname, 
                                                         grade_level, section, strand, esc_stat, scholar
                                                  FROM students 
                                                  WHERE status = 'active' 
                                                  ORDER BY lname ASC";
                                        $result = $conn->query($query);

                                        while ($row = $result->fetch_assoc()) {
                                            $student_id = $row['student_id'];
                                            $full_name = $row['lname'] . ', ' . $row['fname'] . ' ' . strtoupper(substr($row['mname'], 0, 1)) . '.';
                                            $grade = $row['grade_level'];
                                            $section = $row['section'];
                                            $strand = $row['strand'] ?? '';
                                            $esc_stat = $row['esc_stat'] ?? '';
                                            $scholar = $row['scholar'] ?? '';

                                            if (strtolower($grade) === 'nursery' || strtolower($grade) === 'kinder') {
                                                $grade_section_strand = htmlspecialchars($grade);
                                            } else {
                                                $grade_section_strand = "Grade " . htmlspecialchars($grade);
                                            }
                                            if (!empty($section)) $grade_section_strand .= " - " . htmlspecialchars($section);
                                            if (!empty($strand)) $grade_section_strand .= " | " . htmlspecialchars($strand);

                                            // Get remaining balance
                                            $bal_stmt = $conn->prepare("
                                    SELECT 
                                        IFNULL((
                                            SELECT SUM(p.amount)
                                            FROM payments p
                                            WHERE (p.target_grade = ? OR p.student_id = ?)
                                            AND p.status = 'active'
                                        ), 0)
                                        -
                                        IFNULL((
                                            SELECT SUM(sp.amount_paid)
                                            FROM student_payments sp
                                            JOIN students s ON sp.student_id = s.student_id
                                            WHERE sp.student_id = ?
                                            AND s.status = 'active'
                                        ), 0) AS balance
                                ");

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
                                            echo "<td style='display:none;'>" . htmlspecialchars($esc_stat) . "</td>";
                                            echo "<td style='display:none;'>" . htmlspecialchars($scholar) . "</td>";
                                            echo "<td>
                                    <button type='button' class='btn btn-primary btn-sm view-student-btn' 
                                        data-id='" . htmlspecialchars($student_id) . "'
                                        data-name='" . htmlspecialchars($full_name) . "'
                                        data-grade='" . htmlspecialchars($grade) . "'
                                        data-section='" . htmlspecialchars($section) . "'
                                        data-esc_stat='" . htmlspecialchars($esc_stat) . "'
                                        data-scholar='" . htmlspecialchars($scholar) . "'
                                        data-balance='" . $balance . "'>
                                        <i class='bi bi-eye'></i> View
                                    </button>
                                  </td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Scripts -->
                <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
                <script>
                    $(document).ready(function() {
                        var table = $('#table1').DataTable({
                            paging: true,
                            searching: true,
                            ordering: true,
                            info: true,
                            responsive: true,
                            autoWidth: false,
                            order: [
                                [1, 'asc']
                            ] // Sort by Full Name (lname first)
                        });

                        // Define the desired grade order
                        var gradeOrder = [
                            "Nursery",
                            "Kinder",
                            "Grade 1",
                            "Grade 2",
                            "Grade 3",
                            "Grade 4",
                            "Grade 5",
                            "Grade 6",
                            "Grade 7",
                            "Grade 8",
                            "Grade 9",
                            "Grade 10",
                            "Grade 11",
                            "Grade 12"
                        ];

                        // Populate filter dropdown with unique values from Grade & Section / Strand column
                        var uniqueValues = {};
                        table.column(2).data().each(function(d) {
                            uniqueValues[d] = true;
                        });

                        // Group unique values by grade prefix for custom ordering
                        var grouped = {};
                        Object.keys(uniqueValues).forEach(function(v) {
                            var match = v.match(/^([A-Za-z ]+\d*|Nursery|Kinder)/);
                            var grade = match ? match[0].trim() : v;
                            if (!grouped[grade]) grouped[grade] = [];
                            grouped[grade].push(v);
                        });

                        // Append options in the specified grade order
                        gradeOrder.forEach(function(grade) {
                            if (grouped[grade]) {
                                grouped[grade].sort().forEach(function(val) {
                                    $('#gradeSectionStrandFilter').append('<option value="' + val + '">' + val + '</option>');
                                });
                            }
                        });

                        // Add any remaining unmatched values at the end
                        Object.keys(grouped).forEach(function(grade) {
                            if (gradeOrder.indexOf(grade) === -1) {
                                grouped[grade].sort().forEach(function(val) {
                                    $('#gradeSectionStrandFilter').append('<option value="' + val + '">' + val + '</option>');
                                });
                            }
                        });

                        // Filter by Grade & Section / Strand
                        $('#gradeSectionStrandFilter').on('change', function() {
                            var val = $(this).val();
                            table.search('').columns().search('');
                            table.column(2).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
                        });

                        // ✅ View button event handler (event delegation)
                        $(document).on('click', '.view-student-btn', function() {
                            const studentId = $(this).data('id');
                            const fullName = $(this).data('name');
                            const grade = $(this).data('grade');
                            const section = $(this).data('section');
                            const escStat = $(this).data('esc_stat') || '';
                            const scholar = $(this).data('scholar') || '';
                            const balance = $(this).data('balance');

                            // Show the data in your modal (update according to your modal content)
                            console.log("Student ID:", studentId);
                            console.log("Name:", fullName);
                            console.log("Grade:", grade);
                            console.log("Section:", section);
                            console.log("Balance:", balance);

                            // Example: Fill modal fields (replace with your actual modal element IDs)
                            $('#studentName').text(fullName);
                            $('#studentGrade').text(grade);
                            $('#studentSection').text(section);
                            $('#modalEscStat').text(escStat);
                            $('#modalScholar').text(scholar);
                            $('#studentBalance').text('₱' + parseFloat(balance).toFixed(2));

                            // Show the modal (if using Bootstrap)
                            $('#studentModal').modal('show');
                        });
                    });
                </script>

                <style>
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


            <!-- Student Payment Info Modal -->
            <script>
                $(document).ready(function() {
                    $('#table1 tbody').on('click', '.view-student-btn', function() {
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
                        const dateString = today.toLocaleString('default', {
                            month: 'long'
                        }) + ' ' + today.getDate() + ', ' + today.getFullYear();
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
                            success: function(data) {
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
                            error: function() {
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

        function reloadStudentPaymentInfo(studentId, grade, section) {
            // Set modal header details (optional, if you want to update them)
            // $('#modalStudentId').text(studentId);
            // $('#modalGradeSection').text(`Grade - ${grade} | ${section}`);

            // Get updated balance and payment types
            $.ajax({
                url: 'php_functions/get_payment_types.php',
                method: 'GET',
                data: {
                    grade: grade,
                    student_id: studentId
                },
                dataType: 'json',
                success: function(data) {
                    let rows = '';
                    let totalBalance = 0;
                    if (data.length > 0) {
                        data.forEach(item => {
                            const paymentAmount = parseFloat(item.amount) || 0;
                            const remainingAmount = parseFloat(item.remaining) || 0;
                            totalBalance += remainingAmount;

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
                    // Update balance
                    $('#modalBalanceAmount').text(`₱${totalBalance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`);
                },
                error: function() {
                    $('#modalPaymentTable').html('<tr><td colspan="6" class="text-center text-danger">Error loading payment types.</td></tr>');
                }
            });
        }


        $(document).on('click', '#confirmPayBtn', function(e) {
            var studentId = $('#modalStudentId').text();
            var amountToPay = $('#payAmount').val();
            var paymentId = $('#payModal').data('payment_id'); // get payment_id from modal
            var paidBy = $('#paidBy').val();
            var description = $('#payDescription').val();
            var datePaid = $('#payDate').val();

            // Overpayment check
            var payAmount = parseFloat(amountToPay) || 0;
            var remaining = parseFloat($('#payRemaining').text().replace(/[₱,]/g, '')) || 0;
            if (payAmount > remaining) {
                showOverpayWarning();
                return;
            }

            if (!amountToPay || payAmount <= 0) {
                Swal.fire('Invalid Amount', 'Please enter a valid amount.', 'warning');
                return;
            }

            if (!datePaid) {
                Swal.fire('Required Field', 'Please select a date paid.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                html: 'You are about to pay <strong>₱' + payAmount.toLocaleString() + '</strong>.<br>This action cannot be undone.',
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
                            paid_by: paidBy,
                            description: description,
                            date_paid: datePaid
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Payment Successful!',
                                text: 'The payment has been saved.',
                                confirmButtonColor: '#3085d6'
                            });
                            // Clear the pay modal fields
                            $('#payAmount').val('');
                            $('#payDescription').val('');
                            $('#payDate').val('');
                            // Hide only the pay modal
                            $('#payModal').modal('hide');
                            // Reload payment info in the student modal
                            const studentId = $('#modalStudentId').text().trim();
                            const gradeSection = $('#modalGradeSection').text().split('-')[1] || '';
                            const grade = gradeSection.split('|')[0]?.trim() || '';
                            const section = gradeSection.split('|')[1]?.trim() || '';
                            reloadStudentPaymentInfo(studentId, grade, section);
                        },
                        error: function() {
                            Swal.fire('Error', 'Error saving payment.', 'error');
                        }
                    });
                }
            });
        });

        // Also support direct JS event for confirmPayBtn (in case of non-jQuery usage)
        document.getElementById("confirmPayBtn")?.addEventListener("click", function(e) {
            const payAmount = parseFloat(document.getElementById("payAmount").value) || 0;
            const remaining = parseFloat(document.getElementById("payRemaining").textContent.replace(/[₱,]/g, '')) || 0;

            if (payAmount > remaining) {
                showOverpayWarning();
                e.preventDefault();
                return false;
            }
        });
    </script>




    <script>
        $(document).on('click', '.btn-outline-primary', function() {
            var studentId = $('#modalStudentId').text();
            $('#recentTransactionsTable').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');
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
                            // Format date_paid as "July 25, 2025"
                            var dateObj = new Date(item.date_paid.replace(/-/g, '/')); // Safari-compatible
                            var options = {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            };
                            var formattedDate = dateObj.toLocaleDateString('en-US', options);

                            rows += '<tr>' +
                                '<td>' + formattedDate + '</td>' +
                                '<td>' + item.payment_type + '</td>' +
                                '<td>₱' + parseFloat(item.amount_paid).toLocaleString() + '</td>' +
                                '<td>' + (item.description ? item.description : '-') + '</td>' +
                                '</tr>';
                        });
                        $('#recentTransactionsTable').html(rows);
                    } else {
                        $('#recentTransactionsTable').html('<tr><td colspan="4" class="text-center text-muted">No transactions found.</td></tr>');
                    }
                },
                error: function() {
                    $('#recentTransactionsTable').html('<tr><td colspan="4" class="text-center text-danger">Error loading transactions.</td></tr>');
                }
            });
        });
    </script>


    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="exportModalLabel"></h5>
                    <i class="bi bi-download me-2"></i>Export Student Ledger by Grade
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="gradeSelect">Select Grade:</label>
                    <select id="gradeSelect" class="form-select">
                        <option value="">-- Select Grade --</option>
                        <option value="Nursery">Nursery</option>
                        <option value="Kinder">Kinder</option>
                        <option value="1">Grade 1</option>
                        <option value="2">Grade 2</option>
                        <option value="3">Grade 3</option>
                        <option value="4">Grade 4</option>
                        <option value="5">Grade 5</option>
                        <option value="6">Grade 6</option>
                        <option value="7">Grade 7</option>
                        <option value="8">Grade 8</option>
                        <option value="9">Grade 9</option>
                        <option value="10">Grade 10</option>
                        <option value="11">Grade 11</option>
                        <option value="12">Grade 12</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button id="exportPdfBtn" class="btn btn-danger">Export PDF</button>
                    <!-- <button id="exportExcelBtn" class="btn btn-success">Export Excel</button> -->
                </div>
            </div>
        </div>
    </div>
    <script>
        // JavaScript to handle export
        document.getElementById('exportPdfBtn').onclick = function() {
            const grade = document.getElementById('gradeSelect').value;
            if (!grade) {
                alert('Please select a grade.');
                return;
            }
            window.location.href = `php_functions/export_grade_pdf.php?grade=${encodeURIComponent(grade)}`;
        };
    </script>


</body>

</html>