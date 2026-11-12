<?php
// functions.php - Harita/Pist Takip Sistemi için yardımcı fonksiyonlar

// Oturumu (session) başlatır. Böylece $_SESSION kullanılabilir.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
 * Başlangıçta kullanılan örnek kayıtlar.
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
        ],
        [
            'id'       => 2,
            'baslik'   => 'Niksar Çevre Yolu Pisti',
            'kategori' => 'Pist',
            'durum'    => 'Planlandı',
            'aciklama' => 'Assetto Corsa için çevre yolundan yarış pisti oluşturulacak.',
        ],
        [
            'id'       => 3,
            'baslik'   => 'Blosm ile Yol Verisi Aktarımı',
            'kategori' => 'Veri',
            'durum'    => 'Tamamlandı',
            'aciklama' => 'OpenStreetMap yol verisi Blosm eklentisiyle Blender\'a aktarıldı.',
        ],
        [
            'id'       => 4,
            'baslik'   => 'KSEditor Pist Ayarları',
            'kategori' => 'Pist',
            'durum'    => 'Devam ediyor',
            'aciklama' => 'Pistin KSEditor\'da yüzey, kamera ve spawn noktası ayarları.',
        ],
        [
            'id'       => 5,
            'baslik'   => 'Yol Dokuları ve Çizgiler',
            'kategori' => 'Harita',
            'durum'    => 'Planlandı',
            'aciklama' => 'Asfalt dokusu, orta çizgi ve kenar çizgilerinin eklenmesi.',
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
 * Form verisini doğrular. Hata mesajlarını dizi olarak döndürür.
 * Dizi boşsa veri geçerlidir.
 */
function kayitDogrula(array $veri): array
{
    $hatalar = [];

    // Başlık: zorunlu, 3-80 karakter
    if ($veri['baslik'] === '') {
        $hatalar[] = 'Başlık boş bırakılamaz.';
    } elseif (mb_strlen($veri['baslik']) < 3) {
        $hatalar[] = 'Başlık en az 3 karakter olmalıdır.';
    } elseif (mb_strlen($veri['baslik']) > 80) {
        $hatalar[] = 'Başlık en fazla 80 karakter olabilir.';
    }

    // Kategori: listedeki değerlerden biri olmalı
    if ($veri['kategori'] === '') {
        $hatalar[] = 'Lütfen bir kategori seçiniz.';
    } elseif (!in_array($veri['kategori'], kategoriListesi(), true)) {
        $hatalar[] = 'Geçersiz kategori seçildi.';
    }

    // Durum: listedeki değerlerden biri olmalı
    if (!in_array($veri['durum'], durumListesi(), true)) {
        $hatalar[] = 'Geçersiz durum seçildi.';
    }

    // Açıklama: zorunlu, 10-300 karakter
    if ($veri['aciklama'] === '') {
        $hatalar[] = 'Açıklama boş bırakılamaz.';
    } elseif (mb_strlen($veri['aciklama']) < 10) {
        $hatalar[] = 'Açıklama en az 10 karakter olmalıdır.';
    } elseif (mb_strlen($veri['aciklama']) > 300) {
        $hatalar[] = 'Açıklama en fazla 300 karakter olabilir.';
    }

    return $hatalar;
}

/**
 * Yeni kaydı $_SESSION içindeki listeye ekler.
 */
function kayitEkle(array $veri): void
{
    $kayitlar = kayitlariGetir();

    $kayitlar[] = [
        'id'       => yeniIdUret($kayitlar),
        'baslik'   => $veri['baslik'],
        'kategori' => $veri['kategori'],
        'durum'    => $veri['durum'],
        'aciklama' => $veri['aciklama'],
    ];

    $_SESSION['kayitlar'] = $kayitlar;
}

/**
 * Durum metnine göre CSS sınıfı döndürür.
 */
function durumSinifi(string $durum): string
{
    if ($durum === 'Tamamlandı') {
        return 'durum-tamam';
    } elseif ($durum === 'Devam ediyor') {
        return 'durum-devam';
    }
    return 'durum-plan';
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
