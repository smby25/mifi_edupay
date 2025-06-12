<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<header class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row bg-white shadow-sm px-3">
    <!-- Left side: Sidebar Toggle (for mobile) -->
    <div class="d-flex align-items-center">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>

        <div class="d-flex align-items-start" style="margin-left: 10px;">
            <div class="logo" style="width: 150px;">
                <a href="index.php">
                    <img src="assets/css/img/malindig_header.png" alt="Logo" style="width: 100%;">
                </a>
            </div>
            <!-- <span class="ms-3 fw-bold fs-5" style="white-space: nowrap;">Malindig EduPay</span> -->
        </div>
    </div>

    <!-- Right side: Notification & Profile -->
    <div class="d-flex align-items-center gap-3">
        <!-- Dark Mode Toggle Button -->
        <button id="darkModeToggle" class="btn btn-light border-0 p-2 rounded-circle shadow-sm position-relative" aria-expanded="false" style="width: 40px; height: 40px;">
            <i class="bi bi-moon"></i>
        </button>

        <!-- Notification Button -->
        <div class="position-relative dropdown">
            <button class="btn btn-light border-0 p-2 rounded-circle shadow-xl position-relative" id="notificationButton" data-bs-toggle="dropdown" aria-expanded="false" style="width: 40px; height: 40px;">
                <i class="bi bi-bell fs-5"></i>


            </button>

            <!-- Dropdown Menu -->
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 p-2" aria-labelledby="notificationButton" style="width: 350px; max-height: 400px; overflow-y: auto; Left: -340px;">



                <li>
                    <hr class="dropdown-divider">
                </li>

                <li>
                    <a class="dropdown-item text-center fw-bold text-primary py-2" href="table_far.php">View All</a>
                </li>
            </ul>
        </div>

        <!-- Profile Dropdown -->
        <div class="dropdown">
            <button class="btn btn-light border-0 d-flex align-items-center gap-2 shadow-xxl rounded-circle p-2" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="assets/images/faces/1.jpg" alt="Profile" class="rounded-circle" width="32" height="32">
            </button>

            <!-- Dropdown Menu -->
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 p-2" aria-labelledby="profileDropdown" style="width: 220px; left: -220px;">
                <li class="d-flex flex-column align-items-center gap-2 p-3 border-bottom">
                    <img src="assets/images/faces/1.jpg" alt="Profile" class="rounded-circle mb-2" width="48" height="48">
                    <span class="fw-bold">
                        <?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : htmlspecialchars($_SESSION['username']); ?>
                    </span>
                    <span class="text-muted small">
                        <!-- Optionally display email if you store it in session -->
                        <?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>
                    </span>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="profile.php">
                        <i class="bi bi-person-circle text-primary"></i> Profile
                    </a>
                </li>

                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2"
                        href="#"
                        data-bs-toggle="modal"
                        data-bs-target="#settingsModal">
                        <i class="bi bi-gear text-dark"></i> Settings
                    </a>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger fw-semibold" href="#" id="logoutButton">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>

