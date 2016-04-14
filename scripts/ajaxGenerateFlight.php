<?php

include ('connect.php');
require_once ('curl/curl.php');
require_once ('phpquery/phpQuery/phpQuery.php');

if($_POST['real_time'] == "all_time") {
    if($_POST['aircraft_type'] == "all_types") {
        $flightResult = $mysqli->query("SELECT * FROM schedule_temp WHERE from_icao = 'UUEE' ORDER BY RAND() LIMIT 0, 1");
    } else {
        $flightResult = $mysqli->query("SELECT * FROM schedule_temp WHERE from_icao = 'UUEE' AND aircraft = '".$_POST['aircraft_type']."' ORDER BY RAND() LIMIT 0, 1");
    }

    $flight = $flightResult->fetch_assoc();
} else {
    if($_POST['aircraft_type'] == "all_types") {
        $flightResult = $mysqli->query("SELECT * FROM schedule_temp WHERE from_icao = 'UUEE'");
    } else {
        $flightResult = $mysqli->query("SELECT * FROM schedule_temp WHERE from_icao = 'UUEE' AND aircraft = '".$_POST['aircraft_type']."'");
    }

    $flights_temp = array();
    $h_now = gmdate('H');
    $m_now = gmdate('i');
    $n_time = (int)$h_now * 60 + (int)$m_now;

    while($flight_temp = $flightResult->fetch_assoc()) {
        $h = substr($flight_temp['dep'], 0, 2);
        $m = substr($flight_temp['dep'], 3);

        $f_time = (int)$h * 60 + (int)$m;


        if($f_time >= ($n_time + 40) and $f_time <= ($n_time + 80)) {
            array_push($flights_temp, $flight_temp);
        }
    }

    if(count($flights_temp) == 0) {
        echo "<div style='width: 100%; margin-top: 400px; float; left; position: relative;'>Ни одного рейса в заданный промежуток времени не найдено.</div><div style='clear: both;'></div>";
    } else {
        $flight = $flights_temp[rand(0, count($flights_temp) - 1)];
    }
}

