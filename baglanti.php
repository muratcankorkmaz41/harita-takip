<?php
// baglanti.php - PDO ile MySQL veritabanı bağlantısı

// XAMPP varsayılan ayarları. Farklı bir sunucuda bu değerleri değiştirin.
const DB_SUNUCU = 'localhost';
const DB_ADI    = 'harita_takip';
const DB_KULLANICI = 'root';
const DB_SIFRE  = '';

/**
 * Tek bir PDO nesnesi oluşturur ve her çağrıda aynısını döndürür.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    // charset=utf8mb4: Türkçe karakterler ve emoji doğru saklanır
    $dsn = 'mysql:host=' . DB_SUNUCU . ';dbname=' . DB_ADI . ';charset=utf8mb4';

    try {
        $pdo = new PDO($dsn, DB_KULLANICI, DB_SIFRE, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // hatada istisna fırlat
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // satırları dizi olarak getir
            PDO::ATTR_EMULATE_PREPARES   => false,                  // gerçek hazır ifadeler (SQL injection koruması)
        ]);
    } catch (PDOException $h) {
        // Ayrıntılı hata yalnızca sunucu günlüğüne yazılır, kullanıcıya gösterilmez
        error_log('Veritabanı bağlantı hatası: ' . $h->getMessage());
        http_response_code(500);
        exit('Veritabanına bağlanılamadı. MySQL\'in çalıştığından ve kurulum.sql dosyasının içe aktarıldığından emin olun.');
    }

    return $pdo;
}
