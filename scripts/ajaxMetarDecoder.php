<?php

include ('connect.php');

$_POST['metar'] = "LLBG 140830Z 10010MPS 050V150 1000S1500W R04/P1200N R22/0800V1000D OVC011TCU BKN007 VV005 15/7 Q1003 WS R04L BECMG AT0120 11009G21MPS";

echo "<b>Исходный код</b>: ".$_POST['metar']."<br /><br />";

$data = explode(' ', $_POST['metar']);

$counter = 0;

function decodeTemperature($cell) {
    if(substr($cell, 0, 1) == 'M') {
        $temperature = "-".substr($cell, 1, 2);
    } else {
        $temperature = substr($cell, 0, 2);
    }

    return "<b>Температура</b>: ".$temperature;
};

function decodeDewPoint($cell) {
    if(substr($cell, 0, 1) == 'M') {
        if(substr($cell, 4, 1) == 'M') {
            $dewPoint = "-".substr($cell, 5);
        } else {
            $dewPoint = substr($cell, 4);
        }
    } else {
        if(substr($cell, 3, 1) == 'M') {
            $dewPoint = "-".substr($cell, 4);
        } else {
            $dewPoint = substr($cell, 3);
        }
    }

    return "<b>Точка росы</b>: ".(int)$dewPoint;
}

