<?php
/**
 * ============================================================================
 * API ROUTER — Pintu Masuk Utama Web Service BimCheck
 * ============================================================================
 * 
 * File ini berfungsi sebagai router sederhana yang mengarahkan request HTTP
 * ke endpoint yang sesuai berdasarkan parameter 'action' di URL.
 * 
 * Cara Kerja:
 *   URL yang masuk dibaca parameternya, lalu di-route ke file PHP yang tepat.
 * 
 * Daftar Route:
 *   POST /api/?action=login    → api/auth.php     (Login & dapatkan token)
 *   POST /api/?action=checkin  → api/checkin.php   (Validasi QR & check-in)
 *   GET  /api/?action=history  → api/history.php   (Ambil riwayat bimbingan)
 *   GET  /api/                 → Halaman info API  (Dokumentasi ringkas)
 * 
 * Contoh Penggunaan:
 *   http://localhost/Bimcheck/api/?action=login
 *   http://localhost/Bimcheck/api/?action=checkin
 *   http://localhost/Bimcheck/api/?action=history&mahasiswa_id=4
 * 
 * Atau langsung akses file:
 *   http://localhost/Bimcheck/api/auth.php
 *   http://localhost/Bimcheck/api/checkin.php
 *   http://localhost/Bimcheck/api/history.php?mahasiswa_id=4
 */

// ─── HEADER JSON ────────────────────────────────────────────────────────────
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ─── BACA PARAMETER ACTION ──────────────────────────────────────────────────
// Action menentukan endpoint mana yang akan dieksekusi
$action = strtolower(trim($_GET['action'] ?? ''));

// ─── ROUTING ────────────────────────────────────────────────────────────────
// Switch-case untuk mengarahkan request ke file yang sesuai
switch ($action) {
    
    // Route: Login mahasiswa untuk mendapatkan token
    case 'login':
        require_once __DIR__ . '/auth.php';
        break;
    
    // Route: Validasi QR Code dan catat kehadiran
    case 'checkin':
        require_once __DIR__ . '/checkin.php';
        break;
    
    // Route: Ambil riwayat bimbingan mahasiswa
    case 'history':
        require_once __DIR__ . '/history.php';
        break;
    
    // ─── DEFAULT: HALAMAN INFO API ──────────────────────────────────────
    // Jika tidak ada action yang dikirim, tampilkan informasi API
    // Ini berguna sebagai dokumentasi ringkas yang bisa diakses langsung
    default:
        http_response_code(200);
        echo json_encode([
            'status'  => 'success',
            'message' => 'BimCheck API v1.0 — Web Service Validasi QR Code Bimbingan Mahasiswa',
            'info'    => [
                'deskripsi' => 'API ini menyediakan layanan validasi QR Code untuk sistem antrian bimbingan skripsi.',
                'versi'     => '1.0.0',
                'teknologi' => 'PHP 8.1+ / MySQL 8.0 / JSON'
            ],
            'endpoints' => [
                [
                    'method'      => 'POST',
                    'url'         => '/api/?action=login',
                    'deskripsi'   => 'Login mahasiswa dan dapatkan token akses.',
                    'auth'        => false,
                    'body'        => ['npm' => 'string', 'password' => 'string']
                ],
                [
                    'method'      => 'POST',
                    'url'         => '/api/?action=checkin',
                    'deskripsi'   => 'Validasi QR Code dosen dan catat kehadiran mahasiswa.',
                    'auth'        => true,
                    'body'        => ['mahasiswa_id' => 'integer', 'qr_code' => 'string']
                ],
                [
                    'method'      => 'GET',
                    'url'         => '/api/?action=history&mahasiswa_id={id}',
                    'deskripsi'   => 'Ambil daftar riwayat bimbingan mahasiswa.',
                    'auth'        => true,
                    'parameters'  => ['mahasiswa_id' => 'integer (opsional, default dari token)']
                ]
            ],
            'catatan' => [
                'Semua endpoint yang memerlukan auth harus menyertakan header: Authorization: Bearer <token>',
                'Token didapat dari endpoint login dan berlaku selama 24 jam.',
                'Semua request dan response menggunakan format JSON.',
                'Dokumentasi lengkap tersedia di file swagger.yaml'
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        break;
}
