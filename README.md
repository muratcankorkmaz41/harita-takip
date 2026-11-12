# Harita & Pist Takip Sistemi

İnternet Programcılığı I dersi için hazırlanan PHP proje ödevi.

Assetto Corsa için hazırladığım harita ve pist projelerinin (Blender modelleme, OpenStreetMap verisi aktarımı, KSEditor ayarları vb.) durumunu takip etmeye yarayan bir kayıt sistemidir. Kayıtlar Niksar haritası üzerinde konumlarıyla birlikte gösterilir.

## Tanıtım Videosu

https://github.com/user-attachments/assets/2138949a-3787-4254-ac9e-c9ac3b6139aa

Video dosyası repoda da bulunur: [video/tanitim.mp4](video/tanitim.mp4)

## Özellikler

**Ana sayfa**
- Koyu, motor sporları esintili "Pit Wall" arayüzü (yapışkan menü, cam efekti, ızgara arka plan)
- Konumu olan kayıtları duruma göre renkli işaretlerle gösteren interaktif Niksar haritası (Leaflet + OpenStreetMap)
- Kartlardaki **Haritada göster** butonu haritayı kayda uçurur; haritadaki işaretten **Karta git** ile karta dönülür
- Toplam / planlanan / devam eden / tamamlanan sayıları ve tamamlanma oranı çubuğu (sayarak artan animasyon)
- Kayıtlarda anlık arama (Türkçe büyük-küçük harf kurallarıyla), duruma ve kategoriye göre filtreleme
- Kayıt eklendikten sonra köşede kaybolan başarı bildirimi ve yeni kartın vurgulanması

**Kayıt ekleme**
- Kategori ve durum için ikonlu seçim kartları
- Başlık ve açıklama için karakter sayacı
- Haritaya tıklayarak (ve işareti sürükleyerek) isteğe bağlı konum seçme
- Yazdıkça güncellenen canlı önizleme kartı
- Hem genel hata özeti hem de her alanın altında kendi hata mesajı; alan düzeltilince hata kaybolur

**Güvenlik ve doğrulama**
- Sunucu tarafında doğrulama (uzunluk, izinli kategori/durum listesi, konum aralığı)
- Tarayıcı tarafında doğrulama (`required`, `minlength`, `maxlength`)
- `htmlspecialchars` ile XSS koruması; harita verisi JSON olarak güvenli şekilde gömülür
- Dizi olarak gönderilen form alanlarına karşı koruma (`postAl()`)
- Kayıt sonrası yönlendirme (Post/Redirect/Get)

**Erişilebilirlik ve uyumluluk**
- Mobil, tablet ve masaüstü uyumlu tasarım
- Klavye ile gezinme, "İçeriğe geç" bağlantısı, ARIA etiketleri
- Hareketi azaltmayı tercih eden kullanıcılar için animasyonlar kapanır

## Kullanılan Teknolojiler

| Teknoloji | Kullanım |
|---|---|
| PHP 8 | Sayfalar, doğrulama, oturum (`$_SESSION`) ile veri saklama |
| HTML5, CSS3 | Yapı ve tasarım (CSS değişkenleri, Grid, Flexbox, animasyonlar) |
| JavaScript | Harita, filtreler, önizleme, animasyonlar (ek kütüphane olmadan) |
| [Leaflet 1.9.4](https://leafletjs.com/) | İnteraktif harita |
| [OpenStreetMap](https://www.openstreetmap.org/) | Harita altlığı |
| [Bootstrap Icons 1.13.2](https://icons.getbootstrap.com/) | İkonlar |
| [Inter](https://rsms.me/inter/) ve [Barlow Condensed](https://fonts.google.com/specimen/Barlow+Condensed) | Yazı tipleri |

Kütüphaneler ve yazı tipleri `assets/vendor` klasöründe yerel olarak bulunur, bu yüzden site internet olmadan da açılır. Yalnızca harita altlığı (OpenStreetMap görselleri) için internet bağlantısı gerekir.

## Dosya Yapısı

| Dosya | Açıklama |
|---|---|
| `index.php` | Ana sayfa: harita, özet, arama/filtre ve kayıt listesi |
| `ekle.php` | Yeni kayıt formu, konum seçici, önizleme ve doğrulama |
| `functions.php` | Yardımcı fonksiyonlar (kayıt getirme, ekleme, doğrulama, ikon/renk bilgisi) |
| `header.php` / `footer.php` | Ortak sayfa üst ve alt bölümleri |
| `assets/style.css` | Site tasarımı |
| `assets/app.js` | Arayüz davranışları |
| `assets/vendor/` | Leaflet, Bootstrap Icons ve yazı tipleri (lisanslarıyla birlikte) |

## Kurulum ve Çalıştırma

1. [XAMPP](https://www.apachefriends.org/) kurun.
2. Bu depoyu `C:\xampp\htdocs\harita-takip` klasörüne indirin.
3. XAMPP Control Panel'den **Apache**'yi başlatın.
4. Tarayıcıda `http://localhost/harita-takip/` adresini açın.

> Not: Kayıtlar oturumda (`$_SESSION`) tutulur. Tarayıcı kapatılınca eklenen kayıtlar silinir ve örnek kayıtlarla yeniden başlanır.
