<?php
/* config values */
$SHIFT_FILE = "./shifts.json";

/* main event data */
$eventInfo = json_decode(file_get_contents($SHIFT_FILE), true);

/* action processing */
if(isset($_POST["action"]) && $_POST["action"] == "register") {
    /* re-read data with exclusive lock for persistence */
    $fp = fopen($SHIFT_FILE, "r+");
    if( ! flock($fp, LOCK_EX) ) {
        die("ERROR: Cannot obtain file lock.");
    }
    $rawData = fread($fp, filesize($SHIFT_FILE));
    $eventInfo = json_decode($rawData, true);

    /* store user data */
    $entry = array(
        "entryName" => $_POST["data-name"],
        "entryZxNick" => $_POST["data-zxnick"]
    );
    $taskName = $_POST["data-task"];
    $shiftName = $_POST["data-shift"];

    /* find correct task and shift */
    $tasks = $eventInfo["eventTasks"];
    $taskIndex = array_search($taskName, array_map(fn($t) => html_entity_decode($t['taskName']), $tasks), true);
    $shifts = $tasks[$taskIndex]['taskShifts'];
    $shiftIndex = array_search($shiftName, array_map(fn($s) => html_entity_decode($s['shiftName']), $shifts), true);

    /* TODO: check if shift is full */

    /* write user data back to json */
    $eventInfo["eventTasks"][$taskIndex]["taskShifts"][$shiftIndex]["entries"][] = $entry;

    /* write back to json file (with exclusive lock since read above) */
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($eventInfo, JSON_PRETTY_PRINT));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    /* output message for user */
    $toast = array(
        "message" => "Deine Registrierung wurde gespeichert. Falls Du Dich austragen möchtest, kannst Du das über den Link in der Bestätigungsmail tun.",
        "style" => "success"      
    );
}

/* call template */
include("template.htm");
?>