function decodePhenomena($cells, $c, $adding, $initResult, $functions) {
    $i = $c + $adding;

    if($initResult == "" or $functions == 1) {
        $result = $initResult;
    } else {
        $result = $initResult."; ";
    }

    $conditionsS = array(
        array('code' => 'DZ', 'name' => 'морось', 's' => 'f'),
        array('code' => 'RA', 'name' => 'дождь', 's' => 'm'),
        array('code' => 'SN', 'name' => 'снег', 's' => 'm'),
        array('code' => 'SG', 'name' => 'снежные зёрна', 's' => 'u'),
        array('code' => 'RASN', 'name' => 'дождь со снегом', 's' => 'm'),
        array('code' => 'SNRA', 'name' => 'снег с дождём', 's' => 'm'),
        array('code' => 'SHSN', 'name' => 'ливневый снег', 's' => 'm'),
        array('code' => 'SHRA', 'name' => 'ливневый дождь', 's' => 'm'),
        array('code' => 'SHGR', 'name' => 'град', 's' => 'm'),
        array('code' => 'FZRA', 'name' => 'переохлаждённый дождь', 's' => 'm'),
        array('code' => 'FZDZ', 'name' => 'переохлаждённая морось', 's' => 'f'),
        array('code' => 'TSRA', 'name' => 'гроза с дождём', 's' => 'f'),
        array('code' => 'TSGR', 'name' => 'гроза с градом', 's' => 'f'),
        array('code' => 'TSGS', 'name' => 'гроза со снежной крупой', 's' => 'f'),
        array('code' => 'TSSN', 'name' => 'гроза со снегом', 's' => 'f'),
        array('code' => 'DS', 'name' => 'пыльная буря', 's' => 'f'),
        array('code' => 'SS', 'name' => 'песчаная буря', 's' => 'f'),
    );

    $conditionsW = array(
        array('code' => 'FG', 'name' => 'туман'),
        array('code' => 'VCFG', 'name' => 'туман в окрестности'),
        array('code' => 'FZFG', 'name' => 'переохлаждённый туман'),
        array('code' => 'MIFG', 'name' => 'позёмный туман'),
        array('code' => 'PRFG', 'name' => 'аэродром частично покрыт туманом'),
        array('code' => 'BCFG', 'name' => 'туман местами'),
        array('code' => 'BR', 'name' => 'дымка'),
        array('code' => 'HZ', 'name' => 'мгла'),
        array('code' => 'FU', 'name' => 'дым'),
        array('code' => 'DRSN', 'name' => 'снежный позёмок'),
        array('code' => 'DRSA', 'name' => 'песчаный позёмок'),
        array('code' => 'DRDU', 'name' => 'пыльный позёмок'),
        array('code' => 'DU', 'name' => 'пыльная мгла'),
        array('code' => 'BLSN', 'name' => 'снежная низовая метель'),
        array('code' => 'BLDU', 'name' => 'пыльная низовая метель'),
        array('code' => 'SQ', 'name' => 'шквал'),
        array('code' => 'IC', 'name' => 'ледяный иглы'),
        array('code' => 'TS', 'name' => 'гроза'),
        array('code' => 'VCTS', 'name' => 'гроза в окрестности'),
        array('code' => 'UP', 'name' => 'неопределённый вид осадков'),
        array('code' => 'PL', 'name' => 'ледяной дождь'),
        array('code' => 'VA', 'name' => 'вулканический пепел'),
        array('code' => 'SA', 'name' => 'песок'),
        array('code' => 'PO', 'name' => 'чётко выраженные пыльные или песчаные вихри'),
        array('code' => 'FC', 'name' => 'Воронкообразное облако, смерч, торнадо или водяной смерч'),
    );

    if(substr($cells[$i], 0, 1) == '-' or substr($cells[$i], 0, 1) == '+') {
        if($result == "") {
            $result = "<br /><br /><b>Погодные явления</b>: ";
        }

        $condition = substr($cells[$i], 1);
        $success = 0;

        for($j = 0; $j < count($conditionsS); $j++) {
            if($condition == $conditionsS[$j]['code']) {
                $success++;

                switch(substr($cells[$i], 0, 1)) {
                    case "-":
                        switch($conditionsS[$j]['s']) {
                            case "m":
                                $power = "слабый ";
                                break;
                            case "f":
                                $power = "слабая ";
                                break;
                            case "u":
                                $power = "слабые ";
                                break;
                            default:
                                $power = "слабый ";
                                break;
                        }
                        break;
                    case "+":
                        switch($conditionsS[$j]['s']) {
                            case "m":
                                $power = "сильный ";
                                break;
                            case "f":
                                $power = "сильная ";
                                break;
                            case "u":
                                $power = "сильные ";
                                break;
                            default:
                                $power = "сильный ";
                                break;
                        }
                        break;
                    default:
                        break;
                }
                $result .= $power.$conditionsS[$j]['name'];
            }
        }

        if($success == 0) {
            for($j = 0; $j < count($conditionsW); $j++) {
                if($condition == $conditionsW[$j]['code']) {
                    $success++;

                    switch(substr($cells[$i], 0, 1)) {
                        case "-":
                            $power = "слабый ";
                            break;
                        case "+":
                            $power = "сильный ";
                            break;
                        default:
                            break;
                    }
                    $result .= $power.$conditionsW[$j]['name'];
                }
            }
        }
    } else {
        $condition = $cells[$i];
        $success = 0;

        for($j = 0; $j < count($conditionsS); $j++) {
            if($condition == $conditionsS[$j]['code']) {
                $success++;

                if($result == "") {
                    $result = "<br /><br /><b>Погодные явления</b>: ";
                }

                $result .= $conditionsS[$j]['name'];
            }
        }

        if($success == 0) {
            for($j = 0; $j < count($conditionsW); $j++) {
                if($condition == $conditionsW[$j]['code']) {
                    $success++;

                    if($result == "") {
                        $result = "<br /><br /><b>Погодные явления</b>: ";
                    }

                    $result .= $conditionsW[$j]['name'];
                }
            }
        }
    }

    if(substr($cells[$i + 1], 2, 1) == '/' or substr($cells[$i + 1], 3, 1) == '/') {
        if($success > 0) {
            $result .= ".";
        }

        return $result."<br /><br />".decodeTemperature($cells[$i + 1])."&deg; C.<br /><br />".decodeDewPoint($cells[$i + 1])."&deg; C.";
    } else {
        if(substr($cells[$i + 1], 0, 3) == "SCT" or substr($cells[$i + 1], 0, 3) == "BKN" or substr($cells[$i + 1], 0, 3) == "OVC" or substr($cells[$i + 1], 0, 3) == "SKC" or substr($cells[$i + 1], 0, 3) == "FEW" or substr($cells[$i + 1], 0, 3) == "NSC" or substr($cells[$i + 1], 0, 3) == "CLR") {
            if($success > 0) {
                $result .= ".";
            }

            return $result."<br /><br />".decodeClouds($cells, $c, $adding + 1, "");
        } else {
            if(substr($cells[$i + 1], 0, 2) == "VV") {
                return $result.".<br /><br />".decodeVerticalVisibility($cells, $c, $adding + 1);
            } else {
                return decodePhenomena($cells, $c, $adding + 1, $result, 0);
            }
        }
    }

}

