<?php
require('lib/tfpdf/tfpdf.php');

class PDF extends tFPDF {
    var $eventInfo;
    var $margins = 8;

    var $colEven = array(0xee, 0xee, 0xee);
    var $colOdd = array(0xff, 0xff, 0xff);

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
        $this->SetXY($this->margins,0);
        $this->SetFont('DejaVu', 'I', 12);
        $this->Cell(100, 10, "Schichtplan " . $this->eventInfo["eventName"], "B", 0, 'L');
        $this->SetFont('DejaVu', 'BI', 16);
        $this->SetTextColor(0, 0,0x80);
        $this->Cell(0, 10, "fsi", "B", 0, 'R');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-10);
        $this->SetFont('DejaVu', 'I', 8);
        $this->Cell(0, 10, 
            "Druckzeitpunkt: " . date(DATE_ATOM) . " - erstellt mit ♥ vom Helfilisten Hosting Interface", 
            0, 0, 'C');
    }
}

function handlePdfExport($config, &$eventInfo) {
    $pdf = new PDF($eventInfo, 'L', 'mm', 'A4');

    foreach ($eventInfo["eventTasks"] as $task) {
        /* page creation and title */
        $pdf->AddPage();
        $pdf->SetFont("DejaVu", "B", 24);
        $pdf->Ln(6);
        $pdf->Cell(0, 10, "Schichtplan " . $task["taskName"], 0, 0, "C");
        $pdf->Ln(13);
        $pdf->SetFont("DejaVu", "I", 12);
        $pdf->MultiCell(0, 6, $task["taskDesc"], 0, "C");
        $pdf->Ln(3);
        /* table header */
        /* scale to max width */
        $colWidth = ($pdf->GetPageWidth() - 2 * $pdf->margins) / (count($task["taskShifts"]) + 1);
        $pdf->Cell($colWidth, 10, "", "B", 0, "C");
        $initFontSize = 14; /* dynamic font size */
        $fontSize = 0;
        foreach($task["taskShifts"] as $shift) {
            $fontSize = $initFontSize;
            $pdf->SetFont("DejaVu", "B", $fontSize);
            while($pdf->GetStringWidth($shift["shiftName"]) > $colWidth) {
                /* shrink until fit */
                $fontSize--;
                $pdf->SetFont("DejaVu", "B", $fontSize);
            } 
            $pdf->Cell($colWidth, 10, $shift["shiftName"], "B", 0, "C");
        }
        $pdf->Ln(10);
        /* table content */
        $slot = 0;
        $maxSlots = max(array_column($task['taskShifts'], 'shiftSlots'));
        while($slot < $maxSlots) {
            /* alternating colors */
            $pdf->SetFillColor(
                ($slot % 2) ? $pdf->colOdd[0] : $pdf->colEven[0],
                ($slot % 2) ? $pdf->colOdd[1] : $pdf->colEven[1],
                ($slot % 2) ? $pdf->colOdd[2] : $pdf->colEven[2]
            );
            $pdf->SetFont("DejaVu", "B", 14);
            $pdf->Cell($colWidth, 10, $slot + 1, "B", 0, "C", 1);
            $pdf->SetFont("DejaVu", "", 14);
            foreach($task["taskShifts"] as $shift) {
                if($slot < $shift["shiftSlots"]) {
                    /* valid slot */
                    $pdf->Cell($colWidth, 10, $shift["entries"][$slot]["entryName"] ?? "", 
                        "B", 0, "C", 1);
                } else {
                    /* invalid slot */
                    $pdf->Cell($colWidth, 10, "", 0, 0, "C", 0);
                }
            }
            $pdf->Ln(10);
            $slot++;
        }
    }
    /* force uncached output */
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    $pdf->Output("I");
}
