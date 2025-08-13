<?php
function handleUnregister(string $hash, array $config, array &$eventInfo): array {
    /* re-read data with exclusive lock for persistence */
    $fp = fopen($config["shiftFile"], "r+");
    if( ! flock($fp, LOCK_EX) ) {
        die("ERROR: Cannot obtain file lock.");
    }
    $rawData = fread($fp, filesize($config["shiftFile"]));
    $eventInfo = json_decode($rawData, true);
    /* search for hash */
    $hash = $_GET["hash"];
    $taskId = -1;
    $shiftId = -1;
    $entryId = -1;
    foreach($eventInfo["eventTasks"] as $taskIndex => $task) {
        foreach($task["taskShifts"] as $shiftIndex => $shift) {
            if( ! isset($shift["entries"])) continue;
            foreach($shift["entries"] as $entryIndex => $entry) {
                if($entry["entryHash"] === $hash) {
                    $taskId  = $taskIndex;
                    $shiftId = $shiftIndex;
                    $entryId = $entryIndex;
                }
            }
        }
    }
    if($entryId != -1) {
        /* shift found */
        array_splice($eventInfo["eventTasks"][$taskId]["taskShifts"][$shiftId]["entries"], $entryId, 1);
        /* write back to json file (with exclusive lock since read above) */
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($eventInfo, JSON_PRETTY_PRINT));
        fflush($fp);
        /* user message */
        $toast = array(
            "message" => "Du hast Dich erfolgreich aus Deiner Schicht abgemeldet.",
            "style" => "success"      
        );
    } else {
        /* shift NOT found */
        $toast = array(
            "message" => "Fehler: Unbekannter Fingerprint.",
            "style" => "error"      
        );    
    }
    flock($fp, LOCK_UN);
    fclose($fp);

    return $toast;
}