function decodeClouds($cells, $c, $adding, $initResult) {

    if($initResult == "") {
        $result = "<b>Облачность</b>:";
    } else {
        $result = $initResult.";";
    }

    $i = $c + $adding;
    $type = "";

    if(strlen($cells[$i]) > 3) {
        $altitude = (int)substr($cells[$i], 3, 3) * 100;

        if(strlen($cells[$i]) > 6) {
            $type = substr($cells[$i], 6);
        }
    }

    if($type != "") {
        switch($type) {
            case "Cb":
                $type = " облака кучево-дождевые,";
                break;
            case "CB":
                $type = " облака кучево-дождевые,";
                break;
            case "TCU":
                $type = " облака мощные кучевые,";
                break;
            default:
                break;
        }
    }

    switch(substr($cells[$i], 0, 3)) {
        case "FEW":
            $result .= " незначительная,";

            if($type != "") {
                $result .= $type;
            }

            $result .= " нижняя кромка: ".$altitude." футов";
            break;
        case "SCT":
            $result .= " рассеяная, ";

            if($type != "") {
                $result .= $type;
            }

            $result .= " нижняя кромка: ".$altitude." футов";
            break;
        case "BKN":
            $result .= " разорванная, значительная,";

            if($type != "") {
                $result .= $type;
            }

            $result .= " нижняя кромка: ".$altitude." футов";
            break;
        case "OVC":
            $result .= " сплошная,";

            if($type != "") {
                $result .= $type;
            }

            $result .= " нижняя кромка: ".$altitude." футов";
            break;
        case "SKC":
            $result .= " ясно";
            break;
        case "NSC":
            $result .= " нет существенной облачности";
            break;
        case "CLR":
            $result .= " Ясно";
            break;
        default:
            break;
    }

    if(substr($cells[$i + 1], 0, 3) == "SCT" or substr($cells[$i + 1], 0, 3) == "BKN" or substr($cells[$i + 1], 0, 3) == "OVC" or substr($cells[$i + 1], 0, 3) == "SKC" or substr($cells[$i + 1], 0, 3) == "FEW" or substr($cells[$i + 1], 0, 3) == "NSC" or substr($cells[$i + 1], 0, 3) == "CLR") {
        return decodeClouds($cells, $c, $adding + 1, $result);
    } else {
        return decodePhenomena($cells, $c, $adding, $result, 1);
    }
}

function decodeVerticalVisibility($cells, $c, $adding) {
    $visibility = (int)substr($cells[$c + $adding], 2, 3) * 100;
    $result = "<b>Вертикальная видимость</b>: ".$visibility." футов.";
    return decodePhenomena($cells, $c, $adding, $result, 1);
}