<?php
// Get current file name without query string
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div id="sidebar" class="active">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header">
            <div class="d-flex justify-content-between">
                <div class="logo">
                    <a href="index.html"><img src="assets/images/logo/logo.png" alt="Logo" srcset=""></a>
                </div>
                <div class="toggler">
                    <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                </div>
            </div>
        </div>
        <div class="sidebar-menu">
            <ul class="menu">
                <li class="sidebar-title">Menu</li>

                <li class="sidebar-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                    <a href="index.php" class='sidebar-link'>
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="sidebar-item <?php echo ($current_page == 'student_sidebar.php') ? 'active' : ''; ?>">
                    <a href="student_sidebar.php" class='sidebar-link'>
                        <i class="bi bi-people"></i>
                        <span>Student</span>
                    </a>
                </li>

                <li class="sidebar-item has-sub <?php
                                                // Check if any of the submenu pages are active
                                                $ledger_active = in_array($current_page, ['student_ledger_sidebar.php', 'student_fees_sidebar.php', 'transactions_sidebar.php']);
                                                echo $ledger_active ? 'active' : '';
                                                ?>">
                    <a href="#" class='sidebar-link'>
                        <i class="bi bi-cash"></i>
                        <span>Student Ledger</span>
                    </a>
                    <ul class="submenu" style="<?php echo $ledger_active ? 'display: block;' : ''; ?>">
                        <li class="submenu-item <?php echo ($current_page == 'student_ledger_sidebar.php') ? 'active' : ''; ?>">
                            <a href="student_ledger_sidebar.php">Ledger List</a>
                        </li>
                        <li class="submenu-item <?php echo ($current_page == 'student_fees_sidebar.php') ? 'active' : ''; ?>">
                            <a href="student_fees_sidebar.php">Fees</a>
                        </li>
                        <li class="submenu-item <?php echo ($current_page == 'transactions_sidebar.php') ? 'active' : ''; ?>">
                            <a href="transactions_sidebar.php">Transactions</a>
                        </li>
                    </ul>
                </li>

                <li class="sidebar-title">Other Information</li>

                <li class="sidebar-item">
                    <a href="about_system.php" class='sidebar-link'>
                        <i class="bi bi-puzzle"></i>
                        <span>About System</span>
                    </a>
                </li>
            </ul>
        </div>
        <button class="sidebar-toggler btn x"><i data-feather="x"></i></button>
    </div>
</div>


<!-- Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="settingsModalLabel">
                    <i class="bi bi-gear me-2 text-dark"></i>Settings
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs mb-3" id="settingsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button" role="tab" aria-controls="backup" aria-selected="true">
                            <i class="bi bi-download"></i> Backup Database
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="import-tab" data-bs-toggle="tab" data-bs-target="#import" type="button" role="tab" aria-controls="import" aria-selected="false">
                            <i class="bi bi-upload"></i> Import Database
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="export-tab" data-bs-toggle="tab" data-bs-target="#export" type="button" role="tab" aria-controls="export" aria-selected="false">
                            <i class="bi bi-file-earmark-excel"></i> Export Students
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="settingsTabContent">
                    <!-- Backup Tab -->
                    <div class="tab-pane fade show active" id="backup" role="tabpanel" aria-labelledby="backup-tab">
                        <div class="mb-3">
                            <p>Click the button below to download a backup of your database.</p>
                            <form method="POST" action="php_functions/backup_db.php">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-download"></i> Backup Now
                                </button>
                            </form>
                        </div>
                    </div>
                    <!-- Import Tab -->
                    <div class="tab-pane fade" id="import" role="tabpanel" aria-labelledby="import-tab">
                        <div class="mb-3">
                            <p>Import a database backup (.sql file):</p>
                            <form method="POST" action="php_functions/import_db.php" enctype="multipart/form-data">
                                <input type="file" name="import_file" accept=".sql" class="form-control mb-2" required>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-upload"></i> Import
                                </button>
                            </form>
                        </div>
                    </div>
                    <!-- Export Tab -->
                    <div class="tab-pane fade" id="export" role="tabpanel" aria-labelledby="export-tab">
                        <div class="mb-3">
                            <p>Export students by grade level to Excel:</p>
                            <form method="POST" action="php_functions/export_students_excel.php" target="_blank">
                                <select name="grade_level" class="form-select mb-2" required>
                                    <option value="" disabled selected>Select Grade Level</option>
                                    <option value="7">Grade 7</option>
                                    <option value="8">Grade 8</option>
                                    <option value="9">Grade 9</option>
                                    <option value="10">Grade 10</option>
                                    <option value="11">Grade 11</option>
                                    <option value="12">Grade 12</option>
                                </select>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-file-earmark-excel"></i> Export to Excel
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Open settings modal when clicking Settings in profile dropdown
    $(document).ready(function() {
        $('a[href="settings.php"]').on('click', function(e) {
            e.preventDefault();
            $('#settingsModal').modal('show');
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const logoutBtn = document.getElementById('logoutButton');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = 'logout.php';
            });
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const exportForm = document.querySelector('form[action="php_functions/export_students_excel.php"]');
    if (exportForm) {
        exportForm.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                icon: 'success',
                title: 'Export Started',
                text: 'Your Excel export will download shortly.',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                exportForm.submit(); // Continue with the download
            });
        });
    }
});
</script>