# Harita & Pist Takip Sistemi

İnternet Programcılığı I dersi için hazırlanan PHP proje ödevi.

Assetto Corsa için hazırladığım harita ve pist projelerinin (Blender modelleme, OpenStreetMap verisi aktarımı, KSEditor ayarları vb.) durumunu takip etmeye yarayan bir kayıt sistemidir. Kayıtlar **MySQL** veritabanında saklanır ve Niksar haritası üzerinde konumlarıyla birlikte gösterilir. Her kullanıcı kendi hesabıyla giriş yapar ve **yalnızca kendi kayıtlarını** görür, düzenler ve siler.

## Tanıtım Videosu

https://github.com/user-attachments/assets/f7a50011-ea37-4427-b079-0a87c5da200a

Video dosyası repoda da bulunur: [video/tanitim.mp4](video/tanitim.mp4)

> Video, vize öncesi sürümü (oturumda saklanan kayıtlar) gösterir. Vize sonrası eklenen veritabanı, giriş ve düzenleme/silme özellikleri aşağıda anlatılmıştır.

## Vize Sonrası Beş Haftalık Geliştirme

| Hafta | Görev | Yapılanlar |
|---|---|---|
| 1 | MySQL veritabanı ve kayıt tablosu | `kurulum.sql`: `harita_takip` veritabanı, `kayitlar` tablosu (ENUM kategori/durum, DECIMAL konum, tarih alanları, indeksler), örnek veriler |
| 2 | PDO ile bağlanma, ekleme ve listeleme | `baglanti.php` (PDO, hata modu, hazır ifadeler), `$_SESSION` yerine veritabanından listeleme ve `INSERT` ile ekleme |
| 3 | Kayıt güncelleme ve silme | `duzenle.php`, `sil.php`, ortak form `kayit-formu.php`; CSRF koruması, silmede onay, silme yalnızca POST ile |
| 4 | Arama, filtreleme ve sıralama | Başlık/açıklamada `LIKE` araması, durum ve kategori filtresi, 6 farklı sıralama; hepsi SQL sorgusunda ve adres satırında (GET) |
| 5 | Kullanıcı girişi, kayıt sahipliği, son testler | `kullanicilar` tablosu, `giris.php`, `kayit-ol.php`, `cikis.php`; tüm sorgular `kullanici_id` ile sınırlı; [TESTLER.md](TESTLER.md) |

Her haftanın değişiklikleri ayrı bir commit olarak depoda görülebilir.

## Özellikler

**Kullanıcı hesabı**
- Kayıt olma (ad soyad, kullanıcı adı, şifre + şifre tekrarı) ve giriş / çıkış
- Şifreler `password_hash()` ile özetlenerek saklanır, `password_verify()` ile kontrol edilir
- Giriş yapmadan kayıt sayfaları açılmaz; giriş sayfasına yönlendirilir
- Her kullanıcı yalnızca kendi kayıtlarını görür ve yönetir

**Kayıtlar**
- Ekleme, düzenleme ve silme (silmeden önce onay sorulur)
- Başlık ve açıklamada arama, duruma ve kategoriye göre filtreleme, sıralama (en yeni, en eski, son güncellenen, başlık A→Z / Z→A, duruma göre)
- Konumu olan kayıtları duruma göre renkli işaretlerle gösteren interaktif Niksar haritası (Leaflet + OpenStreetMap)
- Toplam / planlanan / devam eden / tamamlanan sayıları ve tamamlanma oranı
- İşlemden sonra köşede beliren bildirim ve eklenen / güncellenen kartın vurgulanması

**Kayıt formu**
- Kategori ve durum için ikonlu seçim kartları, karakter sayaçları
- Haritaya tıklayarak (ve işareti sürükleyerek) isteğe bağlı konum seçme
- Yazdıkça güncellenen canlı önizleme kartı
- Genel hata özeti ve her alanın altında kendi hata mesajı