function decodeTempo($cells, $c, $adding) {
    $i = $c + $adding;
    $result = "";

    if(substr($cells[$i], 0, 2) == "FM" or substr($cells[$i], 0, 2) == "TL" or substr($cells[$i], 0, 2) == "AT") {
        switch(substr($cells[$i], 0, 2)) {
            case "FM":
                $result .= "от ".substr($cells[$i], 2, 2).":".substr($cells[$i], 4)." по UTC";
                break;
            case "TL":
                $result .= "до  ".substr($cells[$i], 2, 2).":".substr($cells[$i], 4)." по UTC";
                break;
            case "AT":
                $result .= "на ";

                if((int)substr($cells[$i], 2, 2) >= 10 and (int)substr($cells[$i], 2, 2) < 20) {
                    $result .= substr($cells[$i], 2, 2)." часов ".substr($cells[$i], 4)." минут";
                } else {
                    if(substr($cells[$i], 3, 1) == "0" or substr($cells[$i], 3, 1) == "5" or substr($cells[$i], 3, 1) == "6" or substr($cells[$i], 3, 1) == "7" or substr($cells[$i], 3, 1) == "8" or substr($cells[$i], 3, 1) == "9") {
                        $result .= (int)substr($cells[$i], 2, 2)." часов ".substr($cells[$i], 4)." минут";
                    } elseif(substr($cells[$i], 3, 1) == "1") {
                        $result .= (int)substr($cells[$i], 2, 2)." час ".substr($cells[$i], 4)." минут";
                    } else {
                        $result .= (int)substr($cells[$i], 2, 2)." часа ".substr($cells[$i], 4)." минут";
                    }
                }
                break;
            default:
                break;
        }
        $result .= " <b>=></b>";
        $i++;
    } else {
        $result .= "1<b>=></b>";
    }

    if(substr($cells[$i], 0, 3) == "VRB") {
        if(substr($cells[$i], 5, 1) == 'G') {
            $result .= " ветер у земли переменный ".(int)substr($cells[$i], 3, 2)." ";
            switch(substr($cells[$i], 8)) {
                case "MPS":
                    if ((int)substr($cells[$i], 3, 2) >= 10 and (int)substr($cells[$i], 3, 2) < 20) {
                        $result .= "метров в секунду";
                    } elseif ((int)substr($cells[$i], 4, 1) == 1) {
                        $result .= "метр в секунду";
                    } elseif ((int)substr($cells[$i], 4, 1) == 2 or (int)substr($cells[$i], 4, 1) == 3 or (int)substr($cells[$i], 4, 1) == 4) {
                        $result .= "метра в секунду";
                    } else {
                        $result .= "метров в секунду";
                    }
                    break;
                case "KT":
                    if ((int)substr($cells[$i], 3, 2) >= 10 and (int)substr($cells[$i], 3, 2) < 20) {
                        $result .= "узлов";
                    } elseif ((int)substr($cells[$i], 4, 1) == 1) {
                        $result .= "узел";
                    } elseif ((int)substr($cells[$i], 4, 1) == 2 or (int)substr($cells[$i], 4, 1) == 3 or (int)substr($cells[$i], 4, 1) == 4) {
                        $result .= "узла";
                    } else {
                        $result .= "узлов";
                    }
                    break;
                case "KMH":
                    if ((int)substr($cells[$i], 3, 2) >= 10 and (int)substr($cells[$i], 3, 2) < 20) {
                        $result .= "километров в час";
                    } elseif ((int)substr($cells[$i], 4, 1) == 1) {
                        $result .= "километр в час";
                    } elseif ((int)substr($cells[$i], 4, 1) == 2 or (int)substr($cells[$i], 4, 1) == 3 or (int)substr($cells[$i], 4, 1) == 4) {
                        $result .= "километра в час";
                    } else {
                        $result .= "километров в час";
                    }
                    break;
                default:
                    break;
            }

            $result .= " с порывами до ".substr($cells[$i], 6, 2)." ";

            switch(substr($cells[$i], 8)) {
                case "MPS":
                    if ((int)substr($cells[$i], 6, 2) >= 10 and (int)substr($cells[$i], 6, 2) < 20) {
                        $result .= "метров в секунду";
                    } elseif ((int)substr($cells[$i], 7, 1) == 1) {
                        $result .= "метра в секунду";
                    } else {
                        $result .= "метров в секунду";
                    }
                    break;
                case "KT":
                    if ((int)substr($cells[$i], 6, 2) >= 10 and (int)substr($cells[$i], 6, 2) < 20) {
                        $result .= "узлов";
                    } elseif ((int)substr($cells[$i], 7, 1) == 1) {
                        $result .= "узла";
                    } else {
                        $result .= "узлов";
                    }
                    break;
                case "KMH":
                    if ((int)substr($cells[$i], 6, 2) >= 10 and (int)substr($cells[$i], 6, 2) < 20) {
                        $result .= "километров в час";
                    } elseif ((int)substr($cells[$i], 7, 1) == 1) {
                        $result .= "километра в час";
                    }else {
                        $result .= "километров в час";
                    }
                    break;
                default:
                    break;
            }
        } else {
            $result .= " ветер у земли переменный ".(int)substr($cells[$i], 3, 2)." ";
            switch(substr($cells[$i], 5, 2)) {
                case "MP":
                    if((int)substr($cells[$i], 3, 2) >= 10 and (int)substr($cells[$i], 3, 2) < 20) {
                        $result .= "метров в секунду";
                    } elseif((int)substr($cells[$i], 4, 1) == 1) {
                        $result .= "метр в секунду";
                    } elseif((int)substr($cells[$i], 4, 1) == 2 or (int)substr($cells[$i], 4, 1) == 3 or (int)substr($cells[$i], 4, 1) == 4) {
                        $result .= "метра в секунду";
                    } else {
                        $result .= "метров в секунду";
                    }
                    break;
                case "KT":
                    if((int)substr($cells[$i], 3, 2) >= 10 and (int)substr($cells[$i], 3, 2) < 20) {
                        $result .= "узлов";
                    } elseif((int)substr($cells[$i], 4, 1) == 1) {
                        $result .= "узел";
                    } elseif((int)substr($cells[$i], 4, 1) == 2 or (int)substr($cells[$i], 4, 1) == 3 or (int)substr($cells[$i], 4, 1) == 4) {
                        $result .= "узла";
                    } else {
                        $result .= "узлов";
                    }
                    break;
                case "KM":
                    if((int)substr($cells[$i], 3, 2) >= 10 and (int)substr($cells[$i], 3, 2) < 20) {
                        $result .= "километров в час";
                    } elseif((int)substr($cells[$i], 4, 1) == 1) {
                        $result .= "километр в час";
                    } elseif((int)substr($cells[$i], 4, 1) == 2 or (int)substr($cells[$i], 4, 1) == 3 or (int)substr($cells[$i], 4, 1) == 4) {
                        $result .= "километра в час";
                    } else {
                        $result .= "километров в час";
                    }
                    break;
                default:
                    break;
            }
        }
        $result .= ";";
        $i++;
    }

    if(is_numeric(substr($cells[$i], 0, 3))) {
        $result .= " ветер у земли ";

        if(substr($cells[$i], 3, 1) == 'V') {
            $result .= "меняет направление от ".(int)substr($cells[$i], 0, 3)."&deg; до ".(int)substr($cells[$i], 4, 3)."&deg;, скорость = ".(int)substr($cells[$i], 7, 2)." ";

            switch(substr($cells[$i], strlen($cells[$i]) - 2)) {
                case "PS":
                    if((int)substr($cells[$i], 7, 2) >= 10 and (int)substr($cells[$i], 7, 2) < 20) {
                        $result .= "метров в секунду";
                    } elseif((int)substr($cells[$i], 8, 1) == 1) {
                        $result .= "метр в секунду";
                    } elseif((int)substr($cells[$i], 8, 1) == 2 or (int)substr($cells[$i], 8, 1) == 3 or (int)substr($cells[$i], 8, 1) == 4){
                        $result .= "метра в секунду";
                    } else {
                        $result .= "метров в секунду";
                    }
                    break;
                case "KT":
                    if((int)substr($cells[$i], 7, 2) >= 10 and (int)substr($cells[$i], 7, 2) < 20) {
                        $result .= "узлов";
                    } elseif((int)substr($cells[$i], 8, 1) == 1) {
                        $result .= "узел";
                    } elseif((int)substr($cells[$i], 8, 1) == 2 or (int)substr($cells[$i], 8, 1) == 3 or (int)substr($cells[$i], 8, 1) == 4){
                        $result .= "узла";
                    } else {
                        $result .= "узлов";
                    }
                    break;
                case "MH":
                    if((int)substr($cells[$i], 7, 2) >= 10 and (int)substr($cells[$i], 7, 2) < 20) {
                        $result .= "километров в час";
                    } elseif((int)substr($cells[$i], 8, 1) == 1) {
                        $result .= "километр в час";
                    } elseif((int)substr($cells[$i], 8, 1) == 2 or (int)substr($cells[$i], 8, 1) == 3 or (int)substr($cells[$i], 8, 1) == 4){
                        $result .= "километра в час";
                    } else {
                        $result .= "километров в час";
                    }
                    break;
                default:
                    break;
            }

            if(substr($cells[$i], 9, 1) == 'G') {
                $result .= ", с порывами до ".(int)substr($cells[$i], 10, 2)." ";

                switch(substr($cells[$i], strlen($cells[$i]) - 2)) {
                    case "PS":
                        if((int)substr($cells[$i], 10, 2) >= 10 and (int)substr($cells[$i], 10, 2) < 20) {
                            $result .= "метров в секунду";
                        } elseif((int)substr($cells[$i], 11, 1) == 1) {
                            $result .= "метра в секунду";
                        } else {
                            $result .= "метров в секунду";
                        }
                        break;
                    case "KT":
                        if((int)substr($cells[$i], 10, 2) >= 10 and (int)substr($cells[$i], 10, 2) < 20) {
                            $result .= "узлов";
                        } elseif((int)substr($cells[$i], 11, 11) == 1) {
                            $result .= "узела";
                        } else {
                            $result .= "узлов";
                        }
                        break;
                    case "MH":
                        if((int)substr($cells[$i], 10, 2) >= 10 and (int)substr($cells[$i], 10, 2) < 20) {
                            $result .= "километров в час";
                        } elseif((int)substr($cells[$i], 11, 2) == 1) {
                            $result .= "километра в час";
                        } else {
                            $result .= "километров в час";
                        }
                        break;
                    default:
                        break;
                }
            }

        } else {
            $result .= "имеет направление ".(int)substr($cells[$i], 0, 3)."&deg;, скорость = ".(int)substr($cells[$i], 3, 2)." ";

            switch(substr($cells[$i], strlen($cells[$i]) - 2)) {
                case "PS":
                    if((int)substr($cells[$i], 3, 2) >= 10 and (int)substr($cells[$i], 3, 2) < 20) {
                        $result .= "метров в секунду";
                    } elseif((int)substr($cells[$i], 4, 1) == 1) {
                        $result .= "метр в секунду";
                    } elseif((int)substr($cells[$i], 4, 1) == 2 or (int)substr($cells[$i], 4, 1) == 3 or (int)substr($cells[$i], 4, 1) == 4) {
                        $result .= "метра в секунду";
                    } else {
                        $result .= "метров в секунду";
                    }
                    break;
                case "KT":
                    if((int)substr($cells[$i], 3, 2) >= 10 and (int)substr($cells[$i], 3, 2) < 20) {
                        $result .= "узлов";
                    } elseif((int)substr($cells[$i], 4, 1) == 1) {
                        $result .= "узел";
                    } elseif((int)substr($cells[$i], 4, 1) == 2 or (int)substr($cells[$i], 4, 1) == 3 or (int)substr($cells[$i], 4, 1) == 4) {
                        $result .= "узла";
                    } else {
                        $result .= "узлов";
                    }
                    break;
                case "MH":
                    if((int)substr($cells[$i], 3, 2) >= 10 and (int)substr($cells[$i], 3, 2) < 20) {
                        $result .= "километров в час";
                    } elseif((int)substr($cells[$i], 4, 1) == 1) {
                        $result .= "километр в час";
                    } elseif((int)substr($cells[$i], 4, 1) == 2 or (int)substr($cells[$i], 4, 1) == 3 or (int)substr($cells[$i], 4, 1) == 4) {
                        $result .= "километра в час";
                    } else {
                        $result .= "километров в час";
                    }
                    break;
                default:
                    break;
            }

            if(substr($cells[$i], 5, 1) == 'G') {
                $result .= " с порывами до ".(int)substr($cells[$i], 6, 2);
            }

            switch(substr($cells[$i], strlen($cells[$i]) - 2)) {
                case "PS":
                    if((int)substr($cells[$i], 6, 2) >= 10 and (int)substr($cells[$i], 6, 2) < 20) {
                        $result .= "метров в секунду";
                    } elseif((int)substr($cells[$i], 7, 1) == 1) {
                        $result .= "метра в секунду";
                    } else {
                        $result .= "метров в секунду";
                    }
                    break;
                case "KT":
                    if((int)substr($cells[$i], 6, 2) >= 10 and (int)substr($cells[$i], 6, 2) < 20) {
                        $result .= "узлов";
                    } elseif((int)substr($cells[$i], 7, 1) == 1) {
                        $result .= "узла";
                    } else {
                        $result .= "узлов";
                    }
                    break;
                case "MH":
                    if((int)substr($cells[$i], 6, 2) >= 10 and (int)substr($cells[$i], 6, 2) < 20) {
                        $result .= "километров в час";
                    } elseif((int)substr($cells[$i], 7, 1) == 1) {
                        $result .= "километра в час";
                    } else {
                        $result .= "километров в час";
                    }
                    break;
                default:
                    break;
            }
        }

        $result .= ";";
        $i++;
    }

    return $result;
}

