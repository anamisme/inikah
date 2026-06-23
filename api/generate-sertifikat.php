<?php
/**
 * API Generate Sertifikat - iNikah
 * Generate sertifikat PNG dari template gambar + overlay text
 * 
 * POST body: { nama, skor, nik }
 */

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

// Path template
$templatePath = __DIR__ . '/../uploads/sertifikat/template.png';
if (!file_exists($templatePath)) {
    echo json_encode(['error' => 'Template sertifikat belum diupload']);
    exit;
}

// Buat folder output jika belum ada
$outputDir = __DIR__ . '/../uploads/sertifikat/';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Load template
$img = imagecreatefrompng($templatePath);
if (!$img) {
    echo json_encode(['error' => 'Gagal memuat template']);
    exit;
}

$width = imagesx($img);
$height = imagesy($img);

// Warna teks
$colorDark = imagecolorallocate($img, 15, 23, 42);       // #0f172a - hitam gelap
$colorGreen = imagecolorallocate($img, 6, 78, 59);       // #064e3b - hijau tua

// Tanggal & tahun
$bulanIndo = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$tanggalFormatted = date('j') . ' ' . $bulanIndo[date('n')-1] . ' ' . date('Y');
$tahun = date('Y');

// Nomor sertifikat: /Kua.11.26.08/BA.00/TAHUN
$nomorSert = '/Kua.11.26.08/BA.00/' . $tahun;

// Font - gunakan font default GD (angka 1-5) atau TrueType jika tersedia
// Coba cari font di sistem
$fontBold = __DIR__ . '/fonts/Inter-Bold.ttf';
$fontRegular = __DIR__ . '/fonts/Inter-Regular.ttf';

// Jika font TTF tidak ada, pakai built-in GD fonts
$useTTF = file_exists($fontBold) && file_exists($fontRegular);

if ($useTTF) {
    // ─── POSISI TEXT (sesuai template ~1024x576) ───
    // Nama peserta - tengah, di dalam segi enam
    $namaSize = 22;
    $bbox = imagettfbbox($namaSize, 0, $fontBold, $nama);
    $namaX = ($width - ($bbox[2] - $bbox[0])) / 2;
    $namaY = $height * 0.39;
    imagettftext($img, $namaSize, 0, (int)$namaX, (int)$namaY, $colorDark, $fontBold, $nama);

    // NIK
    $nikText = $nik;
    $nikSize = 12;
    $bbox = imagettfbbox($nikSize, 0, $fontRegular, $nikText);
    $nikX = ($width - ($bbox[2] - $bbox[0])) / 2;
    $nikY = $height * 0.47;
    imagettftext($img, $nikSize, 0, (int)$nikX, (int)$nikY, $colorDark, $fontRegular, $nikText);

    // Skor
    $skorText = (string)$skor;
    $skorSize = 12;
    $bbox = imagettfbbox($skorSize, 0, $fontRegular, $skorText);
    $skorX = ($width - ($bbox[2] - $bbox[0])) / 2;
    $skorY = $height * 0.53;
    imagettftext($img, $skorSize, 0, (int)$skorX, (int)$skorY, $colorDark, $fontRegular, $skorText);

    // Nomor sertifikat (di bawah tulisan "SERTIFIKAT")
    $nomorSize = 10;
    $bbox = imagettfbbox($nomorSize, 0, $fontRegular, $nomorSert);
    $nomorX = ($width - ($bbox[2] - $bbox[0])) / 2 + ($width * 0.05);
    $nomorY = $height * 0.235;
    imagettftext($img, $nomorSize, 0, (int)$nomorX, (int)$nomorY, $colorDark, $fontRegular, $nomorSert);

    // Tanggal (kanan bawah, setelah "Karangdadap,")
    $tglSize = 10;
    $tglX = $width * 0.60;
    $tglY = $height * 0.72;
    imagettftext($img, $tglSize, 0, (int)$tglX, (int)$tglY, $colorDark, $fontRegular, $tanggalFormatted);

} else {
    // ─── FALLBACK: pakai built-in GD fonts ───
    // Font size 5 = terbesar di GD built-in (~13px)
    
    // Nama peserta - tengah
    $namaW = imagefontwidth(5) * strlen($nama);
    $namaX = ($width - $namaW) / 2;
    $namaY = $height * 0.36;
    imagestring($img, 5, (int)$namaX, (int)$namaY, $nama, $colorDark);

    // NIK
    $nikText = $nik;
    $nikW = imagefontwidth(4) * strlen($nikText);
    $nikX = ($width - $nikW) / 2;
    $nikY = $height * 0.45;
    imagestring($img, 4, (int)$nikX, (int)$nikY, $nikText, $colorDark);

    // Skor
    $skorText = (string)$skor;
    $skorW = imagefontwidth(4) * strlen($skorText);
    $skorX = ($width - $skorW) / 2;
    $skorY = $height * 0.51;
    imagestring($img, 4, (int)$skorX, (int)$skorY, $skorText, $colorDark);

    // Nomor sertifikat
    $nomorW = imagefontwidth(3) * strlen($nomorSert);
    $nomorX = ($width - $nomorW) / 2 + ($width * 0.05);
    $nomorY = $height * 0.215;
    imagestring($img, 3, (int)$nomorX, (int)$nomorY, $nomorSert, $colorDark);

    // Tanggal
    $tglX = $width * 0.60;
    $tglY = $height * 0.70;
    imagestring($img, 3, (int)$tglX, (int)$tglY, $tanggalFormatted, $colorDark);
}

// Simpan output
$filename = 'sertifikat_' . preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($nama)) . '_' . time() . '.png';
$outputPath = $outputDir . $filename;
$link = 'uploads/sertifikat/' . $filename;

imagepng($img, $outputPath);
imagedestroy($img);

// Simpan ke database
$stmt = $pdo->prepare("INSERT INTO sertifikat (nama, link) VALUES (?, ?)");
$stmt->execute([$nama, $link]);

echo json_encode([
    'success' => true,
    'message' => 'Sertifikat berhasil digenerate',
    'link' => $link
]);
