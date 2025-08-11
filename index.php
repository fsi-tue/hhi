<?php
/* main event data */
$eventInfo = json_decode(file_get_contents("./shifts.json"), true);

/* action processing */
if(isset($_POST["action"]) && $_POST["action"] == "register") {
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

    /* TODO: persistent write to file (please use flock) */

    /* output message for user */
    $toast = array(
        "message" => "Deine Registrierung wurde gespeichert. Falls Du Dich austragen möchtest, kannst Du das über den Link in der Bestätigungsmail tun.",
        "style" => "success"      
    );
}

/* call template */
include("template.htm");
?>