<?php

namespace App\Helpers;

use Doctrine_Query;

// $date is (YYYY-mm-dd)
function add_working_days($date, $days)
{
    $timestamp = strtotime($date);
    $skipdays = array("Saturday", "Sunday");
    $skipdates = array();
    $feriados = Doctrine::getTable('Feriado')->findAll();
    foreach ($feriados as $f)
        $skipdates[] = $f->fecha;

    $i = 1;
    while ($days >= $i) {
        $timestamp = strtotime("+1 day", $timestamp);
        if (in_array(date("l", $timestamp), $skipdays)) {
            $days++;
        } else if (in_array(date("Y-m-d", $timestamp), $skipdates)) {
            $days++;
        }
        $i++;
    }

    return date("Y-m-d", $timestamp);
}


function get_working_days_count($d1, $d2)
{

    if ($d1 > $d2) {
        $tmp = $d1;
        $d1 = $d2;
        $d2 = $tmp;
    }

    $feriados = Doctrine_Query::create()
        ->select('COUNT(id) AS feriados_qt')
        ->from('Feriado f')
        ->where('f.fecha BETWEEN ? and ?', array($d2, $d1))
        ->execute();

    $feriados_count = $feriados[0]->feriados_qt;

    $weekend = array("Saturday", "Sunday");
    $working_days = 0;

    $d1 = strtotime($d1);
    $d2 = strtotime($d2);

    for ($d = $d1; date('Y-m-d', $d) <= date('Y-m-d', $d2); $d = strtotime("+1 day", $d)) {

        if (in_array(date('l', $d), $weekend))
            $working_days++;

    }

    return $working_days + $feriados_count;

}