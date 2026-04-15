<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'lib/PHPMailer/src/Exception.php';
require 'lib/PHPMailer/src/PHPMailer.php';
require 'lib/PHPMailer/src/SMTP.php';
require 'lib/constants.php';
require 'src/i18n.php';
require 'src/utils.php';
require 'src/register.php';
require 'src/unregister.php';
require 'src/pdfexport.php';
require 'src/csvexport.php';

/* config values */
$config = json_decode(file_get_contents("./config.json"), true);
i18n_set_locale($config["language"] ?? I18N_DEFAULT_LOCALE);

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
                    $toast = array("style" => "success", "message" => i18n("toast.register.success"));
                    break;
                case MSG_REGISTER_UNKNOWN:
                    $toast = array("style" => "error", "message" => i18n("toast.register.unknown"));
                    break;
                case MSG_REGISTER_FAILURE:
                    $toast = array("style" => "error", "message" => i18n("toast.register.failure"));
                    break;
                case MSG_REGISTER_NOSPACE:
                    $toast = array("style" => "error", "message" => i18n("toast.register.no_space"));
                    break;
                case MSG_REGISTER_DISABLED:
                    $toast = array("style" => "warning", "message" => i18n("toast.register.disabled"));
                    break;
            }
        }
        break;
    case "unregisterDialog":
        $hash = $_GET["hash"];
        $unregisterLink = "{$config["baseUrl"]}?action=unregister&hash={$hash}";
        if(isHashExisting($_GET['hash'] ?? '', $config, $eventInfo)) {
            $toast = array("style" => "primary", "message" => "<h3>" . i18n("toast.unregister_dialog.title") . "</h3><p>" . i18n("toast.unregister_dialog.question") . "</p><p><a class='btn' href='{$unregisterLink}'>" . i18n("toast.unregister_dialog.confirm") . "</a><a class='btn' href='{$config['baseUrl']}'>" . i18n("toast.unregister_dialog.cancel") . "</a></p>");
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
                    $toast= array("style" => "success", "message" => i18n("toast.unregister.success"));
                    break;
                case MSG_UNREGISTER_UNKNOWN:
                    $toast = array("style" => "error", "message" => i18n("toast.unregister.unknown"));
                    break;
                case MSG_UNREGISTER_DISABLED:
                    $toast = array("style" => "warning", "message" => i18n("toast.unregister.disabled"));
                    break;
            }
        }
        break;
    case "pdfexport":
        if($config["hidePdfExport"]) die(i18n("error.pdf_export_hidden"));
        handlePdfExport($config, $eventInfo);
        exit(0);
    case "csvexport":
        handleCsvExport($config, $eventInfo);
        exit(0);
}

/* dynamically calculate occupancy */
calculcateOccupancy($eventInfo);

/* add special css classes */
appendClasses($eventInfo);

/* hide names */
if($config["hideNames"]) hideEntryNames($eventInfo);

/* read authors from file */
//$authors = implode(", ", explode("\n", file_get_contents("./AUTHORS")));
$authors = implode(
    ", ",
    array_map(
        fn($c) => "<span class='contributor'>" . $c . "</span>",
        explode("\n", file_get_contents("./AUTHORS"))
    )
);

/* call template */
include("template.htm");
?>
