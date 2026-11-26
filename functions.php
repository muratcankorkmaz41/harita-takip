<?php
// functions.php - Harita/Pist Takip Sistemi için yardımcı fonksiyonlar

require_once __DIR__ . '/baglanti.php';

// Oturumu (session) başlatır. Bildirim mesajları için $_SESSION kullanılır.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * HTML çıktısı için kısa kaçış fonksiyonu (XSS koruması).
 */
function e($deger): string
{
    return htmlspecialchars((string) $deger, ENT_QUOTES, 'UTF-8');
}

/**
 * Seçilebilecek kategorilerin listesi.
 */
function kategoriListesi(): array
{
    return ['Harita', 'Pist', 'Veri', 'Araç'];
}

/**
 * Seçilebilecek durumların listesi.
 */
function durumListesi(): array
{
    return ['Planlandı', 'Devam ediyor', 'Tamamlandı'];
}

/**
 * Kategoriye göre ikon (Bootstrap Icons) ve CSS sınıfı.
 */
function kategoriBilgi(string $kategori): array
{
    $liste = [
        'Harita' => ['ikon' => 'bi-map',          'sinif' => 'kat-harita'],
        'Pist'   => ['ikon' => 'bi-flag-fill',    'sinif' => 'kat-pist'],
        'Veri'   => ['ikon' => 'bi-database',     'sinif' => 'kat-veri'],
        'Araç'   => ['ikon' => 'bi-car-front-fill', 'sinif' => 'kat-arac'],
    ];
    return $liste[$kategori] ?? ['ikon' => 'bi-layers', 'sinif' => 'kat-diger'];
}

/**
 * Duruma göre ikon ve kısa anahtar (CSS ve JavaScript filtresi için).
 */
function durumBilgi(string $durum): array
{
    $liste = [
        'Planlandı'    => ['ikon' => 'bi-calendar-event',  'anahtar' => 'plan'],
        'Devam ediyor' => ['ikon' => 'bi-hourglass-split', 'anahtar' => 'devam'],
        'Tamamlandı'   => ['ikon' => 'bi-check-circle-fill', 'anahtar' => 'tamam'],
    ];
    return $liste[$durum] ?? ['ikon' => 'bi-calendar-event', 'anahtar' => 'plan'];
}

/**
 * Durum metnine göre CSS sınıfı döndürür.
 */
function durumSinifi(string $durum): string
{
    return 'durum-' . durumBilgi($durum)['anahtar'];
}

/**
 * Veritabanındaki kayıtları en yeniden eskiye doğru listeler.
 */
