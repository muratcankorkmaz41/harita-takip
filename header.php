<?php
// Sayfa ayarları tanımlanmadıysa (ör. dosya doğrudan açıldıysa) varsayılanları kullan
$sayfaBasligi  = $sayfaBasligi ?? 'Ana Sayfa';
$aktifSayfa    = $aktifSayfa ?? '';
$haritaGerekli = $haritaGerekli ?? false;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Assetto Corsa için hazırlanan harita ve pist projelerinin durumunu takip eden PHP kayıt sistemi.">
    <meta name="theme-color" content="#0b0d12">
    <title><?php echo e($sayfaBasligi); ?> · Harita &amp; Pist Takip Sistemi</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23ff5a1f'/%3E%3Cpath d='M8 22c0-5 4-5 6-8s1-6 5-6 5 4 3 7-6 3-6 7 3 3 6 3' fill='none' stroke='%23fff' stroke-width='2.6' stroke-linecap='round'/%3E%3C/svg%3E">
    <link rel="stylesheet" href="assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <?php if ($haritaGerekli): ?>
        <link rel="stylesheet" href="assets/vendor/leaflet/leaflet.css">
    <?php endif; ?>
    <link rel="stylesheet" href="assets/style.css?v=<?php echo filemtime(__DIR__ . '/assets/style.css'); ?>">
    <script>document.documentElement.classList.add('js');</script>
</head>
<body>
<a class="atla" href="#icerik">İçeriğe geç</a>

<header class="ust">
    <div class="ust-ic">
        <a class="marka" href="index.php" aria-label="Harita ve Pist Takip Sistemi - Ana Sayfa">
            <span class="marka-logo" aria-hidden="true">
                <svg viewBox="0 0 32 32"><path d="M8 22c0-5 4-5 6-8s1-6 5-6 5 4 3 7-6 3-6 7 3 3 6 3"/></svg>
            </span>
            <span class="marka-metin">
                <strong>Harita &amp; Pist</strong>
                <small>Takip Sistemi</small>
            </span>
        </a>

        <nav class="menu" aria-label="Ana menü">
            <a href="index.php" class="<?php echo $aktifSayfa === 'ana' ? 'aktif' : ''; ?>"
               <?php echo $aktifSayfa === 'ana' ? 'aria-current="page"' : ''; ?>>
                <i class="bi bi-house-door" aria-hidden="true"></i><span>Ana Sayfa</span>
            </a>
            <a href="index.php#kayitlar">
                <i class="bi bi-grid-3x3-gap" aria-hidden="true"></i><span>Kayıtlar</span>
            </a>
            <a href="ekle.php" class="menu-cta <?php echo $aktifSayfa === 'ekle' ? 'aktif' : ''; ?>"
               <?php echo $aktifSayfa === 'ekle' ? 'aria-current="page"' : ''; ?>>
                <i class="bi bi-plus-lg" aria-hidden="true"></i><span>Yeni Kayıt</span>
            </a>
        </nav>
    </div>
</header>
