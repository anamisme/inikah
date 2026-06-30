<?php

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');

if (!$query) {
    echo json_encode([]);
    exit;
}

if (strlen($query) < 3) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT nama, link FROM sertifikat WHERE nama LIKE ? ORDER BY nama ASC LIMIT 10");
$stmt->execute(['%' . $query . '%']);
$results = $stmt->fetchAll();

echo json_encode($results);
