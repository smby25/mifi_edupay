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

    <!-- <style>
        body,
        html {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
        }
    </style> -->
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
                        <h3>Student Dashboard</h3>
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
                                            <th style="display:none;">ID</th>
                                            <th style="display:none;">Student ID</th>
                                            <th>Name</th>
                                            <th>LRN</th>
                                            <th>Grade &amp; Section / Strand</th>
                                            <th style="display:none;">Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small-font-table">
                                        <?php
                                        include '../conn.php';
                                        $query = "SELECT student_id, fname, mname, lname, lrn, grade_level, section, strand, status 
                                            FROM students 
                                            WHERE status = 'active' 
                                            ORDER BY lname ASC";
                                        $result = $conn->query($query);
                                        while ($row = $result->fetch_assoc()) {
                                                // Normalize each part to have first letter uppercase, rest lowercase
                                                $lname = ucwords(strtolower($row['lname']));
                                                $fname = ucwords(strtolower($row['fname']));
                                                $mname = ucwords(strtolower($row['mname']));
                                                $middle_initial = !empty($mname) ? strtoupper(substr($mname, 0, 1)) . '.' : '';
                                                $full_name = $lname . ', ' . $fname . ' ' . $middle_initial;
                                                $grade_section_strand = htmlspecialchars($row['grade_level']) . ' - ' . htmlspecialchars($row['section']);
                                                if (!empty($row['strand'])) {
                                                        $grade_section_strand .= ' / ' . htmlspecialchars($row['strand']);
                                                }
                                                echo "<tr>";
                                                echo "<td style='display:none;'>" . htmlspecialchars($row['student_id']) . "</td>";
                                                echo "<td style='display:none;'>" . htmlspecialchars($row['student_id']) . "</td>";
                                                echo "<td>" . htmlspecialchars($full_name) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['lrn']) . "</td>";
                                            echo "<td>" . $grade_section_strand . "</td>";
                                            echo "<td style='display:none;'>" . htmlspecialchars($row['status']) . "</td>";
                                            echo "<td>
                    <button type='button' class='btn btn-primary btn-sm view-student-btn' data-id='" . htmlspecialchars($row['student_id']) . "'>
                        <i class='bi bi-eye'></i>
                    </button>
                    <button type='button' class='btn btn-danger btn-sm delete-student-btn ms-1' data-id='" . htmlspecialchars($row['student_id']) . "'>
                        <i class='bi bi-trash'></i>
                    </button>
                </td>";
                                            echo "</tr>";
                                        }
                                        $result->close();
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
                                [2, 'asc']
                            ] // Column index 2 is "Name"
                        });

                        // Define the desired order
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

                        // Collect unique values from column 4
                        var uniqueValues = {};
                        table.column(4).data().each(function(d) {
                            uniqueValues[d] = true;
                        });

                        // Group values by grade prefix
                        var grouped = {};
                        $.each(Object.keys(uniqueValues), function(i, v) {
                            // Extract grade prefix (e.g., "Grade 1", "Kinder", etc.)
                            var prefix = v.split(' - ')[0].trim();
                            if (!grouped[prefix]) grouped[prefix] = [];
                            grouped[prefix].push(v);
                        });

                        // Add options in the desired order
                        gradeOrder.forEach(function(grade) {
                            if (grouped[grade]) {
                                grouped[grade].sort(); // Sort by section/strand
                                grouped[grade].forEach(function(val) {
                                    $('#gradeSectionStrandFilter').append('<option value="' + val + '">' + val + '</option>');
                                });
                            }
                        });

                        // Add any remaining (unmatched) options
                        Object.keys(grouped).forEach(function(grade) {
                            if (gradeOrder.indexOf(grade) === -1) {
                                grouped[grade].sort();
                                grouped[grade].forEach(function(val) {
                                    $('#gradeSectionStrandFilter').append('<option value="' + val + '">' + val + '</option>');
                                });
                            }
                        });

                        // Filter when dropdown changes
                        $('#gradeSectionStrandFilter').on('change', function() {
                            var val = $(this).val();
                            table.column(4).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
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

    

    <!-- Strand Required -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const gradeLevelSelect = document.getElementById("gradeLevelSelect");
            const strandWrapper = document.getElementById("strandWrapper");
            const strandSelect = strandWrapper.querySelector('select[name="strand"]');

            gradeLevelSelect.addEventListener("change", function() {
                const selectedGrade = this.value;
                if (selectedGrade === "11" || selectedGrade === "12") {
                    strandWrapper.classList.remove("d-none");
                    strandSelect.setAttribute("required", "required");
                    // Show modal alert
                    Swal.fire({
                        icon: 'info',
                        title: 'Strand Required',
                        text: 'Please select a strand for Grade 11 or 12 students.',
                        confirmButtonColor: '#3085d6'
                    });
                } else {
                    strandWrapper.classList.add("d-none");
                    strandSelect.removeAttribute("required");
                    strandSelect.value = "";
                }
            });
        });
    </script>

    <script>
        $(document).on('click', '.view-student-btn', function() {
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
                    text: "This will move the student record to archive.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, archive it!',
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

    <!-- Duplicate student Modal -->
    <?php if (isset($_GET['duplicate']) && $_GET['duplicate'] == 1): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Duplicate Student',
                    text: 'A student with the same name and LRN already exists.',
                    confirmButtonColor: '#3085d6'
                });
                // Automatically open the Add Student modal
                $('#addStudentModal').modal('show');
            });
        </script>
    <?php endif; ?>


</body>

</html>