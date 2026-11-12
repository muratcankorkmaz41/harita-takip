<?php
require_once 'functions.php';

$hatalar = [];

// Form ilk açıldığında gösterilecek varsayılan değerler
$veri = [
    'baslik'   => '',
    'kategori' => '',
    'durum'    => 'Planlandı',
    'aciklama' => '',
    'enlem'    => '',
    'boylam'   => '',
];

// Sayfa form gönderilerek (POST) açıldıysa verileri işle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // postAl() metin olmayan (ör. dizi) değerleri boş sayar ve boşlukları temizler
    $veri = [
        'baslik'   => postAl('baslik'),
        'kategori' => postAl('kategori'),
        'durum'    => postAl('durum'),
        'aciklama' => postAl('aciklama'),
        'enlem'    => postAl('enlem'),
        'boylam'   => postAl('boylam'),
    ];

    $hatalar = kayitDogrula($veri);

    // Hata yoksa kaydet ve ana sayfaya yönlendir
    if (count($hatalar) === 0) {
        $yeniId = kayitEkle($veri);
        $_SESSION['mesaj']  = 'Kayıt başarıyla eklendi: ' . $veri['baslik'];
        $_SESSION['yeniId'] = $yeniId;
        header('Location: index.php#kayitlar');
        exit;
    }
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

// Önizleme kartı için başlangıç değerleri
$onKat   = kategoriBilgi($veri['kategori']);
$onDurum = durumBilgi($veri['durum']);

$sayfaBasligi  = 'Yeni Kayıt';
$aktifSayfa    = 'ekle';
$haritaGerekli = true;
require 'header.php';
?>

