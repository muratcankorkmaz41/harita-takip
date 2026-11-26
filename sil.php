<?php
require_once 'functions.php';

// Silme işlemi yalnızca POST ile yapılır. Böylece bir bağlantıya tıklamak
// ya da adresi tarayıcıda açmak kayıt silmez.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Bu sayfa yalnızca silme formu ile kullanılabilir.');
}

csrfDogrula();

$id    = idAl($_POST['id'] ?? '');
$kayit = $id > 0 ? kayitBul($id) : null;

if ($kayit !== null && kayitSil($id)) {
    bildirimAyarla('Kayıt silindi: ' . $kayit['baslik']);
} else {
    bildirimAyarla('Silinmek istenen kayıt bulunamadı.', 'hata');
}

yonlendir('index.php#kayitlar');
