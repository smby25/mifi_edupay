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
    $escScholarInfo .= htmlspecialchars($student['esc_stat']);
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
$imagePath = '../assets/images/header/malindig_header_pdf_v2.jpg';
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
    $pdf->SetFont('arial', 'B', 12);
    $pdf->SetFillColor(200, 200, 200);
    $pdf->Cell(70, 10, 'TOTAL', 1, 0, '', 1);
    $pdf->Cell(50, 10, '₱' . number_format($total_amount_sum, 2), 1, 0, 'R', 1);
    $pdf->Cell(50, 10, '₱' . number_format($remaining_balance_sum, 2), 1, 1, 'R', 1);


$uponEnrollment = 500.00;
$escVoucherAmount = 9000.00;

// Check the ESC status
$escStatus = strtoupper(trim($student['esc_stat']));

$pdf->Ln(10); // Add spacing

if ($escStatus === 'ESC') {
    // ESC Breakdown
    $installmentAmount = ($total_amount_sum - $uponEnrollment) / 10;

    $pdf->SetFont('arial', 'B', 12);
    $pdf->Cell(0, 10, 'With ESC Voucher Installment Breakdown', 0, 1, 'L');

    $pdf->SetFont('arial', '', 11);
    $pdf->Cell(50, 8, 'Upon Enrollment:', 0, 0, 'L');
    $pdf->Cell(0, 8, '₱ ' . number_format($uponEnrollment, 2), 0, 1, 'L');

    $pdf->Cell(50, 8, 'Installment (June to March):', 0, 0, 'L');
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $installmentText = '₱ ' . number_format($installmentAmount, 2) . ' x 10';
    $pdf->Cell(0, 8, $installmentText, 0, 1, 'L');
    $underlineWidth = $pdf->GetStringWidth($installmentText) + 15;
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->Line((float)$x, (float)$y + 8.5, (float)$x + (float)$underlineWidth, (float)$y + 8.5);

    // Total
    $pdf->Cell(50, 8, '', 0, 0);
    $pdf->Cell(0, 8, '₱ ' . number_format($total_amount_sum, 2), 0, 1, 'L');

} elseif ($escStatus === 'NON-ESC') {
    // NON-ESC Breakdown (add ESC voucher value to total)
    $adjustedTotal = $total_amount_sum + $escVoucherAmount;
    $installmentAmount = ($adjustedTotal - $uponEnrollment) / 10;

    $pdf->SetFont('arial', 'B', 12);
    $pdf->Cell(0, 10, 'Full Tuition Breakdown (Non-ESC)', 0, 1, 'L');

    $pdf->SetFont('arial', '', 11);
    $pdf->Cell(50, 8, 'Upon Enrollment:', 0, 0, 'L');
    $pdf->Cell(0, 8, '₱ ' . number_format($uponEnrollment, 2), 0, 1, 'L');

    $pdf->Cell(50, 8, 'ESC:', 0, 0, 'L');
    $pdf->Cell(0, 8, '₱ ' . number_format($escVoucherAmount, 2), 0, 1, 'L');

    $pdf->Cell(50, 8, 'Installment (June to March):', 0, 0, 'L');
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $installmentText = '₱ ' . number_format($installmentAmount, 2) . ' x 10';
    $pdf->Cell(0, 8, $installmentText, 0, 1, 'L');
    $underlineWidth = $pdf->GetStringWidth($installmentText) + 15;
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->Line((float)$x, (float)$y + 8.5, (float)$x + (float)$underlineWidth, (float)$y + 8.5);

    // Total with voucher included
    $pdf->Cell(50, 8, '', 0, 0);
    $pdf->Cell(0, 8, '₱ ' . number_format($adjustedTotal, 2), 0, 1, 'L');
} else {
    // ESC/Alumni Scholar Breakdown
    $installmentAmount = ($total_amount_sum - $uponEnrollment) / 10;
    $escAlumniAmount = 0; // No ESC voucher for Alumni Scholar

    $pdf->SetFont('arial', 'B', 12);
    $pdf->Cell(0, 10, 'ESC/Alumni Scholar Installment Breakdown', 0, 1, 'L');

    $pdf->SetFont('arial', '', 11);
    $pdf->Cell(50, 8, 'Upon Enrollment:', 0, 0, 'L');
    $pdf->Cell(0, 8, '₱ ' . number_format($uponEnrollment, 2), 0, 1, 'L');

    $pdf->Cell(50, 8, 'Installment (June to March):', 0, 0, 'L');
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $installmentText = '₱ ' . number_format($installmentAmount, 2) . ' x 10';
    $pdf->Cell(0, 8, $installmentText, 0, 1, 'L');
    $underlineWidth = $pdf->GetStringWidth($installmentText) + 15;
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->Line((float)$x, (float)$y + 8.5, (float)$x + (float)$underlineWidth, (float)$y + 8.5);

    // Total
    $pdf->Cell(50, 8, '', 0, 0);
    $pdf->Cell(0, 8, '₱ ' . number_format($escAlumniAmount, 2), 0, 1, 'L');
}


//     // ESC Installment Note
// if (trim($student['esc_stat']) === 'ESC') {
//     $pdf->Ln(10); // Add some space
    
//     $uponEnrollment = 500.00;
//     $installmentAmount = ($total_amount_sum - $uponEnrollment)/ 10;
    

//     // Section Title
//     $pdf->SetFont('arial', 'B', 12);
//     $pdf->Cell(0, 10, 'With ESC Voucher Installment Breakdown', 0, 1, 'L');