<main id="icerik" class="kapsayici sayfa-ekle">
    <div class="sayfa-baslik reveal">
        <a class="geri" href="index.php"><i class="bi bi-arrow-left" aria-hidden="true"></i> Ana sayfa</a>
        <h1>Yeni Kayıt Ekle</h1>
        <p>Yeni bir harita veya pist çalışması eklemek için formu doldurun. Konum dışındaki tüm alanlar zorunludur.</p>
    </div>

    <?php if (count($hatalar) > 0): ?>
        <div class="uyari hata reveal" role="alert">
            <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
            <div>
                <strong>Kayıt eklenemedi</strong>
                <ul>
                    <?php foreach ($hatalar as $hata): ?>
                        <li><?php echo e($hata); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <form class="ekle-izgara" method="post" action="ekle.php" id="kayit-formu">
        <section class="form-kart reveal" aria-labelledby="adim-1">
            <h2 class="adim" id="adim-1"><span>1</span> Proje bilgileri</h2>

            <div class="alan <?php echo isset($hatalar['baslik']) ? 'hatali' : ''; ?>">
                <div class="alan-ust">
                    <label for="baslik">Başlık</label>
                    <span class="sayac" data-sayac-for="baslik">0 / 80</span>
                </div>
                <div class="girdi-ikon">
                    <i class="bi bi-pencil-square" aria-hidden="true"></i>
                    <input type="text" id="baslik" name="baslik" required minlength="3" maxlength="80"
                           placeholder="Örn. Niksar Kalesi Yolu Pisti"
                           value="<?php echo e($veri['baslik']); ?>" <?php echo hataOznitelik($hatalar, 'baslik'); ?>>
                </div>
                <?php echo hataMesaji($hatalar, 'baslik'); ?>
            </div>

            <fieldset class="alan <?php echo isset($hatalar['kategori']) ? 'hatali' : ''; ?>"
                <?php echo isset($hatalar['kategori']) ? 'aria-describedby="kategori-hata"' : ''; ?>>
                <legend>Kategori</legend>
                <div class="secenekler dortlu">
                    <?php foreach (kategoriListesi() as $i => $kategori):
                        $kb = kategoriBilgi($kategori); ?>
                        <label class="secenek <?php echo $kb['sinif']; ?>">
                            <input type="radio" name="kategori" value="<?php echo e($kategori); ?>"
                                   data-ikon="<?php echo $kb['ikon']; ?>" data-sinif="<?php echo $kb['sinif']; ?>"
                                <?php echo $i === 0 ? 'required' : ''; ?>
                                <?php echo $veri['kategori'] === $kategori ? 'checked' : ''; ?>>
                            <span><i class="bi <?php echo $kb['ikon']; ?>" aria-hidden="true"></i><?php echo e($kategori); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?php echo hataMesaji($hatalar, 'kategori'); ?>
            </fieldset>

            <fieldset class="alan <?php echo isset($hatalar['durum']) ? 'hatali' : ''; ?>"
                <?php echo isset($hatalar['durum']) ? 'aria-describedby="durum-hata"' : ''; ?>>
                <legend>Durum</legend>
                <div class="secenekler uclu">
                    <?php foreach (durumListesi() as $durum):
                        $db = durumBilgi($durum); ?>
                        <label class="secenek durum-secenek <?php echo $db['anahtar']; ?>">
                            <input type="radio" name="durum" value="<?php echo e($durum); ?>"
                                   data-ikon="<?php echo $db['ikon']; ?>" data-anahtar="<?php echo $db['anahtar']; ?>"
                                <?php echo $veri['durum'] === $durum ? 'checked' : ''; ?>>
                            <span><i class="bi <?php echo $db['ikon']; ?>" aria-hidden="true"></i><?php echo e($durum); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?php echo hataMesaji($hatalar, 'durum'); ?>
            </fieldset>

            <div class="alan <?php echo isset($hatalar['aciklama']) ? 'hatali' : ''; ?>">
                <div class="alan-ust">
                    <label for="aciklama">Açıklama</label>
                    <span class="sayac" data-sayac-for="aciklama">0 / 300</span>
                </div>
                <textarea id="aciklama" name="aciklama" rows="5" required minlength="10" maxlength="300"
                          placeholder="Bu çalışmada neler yapılacak? (en az 10 karakter)"
                          <?php echo hataOznitelik($hatalar, 'aciklama'); ?>><?php echo e($veri['aciklama']); ?></textarea>
                <?php echo hataMesaji($hatalar, 'aciklama'); ?>
            </div>

            <div class="form-butonlar">
                <button type="submit" class="buton birincil"><i class="bi bi-check2" aria-hidden="true"></i> Kaydet</button>
                <a class="buton hayalet" href="index.php">İptal</a>
            </div>
        </section>

        <aside class="yan">
            <section class="form-kart reveal" aria-labelledby="adim-2">
                <h2 class="adim" id="adim-2"><span>2</span> Konum <small>isteğe bağlı</small></h2>
                <p class="ipucu"><i class="bi bi-cursor-fill" aria-hidden="true"></i> Çalışmanın yerini işaretlemek için haritaya tıklayın.</p>
                <div id="konum-harita" class="harita kucuk" role="application"
                     aria-label="Konum seçmek için haritaya tıklayın"
                     data-merkez="40.5855,36.9555" data-zoom="14"></div>
                <div class="konum-bilgi <?php echo isset($hatalar['konum']) ? 'hatali' : ''; ?>">
                    <span data-konum-metin>
                        <?php if ($veri['enlem'] !== '' && $veri['boylam'] !== ''): ?>
                            <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                            <?php echo e($veri['enlem']); ?>, <?php echo e($veri['boylam']); ?>
                        <?php else: ?>
                            <i class="bi bi-geo-alt" aria-hidden="true"></i> Konum seçilmedi
                        <?php endif; ?>
                    </span>
                    <button type="button" class="metin-buton" data-konum-temizle>
                        <i class="bi bi-x-lg" aria-hidden="true"></i> Temizle
                    </button>
                </div>
                <?php echo hataMesaji($hatalar, 'konum'); ?>
                <input type="hidden" name="enlem" id="enlem" value="<?php echo e($veri['enlem']); ?>">
                <input type="hidden" name="boylam" id="boylam" value="<?php echo e($veri['boylam']); ?>">
            </section>

            <section class="onizleme reveal" aria-labelledby="onizleme-baslik">
                <h2 class="adim" id="onizleme-baslik"><span><i class="bi bi-eye" aria-hidden="true"></i></span> Önizleme</h2>
                <article class="kart durum-<?php echo $onDurum['anahtar']; ?>" data-onizleme aria-live="polite">
                    <div class="kart-ust">
                        <span class="kategori-rozet <?php echo $onKat['sinif']; ?>" data-on-kategori>
                            <i class="bi <?php echo $onKat['ikon']; ?>" aria-hidden="true"></i>
                            <span><?php echo $veri['kategori'] !== '' ? e($veri['kategori']) : 'Kategori'; ?></span>
                        </span>
                        <span class="durum <?php echo durumSinifi($veri['durum']); ?>" data-on-durum>
                            <i class="bi <?php echo $onDurum['ikon']; ?>" aria-hidden="true"></i>
                            <span><?php echo e($veri['durum']); ?></span>
                        </span>
                    </div>
                    <h3 data-on-baslik><?php echo $veri['baslik'] !== '' ? e($veri['baslik']) : 'Kayıt başlığı'; ?></h3>
                    <p data-on-aciklama><?php echo $veri['aciklama'] !== '' ? e($veri['aciklama']) : 'Açıklama burada görünecek.'; ?></p>
                    <div class="kart-alt">
                        <span class="kart-no">#Yeni</span>
                        <span class="konum-yok" data-on-konum><i class="bi bi-geo-alt" aria-hidden="true"></i> Konum yok</span>
                    </div>
                </article>
            </section>
        </aside>
    </form>
</main>

<?php require 'footer.php'; ?>
