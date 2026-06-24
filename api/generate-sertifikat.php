<?php
/**
 * API Generate Sertifikat PNG - iNikah (PHP GD Version)
 * Generate sertifikat sebagai gambar PNG dengan teks overlay langsung di atas template
 * Posisi teks menggunakan koordinat pixel — presisi dan konsisten di semua device
 * 
 * POST body: { nama, skor, nik }
 * 
 * Template: uploads/sertifikat/template.png (1280x720)
 */

// DEBUG MODE
if (isset($_GET['debug'])) {
    require_once __DIR__ . '/config.php';
    $templatePath = __DIR__ . '/../uploads/sertifikat/template.png';
    $outputDir = __DIR__ . '/../uploads/sertifikat/';
    $fontPath = __DIR__ . '/fonts/Inter_28pt-Bold.ttf';
    echo json_encode([
        'template_exists' => file_exists($templatePath),
        'template_size' => file_exists($templatePath) ? getimagesize($templatePath) : null,
        'output_dir_exists' => is_dir($outputDir),
        'output_dir_writable' => is_writable($outputDir),
        'font_exists' => file_exists($fontPath),
        'gd_enabled' => extension_loaded('gd'),
        'php_version' => phpversion()
    ]);
    exit;
}

// TEST MODE
if (isset($_GET['test'])) {
    require_once __DIR__ . '/config.php';
    $nama = 'TEST GD SERTIFIKAT';
    $skor = 92;
    $nik = '3326059999990001';
    goto GENERATE;
}

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method harus POST']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$nama = strtoupper(trim($input['nama'] ?? ''));
$skor = intval($input['skor'] ?? 0);
$nik  = trim($input['nik'] ?? '');

GENERATE:

if (!$nama) {
    echo json_encode(['error' => 'Nama wajib diisi']);
    exit;
}

// Cek apakah sertifikat untuk nama ini sudah ada
$stmt = $pdo->prepare("SELECT id, link FROM sertifikat WHERE nama = ? LIMIT 1");
$stmt->execute([$nama]);
$existing = $stmt->fetch();
if ($existing) {
    echo json_encode(['success' => true, 'message' => 'Sertifikat sudah ada', 'link' => $existing['link']]);
    exit;
}

// Cek GD extension
if (!extension_loaded('gd')) {
    echo json_encode(['error' => 'PHP GD extension tidak tersedia di server']);
    exit;
}

// Path setup
$templatePath = __DIR__ . '/../uploads/sertifikat/template.png';
$outputDir = __DIR__ . '/../uploads/sertifikat/';
$fontBold = __DIR__ . '/fonts/Inter_28pt-Bold.ttf';
$fontRegular = __DIR__ . '/fonts/Inter_28pt-Regular.ttf';

// Validasi file
if (!file_exists($templatePath)) {
    echo json_encode(['error' => 'Template sertifikat tidak ditemukan']);
    exit;
}
if (!file_exists($fontBold)) {
    echo json_encode(['error' => 'Font Bold tidak ditemukan. Upload Inter_28pt-Bold.ttf ke api/fonts/']);
    exit;
}

// Buat folder output jika belum ada
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Load template
$img = imagecreatefrompng($templatePath);
if (!$img) {
    echo json_encode(['error' => 'Gagal membuka template PNG']);
    exit;
}

$imgWidth = imagesx($img);
$imgHeight = imagesy($img);

// Warna teks
$black = imagecolorallocate($img, 26, 26, 26);
$darkGray = imagecolorallocate($img, 50, 50, 50);

// Tanggal & tahun
$bulanIndo = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$tanggalFormatted = date('j') . ' ' . $bulanIndo[date('n')-1] . ' ' . date('Y');
$tahun = date('Y');

// ════════════════════════════════════════════════════════
// KOORDINAT TEKS (sesuaikan nilai pixel ini dengan template)
// Template diasumsikan 1280x720 px
// Gunakan ?debug untuk cek ukuran template sebenarnya
// ════════════════════════════════════════════════════════

// Helper: tulis teks rata tengah horizontal
function drawCenteredText($img, $size, $y, $color, $font, $text, $imgWidth) {
    $bbox = imagettfbbox($size, 0, $font, $text);
    $textWidth = $bbox[2] - $bbox[0];
    $x = ($imgWidth - $textWidth) / 2;
    imagettftext($img, $size, 0, (int)$x, $y, $color, $font, $text);
}

// Helper: tulis teks di posisi x,y tertentu
function drawText($img, $size, $x, $y, $color, $font, $text) {
    imagettftext($img, $size, 0, $x, $y, $color, $font, $text);
}

// ──── NAMA (tengah, besar, bold) ────
// Posisi Y sekitar 37.5% dari 720 = ~270px
drawCenteredText($img, 28, 270, $black, $fontBold, $nama, $imgWidth);

// ──── NIK (tengah, di bawah label "NIK :") ────
// Posisi Y sekitar 44% dari 720 = ~317px
drawCenteredText($img, 14, 317, $darkGray, file_exists($fontRegular) ? $fontRegular : $fontBold, $nik, $imgWidth);

// ──── SKOR (tengah, di bawah label "Skor Nilai :") ────
// Posisi Y sekitar 48% dari 720 = ~346px
drawCenteredText($img, 16, 346, $black, $fontBold, (string)$skor, $imgWidth);

// ──── TAHUN (di area nomor surat, kanan atas setelah "SERTIFIKAT") ────
// Posisi: sekitar x=62% dari 1280 = ~794px, y=32% dari 720 = ~230px
drawText($img, 12, 794, 230, $black, $fontBold, $tahun);

// ──── TANGGAL (area tanda tangan, kanan bawah) ────
// Posisi: sekitar x=68% dari 1280 = ~870px, y=50% dari 720 = ~360px
drawText($img, 11, 870, 360, $darkGray, file_exists($fontRegular) ? $fontRegular : $fontBold, $tanggalFormatted);

// ════════════════════════════════════════════════════════
// SIMPAN OUTPUT
// ════════════════════════════════════════════════════════

$filename = 'sertifikat_' . preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($nama)) . '_' . time() . '.png';
$filepath = $outputDir . $filename;
$link = 'uploads/sertifikat/' . $filename;

// Simpan sebagai PNG
imagepng($img, $filepath, 6); // kualitas kompresi 6 (0-9, 0=terbaik)
imagedestroy($img);

// Simpan ke database
$stmt = $pdo->prepare("INSERT INTO sertifikat (nama, link) VALUES (?, ?)");
$stmt->execute([$nama, $link]);

echo json_encode([
    'success' => true,
    'message' => 'Sertifikat berhasil digenerate (PNG)',
    'link' => $link
]);
