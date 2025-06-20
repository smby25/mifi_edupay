<?php
include "../../conn.php";

$student_id   = $_POST['student_id'] ?? '';
$payment_id   = $_POST['payment_id'] ?? '';
$amount_paid  = $_POST['amount_paid'] ?? '';
$paid_by      = $_POST['paid_by'] ?? null;        // Optional
$description  = $_POST['description'] ?? null;    // Optional
$date_paid    = $_POST['date_paid'] ?? '';        // Required

if ($student_id && $payment_id && $amount_paid > 0 && $date_paid) {
    $stmt = $conn->prepare("INSERT INTO student_payments (student_id, payment_id, amount_paid, paid_by, description, date_paid) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iidsss", $student_id, $payment_id, $amount_paid, $paid_by, $description, $date_paid);

    if ($stmt->execute()) {
        echo "success";
    } else {
        http_response_code(500);
        echo "Database error: " . $stmt->error;
    }

    $stmt->close();
} else {
    http_response_code(400);
    echo "Invalid input";
}

$conn->close();
?>
