<?php
/* config values */
$config = json_decode(file_get_contents("./config.json"), true);

/* main event data */
$eventInfo = json_decode(file_get_contents($config["shiftFile"]), true);

/* action processing */
if(isset($_POST["action"]) && $_POST["action"] == "register") {
    /* re-read data with exclusive lock for persistence */
    $fp = fopen($config["shiftFile"], "r+");
    if( ! flock($fp, LOCK_EX) ) {
        die("ERROR: Cannot obtain file lock.");
    }
    $rawData = fread($fp, filesize($config["shiftFile"]));
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

    /* error prevention */
    if( ! isset($eventInfo["eventTasks"][$taskIndex]["taskShifts"][$shiftIndex]["entries"])) {
        $eventInfo["eventTasks"][$taskIndex]["taskShifts"][$shiftIndex]["entries"] = [];
    }

    /* check if there is space left in this shift */
    if(count($eventInfo["eventTasks"][$taskIndex]["taskShifts"][$shiftIndex]["entries"]) 
        < $eventInfo["eventTasks"][$taskIndex]["taskShifts"][$shiftIndex]["shiftSlots"]) {
        /* write user data back to json */
        $eventInfo["eventTasks"][$taskIndex]["taskShifts"][$shiftIndex]["entries"][] = $entry;
        /* write back to json file (with exclusive lock since read above) */
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($eventInfo, JSON_PRETTY_PRINT));
        fflush($fp);
        /* output message for user */
        $toast = array(
            "message" => "Deine Registrierung wurde gespeichert. Falls Du Dich austragen möchtest, kannst Du das über den Link in der Bestätigungsmail tun.",
            "style" => "success"      
        );    
    } else {
        /* no space left in this shift */
        /* output message for user */
        $toast = array(
            "message" => "Diese Schicht ist leider schon voll!",
            "style" => "error"      
        );   
    } 
    flock($fp, LOCK_UN);
    fclose($fp);
}

/* call template */
include("template.htm");
?>