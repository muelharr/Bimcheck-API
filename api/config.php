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

// ─── HEADER HTTP ────────────────────────────────────────────────────────────
// Wajib: memberitahu klien bahwa semua output dari API ini adalah JSON
header('Content-Type: application/json; charset=UTF-8');

// CORS Headers: mengizinkan akses dari domain/aplikasi klien manapun
// Penting untuk integrasi dengan aplikasi mobile atau frontend terpisah
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request (OPTIONS) dari browser
// Browser mengirim OPTIONS sebelum POST untuk mengecek izin CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ─── KONEKSI DATABASE ───────────────────────────────────────────────────────
// Menggunakan koneksi yang sudah ada di config/koneksi.php
// Variabel $conn akan tersedia setelah include ini
require_once __DIR__ . '/../config/koneksi.php';

// Include helper database untuk fungsi prepared statement (db_fetch, db_fetch_all, dll)
require_once __DIR__ . '/../config/db_helper.php';

// ─── KONSTANTA API ──────────────────────────────────────────────────────────
// Secret key untuk generate token (simulasi JWT)
// Di production, simpan di environment variable, JANGAN hardcode
define('API_SECRET_KEY', 'bimcheck_secret_key_2026');

// Durasi token berlaku (dalam detik) — 24 jam
define('TOKEN_EXPIRY', 86400);

// ─── FUNGSI HELPER RESPONSE ────────────────────────────────────────────────

/**
 * Kirim response JSON sukses ke klien
 * 
 * @param string $message  Pesan deskriptif untuk klien
 * @param mixed  $data     Data yang dikembalikan (array/object), opsional
 * @param int    $code     HTTP Status Code (default: 200)
 */
function json_success($message, $data = null, $code = 200) {
    http_response_code($code);
    $response = [
        'status'  => 'success',
        'message' => $message
    ];
    // Hanya sertakan key 'data' jika memang ada data yang dikirim
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Kirim response JSON error ke klien
 * 
 * @param string $message  Pesan error yang mudah dipahami
 * @param int    $code     HTTP Status Code (default: 400)
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
 * Digunakan oleh endpoint POST untuk membaca input dari klien
 * 
 * @return array  Data JSON yang sudah di-decode menjadi array asosiatif
 */
function get_json_input() {
    // php://input membaca raw body dari HTTP request
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    
    // Jika body bukan JSON yang valid, kembalikan array kosong
    if (!is_array($data)) {
        return [];
    }
    return $data;
}

/**
 * Generate token simulasi (pengganti JWT sederhana)
 * Token = base64 dari payload + signature menggunakan HMAC-SHA256
 * 
 * @param int    $user_id  ID pengguna
 * @param string $role     Role pengguna (mahasiswa/dosen/admin)
 * @return string          Token string yang bisa dikirim ke klien
 */
function generate_token($user_id, $role) {
    // Payload berisi informasi user dan waktu kedaluwarsa
    $payload = json_encode([
        'user_id' => $user_id,
        'role'    => $role,
        'iat'     => time(),                    // Issued At: waktu token dibuat
        'exp'     => time() + TOKEN_EXPIRY      // Expiry: waktu token kedaluwarsa
    ]);
    
    // Encode payload ke base64
    $base64_payload = base64_encode($payload);
    
    // Buat signature menggunakan HMAC-SHA256 untuk mencegah manipulasi
    $signature = hash_hmac('sha256', $base64_payload, API_SECRET_KEY);
    
    // Gabungkan payload dan signature dengan pemisah titik (mirip format JWT)
    return $base64_payload . '.' . $signature;
}

/**
 * Verifikasi dan decode token dari header Authorization
 * 
 * @return array|null  Data payload jika token valid, null jika tidak
 */
function verify_token() {
    // Ambil header Authorization dari request
    $headers = getallheaders();
    $auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    // Cek apakah menggunakan format "Bearer <token>"
    if (!preg_match('/^Bearer\s+(.+)$/i', $auth_header, $matches)) {
        return null;
    }
    
    $token = $matches[1];
    
    // Pisahkan payload dan signature
    $parts = explode('.', $token);
    if (count($parts) !== 2) {
        return null;
    }
    
    $base64_payload = $parts[0];
    $signature      = $parts[1];
    
    // Verifikasi signature — pastikan token tidak dimanipulasi
    $expected_signature = hash_hmac('sha256', $base64_payload, API_SECRET_KEY);
    if (!hash_equals($expected_signature, $signature)) {
        return null;
    }
    
    // Decode payload
    $payload = json_decode(base64_decode($base64_payload), true);
    if (!$payload) {
        return null;
    }
    
    // Cek apakah token sudah kedaluwarsa
    if (isset($payload['exp']) && $payload['exp'] < time()) {
        return null;
    }
    
    return $payload;
}

/**
 * Middleware: Wajibkan autentikasi token sebelum mengakses endpoint
 * Jika token tidak valid, langsung kirim response 401 Unauthorized
 * 
 * @return array  Payload token yang berisi data user
 */
function require_auth() {
    $payload = verify_token();
    if (!$payload) {
        json_error('Token tidak valid atau telah kedaluwarsa. Silakan login ulang.', 401);
    }
    return $payload;
}
