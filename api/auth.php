<?php
/**
 * ============================================================================
 * API AUTH — Endpoint Autentikasi Mahasiswa
 * ============================================================================
 * 
 * Endpoint : POST /api/auth.php
 * Fungsi   : Memverifikasi login mahasiswa dan mengembalikan token akses
 * 
 * Alur Kerja (IPO):
 *   INPUT   → Menerima JSON { "npm": "...", "password": "..." }
 *   PROCESS → Cari mahasiswa di DB berdasarkan NPM, verifikasi password
 *   OUTPUT  → Response JSON berisi token jika berhasil, atau pesan error
 * 
 * Contoh Request (Postman):
 *   POST http://localhost/Bimcheck/api/auth.php
 *   Body (raw JSON):
 *   {
 *       "npm": "714240020",
 *       "password": "password_anda"
 *   }
 */

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method tidak diizinkan. Gunakan POST.', 405);
}

$input = get_json_input();

$npm      = trim($input['npm'] ?? '');
$password = $input['password'] ?? '';

if (empty($npm) || empty($password)) {
    json_error('NPM dan password wajib diisi.', 400);
}

$mahasiswa = db_fetch(
    $conn,
    "SELECT id_mahasiswa, npm, nama, prodi, password FROM mahasiswa WHERE npm = ?",
    's',
    [$npm]
);

if (!$mahasiswa) {
    json_error('NPM atau password salah.', 401);
}

if (!password_verify($password, $mahasiswa['password'])) {
    json_error('NPM atau password salah.', 401);
}

$token = generate_token($mahasiswa['id_mahasiswa'], 'mahasiswa');

json_success('Login berhasil.', [
    'token' => $token,
    'user'  => [
        'id'    => (int) $mahasiswa['id_mahasiswa'],
        'npm'   => $mahasiswa['npm'],
        'nama'  => $mahasiswa['nama'],
        'prodi' => $mahasiswa['prodi'],
        'role'  => 'mahasiswa'
    ]
]);
