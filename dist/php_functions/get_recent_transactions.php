<?php
include "../../conn.php";

$student_id = $_GET['student_id'] ?? '';
$show_all = isset($_GET['all']) && $_GET['all'] === 'true';

$data = [];

if ($student_id) {
    $sql = "SELECT sp.date_paid, p.payment_type, sp.amount_paid, sp.paid_by
            FROM student_payments sp
            JOIN payments p ON sp.payment_id = p.id
            WHERE sp.student_id = ?
            ORDER BY sp.date_paid DESC";
    
    if (!$show_all) {
        $sql .= " LIMIT 10";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();
}

echo json_encode($data);
$conn->close();
?>
