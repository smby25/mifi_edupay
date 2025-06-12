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
    // Get all days of the current month
    $days_in_month = date('t');
    $current_year = date('Y');
    $current_month = date('m');
    for ($d = 1; $d <= $days_in_month; $d++) {
        $labels[] = date('M d', strtotime("$current_year-$current_month-$d"));
        $data[] = 0;
    }
    // Fetch actual data
    $res = $conn->query("
        SELECT DAY(date_paid) as day, SUM(amount_paid) as total
        FROM student_payments
        WHERE YEAR(date_paid) = YEAR(CURDATE()) AND MONTH(date_paid) = MONTH(CURDATE())
        GROUP BY DAY(date_paid)
        ORDER BY DAY(date_paid)
    ");
    while ($row = $res->fetch_assoc()) {
        $index = (int)$row['day'] - 1;
        $data[$index] = $row['total'] ?? 0;
    }
} elseif ($type === 'year') {
    // Get all months of the current year
    for ($m = 1; $m <= 12; $m++) {
        $labels[] = date('M', mktime(0, 0, 0, $m, 1));
        $data[] = 0;
    }
    // Fetch actual data
    $res = $conn->query("
        SELECT MONTH(date_paid) as month, SUM(amount_paid) as total
        FROM student_payments
        WHERE YEAR(date_paid) = YEAR(CURDATE())
        GROUP BY MONTH(date_paid)
        ORDER BY MONTH(date_paid)
    ");
    while ($row = $res->fetch_assoc()) {
        $index = (int)$row['month'] - 1;
        $data[$index] = $row['total'] ?? 0;
    }
}
echo json_encode(['labels' => $labels, 'data' => $data]);
?>