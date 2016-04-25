<?php

    session_start();

    if(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) == "ru" and (!isset($_SESSION['language']) or $_SESSION['language'] == "ru")) {
        header("Location: ../?p=1");
    }

    if(empty($_REQUEST['p'])) {
        header("Location: ?p=1");
    }

    include('scripts/connect.php');
    include('scripts/functions.php');

    if(!isset($_SESSION['from'])) {
        $_SESSION['from'] = "UUEE";
    }

    if(!isset($_SESSION['language'])) {
        $_SESSION['language'] = "en";
    }

    $flightsCountResult = $mysqli->query("SELECT * FROM schedule WHERE from_icao LIKE '%".$_SESSION['from']."%'");
    $count = 0;
    while($flightsCount = $flightsCountResult->fetch_assoc()) {
        if($flightsCount['days'] == '0') {
            $count++;
        } else {
            if(strlen($flightsCount['days']) == 1) {
                if($flightsCount['days'] == getDay(gmdate('d'), gmdate('m'), gmdate('Y'))) {
                    $count++;
                }
            } else {
                $days = explode(',', $flightsCount['days']);

                for($i = 0; $i < count($days); $i++) {
                    if($days[$i] == getDay(gmdate('d'), gmdate('m'), gmdate('Y'))) {
                        $count++;
                    }
                }
            }
        }
    }

    if($count > 10) {
        if($count % 10 != 0) {
            $numbers = intval(($count / 10) + 1);
        } else {
            $numbers = intval($count / 10);
        }
    } else {
        $numbers = 1;
    }

    $start = $_REQUEST['p'] * 10 - 10;

    if($_REQUEST['p'] > $numbers or $_REQUEST['p'] < 1 or !is_int((int)$_REQUEST['p'])) {
        header("Location: ?p=1");
    }

    $dateResult = $mysqli->query("SELECT date FROM date");
    $date = $dateResult->fetch_array(MYSQLI_NUM);

    if(gmdate('d-m-Y') != $date[0]) {
        $mysqli->query("UPDATE date SET date = '".gmdate('d-m-Y')."'");
        $mysqli->query("DELETE FROM schedule_temp WHERE days <> '0'");
        $scheduleResult = $mysqli->query("SELECT * FROM schedule");
        while($schedule = $scheduleResult->fetch_assoc()) {
            if (strlen($schedule['days']) == 1) {
                if ($schedule['days'] == getDay(gmdate('d'), gmdate('m'), gmdate('Y'))) {
                    $mysqli->query("INSERT INTO schedule_temp (number, from_icao, to_icao, dep, arr, days, aircraft) VALUES ('" . $schedule['number'] . "', '" . $schedule['from_icao'] . "', '" . $schedule['to_icao'] . "', '" . $schedule['dep'] . "', '" . $schedule['arr'] . "', '" . $schedule['days'] . "', '" . $schedule['aircraft'] . "')");
                }
            } else {
                $days = explode(',', $schedule['days']);

                for ($i = 0; $i < count($days); $i++) {
                    if ($days[$i] == getDay(gmdate('d'), gmdate('m'), gmdate('Y'))) {
                        $mysqli->query("INSERT INTO schedule_temp (number, from_icao, to_icao, dep, arr, days, aircraft) VALUES ('" . $schedule['number'] . "', '" . $schedule['from_icao'] . "', '" . $schedule['to_icao'] . "', '" . $schedule['dep'] . "', '" . $schedule['arr'] . "', '" . $schedule['days'] . "', '" . $schedule['aircraft'] . "')");
                    }
                }
            }
        }
    }

?>

<!doctype html>

<html>

<head>

    <meta charset="utf-8">
    <title>Aeroflot Booking</title>
    <link rel='stylesheet' media='screen' type='text/css' href='css/style.css'>
    <link rel='shortcut icon' href='img/favicon.ico' type='image/x-icon'>

    <script type="text/javascript" src="js/jquery-1.12.3.js"></script>
    <script type="text/javascript" src="js/functions.js"></script>

</head>

