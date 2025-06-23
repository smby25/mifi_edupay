<?php include "../conn.php"; ?>

<!-- Add High School Student Modal (Modern + Clean UI) -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <form class="modal-content p-4 rounded-4 shadow-sm bg-white needs-validation" novalidate autocomplete="off" method="POST" action="php_functions/add_student.php">
            <!-- Modal Header -->
            <div class="modal-header border-0">
                <h1 class="modal-title fw-semibold d-flex align-items-center" id="addStudentModalLabel">
                    <i class="bi bi-person-plus-fill me-2 text-success"></i> Add Student
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Reset Button -->
            <div class="d-flex justify-content-end mb-2">
                <button type="reset" class="btn btn-outline-danger btn-sm rounded-circle d-flex align-items-center justify-content-center" id="resetFormBtn" style="width:32px;height:32px;" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body pt-0">
                <!-- Section: Personal Information -->
                <h6 class="fw-semibold text-primary border-bottom pb-2 mb-3">Personal Information</h6>
                <div class="row g-3">
                    <input type="hidden" name="student_id" id="student_id">
                    <div class="col-md-4">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="fname" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Middle Name</label>
                        <input type="text" class="form-control" name="mname">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="lname" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Suffix</label>
                        <input type="text" class="form-control" name="suffix" placeholder="e.g., Jr., III">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="dbirth" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sex <span class="text-danger">*</span></label>
                        <select class="form-select" name="sex" required>
                            <option value="" disabled selected>Select</option>
                            <option>Male</option>
                            <option>Female</option>
                        </select>
                    </div>
                </div>

                <!-- Section: Contact & Address -->
                <h6 class="fw-semibold text-primary border-bottom pt-4 pb-2 mb-3">Contact & Address</h6>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Complete Address</label>
                        <input type="text" class="form-control" name="complete_address">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Barangay</label>
                        <input type="text" class="form-control" name="barangay">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">City/Municipality</label>
                        <input type="text" class="form-control" name="city_municipality">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Province</label>
                        <input type="text" class="form-control" name="province">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ZIP Code</label>
                        <input type="text" class="form-control" name="zipcode">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" class="form-control" name="mobile_num">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                </div>

                <!-- Section: Academic Info -->
                <h6 class="fw-semibold text-primary border-bottom pt-4 pb-2 mb-3">Academic Information</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">LRN</label>
                        <input type="text" class="form-control" name="lrn" placeholder="e.g., 403392150071">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                        <select class="form-select" id="gradeLevelSelect" name="grade_level" required>
                            <option value="" disabled selected>Select</option>
                            <option value="Nursery">Nursery</option>
                            <option value="Kindery">Kinder</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Section <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="section" placeholder="e.g., Diamond" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Student Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="student_type" required>
                            <option value="" disabled selected>Select</option>
                            <option>Regular</option>
                            <option>Transferee</option>
                            <option>Balik-Aral</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">School Year</label>
                        <input type="text" class="form-control" name="school_year" placeholder="e.g., 2024-2025">
                    </div>
                    <!-- WRAPPER WITH ID -->
                    <div class="col-md-4 d-none" id="strandWrapper">
                        <label class="form-label">Strand</label>
                        <select class="form-select" name="strand">
                            <option value="" disabled selected>Select</option>
                            <option>STEM</option>
                            <option>HUMSS</option>
                            <option>ABM</option>
                            <option>GAS</option>
                            <option>TVL</option>
                            <option>CSS</option>
                            <option>Others</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Previous School Attended</label>
                        <input type="text" class="form-control" name="prev_school">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">General Average</label>
                        <input type="text" class="form-control" name="general_avg">
                    </div>
                </div>

                <!-- Section: Family Background -->
                <h6 class="fw-semibold text-primary border-bottom pt-4 pb-2 mb-3">Family Background</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Father’s Name</label>
                        <input type="text" class="form-control" name="father_name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Father’s Occupation</label>
                        <input type="text" class="form-control" name="father_occupation">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mother’s Name</label>
                        <input type="text" class="form-control" name="mother_name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mother’s Occupation</label>
                        <input type="text" class="form-control" name="mother_occupation">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Guardian’s Name</label>
                        <input type="text" class="form-control" name="guardian">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="font-size:12px;">Relationship to Guardian</label>
                        <input type="text" class="form-control" name="guardian_relationship">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Guardian’s Contact</label>
                        <input type="text" class="form-control" name="guardian_contact">
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 pt-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success rounded-pill px-4">Add Student</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const gradeLevelSelect = document.getElementById("gradeLevelSelect");
        const strandWrapper = document.getElementById("strandWrapper");

        gradeLevelSelect.addEventListener("change", function() {
            const selectedGrade = this.value;
            if (selectedGrade === "11" || selectedGrade === "12") {
                strandWrapper.classList.remove("d-none");
            } else {
                strandWrapper.classList.add("d-none");
            }
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const gradeLevelSelect = document.getElementById("gradeLevelSelect");
        const strandWrapper = document.getElementById("strandWrapper");
        const studentIdInput = document.getElementById("student_id");
        const submitBtn = document.querySelector('#addStudentModal button[type="submit"]');

        gradeLevelSelect.addEventListener("change", function() {
            const selectedGrade = this.value;
            if (selectedGrade === "11" || selectedGrade === "12") {
                strandWrapper.classList.remove("d-none");
            } else {
                strandWrapper.classList.add("d-none");
            }
        });

        // Listen for modal show event
        $('#addStudentModal').on('show.bs.modal', function () {
            setTimeout(function() { // Wait for fields to be populated
                if (studentIdInput.value) {
                    submitBtn.textContent = "Update Student";
                } else {
                    submitBtn.textContent = "Add Student";
                }
            }, 100); // Adjust delay if needed
        });

        // Optional: Reset button text when modal is hidden
        $('#addStudentModal').on('hidden.bs.modal', function () {
            submitBtn.textContent = "Add Student";
        });
    });
</script>


