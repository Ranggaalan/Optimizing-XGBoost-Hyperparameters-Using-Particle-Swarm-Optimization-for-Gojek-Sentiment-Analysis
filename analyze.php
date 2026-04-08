<?php

set_time_limit(0);

require 'tfidf.php';
require 'split.php';
require 'xgboost.php';
require 'pso.php';

$path = "processed_balanced.csv";

if(!file_exists($path)){
    die("Dataset tidak ditemukan. Jalankan preprocessing dulu.");
}

$file = fopen($path,"r");

$header = fgetcsv($file);

$documents = [];
$labels = [];

while(($row = fgetcsv($file)) !== false){

    $documents[] = $row[0];
    $labels[] = (int)$row[2];

}

fclose($file);


/* =====================
   INFORMASI DATASET
===================== */

$total = count($documents);


/* =====================
   SPLIT DATA
===================== */

list($trainData,$testData) = DataSplit::split($documents,$labels);

$trainDocs = array_column($trainData,0);
$ytrain = array_column($trainData,1);

$testDocs = array_column($testData,0);
$ytest = array_column($testData,1);


/* =====================
   TF-IDF
===================== */

echo "<h3>Proses TF-IDF...</h3>";

list($Xtrain,$vocab,$idf) = TFIDF::computeTFIDFTrain($trainDocs);

$Xtest = TFIDF::computeTFIDFTest($testDocs,$vocab,$idf);


/* =====================
   PSO OPTIMIZATION
===================== */

echo "<h3>Optimasi Parameter dengan PSO...</h3>";

$params = PSO::optimize($Xtrain,$ytrain);

$bestLR = $params[0];
$bestEstimator = $params[1];


/* =====================
   TRAIN MODEL
===================== */

echo "<h3>Training Model XGBoost...</h3>";

$model = new SimpleXGBoost($bestLR,$bestEstimator);

$model->train($Xtrain,$ytrain);


/* =====================
   TESTING
===================== */

$correct = 0;
$predictions = [];

for($i=0;$i<count($Xtest);$i++){

    $pred = $model->predict($Xtest[$i]);

    $predictions[] = $pred;

    if($pred == $ytest[$i]){
        $correct++;
    }

}

$accuracy = $correct / count($Xtest) * 100;


/* =====================
   SIMPAN MODEL
===================== */

$modelData = [
    "model"=>$model,
    "vocab"=>$vocab,
    "idf"=>$idf
];

file_put_contents("model_sentimen.model", serialize($modelData));


/* =====================
   DISTRIBUSI PREDIKSI
===================== */

$predCount = array_count_values($predictions);

?>

<!DOCTYPE html>
<html>

<head>

<title>Hasil Analisis Sentimen</title>

<style>

body{
font-family:Arial;
margin:40px;
background:#f4f6f9;
}

.card{
background:white;
padding:20px;
margin-bottom:20px;
border-radius:10px;
box-shadow:0 2px 5px rgba(0,0,0,0.1);
}

button{
padding:12px 20px;
font-size:16px;
cursor:pointer;
}

</style>

</head>

<body>

<h1>Hasil Analisis Sentimen</h1>

<div class="card">

<h3>Informasi Dataset</h3>

<p>Total Dataset: <?php echo $total; ?></p>

<p>Data Training: <?php echo count($trainDocs); ?></p>

<p>Data Testing: <?php echo count($testDocs); ?></p>

</div>


<div class="card">

<h3>Parameter Model</h3>

<p>Learning Rate Terbaik: <?php echo $bestLR; ?></p>

<p>Jumlah Estimator Terbaik: <?php echo $bestEstimator; ?></p>

</div>


<div class="card">

<h3>Hasil Evaluasi</h3>

<p><b>Akurasi Model:</b> <?php echo round($accuracy,2); ?>%</p>

</div>


<div class="card">

<h3>Distribusi Prediksi</h3>

<pre>

<?php print_r($predCount); ?>

</pre>

</div>


<div class="card">

<h3>Visualisasi Dataset</h3>

<a href="visualisasi.php">

<button>Lihat Grafik dan Wordcloud</button>

</a>

</div>
<div class="card">

<h3>Dashboard Analisis Lengkap</h3>

<p>
Lihat dashboard lengkap yang menampilkan distribusi sentimen,
wordcloud, top kata, serta review positif dan negatif pengguna.
</p>

<a href="dashboard.php">

<button>Lihat Dashboard Analisis</button>

</a>

</div>

<br>

<a href="index.php">Kembali ke Dashboard</a>

</body>

</html>