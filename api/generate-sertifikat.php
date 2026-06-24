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

// Nomor sertifikat: hanya tahun di paling akhir (sesuai template: /Kua.11.26.08/BA.00/TAHUN)
$nomorSert = $tahun;

// Font - gunakan font default GD (angka 1-5) atau TrueType jika tersedia
// Coba cari font di sistem
$fontBold = __DIR__ . '/fonts/Inter_28pt-Bold.ttf';
$fontRegular = __DIR__ . '/fonts/Inter_28pt-Regular.ttf';

// Jika font TTF tidak ada, pakai built-in GD fonts
$useTTF = file_exists($fontBold) && file_exists($fontRegular);

if ($useTTF) {
    // ─── POSISI TEXT (template 1280x720) ───
    
    // Nama peserta - center horizontal, di dalam hexagon
    $namaSize = 26;
    $bbox = imagettfbbox($namaSize, 0, $fontBold, $nama);
    $namaX = ($width - ($bbox[2] - $bbox[0])) / 2;
    $namaY = (int)($height * 0.345);
    imagettftext($img, $namaSize, 0, (int)$namaX, (int)$namaY, $colorDark, $fontBold, $nama);

    // NIK - setelah teks "NIK :" di template (geser ke kanan dari center)
    $nikText = $nik;
    $nikSize = 13;
    $nikX = (int)($width * 0.42);
    $nikY = (int)($height * 0.435);
    imagettftext($img, $nikSize, 0, (int)$nikX, (int)$nikY, $colorDark, $fontBold, $nikText);

    // Skor - setelah teks "Skor Nilai :" di template
    $skorText = (string)$skor;
    $skorSize = 13;
    $skorX = (int)($width * 0.445);
    $skorY = (int)($height * 0.505);
    imagettftext($img, $skorSize, 0, (int)$skorX, (int)$skorY, $colorDark, $fontBold, $skorText);

    // Tahun - paling akhir baris nomor (setelah /Kua.11.26.08/BA.00/)
    $nomorSize = 12;
    $nomorX = (int)($width * 0.495);
    $nomorY = (int)($height * 0.265);
    imagettftext($img, $nomorSize, 0, (int)$nomorX, (int)$nomorY, $colorDark, $fontRegular, $nomorSert);

    // Tanggal - setelah "Karangdadap," di area kanan bawah
    $tglSize = 11;
    $tglX = (int)($width * 0.635);
    $tglY = (int)($height * 0.655);
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
