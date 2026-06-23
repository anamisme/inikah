<?php
/**
 * API Admin - iNikah
 * Endpoint untuk kelola sertifikat, jadwal, dan lihat hasil test
 * 
 * Sertifikat:
 *   GET  ?action=getSertifikat
 *   POST ?action=addSertifikat  (nama, link)
 *   GET  ?action=deleteSertifikat&id=X
 * 
 * Jadwal:
 *   GET  ?action=getJadwal
 *   POST ?action=addJadwal (tanggal_akad, waktu, nama_pria, nama_wanita, desa, petugas, keterangan)
 *   GET  ?action=deleteJadwal&id=X
 * 
 * Hasil Test:
 *   GET  ?action=getHasilTest&tipe=pretest|posttest
 */

require_once __DIR__ . '/config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    // ─── SERTIFIKAT ───────────────────────────────────

    case 'getSertifikat':
        $stmt = $pdo->query("SELECT id, nama, link, tanggal FROM sertifikat ORDER BY tanggal DESC LIMIT 100");
        echo json_encode($stmt->fetchAll());
        break;

    case 'addSertifikat':
        $nama = clean($_POST['nama'] ?? $_GET['nama'] ?? '');
        $link = clean($_POST['link'] ?? $_GET['link'] ?? '');

        if (!$nama || !$link) {
            echo json_encode(['error' => 'Nama dan link wajib diisi']);
            break;
        }

        $stmt = $pdo->prepare("INSERT INTO sertifikat (nama, link) VALUES (?, ?)");
        $stmt->execute([$nama, $link]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;

    case 'deleteSertifikat':
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['error' => 'ID tidak valid']); break; }

        $stmt = $pdo->prepare("DELETE FROM sertifikat WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    // ─── JADWAL ───────────────────────────────────────

    case 'getJadwal':
        $stmt = $pdo->query("SELECT id, tanggal_akad, waktu, nama_pria, nama_wanita, desa, petugas, keterangan FROM jadwal ORDER BY tanggal_akad DESC LIMIT 50");
        echo json_encode($stmt->fetchAll());
        break;

    case 'addJadwal':
        $tanggal   = clean($_POST['tanggal_akad'] ?? $_GET['tanggal_akad'] ?? '');
        $waktu     = clean($_POST['waktu'] ?? $_GET['waktu'] ?? '');
        $pria      = clean($_POST['nama_pria'] ?? $_GET['nama_pria'] ?? '');
        $wanita    = clean($_POST['nama_wanita'] ?? $_GET['nama_wanita'] ?? '');
        $desa      = clean($_POST['desa'] ?? $_GET['desa'] ?? '');
        $petugas   = clean($_POST['petugas'] ?? $_GET['petugas'] ?? '');
        $ket       = clean($_POST['keterangan'] ?? $_GET['keterangan'] ?? '');

        if (!$tanggal || !$pria || !$wanita) {
            echo json_encode(['error' => 'Tanggal, nama pria, dan nama wanita wajib diisi']);
            break;
        }

        $stmt = $pdo->prepare("INSERT INTO jadwal (tanggal_akad, waktu, nama_pria, nama_wanita, desa, petugas, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$tanggal, $waktu ?: null, $pria, $wanita, $desa ?: null, $petugas ?: null, $ket ?: null]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;

    case 'deleteJadwal':
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['error' => 'ID tidak valid']); break; }

        $stmt = $pdo->prepare("DELETE FROM jadwal WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    // ─── HASIL TEST ───────────────────────────────────

    case 'getHasilTest':
        $tipe = clean($_GET['tipe'] ?? 'pretest');
        if (!in_array($tipe, ['pretest', 'posttest'])) $tipe = 'pretest';

        $stmt = $pdo->prepare("SELECT id, nama, nik, no_hp, skor, tanggal FROM jawaban_test WHERE tipe = ? ORDER BY tanggal DESC LIMIT 100");
        $stmt->execute([$tipe]);
        echo json_encode($stmt->fetchAll());
        break;

    default:
        echo json_encode(['error' => 'Action tidak dikenal']);
}
