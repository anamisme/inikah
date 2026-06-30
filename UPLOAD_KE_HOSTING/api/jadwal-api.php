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

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'getJadwal':
        $stmt = $pdo->query("SELECT id, tanggal_akad, waktu, nama_pria, nama_wanita, desa, petugas, keterangan FROM jadwal ORDER BY tanggal_akad DESC LIMIT 50");
        echo json_encode($stmt->fetchAll());
        break;

    case 'addJadwal':
        requireAuth();
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Data tidak valid']);
            exit;
        }

        $tanggal = clean($input['tanggal_akad'] ?? '');
        $waktu = clean($input['waktu'] ?? '');
        $pria = clean($input['nama_pria'] ?? '');
        $wanita = clean($input['nama_wanita'] ?? '');
        $desa = clean($input['desa'] ?? '');

        if (!$tanggal || !$waktu || !$pria || !$wanita || !$desa) {
            http_response_code(400);
            echo json_encode(['error' => 'Semua field wajib diisi']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO jadwal (tanggal_akad, waktu, nama_pria, nama_wanita, desa) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$tanggal, $waktu, $pria, $wanita, $desa]);
        echo json_encode(['success' => true]);
        break;

    case 'deleteJadwal':
        requireAuth();
        $id = $_GET['id'] ?? '';
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID wajib']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM jadwal WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    case 'getPetugas':
        $stmt = $pdo->query("SELECT id, nama_petugas, tanggal, waktu, nama_pria, nama_wanita, foto FROM petugas_akad ORDER BY tanggal DESC LIMIT 50");
        echo json_encode($stmt->fetchAll());
        break;

    case 'addPetugas':
        requireAuth();
        $nama_petugas = clean($_POST['nama_petugas'] ?? '');
        $tanggal = clean($_POST['tanggal'] ?? '');
        $waktu = clean($_POST['waktu'] ?? '');
        $pria = clean($_POST['nama_pria'] ?? '');
        $wanita = clean($_POST['nama_wanita'] ?? '');

        if (!$nama_petugas || !$tanggal || !$waktu || !$pria || !$wanita) {
            http_response_code(400);
            echo json_encode(['error' => 'Semua field wajib diisi']);
            exit;
        }

        $fotoPath = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/petugas/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($ext, $allowed)) {
                http_response_code(400);
                echo json_encode(['error' => 'Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.']);
                exit;
            }

            if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
                http_response_code(400);
                echo json_encode(['error' => 'Ukuran file maksimal 2MB.']);
                exit;
            }

            $filename = uniqid('petugas_') . '.' . $ext;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $destination)) {
                $fotoPath = 'uploads/petugas/' . $filename;
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Gagal mengupload foto']);
                exit;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO petugas_akad (nama_petugas, tanggal, waktu, nama_pria, nama_wanita, foto) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nama_petugas, $tanggal, $waktu, $pria, $wanita, $fotoPath]);
        echo json_encode(['success' => true]);
        break;

    case 'deletePetugas':
        requireAuth();
        $id = $_GET['id'] ?? '';
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID wajib']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM petugas_akad WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Action tidak valid']);
        break;
}
