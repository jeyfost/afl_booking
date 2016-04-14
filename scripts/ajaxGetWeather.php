<?php

    include('connect.php');
    require_once ('curl/curl.php');
    require_once ('phpquery/phpQuery/phpQuery.php');

    $curl1 = new Curl();
    $response1 = $curl1->get('http://va-aeroflot.su/airport/UUEE/index.php');
    $doc1 = phpQuery::newDocument($response1->body);
    $fleet1 = $doc1->find('.panel');
    $data_strings1 = array();

    foreach($fleet1 as $plane1) {
        $pq1 = pq($plane1);
        array_push($data_strings1, $pq1->find('pre')->text());
    }

    foreach ($data_strings1 as $data1) {
        if(substr($data1, 0, 4) == "UUEE") {
            $w1 = explode('UUEE ', $data1);
        }
    }

    $curl2 = new Curl();
    $response2 = $curl2->get('http://va-aeroflot.su/airport/'.$_POST['icao'].'/index.php');
    $doc2 = phpQuery::newDocument($response2->body);
    $fleet2 = $doc2->find('.panel');
    $data_strings2 = array();

    foreach($fleet2 as $plane2) {
        $pq2 = pq($plane2);
        array_push($data_strings2, $pq2->find('pre')->text());
    }

    foreach ($data_strings2 as $data2) {
        if(substr($data2, 0, 4) == $_POST['icao']) {
            $w2 = explode($_POST['icao'].' ', $data2);
        }
    }

    $airportResult = $mysqli->query("SELECT * FROM airports WHERE icao = '".$_POST['icao']."'");
    $airport = $airportResult->fetch_assoc();

    echo "
        <br />
            <p>Погода в аэропортах вылета и назначения:</p>
            <div class='metar_taf'>
                <img src='img/flags/RU.png' title='Russian Federation' /> <a href='http://va-aeroflot.su/airport/UUEE' style='margin-left: 0;'>Sheremetyevo (UUEE)</a>:<br /><br />
                <div class='metar_taf_inner' id='depMetarBlock' style='margin-top: 0;'><b>Metar</b>: <span id='depMetar'>UUEE ".$w1[1]."</span></div><div class='metar_taf_inner'><b>TAF</b>: UUEE ".$w1[2]."</div>
                <p class='showWeatherText' onclick='decodeMetar(\"depMetarBlock\")'>Расшифровать код метар</p>
            </div>
            <div class='metar_taf'>
                <img src='img/flags/".$airport['iso_code'].".png' title='".$airport['country']."' /> <a href='http://va-aeroflot.su/airport/".$airport['icao']."' style='margin-left: 0;'>".$airport['name']." (".$airport['icao'].")</a>:<br /><br />
                <div class='metar_taf_inner' id='arrMetarBlock' style='margin-top: 0;'><b>Metar</b>: <span id='arrMetar'>".$_POST['icao']." ".$w2[1]."</span></div><div class='metar_taf_inner'><b>TAF</b>: ".$_POST['icao']." ".$w2[2]."</div>
                <p class='showWeatherText' onclick='decodeMetar(\"arrMetarBlock\")'>Расшифровать код метар</p>
            </div>
    ";