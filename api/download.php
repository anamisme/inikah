<?php
/**
 * Force download file - memaksa browser/WebView untuk download
 * Usage: api/download.php?file=uploads/sertifikat/nama.png
 */

$file = $_GET['file'] ?? '';

// Sanitasi: hanya izinkan file dari folder uploads
if (!$file || strpos($file, '..') !== false || !preg_match('/^uploads\//', $file)) {
    http_response_code(400);
    echo 'Invalid file';
    exit;
}

$fullPath = __DIR__ . '/../' . $file;

if (!file_exists($fullPath)) {
    http_response_code(404);
    echo 'File not found';
    exit;
}

$filename = basename($fullPath);
$mime = mime_content_type($fullPath);

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: no-cache, must-revalidate');

readfile($fullPath);
exit;
