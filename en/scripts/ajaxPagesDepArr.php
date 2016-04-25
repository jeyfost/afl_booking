<?php

include('connect.php');

if($_POST['dep_icao'] == "") {
    $from = "UUEE";
} else {
    $from = $_POST['dep_icao'];
}

if($_POST['arr_icao'] == "") {
    $flightsCountResult = $mysqli->query("SELECT COUNT(number) FROM schedule_temp WHERE from_icao LIKE '%".$from."%'");
} else {
    $flightsCountResult = $mysqli->query("SELECT COUNT(number) FROM schedule_temp WHERE from_icao LIKE '%".$from."%' AND to_icao LIKE '%".$_POST['arr_icao']."%'");
}

$flightsCount = $flightsCountResult->fetch_array(MYSQLI_NUM);

if($flightsCount[0] > 10) {
    if($flightsCount[0] % 10 != 0) {
        $numbers = intval(($flightsCount[0] / 10) + 1);
    } else {
        $numbers = intval($flightsCount[0] / 10);
    }
} else {
    $numbers = 1;
}

if($numbers > 1) {
    echo "<div class='numberBlockActive'>1</div>";
    for($i = 2; $i <= $numbers; $i++) {
        echo "<div class='numberBlock' id='b".$i."' onmouseover='changeBlock(\"1\", \"b".$i."\", \"t".$i."\")' onmouseout='changeBlock(\"0\", \"b".$i."\", \"t".$i."\")' onclick='return location.href = \"?p=".$i."\"'><span id='t".$i."'>".$i."</span></div>";
    }
}