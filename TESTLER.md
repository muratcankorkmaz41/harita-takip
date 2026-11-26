# Testler

Bu dosyada projenin vize sonrası beş haftalık geliştirmesi için yapılan testler ve sonuçları yer alır.

**Test ortamı:** Windows 11, XAMPP (Apache 2.4, PHP 8.2.12, MariaDB 10.4.32), Chromium tabanlı tarayıcı
**Başlangıç durumu:** Her test turundan önce `kurulum.sql` yeniden içe aktarıldı (temiz veritabanı).
**Yöntem:** Testler hem tarayıcıda elle hem de form isteklerini HTTP üzerinden gönderen bir betikle yapıldı. Beklenen sonuçla gerçek sonuç karşılaştırıldı.

Örnek hesaplar: `demo` / `Demo1234` (5 kayıt), `deneme` / `Deneme1234` (2 kayıt).

**Sonuç: 69 testin 69'u geçti.**

---

## 1. Hafta: Veritabanı ve kayıt tablosu

| No | Test | Beklenen sonuç | Sonuç |
|---|---|---|---|
| 1.1 | `kurulum.sql` dosyasını içe aktar | `harita_takip` veritabanı, `kullanicilar` ve `kayitlar` tabloları oluşur, hata yok | ✅ Geçti |
| 1.2 | `kurulum.sql` dosyasını ikinci kez içe aktar | Tablolar silinip yeniden oluşur, hata yok | ✅ Geçti |
| 1.3 | Tablo yapısı (`SHOW CREATE TABLE kayitlar`) | ENUM kategori/durum, DECIMAL(9,6) konum, `utf8mb4_turkish_ci`, yabancı anahtar | ✅ Geçti |
| 1.4 | Örnek veriler | 2 kullanıcı, 7 kayıt (demo: 5, deneme: 2); Türkçe karakterler doğru | ✅ Geçti |

## 2. Hafta: PDO bağlantısı, ekleme ve listeleme

| No | Test | Beklenen sonuç | Sonuç |
|---|---|---|---|
| 2.1 | Ana sayfayı aç | Kullanıcının 5 kaydı listelenir, en yeni kayıt en üstte | ✅ Geçti |
| 2.2 | Geçerli bilgilerle kayıt ekle | Ana sayfaya yönlendirilir, "Kayıt başarıyla eklendi" bildirimi, yeni kart en üstte ve "Yeni" etiketli | ✅ Geçti |
| 2.3 | Başlık boş, açıklama 4 karakter | Kayıt eklenmez; "Başlık boş bırakılamaz", "en az 10 karakter" hataları | ✅ Geçti |
| 2.4 | Kategori "Uçak", durum "Bitti" gönder (form dışından) | "Geçersiz kategori", "Geçersiz durum" hataları | ✅ Geçti |
| 2.5 | Enlem 999, boylam "abc" | "Geçersiz konum seçildi" hatası | ✅ Geçti |
| 2.6 | Başlık: `x'); DROP TABLE kayitlar; --` | SQL çalışmaz, metin olduğu gibi kaydedilir, tablo yerinde | ✅ Geçti |
| 2.7 | Başlık: `<script>alert(1)</script>` | Kod çalışmaz, sayfada `&lt;script&gt;` olarak görünür | ✅ Geçti |
| 2.8 | Başlığı dizi olarak gönder (`baslik[]=a&baslik[]=b`) | PHP hatası olmaz, "Başlık boş bırakılamaz" hatası | ✅ Geçti |
| 2.9 | Formu yanlış CSRF anahtarıyla gönder | 400 hatası, kayıt eklenmez | ✅ Geçti |
| 2.10 | MySQL kapalıyken sayfayı aç | Ayrıntı göstermeyen "Veritabanına bağlanılamadı" mesajı | ✅ Geçti |

## 3. Hafta: Güncelleme ve silme

