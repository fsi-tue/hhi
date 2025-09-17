<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'lib/PHPMailer/src/Exception.php';
require 'lib/PHPMailer/src/PHPMailer.php';
require 'lib/PHPMailer/src/SMTP.php';
require 'lib/constants.php';
require 'src/utils.php';
require 'src/register.php';
require 'src/unregister.php';
require 'src/pdfexport.php';

/* config values */
$config = json_decode(file_get_contents("./config.json"), true);

/* main event data */
$eventInfo = json_decode(file_get_contents($config["shiftFile"]), true);

/* action processing (POST precedence before GET) */
$action = $_POST['action'] ?? $_GET['action'] ?? null;
$method = isset($_POST["action"]) ? "POST" : (isset($_GET["action"]) ? "GET" : "");
$eventInfo = json_decode(file_get_contents($config["shiftFile"]), true);
switch ($action) {
    case "register":
        if($method == "POST") {
            /* handle registration */
            if($config["enableRegister"]) {
                $msg = handleRegister($_POST, $config, $eventInfo);
            } else {
                $msg = MSG_REGISTER_DISABLED;
            }
            /* forward to GET location */
            header('Location: ' . $config['baseUrl'] . '?action=register&msg=' . $msg, true, 303);
            exit(0);
        }
        if($method == "GET") {
            switch($_GET["msg"]) {
                case MSG_REGISTER_SUCCESS:
                    $toast = array("style" => "success", "message" => "Deine Registrierung wurde gespeichert. Falls Du Dich austragen möchtest, kannst Du das über den Link in der Bestätigungsmail tun.");    
                    break;
                case MSG_REGISTER_UNKNOWN:
                    $toast = array("style" => "error", "message" => "Fehler bei Registrierung: Unbekannte Aufgabe/Schicht.");
                    break;
                case MSG_REGISTER_FAILURE:
                    $toast = array("style" => "error", "message" => "Fehler: Bestätigungsmail konnte nicht versendet werden (ZX-Kürzel unbekannt).<br/><br/>Eintrag im Dienstplan wurde <b>nicht</b> erstellt."); 
                    break;
                case MSG_REGISTER_NOSPACE:
                    $toast = array("style" => "error", "message" => "Diese Schicht ist leider schon voll!");   
                    break;
                case MSG_REGISTER_DISABLED:
                    $toast = array("style" => "warning", "message" => "Die Registrierung für diese Veranstaltung wurde deaktiviert.");
                    break;
            }
        }
        break;
    case "unregisterDialog":
        $hash = $_GET["hash"];
        $unregisterLink = "{$config["baseUrl"]}?action=unregister&hash={$hash}";
        if(isHashExisting($_GET['hash'] ?? '', $config, $eventInfo)) {
            $toast = array("style" => "primary", "message" => "<h3>Abmeldung</h3><p>Möchtest Du dich wirklich aus Deiner Schicht abmelden?</p><p><a class='btn' href='{$unregisterLink}'>Ja, Abmelden!</a></p>");
        }
        break;
    case "unregister":
        if( ! isset($_GET["msg"])) {
            if($config["enableUnregister"]) {
                $msg = handleUnregister($_GET['hash'] ?? '', $config, $eventInfo);
            } else {
                $msg = MSG_UNREGISTER_DISABLED;
            }
            /* forward to GET location */
            header('Location: ' . $config['baseUrl'] . '?action=unregister&msg=' . $msg, true, 303);
            exit(0);
        }
        if(isset($_GET["msg"])) {
            switch($_GET["msg"]) {
                case MSG_UNREGISTER_SUCCESS:
                    $toast= array("style" => "success", "message" => "Du hast Dich erfolgreich aus Deiner Schicht abgemeldet.");
                    break;
                case MSG_UNREGISTER_UNKNOWN:
                    $toast = array("style" => "error", "message" => "Fehler: Unbekannter Fingerprint oder Abmeldung bereits durchgeführt.");
                    break;
                case MSG_UNREGISTER_DISABLED:
                    $toast = array("style" => "warning", "message" => "Die Abmeldung für diese Veranstaltung wurde deaktiviert.");
                    break;
            }
        }
        break;
    case "export":
        handlePdfExport($config, $eventInfo);
        /* what to do? */
        exit(0);
        break;
}

/* dynamically calculate occupancy */
calculcateOccupancy($eventInfo);

/* read authors from file */
$authors = implode(", ", explode("\n", file_get_contents("./AUTHORS")));

/* call template */
include("template.htm");
?>