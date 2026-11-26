<?php
require_once 'functions.php';

// Yalnızca giriş yapmış kullanıcılar; tüm sorgular bu kullanıcının id'si ile sınırlanır
$kullaniciId = girisGerekli();

// Arama, filtre ve sıralama değerleri adres satırından (GET) okunur
$kosul    = listeKosullariAl();
$kayitlar = kayitlariGetir($kullaniciId, $kosul);
$filtreVar = $kosul['q'] !== '' || $kosul['durum'] !== '' || $kosul['kategori'] !== '';

// Ekleme, güncelleme ya da silmeden sonra bırakılan bildirim (bir kez gösterilir)
$bildirim = bildirimAl();
$vurguId  = $bildirim['vurguId'] ?? 0;

// Özet sayıları (filtrelerden bağımsız, tüm kayıtlar)
$ozet      = kayitOzeti($kullaniciId);
$toplam    = $ozet['toplam'];
$planlanan = $ozet['Planlandı'];
$devam     = $ozet['Devam ediyor'];
$tamam     = $ozet['Tamamlandı'];
$gorunen   = count($kayitlar);
$oran      = $toplam > 0 ? (int) round($tamam / $toplam * 100) : 0;

$haritaKayitlari = haritaVerisi($kayitlar);

$sayfaBasligi  = 'Ana Sayfa';
$aktifSayfa    = 'ana';
$haritaGerekli = true;
require 'header.php';
?>

