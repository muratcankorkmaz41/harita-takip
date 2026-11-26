<?php
require_once 'functions.php';

if (oturumKullanici() !== null) {
    yonlendir('index.php');
}

$veri    = ['ad_soyad' => '', 'kullanici_adi' => '', 'sifre' => '', 'sifre_tekrar' => ''];
$hatalar = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfDogrula();

    // Şifreler trim edilmez (boşluk şifrenin parçası olabilir)
    $sifreAl = function (string $alan): string {
        $deger = $_POST[$alan] ?? '';
        return is_string($deger) ? $deger : '';
    };
    $veri = [
        'ad_soyad'      => postAl('ad_soyad'),
        'kullanici_adi' => mb_strtolower(postAl('kullanici_adi')),
        'sifre'         => $sifreAl('sifre'),
        'sifre_tekrar'  => $sifreAl('sifre_tekrar'),
    ];

    $hatalar = kullaniciDogrula($veri);

    if (count($hatalar) === 0) {
        kullaniciOlustur($veri);
        girisYap($veri['kullanici_adi'], $veri['sifre']);   // kayıttan sonra otomatik giriş
        bildirimAyarla('Hesabınız oluşturuldu. İlk kaydınızı ekleyebilirsiniz.');
        yonlendir('index.php');
    }
}

$sayfaBasligi = 'Kayıt Ol';
$aktifSayfa   = 'kayit-ol';
require 'header.php';
?>

<main id="icerik" class="kapsayici sayfa-giris">
    <section class="form-kart giris-kart reveal" aria-labelledby="kayit-baslik">
        <div class="giris-ust">
            <span class="giris-ikon" aria-hidden="true"><i class="bi bi-person-plus"></i></span>
            <h1 id="kayit-baslik">Kayıt Ol</h1>
            <p>Yeni bir hesap oluşturun. Her kullanıcı yalnızca kendi kayıtlarını görür.</p>
        </div>

        <?php if (count($hatalar) > 0): ?>
            <div class="uyari hata" role="alert">
                <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                <div>
                    <strong>Hesap oluşturulamadı</strong>
                    <ul>
                        <?php foreach ($hatalar as $h): ?>
                            <li><?php echo e($h); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <form method="post" action="kayit-ol.php">
            <?php echo csrfAlani(); ?>
            <div class="alan <?php echo isset($hatalar['ad_soyad']) ? 'hatali' : ''; ?>">
                <label for="ad_soyad">Ad soyad</label>
                <div class="girdi-ikon">
                    <i class="bi bi-person-vcard" aria-hidden="true"></i>
                    <input type="text" id="ad_soyad" name="ad_soyad" required minlength="3" maxlength="60"
                           autocomplete="name" value="<?php echo e($veri['ad_soyad']); ?>"
                           <?php echo hataOznitelik($hatalar, 'ad_soyad'); ?>>
                </div>
                <?php echo hataMesaji($hatalar, 'ad_soyad'); ?>
            </div>
            <div class="alan <?php echo isset($hatalar['kullanici_adi']) ? 'hatali' : ''; ?>">
                <label for="kullanici_adi">Kullanıcı adı</label>
                <div class="girdi-ikon">
                    <i class="bi bi-at" aria-hidden="true"></i>
                    <input type="text" id="kullanici_adi" name="kullanici_adi" required minlength="3" maxlength="30"
                           pattern="[a-z0-9_]{3,30}" title="3-30 karakter: küçük harf (a-z), rakam ve _"
                           autocomplete="username" autocapitalize="none"
                           value="<?php echo e($veri['kullanici_adi']); ?>"
                           <?php echo hataOznitelik($hatalar, 'kullanici_adi'); ?>>
                </div>
                <?php echo hataMesaji($hatalar, 'kullanici_adi'); ?>
            </div>
            <div class="alan <?php echo isset($hatalar['sifre']) ? 'hatali' : ''; ?>">
                <label for="sifre">Şifre</label>
                <div class="girdi-ikon">
                    <i class="bi bi-key" aria-hidden="true"></i>
                    <input type="password" id="sifre" name="sifre" required minlength="8" maxlength="72"
                           autocomplete="new-password" placeholder="En az 8 karakter, harf ve rakam"
                           <?php echo hataOznitelik($hatalar, 'sifre'); ?>>
                </div>
                <?php echo hataMesaji($hatalar, 'sifre'); ?>
            </div>
            <div class="alan <?php echo isset($hatalar['sifre_tekrar']) ? 'hatali' : ''; ?>">
                <label for="sifre_tekrar">Şifre (tekrar)</label>
                <div class="girdi-ikon">
                    <i class="bi bi-key-fill" aria-hidden="true"></i>
                    <input type="password" id="sifre_tekrar" name="sifre_tekrar" required minlength="8" maxlength="72"
                           autocomplete="new-password" <?php echo hataOznitelik($hatalar, 'sifre_tekrar'); ?>>
                </div>
                <?php echo hataMesaji($hatalar, 'sifre_tekrar'); ?>
            </div>
            <button type="submit" class="buton birincil tam"><i class="bi bi-person-check" aria-hidden="true"></i> Hesap Oluştur</button>
        </form>

        <p class="giris-alt">Zaten hesabınız var mı? <a href="giris.php">Giriş yapın</a></p>
    </section>
</main>

<?php require 'footer.php'; ?>
