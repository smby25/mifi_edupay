<?php
include "../../conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $batch_name = $_POST['batch_name'];
    $target_grade = isset($_POST['target_grade']) ? $_POST['target_grade'] : null;
    $payment_types = $_POST['payment_type'];
    $amounts = $_POST['amount'];
    $payment_id = isset($_POST['payment_id']) ? intval($_POST['payment_id']) : 0;

    // Handle student input
    $student_id = null;
    if (isset($_POST['student_id']) && !empty($_POST['student_id'])) {
        $input = trim($_POST['student_id']);

        if (is_numeric($input)) {
            $student_id = $input; // existing student
        } else {
            // Assume input is in format: "Lastname, Firstname Middlename"
            $nameParts = explode(',', $input);
            $lname = trim($nameParts[0]);
            $fnameMname = isset($nameParts[1]) ? trim($nameParts[1]) : '';

            $fname = '';
            $mname = '';

            if (!empty($fnameMname)) {
                $parts = explode(' ', $fnameMname);
                $fname = $parts[0];
                $mname = isset($parts[1]) ? $parts[1] : '';
            }

            // Insert new student
            $insertStmt = $conn->prepare("INSERT INTO students (fname, mname, lname) VALUES (?, ?, ?)");
            $insertStmt->bind_param("sss", $fname, $mname, $lname);
            $insertStmt->execute();
            $student_id = $conn->insert_id;
            $insertStmt->close();
        }
    }

    if ($payment_id > 0) {
        // Update mode
        $type = htmlspecialchars(trim($payment_types[0]));
        $amt = floatval($amounts[0]);

        $stmt = $conn->prepare("UPDATE payments SET batch_name=?, target_grade=?, student_id=?, payment_type=?, amount=? WHERE id=?");
        $stmt->bind_param("ssissi", $batch_name, $target_grade, $student_id, $type, $amt, $payment_id);
        $stmt->execute();
        $stmt->close();

        // Update all related student_payments
        $stmt2 = $conn->prepare("UPDATE student_payments SET amount_paid=? WHERE payment_id=?");
        $stmt2->bind_param("di", $amt, $payment_id);
        $stmt2->execute();
        $stmt2->close();

        $conn->close();
        header("Location: ../student_fees_sidebar.php?success=1");
        exit();
    } else {
        // Insert mode
        $stmt = $conn->prepare("INSERT INTO payments (batch_name, target_grade, student_id, payment_type, amount, status) VALUES (?, ?, ?, ?, ?, 'active')");

        for ($i = 0; $i < count($payment_types); $i++) {
            $type = htmlspecialchars(trim($payment_types[$i]));
            $amt = floatval($amounts[$i]);

            $stmt->bind_param("ssssd", $batch_name, $target_grade, $student_id, $type, $amt);
            $stmt->execute();
        }

        $stmt->close();
        $conn->close();

        header("Location: ../student_fees_sidebar.php?success=1");
        exit();
    }
}
?>
