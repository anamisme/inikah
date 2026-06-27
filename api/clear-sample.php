<?php
/**
 * HAPUS SEMUA DATA SAMPLE - jalankan sekali lalu hapus file ini
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

// Wajib token admin
$token = $_GET['token'] ?? '';
if (!verifyToken($token) || getTokenRole($token) !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

$confirm = $_GET['confirm'] ?? '';
if ($confirm !== 'yes') {
    echo json_encode(['warning' => 'Tambahkan &confirm=yes untuk mengonfirmasi penghapusan']);
    exit;
}

$pdo->exec("TRUNCATE TABLE jawaban_test");
$pdo->exec("TRUNCATE TABLE jadwal");
$pdo->exec("TRUNCATE TABLE sertifikat");
$pdo->exec("TRUNCATE TABLE petugas_akad");

echo json_encode(['success' => true, 'message' => 'Semua data sample berhasil dihapus']);
