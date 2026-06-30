<?php
/**
 * Konfigurasi Database iNikah
 * Ganti nilai di bawah sesuai dengan database cPanel kamu
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'uifodiej_inikah');      // sesuaikan dengan nama database
define('DB_USER', 'uifodiej_inikah');      // sesuaikan dengan user database
define('DB_PASS', 'PASSWORD_KAMU_DISINI'); // ganti dengan password database

// Secret key untuk token HMAC (ganti dengan string acak yang kuat)
define('INIKAH_SECRET', 'ganti_dengan_secret_key_acak_panjang');

// Password admin & petugas (WAJIB diganti sebelum upload)
define('ADMIN_PASSWORD', 'ganti_password_admin');
define('PETUGAS_PASSWORD', 'ganti_password_petugas');

// Koneksi PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Koneksi database gagal']);
    exit;
}

// CORS headers (supaya frontend bisa akses API)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Helper: bersihkan input
function clean($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}
