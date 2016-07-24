<?php

include_once 'vendor/autoload.php';
use MongoDB\Collection as Collection;

$m = new MongoDB\Driver\Manager("mongodb://localhost:27017");
$structural = new Collection($m, 'spitsgids', 'structural');
$occupancy = new MongoDB\Collection($m, 'spitsgids', 'occupancy');

date_default_timezone_set('Europe/Brussels');
$dayOfTheWeek = date('N');
$isWeekday = 1;

if($dayOfTheWeek == 5 || $dayOfTheWeek == 6) {
    $isWeekday = 0;
}

$structuralData = $structural->find(array('weekday' => $isWeekday));

foreach ($structuralData as $structuralElement) {
    $extra = 1;
    $time = $structuralElement->time;

    if ($time >= 1000) {
        $time = $time;
    } elseif ($time >= 100) {
        if($time < 400) {
            $extra = $extra + 1;
        }

        $time = "0" . $time;
    } elseif ($time >= 10) {
        $extra = $extra + 1;
        $time = "00" . $time;
    } else {
        $extra = $extra + 1;
        $time = "000" . $time;
    }

    $date = date('Ymd\T', strtotime(date() . ' + ' . $extra . ' days')) . $time;
    $id = substr(basename($structuralElement->from), 2) . "-" . $date . "-" . $structuralElement->vehicle;

    $structuralToOccupancy = array(
        'id' => $id,
        'vehicle' => $structuralElement->vehicle,
        'from' => $structuralElement->from,
        'date' => $date,
        'structural' => $structuralElement->occupancy,
        'occupancy' => $structuralElement->occupancy
    );

    $occupancy->insertOne($structuralToOccupancy);
}

?>