-- ==========================================================================
-- Harita & Pist Takip Sistemi - veritabanı kurulum dosyası
-- phpMyAdmin > İçe Aktar ile ya da komut satırından çalıştırılabilir:
--   mysql -u root < kurulum.sql
--
-- Örnek kullanıcılar (yalnızca deneme amaçlı):
--   kullanıcı adı: demo    şifre: Demo1234
--   kullanıcı adı: deneme  şifre: Deneme1234
-- ==========================================================================

CREATE DATABASE IF NOT EXISTS harita_takip
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_turkish_ci;

USE harita_takip;

-- Yeniden kurulumda eski tabloları kaldır (önce yabancı anahtarı olan tablo)
DROP TABLE IF EXISTS kayitlar;
DROP TABLE IF EXISTS kullanicilar;

-- --------------------------------------------------------------------------
-- Kullanıcı tablosu: şifreler password_hash() ile özetlenmiş olarak saklanır
-- --------------------------------------------------------------------------
CREATE TABLE kullanicilar (
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    kullanici_adi      VARCHAR(30)  NOT NULL,
    ad_soyad           VARCHAR(60)  NOT NULL,
    sifre_hash         VARCHAR(255) NOT NULL,
    olusturma_tarihi   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_kullanici_adi (kullanici_adi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------------------------
-- Kayıt tablosu: her satır bir harita / pist çalışmasıdır.
-- kullanici_id kaydın sahibini gösterir; kullanıcı silinirse kayıtları da silinir.
-- --------------------------------------------------------------------------
CREATE TABLE kayitlar (
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    kullanici_id       INT UNSIGNED NOT NULL,
    baslik             VARCHAR(80)  NOT NULL,
    kategori           ENUM('Harita', 'Pist', 'Veri', 'Araç') NOT NULL,
    durum              ENUM('Planlandı', 'Devam ediyor', 'Tamamlandı') NOT NULL DEFAULT 'Planlandı',
    aciklama           VARCHAR(300) NOT NULL,
    enlem              DECIMAL(9,6) NULL DEFAULT NULL,   -- konum isteğe bağlı
    boylam             DECIMAL(9,6) NULL DEFAULT NULL,
    olusturma_tarihi   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    guncelleme_tarihi  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_kullanici (kullanici_id),
    KEY idx_durum (durum),
    KEY idx_kategori (kategori),
    CONSTRAINT fk_kayit_kullanici FOREIGN KEY (kullanici_id)
        REFERENCES kullanicilar (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------------------------
-- Örnek kullanıcılar
-- --------------------------------------------------------------------------
INSERT INTO kullanicilar (id, kullanici_adi, ad_soyad, sifre_hash) VALUES
(1, 'demo',   'Demo Kullanıcı',   '$2y$10$MKXBXkg34.16bw1X5WYGNukJ2zAYGkaPR5XOKMI7XA7GPOqZWTQFW'),  -- Demo1234
(2, 'deneme', 'Deneme Kullanıcı', '$2y$10$aw50g4HZZEAKj8tuV9QfeOuBXI7QD3tYCzDOZjaXcKNFh4Q3dRhHq'); -- Deneme1234

-- --------------------------------------------------------------------------
-- Örnek kayıtlar (1-5 demo kullanıcısına, 6-7 deneme kullanıcısına ait)
-- --------------------------------------------------------------------------
INSERT INTO kayitlar (kullanici_id, baslik, kategori, durum, aciklama, enlem, boylam) VALUES
(1, 'Niksar Merkez Yolları',          'Harita', 'Devam ediyor', 'Niksar ilçe merkezindeki tüm yolların Blender''da modellenmesi.',          40.589590, 36.950570),
(1, 'Niksar Çevre Yolu Pisti',        'Pist',   'Planlandı',    'Assetto Corsa için çevre yolundan yarış pisti oluşturulacak.',              40.580050, 36.961320),
(1, 'Blosm ile Yol Verisi Aktarımı',  'Veri',   'Tamamlandı',   'OpenStreetMap yol verisi Blosm eklentisiyle Blender''a aktarıldı.',         NULL,      NULL),
(1, 'KSEditor Pist Ayarları',         'Pist',   'Devam ediyor', 'Pistin KSEditor''da yüzey, kamera ve spawn noktası ayarları.',              40.580600, 36.941070),
(1, 'Yol Dokuları ve Çizgiler',       'Harita', 'Planlandı',    'Asfalt dokusu, orta çizgi ve kenar çizgilerinin eklenmesi.',                40.591980, 36.959360),
(2, 'Niksar Kalesi Tırmanış Yolu',    'Pist',   'Planlandı',    'Kaleye çıkan virajlı yoldan tırmanış (hillclimb) pisti hazırlanacak.',     40.593700, 36.957900),
(2, 'Yarış Aracı Ses Paketi',         'Araç',   'Tamamlandı',   'Kullanılacak aracın motor sesleri düzenlenip pakete eklendi.',             NULL,      NULL);
