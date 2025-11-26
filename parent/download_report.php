<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is parent
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'parent') {
    header('Location: ../index.php');
    exit();
}

// Check if child_id is provided
if (!isset($_GET['child_id']) || !is_numeric($_GET['child_id'])) {
    $_SESSION['error_message'] = 'Invalid child ID';
    header('Location: children.php');
    exit();
}

$child_id = (int)$_GET['child_id'];
$format = isset($_GET['format']) && in_array($_GET['format'], ['pdf', 'excel']) ? $_GET['format'] : 'pdf';

try {
    $conn = getDBConnection();
    
    // Verify the child belongs to the logged-in parent
    $stmt = $conn->prepare("
        SELECT s.*, u.first_name as parent_first_name, u.last_name as parent_last_name
        FROM students s
        JOIN users u ON s.parent_id = u.id
        WHERE s.id = ? AND s.parent_id = ? AND s.status = 'active'
    ");
    $stmt->execute([$child_id, $_SESSION['user_id']]);
    $child = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$child) {
        throw new Exception('Child not found or access denied');
    }
    
    // Get the latest progress report for this child
    $stmt = $conn->prepare("
        SELECT pr.*, u.first_name as teacher_first_name, u.last_name as teacher_last_name
        FROM progress_reports pr
        LEFT JOIN users u ON pr.created_by = u.id
        WHERE pr.student_id = ?
        ORDER BY pr.report_date DESC, pr.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$child_id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$report) {
        throw new Exception('No progress report found for this child');
    }
    
    // Format the data for the report
    $report_data = [
        'child_name' => $child['first_name'] . ' ' . $child['last_name'],
        'parent_name' => $child['parent_first_name'] . ' ' . $child['parent_last_name'],
        'birthdate' => date('F j, Y', strtotime($child['birthdate'])),
        'report_date' => date('F j, Y', strtotime($report['report_date'])),
        'teacher_name' => $report['teacher_first_name'] . ' ' . $report['teacher_last_name'],
        'social_development' => $report['social_development'] ?? 'Not evaluated',
        'cognitive_development' => $report['cognitive_development'] ?? 'Not evaluated',
        'language_development' => $report['language_development'] ?? 'Not evaluated',
        'physical_development' => $report['physical_development'] ?? 'Not evaluated',
        'emotional_development' => $report['emotional_development'] ?? 'Not evaluated',
        'overall_development' => $report['overall_development'] ?? 'Not evaluated',
        'strengths' => $report['strengths'] ?? 'Not specified',
        'areas_for_improvement' => $report['areas_for_improvement'] ?? 'Not specified',
        'teacher_comments' => $report['teacher_comments'] ?? 'No additional comments',
        'attendance' => $report['attendance'] ?? 'Not specified'
    ];
    
    // Generate the report based on the requested format
    if ($format === 'pdf') {
        generatePdfReport($report_data);
    } else {
        generateExcelReport($report_data);
    }
    
} catch (Exception $e) {
    $_SESSION['error_message'] = 'Error generating report: ' . $e->getMessage();
    header('Location: children.php');
    exit();
}

/**
 * Generate a PDF report using FPDF
 */
