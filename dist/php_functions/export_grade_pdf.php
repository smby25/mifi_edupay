<?php
require_once('../../conn.php');
require_once('../tcpdf/tcpdf.php');

if (!isset($_GET['grade']) || empty($_GET['grade'])) {
    die('Grade not specified.');
}

$grade = $_GET['grade'];

// Extend TCPDF to customize the footer
class MYPDF extends TCPDF {
    public function Footer() {
        $this->SetY(-15); // Position 15 mm from bottom
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, false, 'R');
    }
}

// Create PDF
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);


// Document Info
$pdf->SetCreator('Student Ledger Export');
$pdf->SetAuthor('Malindig Institute Foundation Inc.');
$pdf->SetTitle("Student Ledger - Grade $grade");

// Set margins
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();

// Add image header
$imagePath = '../assets/images/header/malindig_header_pdf.jpg';
$pdf->Image($imagePath, 15, 10, 180, 30, '', '', 'T', false, 300); // 25mm height

// Add space below image (image Y=10 + height 25 = 35)
$pdf->SetY(40);

// Set font for title
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 10, "Student Ledger - Grade $grade", 0, 1, 'C');

// Add space before table
$pdf->Ln(5);

// Table Header and Style (with corrected column widths and alignment)
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
                    <th width="20%" style="text-align: center;"><b>Remaining Balance</b></th>
                </tr>
            </thead>
            <tbody>';



// Fetch students by grade
$stmt = $conn->prepare("SELECT student_id, fname, mname, lname, section, strand FROM students WHERE grade_level = ? AND status = 'active' ORDER BY lname ASC");
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

// Write table
$pdf->writeHTML($html, true, false, true, false, '');

// Output PDF
$pdf->Output("Grade_{$grade}_student_ledger.pdf", 'D');
exit;
?>
