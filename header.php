<?php
// Sayfa başlığı tanımlanmadıysa (ör. dosya doğrudan açıldıysa) varsayılanı kullan
$sayfaBasligi = $sayfaBasligi ?? 'Ana Sayfa';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($sayfaBasligi); ?> - Harita &amp; Pist Takip Sistemi</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header class="ust">
    <h1>🗺️ Harita &amp; Pist Takip Sistemi</h1>
    <nav>
        <a href="index.php">Ana Sayfa</a>
        <a href="index.php#kayitlar">Kayıtlar</a>
        <a href="ekle.php">Yeni Kayıt</a>
    </nav>
</header>
