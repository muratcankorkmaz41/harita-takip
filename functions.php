<?php
// functions.php - Harita/Pist Takip Sistemi için yardımcı fonksiyonlar

require_once __DIR__ . '/baglanti.php';

// Oturumu (session) başlatır. Giriş yapan kullanıcı ve bildirimler $_SESSION'da tutulur.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,   // oturum çerezi JavaScript ile okunamaz
        'samesite' => 'Lax',  // başka sitelerden gelen POST isteklerine çerez eklenmez
    ]);
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
 * Başka bir sayfaya yönlendirir ve betiği sonlandırır.
 */
function yonlendir(string $adres): void
{
    header('Location: ' . $adres);
    exit;
}

/**
 * Bir sonraki sayfada bir kez gösterilecek bildirimi oturuma yazar.
 * $vurguId verilirse o kayıt kartı vurgulanır ve üstünde $etiket yazar.
 */
function bildirimAyarla(string $mesaj, string $tur = 'basari', int $vurguId = 0, string $etiket = 'Yeni'): void
{
    $_SESSION['bildirim'] = ['mesaj' => $mesaj, 'tur' => $tur, 'vurguId' => $vurguId, 'etiket' => $etiket];
}

/**
 * Oturumdaki bildirimi döndürür ve siler (yoksa null).
 */
function bildirimAl(): ?array
{
    $bildirim = $_SESSION['bildirim'] ?? null;
    unset($_SESSION['bildirim']);
    return is_array($bildirim) ? $bildirim : null;
}

/* --------------------------------------------------------------------------
   CSRF koruması: veri değiştiren her POST formu oturuma özel bir anahtar taşır.
   Başka bir siteden gönderilen sahte formlar bu anahtarı bilemez.
   -------------------------------------------------------------------------- */
function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfAlani(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrfToken()) . '">';
}

function csrfDogrula(): void
{
    $gelen = $_POST['csrf'] ?? '';
    if (!is_string($gelen) || !hash_equals(csrfToken(), $gelen)) {
        http_response_code(400);
        exit('Geçersiz form isteği. Sayfayı yenileyip tekrar deneyin.');
    }
}

/* --------------------------------------------------------------------------
   Kullanıcı oturumu
   -------------------------------------------------------------------------- */

/**
 * Giriş yapmış kullanıcıyı döndürür: ['id' => 1, 'kullanici_adi' => ..., 'ad_soyad' => ...]
 * Giriş yapılmamışsa null döner.
 */
function oturumKullanici(): ?array
{
    $kullanici = $_SESSION['kullanici'] ?? null;
    return is_array($kullanici) ? $kullanici : null;
}

/**
 * Sayfayı yalnızca giriş yapmış kullanıcılara açar ve kullanıcının id'sini döndürür.
 * Giriş yapılmamışsa giriş sayfasına yönlendirir.
 */
function girisGerekli(): int
{
    $kullanici = oturumKullanici();
    if ($kullanici === null) {
        bildirimAyarla('Bu sayfayı görmek için giriş yapmalısınız.', 'hata');
        yonlendir('giris.php');
    }
    return (int) $kullanici['id'];
}

/**
 * Kullanıcı adı ve şifreyi kontrol eder; doğruysa oturumu açar.
 */
