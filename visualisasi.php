<?php

/* =====================
   LOAD DATASET ASLI
===================== */

$file = "processed.csv"; // DATASET ASLI (BUKAN BALANCED)

if(!file_exists($file)){
    die("Dataset belum ada. Jalankan preprocessing.php dulu");
}

$data = array_map('str_getcsv', file($file));
$header = array_shift($data);

$textIndex = array_search("text", $header);
$labelIndex = array_search("label", $header);

$label_map = [
0=>"Negatif",
1=>"Netral",
2=>"Positif"
];

$sentiment_count = [
"Negatif"=>0,
"Netral"=>0,
"Positif"=>0
];

$text_by_sentiment = [
"Negatif"=>"",
"Netral"=>"",
"Positif"=>""
];

$word_freq=[];

/* =====================
   PROSES DATA
===================== */

foreach($data as $row){

$text=$row[$textIndex];
$label=$row[$labelIndex];

$sent=$label_map[$label];

$sentiment_count[$sent]++;

$text_by_sentiment[$sent].=" ".$text;

$words=explode(" ",$text);

foreach($words as $w){

if(strlen($w)<3) continue;

if(!isset($word_freq[$w])) $word_freq[$w]=0;

$word_freq[$w]++;

}

}

arsort($word_freq);

$top_words=array_slice($word_freq,0,20,true);

$total=count($data);

?>

<!DOCTYPE html>
<html>

<head>

<title>Visualisasi Sentimen</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/wordcloud@1.2.2/src/wordcloud2.js"></script>

<style>

body{
font-family:Arial;
margin:40px;
background:#f4f6f9;
}

.card{
background:white;
padding:25px;
margin-bottom:30px;
border-radius:10px;
box-shadow:0 2px 6px rgba(0,0,0,0.1);
}

table{
border-collapse:collapse;
width:100%;
}

th,td{
padding:10px;
border:1px solid #ddd;
text-align:left;
}

th{
background:#f2f2f2;
}

</style>

</head>

<body>

<h1>Visualisasi Analisis Sentimen</h1>

<div class="card">

<h3>Total Dataset</h3>

<p><?php echo $total ?></p>

</div>


<div class="card">

<h3>Distribusi Sentimen (Dataset Asli)</h3>

<canvas id="chart"></canvas>

</div>


<div class="card">

<h3>Top 20 Kata Paling Sering</h3>

<table>

<tr>
<th>Kata</th>
<th>Frekuensi</th>
</tr>

<?php foreach($top_words as $w=>$c){ ?>

<tr>
<td><?php echo $w ?></td>
<td><?php echo $c ?></td>
</tr>

<?php } ?>

</table>

</div>


<div class="card">

<h3>Wordcloud Sentimen</h3>

<div style="display:flex;gap:40px;flex-wrap:wrap">

<div>

<h4>Negatif</h4>

<canvas id="negatif" width="400" height="300"></canvas>

</div>

<div>

<h4>Netral</h4>

<canvas id="netral" width="400" height="300"></canvas>

</div>

<div>

<h4>Positif</h4>

<canvas id="positif" width="400" height="300"></canvas>

</div>

</div>

</div>


<script>

/* =====================
   CHART SENTIMEN
===================== */

var counts=<?php echo json_encode(array_values($sentiment_count)); ?>;

new Chart(

document.getElementById("chart"),

{

type:"bar",

data:{

labels:["Negatif","Netral","Positif"],

datasets:[{

label:"Jumlah Sentimen",

backgroundColor:[
"#dc3545",
"#6c757d",
"#198754"
],

data:counts

}]

}

}

);


/* =====================
   WORDCLOUD
===================== */

var textData=<?php echo json_encode($text_by_sentiment); ?>;

function getWords(text){

var freq={};

var words=text.split(" ");

words.forEach(function(w){

if(w.length<3) return;

if(!freq[w]) freq[w]=0;

freq[w]++;

});

var list=[];

for(var w in freq){

list.push([w,freq[w]]);

}

return list;

}

WordCloud(document.getElementById("negatif"),{
list:getWords(textData["Negatif"])
});

WordCloud(document.getElementById("netral"),{
list:getWords(textData["Netral"])
});

WordCloud(document.getElementById("positif"),{
list:getWords(textData["Positif"])
});

</script>

</body>

</html>