| No | Test | Beklenen sonuç | Sonuç |
|---|---|---|---|
| 3.1 | Karttaki **Düzenle** butonu | Form kaydın mevcut değerleriyle (başlık, kategori, durum, konum) dolu açılır | ✅ Geçti |
| 3.2 | Başlığı ve durumu değiştir, konumu temizle, kaydet | "Kayıt güncellendi" bildirimi, kart "Güncellendi" etiketiyle vurgulanır, konum NULL olur | ✅ Geçti |
| 3.3 | Düzenlerken başlığı 2 karaktere indir | Kayıt değişmez, "Kayıt güncellenemedi" ve alan hatası | ✅ Geçti |
| 3.4 | `duzenle.php?id=9999` | Ana sayfaya yönlendirilir, "kayıt bulunamadı" bildirimi | ✅ Geçti |
| 3.5 | `duzenle.php?id=abc` | Ana sayfaya yönlendirilir, "kayıt bulunamadı" bildirimi | ✅ Geçti |
| 3.6 | `sil.php?id=3` adresini tarayıcıda aç (GET) | 405 hatası, kayıt silinmez | ✅ Geçti |
| 3.7 | Karttaki **Sil** butonu, onay penceresinde **Tamam** | "Kayıt silindi" bildirimi, kart listeden kalkar | ✅ Geçti |
| 3.8 | Onay penceresinde **İptal** | Form gönderilmez, kayıt yerinde | ✅ Geçti |
| 3.9 | Silinmiş kaydı tekrar silmeye çalış | "Silinmek istenen kayıt bulunamadı" bildirimi | ✅ Geçti |
| 3.10 | Silme isteğini CSRF anahtarı olmadan gönder | 400 hatası, kayıt silinmez | ✅ Geçti |

## 4. Hafta: Arama, filtreleme ve sıralama

| No | Test | Beklenen sonuç | Sonuç |
|---|---|---|---|
| 4.1 | "pist" ara | Başlığında "pist" geçen 2 kayıt | ✅ Geçti |
| 4.2 | "blender" ara | Açıklamasında "Blender" geçen kayıt da bulunur | ✅ Geçti |
| 4.3 | "PİST" ara (Türkçe büyük İ) | Küçük harfle aramayla aynı 2 kayıt | ✅ Geçti |
| 4.4 | Durum: Planlandı | Yalnızca planlanan kayıtlar | ✅ Geçti |
| 4.5 | Kategori: Pist + Durum: Devam ediyor | İki koşulu da sağlayan tek kayıt | ✅ Geçti |
| 4.6 | Sıralama: Başlık (A → Z) | Kayıtlar Türkçe alfabe sırasıyla | ✅ Geçti |
| 4.7 | Sıralama: En eski | İlk eklenen kayıt en üstte | ✅ Geçti |
| 4.8 | Sıralama: Duruma göre | Planlandı → Devam ediyor → Tamamlandı | ✅ Geçti |
| 4.9 | "%" ara | Joker karakter gibi davranmaz, sonuç yok | ✅ Geçti |
| 4.10 | Olmayan bir kelime ara | "Aramanıza uyan kayıt bulunamadı" mesajı, arama kutusunda yazılan kelime kalır | ✅ Geçti |
| 4.11 | Adres satırına `durum=x&kategori=' OR 1=1 --&sirala=id; DROP TABLE kayitlar` yaz | Geçersiz değerler yok sayılır, tüm kayıtlar varsayılan sırayla listelenir | ✅ Geçti |
| 4.12 | `index.php?q[]=a&sirala[]=b` | PHP hatası olmaz | ✅ Geçti |
| 4.13 | Filtre uygulanmış sayfayı yenile | Seçilen kategori ve sıralama formda seçili kalır, sayaç "2 / 5 kayıt" | ✅ Geçti |
| 4.14 | Açılır listeden seçim yap (tarayıcı) | Form kendiliğinden gönderilir, liste ve harita güncellenir | ✅ Geçti |

## 5. Hafta: Kullanıcı girişi ve kayıt sahipliği

### Giriş, kayıt olma ve çıkış

