<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

$token = extractBearerToken();
if (!verifyToken($token)) {
    http_response_code(401);
    echo json_encode(['error' => 'Akses ditolak. Silakan login terlebih dahulu.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method harus POST']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$nama = strtoupper(trim($input['nama'] ?? ''));
$skor = intval($input['skor'] ?? 0);
$nik  = trim($input['nik'] ?? '');

if (!$nama) {
    echo json_encode(['error' => 'Nama wajib diisi']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, link FROM sertifikat WHERE nama = ? LIMIT 1");
$stmt->execute([$nama]);
$existing = $stmt->fetch();
if ($existing) {
    echo json_encode(['success' => true, 'message' => 'Sertifikat sudah ada', 'link' => $existing['link']]);
    exit;
}

if (!extension_loaded('gd')) {
    echo json_encode(['error' => 'PHP GD extension tidak tersedia di server']);
    exit;
}

$templatePath = __DIR__ . '/../uploads/sertifikat/template.png';
$outputDir = __DIR__ . '/../uploads/sertifikat/';
$fontBold = __DIR__ . '/fonts/Inter_28pt-Bold.ttf';
$fontRegular = __DIR__ . '/fonts/Inter_28pt-Regular.ttf';

if (!file_exists($templatePath)) {
    echo json_encode(['error' => 'Template sertifikat tidak ditemukan']);
    exit;
}
if (!file_exists($fontBold)) {
    echo json_encode(['error' => 'Font Bold tidak ditemukan. Upload Inter_28pt-Bold.ttf ke api/fonts/']);
    exit;
}

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$img = imagecreatefrompng($templatePath);
if (!$img) {
    echo json_encode(['error' => 'Gagal membuka template PNG']);
    exit;
}

$imgWidth = imagesx($img);
$imgHeight = imagesy($img);

$black = imagecolorallocate($img, 26, 26, 26);
$darkGray = imagecolorallocate($img, 50, 50, 50);

$bulanIndo = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$tanggalFormatted = date('j') . ' ' . $bulanIndo[date('n')-1] . ' ' . date('Y');
$tahun = date('Y');

function drawCenteredText($img, $size, $y, $color, $font, $text, $imgWidth) {
    $bbox = imagettfbbox($size, 0, $font, $text);
    $textWidth = $bbox[2] - $bbox[0];
    $x = ($imgWidth - $textWidth) / 2;
    imagettftext($img, $size, 0, (int)$x, $y, $color, $font, $text);
}

function drawText($img, $size, $x, $y, $color, $font, $text) {
    imagettftext($img, $size, 0, $x, $y, $color, $font, $text);
}

drawCenteredText($img, 22, 318, $black, $fontBold, $nama, $imgWidth);

drawText($img, 13, 725, 388, $black, $fontBold, (string)$skor);

drawText($img, 11, 850, 254, $black, $fontBold, $tahun);

drawText($img, 11, 1095, 484, $darkGray, $fontBold, $tanggalFormatted);

$filename = 'sertifikat_' . preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($nama)) . '_' . time() . '.png';
$filepath = $outputDir . $filename;
$link = 'uploads/sertifikat/' . $filename;

imagepng($img, $filepath, 6);
imagedestroy($img);

$stmt = $pdo->prepare("INSERT INTO sertifikat (nama, link) VALUES (?, ?)");
$stmt->execute([$nama, $link]);

echo json_encode([
    'success' => true,
    'message' => 'Sertifikat berhasil digenerate (PNG)',
    'link' => $link
]);
