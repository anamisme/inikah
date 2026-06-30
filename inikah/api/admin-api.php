<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

function requireAdminAuth() {
    $token = extractBearerToken();
    if (!verifyToken($token)) {
        http_response_code(401);
        echo json_encode(['error' => 'Akses ditolak. Silakan login ulang.']);
        exit;
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    case 'getSertifikat':
        requireAdminAuth();
        $stmt = $pdo->query("SELECT id, nama, link, tanggal FROM sertifikat ORDER BY tanggal DESC LIMIT 100");
        echo json_encode($stmt->fetchAll());
        break;

    case 'addSertifikat':
        requireAdminAuth();
        $input = json_decode(file_get_contents('php://input'), true);
        $nama = clean($input['nama'] ?? '');
        $link = clean($input['link'] ?? '');

        if (!$nama || !$link) {
            echo json_encode(['error' => 'Nama dan link wajib diisi']);
            break;
        }

        $stmt = $pdo->prepare("INSERT INTO sertifikat (nama, link) VALUES (?, ?)");
        $stmt->execute([$nama, $link]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;

    case 'deleteSertifikat':
        requireAdminAuth();
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['error' => 'ID tidak valid']); break; }

        $stmt = $pdo->prepare("DELETE FROM sertifikat WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    case 'getJadwal':
        requireAdminAuth();
        $stmt = $pdo->query("SELECT id, tanggal_akad, waktu, nama_pria, nama_wanita, desa, petugas, keterangan FROM jadwal ORDER BY tanggal_akad DESC LIMIT 50");
        echo json_encode($stmt->fetchAll());
        break;

    case 'addJadwal':
        requireAdminAuth();
        $input = json_decode(file_get_contents('php://input'), true);
        $tanggal   = clean($input['tanggal_akad'] ?? '');
        $waktu     = clean($input['waktu'] ?? '');
        $pria      = clean($input['nama_pria'] ?? '');
        $wanita    = clean($input['nama_wanita'] ?? '');
        $desa      = clean($input['desa'] ?? '');
        $petugas   = clean($input['petugas'] ?? '');
        $ket       = clean($input['keterangan'] ?? '');

        if (!$tanggal || !$pria || !$wanita) {
            echo json_encode(['error' => 'Tanggal, nama pria, dan nama wanita wajib diisi']);
            break;
        }

        $stmt = $pdo->prepare("INSERT INTO jadwal (tanggal_akad, waktu, nama_pria, nama_wanita, desa, petugas, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$tanggal, $waktu ?: null, $pria, $wanita, $desa ?: null, $petugas ?: null, $ket ?: null]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;

    case 'deleteJadwal':
        requireAdminAuth();
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['error' => 'ID tidak valid']); break; }

        $stmt = $pdo->prepare("DELETE FROM jadwal WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    case 'getHasilTest':
        requireAdminAuth();
        $tipe = clean($_GET['tipe'] ?? 'pretest');
        if (!in_array($tipe, ['pretest', 'posttest'])) $tipe = 'pretest';

        $stmt = $pdo->prepare("SELECT id, nama, nik, no_hp, skor, tanggal FROM jawaban_test WHERE tipe = ? ORDER BY tanggal DESC LIMIT 100");
        $stmt->execute([$tipe]);
        echo json_encode($stmt->fetchAll());
        break;

    default:
        echo json_encode(['error' => 'Action tidak dikenal']);
}
