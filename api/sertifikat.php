<?php
/**
 * API Pencarian Sertifikat - iNikah
 * Endpoint: api/sertifikat.php?q=nama
 */

require_once __DIR__ . '/config.php';

$query = trim($_GET['q'] ?? '');

if (!$query) {
    echo json_encode([]);
    exit;
}

// Cari berdasarkan nama (LIKE, case-insensitive)
$stmt = $pdo->prepare("SELECT nama, link FROM sertifikat WHERE nama LIKE ? ORDER BY nama ASC LIMIT 10");
$stmt->execute(['%' . $query . '%']);
$results = $stmt->fetchAll();

echo json_encode($results);
