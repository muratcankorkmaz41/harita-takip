<?php
require_once 'functions.php';

$hatalar = [];

// Form ilk açıldığında gösterilecek varsayılan değerler
$veri = [
    'baslik'   => '',
    'kategori' => '',
    'durum'    => 'Planlandı',
    'aciklama' => '',
    'enlem'    => '',
    'boylam'   => '',
];

// Sayfa form gönderilerek (POST) açıldıysa verileri işle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfDogrula();

    // formVerisiAl() metin olmayan (ör. dizi) değerleri boş sayar ve boşlukları temizler
    $veri    = formVerisiAl();
    $hatalar = kayitDogrula($veri);

    // Hata yoksa kaydet ve ana sayfaya yönlendir (Post/Redirect/Get)
    if (count($hatalar) === 0) {
        $yeniId = kayitEkle($veri);
        bildirimAyarla('Kayıt başarıyla eklendi: ' . $veri['baslik'], 'basari', $yeniId, 'Yeni');
        yonlendir('index.php#kayitlar');
    }
}

$duzenleme  = false;
$kayitId    = 0;
$formAdresi = 'ekle.php';

$sayfaBasligi  = 'Yeni Kayıt';
$aktifSayfa    = 'ekle';
$haritaGerekli = true;
require 'header.php';
require 'kayit-formu.php';
require 'footer.php';
