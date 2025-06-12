<?php
include "../../conn.php";

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // If mode=edit, return JSON and exit
        if (isset($_GET['mode']) && $_GET['mode'] === 'edit') {
            header('Content-Type: application/json');
            echo json_encode($row);
            exit;
        }
?>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <div class="container py-2" style="max-width: 900px;">
            <div class="bg-light p-1 rounded-4 shadow-sm">
                <!-- <h2 class="mb-4 text-primary fw-bold d-flex align-items-center gap-1">
            Student Profile
        </h2> -->
                <div class="d-flex justify-content-end mb-2">
                    <button
                        type="button"
                        class="btn btn-sm btn-primary editBtn edit-student-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#addStudentModal"
                        data-id="<?= htmlspecialchars($row['student_id']) ?>"
                        data-fname="<?= htmlspecialchars($row['fname']) ?>"
                        data-mname="<?= htmlspecialchars($row['mname']) ?>"
                        data-lname="<?= htmlspecialchars($row['lname']) ?>"
                        data-sex="<?= htmlspecialchars($row['sex']) ?>"
                        data-db="<?= htmlspecialchars($row['dbirth']) ?>"
                        data-grade="<?= htmlspecialchars($row['grade_level']) ?>"
                        data-section="<?= htmlspecialchars($row['section']) ?>"
                        data-stype="<?= htmlspecialchars($row['student_type']) ?>"
                        data-strand="<?= htmlspecialchars($row['strand']) ?>"
                        data-address="<?= htmlspecialchars($row['complete_address']) ?>"
                        data-bs-dismiss="modal">
                        <i class="bi bi-pencil-square"></i> Edit
                    </button>
                </div>
                <!-- Personal Information -->
                <section class="mb-5">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-2 col-4 d-flex align-items-center">
                            <img src="<?= !empty($row['photo_url']) ? htmlspecialchars($row['photo_url']) : 'https://ui-avatars.com/api/?name=' . urlencode($row['fname'] . ' ' . $row['lname']) . '&background=0D8ABC&color=fff&size=128' ?>"
                                alt="Student Photo" class="img-fluid rounded-circle shadow-sm" style="width: 90px; height: 90px; object-fit: cover;">
                        </div>
                        <div class="col-md-10 col-8">
                            <h2 class="mb-0">
                                <?= htmlspecialchars(ucwords(strtolower($row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'] . ' ' . $row['suffix']))) ?>
                            </h2>
                            <input type="text" readonly class="form-control-plaintext ps-0" value="<?= htmlspecialchars($row['lrn']) ?>">
                            <input type="text" readonly class="form-control-plaintext ps-0 pt-n2" value="Grade-<?= htmlspecialchars($row['grade_level']) ?> | <?= htmlspecialchars($row['section']) ?> | <?= htmlspecialchars($row['strand']) ?>">
                            <label class="ps-0 pt-n2" style="font-size: 0.70rem; opacity: 0.7;">Grade Level | Section | Strand</label>
                        </div>
                    </div>
                </section>
                <!-- Personal Information -->
                <section class="mb-2">
                    <h5 class="text-secondary border-bottom pb-2 mb-3 fw-semibold">
                        <i class="bi bi-person-lines-fill me-2"></i>Personal Information
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars(date('F j, Y', strtotime($row['dbirth']))) ?>">
                                <label>Birthdate</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars(floor((time() - strtotime($row['dbirth'])) / (365.25 * 24 * 60 * 60))) ?>">
                                <label>Age</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($row['sex']) ?>">
                                <label>Sex</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($row['complete_address']) ?>">
                                <label>Address</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($row['barangay']) ?>">
                                <label>Barangay</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($row['city_municipality']) ?>">
                                <label>City/Municipality</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($row['province']) ?>">
                                <label>Province</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($row['zipcode']) ?>">
                                <label>ZIP Code</label>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- Contact Information -->
                <section class="mb-5">
                    <h5 class="text-secondary border-bottom pb-2 mb-3 fw-semibold">
                        <i class="bi bi-telephone me-2"></i>Contact Information
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($row['mobile_num']) ?>">
                                <label>Mobile</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($row['email']) ?>">
                                <label>Email</label>
                            </div>
                        </div>

                    </div>
                </section>
                <!-- Academic Information -->
                <section class="mb-5">
                    <h5 class="text-secondary border-bottom pb-2 mb-3 fw-semibold">
                        <i class="bi bi-mortarboard me-2"></i>Academic Information
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($row['lrn']) ?>">
                                <label>LRN</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($row['grade_level']) ?>">
                                <label>Grade Level</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($row['section']) ?>">
                                <label>Section</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($row['student_type']) ?>">
                                <label>Student Type</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($row['school_year']) ?>">
                                <label>School Year</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($row['strand']) ?>">
                                <label>Strand</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($row['status']) ?>">
                                <label>Status</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($row['prev_school']) ?>">
                                <label>Previous School</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($row['general_avg']) ?>">
                                <label>General Average</label>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- Family/Guardian Information -->
                <section>
                    <h5 class="text-secondary border-bottom pb-2 mb-3 fw-semibold">
                        <i class="bi bi-people-fill me-2"></i>Family / Guardian Information
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($row['father_name']) . ' (' . htmlspecialchars($row['father_occupation']) . ')' ?>">
                                <label>Father</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($row['mother_name']) . ' (' . htmlspecialchars($row['mother_occupation']) . ')' ?>">
                                <label>Mother</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($row['guardian']) ?>">
                                <label>Guardian</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($row['guardian_relationship']) . ', ' . htmlspecialchars($row['guardian_contact']) ?>">
                                <label>Relationship & Contact</label>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <!-- Modern UI: Requires Bootstrap 5 and Bootstrap Icons -->
        <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"> -->
        <style>
            .form-floating>.form-control-plaintext:read-only {
                background: transparent;
                border: none;
                padding-left: 10;
                font-size: 1rem;
                color: #212529;
            }

            .form-floating>label {
                opacity: .7;
                font-size: .95rem;
            }

            .card {
                border-radius: 1.5rem !important;
            }

            @media (max-width: 767px) {
                .container {
                    padding: 0 0.5rem !important;
                }

                .card-body {
                    padding: 1.5rem !important;
                }
            }
        </style>

<?php
    } else {
        echo "<div class='alert alert-danger'>Student not found.</div>";
    }
    $stmt->close();
    $conn->close();
} else {
    echo "<div class='alert alert-warning'>Invalid request.</div>";
}
?>

<script>
    $(document).on('click', '.edit-student-btn', function() {
        var studentId = $(this).data('id');
        // Fetch student data as JSON
        $.ajax({
            url: 'php_functions/get_student_info.php',
            type: 'GET',
            data: {
                id: studentId,
                mode: 'edit'
            }, // mode=edit for JSON response
            dataType: 'json',
            success: function(data) {
                // Populate modal fields
                $('#addStudentModal input[name="student_id"]').val(data.student_id);
                $('#addStudentModal input[name="fname"]').val(data.fname);
                $('#addStudentModal input[name="mname"]').val(data.mname);
                $('#addStudentModal input[name="lname"]').val(data.lname);
                $('#addStudentModal input[name="suffix"]').val(data.suffix);
                $('#addStudentModal input[name="dbirth"]').val(data.dbirth);
                $('#addStudentModal select[name="sex"]').val(data.sex);
                $('#addStudentModal input[name="complete_address"]').val(data.complete_address);
                $('#addStudentModal input[name="barangay"]').val(data.barangay);
                $('#addStudentModal input[name="city_municipality"]').val(data.city_municipality);
                $('#addStudentModal input[name="province"]').val(data.province);
                $('#addStudentModal input[name="zipcode"]').val(data.zipcode);
                $('#addStudentModal input[name="mobile_num"]').val(data.mobile_num);
                $('#addStudentModal input[name="email"]').val(data.email);
                $('#addStudentModal input[name="lrn"]').val(data.lrn);
                $('#addStudentModal select[name="grade_level"]').val(data.grade_level).trigger('change');
                $('#addStudentModal input[name="section"]').val(data.section);
                $('#addStudentModal select[name="student_type"]').val(data.student_type);
                $('#addStudentModal input[name="school_year"]').val(data.school_year);
                $('#addStudentModal select[name="strand"]').val(data.strand);
                $('#addStudentModal input[name="prev_school"]').val(data.prev_school);
                $('#addStudentModal input[name="general_avg"]').val(data.general_avg);
                $('#addStudentModal input[name="father_name"]').val(data.father_name);
                $('#addStudentModal input[name="father_occupation"]').val(data.father_occupation);
                $('#addStudentModal input[name="mother_name"]').val(data.mother_name);
                $('#addStudentModal input[name="mother_occupation"]').val(data.mother_occupation);
                $('#addStudentModal input[name="guardian"]').val(data.guardian);
                $('#addStudentModal input[name="guardian_relationship"]').val(data.guardian_relationship);
                $('#addStudentModal input[name="guardian_contact"]').val(data.guardian_contact);

                $('#addStudentModal').modal('show');
            }
        });
    });
</script>