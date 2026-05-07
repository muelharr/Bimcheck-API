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

// Load konfigurasi API (header JSON, koneksi DB, fungsi helper)
require_once __DIR__ . '/config.php';

// ─── VALIDASI METHOD ────────────────────────────────────────────────────────
// Endpoint ini HANYA menerima method POST
// GET tidak diizinkan karena kita mengirim data sensitif (password)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method tidak diizinkan. Gunakan POST.', 405);
}

// ─── AMBIL INPUT JSON ───────────────────────────────────────────────────────
$input = get_json_input();

$npm      = trim($input['npm'] ?? '');
$password = $input['password'] ?? '';

// ─── VALIDASI INPUT ─────────────────────────────────────────────────────────
// Pastikan kedua field tidak kosong sebelum query ke database
if (empty($npm) || empty($password)) {
    json_error('NPM dan password wajib diisi.', 400);
}

// ─── PROSES AUTENTIKASI ─────────────────────────────────────────────────────
// Cari data mahasiswa berdasarkan NPM menggunakan prepared statement
// Prepared statement mencegah SQL Injection
$mahasiswa = db_fetch(
    $conn,
    "SELECT id_mahasiswa, npm, nama, prodi, password FROM mahasiswa WHERE npm = ?",
    's',
    [$npm]
);

// Cek 1: Apakah NPM terdaftar di database?
if (!$mahasiswa) {
    json_error('NPM atau password salah.', 401);
}

// Cek 2: Apakah password cocok dengan hash di database?
// password_verify() membandingkan plaintext dengan hash bcrypt
if (!password_verify($password, $mahasiswa['password'])) {
    json_error('NPM atau password salah.', 401);
}

// ─── GENERATE TOKEN ─────────────────────────────────────────────────────────
// Jika autentikasi berhasil, buat token untuk akses endpoint lainnya
// Token ini harus disertakan di header Authorization untuk request berikutnya
$token = generate_token($mahasiswa['id_mahasiswa'], 'mahasiswa');

// ─── KIRIM RESPONSE SUKSES ─────────────────────────────────────────────────
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
