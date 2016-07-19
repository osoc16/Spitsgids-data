<?php

include_once 'vendor/autoload.php';
use MongoDB\Collection as Collection;

$m = new MongoDB\Driver\Manager("mongodb://localhost:27017");
$structural = new Collection($m, 'spitsgids', 'structural');
$occupancy = new MongoDB\Collection($m, 'spitsgids', 'occupancy');

date_default_timezone_set('Europe/Brussels');
$dayOfTheWeek = date('N');

$weekendCheck = 6;

for($i=0; $i<2; $i++) {
    $isWeekday = 1;

    if($dayOfTheWeek == $weekendCheck-$i || $dayOfTheWeek == $weekendCheck-$i+1) {
        $isWeekday = 0;
    }

    $structuralData = $structural->find(array('weekday' => $isWeekday));

    foreach ($structuralData as $structuralElement) {
        $date = date('Ymd', strtotime(date(). ' + ' . $i . ' days'));
        $id = $structuralElement->vehicle . "-" . $date . "-" . basename($structuralElement->from);

        $structuralToOccupancy = array(
            'id' => $id,
            'vehicle' => $structuralElement->vehicle,
            'from' => $structuralElement->from,
            'to' => $structuralElement->to,
            'date' => $date,
            'structural' => $structuralElement->occupancy,
            'occupancy' => $structuralElement->occupancy
        );

        $occupancy->insertOne($structuralToOccupancy);
    }
}

?>