if(!empty($flight)) {
    $airport1Result = $mysqli->query("SELECT * FROM airports WHERE icao = 'UUEE'");
    $airport1 = $airport1Result->fetch_assoc();

    $airport2Result = $mysqli->query("SELECT * FROM airports WHERE icao = '".$flight['to_icao']."'");
    $airport2 = $airport2Result->fetch_assoc();

    if(strlen($flight['number']) < 3) {
        if(strlen($flight['number']) == 1) {
            $number = "00".$flight['number'];
        } else {
            $number = "0".$flight['number'];
        }
    } else {
        $number = $flight['number'];
    }

    $dep_h = substr($flight['dep'], 0, 2);
    if(substr($dep_h, 0, 1) == '0') {
        $dep_h = substr($dep_h, 1, 1);
    }

    $dep_m = substr($flight['dep'], 3);

    $arr_h = substr($flight['arr'], 0, 2);
    if(substr($arr_h, 0, 1) == '0') {
        $arr_h = substr($arr_h, 1, 1);
    }

    $arr_m = substr($flight['arr'], 3);

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

    if($_POST['terminal'] == "all_terminals") {
        $random_number = rand(1, 3);
        switch($random_number) {
            case "1":
                $terminal = "D";
                break;
            case "2":
                $terminal = "E";
                break;
            case "3":
                $terminal = "F";
                break;
            default:
                $terminal = "D";
                break;
        }
    } else {
        $terminal = $_POST['terminal'];
    }

    switch($terminal) {
        case "D":
            if($flight['aircraft'] == 'A320' or $flight['aircraft'] == 'A321' or $flight['aircraft'] == 'SU95' or $flight['aircraft'] == 'B738') {
                $parking = array('2', '3', '4', '5', '6', '7', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '23', '24', '26', '27', '28', '29', '30', '31', '32');
                $random_number = rand(0, count($parking) - 1);
                $stand = $parking[$random_number];
            } else {
                $parking = array('1', '8', '20', '22', '25');
                $random_number = rand(0, count($parking) - 1);
                $stand = $parking[$random_number];
            }
            break;
        case "E":
            if($flight['aircraft'] == 'A320' or $flight['aircraft'] == 'A321' or $flight['aircraft'] == 'SU95' or $flight['aircraft'] == 'B738') {
                $parking = array('33', '34', '36', '37', '38');
                $random_number = rand(0, count($parking) - 1);
                $stand = $parking[$random_number];
            } else {
                $stand = 35;
            }
            break;
        case "F":
            if($flight['aircraft'] == 'A320' or $flight['aircraft'] == 'A321' or $flight['aircraft'] == 'SU95' or $flight['aircraft'] == 'B738') {
                $parking = array('39', '40', '41', '42', '45', '47', '48', '49', '50', '51', '52', '53');
                $random_number = rand(0, count($parking) - 1);
                $stand = $parking[$random_number];
            } else {
                $parking = array('43', '44', '46');
                $random_number = rand(0, count($parking) - 1);
                $stand = $parking[$random_number];
            }
            break;
        default:
            break;
    }

    $curl = new Curl();
    $response = $curl->get('http://va-aeroflot.su/fleet/list/index.php');

    $doc = phpQuery::newDocument($response->body);

    $fleet = $doc->find('.panel');

    $data_strings = array();

    foreach ($fleet as $plane) {
        $pq = pq($plane);
        array_push($data_strings, $pq->find('td')->text());
    }

    switch($flight['aircraft']) {
        case "A320":
            $data = preg_replace('/[^a-zа-яё0-9]+/iu', '', $data_strings[1]);
            $data2 = explode('AirbusA320', $data);

            $planes = array();

            for($i = 1; $i < count($data2); $i++) {
                if(substr($data2[$i], strlen($data2[$i]) - 9) == "Avaliable" and substr($data2[$i], 5, 4) == "UUEE") {
                    array_push($planes, substr($data2[$i], 0, 5));
                }
            }

            if(count($planes) > 0) {
                $reg = $planes[rand(0, count($planes) - 1)];
                $reg = substr($reg, 0, 2)."-".substr($reg, 2, 3);
            } else {
                $reg = "";
            }
            break;
        case "A321":
            $data = preg_replace('/[^a-zа-яё0-9]+/iu', '', $data_strings[2]);
            $data2 = explode('AirbusA321', $data);

            $planes = array();

            for($i = 1; $i < count($data2); $i++) {
                if(substr($data2[$i], strlen($data2[$i]) - 9) == "Avaliable" and substr($data2[$i], 5, 4) == "UUEE") {
                    array_push($planes, substr($data2[$i], 0, 5));
                }
            }

            if(count($planes) > 0) {
                $reg = $planes[rand(0, count($planes) - 1)];
                $reg = substr($reg, 0, 2)."-".substr($reg, 2, 3);
            } else {
                $reg = "";
            }
            break;
        case "B738":
            $data = preg_replace('/[^a-zа-яё0-9]+/iu', '', $data_strings[5]);
            $data2 = explode('Boeing737800', $data);

            $planes = array();

            for($i = 1; $i < count($data2); $i++) {
                if(substr($data2[$i], strlen($data2[$i]) - 9) == "Avaliable" and substr($data2[$i], 5, 4) == "UUEE") {
                    array_push($planes, substr($data2[$i], 0, 5));
                }
            }

            if(count($planes) > 0) {
                $reg = $planes[rand(0, count($planes) - 1)];
                $reg = substr($reg, 0, 2)."-".substr($reg, 2, 3);
            } else {
                $reg = "";
            }
            break;
        case "SU95":
            $data = preg_replace('/[^a-zа-яё0-9]+/iu', '', $data_strings[7]);
            $data2 = explode('SukhoiSuperjet100', $data);

            $planes = array();

            for($i = 1; $i < count($data2); $i++) {
                if(substr($data2[$i], strlen($data2[$i]) - 9) == "Avaliable" and substr($data2[$i], 7, 4) == "UUEE") {
                    array_push($planes, substr($data2[$i], 0, 7));
                }
            }

            if(count($planes) > 0) {
                $reg = $planes[rand(0, count($planes) - 1)];
                $reg = substr($reg, 0, 2)."-".substr($reg, 2, 5);
            } else {
                $reg = "";
            }
            break;
        case "A332":
            $data = preg_replace('/[^a-zа-яё0-9]+/iu', '', $data_strings[3]);
            $data2 = explode('AirbusA330200', $data);

            $planes = array();

            for($i = 1; $i < count($data2); $i++) {
                if(substr($data2[$i], strlen($data2[$i]) - 9) == "Avaliable" and substr($data2[$i], 5, 4) == "UUEE") {
                    array_push($planes, substr($data2[$i], 0, 5));
                }
            }

            if(count($planes) > 0) {
                $reg = $planes[rand(0, count($planes) - 1)];
                $reg = substr($reg, 0, 2)."-".substr($reg, 2, 3);
            } else {
                $reg = "";
            }
            break;
        case "A333":
            $data = preg_replace('/[^a-zа-яё0-9]+/iu', '', $data_strings[4]);
            $data2 = explode('AirbusA330300', $data);

            $planes = array();

            for($i = 1; $i < count($data2); $i++) {
                if(substr($data2[$i], strlen($data2[$i]) - 9) == "Avaliable" and substr($data2[$i], 5, 4) == "UUEE") {
                    array_push($planes, substr($data2[$i], 0, 5));
                }
            }

            if(count($planes) > 0) {
                $reg = $planes[rand(0, count($planes) - 1)];
                $reg = substr($reg, 0, 2)."-".substr($reg, 2, 3);
            } else {
                $reg = "";
            }
            break;
        case "B77W":
            $data = preg_replace('/[^a-zа-яё0-9]+/iu', '', $data_strings[6]);
            $data2 = explode('Boeing7773M0ER', $data);

            $planes = array();

            for($i = 1; $i < count($data2); $i++) {
                if(substr($data2[$i], strlen($data2[$i]) - 9) == "Avaliable" and substr($data2[$i], 5, 4) == "UUEE") {
                    array_push($planes, substr($data2[$i], 0, 5));
                }
            }

            if(count($planes) > 0) {
                $reg = $planes[rand(0, count($planes) - 1)];
                $reg = substr($reg, 0, 2)."-".substr($reg, 2, 3);
            } else {
                $reg = "";
            }
            break;
        default:
            break;
    }

    echo "
        <div style='width: 100%; float: left; height: 40px;'></div>
        <p style='margin-left: 2px;'>Ваш рейс:</p>
        <table>
            <tr class=\"headTR\">
                <td class=\"headTD\">Позывной</td>
                <td class=\"headTD\">Тип</td>
                <td class=\"headTD\">Аэропорт вылета</td>
                <td class=\"headTD\">Аэропорт прилёта</td>
                <td class=\"headTD\">Время вылета (UTC)</td>
                <td class=\"headTD\">Время прилёта (UTC)</td>
                <td class=\"headTD\">Время в пути</td>
            </tr>
            <tr>
                <td>AFL".$number."</td>
                <td>".$flight['aircraft']."<br /><a href='http://va-aeroflot.su/fleet/".$reg."'>".$reg."</a></td>
                <td><img src='img/flags/".$airport1['iso_code'].".png' title='".$airport1['country']."' /><a target='_blank' title='".$airport1['city']."' href='http://va-aeroflot.su/airport/".$airport1['icao']."'>".$airport1['name']."<br />(".$airport1['icao'].")</a><br /><br />Стоянка ".$terminal.$stand."</td>
                <td><img src='img/flags/".$airport2['iso_code'].".png' title='".$airport2['country']."' /><a target='_blank' title='".$airport2['city']."' href='http://va-aeroflot.su/airport/".$airport2['icao']."'>".$airport2['name']."<br />(".$airport2['icao'].")</a></td>
                <td>".$flight['dep'].":00</td>
                <td>".$flight['arr'].":00</td>
                <td>".$time.":00</td>
            </tr>
        </table>
        <div style='width: 100% margin-top: 20px;' id='weatherBlock'>
            <br />
            <center><p class='showWeatherText' onclick='showWeather(\"".$airport2['icao']."\")'>Показать погоду в аэропортах вылета и назначения</p></center>
        </div>
    ";
}