<?php
$eventInfo = json_decode(file_get_contents("./shifts.json"), true);
if(isset($_POST["action"])) {
    $toast = array(
        "message" => "Deine Registrierung wurde gespeichert. Falls Du Dich austragen möchtest, kannst Du das über den Link in der Bestätigungsmail tun.",
        "style" => "success"      
    );
}
include("template.htm");
?>