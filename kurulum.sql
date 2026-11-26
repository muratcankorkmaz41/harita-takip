-- ==========================================================================
-- Harita & Pist Takip Sistemi - veritabanı kurulum dosyası
-- phpMyAdmin > İçe Aktar ile ya da komut satırından çalıştırılabilir:
--   mysql -u root < kurulum.sql
-- ==========================================================================

CREATE DATABASE IF NOT EXISTS harita_takip
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_turkish_ci;

USE harita_takip;

-- Yeniden kurulumda eski tabloyu kaldır
DROP TABLE IF EXISTS kayitlar;

-- --------------------------------------------------------------------------
-- Kayıt tablosu: her satır bir harita / pist çalışmasıdır
-- --------------------------------------------------------------------------
CREATE TABLE kayitlar (
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    baslik             VARCHAR(80)  NOT NULL,
    kategori           ENUM('Harita', 'Pist', 'Veri', 'Araç') NOT NULL,
    durum              ENUM('Planlandı', 'Devam ediyor', 'Tamamlandı') NOT NULL DEFAULT 'Planlandı',
    aciklama           VARCHAR(300) NOT NULL,
    enlem              DECIMAL(9,6) NULL DEFAULT NULL,   -- konum isteğe bağlı
    boylam             DECIMAL(9,6) NULL DEFAULT NULL,
    olusturma_tarihi   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    guncelleme_tarihi  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_durum (durum),
    KEY idx_kategori (kategori)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------------------------
-- Örnek kayıtlar
-- --------------------------------------------------------------------------
INSERT INTO kayitlar (baslik, kategori, durum, aciklama, enlem, boylam) VALUES
('Niksar Merkez Yolları',          'Harita', 'Devam ediyor', 'Niksar ilçe merkezindeki tüm yolların Blender''da modellenmesi.',          40.589590, 36.950570),
('Niksar Çevre Yolu Pisti',        'Pist',   'Planlandı',    'Assetto Corsa için çevre yolundan yarış pisti oluşturulacak.',              40.580050, 36.961320),
('Blosm ile Yol Verisi Aktarımı',  'Veri',   'Tamamlandı',   'OpenStreetMap yol verisi Blosm eklentisiyle Blender''a aktarıldı.',         NULL,      NULL),
('KSEditor Pist Ayarları',         'Pist',   'Devam ediyor', 'Pistin KSEditor''da yüzey, kamera ve spawn noktası ayarları.',              40.580600, 36.941070),
('Yol Dokuları ve Çizgiler',       'Harita', 'Planlandı',    'Asfalt dokusu, orta çizgi ve kenar çizgilerinin eklenmesi.',                40.591980, 36.959360);
