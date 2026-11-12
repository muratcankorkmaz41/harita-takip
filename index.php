<?php
require_once 'functions.php';

$kayitlar = kayitlariGetir();

// Kayıt eklendikten sonra ekle.php'nin bıraktığı başarı mesajı (bir kez gösterilir)
$mesaj = '';
if (isset($_SESSION['mesaj'])) {
    $mesaj = $_SESSION['mesaj'];
    unset($_SESSION['mesaj']);
}

$sayfaBasligi = 'Ana Sayfa';
require 'header.php';
?>

<main>
    <section class="tanitim">
        <h2>Hoş geldin</h2>
        <p>
            Assetto Corsa için hazırladığım harita ve pist projelerinin
            durumunu takip etmek amacıyla geliştirilmiş basit bir PHP kayıt sistemidir.
        </p>
        <a class="buton" href="ekle.php">+ Yeni Kayıt Ekle</a>
    </section>

    <?php if ($mesaj !== ''): ?>
        <div class="mesaj basari"><?php echo htmlspecialchars($mesaj); ?></div>
    <?php endif; ?>

    <section class="ozet">
        <div class="kutu">
            <strong><?php echo count($kayitlar); ?></strong>
            <span>Toplam kayıt</span>
        </div>
        <div class="kutu">
            <strong><?php echo durumSay($kayitlar, 'Devam ediyor'); ?></strong>
            <span>Devam ediyor</span>
        </div>
        <div class="kutu">
            <strong><?php echo durumSay($kayitlar, 'Tamamlandı'); ?></strong>
            <span>Tamamlandı</span>
        </div>
    </section>

    <section id="kayitlar">
        <h2>Proje Kayıtları</h2>

        <?php if (count($kayitlar) === 0): ?>
            <p class="bos">Henüz kayıt bulunmuyor.</p>
        <?php else: ?>
            <div class="liste">
                <?php foreach ($kayitlar as $kayit): ?>
                    <article class="kart">
                        <h3><?php echo htmlspecialchars($kayit['baslik']); ?></h3>
                        <p class="kategori"><?php echo htmlspecialchars($kayit['kategori']); ?></p>
                        <p><?php echo htmlspecialchars($kayit['aciklama']); ?></p>
                        <span class="durum <?php echo durumSinifi($kayit['durum']); ?>">
                            <?php echo htmlspecialchars($kayit['durum']); ?>
                        </span>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require 'footer.php'; ?>
