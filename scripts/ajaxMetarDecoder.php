<?php

include ('connect.php');

$_POST['metar'] = "LLBG 140830Z 10010KT 050V150 0050S5000W R04/P1500N R22/0200V1000D FG VV001 15/15 Q1012";

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

$time = $day."-е число этого месяца, ".$hours." ";

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

if($data[$counter] == "AUTO") {
    $counter++;
}

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
    if(strpos($data[$counter], "SM") === false) {
        if($data[$counter] == 9999) {
            $visibility = "свыше 10 км";
            $counter++;
        } else {
            if(strlen($data[$counter]) == 5 or strlen($data[$counter]) == 6) {
                $distance = (int)substr($data[$counter], 0, 4);
                $direction = substr($data[$counter], 4);

                switch($direction){
                    case "N":
                        $direction = "северном";
                        break;
                    case "NE":
                        $direction = "северо-восточном";
                        break;
                    case "NW":
                        $direction = "северо-западном";
                        break;
                    case "E":
                        $direction = "восточном";
                        break;
                    case "S":
                        $direction = "южном";
                        break;
                    case "SE":
                        $direction = "юго-восточном";
                        break;
                    case "SW":
                        $direction = "юго-западном";
                        break;
                    case "W":
                        $direction = "западном";
                        break;
                    default:
                        break;
                }

                $visibility = $distance." метров в ".$direction." напрвлении";
                $counter++;
            } else {
                if(strlen($data[$counter]) > 6) {
                    $distance1 = (int)substr($data[$counter], 0, 4);

                    if(ctype_alpha(substr($data[$counter], 4, 2))) {
                        $start = 6;
                        $direction1 = substr($data[$counter], 4, 2);
                    } elseif(ctype_alpha(substr($data[$counter], 4, 1))) {
                        $start = 5;
                        $direction1 = substr($data[$counter], 4, 1);
                    }

                    switch($direction1){
                        case "N":
                            $direction1 = "северном";
                            break;
                        case "NE":
                            $direction1 = "северо-восточном";
                            break;
                        case "NW":
                            $direction1 = "северо-западном";
                            break;
                        case "E":
                            $direction1 = "восточном";
                            break;
                        case "S":
                            $direction1 = "южном";
                            break;
                        case "SE":
                            $direction1 = "юго-восточном";
                            break;
                        case "SW":
                            $direction1 = "юго-западном";
                            break;
                        case "W":
                            $direction1 = "западном";
                            break;
                        default:
                            break;
                    }

                    $distance2 = (int)substr($data[$counter], $start, 4);
                    $direction2 = substr($data[$counter], $start + 4);

                    switch($direction2){
                        case "N":
                            $direction2 = "северном";
                            break;
                        case "NE":
                            $direction2 = "северо-восточном";
                            break;
                        case "NW":
                            $direction2 = "северо-западном";
                            break;
                        case "E":
                            $direction2 = "восточном";
                            break;
                        case "S":
                            $direction2 = "южном";
                            break;
                        case "SE":
                            $direction2 = "юго-восточном";
                            break;
                        case "SW":
                            $direction2 = "юго-западном";
                            break;
                        case "W":
                            $direction2 = "западном";
                            break;
                        default:
                            break;
                    }

                    $visibility = $distance1." метров в ".$direction1." напрвлении и ".$distance2." метров в ".$direction2." направлении";
                    $counter++;
                } else {
                    $visibility = (int)$data[$counter]." метров";
                    $counter++;
                }
            }
        }
    } else {
        $pos = strpos($data[$counter], "SM");
        $distance = substr($data[$counter], 0, $pos);

        if(strlen($distance) == 1) {
            if($distance == 1) {
                $distanceMeasure = "миля";
            } elseif($distance == 2 or $distance == 3 or $distance == 4) {
                $distanceMeasure = "мили";
            } else {
                $distanceMeasure = "миль";
            }
        } else {
            if(substr($distance, 1, 1) == "/") {
                $distanceMeasure = "мили";
            } else {
                if (substr($distance, 0, 1) == "1") {
                    $distanceMeasure = "миль";
                } else {
                    if (substr($distance, 1) == "2" or substr($distance, 1) == "3" or substr($distance, 1) == "4") {
                        $distanceMeasure = "мили";
                    } elseif (substr($distance, 1) == "1") {
                        $distanceMeasure = "миля";
                    } else {
                        $distanceMeasure = "миль";
                    }
                }
            }
        }

        $visibility = $distance." ".$distanceMeasure;
        $counter++;
    }
}

