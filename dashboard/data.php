<?php

//error_reporting(E_ALL);
//ini_set('display_errors', true);

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
        'limits' => [
            'min' => 'LOTRANS',
            'max' => 'HITRANS'
        ],
    ],
    'lineFrequency' => [
        'datasets' => ['LINEFREQ']
    ],
    'batteryTime' => [
        'datasets' => ['TIMELEFT'],
        'limits' => [
            'min' => 'MINTIMEL',
        ],
    ],
    'batteryCharge' => [
        'datasets' => ['BCHARGE'],
        'limits' => [
            'min' => 'MBATTCHG'
        ],
    ],
];

// Check the graph exists
if (array_key_exists($_GET['graphName'], $graphs) !== true) {
    throw new Exception('Unknown Graph "'.$_GET['graphName'].'"');
}

$datasets = [];

$graphToDraw = $graphs[$_GET['graphName']];

// Add the date column as we will always need it
array_push($graphToDraw['datasets'], 'DATE');

// Get all the columns we need to select
$columnArray = call_user_func_array('array_merge', $graphToDraw);

foreach($dbh->query('SELECT '.implode(',', $columnArray).' FROM stats ORDER BY DATE DESC LIMIT 0,25') as $row) {

    foreach ($columnArray as $datasetName) {
        $dataPoint = $row[$datasetName];

        // Format the DATE data set to dates
        if ($datasetName === 'DATE') {
            $dataPoint = date('Hi', $dataPoint);
        }

        // Ensure the array exists so we can use array_unshift
        if (is_array($datasets[$datasetName]) !== true) {
            $datasets[$datasetName] = [];
        }

        array_unshift($datasets[$datasetName], $dataPoint);
    }
}

// Add the DATE data to the graph
$chartData['labels'] = $datasets['DATE'];

foreach ($datasets as $datasetName => $dataset) {

    // Dont add the DATE data to the datasets
    if ($datasetName === 'DATE') {
        continue;
    }

    // Determine the colour of the data set
    $datasetColour = ($datasetName === $graphToDraw['limits']['max'] || $datasetName === $graphToDraw['limits']['min']) ? 'rgba(255,187,205,1)':'rgba(220,220,220,1)';

    // Add the data set to the graph
    $chartData['datasets'][] =  [
            'fillColor' => "rgba(220,220,220,0.2)",
            'strokeColor' => $datasetColour,
            'pointColor' => $datasetColour,
            'pointStrokeColor' => "#fff",
            'pointHighlightFill' => "#fff",
            'pointHighlightStroke' => $datasetColour,
            'data' => $dataset
        ];
}

echo json_encode($chartData);