**Arayüz**
- Koyu, motor sporları esintili "Pit Wall" tasarımı
- Mobil, tablet ve masaüstü uyumlu; klavye ile gezinme ve ARIA etiketleri
- Hareketi azaltmayı tercih eden kullanıcılar için animasyonlar kapanır

## Güvenlik: PDO, Oturum ve Kayıt Sahipliği Nasıl Çalışır?

### PDO bağlantısı (`baglanti.php`)
- `db()` fonksiyonu tek bir `PDO` nesnesi oluşturur ve sonraki çağrılarda aynı bağlantıyı döndürür.
- Bağlantı `charset=utf8mb4` ile açılır; Türkçe karakterler doğru saklanır.
- `PDO::ERRMODE_EXCEPTION`: SQL hatası olursa istisna fırlatılır, hata gözden kaçmaz.
- `PDO::ATTR_EMULATE_PREPARES => false`: sorgu ve veriler MySQL'e **ayrı ayrı** gönderilir. Kullanıcının yazdığı metin hiçbir zaman SQL komutunun parçası olmaz; bu yüzden SQL injection yapılamaz.
- Bağlantı hatasının ayrıntısı kullanıcıya gösterilmez, sunucu günlüğüne yazılır.
- Bütün sorgular `prepare()` + `execute([':parametre' => $deger])` ile çalışır. Sıralama (`ORDER BY`) parametre olarak gönderilemediği için kullanıcıdan gelen değer doğrudan yazılmaz; `siralamaListesi()` içindeki sabit ifadelerden biri seçilir.

### Kullanıcı oturumu (`functions.php`)
- **Giriş:** `girisYap()` kullanıcıyı kullanıcı adıyla bulur, şifreyi `password_verify()` ile özetle karşılaştırır. Doğruysa `session_regenerate_id(true)` ile oturum kimliği yenilenir (oturum sabitleme saldırısına karşı) ve `$_SESSION['kullanici']` içine kullanıcının `id`, kullanıcı adı ve adı yazılır.
- Yanlış girişte "Kullanıcı adı veya şifre hatalı" mesajı verilir; hangi alanın yanlış olduğu söylenmez.
- **Sayfa koruması:** Kayıtlarla ilgili her sayfa en başta `girisGerekli()` çağırır. Oturumda kullanıcı yoksa giriş sayfasına yönlendirilir; varsa kullanıcının `id`'si döndürülür.
- **Çıkış:** `cikis.php` yalnızca POST ile çalışır; oturum verisi ve çerezi silinir.
- Oturum çerezi `HttpOnly` (JavaScript okuyamaz) ve `SameSite=Lax` olarak ayarlanır.
- Veri değiştiren tüm formlar (ekle, düzenle, sil, giriş, kayıt ol, çıkış) oturuma özel bir **CSRF anahtarı** taşır; anahtar `hash_equals()` ile kontrol edilir.

