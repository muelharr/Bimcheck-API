<?php
/**
 * ============================================================================
 * API CONFIG — Konfigurasi Dasar Web Service BimCheck
 * ============================================================================
 * 
 * File ini berfungsi sebagai bootstrap untuk seluruh endpoint API.
 * Tugasnya:
 *   1. Mengatur header HTTP agar semua response bertipe JSON
 *   2. Mengaktifkan CORS agar bisa diakses dari aplikasi klien manapun
 *   3. Menghubungkan ke koneksi database yang sudah ada (config/koneksi.php)
 *   4. Menyediakan fungsi helper untuk mengirim response JSON yang konsisten
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/db_helper.php';

define('API_SECRET_KEY', 'bimcheck_secret_key_2026');
define('TOKEN_EXPIRY', 86400);

/**
 * Kirim response JSON sukses ke klien
 */
function json_success($message, $data = null, $code = 200) {
    http_response_code($code);
    $response = [
        'status'  => 'success',
        'message' => $message
    ];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Kirim response JSON error ke klien
 */
function json_error($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'status'  => 'error',
        'message' => $message
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Ambil body request yang berformat JSON
 */
function get_json_input() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    
    if (!is_array($data)) {
        return [];
    }
    return $data;
}

/**
 * Generate token simulasi (pengganti JWT sederhana)
 */
function generate_token($user_id, $role) {
    $payload = json_encode([
        'user_id' => $user_id,
        'role'    => $role,
        'iat'     => time(),
        'exp'     => time() + TOKEN_EXPIRY
    ]);
    
    $base64_payload = base64_encode($payload);
    $signature = hash_hmac('sha256', $base64_payload, API_SECRET_KEY);
    
    return $base64_payload . '.' . $signature;
}

/**
 * Verifikasi dan decode token dari header Authorization
 */
function verify_token() {
    $headers = getallheaders();
    $auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    if (!preg_match('/^Bearer\s+(.+)$/i', $auth_header, $matches)) {
        return null;
    }
    
    $token = $matches[1];
    $parts = explode('.', $token);
    
    if (count($parts) !== 2) {
        return null;
    }
    
    $base64_payload = $parts[0];
    $signature      = $parts[1];
    
    $expected_signature = hash_hmac('sha256', $base64_payload, API_SECRET_KEY);
    if (!hash_equals($expected_signature, $signature)) {
        return null;
    }
    
    $payload = json_decode(base64_decode($base64_payload), true);
    if (!$payload) {
        return null;
    }
    
    if (isset($payload['exp']) && $payload['exp'] < time()) {
        return null;
    }
    
    return $payload;
}

/**
 * Middleware: Wajibkan autentikasi token sebelum mengakses endpoint
 */
function require_auth() {
    $payload = verify_token();
    if (!$payload) {
        json_error('Token tidak valid atau telah kedaluwarsa. Silakan login ulang.', 401);
    }
    return $payload;
}
