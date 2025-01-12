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

function getMonthFromYear($year)
{
    return date("m", strtotime($year));
}

function getDay()
{
    return date("d");
}

function getNext30Days()
{
    $days = [];
    for ($i = 0; $i < 30; $i++) {
        $days[] = date("Y-m-d", strtotime("+$i days"));
    }
    return $days;
}


?>