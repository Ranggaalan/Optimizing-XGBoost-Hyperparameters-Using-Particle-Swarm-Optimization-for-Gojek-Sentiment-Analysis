<?php

require 'vendor/autoload.php';

use Sastrawi\Stemmer\StemmerFactory;
use Sastrawi\StopWordRemover\StopWordRemoverFactory;

$stemmerFactory = new StemmerFactory();
$stemmer = $stemmerFactory->createStemmer();

$stopwordFactory = new StopWordRemoverFactory();
$stopword = $stopwordFactory->createStopWordRemover();

function clean_text($text){
    $text = strtolower($text);
    $text = preg_replace('/[^a-zA-Z\s]/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
}

$file = fopen("Data_Gojek.csv", "r");
$header = fgetcsv($file);

$documents = [];

while (($row = fgetcsv($file)) !== false){

    if(!isset($row[3])) continue;
    $review = $row[3];

    if(empty($review)) continue;

    $clean = clean_text($review);
    $noStop = $stopword->remove($clean);
    $stem = $stemmer->stem($noStop);

    if(empty($stem)) continue;

    $documents[] = explode(" ", $stem);
}

fclose($file);

echo "Total dokumen: ".count($documents)."\n";


// ===============================
// BUILD VOCAB
// ===============================
$vocab = [];
foreach($documents as $doc){
    foreach($doc as $w){
        $vocab[$w] = true;
    }
}
$vocab = array_keys($vocab);

echo "Jumlah vocab: ".count($vocab)."\n";


// ===============================
// WORD2VEC TRAINING (SIMPLIFIED)
// ===============================
$embedding_size = 50;
$window = 2;
$learning_rate = 0.01;
$epochs = 3;

$word_vectors = [];

foreach($vocab as $word){
    for($i=0;$i<$embedding_size;$i++){
        $word_vectors[$word][$i] = mt_rand(-100,100)/100;
    }
}

echo "Training Word2Vec...\n";

for($e=0;$e<$epochs;$e++){

    foreach($documents as $doc){

        $length = count($doc);

        for($i=0;$i<$length;$i++){

            $target = $doc[$i];

            for($j=max(0,$i-$window); $j<=min($length-1,$i+$window); $j++){

                if($j == $i) continue;

                $context = $doc[$j];

                for($k=0;$k<$embedding_size;$k++){

                    $diff = $word_vectors[$target][$k] - $word_vectors[$context][$k];

                    $word_vectors[$target][$k] -= $learning_rate * $diff;
                    $word_vectors[$context][$k] += $learning_rate * $diff;
                }
            }
        }
    }

    echo "Epoch ".($e+1)." selesai\n";
}


// ===============================
// DOCUMENT VECTOR
// ===============================
function document_vector($doc, $word_vectors, $embedding_size){

    $vector = array_fill(0,$embedding_size,0);
    $count = 0;

    foreach($doc as $w){
        if(isset($word_vectors[$w])){
            for($i=0;$i<$embedding_size;$i++){
                $vector[$i] += $word_vectors[$w][$i];
            }
            $count++;
        }
    }

    if($count > 0){
        for($i=0;$i<$embedding_size;$i++){
            $vector[$i] /= $count;
        }
    }

    return $vector;
}


// ===============================
// HITUNG VECTOR DOKUMEN
// ===============================
$doc_vectors = [];

foreach($documents as $doc){
    $doc_vectors[] = document_vector($doc, $word_vectors, $embedding_size);
}


// ===============================
// CLUSTERING (AUTO LABEL)
// ===============================
function vector_sum($v){
    return array_sum($v);
}

$pos = [];
$neg = [];
$net = [];

foreach($doc_vectors as $i => $vec){

    $score = vector_sum($vec);

    if($score > 0.1){
        $label = 2;
        $pos[] = [$documents[$i], $score, $label];
    }
    elseif($score < -0.1){
        $label = 0;
        $neg[] = [$documents[$i], $score, $label];
    }
    else{
        $label = 1;
        $net[] = [$documents[$i], $score, $label];
    }
}


// ===============================
// BALANCING
// ===============================
$min = min(count($pos), count($neg), count($net));

shuffle($pos); $pos = array_slice($pos,0,$min);
shuffle($neg); $neg = array_slice($neg,0,$min);
shuffle($net); $net = array_slice($net,0,$min);

$data = array_merge($pos,$neg,$net);


// ===============================
// SAVE
// ===============================
$output = fopen("processed_balanced.csv","w");
fputcsv($output,["text","score","label"]);

foreach($data as $row){

    $text = implode(" ", $row[0]);
    fputcsv($output,[$text,$row[1],$row[2]]);
}

fclose($output);

echo "===== SELESAI =====\n";
echo "Total data: ".count($data)."\n";
echo "Per kelas: ".$min."\n";
echo "Dataset siap untuk XGBoost + PSO\n";