| No | Test | Beklenen sonuç | Sonuç |
|---|---|---|---|
| 5.1 | Giriş yapmadan `index.php`, `ekle.php`, `duzenle.php?id=1` aç | Giriş sayfasına yönlendirilir, "giriş yapmalısınız" bildirimi | ✅ Geçti |
| 5.2 | Giriş yapmadan `sil.php`'ye POST gönder | Giriş sayfasına yönlendirilir, kayıt silinmez | ✅ Geçti |
| 5.3 | `demo` + yanlış şifre | "Kullanıcı adı veya şifre hatalı" | ✅ Geçti |
| 5.4 | Olmayan kullanıcı adı | Aynı mesaj (kullanıcı adının var olup olmadığı belli olmaz) | ✅ Geçti |
| 5.5 | Kullanıcı adı ve şifre: `demo' OR '1'='1` | Giriş yapılamaz | ✅ Geçti |
| 5.6 | Giriş formunu CSRF anahtarı olmadan gönder | 400 hatası | ✅ Geçti |
| 5.7 | `DEMO` / `Demo1234` ile giriş | Giriş yapılır ("Hoş geldiniz, Demo Kullanıcı!"), menüde kullanıcı adı ve Çıkış görünür | ✅ Geçti |
| 5.8 | Giriş öncesi ve sonrası oturum çerezi | Oturum kimliği değişir (`session_regenerate_id`) | ✅ Geçti |
| 5.9 | Giriş yapmışken `giris.php` aç | Ana sayfaya yönlendirilir | ✅ Geçti |
| 5.10 | `cikis.php` adresini tarayıcıda aç (GET) | Çıkış yapılmaz | ✅ Geçti |
| 5.11 | Menüdeki **Çıkış** butonu | "Oturumunuz kapatıldı", giriş sayfası | ✅ Geçti |
| 5.12 | Çıkıştan sonra `index.php` aç | Giriş sayfasına yönlendirilir | ✅ Geçti |
| 5.13 | Kayıt ol: ad "A", kullanıcı adı "Demo!", şifre "kisa" | Üç alan için ayrı hata mesajı | ✅ Geçti |
| 5.14 | Kayıt ol: kullanıcı adı `demo` | "Bu kullanıcı adı alınmış" | ✅ Geçti |
| 5.15 | Kayıt ol: şifre ve tekrar farklı | "Şifreler birbiriyle aynı değil" | ✅ Geçti |
| 5.16 | Geçerli bilgilerle kayıt ol | Hesap oluşur, otomatik giriş yapılır, boş liste ("Henüz kayıt bulunmuyor") | ✅ Geçti |
| 5.17 | Veritabanında `sifre_hash` sütunu | Şifreler düz metin değil, `$2y$` ile başlayan bcrypt özeti | ✅ Geçti |

### Kayıt sahipliği

| No | Test | Beklenen sonuç | Sonuç |
|---|---|---|---|
| 5.18 | `demo` ile ana sayfa | Yalnızca kendi 5 kaydı ve özette 5 kayıt; `deneme`'nin kayıtları görünmez | ✅ Geçti |
| 5.19 | `demo` ile "Kalesi" ara (`deneme`'nin kaydında geçen kelime) | Sonuç yok | ✅ Geçti |
| 5.20 | `demo` ile `duzenle.php?id=6` (`deneme`'nin kaydı) | Form açılmaz, "kayıt bulunamadı" | ✅ Geçti |
| 5.21 | `demo` ile id 6 için düzenleme formunu POST et | Kayıt değişmez, "kayıt bulunamadı" | ✅ Geçti |
| 5.22 | `demo` ile id 6 için silme isteği gönder | Kayıt silinmez, "kayıt bulunamadı" | ✅ Geçti |
| 5.23 | `demo` yeni kayıt ekler | Kayıt `kullanici_id = 1` ile eklenir, listede 6 kayıt | ✅ Geçti |
| 5.24 | `deneme` ile ana sayfa | Yalnızca kendi 2 kaydı; `demo`'nun yeni kaydı görünmez | ✅ Geçti |
| 5.25 | `deneme`, `demo`'nun yeni kaydını düzenlemeye çalışır | "kayıt bulunamadı" | ✅ Geçti |
| 5.26 | `deneme`, `demo`'nun yeni kaydını silmeye çalışır | Kayıt silinmez | ✅ Geçti |
| 5.27 | `deneme` oturumunda `demo`'nun CSRF anahtarıyla istek gönder | 400 hatası | ✅ Geçti |

### Arayüz

| No | Test | Beklenen sonuç | Sonuç |
|---|---|---|---|
| 5.28 | Masaüstü görünüm (giriş, liste, düzenleme sayfaları) | Düzgün hizalı, kart butonları ve filtre çubuğu görünür | ✅ Geçti |
| 5.29 | Mobil görünüm (375 px) | Yatay kaydırma yok, menü yalnızca ikonlarla, filtreler alt alta | ✅ Geçti |
| 5.30 | Tarayıcı konsolu | JavaScript hatası yok | ✅ Geçti |
| 5.31 | Apache hata günlüğü (tüm testlerden sonra) | PHP uyarısı / hatası yok | ✅ Geçti |
