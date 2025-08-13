<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'lib/PHPMailer/src/Exception.php';
require 'lib/PHPMailer/src/PHPMailer.php';
require 'lib/PHPMailer/src/SMTP.php';
require 'src/utils.php';
require 'src/register.php';
require 'src/unregister.php';

/* config values */
$config = json_decode(file_get_contents("./config.json"), true);

/* main event data */
$eventInfo = json_decode(file_get_contents($config["shiftFile"]), true);

/* action processing */
$action = $_GET['action'] ?? $_POST['action'] ?? null;
$eventInfo = json_decode(file_get_contents($config["shiftFile"]), true);
switch ($action) {
    case "register":
        $toast = handleRegister($_POST, $config, $eventInfo);
        break;
    case "unregister":
        $toast = handleUnregister($_GET['hash'] ?? '', $config, $eventInfo);
        break;
}

/* dynamically calculate occupancy */
calculcateOccupancy($eventInfo);

/* read authors from file */
$authors = implode(", ", explode("\n", file_get_contents("./AUTHORS")));

/* call template */
include("template.htm");
?>