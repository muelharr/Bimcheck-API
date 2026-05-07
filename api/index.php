<?php
/**
 * ============================================================================
 * API ROUTER — Pintu Masuk Utama Web Service BimCheck
 * ============================================================================
 * 
 * File ini berfungsi sebagai router sederhana yang mengarahkan request HTTP
 * ke endpoint yang sesuai berdasarkan parameter 'action' di URL.
 * 
 * Daftar Route:
 *   POST /api/?action=login    → api/auth.php
 *   POST /api/?action=checkin  → api/checkin.php
 *   GET  /api/?action=history  → api/history.php
 *   GET  /api/                 → Halaman info API
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$action = strtolower(trim($_GET['action'] ?? ''));

switch ($action) {
    case 'login':
        require_once __DIR__ . '/auth.php';
        break;
    
    case 'checkin':
        require_once __DIR__ . '/checkin.php';
        break;
    
    case 'history':
        require_once __DIR__ . '/history.php';
        break;
    
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
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        break;
}