//     // Upon Enrollment
//     $pdf->SetFont('arial', '', 11);
//     $pdf->Cell(50, 8, 'Upon Enrollment:', 0, 0, 'L');
//     $pdf->Cell(0, 8, '₱ 500.00', 0, 1, 'L');

//     // Installment (June to March) with a longer underline under the amount
//     $pdf->Cell(50, 8, 'Installment (June to March):', 0, 0, 'L');
//     // Save current X/Y
//     $x = floatval($pdf->GetX());
//     $y = floatval($pdf->GetY());
//     $installmentText = '₱ ' . number_format($installmentAmount, 2) . ' x 10';
//     $pdf->Cell(0, 8, $installmentText, 0, 1, 'L');
//     // Draw a longer underline under the amount (add 15mm to width)
//     $underlineWidth = $pdf->GetStringWidth($installmentText) + 15;
//     $pdf->SetDrawColor(0, 0, 0);
//     $pdf->Line(floatval($x), floatval($y) + 8.5, floatval($x) + floatval($underlineWidth), floatval($y) + 8.5);

//     // Total amount directly below
//     $pdf->SetFont('arial', '', 11);
//     $pdf->Cell(50, 8, '', 0, 0); // spacer again
//     $pdf->Cell(0, 8, '₱ ' . number_format($total_amount_sum, 2), 0, 1, 'L');
// }

// $pdf->Ln(10); // Add some space
// $tenmonths = 10; // Number of months for installment
// $uponEnrollment = 500.00;
// $escVoucherAmount = 9000.00; // fixed voucher value
// $installmentAmount = $total_amount_sum / 10;
// $installmentAmount_nonesc = ($total_amount_sum + $escVoucherAmount - $uponEnrollment) / $tenmonths;
// $total_nonesc = $total_amount_sum + $escVoucherAmount - $uponEnrollment;

// // Check if student is ESC or Alumni Scholar
// $isFullScholar = (trim($student['esc_stat']) === 'ESC') || (strtoupper(trim($student['scholar'])) === 'ALUMNI SCHOLAR');

// if ($isFullScholar) {
//     // ESC or Alumni Scholar
//     $pdf->SetFont('arial', 'B', 12);
//     $pdf->Cell(0, 10, 'With ESC Voucher Installment Breakdown', 0, 1, 'L');

//     $pdf->SetFont('arial', '', 11);
//     $pdf->Cell(50, 8, 'Upon Enrollment:', 0, 0, 'L');
//     $pdf->Cell(0, 8, '₱ 500.00', 0, 1, 'L');

//     $pdf->Cell(50, 8, 'Installment (June to March):', 0, 0, 'L');
//     $x = floatval($pdf->GetX());
//     $y = floatval($pdf->GetY());
//     $installmentText = '₱ ' . number_format($installmentAmount, 2) . ' x 10';
//     $pdf->Cell(0, 8, $installmentText, 0, 1, 'L');
//     $underlineWidth = floatval($pdf->GetStringWidth($installmentText)) + 15.0;
//     $pdf->SetDrawColor(0, 0, 0);
//     $pdf->Line($x, $y + 8.5, $x + $underlineWidth, $y + 8.5);

//     // Total shown as ₱ 0.00
//     $pdf->SetFont('arial', '', 11);
//     $pdf->Cell(50, 8, '', 0, 0);
//     $pdf->Cell(0, 8, '₱ 0.00', 0, 1, 'L');

// } else {
//     // NON-ESC
//     $pdf->SetFont('arial', 'B', 12);
//     $pdf->Cell(0, 10, 'Full Tuition Breakdown (Non-ESC)', 0, 1, 'L');

//     $pdf->SetFont('arial', '', 11);
//     $pdf->Cell(50, 8, 'Upon Enrollment:', 0, 0, 'L');
//     $pdf->Cell(0, 8, '₱ 500.00', 0, 1, 'L');

//     $pdf->Cell(50, 8, 'ESC:', 0, 0, 'L');
//     $pdf->Cell(0, 8, '₱ ' . number_format($escVoucherAmount, 2), 0, 1, 'L');

//     $pdf->Cell(50, 8, 'Installment (June to March):', 0, 0, 'L');
//     $x = floatval($pdf->GetX());
//     $y = floatval($pdf->GetY());
//     $installmentText = '₱ ' . number_format($installmentAmount_nonesc, 2) . ' x 10';
//     $pdf->Cell(0, 8, $installmentText, 0, 1, 'L');
//     $underlineWidth = floatval($pdf->GetStringWidth($installmentText)) + 15.0;
//     $pdf->SetDrawColor(0, 0, 0);
//     $pdf->Line(floatval($x), floatval($y) + 8.5, floatval($x) + floatval($underlineWidth), floatval($y) + 8.5);


//     $pdf->SetFont('arial', '', 11);
//     $pdf->Cell(50, 8, '', 0, 0);
//     $pdf->Cell(0, 8, '₱ ' . number_format($total_nonesc, 2), 0, 1, 'L');
// }



// Add footer note
$pdf->Ln(10);
$pdf->SetFont('arial', 'I', 9);
$pdf->SetTextColor(90, 90, 90);
$pdf->MultiCell(0, 8, 'Note: This ledger is for reference only. For official records, please contact the school administration.', 0, 'L');
}

// Sanitize full name for filename
$sanitizedFullName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $fullName);
$pdf->Output("Student_Payment_Ledger_{$sanitizedFullName}.pdf", 'I');
exit;
?>