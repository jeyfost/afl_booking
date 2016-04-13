<?php

session_start();
include ('connect.php');

$_SESSION['sort'] = "type";

if(empty($_POST['dep_icao'])) {
    $from = "UUEE";
} else {
    $from = $_POST['dep_icao'];
}

if(!empty($_POST['arr_icao'])) {
    $scheduleResult = $mysqli->query("SELECT * FROM schedule_temp WHERE from_icao = '".$from."' AND to_icao = '".$_POST['arr_icao']."' ORDER BY aircraft");
} else {
    $scheduleResult = $mysqli->query("SELECT * FROM schedule_temp WHERE from_icao = '".$from."' ORDER BY aircraft");
}

while($schedule = $scheduleResult->fetch_assoc()) {
    if(strlen($schedule['number']) < 3) {
        if(strlen($schedule['number']) == 1) {
            $number = "00".$schedule['number'];
        } else {
            $number = "0".$schedule['number'];
        }
    } else {
        $number = $schedule['number'];
    }

    $airportResult = $mysqli->query("SELECT * FROM airports WHERE icao = '".$schedule['from_icao']."'");
    $airport = $airportResult->fetch_assoc();

    $airport2Result = $mysqli->query("SELECT * FROM airports WHERE icao = '".$schedule['to_icao']."'");
    $airport2 = $airport2Result->fetch_assoc();

    $dep_h = substr($schedule['dep'], 0, 2);
    if(substr($dep_h, 0, 1) == '0') {
        $dep_h = substr($dep_h, 1, 1);
    }

    $dep_m = substr($schedule['dep'], 3);

    $arr_h = substr($schedule['arr'], 0, 2);
    if(substr($arr_h, 0, 1) == '0') {
        $arr_h = substr($arr_h, 1, 1);
    }

    $arr_m = substr($schedule['arr'], 3);

    if($arr_h > $dep_h) {
        $h = $arr_h - $dep_h;
    } else {
        $h = (int)$arr_h - (int)$dep_h + 24;
    }

    if($arr_m >= $dep_m) {
        $m = $arr_m - $dep_m;
    } else {
        $m = (int)$arr_m - (int)$dep_m + 60;
        $h--;
    }

    if(strlen($h) == 1) {
        $h = "0".$h;
    }

    if(strlen($m) == 1) {
        $m = "0".$m;
    }


    $time = $h.":".$m;

    echo "
                        <tr>
                            <td>AFL".$number."</td>
                            <td>".$schedule['aircraft']."</td>
                            <td><img src='img/flags/".$airport['iso_code'].".png' title='".$airport['country']."' /><a target='_blank' title='".$airport['city']."' href='http://va-aeroflot.su/airport/".$airport['icao']."'>".$airport['name']."<br />(".$airport['icao'].")</a></td>
                            <td><img src='img/flags/".$airport2['iso_code'].".png' title='".$airport2['country']."' /><a target='_blank' title='".$airport2['city']."' href='http://va-aeroflot.su/airport/".$airport2['icao']."'>".$airport2['name']."<br />(".$airport2['icao'].")</a></td>
                            <td>".$schedule['dep'].":00</td>
                            <td>".$schedule['arr'].":00</td>
                            <td>".$time.":00</td>
                        </tr>
                    ";
}

echo "<br /><br />Количество доступных рейсов: ".$count;