<?php
/**
 * API Submit Form Pre/Post-Test - iNikah
 * Endpoint: POST api/form-submit.php
 * Body JSON: { tipe, nama, nik, no_hp, jawaban: {...}, skor }
 */

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method harus POST']);
    exit;
}

// Ambil data dari body JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    // Fallback ke form-data
    $input = $_POST;
}

$tipe    = clean($input['tipe'] ?? '');
$nama    = clean($input['nama'] ?? '');
$nik     = clean($input['nik'] ?? '');
$no_hp   = clean($input['no_hp'] ?? '');
$jawaban = $input['jawaban'] ?? [];
$skor    = isset($input['skor']) ? intval($input['skor']) : null;

// Validasi
if (!in_array($tipe, ['pretest', 'posttest'])) {
    echo json_encode(['error' => 'Tipe harus pretest atau posttest']);
    exit;
}

if (!$nama) {
    echo json_encode(['error' => 'Nama wajib diisi']);
    exit;
}

if (empty($jawaban)) {
    echo json_encode(['error' => 'Jawaban tidak boleh kosong']);
    exit;
}

// Simpan ke database
$stmt = $pdo->prepare("INSERT INTO jawaban_test (tipe, nama, nik, no_hp, jawaban, skor) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([
    $tipe,
    $nama,
    $nik ?: null,
    $no_hp ?: null,
    json_encode($jawaban),
    $skor
]);

echo json_encode([
    'success' => true,
    'message' => 'Jawaban berhasil disimpan',
    'id' => $pdo->lastInsertId()
]);
