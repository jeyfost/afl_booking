<?php

function getDay($day, $month, $year)
{
    $days = array(7, 1, 2, 3, 4, 5, 6);
    $day = (int)$day;
    $month = (int)$month;
    $a = (int)((14 - $month) / 12);
    $y = $year - $a;
    $m = $month + 12 * $a - 2;
    $d = (7000 + (int)($day + $y + (int)($y / 4) - (int)($y / 100) + (int)($y / 400) + (31 * $m) / 12)) % 7;
    return $days[$d];
}