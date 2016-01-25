<?php

try {
    $dbh = new PDO('mysql:host=127.0.0.1;dbname=APCUPS', 'APCUPSStats', 'bm6+h7%w_gn!4');
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}

$data = [];
$labels = [];

foreach($dbh->query('SELECT DATE,LOADPCT from stats') as $row) {
    $data[] = $row['LOADPCT'];
    $labels[] = date('Hi', $row['DATE']);
}


$chartData = [
    'labels' => $labels,
    'datasets' => [
        [
            'fillColor' => "rgba(220,220,220,0.2)",
            'strokeColor' => "rgba(220,220,220,1)",
            'pointColor' => "rgba(220,220,220,1)",
            'pointStrokeColor' => "#fff",
            'pointHighlightFill' => "#fff",
            'pointHighlightStroke' => "rgba(220,220,220,1)",
            'data' => $data
            ]
    ]
];

echo json_encode($chartData);

