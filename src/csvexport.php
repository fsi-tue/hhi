<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function handleCsvExport($config, &$eventInfo) {
    /* generate entry csv */
    $colDelim = ";";
    $rowDelim = "\n";
    $result = implode($colDelim, array("task", "shift", "name", "mail", "timestamp")) . $rowDelim;
    foreach($eventInfo["eventTasks"] as $taskIndex => $task) {
        foreach($task["taskShifts"] as $shiftIndex => $shift) {
            if( ! isset($shift["entries"])) continue;
            foreach($shift["entries"] as $entryIndex => $entry) {
                $result .= html_entity_decode($task["taskName"]) . $colDelim;
                $result .= html_entity_decode($shift["shiftName"]) . $colDelim;
                $result .= html_entity_decode($entry["entryName"]) . $colDelim;
                $result .= html_entity_decode($entry["entryMail"]) . $colDelim;
                $result .= date(DATE_ATOM, $entry["entryTimestamp"] ?? 0);
                $result .= $rowDelim;
            }
        }
    }
    /* generate admin mail */
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
        $mail->addAddress($config["adminMail"], "Event Administrator");
        $mail->CharSet = "UTF-8";
        /* content */
        $mail->isHTML(false);
        $mail->Subject = "EXPORT Helfiliste " . $eventInfo["eventName"];
        $mail->Body = "see attachment";
        $mail->AddStringAttachment($result, "export.csv");
        $mail->send();
        echo "mail sent, everything okay";
    } catch (Exception $e) {
        echo "unknown error";
    }
}