<main id="icerik">

    <?php require 'bildirim.php'; ?>

    <!-- Giriş (hero) bölümü -->
    <section class="hero kapsayici">
        <div class="hero-metin reveal">
            <span class="ust-etiket"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i> Niksar · Tokat</span>
            <h1>Assetto Corsa için <span class="vurgu">harita &amp; pist</span> projelerini takip et</h1>
            <p class="giris">
                Assetto Corsa için hazırladığım harita ve pist projelerinin
                durumunu takip etmek amacıyla geliştirilmiş PHP kayıt sistemi.
                Her çalışmayı kategori, durum ve haritadaki konumuyla kaydet.
            </p>
            <div class="hero-butonlar">
                <a class="buton birincil" href="ekle.php"><i class="bi bi-plus-lg" aria-hidden="true"></i> Yeni Kayıt Ekle</a>
                <a class="buton hayalet" href="#kayitlar">Kayıtları İncele <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>

            <ol class="is-akisi" aria-label="Çalışma akışı">
                <li><i class="bi bi-globe2" aria-hidden="true"></i><span>OpenStreetMap</span></li>
                <li><i class="bi bi-bezier2" aria-hidden="true"></i><span>Blender</span></li>
                <li><i class="bi bi-sliders" aria-hidden="true"></i><span>KSEditor</span></li>
                <li><i class="bi bi-flag-fill" aria-hidden="true"></i><span>Assetto Corsa</span></li>
            </ol>
        </div>

        <div class="hero-harita reveal">
            <div class="harita-kart">
                <div class="harita-baslik">
                    <span><i class="bi bi-pin-map-fill" aria-hidden="true"></i> Çalışma Bölgesi</span>
                    <small><?php echo count($haritaKayitlari); ?> konumlu kayıt</small>
                </div>
                <div id="harita" class="harita" role="region" aria-label="Konumlu kayıtların Niksar haritası"
                     data-merkez="40.5855,36.9555" data-zoom="14">
                    <noscript><p class="harita-not">Haritayı görmek için JavaScript gereklidir.</p></noscript>
                </div>
                <ul class="lejant" aria-label="Harita renkleri">
                    <li><span class="nokta plan"></span>Planlandı</li>
                    <li><span class="nokta devam"></span>Devam ediyor</li>
                    <li><span class="nokta tamam"></span>Tamamlandı</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Özet istatistikler -->
    <section class="istatistik kapsayici" aria-label="Özet">
        <div class="stat reveal">
            <i class="bi bi-layers stat-ikon" aria-hidden="true"></i>
            <strong data-sayi="<?php echo $toplam; ?>"><?php echo $toplam; ?></strong>
            <span>Toplam kayıt</span>
        </div>
        <div class="stat plan reveal">
            <i class="bi bi-calendar-event stat-ikon" aria-hidden="true"></i>
            <strong data-sayi="<?php echo $planlanan; ?>"><?php echo $planlanan; ?></strong>
            <span>Planlandı</span>
        </div>
        <div class="stat devam reveal">
            <i class="bi bi-hourglass-split stat-ikon" aria-hidden="true"></i>
            <strong data-sayi="<?php echo $devam; ?>"><?php echo $devam; ?></strong>
            <span>Devam ediyor</span>
        </div>
        <div class="stat tamam reveal">
            <i class="bi bi-check-circle-fill stat-ikon" aria-hidden="true"></i>
            <strong data-sayi="<?php echo $tamam; ?>"><?php echo $tamam; ?></strong>
            <span>Tamamlandı</span>
        </div>

        <div class="ilerleme reveal">
            <div class="ilerleme-ust">
                <span><i class="bi bi-speedometer2" aria-hidden="true"></i> Tamamlanma oranı</span>
                <strong>%<?php echo $oran; ?></strong>
            </div>
            <div class="ilerleme-cubuk" role="progressbar" aria-valuenow="<?php echo $oran; ?>"
                 aria-valuemin="0" aria-valuemax="100" aria-label="Tamamlanma oranı">
                <span style="--oran: <?php echo $oran; ?>%"></span>
            </div>
        </div>
    </section>

    <!-- Kayıt listesi -->
    <section id="kayitlar" class="kayitlar kapsayici">
        <div class="bolum-baslik">
            <div>
                <h2>Proje Kayıtları</h2>
                <p><?php echo $gorunen; ?> / <?php echo $toplam; ?> kayıt gösteriliyor</p>
            </div>

            <?php if ($toplam > 0): ?>
                <form class="arac-cubugu" method="get" action="index.php#kayitlar" role="search" data-filtre-formu>
                    <label class="arama">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <span class="gorunmez">Başlık veya açıklamada ara</span>
                        <input type="search" name="q" id="arama" placeholder="Kayıtlarda ara…" autocomplete="off"
                               maxlength="100" value="<?php echo e($kosul['q']); ?>">
                    </label>
                    <div class="filtreler" role="radiogroup" aria-label="Duruma göre filtrele">
                        <?php foreach (['' => 'Tümü', 'Planlandı' => 'Planlandı', 'Devam ediyor' => 'Devam', 'Tamamlandı' => 'Tamam'] as $deger => $etiket): ?>
                            <label class="filtre<?php echo $kosul['durum'] === $deger ? ' aktif' : ''; ?>">
                                <input type="radio" name="durum" value="<?php echo e($deger); ?>"
                                    <?php echo $kosul['durum'] === $deger ? 'checked' : ''; ?>>
                                <?php echo e($etiket); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <label class="secim">
                        <span class="gorunmez">Kategoriye göre filtrele</span>
                        <select name="kategori" id="kategori-filtre">
                            <option value="">Tüm kategoriler</option>
                            <?php foreach (kategoriListesi() as $kategori): ?>
                                <option value="<?php echo e($kategori); ?>" <?php echo $kosul['kategori'] === $kategori ? 'selected' : ''; ?>>
                                    <?php echo e($kategori); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="secim">
                        <span class="gorunmez">Sıralama</span>
                        <select name="sirala" id="sirala">
                            <?php foreach (siralamaListesi() as $anahtar => [$etiket]): ?>
                                <option value="<?php echo e($anahtar); ?>" <?php echo $kosul['sirala'] === $anahtar ? 'selected' : ''; ?>>
                                    <?php echo e($etiket); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button type="submit" class="buton hayalet kucuk-buton">
                        <i class="bi bi-funnel" aria-hidden="true"></i> Uygula
                    </button>
                    <?php if ($filtreVar || $kosul['sirala'] !== 'yeni'): ?>
                        <a class="metin-buton" href="index.php#kayitlar"><i class="bi bi-x-lg" aria-hidden="true"></i> Temizle</a>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </div>

        <?php if ($toplam === 0): ?>
            <div class="bos-durum">
                <i class="bi bi-inbox" aria-hidden="true"></i>
                <p>Henüz kayıt bulunmuyor.</p>
                <a class="buton birincil" href="ekle.php"><i class="bi bi-plus-lg" aria-hidden="true"></i> İlk kaydı ekle</a>
            </div>
        <?php elseif ($gorunen === 0): ?>
            <div class="bos-durum">
                <i class="bi bi-search" aria-hidden="true"></i>
                <p>Aramanıza uyan kayıt bulunamadı.</p>
                <a class="buton hayalet" href="index.php#kayitlar">Filtreleri temizle</a>
            </div>
        <?php else: ?>
            <div class="liste">
                <?php foreach ($kayitlar as $kayit):
                    $kat   = kategoriBilgi($kayit['kategori']);
                    $durum = durumBilgi($kayit['durum']);
                    $konumVar = ($kayit['enlem'] ?? null) !== null && ($kayit['boylam'] ?? null) !== null;
                    $yeni  = $kayit['id'] === $vurguId;
                ?>
                    <article class="kart reveal durum-<?php echo $durum['anahtar']; ?><?php echo $yeni ? ' yeni' : ''; ?>"
                             data-durum="<?php echo $durum['anahtar']; ?>"
                             data-kategori="<?php echo e($kayit['kategori']); ?>"
                             data-id="<?php echo (int) $kayit['id']; ?>">
                        <?php if ($yeni): ?>
                            <span class="yeni-rozet"><?php echo e($bildirim['etiket'] ?? 'Yeni'); ?></span>
                        <?php endif; ?>
                        <div class="kart-ust">
                            <span class="kategori-rozet <?php echo $kat['sinif']; ?>">
                                <i class="bi <?php echo $kat['ikon']; ?>" aria-hidden="true"></i>
                                <?php echo e($kayit['kategori']); ?>
                            </span>
                            <span class="durum <?php echo durumSinifi($kayit['durum']); ?>">
                                <i class="bi <?php echo $durum['ikon']; ?>" aria-hidden="true"></i>
                                <?php echo e($kayit['durum']); ?>
                            </span>
                        </div>
                        <h3><?php echo e($kayit['baslik']); ?></h3>
                        <p><?php echo e($kayit['aciklama']); ?></p>
                        <div class="kart-alt">
                            <span class="kart-no">#<?php echo str_pad((string) $kayit['id'], 2, '0', STR_PAD_LEFT); ?></span>
                            <?php if ($konumVar): ?>
                                <button type="button" class="konum-btn" data-konum="<?php echo (int) $kayit['id']; ?>">
                                    <i class="bi bi-geo-alt-fill" aria-hidden="true"></i> Haritada göster
                                </button>
                            <?php else: ?>
                                <span class="konum-yok"><i class="bi bi-geo-alt" aria-hidden="true"></i> Konum yok</span>
                            <?php endif; ?>
                        </div>
                        <div class="kart-islemler">
                            <a class="islem-btn" href="duzenle.php?id=<?php echo $kayit['id']; ?>">
                                <i class="bi bi-pencil" aria-hidden="true"></i> Düzenle
                            </a>
                            <form method="post" action="sil.php" class="sil-formu"
                                  data-onay="&quot;<?php echo e($kayit['baslik']); ?>&quot; kaydı kalıcı olarak silinsin mi?">
                                <?php echo csrfAlani(); ?>
                                <input type="hidden" name="id" value="<?php echo $kayit['id']; ?>">
                                <button type="submit" class="islem-btn tehlike">
                                    <i class="bi bi-trash3" aria-hidden="true"></i> Sil
                                </button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<script type="application/json" id="harita-verisi"><?php echo jsonGom($haritaKayitlari); ?></script>

<?php require 'footer.php'; ?>
