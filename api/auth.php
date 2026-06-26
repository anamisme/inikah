<?php
/**
 * API Autentikasi - iNikah
 * POST api/auth.php dengan body: { password, role }
 * role = "admin" atau "petugas"
 * 
 * Mengembalikan token session jika password benar
 */

require_once __DIR__ . '/config.php';

// ─── HELPER FUNCTIONS ─────────────────────────────

function getSecretKey() {
    // Diambil dari config.php (didefinisikan di server, tidak masuk ke git)
    return defined('INIKAH_SECRET') ? INIKAH_SECRET : 'inikah-secret-key-2024';
}

function generateToken($role) {
    $payload = $role . '|' . time() . '|' . bin2hex(random_bytes(16));
    $signature = hash_hmac('sha256', $payload, getSecretKey());
    return base64_encode($payload . '|' . $signature);
}

function verifyToken($token) {
    if (!$token) return false;
    
    $decoded = base64_decode($token);
    if (!$decoded) return false;
    
    $parts = explode('|', $decoded);
    if (count($parts) !== 4) return false;
    
    $role = $parts[0];
    $timestamp = (int)$parts[1];
    $random = $parts[2];
    $signature = $parts[3];
    
    // Cek expired (24 jam)
    if (time() - $timestamp > 86400) return false;
    
    // Verifikasi signature
    $payload = $role . '|' . $timestamp . '|' . $random;
    $expectedSig = hash_hmac('sha256', $payload, getSecretKey());
    
    return hash_equals($expectedSig, $signature);
}

function getTokenRole($token) {
    if (!verifyToken($token)) return null;
    $decoded = base64_decode($token);
    $parts = explode('|', $decoded);
    return $parts[0] ?? null;
}

// ─── HANDLE DIRECT REQUEST ────────────────────────
// Hanya jalankan logic login jika dipanggil langsung (bukan di-include)

if (basename($_SERVER['SCRIPT_FILENAME']) === 'auth.php') {

    header('Content-Type: application/json');

    $VALID_PASSWORDS = [
        'admin'   => defined('ADMIN_PASSWORD') ? ADMIN_PASSWORD : 'kuakarangdadap2024',
        'petugas' => defined('PETUGAS_PASSWORD') ? PETUGAS_PASSWORD : 'petugaskua2024'
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'verify') {
        $token = $_GET['token'] ?? '';
        if (verifyToken($token)) {
            echo json_encode(['valid' => true]);
        } else {
            http_response_code(401);
            echo json_encode(['valid' => false, 'error' => 'Token tidak valid']);
        }
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method harus POST']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $password = $input['password'] ?? '';
    $role = $input['role'] ?? 'admin';

    if (!in_array($role, ['admin', 'petugas'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Role tidak valid']);
        exit;
    }

    if ($password === $VALID_PASSWORDS[$role]) {
        $token = generateToken($role);
        echo json_encode(['success' => true, 'token' => $token, 'role' => $role]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Password salah']);
    }
}
