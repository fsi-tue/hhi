<?php
$eventInfo = json_decode(file_get_contents("./shifts.json"), true);
//var_dump($eventInfo);
include("template.htm");
?>