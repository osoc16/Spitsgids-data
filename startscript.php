<?php

include_once 'vendor/autoload.php';
use MongoDB\Collection as Collection;

$dotenv = new Dotenv\Dotenv(dirname(__DIR__));
$dotenv->load();
$mongodb_url = getenv('MONGODB_URL');
$mongodb_db = getenv('MONGODB_DB');

$m = new MongoDB\Driver\Manager($mongodb_url);
$structural = new Collection($m, $mongodb_db, 'structural');
$occupancy = new MongoDB\Collection($m, $mongodb_db, 'occupancy');

date_default_timezone_set('Europe/Brussels');
$dayOfTheWeek = date('N') + 2;

$weekendCheck = 6;

for($i=0; $i<2; $i++) {
    $isWeekday = 1;

    if ($dayOfTheWeek == $weekendCheck-$i || $dayOfTheWeek == $weekendCheck-$i+1) {
        $isWeekday = 0;
    }

    $structuralData = $structural->find(array('weekday' => $isWeekday));

    foreach ($structuralData as $structuralElement) {
        $date = "";
        $extra = $i;
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
        $connectionid = 'http://irail.be/connections/'.substr(basename($structuralElement->from), 2)."/".$date."/".$structuralElement->vehicle;

        $structuralToOccupancy = array(
            'connection' => $connectionid,
            'vehicle' => $structuralElement->vehicle,
            'from' => $structuralElement->from,
            'date' => $date,
            'structural' => $structuralElement->occupancy,
            'occupancy' => $structuralElement->occupancy
        );

        $occupancy->insertOne($structuralToOccupancy);
    }
}

?>