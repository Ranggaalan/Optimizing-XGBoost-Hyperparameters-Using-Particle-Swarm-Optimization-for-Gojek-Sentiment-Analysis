<?php
?>

<!DOCTYPE html>
<html>
<head>

<title>Dashboard Analisis Sentimen</title>

<style>

body{
font-family:'Segoe UI',sans-serif;
margin:0;
background:#f5f7fb;
}

/* HEADER */

.header{
background:white;
padding:18px 40px;
border-bottom:1px solid #e5e5e5;
display:flex;
justify-content:space-between;
align-items:center;
}

.header h2{
margin:0;
font-size:20px;
color:#333;
}

/* CONTAINER */

.container{
max-width:1100px;
margin:auto;
padding:50px 40px;
}

/* GRID */

.grid{
display:grid;
grid-template-columns:1fr 1fr;
gap:40px;
align-items:center;
}

/* CARD */

.card{
background:white;
border-radius:12px;
padding:30px;
box-shadow:0 8px 20px rgba(0,0,0,0.06);
}

/* IMAGE */

.review-img{
width:100%;
max-width:420px;
display:block;
margin:auto;
}

/* UPLOAD */

input[type=file]{
width:100%;
padding:10px;
border:1px solid #ddd;
border-radius:8px;
}

button{
margin-top:15px;
padding:10px 20px;
background:#2563eb;
border:none;
border-radius:8px;
color:white;
font-weight:500;
cursor:pointer;
}

button:hover{
background:#1d4ed8;
}

/* FOOTER */

footer{
text-align:center;
margin-top:60px;
color:#888;
font-size:14px;
}

</style>

</head>

<body>

<div class="header">

<h2>Dashboard Analisis Sentimen</h2>

</div>

<div class="container">

<div class="grid">

<!-- GAMBAR REVIEW -->

<div>

<img src="https://cdn-icons-png.flaticon.com/512/942/942748.png" class="review-img">

</div>

<!-- UPLOAD DATASET -->

<div class="card">

<h3>Upload Data</h3>

<form action="upload.php" method="post" enctype="multipart/form-data">

<input type="file" name="dataset" required>

<button type="submit">Upload</button>

</form>

</div>

</div>

<footer>
Sistem Analisis Sentimen Review Aplikasi
</footer>

</div>

</body>
</html>