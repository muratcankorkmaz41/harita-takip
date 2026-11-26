<?php
require_once 'functions.php';

// Çıkış da durum değiştiren bir işlemdir; yalnızca CSRF anahtarlı POST ile yapılır
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    yonlendir('index.php');
}

csrfDogrula();
cikisYap();
bildirimAyarla('Oturumunuz kapatıldı.');
yonlendir('giris.php');
