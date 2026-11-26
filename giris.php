<?php
require_once 'functions.php';

// Zaten giriş yapılmışsa ana sayfaya gönder
if (oturumKullanici() !== null) {
    yonlendir('index.php');
}

$kullaniciAdi = '';
$hata = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfDogrula();

    $kullaniciAdi = mb_strtolower(postAl('kullanici_adi'));
    $sifre        = $_POST['sifre'] ?? '';
    $sifre        = is_string($sifre) ? $sifre : '';

    if ($kullaniciAdi === '' || $sifre === '') {
        $hata = 'Kullanıcı adı ve şifre boş bırakılamaz.';
    } elseif (girisYap($kullaniciAdi, $sifre)) {
        bildirimAyarla('Hoş geldiniz, ' . oturumKullanici()['ad_soyad'] . '!');
        yonlendir('index.php');
    } else {
        // Hangi alanın yanlış olduğu söylenmez; böylece kayıtlı kullanıcı adları tahmin edilemez
        $hata = 'Kullanıcı adı veya şifre hatalı.';
    }
}

$bildirim = bildirimAl();

$sayfaBasligi = 'Giriş Yap';
$aktifSayfa   = 'giris';
require 'header.php';
?>

<main id="icerik" class="kapsayici sayfa-giris">
    <?php require 'bildirim.php'; ?>

    <section class="form-kart giris-kart reveal" aria-labelledby="giris-baslik">
        <div class="giris-ust">
            <span class="giris-ikon" aria-hidden="true"><i class="bi bi-shield-lock"></i></span>
            <h1 id="giris-baslik">Giriş Yap</h1>
            <p>Kayıtlarınızı görmek ve yönetmek için hesabınıza giriş yapın.</p>
        </div>

        <?php if ($hata !== ''): ?>
            <div class="uyari hata" role="alert">
                <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                <div><strong><?php echo e($hata); ?></strong></div>
            </div>
        <?php endif; ?>

        <form method="post" action="giris.php" novalidate>
            <?php echo csrfAlani(); ?>
            <div class="alan">
                <label for="kullanici_adi">Kullanıcı adı</label>
                <div class="girdi-ikon">
                    <i class="bi bi-person" aria-hidden="true"></i>
                    <input type="text" id="kullanici_adi" name="kullanici_adi" required maxlength="30"
                           autocomplete="username" autocapitalize="none" autofocus
                           value="<?php echo e($kullaniciAdi); ?>">
                </div>
            </div>
            <div class="alan">
                <label for="sifre">Şifre</label>
                <div class="girdi-ikon">
                    <i class="bi bi-key" aria-hidden="true"></i>
                    <input type="password" id="sifre" name="sifre" required maxlength="72" autocomplete="current-password">
                </div>
            </div>
            <button type="submit" class="buton birincil tam"><i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Giriş Yap</button>
        </form>

        <p class="giris-alt">Hesabınız yok mu? <a href="kayit-ol.php">Kayıt olun</a></p>
    </section>
</main>

<?php require 'footer.php'; ?>
