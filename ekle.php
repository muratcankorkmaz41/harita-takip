<?php
require_once 'functions.php';

$hatalar = [];

// Form ilk açıldığında gösterilecek varsayılan değerler
$veri = [
    'baslik'   => '',
    'kategori' => '',
    'durum'    => 'Planlandı',
    'aciklama' => '',
];

// Sayfa form gönderilerek (POST) açıldıysa verileri işle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // postAl() metin olmayan (ör. dizi) değerleri boş sayar ve boşlukları temizler
    $veri = [
        'baslik'   => postAl('baslik'),
        'kategori' => postAl('kategori'),
        'durum'    => postAl('durum'),
        'aciklama' => postAl('aciklama'),
    ];

    $hatalar = kayitDogrula($veri);

    // Hata yoksa kaydet ve ana sayfaya yönlendir
    if (count($hatalar) === 0) {
        kayitEkle($veri);
        $_SESSION['mesaj'] = 'Kayıt başarıyla eklendi: ' . $veri['baslik'];
        header('Location: index.php');
        exit;
    }
}

$sayfaBasligi = 'Yeni Kayıt';
require 'header.php';
?>

<main>
    <section class="tanitim">
        <h2>Yeni Kayıt Ekle</h2>
        <p>Yeni bir harita veya pist çalışması eklemek için formu doldurun. Tüm alanlar zorunludur.</p>
    </section>

    <?php if (count($hatalar) > 0): ?>
        <div class="mesaj hata">
            <strong>Kayıt eklenemedi:</strong>
            <ul>
                <?php foreach ($hatalar as $hata): ?>
                    <li><?php echo htmlspecialchars($hata); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form class="form" method="post" action="ekle.php">
        <label for="baslik">Başlık</label>
        <input type="text" id="baslik" name="baslik" required minlength="3" maxlength="80"
               value="<?php echo htmlspecialchars($veri['baslik']); ?>">

        <label for="kategori">Kategori</label>
        <select id="kategori" name="kategori" required>
            <option value="">-- Seçiniz --</option>
            <?php foreach (kategoriListesi() as $kategori): ?>
                <option value="<?php echo htmlspecialchars($kategori); ?>"
                    <?php if ($veri['kategori'] === $kategori) { echo 'selected'; } ?>>
                    <?php echo htmlspecialchars($kategori); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="durum">Durum</label>
        <select id="durum" name="durum">
            <?php foreach (durumListesi() as $durum): ?>
                <option value="<?php echo htmlspecialchars($durum); ?>"
                    <?php if ($veri['durum'] === $durum) { echo 'selected'; } ?>>
                    <?php echo htmlspecialchars($durum); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="aciklama">Açıklama</label>
        <textarea id="aciklama" name="aciklama" rows="4" required minlength="10" maxlength="300"><?php echo htmlspecialchars($veri['aciklama']); ?></textarea>

        <div class="form-butonlar">
            <button type="submit" class="buton">Kaydet</button>
            <a class="buton ikincil" href="index.php">İptal</a>
        </div>
    </form>
</main>

<?php require 'footer.php'; ?>