//анализатор видимости по полосе
if(substr($data[$counter], 0, 1) == "R" and is_numeric(substr($data[$counter], 1, 2))) {
    if(ctype_alpha(substr($data[$counter], 3, 1))) {
        $runway = substr($data[$counter], 1, 3);
        $start = 4;
    } else {
        $runway = substr($data[$counter], 1, 2);
        $start = 3;
    }

    if(substr($data[$counter], $start + 1, 1) == "P" or substr($data[$counter], $start + 1, 1) == "M") {
        switch(substr($data[$counter], $start + 1, 1)) {
            case "P":
                $visibilityIs = "более";
                break;
            case "M":
                $visibilityIs = "менее";
                break;
            default:
                break;
        }

        if(ctype_alpha(substr($data[$counter], $start + 6))) {
            switch(substr($data[$counter], $start + 6)) {
                case "D":
                    $visibilityForecast = "Прогнозируется ухудшение видимости";
                    break;
                case "U":
                    $visibilityForecast = "Прогнозируется улучшение видимости";
                    break;
                case "N":
                    $visibilityForecast = "Значительных изменений в видимости не прогнозируется";
                    break;
                default:
                    break;
            }

            $visibility .= ". Видимость на полосе ".$runway." ".$visibilityIs." ".(int)substr($data[$counter], $start + 2, 4)." метров. ".$visibilityForecast;
            $counter++;
        } else {
            $visibility .= ". Видимость на полосе ".$runway." ".$visibilityIs." ".(int)substr($data[$counter], $start + 2, 4)." метров.";
            $counter++;
        }
    } else {
        if(substr($data[$counter], $start + 5, 1) == "V") {
            if(ctype_alpha(substr($data[$counter], $start + 10))) {
                switch(substr($data[$counter], $start + 10)) {
                    case "D":
                        $visibilityForecast = "Прогнозируется ухудшение видимости";
                        break;
                    case "U":
                        $visibilityForecast = "Прогнозируется улучшение видимости";
                        break;
                    case "N":
                        $visibilityForecast = "Значительных изменений в видимости не прогнозируется";
                        break;
                    default:
                        break;
                }

                $visibility .= ". Видимость на полосе ".$runway." варьируется от ".(int)substr($data[$counter], $start + 1, 4)." до ".(int)substr($data[$counter], $start + 6, 4)." метров. ".$visibilityForecast;
                $counter++;
            } else {
                $visibility .= ". Видимость на полосе ".$runway." варьируется от ".(int)substr($data[$counter], $start + 1, 4)." до ".(int)substr($data[$counter], $start + 6)." метров.";
                $counter++;
            }
        } else {
            if(ctype_alpha(substr($data[$counter], $start + 5))) {
                switch(substr($data[$counter], $start + 5)) {
                    case "D":
                        $visibilityForecast = "Прогнозируется ухудшение видимости";
                        break;
                    case "U":
                        $visibilityForecast = "Прогнозируется улучшение видимости";
                        break;
                    case "N":
                        $visibilityForecast = "Значительных изменений в видимости не прогнозируется";
                        break;
                    default:
                        break;
                }
                $visibility .= ". Видимость на полосе ".$runway." составляет ".(int)substr($data[$counter], $start + 1, 4)." метров. ".$visibilityForecast;
                $counter++;
            } else {
                $visibility .= ". Видимость на полосе ".$runway." составляет ".(int)substr($data[$counter], $start + 1)." метров";
                $counter++;
            }
        }
    }
}
//////////////////////////////////////////////////////

