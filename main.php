<?php
ini_set('memory_limit', '2048M');

require 'tfidf.php';
require 'split.php';
require 'xgboost.php';
require 'pso.php';
require 'evaluation.php';

$documents = [];
$labels = [];

// ==========================
// BACA FILE HASIL PREPROCESSING
// ==========================

$file = fopen("processed_balanced.csv", "r");

if(!$file){
    die("processed_balanced.csv tidak ditemukan! Jalankan preprocessing dulu.");
}

$header = fgetcsv($file);

while(($row = fgetcsv($file)) !== false){

    $documents[] = $row[0];   
    $labels[]    = (int)$row[2];
}

fclose($file);

echo "Total data loaded: " . count($documents) . PHP_EOL;
print_r(array_count_values($labels));


// ==========================
// SPLIT TEXT DULU
// ==========================

list($trainData, $testData) = DataSplit::split($documents, $labels);

$trainDocs = array_column($trainData, 0);
$ytrain    = array_column($trainData, 1);

$testDocs = array_column($testData, 0);
$ytest    = array_column($testData, 1);

echo "Train size: " . count($trainDocs) . PHP_EOL;


// ==========================
// TF-IDF TANPA LEAKAGE
// ==========================

list($Xtrain, $vocab, $idf) = TFIDF::computeTFIDFTrain($trainDocs);

$Xtest = TFIDF::computeTFIDFTest($testDocs, $vocab, $idf);

echo "TF-IDF selesai" . PHP_EOL;


// ==========================
// PSO HYPERPARAMETER SEARCH
// ==========================

echo "Menjalankan PSO..." . PHP_EOL;

$params = PSO::optimize($Xtrain, $ytrain);

$bestLR = $params[0];
$bestEstimator = $params[1];

echo "Best Learning Rate : $bestLR" . PHP_EOL;
echo "Best Estimators    : $bestEstimator" . PHP_EOL;


// ==========================
// TRAIN MODEL FINAL
// ==========================

$model = new SimpleXGBoost($bestLR, $bestEstimator);
$model->train($Xtrain, $ytrain);


// ==========================
// TESTING
// ==========================

$predictions = [];

foreach ($Xtest as $features) {
    $predictions[] = $model->predict($features);
}


// ==========================
// EVALUATION MULTI-CLASS
// ==========================

$total = count($ytest);
$correct = 0;

for ($i = 0; $i < $total; $i++) {
    if ($ytest[$i] == $predictions[$i]) {
        $correct++;
    }
}

$accuracy = $correct / $total;

echo PHP_EOL;
echo "===== HASIL EVALUASI =====" . PHP_EOL;
echo "Accuracy : $accuracy" . PHP_EOL;


// Precision Recall F1 per kelas
$classes = [0,1,2];

foreach ($classes as $class) {

    $tp = 0;
    $fp = 0;
    $fn = 0;

    for ($i = 0; $i < $total; $i++) {

        if ($predictions[$i] == $class && $ytest[$i] == $class) $tp++;
        if ($predictions[$i] == $class && $ytest[$i] != $class) $fp++;
        if ($predictions[$i] != $class && $ytest[$i] == $class) $fn++;
    }

    $precision = $tp / ($tp + $fp + 1e-9);
    $recall = $tp / ($tp + $fn + 1e-9);
    $f1 = 2 * ($precision * $recall) / ($precision + $recall + 1e-9);

    echo PHP_EOL;
    echo "Class $class" . PHP_EOL;
    echo "TP: $tp FP: $fp FN: $fn" . PHP_EOL;
    echo "Precision: $precision" . PHP_EOL;
    echo "Recall   : $recall" . PHP_EOL;
    echo "F1 Score : $f1" . PHP_EOL;
}

echo PHP_EOL;
echo "Distribusi Prediksi:" . PHP_EOL;
print_r(array_count_values($predictions));
