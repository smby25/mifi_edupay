<?php
require_once('../../conn.php');

if (!isset($_GET['grade']) || empty($_GET['grade'])) {
    die('Grade not specified.');
}

$grade = $_GET['grade'];

// Set headers to download CSV
header('Content-Type: text/csv');
header("Content-Disposition: attachment; filename=Grade_{$grade}_student_ledger.csv");
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// CSV Header
fputcsv($output, ['Full Name', 'Grade & Section / Strand', 'Remaining Balance']);

// Fetch students
$stmt = $conn->prepare("SELECT student_id, fname, mname, lname, section, strand FROM students WHERE grade_level = ? AND status = 'active'");
$stmt->bind_param("s", $grade);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $student_id = $row['student_id'];
    $full_name = $row['lname'] . ', ' . $row['fname'] . ' ' . strtoupper(substr($row['mname'], 0, 1)) . '.';

    $section = $row['section'];
    $strand = $row['strand'];
    $gradeSectionStrand = "Grade $grade";
    if (!empty($section)) $gradeSectionStrand .= " - $section";
    if (!empty($strand)) $gradeSectionStrand .= " | $strand";

    // Get balance
    $bal_stmt = $conn->prepare("
        SELECT 
            IFNULL((
                SELECT SUM(p.amount) FROM payments p
                WHERE p.target_grade = ? OR p.student_id = ?
            ), 0)
            -
            IFNULL((
                SELECT SUM(sp.amount_paid) FROM student_payments sp
                WHERE sp.student_id = ?
            ), 0) AS balance
    ");
    $bal_stmt->bind_param("sii", $grade, $student_id, $student_id);
    $bal_stmt->execute();
    $bal_result = $bal_stmt->get_result();
    $balance = $bal_result->fetch_assoc()['balance'] ?? 0;
    $bal_stmt->close();

    fputcsv($output, [$full_name, $gradeSectionStrand, number_format($balance, 2)]);
}

fclose($output);
exit;
?>