function generatePdfReport($data) {
    require_once('../vendor/autoload.php');
    
    $pdf = new \FPDF();
    $pdf->AddPage();
    
    // Set document information
    $pdf->SetCreator('Gumamela Daycare Center');
    $pdf->SetAuthor('Gumamela Daycare Center');
    $pdf->SetTitle('Progress Report - ' . $data['child_name']);
    
    // Add header
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Gumamela Daycare Center', 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Progress Report', 0, 1, 'C');
    $pdf->Ln(10);
    
    // Student Information
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Student Information', 0, 1, 'L');
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(50, 8, 'Student Name:', 0, 0);
    $pdf->Cell(0, 8, $data['child_name'], 0, 1);
    $pdf->Cell(50, 8, 'Date of Birth:', 0, 0);
    $pdf->Cell(0, 8, $data['birthdate'], 0, 1);
    $pdf->Cell(50, 8, 'Report Date:', 0, 0);
    $pdf->Cell(0, 8, $data['report_date'], 0, 1);
    $pdf->Cell(50, 8, 'Teacher:', 0, 0);
    $pdf->Cell(0, 8, $data['teacher_name'], 0, 1);
    $pdf->Ln(5);
    
    // Development Areas
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Development Areas', 0, 1, 'L');
    $pdf->SetFont('Arial', '', 11);
    
    $areas = [
        'Social Development' => $data['social_development'],
        'Cognitive Development' => $data['cognitive_development'],
        'Language Development' => $data['language_development'],
        'Physical Development' => $data['physical_development'],
        'Emotional Development' => $data['emotional_development'],
        'Overall Development' => $data['overall_development']
    ];
    
    foreach ($areas as $area => $rating) {
        $pdf->Cell(80, 8, $area . ':', 0, 0);
        $pdf->Cell(0, 8, $rating, 0, 1);
    }
    
    $pdf->Ln(5);
    
    // Comments
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Teacher Comments', 0, 1, 'L');
    $pdf->SetFont('Arial', '', 11);
    $pdf->MultiCell(0, 8, $data['teacher_comments']);
    
    // Output the PDF
    $filename = 'Progress_Report_' . str_replace(' ', '_', $data['child_name']) . '_' . date('Y-m-d') . '.pdf';
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $pdf->Output('D', $filename);
    exit();
}

/**
 * Generate an Excel report using PhpSpreadsheet
 */
function generateExcelReport($data) {
    require_once '../vendor/autoload.php';
    
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set document properties
    $spreadsheet->getProperties()
        ->setCreator('Gumamela Daycare Center')
        ->setTitle('Progress Report - ' . $data['child_name']);
    
    // Add headers
    $sheet->setCellValue('A1', 'Gumamela Daycare Center')
          ->setCellValue('A2', 'Progress Report')
          ->setCellValue('A3', 'Generated on: ' . date('F j, Y'));
    
    // Merge header cells
    $sheet->mergeCells('A1:E1');
    $sheet->mergeCells('A2:E2');
    $sheet->mergeCells('A3:E3');
    
    // Style headers
    $headerStyle = [
        'font' => ['bold' => true],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
    ];
    
    $sheet->getStyle('A1:A3')->applyFromArray($headerStyle);
    
    // Add student information
    $row = 5;
    $sheet->setCellValue("A$row", 'Student Information:');
    $sheet->getStyle("A$row")->getFont()->setBold(true);
    $row++;
    
    $sheet->setCellValue("B$row", 'Student Name:');
    $sheet->setCellValue("C$row", $data['child_name']);
    $row++;
    
    $sheet->setCellValue("B$row", 'Date of Birth:');
    $sheet->setCellValue("C$row", $data['birthdate']);
    $row++;
    
    $sheet->setCellValue("B$row", 'Report Date:');
    $sheet->setCellValue("C$row", $data['report_date']);
    $row++;
    
    $sheet->setCellValue("B$row", 'Teacher:');
    $sheet->setCellValue("C$row", $data['teacher_name']);
    $row += 2;
    
    // Add development areas
    $sheet->setCellValue("A$row", 'Development Areas:');
    $sheet->getStyle("A$row")->getFont()->setBold(true);
    $row++;
    
    $areas = [
        'Social Development' => $data['social_development'],
        'Cognitive Development' => $data['cognitive_development'],
        'Language Development' => $data['language_development'],
        'Physical Development' => $data['physical_development'],
        'Emotional Development' => $data['emotional_development'],
        'Overall Development' => $data['overall_development']
    ];
    
    foreach ($areas as $area => $rating) {
        $sheet->setCellValue("B$row", $area . ':');
        $sheet->setCellValue("C$row", $rating);
        $row++;
    }
    
    // Add comments
    $row++;
    $sheet->setCellValue("A$row", 'Teacher Comments:');
    $sheet->getStyle("A$row")->getFont()->setBold(true);
    $row++;
    
    $sheet->mergeCells("B$row:E" . ($row + 3));
    $sheet->setCellValue("B$row", $data['teacher_comments']);
    $sheet->getStyle("B$row")->getAlignment()->setWrapText(true);
    
    // Auto-size columns
    foreach (range('A', 'E') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Set headers for download
    $filename = 'Progress_Report_' . str_replace(' ', '_', $data['child_name']) . '_' . date('Y-m-d') . '.xlsx';
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
    exit();
}
?>
