<?php
/**
 * API Generate Sertifikat HTML - iNikah
 * Generate sertifikat sebagai halaman HTML dengan background template
 * 
 * POST body: { nama, skor, nik }
 */

// DEBUG MODE - hapus setelah selesai testing
if (isset($_GET['debug'])) {
    require_once __DIR__ . '/config.php';
    $templatePath = __DIR__ . '/../uploads/sertifikat/template.png';
    $outputDir = __DIR__ . '/../uploads/sertifikat/';
    echo json_encode([
        'template_exists' => file_exists($templatePath),
        'output_dir_exists' => is_dir($outputDir),
        'output_dir_writable' => is_writable($outputDir),
        'php_version' => phpversion()
    ]);
    exit;
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

// Buat folder output jika belum ada
$outputDir = __DIR__ . '/../uploads/sertifikat/';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Tanggal & tahun
$bulanIndo = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$tanggalFormatted = date('j') . ' ' . $bulanIndo[date('n')-1] . ' ' . date('Y');
$tahun = date('Y');

// Generate HTML sertifikat
$filename = 'sertifikat_' . preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($nama)) . '_' . time() . '.html';
$filepath = $outputDir . $filename;
$link = 'uploads/sertifikat/' . $filename;

$htmlCert = '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>E-Sertifikat - ' . htmlspecialchars($nama) . '</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { background: #e2e8f0; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", sans-serif; }
.cert-wrapper { position: relative; width: 100%; max-width: 900px; aspect-ratio: 1280/720; background-image: url("template.png"); background-size: cover; background-position: center; border-radius: 8px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); overflow: hidden; }
.cert-text { position: absolute; font-weight: 700; color: #1a1a1a; }
.cert-nama { top: 28%; left: 50%; transform: translateX(-50%); font-size: clamp(1.2rem, 3vw, 2rem); text-align: center; white-space: nowrap; }
.cert-nik { top: 39.5%; left: 50%; transform: translateX(-50%); font-size: clamp(0.7rem, 1.4vw, 1rem); text-align: center; letter-spacing: 1px; }
.cert-skor { top: 46.5%; left: 50%; transform: translateX(-50%); font-size: clamp(0.7rem, 1.4vw, 1rem); text-align: center; }
.cert-tahun { top: 20.5%; left: 50.5%; font-size: clamp(0.6rem, 1.2vw, 0.85rem); font-weight: 400; }
.cert-tanggal { top: 62%; left: 61%; font-size: clamp(0.55rem, 1.1vw, 0.8rem); font-weight: 400; }
.btn-area { margin-top: 20px; display: flex; gap: 12px; flex-wrap: wrap; justify-content: center; }
.btn-cert { padding: 14px 28px; border-radius: 12px; border: none; font-size: 0.9rem; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
.btn-print { background: #064e3b; color: #fff; }
.btn-back { background: #fff; color: #064e3b; border: 2px solid #064e3b; }
.btn-cert:hover { opacity: 0.85; }
@media print {
    body { background: #fff; padding: 0; }
    .cert-wrapper { max-width: 100%; border-radius: 0; box-shadow: none; }
    .btn-area { display: none; }
}
@media (max-width: 600px) {
    .cert-wrapper { aspect-ratio: auto; height: auto; padding-bottom: 56.25%; }
}
</style>
</head>
<body>
<div class="cert-wrapper">
    <div class="cert-text cert-nama">' . htmlspecialchars($nama) . '</div>
    <div class="cert-text cert-nik">' . htmlspecialchars($nik) . '</div>
    <div class="cert-text cert-skor">' . $skor . '</div>
    <div class="cert-text cert-tahun">' . $tahun . '</div>
    <div class="cert-text cert-tanggal">' . $tanggalFormatted . '</div>
</div>
<div class="btn-area">
    <button class="btn-cert btn-print" onclick="window.print()">🖨️ Cetak / Save PDF</button>
    <a href="../index.html" class="btn-cert btn-back">← Kembali ke Beranda</a>
</div>
</body>
</html>';

// Simpan file
file_put_contents($filepath, $htmlCert);

// Simpan ke database
$stmt = $pdo->prepare("INSERT INTO sertifikat (nama, link) VALUES (?, ?)");
$stmt->execute([$nama, $link]);

echo json_encode([
    'success' => true,
    'message' => 'Sertifikat berhasil digenerate',
    'link' => $link
]);
