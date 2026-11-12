<?php
// functions.php - Harita/Pist Takip Sistemi için yardımcı fonksiyonlar

// Oturumu (session) başlatır. Böylece $_SESSION kullanılabilir.
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
 * Başlangıçta kullanılan örnek kayıtlar.
 * enlem/boylam isteğe bağlıdır (null = konum yok).
 */
function ornekKayitlar(): array
{
    return [
        [
            'id'       => 1,
            'baslik'   => 'Niksar Merkez Yolları',
            'kategori' => 'Harita',
            'durum'    => 'Devam ediyor',
            'aciklama' => 'Niksar ilçe merkezindeki tüm yolların Blender\'da modellenmesi.',
            'enlem'    => 40.58959,
            'boylam'   => 36.95057,
        ],
        [
            'id'       => 2,
            'baslik'   => 'Niksar Çevre Yolu Pisti',
            'kategori' => 'Pist',
            'durum'    => 'Planlandı',
            'aciklama' => 'Assetto Corsa için çevre yolundan yarış pisti oluşturulacak.',
            'enlem'    => 40.58005,
            'boylam'   => 36.96132,
        ],
        [
            'id'       => 3,
            'baslik'   => 'Blosm ile Yol Verisi Aktarımı',
            'kategori' => 'Veri',
            'durum'    => 'Tamamlandı',
            'aciklama' => 'OpenStreetMap yol verisi Blosm eklentisiyle Blender\'a aktarıldı.',
            'enlem'    => null,
            'boylam'   => null,
        ],
        [
            'id'       => 4,
            'baslik'   => 'KSEditor Pist Ayarları',
            'kategori' => 'Pist',
            'durum'    => 'Devam ediyor',
            'aciklama' => 'Pistin KSEditor\'da yüzey, kamera ve spawn noktası ayarları.',
            'enlem'    => 40.58060,
            'boylam'   => 36.94107,
        ],
        [
            'id'       => 5,
            'baslik'   => 'Yol Dokuları ve Çizgiler',
            'kategori' => 'Harita',
            'durum'    => 'Planlandı',
            'aciklama' => 'Asfalt dokusu, orta çizgi ve kenar çizgilerinin eklenmesi.',
            'enlem'    => 40.59198,
            'boylam'   => 36.95936,
        ],
    ];
}

/**
 * Kayıtları $_SESSION'dan döndürür.
 * Oturumda henüz kayıt yoksa örnek kayıtlarla başlatır.
 */
function kayitlariGetir(): array
{
    if (!isset($_SESSION['kayitlar'])) {
        $_SESSION['kayitlar'] = ornekKayitlar();
    }
    return $_SESSION['kayitlar'];
}

/**
 * Yeni kayıt için bir sonraki id numarasını üretir.
 */
function yeniIdUret(array $kayitlar): int
{
    $enBuyuk = 0;
    foreach ($kayitlar as $kayit) {
        if ($kayit['id'] > $enBuyuk) {
            $enBuyuk = $kayit['id'];
        }
    }
    return $enBuyuk + 1;
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
 * Yeni kaydı $_SESSION içindeki listeye ekler ve yeni kaydın id'sini döndürür.
 */
function kayitEkle(array $veri): int
{
    $kayitlar = kayitlariGetir();
    $id = yeniIdUret($kayitlar);
    $konumVar = ($veri['enlem'] ?? '') !== '' && ($veri['boylam'] ?? '') !== '';

    $kayitlar[] = [
        'id'       => $id,
        'baslik'   => $veri['baslik'],
        'kategori' => $veri['kategori'],
        'durum'    => $veri['durum'],
        'aciklama' => $veri['aciklama'],
        'enlem'    => $konumVar ? round((float) $veri['enlem'], 6) : null,
        'boylam'   => $konumVar ? round((float) $veri['boylam'], 6) : null,
    ];

    $_SESSION['kayitlar'] = $kayitlar;
    return $id;
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