function kayitlariGetir(): array
{
    $sorgu = db()->query('SELECT id, baslik, kategori, durum, aciklama, enlem, boylam
                          FROM kayitlar ORDER BY id DESC');
    return array_map('kayitDuzenle', $sorgu->fetchAll());
}

/**
 * Veritabanından gelen satırdaki sayısal alanları doğru türe çevirir.
 * (PDO, DECIMAL değerlerini metin olarak döndürür.)
 */
function kayitDuzenle(array $satir): array
{
    $satir['id']     = (int) $satir['id'];
    $satir['enlem']  = $satir['enlem']  !== null ? (float) $satir['enlem']  : null;
    $satir['boylam'] = $satir['boylam'] !== null ? (float) $satir['boylam'] : null;
    return $satir;
}

/**
 * $_POST'tan bir alanı güvenli şekilde okur.
 * Alan yoksa ya da metin değilse (ör. baslik[]=x ile dizi gönderildiyse) boş metin döner.
 */
function postAl(string $alan): string
{
    $deger = $_POST[$alan] ?? '';
    return is_string($deger) ? trim($deger) : '';
}

/**
 * Form verisini doğrular.
 * Hataları alan adına göre döndürür: ['baslik' => 'mesaj', ...]
 * Dizi boşsa veri geçerlidir.
 */
function kayitDogrula(array $veri): array
{
    $hatalar = [];

    // Başlık: zorunlu, 3-80 karakter
    if ($veri['baslik'] === '') {
        $hatalar['baslik'] = 'Başlık boş bırakılamaz.';
    } elseif (mb_strlen($veri['baslik']) < 3) {
        $hatalar['baslik'] = 'Başlık en az 3 karakter olmalıdır.';
    } elseif (mb_strlen($veri['baslik']) > 80) {
        $hatalar['baslik'] = 'Başlık en fazla 80 karakter olabilir.';
    }

    // Kategori: listedeki değerlerden biri olmalı
    if ($veri['kategori'] === '') {
        $hatalar['kategori'] = 'Lütfen bir kategori seçiniz.';
    } elseif (!in_array($veri['kategori'], kategoriListesi(), true)) {
        $hatalar['kategori'] = 'Geçersiz kategori seçildi.';
    }

    // Durum: listedeki değerlerden biri olmalı
    if (!in_array($veri['durum'], durumListesi(), true)) {
        $hatalar['durum'] = 'Geçersiz durum seçildi.';
    }

    // Açıklama: zorunlu, 10-300 karakter
    if ($veri['aciklama'] === '') {
        $hatalar['aciklama'] = 'Açıklama boş bırakılamaz.';
    } elseif (mb_strlen($veri['aciklama']) < 10) {
        $hatalar['aciklama'] = 'Açıklama en az 10 karakter olmalıdır.';
    } elseif (mb_strlen($veri['aciklama']) > 300) {
        $hatalar['aciklama'] = 'Açıklama en fazla 300 karakter olabilir.';
    }

    // Konum: isteğe bağlı; verildiyse iki değer de geçerli sayı olmalı
    $enlem  = $veri['enlem'] ?? '';
    $boylam = $veri['boylam'] ?? '';
    if ($enlem !== '' || $boylam !== '') {
        if (!is_numeric($enlem) || !is_numeric($boylam)
            || abs((float) $enlem) > 90 || abs((float) $boylam) > 180) {
            $hatalar['konum'] = 'Geçersiz konum seçildi.';
        }
    }

    return $hatalar;
}

/**
 * Konum alanlarını veritabanına yazılacak hale getirir (boşsa NULL).
 */
function konumDegerleri(array $veri): array
{
    $konumVar = ($veri['enlem'] ?? '') !== '' && ($veri['boylam'] ?? '') !== '';
    return [
        $konumVar ? round((float) $veri['enlem'], 6) : null,
        $konumVar ? round((float) $veri['boylam'], 6) : null,
    ];
}

/**
 * Yeni kaydı veritabanına ekler ve yeni kaydın id'sini döndürür.
 * Değerler hazır ifade (prepared statement) parametreleriyle gönderilir,
 * böylece kullanıcı girdisi SQL komutu olarak yorumlanamaz.
 */
function kayitEkle(array $veri): int
{
    [$enlem, $boylam] = konumDegerleri($veri);

    $sorgu = db()->prepare('INSERT INTO kayitlar (baslik, kategori, durum, aciklama, enlem, boylam)
                            VALUES (:baslik, :kategori, :durum, :aciklama, :enlem, :boylam)');
    $sorgu->execute([
        ':baslik'   => $veri['baslik'],
        ':kategori' => $veri['kategori'],
        ':durum'    => $veri['durum'],
        ':aciklama' => $veri['aciklama'],
        ':enlem'    => $enlem,
        ':boylam'   => $boylam,
    ]);

    return (int) db()->lastInsertId();
}

/**
 * Belirli durumdaki kayıt sayısını döndürür.
 */
function durumSay(array $kayitlar, string $durum): int
{
    $sayac = 0;
    foreach ($kayitlar as $kayit) {
        if ($kayit['durum'] === $durum) {
            $sayac++;
        }
    }
    return $sayac;
}

/**
 * Haritada gösterilecek kayıtları JavaScript için hazırlar.
 * Konumu olmayan kayıtlar listeye alınmaz.
 */
function haritaVerisi(array $kayitlar): array
{
    $sonuc = [];
    foreach ($kayitlar as $kayit) {
        $enlem  = $kayit['enlem'] ?? null;
        $boylam = $kayit['boylam'] ?? null;
        if ($enlem === null || $boylam === null) {
            continue;
        }
        $sonuc[] = [
            'id'       => $kayit['id'],
            'baslik'   => $kayit['baslik'],
            'kategori' => $kayit['kategori'],
            'durum'    => $kayit['durum'],
            'anahtar'  => durumBilgi($kayit['durum'])['anahtar'],
            'enlem'    => $enlem,
            'boylam'   => $boylam,
        ];
    }
    return $sonuc;
}

/**
 * Veriyi <script type="application/json"> içine güvenle gömmek için JSON'a çevirir.
 */
function jsonGom(array $veri): string
{
    return json_encode(
        $veri,
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    );
}
