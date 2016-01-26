<?php

try {
    $dbh = new PDO('mysql:host=127.0.0.1;dbname=APCUPS', 'APCUPSStats', 'bm6+h7%w_gn!4');
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}

$graphs = [
    'load' => [
        'datasets' => ['LOADPCT']
    ],
    'internalTemperature' => [
        'datasets' => ['ITEMP']
    ],
    'lineVoltages' => [
        'datasets' => ['LINEV', 'OUTPUTV'],
        'min' => 'LOTRANS',
        'max' => 'HITRANS'
    ],
    'lineFrequency' => [
        'datasets' => ['LINEFREQ']
    ],
    'batteryTime' => [
        'datasets' => ['TIMELEFT'],
        'min' => 'MINTIMEL'
    ],
    'batteryCharge' => [
        'datasets' => ['BCHARGE'],
        'min' => 'MBATTCHG'
    ],
];

$data = [];
$labels = [];

foreach($dbh->query('SELECT DATE,LOADPCT from stats ORDER BY DATE DESC LIMIT 0,25') as $row) {
    $data[] = $row['LOADPCT'];
    $labels[] = date('Hi', $row['DATE']);
}

// Reverse the order of the data
$data = array_reverse($data);
$labels = array_reverse($labels);

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