//информация об аэропорте
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
    $m_word = "минут";
}

$time  = $time.$word." ".$minutes." ".$m_word." по UTC.";
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

//анализатор горизонтальной видимости
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

if(substr($visibility, strlen($visibility) - 1) != '.') {
    $visibility .= '.';
}
//////////////////////////////////////////////////////

$restInfo = "";

//сервисные службы для погодных явлений, облачности и вертикальной видимости
if(substr($data[$counter], 2, 1) == '/' or substr($data[$counter], 3, 1) == '/') {
    $restInfo .= decodeTemperature($data[$counter])."&deg;C"."<br /><br />".decodeDewPoint($data[$counter])."&deg;C";
    $counter++;
} else {
    $restInfo .= decodePhenomena($data, $counter, 0, "", 0);
}

function increaseCounter($cells, $c) {
    $c++;

    if(substr($cells[$c], 0, 1) != "Q" and substr($cells[$c], 0, 1) != "A") {
        return increaseCounter($cells, $c);
    } else {
        return $c;
    }
}
//////////////////////////////////////////////////////

//анализатор давления
switch(substr($data[(int)increaseCounter($data, $counter)], 0, 1)) {
    case "A":
        $pressure = substr($data[(int)increaseCounter($data, $counter)], 1, 2).".".substr($data[(int)increaseCounter($data, $counter)], 3, 2)." дюймов ртутного столба.";
        break;
    case "Q":
        $lastDigit = substr($data[(int)increaseCounter($data, $counter)], strlen($data[(int)increaseCounter($data, $counter)]) - 1);
        $twoDigits = substr($data[(int)increaseCounter($data, $counter)], strlen($data[(int)increaseCounter($data, $counter)]) - 2);

        if(substr($twoDigits, 0, 1) == 1) {
            $pressure = (int)substr($data[(int)increaseCounter($data, $counter)], 1, 4)." гектапаскалей.";
        } else {
            if($lastDigit == "0" or $lastDigit == "6" or $lastDigit == "7" or $lastDigit == "8" or $lastDigit == "9") {
                $pressure = (int)substr($data[(int)increaseCounter($data, $counter)], 1, 4)." гектапаскалей.";
            } elseif($lastDigit == "1") {
                $pressure = (int)substr($data[(int)increaseCounter($data, $counter)], 1, 4)." гектапаскаль.";
            } elseif($lastDigit == "2" or $lastDigit == "3" or $lastDigit == "4") {
                $pressure = (int)substr($data[(int)increaseCounter($data, $counter)], 1, 4)." гектапаскаля.";
            }
        }
        break;
    default:
        break;
}