//анализатор видимости по другой полосе, если она есть
if(substr($data[$counter], 0, 1) == "R" and is_numeric(substr($data[$counter], 1, 2))) {
    if(ctype_alpha(substr($data[$counter], 3, 1))) {
        $runway = substr($data[$counter], 1, 3);
        $start = 4;
    } else {
        $runway = substr($data[$counter], 1, 2);
        $start = 3;
    }

    if(substr($data[$counter], $start + 1, 1) == "P" or substr($data[$counter], $start + 1, 1) == "M") {
        switch(substr($data[$counter], $start + 1, 1)) {
            case "P":
                $visibilityIs = "более";
                break;
            case "M":
                $visibilityIs = "менее";
                break;
            default:
                break;
        }

        if(ctype_alpha(substr($data[$counter], $start + 6))) {
            switch(substr($data[$counter], $start + 6)) {
                case "D":
                    $visibilityForecast = "Прогнозируется ухудшение видимости";
                    break;
                case "U":
                    $visibilityForecast = "Прогнозируется улучшение видимости";
                    break;
                case "N":
                    $visibilityForecast = "Значительных изменений в видимости не прогнозируется";
                    break;
                default:
                    break;
            }

            $visibility .= ". Видимость на полосе ".$runway." ".$visibilityIs." ".(int)substr($data[$counter], $start + 2, 4)." метров. ".$visibilityForecast;
            $counter++;
        } else {
            $visibility .= ". Видимость на полосе ".$runway." ".$visibilityIs." ".(int)substr($data[$counter], $start + 2, 4)." метров.";
            $counter++;
        }
    } else {
        if(substr($data[$counter], $start + 5, 1) == "V") {
            if(ctype_alpha(substr($data[$counter], $start + 10))) {
                switch(substr($data[$counter], $start + 10)) {
                    case "D":
                        $visibilityForecast = "Прогнозируется ухудшение видимости";
                        break;
                    case "U":
                        $visibilityForecast = "Прогнозируется улучшение видимости";
                        break;
                    case "N":
                        $visibilityForecast = "Значительных изменений в видимости не прогнозируется";
                        break;
                    default:
                        break;
                }

                $visibility .= ". Видимость на полосе ".$runway." варьируется от ".(int)substr($data[$counter], $start + 1, 4)." до ".(int)substr($data[$counter], $start + 6, 4)." метров. ".$visibilityForecast;
                $counter++;
            } else {
                $visibility .= ". Видимость на полосе ".$runway." варьируется от ".(int)substr($data[$counter], $start + 1, 4)." до ".(int)substr($data[$counter], $start + 6)." метров.";
                $counter++;
            }
        } else {
            if(ctype_alpha(substr($data[$counter], $start + 5))) {
                switch(substr($data[$counter], $start + 5)) {
                    case "D":
                        $visibilityForecast = "Прогнозируется ухудшение видимости";
                        break;
                    case "U":
                        $visibilityForecast = "Прогнозируется улучшение видимости";
                        break;
                    case "N":
                        $visibilityForecast = "Значительных изменений в видимости не прогнозируется";
                        break;
                    default:
                        break;
                }
                $visibility .= ". Видимость на полосе ".$runway." составляет ".(int)substr($data[$counter], $start + 1, 4)." метров. ".$visibilityForecast;
                $counter++;
            } else {
                $visibility .= ". Видимость на полосе ".$runway." составляет ".(int)substr($data[$counter], $start + 1)." метров";
                $counter++;
            }
        }
    }
}
//////////////////////////////////////////////////////

if(substr($visibility, strlen($visibility) - 1) != '.') {
    $visibility .= '.';
}

echo "Погода в аэропорту <img src='img/flags/".$airport['iso_code'].".png' title='".$airport['country']."' /> <a href='http://va-aeroflot.su/airport/".$data[0]."' style='margin-left: 0;'>".$airport['name']." (".$data[0].")"."</a> по состоянию на ".$time.":<br /><br /><b>Ветер у земли</b>: ".$windTotal."<br /><br /><b>Видимость</b>: ".$visibility;