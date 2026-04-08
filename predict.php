<?php

require "xgboost.php";

if(!file_exists("model_sentimen.model")){
    die("Model belum dibuat. Jalankan analyze.php dulu.");
}

$modelData = unserialize(file_get_contents("model_sentimen.model"));

$model = $modelData["model"];
$vocab = $modelData["vocab"];
$idf   = $modelData["idf"];

$text = $_POST["text"] ?? "";

if(trim($text) == ""){
    die("Teks kosong");
}

$text_asli = $text;

/* =========================
PREPROCESSING (untuk input manual)
========================= */

$text = strtolower($text);

$text = preg_replace('/[^a-zA-Z\s]/',' ',$text);

$text = preg_replace('/\s+/',' ',$text);

$text = trim($text);

$words = explode(" ",$text);


/* =========================
TF VECTOR
========================= */

$vector = array_fill(0,count($vocab),0);

foreach($words as $w){

    if($w == "") continue;

    $index = array_search($w,$vocab);

    if($index !== false){
        $vector[$index]++;
    }

}


/* =========================
TF-IDF
========================= */

$total_words = count($words);

if($total_words > 0){

    for($i=0;$i<count($vector);$i++){

        $tf = $vector[$i] / $total_words;

        $vector[$i] = $tf * $idf[$i];

    }

}


/* =========================
PREDIKSI
========================= */

$pred = $model->predict($vector);

$label_map = [
0=>"Negatif",
1=>"Netral",
2=>"Positif"
];

$sentimen = $label_map[$pred] ?? "Tidak diketahui";

?>

<h2>Hasil Analisis Sentimen</h2>

<p><b>Teks:</b></p>
<p><?php echo htmlspecialchars($text_asli); ?></p>

<p><b>Sentimen:</b> <?php echo $sentimen; ?></p>

<br>

<a href="index.php">Kembali</a>