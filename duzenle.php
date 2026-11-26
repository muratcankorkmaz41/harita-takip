<?php
require_once 'functions.php';

// Düzenlenecek kaydın id'si adres satırından gelir: duzenle.php?id=3
$kayitId = idAl($_GET['id'] ?? '');
$kayit   = $kayitId > 0 ? kayitBul($kayitId) : null;

if ($kayit === null) {
    bildirimAyarla('Düzenlenmek istenen kayıt bulunamadı.', 'hata');
    yonlendir('index.php#kayitlar');
}

$hatalar = [];
$veri    = kayittanFormVerisi($kayit);   // form, kaydın mevcut değerleriyle açılır

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfDogrula();

    $veri    = formVerisiAl();
    $hatalar = kayitDogrula($veri);

    if (count($hatalar) === 0) {
        kayitGuncelle($kayitId, $veri);
        bildirimAyarla('Kayıt güncellendi: ' . $veri['baslik'], 'basari', $kayitId, 'Güncellendi');
        yonlendir('index.php#kayitlar');
    }
}

$duzenleme  = true;
$formAdresi = 'duzenle.php?id=' . $kayitId;

$sayfaBasligi  = 'Kaydı Düzenle';
$aktifSayfa    = '';
$haritaGerekli = true;
require 'header.php';
require 'kayit-formu.php';
require 'footer.php';
