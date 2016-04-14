<?php

include ('connect.php');

$_POST['metar'] = "KBDL 131351Z 03013G21KT 010V050 10SM FEW040 FEW250 08/M03 A3041";

$data = explode(' ', $_POST['metar']);

$counter = 0;

//информация оь аэропорте
$airportResult = $mysqli->query("SELECT * FROM airports WHERE icao = '".$data[$counter]."'");
$airport = $airportResult->fetch_assoc();
$counter++;
//////////////////////////////////////////////////////

//анализатор времени
$day = substr($data[$counter], 0, 2);
$hours = substr($data[$counter], 2, 2);
$minutes = substr($data[$counter], 4, 2);

$time = $hours." ";

if(substr($hours, 0, 1) != "1") {
    if(substr($hours, 1) == "1") {
        $word = "час";
    } elseif (substr($hours, 1) == "2" or substr($hours, 1) == "3" or substr($hours, 1) == "4") {
        $word = "часа";
    } else {
        $word = "часов";
    }
} else {
    $word = "часов";
}

if(substr($minutes, 0, 1) != "1") {
    if(substr($minutes, 1) == "1") {
        $m_word = "минуту";
    } elseif (substr($minutes, 1) == "2" or substr($minutes, 1) == "3" or substr($minutes, 1) == "4") {
        $m_word = "минуты";
    } else {
        $m_word = "минут";
    }
} else {
    $m_word = "мунут";
}

$time  = $time.$word." ".$minutes." ".$m_word." по UTC";
$counter++;
//////////////////////////////////////////////////////

//анализатор ветра
$windDirection = substr($data[$counter], 0, 3);
$windSpeed = substr($data[$counter], 3, 2);

if($windDirection == "VRB") {
    $windDirection = "переменный";
} else {
    $windDirection = "направление = ".$windDirection."&deg;";
}

if(substr($data[$counter], 5, 1) == "G") {
    $gusts = 1;
    $gustsSpeed = substr($data[$counter], 6, 2);
    $gustsMeasure = $windMeasure = substr($data[$counter], 8);
} else {
    $gusts = 0;
    $windMeasure = substr($data[$counter], 5);
}

$counter++;

if(strlen($data[$counter]) == 7 and substr($data[$counter], 3, 1) == "V") {
    $variableFrom = substr($data[$counter], 0, 3);
    $variableTo = substr($data[$counter], 4, 3);
    $variability = 1;
    $counter++;
} else {
    $variability = 0;
}

if($windSpeed > 0) {
    switch($windMeasure) {
        case "KMH":
            if(substr($windSpeed, 0, 1) == "1") {
                $windMeasure = "километров";
            } else {
                if(substr($windSpeed, 1) == "1") {
                    $windMeasure = "километр";
                } elseif(substr($windSpeed, 1) == "2" or substr($windSpeed, 1) == "3" or substr($windSpeed, 1) == "4") {
                    $windMeasure = "километра";
                } else {
                    $windMeasure = "километров";
                }
            }

            $windMeasure .= " в час";
            break;
        case "MPS":
            if(substr($windSpeed, 0, 1) == "1") {
                $windMeasure = "метров";
            } else {
                if(substr($windSpeed, 1) == "1") {
                    $windMeasure = "метр";
                } elseif(substr($windSpeed, 1) == "2" or substr($windSpeed, 1) == "3" or substr($windSpeed, 1) == "4") {
                    $windMeasure = "метра";
                } else {
                    $windMeasure = "метров";
                }
            }

            $windMeasure .= " в секунду";
            break;
        case "KT":
            if(substr($windSpeed, 0, 1) == "1") {
                $windMeasure = "узлов";
            } else {
                if(substr($windSpeed, 1) == "1") {
                    $windMeasure = "узел";
                } elseif(substr($windSpeed, 1) == "2" or substr($windSpeed, 1) == "3" or substr($windSpeed, 1) == "4") {
                    $windMeasure = "узла";
                } else {
                    $windMeasure = "узлов";
                }
            }
            break;
        default:
            break;
    }

    if(substr($windSpeed, 0, 1) == "0") {
        $windSpeed = substr($windSpeed, 1);
    }

    $windTotal = $windDirection.", скорость = ".$windSpeed." ".$windMeasure;

    if($gusts == 1) {
        switch($gustsMeasure) {
            case "KMH":
                if(substr($gustsSpeed, 0, 1) == "1") {
                    $gustsMeasure = "километров";
                } else {
                    if(substr($gustsSpeed, 1) == "1") {
                        $gustsMeasure = "километра";
                    } else {
                        $gustsMeasure = "километров";
                    }
                }

                $gustsMeasure .= " в час";
                break;
            case "MPS":
                if(substr($gustsSpeed, 0, 1) == "1") {
                    $gustsMeasure = "метров";
                } else {
                    if(substr($gustsSpeed, 1) == "1") {
                        $gustsMeasure = "метра";
                    } else {
                        $gustsMeasure = "метров";
                    }
                }

                $gustsMeasure .= " в секунду";
                break;
            case "KT":
                if(substr($gustsSpeed, 0, 1) == "1") {
                    $gustsMeasure = "узлов";
                } else {
                    if(substr($gustsSpeed, 1) == "1") {
                        $gustsMeasure = "узла";
                    } else {
                        $gustsMeasure = "узлов";
                    }
                }
                break;
            default:
                break;
        }

        $windTotal = $windTotal." c порывами до ".$gustsSpeed." ".$gustsMeasure;
    }

    if($variability == 1) {
        $windTotal .= ", направление ветра может изменяться от ".$variableFrom."&deg; до ".$variableTo."&deg;";
    }
} else {
    $windTotal = "у земли штиль";
}
//////////////////////////////////////////////////////

//анализатор видимости
if($data[$counter] == "CAVOK") {
    $visibility = "свыше 10 км, нет облаков ниже 5000 футов или минимальной высоты сектора (в зависимости от того, какое из значений больше), нет никаких погодных явлений на аэродроме и в его окрестностях";
} else {

}


echo "Погода в аэропорту <img src='img/flags/".$airport['iso_code'].".png' title='".$airport['country']."' /> <a href='http://va-aeroflot.su/airport/".$data[0]."' style='margin-left: 0;'>".$airport['name']." (".$data[0].")"."</a> по состоянию на ".$time.":<br /><br /><b>Ветер у земли</b>: ".$windTotal."<br /><br /><b>Видимость</b>: ".$visibility;