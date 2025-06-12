<?php
// filepath: c:\xampp\htdocs\mifi_edupay\php_functions\export_students_excel.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['grade_level'])) {
    die('Invalid request.');
}

$grade_level = $_POST['grade_level'];

// Database connection
require_once '../../conn.php';

// Query students by grade level, order by lname, fname
$stmt = $conn->prepare("SELECT student_id, fname, mname, lname, lrn, grade_level, section, strand, status FROM students WHERE grade_level = ? AND status = 'active' ORDER BY lname ASC, fname ASC");
$stmt->bind_param("s", $grade_level);
$stmt->execute();
$result = $stmt->get_result();

// Set headers for CSV download
$filename = "students_grade{$grade_level}_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=\"$filename\"");

// Open output stream
$output = fopen('php://output', 'w');

// Output CSV header
$header = ['Full Name', 'LRN', 'Grade Level', 'Section'];
if ($grade_level == '11' || $grade_level == '12') {
    $header[] = 'Strand';
}
fputcsv($output, $header);

// Output CSV rows
while ($row = $result->fetch_assoc()) {
    // Combine Last Name, First Name, Middle Initial
    $middle_initial = !empty($row['mname']) ? strtoupper(substr($row['mname'], 0, 1)) . '.' : '';
    $full_name = "{$row['lname']}, {$row['fname']}" . ($middle_initial ? " {$middle_initial}" : '');

    $data = [
        $full_name,
        $row['lrn'],
        $row['grade_level'],
        $row['section'],
    ];
    if ($grade_level == '11' || $grade_level == '12') {
        $data[] = $row['strand'];
    }
    fputcsv($output, $data);
}

fclose($output);
$stmt->close();
$conn->close();
exit;
?>
