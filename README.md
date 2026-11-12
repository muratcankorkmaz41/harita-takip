# Harita & Pist Takip Sistemi

İnternet Programcılığı I dersi için hazırlanan PHP proje ödevi.

Assetto Corsa için hazırladığım harita ve pist projelerinin (Blender modelleme, OpenStreetMap verisi aktarımı, KSEditor ayarları vb.) durumunu takip etmeye yarayan basit bir kayıt sistemidir.

## Tanıtım Videosu

https://github.com/user-attachments/assets/2138949a-3787-4254-ac9e-c9ac3b6139aa

Video dosyası repoda da bulunur: [video/tanitim.mp4](video/tanitim.mp4)

## Özellikler

- Proje kayıtlarını kart görünümünde listeleme
- Toplam, devam eden ve tamamlanan kayıt sayılarını gösteren özet kutuları
- Form ile yeni kayıt ekleme (başlık, kategori, durum, açıklama)
- Sunucu tarafında form doğrulama ve hata mesajları
- Tarayıcı tarafında doğrulama (`required`, `minlength`, `maxlength`)
- Kayıt sonrası yönlendirme (Post/Redirect/Get) ve tek seferlik başarı mesajı
- `htmlspecialchars` ile XSS koruması
- Duruma göre renklendirilmiş etiketler
- Mobil uyumlu tasarım

## Kullanılan Teknolojiler

- PHP 8 (oturum / `$_SESSION` ile veri saklama)
- HTML5, CSS3
- XAMPP (Apache)

## Dosya Yapısı

| Dosya | Açıklama |
|---|---|
| `index.php` | Ana sayfa: özet kutuları ve kayıt listesi |
| `ekle.php` | Yeni kayıt ekleme formu ve doğrulama |
| `functions.php` | Yardımcı fonksiyonlar (kayıt getirme, ekleme, doğrulama) |
| `header.php` / `footer.php` | Ortak sayfa üst ve alt bölümleri |
| `assets/style.css` | Site stilleri |

## Kurulum ve Çalıştırma

1. [XAMPP](https://www.apachefriends.org/) kurun.
2. Bu depoyu `C:\xampp\htdocs\harita-takip` klasörüne indirin.
3. XAMPP Control Panel'den **Apache**'yi başlatın.
4. Tarayıcıda `http://localhost/harita-takip/` adresini açın.

> Not: Kayıtlar oturumda (`$_SESSION`) tutulur. Tarayıcı kapatılınca eklenen kayıtlar silinir ve örnek kayıtlarla yeniden başlanır.
