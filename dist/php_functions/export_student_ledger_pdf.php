<?php
session_start();

// Use fullname or fallback to username for "Prepared by"
$preparedBy = isset($_SESSION['fullname']) 
    ? htmlspecialchars($_SESSION['fullname']) 
    : (isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Unknown');

require_once('../../conn.php');
require_once('../tcpdf/tcpdf.php');

class MYPDF extends TCPDF {
    public $preparedBy = '';

    public function Footer() {
        $this->SetY(-15); // 15 mm from bottom
        $this->SetFont('Arial', 'I', 9);
        $this->Cell(0, 10, 'Prepared by: ' . $this->preparedBy, 0, 0, 'L');
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'R');
    }
}

if (!isset($_GET['student_id']) || empty($_GET['student_id'])) {
    die('Student ID not specified.');
}

$student_id = $_GET['student_id'];

// Fetch student info
$stmt = $conn->prepare("SELECT fname, mname, lname, grade_level, section, strand, esc_stat, scholar FROM students WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Student not found.');
}

$student = $result->fetch_assoc();
$fullName = $student['lname'] . ', ' . $student['fname'] . ' ' . strtoupper(substr($student['mname'], 0, 1)) . '.';
$gradeInfo = "Grade " . $student['grade_level'];
if (!empty($student['section'])) $gradeInfo .= " - " . $student['section'];
if (!empty($student['strand'])) $gradeInfo .= " | " . $student['strand'];

// Build ESC/Scholar info only if at least one is present
$escScholarInfo = '';
if (!empty($student['esc_stat'])) {
    $escScholarInfo .= 'ESC';
}
if (!empty($student['scholar'])) {
    if (!empty($escScholarInfo)) {
        $escScholarInfo .= ' | ';
    }
    $escScholarInfo .= 'Under the Scholarship of ' . htmlspecialchars($student['scholar']);
}

// If both are empty, don't show the row later
$showEscScholarInfo = !empty($escScholarInfo);


// Create PDF
$pdf = new MYPDF();
$pdf->preparedBy = $preparedBy;

// Document Info
$pdf->SetCreator('Student Ledger Export');
$pdf->SetAuthor('Malindig Institute Foundation Inc.');
$pdf->SetTitle("Student Payment Ledger - $fullName");
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();

// Add image header
$imagePath = '../assets/images/header/malindig_header_pdf.jpg';
$pdf->Image($imagePath, 15, 10, 180, 30, '', '', 'T', false, 300);

// Adjust Y after header
$pdf->SetY(40);

// Title
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Student Payment Ledger', 0, 1, 'C');
$pdf->Ln(5);

// Student Info
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 8, $fullName, 0, 1);
$pdf->Cell(0, 8, $gradeInfo, 0, 1);
$pdf->Cell(0, 8, $escScholarInfo, 0, 1);
$pdf->Ln(5);

// Payment Table Header
$pdf->SetFont('arial', 'B', 11);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(70, 10, 'Payment Type', 1, 0, 'C', 1);
$pdf->Cell(50, 10, 'Total Amount', 1, 0, 'C', 1);
$pdf->Cell(50, 10, 'Remaining Balance', 1, 1, 'C', 1);

// Fetch payment rows
$payments_stmt = $conn->prepare("
    SELECT 
        p.payment_type,
        p.amount AS total_amount,
        (p.amount - IFNULL(SUM(sp.amount_paid), 0)) AS remaining_balance
    FROM payments p
    LEFT JOIN student_payments sp ON sp.payment_id = p.id AND sp.student_id = ?
    WHERE p.target_grade = ? OR p.student_id = ?
    GROUP BY p.id
    ORDER BY p.payment_type ASC
");
$payments_stmt->bind_param("iss", $student_id, $student['grade_level'], $student_id);
$payments_stmt->execute();
$payments_result = $payments_stmt->get_result();

$pdf->SetFont('Arial', '', 10);

if ($payments_result->num_rows === 0) {
    $pdf->Cell(0, 10, 'No payments found.', 1, 1, 'C');
} else {
    $total_amount_sum = 0;
    $remaining_balance_sum = 0;

    while ($row = $payments_result->fetch_assoc()) {
        $total = $row['total_amount'] ?? 0;
        $remaining = $row['remaining_balance'] ?? 0;

        $pdf->Cell(70, 10, $row['payment_type'], 1);
        $pdf->Cell(50, 10, '₱' . number_format($total, 2), 1, 0, 'R');
        $pdf->Cell(50, 10, '₱' . number_format($remaining, 2), 1, 1, 'R');

        $total_amount_sum += $total;
        $remaining_balance_sum += $remaining;
    }

    // Add Grand Total Row
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(200, 200, 200);
    $pdf->Cell(70, 10, 'TOTAL', 1, 0, '', 1);
    $pdf->Cell(50, 10, '₱' . number_format($total_amount_sum, 2), 1, 0, 'R', 1);
    $pdf->Cell(50, 10, '₱' . number_format($remaining_balance_sum, 2), 1, 1, 'R', 1);
}

// Sanitize full name for filename
$sanitizedFullName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $fullName);
$pdf->Output("Student_Payment_Ledger_{$sanitizedFullName}.pdf", 'I');
exit;
?>