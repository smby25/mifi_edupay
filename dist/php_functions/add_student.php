<?php
include "../../conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    function clean_input($data) {
        return htmlspecialchars(trim($data));
    }

    $student_id = isset($_POST['student_id']) ? clean_input($_POST['student_id']) : ''; // Check for update

    $fname = clean_input($_POST['fname']);
    $mname = clean_input($_POST['mname']);
    $lname = clean_input($_POST['lname']);
    $suffix = clean_input($_POST['suffix']);
    $dbirth = clean_input($_POST['dbirth']);
    $sex = clean_input($_POST['sex']);

    $complete_address = clean_input($_POST['complete_address']);
    $barangay = clean_input($_POST['barangay']);
    $city_municipality = clean_input($_POST['city_municipality']);
    $province = clean_input($_POST['province']);
    $zipcode = clean_input($_POST['zipcode']);
    $mobile_num = clean_input($_POST['mobile_num']);
    $email = clean_input($_POST['email']);

    $lrn = clean_input($_POST['lrn']);
    $grade_level = clean_input($_POST['grade_level']);
    $section = clean_input($_POST['section']);
    $student_type = clean_input($_POST['student_type']);
    $school_year = clean_input($_POST['school_year']);
    $strand = isset($_POST['strand']) ? clean_input($_POST['strand']) : null;
    $esc_stat = clean_input($_POST['esc_stat']); // changed from prev_school
    $scholar = isset($_POST['scholar']) ? clean_input($_POST['scholar']) : null;  // changed from general_avg

    $father_name = clean_input($_POST['father_name']);
    $father_occupation = clean_input($_POST['father_occupation']);
    $mother_name = clean_input($_POST['mother_name']);
    $mother_occupation = clean_input($_POST['mother_occupation']);
    $guardian = clean_input($_POST['guardian']);
    $guardian_relationship = clean_input($_POST['guardian_relationship']);
    $guardian_contact = clean_input($_POST['guardian_contact']);

    try {
        if (!empty($student_id)) {
            // UPDATE existing record
            $stmt = $conn->prepare("UPDATE students SET 
                fname=?, mname=?, lname=?, suffix=?, dbirth=?, sex=?,
                complete_address=?, barangay=?, city_municipality=?, province=?, zipcode=?, mobile_num=?, email=?,
                lrn=?, grade_level=?, section=?, student_type=?, school_year=?, strand=?, esc_stat=?, scholar=?,
                father_name=?, father_occupation=?, mother_name=?, mother_occupation=?, guardian=?, guardian_relationship=?, guardian_contact=?
                WHERE student_id=?
            ");

            $stmt->bind_param(
                "ssssssssssssssssssssssssssssi",
                $fname, $mname, $lname, $suffix, $dbirth, $sex,
                $complete_address, $barangay, $city_municipality, $province, $zipcode, $mobile_num, $email,
                $lrn, $grade_level, $section, $student_type, $school_year, $strand, $esc_stat, $scholar,
                $father_name, $father_occupation, $mother_name, $mother_occupation, $guardian, $guardian_relationship, $guardian_contact,
                $student_id
            );
        } else {
            // Check for duplicate
            $dup_stmt = $conn->prepare("SELECT student_id FROM students WHERE fname=? AND mname=? AND lname=? AND lrn=?");
            $dup_stmt->bind_param("ssss", $fname, $mname, $lname, $lrn);
            $dup_stmt->execute();
            $dup_stmt->store_result();
            if ($dup_stmt->num_rows > 0) {
                // Duplicate found, redirect with error
                header("Location: ../student_sidebar.php?duplicate=1");
                exit();
            }
            $dup_stmt->close();

            // INSERT new record
            $stmt = $conn->prepare("INSERT INTO students (
                fname, mname, lname, suffix, dbirth, sex,
                complete_address, barangay, city_municipality, province, zipcode, mobile_num, email,
                lrn, grade_level, section, student_type, school_year, strand, esc_stat, scholar,
                father_name, father_occupation, mother_name, mother_occupation, guardian, guardian_relationship, guardian_contact
            ) VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?
            )");

            $stmt->bind_param(
                "ssssssssssssssssssssssssssss",
                $fname, $mname, $lname, $suffix, $dbirth, $sex,
                $complete_address, $barangay, $city_municipality, $province, $zipcode, $mobile_num, $email,
                $lrn, $grade_level, $section, $student_type, $school_year, $strand, $esc_stat, $scholar,
                $father_name, $father_occupation, $mother_name, $mother_occupation, $guardian, $guardian_relationship, $guardian_contact
            );
        }

        if ($stmt->execute()) {
            header("Location: ../student_sidebar.php?success=1");
            exit();
        } else {
            echo "Error: {$stmt->error}";
        }

        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        echo "Exception occurred: " . $e->getMessage();
    }
} else {
    header("Location: ../student_sidebar.php");
    exit();
}
