<?php
include "../../conn.php";
$type = $_GET['type'] ?? '7days';
$labels = [];
$data = [];

if ($type === '7days') {
    $res = $conn->query("
        SELECT DATE(date_paid) as label, SUM(amount_paid) as total
        FROM student_payments
        WHERE date_paid >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE(date_paid)
        ORDER BY DATE(date_paid)
    ");
    while ($row = $res->fetch_assoc()) {
        $labels[] = date('M d', strtotime($row['label']));
        $data[] = $row['total'] ?? 0;
    }
} elseif ($type === 'month') {
    $res = $conn->query("
        SELECT DATE_FORMAT(date_paid, '%b %d') as label, SUM(amount_paid) as total
        FROM student_payments
        WHERE YEAR(date_paid) = YEAR(CURDATE()) AND MONTH(date_paid) = MONTH(CURDATE())
        GROUP BY DATE(date_paid)
        ORDER BY DATE(date_paid)
    ");
    while ($row = $res->fetch_assoc()) {
        $labels[] = $row['label'];
        $data[] = $row['total'] ?? 0;
    }
} elseif ($type === 'year') {
    $res = $conn->query("
        SELECT DATE_FORMAT(date_paid, '%b') as label, SUM(amount_paid) as total
        FROM student_payments
        WHERE YEAR(date_paid) = YEAR(CURDATE())
        GROUP BY MONTH(date_paid)
        ORDER BY MONTH(date_paid)
    ");
    while ($row = $res->fetch_assoc()) {
        $labels[] = $row['label'];
        $data[] = $row['total'] ?? 0;
    }
}
echo json_encode(['labels' => $labels, 'data' => $data]);
?>