<body>

    <div id="languageSelect">
        <a href="scripts/languageSelect.php"><img src="img/flags/RU.png" title="Переключиться на русскую версию" /></a>
    </div>

    <div id="content">
        <form id="departureAirportForm" method="post" style="margin-left: 2px;">
            <label for="dep_icao">Departure ICAO:</label>
            <br />
            <input type="text" name="dep_icao" id="dep_icao" value="<?php echo $_SESSION['from']; ?>" />
        </form>
        <form id="arrivalAirportForm" method="post" style="margin-left: 15px;">
            <label for="arr_icao">Arrival ICAO:</label>
            <br />
            <input type="text" name="arr_icao" id="arr_icao" />
        </form>

        <div style="clear: both;"></div>

        <table id='scheduleTable'>
            <tr class="headTR">
                <td class="headTD">Callsign</td>
                <td class="headTD">Aircraft</td>
                <td class="headTD">Departure Airport</td>
                <td class="headTD">Arrival Airport</td>
                <td class="headTD">Departure Time (UTC)</td>
                <td class="headTD">Arrival Time (UTC)</td>
                <td class="headTD">Time in Flight</td>
            </tr>

            <?php
                if($count == 0) {
                    echo "<th>Unfortunately, flights can not be found.</th>";
                }

                $scheduleResult = $mysqli->query("SELECT * FROM schedule_temp WHERE from_icao LIKE '%".$_SESSION['from']."%' ORDER BY number LIMIT ".$start.", 10");
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

                echo "<br /><br />Quantity of flights available for booking: ".$count;
            ?>
        </table>
        <div id="pageNumbers">
            <?php
                if($numbers > 1) {
                    for($i = 1; $i <= $numbers; $i++) {
                        if($i == $_REQUEST['p']) {
                            echo "<div class='numberBlockActive'>".$i."</div>";
                        } else {
                            echo "<div class='numberBlock' id='b".$i."' onmouseover='changeBlock(\"1\", \"b".$i."\", \"t".$i."\")' onmouseout='changeBlock(\"0\", \"b".$i."\", \"t".$i."\")' onclick='return location.href = \"?p=".$i."\"'><span id='t".$i."'>".$i."</span></div>";
                        }
                    }
                }
            ?>
        </div>

        <div style="clear: both;"></div>

    </div>

    <div style="clear: both;"></div>

    <div id="content2">
        <div id="booking">
            <p>Generate a random flight from Sheremetyevo</p>
            <form id="generateFlightForm" method="post">
                <div class="radioContainer">
                    <label>Aircraft:</label>
                    <br /><br />
                    <input class="radio" type="radio" name="aircraft_type" value="all_types" checked /> Any aircraft<br />
                    <input class="radio" type="radio" name="aircraft_type" value="A320" /> Airbus A320<br />
                    <input class="radio" type="radio" name="aircraft_type" value="A321" /> Airbus A321<br />
                    <input class="radio" type="radio" name="aircraft_type" value="A332" /> Airbus A330-200<br />
                    <input class="radio" type="radio" name="aircraft_type" value="A333" /> Airbus A330-300<br />
                    <input class="radio" type="radio" name="aircraft_type" value="B738" /> Boeing 737-800<br />
                    <input class="radio" type="radio" name="aircraft_type" value="B77W" /> Boeing 777-3M0(ER)<br />
                    <input class="radio" type="radio" name="aircraft_type" value="SU95" /> Sukhoi Superjet 100
                </div>
                <div class="radioContainer" style="margin-left: 20px;">
                    <label>Departure Terminal:</label>
                    <br /><br />
                    <input class="radio" type="radio" name="terminal" value="all_terminals" checked /> Any terminal<br />
                    <input class="radio" type="radio" name="terminal" value="D" /> Terminal D<br />
                    <input class="radio" type="radio" name="terminal" value="E" /> Termianl E<br />
                    <input class="radio" type="radio" name="terminal" value="F" /> Terminal F
                </div>
                <div class="radioContainer" style="margin-left: 20px;">
                    <label>Linking to Real Time Flights:</label>
                    <br /><br />
                    <input class="radio" type="radio" name="real_time" value="all_time" checked /> Without linking<br />
                    <input class="radio" type="radio" name="real_time" value="real" /> Generate flight real prototype of which will take place in 40-80 minutes
                </div>
                <div style="clear: both;"></div>
                <br /><br />
                <input type="button" value="Generate a Flight" id="button" />
            </form>
            <div id="generationResult"></div>
        </div>

        <div style="clear: both;"></div>
    </div>

</body>

</html>