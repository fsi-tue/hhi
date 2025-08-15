<?php
require('lib/tfpdf/tfpdf.php');

class PDF extends tFPDF {
    var $eventInfo;
    var $margins = 8;

    function __construct($eventInfo, $orientation = "P", $unit= "mm", $size= "A4") {
        $this->eventInfo = $eventInfo;
        parent::__construct($orientation, $unit, $size);
        $this->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
        $this->AddFont('DejaVu', 'B', 'DejaVuSansCondensed-Bold.ttf', true);
        $this->AddFont('DejaVu', 'I', 'DejaVuSansCondensed-Oblique.ttf', true);
        $this->AddFont('DejaVu', 'BI', 'DejaVuSans-BoldOblique.ttf', true);
        $this->SetMargins($this->margins, $this->margins);
    }

    function Header() {
        $this->SetTextColor(0, 0,0x80);
        $this->SetXY(3,0);
        $this->SetFont('DejaVu', '', 12);
        $this->Cell(100, 10, "Schichtplan " . $this->eventInfo["eventName"], "B", 0, 'L');
        $this->SetFont('DejaVu', 'BI', 16);
        $this->Cell(187, 10, "fsi", "B", 0, 'R');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-10);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, "Druckzeitpunkt: " . date(DATE_ATOM), 0, 0, 'C');
    }
}

function handleExport($config, &$eventInfo) {
    $pdf = new PDF($eventInfo, 'L', 'mm', 'A4');

    foreach ($eventInfo["eventTasks"] as $task) {
        /* page creation and title */
        $pdf->AddPage();
        $pdf->SetFont("DejaVu", "B", 24);
        $pdf->Ln(6);
        $pdf->Cell(0, 10, "Schichtplan :: " . $task["taskName"], 0, 0, "C");
        $pdf->Ln(13);
        $pdf->SetFont("DejaVu", "I", 12);
        $pdf->MultiCell(0, 6, $task["taskDesc"], 0, "C");
        $pdf->Ln(3);
        /* table header */
        /* scale to max width */
        $colWidth = ($pdf->GetPageWidth() - 2 * $pdf->margins) / (count($task["taskShifts"]) + 1);
        $pdf->Cell($colWidth, 10, "", 1, 0, "C");
        $initFontSize = 14; /* dynamic font size */
        $fontSize = 0;
        foreach($task["taskShifts"] as $shift) {
            $fontSize = $initFontSize;
            $pdf->SetFont("DejaVu", "B", $fontSize);
            while($pdf->GetStringWidth($shift["shiftName"]) > $colWidth) {
                $fontSize--;
                $pdf->SetFont("DejaVu", "B", $fontSize);
            } 
            $pdf->Cell($colWidth, 10, $shift["shiftName"], 1, 0, "C");
        }
        $pdf->Ln(10);
        /* output table content */
        $pdf->SetFont("DejaVu", "", 14);
        $slot = 0;
        $maxSlots = max(array_column($task['taskShifts'], 'shiftSlots'));
        while($slot < $maxSlots) {
            $pdf->Cell($colWidth, 10, $slot + 1, 1, 0, "C");
            foreach($task["taskShifts"] as $shift) {
                if($slot < $shift["shiftSlots"]) {
                    /* valid slot */
                    $pdf->Cell($colWidth, 10, $shift["entries"][$slot]["entryName"] ?? "", 
                        1, 0, "C", 0);
                } else {
                    /* invalid slot (greyed out) */
                    $pdf->Cell($colWidth, 10, "", 0, 0, "C", 0);
                }
            }
            $pdf->Ln(10);
            $slot++;
        }
    }
    $pdf->Output();
}
