<?php
function calculcateOccupancy(&$eventInfo) {
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
        $task["occupancyPercentage"] = $taskEntries / $taskSlots;
        $task["occupancyColor"] = getOccupancyColorFromPercentage($task["occupancyPercentage"]);
        $task["occupancyString"] = getOccupancyStringFromPercentage($task["occupancyPercentage"]);
    }
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

?>