<?php
session_start();

require_once('../../conn.php');
require_once('../tcpdf/tcpdf.php');

/* =========================
   PREPARED BY
========================= */
$preparedBy = $_SESSION['fullname']
    ?? $_SESSION['username']
    ?? 'School Accounting';

/* =========================
   CUSTOM PDF CLASS
========================= */
class MYPDF extends TCPDF {
    public $preparedBy = '';

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 9);
        $this->Cell(0, 10, 'Prepared by: ' . $this->preparedBy, 0, 0, 'L');
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'R');
    }
}

/* =========================
   VALIDATION
========================= */
if (empty($_GET['student_id'])) {
    die('Student ID not specified.');
}
$student_id = (int)$_GET['student_id'];

//ACTUAL PAYMENT RECORDS
$history_stmt = $conn->prepare("
    SELECT 
        sp.date_paid,
        sp.reference_no,
        p.payment_type,
        sp.amount_paid
    FROM student_payments sp
    JOIN payments p ON p.id = sp.payment_id
    WHERE sp.student_id = ? AND p.status != 'inactive'
    ORDER BY sp.date_paid ASC
");
$history_stmt->bind_param("i", $student_id);
$history_stmt->execute();
$payment_history = $history_stmt->get_result();


/* =========================
   FETCH STUDENT INFO
========================= */
$stmt = $conn->prepare("
    SELECT fname, mname, lname, grade_level, section, strand, esc_stat, scholar
    FROM students WHERE student_id = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    die('Student not found.');
}

$fullName = $student['lname'] . ', ' . $student['fname'] . ' ' . strtoupper(substr($student['mname'], 0, 1)) . '.';
$gradeInfo = "Grade {$student['grade_level']}";
if (!empty($student['section'])) $gradeInfo .= " - {$student['section']}";
if (!empty($student['strand']))  $gradeInfo .= " | {$student['strand']}";

/* =========================
   FETCH PAYMENTS
========================= */
$payments_stmt = $conn->prepare("
    SELECT p.payment_type, p.amount,
           IFNULL(SUM(sp.amount_paid),0) AS paid
    FROM payments p
    LEFT JOIN student_payments sp
        ON sp.payment_id = p.id AND sp.student_id = ?
    WHERE (p.target_grade = ? OR p.student_id = ?) AND p.status != 'inactive'
    GROUP BY p.id
    ORDER BY p.payment_type
");
$payments_stmt->bind_param("iss", $student_id, $student['grade_level'], $student_id);
$payments_stmt->execute();
$payments = $payments_stmt->get_result();

/* =========================
   PDF INIT
========================= */
$pdf = new MYPDF();
$pdf->preparedBy = $preparedBy;

$pdf->SetCreator('EduPay System');
$pdf->SetAuthor('Malindig Institute Foundation Inc.');
$pdf->SetTitle('Student Financial Statement');

$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();

/* =========================
   HEADER IMAGE
========================= */
$pdf->Image('../assets/images/header/malindig_header_main.jpg', 15, 10, 180);
$pdf->SetY(45);

/* =========================
   LETTER HEADER
========================= */
$pdf->SetFont('dejavusans', '', 9);
date_default_timezone_set('Asia/Manila');

// Get current month in Tagalog
$tagalogMonths = [
    'Enero', 'Pebrero', 'Marso', 'Abril', 'Mayo', 'Hunyo',
    'Hulyo', 'Agosto', 'Setyembre', 'Oktubre', 'Nobyembre', 'Disyembre'
];
$currentMonthTagalog = $tagalogMonths[date('n') - 1];

$pdf->Cell(0, 8, $currentMonthTagalog . ' ' . date('j, Y'), 0, 1);
$pdf->Ln(3);

$pdf->MultiCell(0, 8, "Magulang at Tagapag-alaga,", 0, 'L');
$pdf->Ln(3);

$pdf->writeHTML(
    "Ang liham na ito ay inihanda upang magbigay ng komprehensibong update patungkol sa Down Payment, Tuition Fee, atbp. para kay <b>" . htmlspecialchars($fullName) . "</b> sa Malindig Institute Foundation Inc. ngayong Buwan ng " . $currentMonthTagalog . " bago ang nakatakdang __________________________.",
    true,
    false,
    true,
    false,
    'L'
);

// $pdf->Ln(3);
// $pdf->SetFont('helvetica', 'B', 11);
// $pdf->Cell(0, 8, $fullName, 0, 1);
// $pdf->SetFont('helvetica', '', 11);
// $pdf->Cell(0, 8, $gradeInfo, 0, 1);

/* =========================
   SECTION I – SUMMARY
========================= */
$pdf->Ln(2);
$pdf->SetFont('dejavusans', 'B', 9);
$pdf->Cell(0, 5, 'I. Buod ng Financial Obligation', 0, 1);

// Table dimensions
$col1Width = 100;
$col2Width = 30;
$tableWidth = $col1Width + $col2Width;
$pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
$tableX = $pdf->getMargins()['left'] + ($pageWidth - $tableWidth) / 2;

// Header row
$pdf->SetFont('dejavusans', 'B', 9);
$pdf->SetFillColor(230,230,230);
$pdf->SetX($tableX);
$pdf->Cell($col1Width, 5, 'Deskripsyon', 1, 0, 'C', true);
$pdf->Cell($col2Width, 5, 'Halaga', 1, 1, 'C', true);

// Row under header (empty amount)
$pdf->SetFont('dejavusans', 'B', 9);
$pdf->SetX($tableX);
$pdf->Cell($col1Width, 5, 'Kabuuang Orihinal na Halaga (Initial/Total Obligation)', 'LTR');
$pdf->Cell($col2Width, 5, '', 'LTR', 1, 'R');

// Check if Tuition Fee exists in payments
$hasTuitionFee = false;
foreach ($payments as $row) {
    if ($row['payment_type'] === 'Tuition Fee') {
        $hasTuitionFee = true;
        break;
    }
}
// Reset pointer
$payments->data_seek(0);

// Row under header (empty amount)
$pdf->SetFont('dejavusans', '', 9);
$pdf->SetX($tableX);
$pdf->Cell($col1Width, 5, '          Tuition Fee:', 'LR');
$pdf->Cell($col2Width, 5, '', 'LR', 1, 'R');

// Only show tuition fee breakdown if Tuition Fee exists
if ($hasTuitionFee) {
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->SetX($tableX);
    $pdf->Cell($col1Width, 5, '               Down Payment ------------------------------------------------', 'LR');
    $pdf->Cell($col2Width, 5, '₱ 500.00', 'LR', 1, 'L');

    //June Tuition Fee
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->SetX($tableX);
    $pdf->Cell($col1Width, 5, '               June ---------------------------------------------------------------', 'LR');
    $pdf->Cell($col2Width, 5, '₱ 399.00', 'LR', 1, 'L');

    //July Tuition Fee
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->SetX($tableX);
    $pdf->Cell($col1Width, 5, '               July ----------------------------------------------------------------', 'LR');
    $pdf->Cell($col2Width, 5, '₱ 399.00', 'LR', 1, 'L');

    //August Tuition Fee
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->SetX($tableX);
    $pdf->Cell($col1Width, 5, '               August -----------------------------------------------------------', 'LR');
    $pdf->Cell($col2Width, 5, '₱ 399.00', 'LR', 1, 'L');

    //September Tuition Fee
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->SetX($tableX);
    $pdf->Cell($col1Width, 5, '               September -----------------------------------------------------', 'LR');
    $pdf->Cell($col2Width, 5, '₱ 399.00', 'LR', 1, 'L');

    //October Tuition Fee
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->SetX($tableX);
    $pdf->Cell($col1Width, 5, '               October ---------------------------------------------------------', 'LR');
    $pdf->Cell($col2Width, 5, '₱ 399.00', 'LR', 1, 'L');

    //November Tuition Fee
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->SetX($tableX);
    $pdf->Cell($col1Width, 5, '               November ------------------------------------------------------', 'LR');
    $pdf->Cell($col2Width, 5, '₱ 399.00', 'LR', 1, 'L');

    //December Tuition Fee
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->SetX($tableX);
    $pdf->Cell($col1Width, 5, '               December ------------------------------------------------------', 'LR');
    $pdf->Cell($col2Width, 5, '₱ 399.00', 'LR', 1, 'L');

    //January Tuition Fee
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->SetX($tableX);
    $pdf->Cell($col1Width, 5, '               January ----------------------------------------------------------', 'LR');
    $pdf->Cell($col2Width, 5, '₱ 399.00', 'LR', 1, 'L');

    //Tuition Fee Total
    $pdf->SetFont('dejavusans', 'B', 9);
    $pdf->SetX($tableX);
    $pdf->Cell($col1Width, 5, '          Tuition Fee:-----------------------------------------------', 'LR');
    // $pdf->SetTextColor(255, 0, 0);
    $pdf->Cell($col2Width, 5, '₱ 3692.00', 'LR', 1, 'L');
    // $pdf->SetTextColor(0, 0, 0);
} else {
    // If no Tuition Fee, show ₱ 0.00
    $pdf->SetFont('dejavusans', 'B', 9);
    $pdf->SetX($tableX);
    $pdf->Cell($col1Width, 5, '          Tuition Fee:-----------------------------------------------', 'LR');
    $pdf->Cell($col2Width, 5, '₱ 0.00', 'LR', 1, 'L');
}

// //February Tuition Fee
// $pdf->SetFont('dejavusans', '', 9);
// $pdf->SetX($tableX);
// $pdf->Cell($col1Width, 5, '               February', 'LR');
// $pdf->Cell($col2Width, 5, '₱ 399.00', 'LR', 1, 'L');

// //March Tuition Fee
// $pdf->SetFont('dejavusans', '', 9);
// $pdf->SetX($tableX);
// $pdf->Cell($col1Width, 5, '               March', 'LR');
// $pdf->Cell($col2Width, 5, '₱ 399.00', 'LR', 1, 'L');


$pdf->SetFont('dejavusans', 'B', 9);

$totalTuition = 0;
$totalPaid = 0;
$tutionFee = $hasTuitionFee ? 3692.00 : 0.00; // Only add tuition fee if it exists

foreach ($payments as $row) {
    if ($row['payment_type'] === 'Tuition Fee') {
        continue; // Skip this iteration if the payment type is Tuition Fee
    }
    $pdf->SetX($tableX);
    $pdf->Cell($col1Width, 6, '          ' . $row['payment_type']. '--------------------------------------', 'LR');
    $pdf->Cell($col2Width, 6, '₱ ' . number_format($row['amount'], 2), 'LR', 1, 'L');
    $totalTuition += $row['amount'];
    $totalPaid += $row['paid'];
    
}


// ===== LAST ROW: KABUUAN =====
$pdf->SetFont('dejavusans', 'B', 9);
$pdf->SetX($tableX);
$pdf->Cell($col1Width, 5, 'Kabuuan', 1);
$pdf->SetTextColor(255, 0, 0);
$pdf->Cell($col2Width, 5, '₱ ' . number_format($totalTuition + $tutionFee, 2), 1, 1, 'L');
$pdf->SetTextColor(0, 0, 0);
// Remaining balance (if needed later)
$remaining = $totalTuition - $totalPaid;

/* =========================
   SECTION II – DETAILED PAYMENT HISTORY
========================= */
$pdf->Ln(5);
$pdf->SetFont('dejavusans', 'B', 9);
$pdf->Cell(0, 5, 'II. Detalyadong Record ng Pagbabayad', 0, 1);

/* Table Header */
$pdf->SetFont('dejavusans', 'B', 9);
$pdf->SetFillColor(230,230,230);

$pdf->Cell(40, 5, 'Petsa ng Pagbayad', 1, 0, 'C', true);
$pdf->Cell(30, 5, 'Reference No.', 1, 0, 'C', true);
$pdf->Cell(60, 5, 'Deskripsyon', 1, 0, 'C', true);
$pdf->Cell(40, 5, 'Halaga ng Binayad', 1, 1, 'C', true);

/* Table Body */
$pdf->SetFont('dejavusans', 'C', 9);

$totalPaid = 0;

if ($payment_history->num_rows > 0) {
    while ($row = $payment_history->fetch_assoc()) {

        $pdf->Cell(40, 5, date('M d, Y', strtotime($row['date_paid'])), 1);
        $pdf->Cell(30, 5, $row['reference_no'], 1);
        $pdf->Cell(60, 5, $row['payment_type'], 1);
        $pdf->Cell(40, 5, '₱ ' . number_format($row['amount_paid'], 2), 1, 1, 'C');

        $totalPaid += $row['amount_paid'];
    }

    /* Kabuuan Row */
    $pdf->SetFont('dejavusans', 'B', 9);
    $pdf->Cell(130, 5, 'Kabuuan', 1, 0, 'R');
    $pdf->SetTextColor(255, 0, 0);
    $pdf->Cell(40, 5, '₱ ' . number_format($totalPaid, 2), 1, 1, 'C');
    $pdf->SetTextColor(0, 0, 0);
} else {
    $pdf->Cell(170, 5, 'Walang naitalang pagbabayad.', 1, 1, 'C');
}



/* =========================
   SECTION III – BALANCE
========================= */
$pdf->Ln(5);
$pdf->SetFont('dejavusans', 'B', 9);
$pdf->Cell(0, 5, 'III. Kasalukuyang Balanse', 0, 1);

$pdf->SetFont('dejavusans', '', 9);
// $pdf->Cell(120, 5, 'Kabuuang Tuition Fee', 1);
// $pdf->Cell(50, 5, '₱ ' . number_format($totalTuition, 2), 1, 1, 'R');
$pdf->SetFont('dejavusans', 'B', 9);
$pdf->SetFillColor(230,230,230);
$pdf->Cell(85, 5, 'Deskripsyon', 1, 0, 'C', true);
$pdf->Cell(85, 5, 'Halaga', 1, 1, 'C', true);

$pdf->SetFont('dejavusans', '', 9);
$pdf->Cell(85, 5, 'Kabuuang Naibayad (Total Payments Made) ', 1);
$pdf->Cell(85, 5, '₱ ' . number_format($totalPaid, 2), 1, 1, 'R');
$pdf->SetFont('dejavusans', 'B', 9);
$pdf->Cell(85, 5, 'Kasalukuyang Balanse (Outstanding Balance) ', 1);
$pdf->SetTextColor(255, 0, 0);
$pdf->Cell(85, 5, '₱ ' . number_format($remaining, 2), 1, 1, 'R');
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('dejavusans', 'BI', 9);
$pdf->Cell(85, 5, 'Petsa ng Deadline (Due Date) ', 1);
$pdf->SetFont('dejavusans', 'B', 9);
$pdf->Cell(85, 5, '', 1, 1, 'R');
// $pdf->Cell(85, 5, '₱ ' . number_format($remaining, 2), 1, 1, 'R');



/* =========================
   NOTICE
========================= */
$pdf->Ln(5);
$pdf->SetFont('dejavusans', '', 9);
$pdf->MultiCell(0, 8,

"Maari lamang po i-settle ang natitirang balanse ng inyong anak. Hindi po papayagang kumuha ng pagsusulit ang mga mag-aaral na hindi makapagbayad ng libro at tuition fee bago sumapit ang petsang nakatakda para sa",
0, 'L');

/* =========================
   CLOSING
========================= */
$pdf->Ln(5);
$pdf->MultiCell(0, 8,
"Maraming salamat po sa inyong agarang aksyon at suporta sa edukasyon ng inyong anak.



Lubos na gumagalang, ",
0, 'L');

$pdf->Ln(15);
$pdf->SetFont('dejavusans', 'B', 9);
$pdf->Cell(0, 3, 'Rona Jieanne P. Tan', 0, 1);
$pdf->SetFont('dejavusans', '', 8);
$pdf->Cell(0, 5, 'Ingat Yaman', 0, 1);

/* =========================
   OUTPUT
========================= */
$filename = 'Financial_Statement_' . preg_replace('/[^A-Za-z0-9]/', '_', $fullName) . '.pdf';
$pdf->Output($filename, 'I');
exit;