$counter = (int)increaseCounter($data, $counter) + 1;
//////////////////////////////////////////////////////

$additionals = "";

//анализатор сдвига ветра
if($data[$counter] == "WS") {
    if($data[$counter + 1] == "ALL" and $data[$counter + 2] == "RWY") {
        $additionals = "на всех ВПП наблюдается сдвиг ветра.";
        $counter += 3;
    } elseif(substr($data[$counter + 1], 0, 1) == "R") {
        if(substr($data[$counter + 1], 0, 3) == "RWY") {
            $runway = substr($data[$counter + 1], 3);
        } elseif(substr($data[$counter + 1], 0, 2) == "RW") {
            $runway = substr($data[$counter + 1], 2);
        } else {
            $runway = substr($data[$counter + 1], 1);
        }

        $additionals = "на ВПП ".$runway." наблюдается сдвиг ветра.";
        $counter += 2;
    } else {
        $additionals = "наблюдается сдвиг ветра.";
        $counter++;
    }
}
//////////////////////////////////////////////////////

//анализатор прогноза
if($data[$counter] == "NOSIG" or $data[$counter] == "BECMG" or $data[$counter] == "TEMPO") {
    $tempo = "";
    switch($data[$counter]) {
        case "NOSIG":
            $tempo = "cущественных изменений не прогнозируется";
            break;
        case "BECMG":
            $tempo = "наступающие изменения ";
            $tempo .= decodeTempo($data, $counter, 1);
            break;
        case "TEMPO":
            $tempo = "временные изменения ";
            $tempo .= decodeTempo($data, $counter, 1);
            break;
        default:
            break;
    }
}
//////////////////////////////////////////////////////

echo "<b>Общие сведения</b>: погода в аэропорту <img src='img/flags/".$airport['iso_code'].".png' title='".$airport['country']."' /> <a href='http://va-aeroflot.su/airport/".$data[0]."' style='margin-left: 0;'>".$airport['name']." (".$data[0].")"."</a> по состоянию на ".$time."<br /><br /><b>Ветер у земли</b>: ".$windTotal.".<br /><br /><b>Горизонтальная видимость</b>: ".$visibility.$restInfo."<br /><br /><b>Давление</b>: ".$pressure;

if($additionals != "") {
    echo "<br /><br /><b>Дополнительная информация</b>: ".$additionals;
}

if($tempo != "") {
    echo "<br /><br /><b>Прогноз</b>: ".$tempo;
}