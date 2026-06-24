<?php
/**
 * API Notifikasi & Banner - iNikah
 * Endpoint: api/notifikasi.php?action=get|add|delete|getBanners|addBanner|deleteBanner
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

// Fungsi cek token untuk aksi yang butuh autentikasi
function requireAuth() {
    $token = $_GET['token'] ?? $_POST['token'] ?? '';
    if (!$token) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = str_replace('Bearer ', '', $authHeader);
    }
    if (!verifyToken($token)) {
        http_response_code(401);
        echo json_encode(['error' => 'Akses ditolak. Silakan login ulang.']);
        exit;
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'get';

switch ($action) {

    // ─── NOTIFIKASI ───────────────────────────────────

    case 'get':
        $stmt = $pdo->query("SELECT id, judul, pesan, tanggal FROM notifikasi ORDER BY tanggal DESC LIMIT 20");
        echo json_encode($stmt->fetchAll());
        break;

    case 'add':
        requireAuth();
        $judul = clean($_GET['judul'] ?? $_POST['judul'] ?? '');
        $pesan = clean($_GET['pesan'] ?? $_POST['pesan'] ?? '');

        if (!$judul || !$pesan) {
            echo json_encode(['error' => 'Judul dan pesan wajib diisi']);
            break;
        }

        $stmt = $pdo->prepare("INSERT INTO notifikasi (judul, pesan) VALUES (?, ?)");
        $stmt->execute([$judul, $pesan]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;

    case 'delete':
        requireAuth();
        $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['error' => 'ID tidak valid']);
            break;
        }

        $stmt = $pdo->prepare("DELETE FROM notifikasi WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    // ─── BANNER ───────────────────────────────────────

    case 'getBanners':
        $stmt = $pdo->query("SELECT id, judul, tag, link, gambar, warna FROM banner WHERE aktif = 1 ORDER BY tanggal DESC LIMIT 6");
        echo json_encode($stmt->fetchAll());
        break;

    case 'addBanner':
        requireAuth();
        $judul = clean($_GET['judul'] ?? $_POST['judul'] ?? '');
        $tag   = clean($_GET['tag'] ?? $_POST['tag'] ?? 'INFO');
        $link  = clean($_GET['link'] ?? $_POST['link'] ?? '');
        $gambar = clean($_GET['gambar'] ?? $_POST['gambar'] ?? '');
        $warna = clean($_GET['warna'] ?? $_POST['warna'] ?? '');

        if (!$judul) {
            echo json_encode(['error' => 'Judul banner wajib diisi']);
            break;
        }

        // Cek jumlah banner aktif (maks 6)
        $count = $pdo->query("SELECT COUNT(*) FROM banner WHERE aktif = 1")->fetchColumn();
        if ($count >= 6) {
            echo json_encode(['error' => 'Maksimal 6 banner aktif. Hapus salah satu terlebih dahulu.']);
            break;
        }

        // Konversi Google Drive ID ke thumbnail URL
        if ($gambar && !str_starts_with($gambar, 'http')) {
            $gambar = 'https://lh3.googleusercontent.com/d/' . $gambar . '=w1200';
        }

        $stmt = $pdo->prepare("INSERT INTO banner (judul, tag, link, gambar, warna) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$judul, $tag, $link ?: null, $gambar ?: null, $warna ?: null]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;

    case 'deleteBanner':
        requireAuth();
        $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['error' => 'ID tidak valid']);
            break;
        }

        $stmt = $pdo->prepare("DELETE FROM banner WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['error' => 'Action tidak dikenal']);
}
