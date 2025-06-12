<?php
include "../../conn.php";

$student_id = $_POST['student_id'] ?? '';
$payment_id = $_POST['payment_id'] ?? '';
$amount_paid = $_POST['amount_paid'] ?? '';
$paid_by = $_POST['paid_by'] ?? '';

if ($student_id && $payment_id && $amount_paid > 0 && $paid_by !== '') {
    $stmt = $conn->prepare("INSERT INTO student_payments (student_id, payment_id, amount_paid, paid_by, date_paid) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iids", $student_id, $payment_id, $amount_paid, $paid_by);
    if ($stmt->execute()) {
        echo "success";
    } else {
        http_response_code(500);
        echo "Database error";
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo "Invalid input";
}
$conn->close();
?>