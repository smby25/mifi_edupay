<?php
session_start();
require_once('../../conn.php');
require_once('../tcpdf/tcpdf.php');

if (!isset($_GET['grade']) || empty($_GET['grade'])) {
    die('Grade not specified.');
}

$grade = $_GET['grade'];

// Use fullname or fallback to username for footer
$preparedBy = isset($_SESSION['fullname']) 
    ? htmlspecialchars($_SESSION['fullname']) 
    : (isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Unknown');

// Extend TCPDF to customize the footer
class MYPDF extends TCPDF {
    public $preparedBy = '';

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Prepared by: ' . $this->preparedBy, 0, 0, 'L');
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 0, 'R');
    }
}

// Create PDF
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->preparedBy = $preparedBy;

// Document Info
$pdf->SetCreator('Student Ledger Export');
$pdf->SetAuthor('Malindig Institute Foundation Inc.');
$pdf->SetTitle("Student Ledger - Grade $grade");

// Set margins
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();

// Add image header
$imagePath = '../assets/images/header/malindig_header_pdf.jpg';
$pdf->Image($imagePath, 15, 10, 180, 30, '', '', 'T', false, 300);

// Set Y below the header image
$pdf->SetY(40);

// Title
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 10, "Student Ledger - Grade $grade", 0, 1, 'C');
$pdf->Ln(5);

// Table HTML
$html = '<style>
            th {
                background-color: #f2f2f2;
                font-weight: bold;
                text-align: center;
                border: 1px solid #000;
                padding: 5px;
            }
            td {
                text-align: left;
                vertical-align: middle;
                border: 1px solid #000;
                padding: 5px;
                font-weight: normal;
            }
        </style>
        <table width="100%" cellspacing="0" cellpadding="4">
            <thead>
                <tr>
                    <th width="40%"><b>Full Name</b></th>
                    <th width="40%"><b>Grade & Section / Strand</b></th>
                    <th width="20%"><b>Remaining Balance</b></th>
                </tr>
            </thead>
            <tbody>';

// Fetch students
$stmt = $conn->prepare("SELECT student_id, fname, mname, lname, section, strand FROM students WHERE grade_level = ? AND status = 'active' ORDER BY lname ASC");
$stmt->bind_param("s", $grade);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $student_id = $row['student_id'];
    $full_name = $row['lname'] . ', ' . $row['fname'] . ' ' . strtoupper(substr($row['mname'], 0, 1)) . '.';

    $gradeSectionStrand = "Grade $grade";
    if (!empty($row['section'])) $gradeSectionStrand .= " - " . $row['section'];
    if (!empty($row['strand'])) $gradeSectionStrand .= " | " . $row['strand'];

    // Get balance
    $bal_stmt = $conn->prepare("
        SELECT 
            IFNULL((SELECT SUM(p.amount) FROM payments p WHERE p.target_grade = ? OR p.student_id = ?), 0)
            -
            IFNULL((SELECT SUM(sp.amount_paid) FROM student_payments sp WHERE sp.student_id = ?), 0) AS balance
    ");
    $bal_stmt->bind_param("sii", $grade, $student_id, $student_id);
    $bal_stmt->execute();
    $bal_result = $bal_stmt->get_result();
    $balance = $bal_result->fetch_assoc()['balance'] ?? 0;
    $bal_stmt->close();

    $html .= '<tr>';
    $html .= '<td width="40%">' . htmlspecialchars($full_name) . '</td>';
    $html .= '<td width="40%">' . htmlspecialchars($gradeSectionStrand) . '</td>';
    $html .= '<td width="20%" style="text-align: right;">₱' . number_format($balance, 2) . '</td>';
    $html .= '</tr>';
}

$html .= '</tbody></table>';

// Output content
$pdf->writeHTML($html, true, false, true, false, '');

// Output PDF
$pdf->Output("Grade_{$grade}_student_ledger.pdf", 'D');
exit;
?>