### Kayıt sahipliği
- `kayitlar` tablosundaki `kullanici_id` sütunu kaydın sahibini gösterir (`kullanicilar.id`'ye yabancı anahtar).
- Kullanıcı id'si **her zaman oturumdan** alınır, formdan ya da adres satırından alınmaz. Böylece kullanıcı başka birinin id'sini gönderemez.
- Kayıtlarla ilgili her sorgu bu id ile sınırlıdır:
  - Listeleme / özet: `... WHERE kullanici_id = :kid`
  - Tek kayıt: `SELECT ... WHERE id = :id AND kullanici_id = :kid`
  - Güncelleme: `UPDATE ... WHERE id = :id AND kullanici_id = :kid`
  - Silme: `DELETE ... WHERE id = :id AND kullanici_id = :kid`
  - Ekleme: `INSERT` içine oturumdaki `kullanici_id` yazılır.
- Başka bir kullanıcının kaydının id'si adres satırına yazılsa bile sorgu sonuç döndürmez ve "kayıt bulunamadı" mesajı gösterilir. Kaydın var olup olmadığı da böylece belli olmaz.

## Kullanılan Teknolojiler

| Teknoloji | Kullanım |
|---|---|
| PHP 8 | Sayfalar, doğrulama, oturum yönetimi |
| MySQL / MariaDB + PDO | Kullanıcı ve kayıt verilerinin saklanması |
| HTML5, CSS3 | Yapı ve tasarım (CSS değişkenleri, Grid, Flexbox, animasyonlar) |
| JavaScript | Harita, önizleme, filtre formunun otomatik gönderimi, silme onayı (ek kütüphane olmadan) |
| [Leaflet 1.9.4](https://leafletjs.com/) | İnteraktif harita |
| [OpenStreetMap](https://www.openstreetmap.org/) | Harita altlığı |
| [Bootstrap Icons 1.13.2](https://icons.getbootstrap.com/) | İkonlar |
| [Inter](https://rsms.me/inter/) ve [Barlow Condensed](https://fonts.google.com/specimen/Barlow+Condensed) | Yazı tipleri |

Kütüphaneler ve yazı tipleri `assets/vendor` klasöründe yerel olarak bulunur. Yalnızca harita altlığı (OpenStreetMap görselleri) için internet bağlantısı gerekir.

## Dosya Yapısı

| Dosya | Açıklama |
|---|---|
| `kurulum.sql` | Veritabanı, tablolar ve örnek veriler |
| `baglanti.php` | PDO veritabanı bağlantısı |
| `functions.php` | Yardımcı fonksiyonlar: oturum, CSRF, kayıt sorguları (listeleme, ekleme, güncelleme, silme), doğrulama |
| `index.php` | Ana sayfa: harita, özet, arama/filtre/sıralama ve kayıt listesi |
| `ekle.php` | Yeni kayıt ekleme |
| `duzenle.php` | Kayıt düzenleme |
| `sil.php` | Kayıt silme (yalnızca POST) |
| `kayit-formu.php` | Ekleme ve düzenleme sayfalarının ortak formu |
| `giris.php` / `kayit-ol.php` / `cikis.php` | Giriş, kayıt olma ve çıkış |
| `header.php` / `footer.php` / `bildirim.php` | Ortak sayfa parçaları |
| `assets/style.css`, `assets/app.js` | Tasarım ve arayüz davranışları |
| `assets/vendor/` | Leaflet, Bootstrap Icons ve yazı tipleri (lisanslarıyla birlikte) |
| `TESTLER.md` | Yapılan testler ve sonuçları |

## Kurulum ve Çalıştırma

1. [XAMPP](https://www.apachefriends.org/) kurun.
2. Bu depoyu `C:\xampp\htdocs\harita-takip` klasörüne indirin.
3. XAMPP Control Panel'den **Apache** ve **MySQL**'i başlatın.
4. Veritabanını oluşturun (ikisinden biri):
   - `http://localhost/phpmyadmin` > **İçe Aktar** > `kurulum.sql` dosyasını seçip **Git**
   - ya da komut satırında: `C:\xampp\mysql\bin\mysql -u root < kurulum.sql`
5. Tarayıcıda `http://localhost/harita-takip/` adresini açın ve giriş yapın.

Veritabanı ayarları (`localhost`, `root`, boş şifre) XAMPP varsayılanlarıdır; farklıysa `baglanti.php` içinden değiştirilebilir.

### Örnek hesaplar

`kurulum.sql` deneme amaçlı iki kullanıcı oluşturur:

| Kullanıcı adı | Şifre | Kayıt sayısı |
|---|---|---|
| `demo` | `Demo1234` | 5 |
| `deneme` | `Deneme1234` | 2 |

İki hesapla ayrı ayrı giriş yapılarak her kullanıcının yalnızca kendi kayıtlarını gördüğü kontrol edilebilir. **Kayıt Ol** sayfasından yeni hesap da açılabilir.
