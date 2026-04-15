<?php
function calculcateOccupancy(&$eventInfo) {
    $eventEntries = 0;
    $eventSlots = 0;
    foreach($eventInfo["eventTasks"] as $taskIndex => &$task) {
        $taskEntries = 0;
        $taskSlots = 0;
        foreach($task["taskShifts"] as $shiftIndex => &$shift) {
            if( ! isset($shift["entries"])) {
                $shift["occupancyPercentage"] = 0.0;
                $taskEntries += 0;
                $taskSlots += $shift["shiftSlots"];
            } else {
                $shift["occupancyPercentage"] = count($shift["entries"]) / $shift["shiftSlots"];
                $taskEntries += count($shift["entries"]);
                $taskSlots += $shift["shiftSlots"];
            }
            $shift["occupancyColor"] = getOccupancyColorFromPercentage($shift["occupancyPercentage"]);
            $shift["occupancyString"] = getOccupancyStringFromPercentage($shift["occupancyPercentage"]);
        }
        $eventEntries += $taskEntries;
        $eventSlots += $taskSlots;
        $task["occupancyPercentage"] = $taskEntries / $taskSlots;
        $task["occupancyColor"] = getOccupancyColorFromPercentage($task["occupancyPercentage"]);
        $task["occupancyString"] = getOccupancyStringFromPercentage($task["occupancyPercentage"]);
    }
    $eventInfo["occupancyPercentage"] = $eventEntries / $eventSlots;
    $eventInfo["occupancyColor"] = getOccupancyColorFromPercentage($eventInfo["occupancyPercentage"]);
    $eventInfo["occupancyString"] = getOccupancyStringFromPercentage($eventInfo["occupancyPercentage"]);
}

function getOccupancyColorFromPercentage($p) {
    if ($p < 0.5) {
        $g = 255 * ($p / 0.5);
        $r = 255;
    } else {
        $g = 255;
        $r = 255 * (1 - ($p - 0.5) / 0.5);
    }
    return sprintf("#%02x%02x00bb", $r, $g);
}

function getOccupancyStringFromPercentage($p) {
    return sprintf("%d%%", $p * 100);
}

function appendClasses(&$eventInfo) {
    foreach($eventInfo["eventTasks"] as $taskIndex => &$task) {
        foreach($task["taskShifts"] as $shiftIndex => &$shift) {
            if(isset($shift["entries"])) {
                foreach($shift["entries"] as $entryIndex => &$entry) {
                    $entry["entryClass"] = "";
                    /* check for fsi */
                    if(str_starts_with($entry["entryName"], "fsi")) $entry["entryClass"] .= "fs-info ";
                    if(str_contains($entry["entryName"], "dino")) $entry["entryClass"] .= "fs-info-dino ";
                }
            } 
        }
    }
}

function hideEntryNames(&$eventInfo) {
    foreach($eventInfo["eventTasks"] as $taskIndex => &$task) {
        foreach($task["taskShifts"] as $shiftIndex => &$shift) {
            if(isset($shift["entries"])) {
                foreach($shift["entries"] as $entryIndex => &$entry) {
                    $entry["entryName"] = i18n("entry.occupied");
                    $entry["entryClass"] .= "";
                }
            } 
        }
    }
}

?>
