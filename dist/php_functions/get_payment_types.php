<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "../../conn.php";

$grade = $_GET['grade'] ?? '';
$student_id = $_GET['student_id'] ?? '';

if ($grade === '' || $student_id === '') {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT payment_type, amount, due_date, id FROM payments WHERE target_grade = ?");
$stmt->bind_param("s", $grade);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    // Get total paid by student for this payment type
    $payment_type = $row['payment_type'];
    $payment_id = $row['id'];
    $paid_stmt = $conn->prepare("SELECT SUM(amount_paid) as paid FROM student_payments WHERE student_id = ? AND payment_id = ?");
    $paid_stmt->bind_param("ii", $student_id, $payment_id);
    $paid_stmt->execute();
    $paid_result = $paid_stmt->get_result();
    $paid = $paid_result->fetch_assoc()['paid'] ?? 0;
    $paid_stmt->close();


    $row['remaining'] = $row['amount'] - $paid;
    $rows[] = $row;
}
echo json_encode($rows);
$stmt->close();
$conn->close();

?>