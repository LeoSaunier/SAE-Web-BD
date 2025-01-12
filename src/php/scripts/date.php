<?php
function getYear()
{
    return date("Y");
}

function getNextYear()
{
    return date("Y") + 1;
}

function getMonth()
{
    return date("m");
}


function getRemainingMonths($year)
    {
    $currentMonth = date("m");
    $months = [];

    for ($month = $currentMonth; $month <= 12; $month++) {
        $months[] = date("F", mktime(0, 0, 0, $month, 10));
    }

    return $months;
}


function getDay()
{
    return date("d");
}

function getNext30Days()
{
    $currentMonth = date("m");
    $currentYear = date("Y");
    $nextMonth = date("m", strtotime("+1 month"));
    $nextYear = date("Y", strtotime("+1 month"));

    $days = [];
    for ($i = 0; $i < 30; $i++) {
        $day = date("Y-m-d", strtotime("+$i days"));
        $month = date("m", strtotime($day));
        $year = date("Y", strtotime($day));
        if ($month == $currentMonth && $year == $currentYear) {
            $days['currentMonth'][] = $day;
        } elseif ($month == $nextMonth && $year == $nextYear) {
            $days['nextMonth'][] = $day;
        }
    }

    return $days;
}


?>