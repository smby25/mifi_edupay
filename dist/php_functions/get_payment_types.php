<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "../../conn.php";

$grade = $_GET['grade'] ?? '';
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

if (empty($grade) || $student_id === 0) {
    echo json_encode([]);
    exit;
}

// Fetch payments by target grade or student-specific payments
$stmt = $conn->prepare("
    SELECT id, payment_type, amount, due_date, student_id
    FROM payments
    WHERE (target_grade = ? OR student_id = ?)
    AND status = 'active'
");
$stmt->bind_param("si", $grade, $student_id);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];

while ($row = $result->fetch_assoc()) {
    $payment_id = $row['id'];

    // Get total amount paid by student for this specific payment
    $paid_stmt = $conn->prepare("
        SELECT IFNULL(SUM(amount_paid), 0) AS paid
        FROM student_payments
        WHERE student_id = ? AND payment_id = ?
    ");
    $paid_stmt->bind_param("ii", $student_id, $payment_id);
    $paid_stmt->execute();
    $paid_result = $paid_stmt->get_result();
    $paid = $paid_result->fetch_assoc()['paid'] ?? 0;
    $paid_stmt->close();

    // Add calculated values
    $row['paid'] = floatval($paid);
    $row['remaining'] = floatval($row['amount']) - floatval($paid);
    $row['student_id'] = $student_id;

    $rows[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($rows);
?>
