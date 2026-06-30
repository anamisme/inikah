<?php

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method harus POST']);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!$input || !is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Data JSON tidak valid']);
    exit;
}

$tipe    = clean($input['tipe'] ?? '');
$nama    = clean($input['nama'] ?? '');
$nik     = clean($input['nik'] ?? '');
$no_hp   = clean($input['no_hp'] ?? '');
$jawaban = $input['jawaban'] ?? [];
$skor    = isset($input['skor']) ? intval($input['skor']) : null;

if (!in_array($tipe, ['pretest', 'posttest'])) {
    echo json_encode(['error' => 'Tipe harus pretest atau posttest']);
    exit;
}

if (!$nama) {
    echo json_encode(['error' => 'Nama wajib diisi']);
    exit;
}

if (empty($jawaban) || !is_array($jawaban)) {
    echo json_encode(['error' => 'Jawaban tidak boleh kosong']);
    exit;
}

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
