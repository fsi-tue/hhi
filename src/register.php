<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function handleRegister(array $postData, array $config, array &$eventInfo): array {
    /* re-read data with exclusive lock for persistence */
    $fp = fopen($config["shiftFile"], "r+");
    if( ! flock($fp, LOCK_EX) ) {
        die("ERROR: Cannot obtain file lock.");
    }
    $rawData = fread($fp, filesize($config["shiftFile"]));
    $eventInfo = json_decode($rawData, true);

    /* store user data */
    $entry = array(
        "entryName" => htmlspecialchars($_POST["data-name"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        "entryZxNick" => htmlspecialchars($_POST["data-zxnick"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        "entryTimestamp" => time(),
        "entryHash" => hash("sha256", $config["hashSalt"] . time() . $_POST["data-name"] . $_POST["data-zxnick"])
    );
    $taskName = $_POST["data-task"];
    $shiftName = $_POST["data-shift"];

    /* find correct task and shift */
    $tasks = $eventInfo["eventTasks"];
    $taskIndex = array_search($taskName, array_map(fn($t) => html_entity_decode($t['taskName']), $tasks), true);
    $shifts = $tasks[$taskIndex]['taskShifts'];
    $shiftIndex = array_search($shiftName, array_map(fn($s) => html_entity_decode($s['shiftName']), $shifts), true);
    /* TODO: prevent invalid shifts */

    /* error prevention */
    if( ! isset($eventInfo["eventTasks"][$taskIndex]["taskShifts"][$shiftIndex]["entries"])) {
        $eventInfo["eventTasks"][$taskIndex]["taskShifts"][$shiftIndex]["entries"] = [];
    }

    /* check if there is space left in this shift */
    if(count($eventInfo["eventTasks"][$taskIndex]["taskShifts"][$shiftIndex]["entries"]) 
        < $eventInfo["eventTasks"][$taskIndex]["taskShifts"][$shiftIndex]["shiftSlots"]) {
        /* generate feedback mail */
        $mail = new PHPMailer(true);
        try {
            /* smtp connection settings */
            $mail->isSMTP();
            $mail->Host = $config["mail"]["smtpserv"];
            $mail->SMTPAuth = true; 
            $mail->Username = $config["mail"]["username"];
            $mail->Password = $config["mail"]["password"];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            /* smtp mail settings */
            $mail->setFrom($config["mail"]["fromaddress"], $config["mail"]["fromname"]);
            $mail->addAddress(str_contains($entry["entryZxNick"], "@") ? $entry["entryZxNick"] : ($entry["entryZxNick"] . "@student.uni-tuebingen.de"), $entry["entryName"]);
            $mail->addReplyTo('fsi@fsi.uni-tuebingen.de', 'FSI');
            $mail->CharSet = "UTF-8";
            /* content */
            $mail->isHTML(false);
            $mail->Subject = "Helfiliste " . $eventInfo["eventName"];
            $mail->Body = "Hallo {$entry["entryName"]}!\n\nVielen Dank für Deine Hilfe. Du hast Dich für folgende Schicht eingetragen:\n
Veranstaltung: {$eventInfo["eventName"]}
Datum: {$eventInfo["eventDate"]}
Schicht: {$taskName} ({$shiftName})\n
Falls Du Dich abmelden möchtest, benutze bitte folgenden Link: \n
{$config["baseUrl"]}?action=unregister&hash={$entry["entryHash"]}
\n\n
Mit freundlichen Grüßen
Fachschaft Informatik";
            $mail->send();
        } catch (Exception $e) {
            flock($fp, LOCK_UN);
            fclose($fp);
            return array(
                "message" => "Fehler: Bestätigungsmail konnte nicht versendet werden.<br/>" . $mail->ErrorInfo,
                "style" => "error"
            );
        }
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
        $toast = array(
            "message" => "Diese Schicht ist leider schon voll!",
            "style" => "error"      
        );   
    } 
    flock($fp, LOCK_UN);
    fclose($fp);
    return $toast;
}