function girisYap(string $kullaniciAdi, string $sifre): bool
{
    $sorgu = db()->prepare('SELECT id, kullanici_adi, ad_soyad, sifre_hash
                            FROM kullanicilar WHERE kullanici_adi = :ad');
    $sorgu->execute([':ad' => $kullaniciAdi]);
    $kullanici = $sorgu->fetch();

    // Şifre düz metin olarak karşılaştırılmaz; password_verify özetle karşılaştırır
    if (!$kullanici || !password_verify($sifre, $kullanici['sifre_hash'])) {
        return false;
    }

    // Oturum sabitleme (session fixation) saldırısına karşı oturum kimliği yenilenir
    session_regenerate_id(true);
    $_SESSION['kullanici'] = [
        'id'            => (int) $kullanici['id'],
        'kullanici_adi' => $kullanici['kullanici_adi'],
        'ad_soyad'      => $kullanici['ad_soyad'],
    ];
    unset($_SESSION['csrf']); // girişten sonra yeni bir CSRF anahtarı üretilir
    return true;
}

/**
 * Oturumu tamamen kapatır.
 */
function cikisYap(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    session_start();          // çıkış bildirimini gösterebilmek için yeni, boş bir oturum
    session_regenerate_id(true);
}

/**
 * Kayıt olma formunu doğrular.
 */
function kullaniciDogrula(array $veri): array
{
    $hatalar = [];

    if ($veri['ad_soyad'] === '') {
        $hatalar['ad_soyad'] = 'Ad soyad boş bırakılamaz.';
    } elseif (mb_strlen($veri['ad_soyad']) < 3 || mb_strlen($veri['ad_soyad']) > 60) {
        $hatalar['ad_soyad'] = 'Ad soyad 3-60 karakter olmalıdır.';
    }

    if ($veri['kullanici_adi'] === '') {
        $hatalar['kullanici_adi'] = 'Kullanıcı adı boş bırakılamaz.';
    } elseif (!preg_match('/^[a-z0-9_]{3,30}$/', $veri['kullanici_adi'])) {
        $hatalar['kullanici_adi'] = 'Kullanıcı adı 3-30 karakter olmalı; yalnızca küçük harf (a-z), rakam ve _ içerebilir.';
    } elseif (kullaniciAdiVarMi($veri['kullanici_adi'])) {
        $hatalar['kullanici_adi'] = 'Bu kullanıcı adı alınmış.';
    }

    if (mb_strlen($veri['sifre']) < 8) {
        $hatalar['sifre'] = 'Şifre en az 8 karakter olmalıdır.';
    } elseif (mb_strlen($veri['sifre']) > 72) {
        $hatalar['sifre'] = 'Şifre en fazla 72 karakter olabilir.';
    } elseif (!preg_match('/[A-Za-z]/', $veri['sifre']) || !preg_match('/\d/', $veri['sifre'])) {
        $hatalar['sifre'] = 'Şifre en az bir harf ve bir rakam içermelidir.';
    }

    if (!isset($hatalar['sifre']) && $veri['sifre'] !== $veri['sifre_tekrar']) {
        $hatalar['sifre_tekrar'] = 'Şifreler birbiriyle aynı değil.';
    }

    return $hatalar;
}

function kullaniciAdiVarMi(string $kullaniciAdi): bool
{
    $sorgu = db()->prepare('SELECT 1 FROM kullanicilar WHERE kullanici_adi = :ad');
    $sorgu->execute([':ad' => $kullaniciAdi]);
    return (bool) $sorgu->fetchColumn();
}

/**
 * Yeni kullanıcı oluşturur. Şifre password_hash() ile özetlenerek saklanır.
 */
function kullaniciOlustur(array $veri): int
{
    $sorgu = db()->prepare('INSERT INTO kullanicilar (kullanici_adi, ad_soyad, sifre_hash)
                            VALUES (:ad, :ad_soyad, :hash)');
    $sorgu->execute([
        ':ad'       => $veri['kullanici_adi'],
        ':ad_soyad' => $veri['ad_soyad'],
        ':hash'     => password_hash($veri['sifre'], PASSWORD_DEFAULT),
    ]);
    return (int) db()->lastInsertId();
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
 * Sıralama seçenekleri: adres satırındaki anahtar => [etiket, ORDER BY ifadesi].
 * ORDER BY kısmı parametre olarak gönderilemez; bu yüzden kullanıcıdan gelen değer
 * doğrudan SQL'e yazılmaz, yalnızca bu listedeki sabit ifadelerden biri seçilir.
 */
function siralamaListesi(): array
{
    return [
        'yeni'     => ['En yeni',            'id DESC'],
        'eski'     => ['En eski',            'id ASC'],
        'guncel'   => ['Son güncellenen',    'guncelleme_tarihi DESC, id DESC'],
        'baslik'   => ['Başlık (A → Z)',     'baslik ASC'],
        'baslik-z' => ['Başlık (Z → A)',     'baslik DESC'],
        'durum'    => ['Duruma göre',        'durum ASC, id DESC'],   // ENUM sırası: Planlandı, Devam ediyor, Tamamlandı
    ];
}

/**
 * Adres satırındaki (GET) arama/filtre/sıralama değerlerini okur.
 * Listede olmayan değerler yok sayılır.
 */
function listeKosullariAl(): array
{
    $al = function (string $alan): string {
        $deger = $_GET[$alan] ?? '';
        return is_string($deger) ? trim($deger) : '';
    };

    $kosul = [
        'q'        => mb_substr($al('q'), 0, 100),
        'durum'    => $al('durum'),
        'kategori' => $al('kategori'),
        'sirala'   => $al('sirala'),
    ];

    if (!in_array($kosul['durum'], durumListesi(), true)) {
        $kosul['durum'] = '';
    }
    if (!in_array($kosul['kategori'], kategoriListesi(), true)) {
        $kosul['kategori'] = '';
    }
    if (!array_key_exists($kosul['sirala'], siralamaListesi())) {
        $kosul['sirala'] = 'yeni';
    }
    return $kosul;
}

/**
 * Kayıtları arama, filtre ve sıralama koşullarına göre veritabanından getirir.
 * WHERE koşulları yalnızca dolu filtreler için eklenir; değerler parametreyle gönderilir.
 */
function kayitlariGetir(int $kullaniciId, array $kosul = []): array
{
    // Kullanıcı yalnızca kendi kayıtlarını görür
    $sartlar = ['kullanici_id = :kid'];
    $param   = [':kid' => $kullaniciId];

    if (($kosul['q'] ?? '') !== '') {
        // % ve _ LIKE içinde joker karakterdir; kullanıcı yazdıysa düz karakter olarak aranır
        $aranan = '%' . addcslashes($kosul['q'], '%_\\') . '%';
        $sartlar[] = '(baslik LIKE :q1 OR aciklama LIKE :q2)';
        $param[':q1'] = $aranan;
        $param[':q2'] = $aranan;
    }
    if (($kosul['durum'] ?? '') !== '') {
        $sartlar[] = 'durum = :durum';
        $param[':durum'] = $kosul['durum'];
    }
    if (($kosul['kategori'] ?? '') !== '') {
        $sartlar[] = 'kategori = :kategori';
        $param[':kategori'] = $kosul['kategori'];
    }

    $siralamalar = siralamaListesi();
    $siralama = $siralamalar[$kosul['sirala'] ?? 'yeni'][1] ?? $siralamalar['yeni'][1];

    $sql = 'SELECT id, baslik, kategori, durum, aciklama, enlem, boylam FROM kayitlar'
         . ' WHERE ' . implode(' AND ', $sartlar)
         . ' ORDER BY ' . $siralama;

    $sorgu = db()->prepare($sql);
    $sorgu->execute($param);
    return array_map('kayitDuzenle', $sorgu->fetchAll());
}

/**
 * Kullanıcının kayıtlarının duruma göre sayılarını döndürür (filtrelerden bağımsız).
 * ['toplam' => 5, 'Planlandı' => 2, 'Devam ediyor' => 2, 'Tamamlandı' => 1]
 */
function kayitOzeti(int $kullaniciId): array
{
    $ozet = ['toplam' => 0];
    foreach (durumListesi() as $durum) {
        $ozet[$durum] = 0;
    }
    $sorgu = db()->prepare('SELECT durum, COUNT(*) AS adet FROM kayitlar
                            WHERE kullanici_id = :kid GROUP BY durum');
    $sorgu->execute([':kid' => $kullaniciId]);
    foreach ($sorgu->fetchAll() as $satir) {
        $ozet[$satir['durum']] = (int) $satir['adet'];
        $ozet['toplam'] += (int) $satir['adet'];
    }
    return $ozet;
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
 * Kullanıcıya ait tek bir kaydı getirir.
 * Kayıt yoksa YA DA başka bir kullanıcıya aitse null döner; iki durum dışarıdan ayırt edilemez.
 */
function kayitBul(int $id, int $kullaniciId): ?array
{
    $sorgu = db()->prepare('SELECT id, baslik, kategori, durum, aciklama, enlem, boylam
                            FROM kayitlar WHERE id = :id AND kullanici_id = :kid');
    $sorgu->execute([':id' => $id, ':kid' => $kullaniciId]);
    $satir = $sorgu->fetch();
    return $satir ? kayitDuzenle($satir) : null;
}

/**
 * Formdan gelen alanları okur (ekleme ve düzenleme formu için ortak).
 */
function formVerisiAl(): array
{
    return [
        'baslik'   => postAl('baslik'),
        'kategori' => postAl('kategori'),
        'durum'    => postAl('durum'),
        'aciklama' => postAl('aciklama'),
        'enlem'    => postAl('enlem'),
        'boylam'   => postAl('boylam'),
    ];
}

/**
 * Veritabanındaki kaydı forma doldurulacak metin değerlerine çevirir.
 */
function kayittanFormVerisi(array $kayit): array
{
    return [
        'baslik'   => $kayit['baslik'],
        'kategori' => $kayit['kategori'],
        'durum'    => $kayit['durum'],
        'aciklama' => $kayit['aciklama'],
        'enlem'    => $kayit['enlem']  !== null ? number_format($kayit['enlem'], 6, '.', '')  : '',
        'boylam'   => $kayit['boylam'] !== null ? number_format($kayit['boylam'], 6, '.', '') : '',
    ];
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
 * Yeni kaydı giriş yapan kullanıcı adına veritabanına ekler ve yeni kaydın id'sini döndürür.
 * Değerler hazır ifade (prepared statement) parametreleriyle gönderilir,
 * böylece kullanıcı girdisi SQL komutu olarak yorumlanamaz.
 */
function kayitEkle(int $kullaniciId, array $veri): int
{
    [$enlem, $boylam] = konumDegerleri($veri);

    $sorgu = db()->prepare('INSERT INTO kayitlar (kullanici_id, baslik, kategori, durum, aciklama, enlem, boylam)
                            VALUES (:kid, :baslik, :kategori, :durum, :aciklama, :enlem, :boylam)');
    $sorgu->execute([
        ':kid'      => $kullaniciId,
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
 * Kullanıcıya ait kaydı günceller. Kayıt bulunduysa true döner.
 * WHERE koşulundaki kullanici_id sayesinde başkasının kaydı değiştirilemez.
 */
function kayitGuncelle(int $id, int $kullaniciId, array $veri): bool
{
    [$enlem, $boylam] = konumDegerleri($veri);

    $sorgu = db()->prepare('UPDATE kayitlar
                            SET baslik = :baslik, kategori = :kategori, durum = :durum,
                                aciklama = :aciklama, enlem = :enlem, boylam = :boylam
                            WHERE id = :id AND kullanici_id = :kid');
    $sorgu->execute([
        ':baslik'   => $veri['baslik'],
        ':kategori' => $veri['kategori'],
        ':durum'    => $veri['durum'],
        ':aciklama' => $veri['aciklama'],
        ':enlem'    => $enlem,
        ':boylam'   => $boylam,
        ':id'       => $id,
        ':kid'      => $kullaniciId,
    ]);

    // Değerler değişmediyse rowCount 0 olabilir; bu yüzden kaydın varlığını ayrıca kontrol ederiz
    return $sorgu->rowCount() > 0 || kayitBul($id, $kullaniciId) !== null;
}

/**
 * Kullanıcıya ait kaydı siler. Bir satır silindiyse true döner.
 */
function kayitSil(int $id, int $kullaniciId): bool
{
    $sorgu = db()->prepare('DELETE FROM kayitlar WHERE id = :id AND kullanici_id = :kid');
    $sorgu->execute([':id' => $id, ':kid' => $kullaniciId]);
    return $sorgu->rowCount() > 0;
}

/**
 * Adres satırından ya da formdan gelen id değerini pozitif tam sayıya çevirir (geçersizse 0).
 */
function idAl($deger): int
{
    $id = filter_var($deger, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? 0 : $id;
}

/**
 * Bir alan hatalıysa form elemanına eklenecek erişilebilirlik öznitelikleri.
 */
function hataOznitelik(array $hatalar, string $alan): string
{
    return isset($hatalar[$alan])
        ? 'aria-invalid="true" aria-describedby="' . $alan . '-hata"'
        : '';
}

/**
 * Alanın altında gösterilecek hata mesajı.
 */
function hataMesaji(array $hatalar, string $alan): string
{
    if (!isset($hatalar[$alan])) {
        return '';
    }
    return '<p class="alan-hata" id="' . $alan . '-hata"><i class="bi bi-exclamation-circle" aria-hidden="true"></i> '
        . e($hatalar[$alan]) . '</p>';
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
