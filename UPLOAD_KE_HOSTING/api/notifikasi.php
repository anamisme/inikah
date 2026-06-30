<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

function requireAuth() {
    $token = extractBearerToken();
    if (!verifyToken($token)) {
        http_response_code(401);
        echo json_encode(['error' => 'Akses ditolak. Silakan login ulang.']);
        exit;
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'get';

switch ($action) {

    case 'get':
        $stmt = $pdo->query("SELECT id, judul, pesan, tanggal FROM notifikasi ORDER BY tanggal DESC LIMIT 20");
        echo json_encode($stmt->fetchAll());
        break;

    case 'add':
        requireAuth();
        $input = json_decode(file_get_contents('php://input'), true);
        $judul = clean($input['judul'] ?? '');
        $pesan = clean($input['pesan'] ?? '');

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
        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['error' => 'ID tidak valid']);
            break;
        }

        $stmt = $pdo->prepare("DELETE FROM notifikasi WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    case 'getBanners':
        $stmt = $pdo->query("SELECT id, judul, tag, link, gambar, warna FROM banner WHERE aktif = 1 ORDER BY tanggal DESC LIMIT 6");
        echo json_encode($stmt->fetchAll());
        break;

    case 'addBanner':
        requireAuth();
        $input = json_decode(file_get_contents('php://input'), true);
        $judul  = clean($input['judul'] ?? '');
        $tag    = clean($input['tag'] ?? 'INFO');
        $link   = clean($input['link'] ?? '');
        $gambar = clean($input['gambar'] ?? '');
        $warna  = clean($input['warna'] ?? '');

        if (!$judul) {
            echo json_encode(['error' => 'Judul banner wajib diisi']);
            break;
        }

        if ($link && !preg_match('/^https?:\/\//i', $link)) {
            echo json_encode(['error' => 'Link banner harus URL yang valid (http/https)']);
            break;
        }

        $count = $pdo->query("SELECT COUNT(*) FROM banner WHERE aktif = 1")->fetchColumn();
        if ($count >= 6) {
            echo json_encode(['error' => 'Maksimal 6 banner aktif. Hapus salah satu terlebih dahulu.']);
            break;
        }

        if ($gambar && !str_starts_with($gambar, 'http')) {
            $gambar = 'https://lh3.googleusercontent.com/d/' . $gambar . '=w1200';
        }

        $stmt = $pdo->prepare("INSERT INTO banner (judul, tag, link, gambar, warna) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$judul, $tag, $link ?: null, $gambar ?: null, $warna ?: null]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;

    case 'deleteBanner':
        requireAuth();
        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id'] ?? $_GET['id'] ?? 0);
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
