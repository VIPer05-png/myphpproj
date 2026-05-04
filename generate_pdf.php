<?php
// Include the FPDF library
require('../fpdf186/fpdf.php');

// Create a new FPDF object
$pdf = new FPDF();

// Add a new page
$pdf->AddPage();

// Set the font for the text
$pdf->SetFont('Arial', 'B', 16);

// Print a cell with text
$pdf->Cell(40, 10, 'Hello World! This is a basic PDF generated using FPDF.');

// Output the document
$pdf->Output();
?>
