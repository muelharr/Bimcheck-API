<?php
/**
 * ============================================================================
 * API HISTORY — Endpoint Riwayat Bimbingan Mahasiswa
 * ============================================================================
 * 
 * Endpoint : GET /api/history.php?mahasiswa_id=4
 * Fungsi   : Menampilkan daftar riwayat bimbingan mahasiswa dalam array JSON
 * Auth     : Wajib menyertakan token di header Authorization
 * 
 * Alur Kerja (IPO):
 *   INPUT   → Parameter query string: mahasiswa_id
 *   PROCESS → Verifikasi token → Query tabel antrian JOIN dosen → Filter selesai
 *   OUTPUT  → Array JSON berisi daftar riwayat bimbingan
 * 
 * Contoh Request (Postman):
 *   GET http://localhost/Bimcheck/api/history.php?mahasiswa_id=4
 *   Headers:
 *     Authorization: Bearer <token_dari_login>
 */

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Method tidak diizinkan. Gunakan GET.', 405);
}

$auth = require_auth();

$mahasiswa_id = (int) ($_GET['mahasiswa_id'] ?? $auth['user_id'] ?? 0);

if ($mahasiswa_id <= 0) {
    json_error('Parameter mahasiswa_id wajib diisi dan harus berupa angka positif.', 400);
}

$mahasiswa = db_fetch(
    $conn,
    "SELECT id_mahasiswa, npm, nama, prodi FROM mahasiswa WHERE id_mahasiswa = ?",
    'i',
    [$mahasiswa_id]
);

if (!$mahasiswa) {
    json_error('Data mahasiswa tidak ditemukan.', 404);
}

$riwayat = db_fetch_all(
    $conn,
    "SELECT 
        a.id_antrian,
        a.nomor_antrian,
        a.tanggal,
        a.waktu_mulai,
        a.waktu_kehadiran,
        a.waktu_selesai,
        a.topik,
        a.deskripsi,
        a.catatan_dosen,
        a.status,
        d.id_dosen,
        d.nama_dosen,
        d.keahlian AS keahlian_dosen
     FROM antrian a 
     JOIN dosen d ON a.id_dosen = d.id_dosen 
     WHERE a.id_mahasiswa = ? 
       AND a.status IN ('selesai', 'dilewati', 'dibatalkan')
     ORDER BY a.tanggal DESC, a.waktu_mulai DESC",
    'i',
    [$mahasiswa_id]
);

$data_riwayat = [];
foreach ($riwayat as $row) {
    $data_riwayat[] = [
        'id_antrian'      => (int) $row['id_antrian'],
        'nomor_antrian'   => (int) $row['nomor_antrian'],
        'tanggal'         => $row['tanggal'],
        'waktu_mulai'     => $row['waktu_mulai'],
        'waktu_kehadiran' => $row['waktu_kehadiran'],
        'waktu_selesai'   => $row['waktu_selesai'],
        'topik'           => $row['topik'],
        'deskripsi'       => $row['deskripsi'],
        'catatan_dosen'   => $row['catatan_dosen'],
        'status'          => $row['status'],
        'dosen' => [
            'id'       => (int) $row['id_dosen'],
            'nama'     => $row['nama_dosen'],
            'keahlian' => $row['keahlian_dosen']
        ]
    ];
}

json_success('Riwayat bimbingan berhasil diambil.', [
    'mahasiswa' => [
        'id'    => (int) $mahasiswa['id_mahasiswa'],
        'npm'   => $mahasiswa['npm'],
        'nama'  => $mahasiswa['nama'],
        'prodi' => $mahasiswa['prodi']
    ],
    'total_riwayat' => count($data_riwayat),
    'riwayat'       => $data_riwayat
]);
