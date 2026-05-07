<?php
/**
 * ============================================================================
 * API CHECKIN — Endpoint Validasi QR Code & Pencatatan Kehadiran
 * ============================================================================
 * 
 * Endpoint : POST /api/checkin.php
 * Fungsi   : Memvalidasi QR Code dosen dan mencatat kehadiran mahasiswa
 * Auth     : Wajib menyertakan token di header Authorization
 * 
 * Alur Kerja (IPO):
 *   INPUT   → JSON { "mahasiswa_id": 4, "qr_code": "5|83920" }
 *   PROCESS → Verifikasi token → Parse QR → Cek timestamp → Query DB → Update status
 *   OUTPUT  → JSON { "status": "success", "message": "Presensi Berhasil!" }
 * 
 * Contoh Request (Postman):
 *   POST http://localhost/Bimcheck/api/checkin.php
 *   Headers:
 *     Authorization: Bearer <token_dari_login>
 *     Content-Type: application/json
 *   Body (raw JSON):
 *   {
 *       "mahasiswa_id": 4,
 *       "qr_code": "5|83920"
 *   }
 */

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method tidak diizinkan. Gunakan POST.', 405);
}

$auth = require_auth();

$input = get_json_input();
$mahasiswa_id = (int) ($input['mahasiswa_id'] ?? 0);
$qr_code      = trim($input['qr_code'] ?? '');

if ($mahasiswa_id <= 0) {
    json_error('Field mahasiswa_id wajib diisi dan harus berupa angka positif.', 400);
}

if (empty($qr_code)) {
    json_error('Field qr_code wajib diisi.', 400);
}

$mahasiswa = db_fetch(
    $conn,
    "SELECT id_mahasiswa, npm, nama FROM mahasiswa WHERE id_mahasiswa = ?",
    'i',
    [$mahasiswa_id]
);

if (!$mahasiswa) {
    json_error('Data mahasiswa tidak ditemukan.', 404);
}

// Parse Time-based QR (format: id_dosen|timestamp) atau format lama (id_dosen)
if (strpos($qr_code, '|') !== false) {
    list($id_dosen, $qr_timestamp) = explode('|', $qr_code, 2);
    $id_dosen = (int) $id_dosen;
    
    $current_timestamp = floor(time() / (5 * 60));
    $time_diff = abs($current_timestamp - intval($qr_timestamp));
    
    if ($time_diff > 2) {
        json_error('QR Code sudah kedaluwarsa. Minta dosen untuk refresh QR Code.', 400);
    }
} else {
    $id_dosen = (int) $qr_code;
}

if ($id_dosen <= 0) {
    json_error('Format QR Code tidak valid.', 400);
}

$antrian = db_fetch(
    $conn,
    "SELECT a.*, d.nama_dosen 
     FROM antrian a 
     JOIN dosen d ON a.id_dosen = d.id_dosen
     WHERE a.id_mahasiswa = ? 
       AND a.id_dosen = ? 
       AND a.tanggal = CURDATE()
       AND a.status IN ('menunggu', 'dipanggil')",
    'ii',
    [$mahasiswa_id, $id_dosen]
);

if ($antrian) {
    $id_antrian = $antrian['id_antrian'];
    $waktu_kehadiran = date('Y-m-d H:i:s');
    
    $updated = db_update($conn, 'antrian', [
        'status'          => 'proses',
        'waktu_kehadiran' => $waktu_kehadiran
    ], 'id_antrian', $id_antrian);
    
    if ($updated !== false) {
        db_insert($conn, 'riwayat_status', [
            'id_antrian'       => $id_antrian,
            'status_lama'      => $antrian['status'],
            'status_baru'      => 'proses',
            'waktu_perubahan'  => $waktu_kehadiran,
            'keterangan'       => 'Check-in via API QR Code'
        ]);
        
        json_success('Presensi Berhasil!', [
            'id_antrian'       => (int) $id_antrian,
            'mahasiswa'        => $mahasiswa['nama'],
            'dosen'            => $antrian['nama_dosen'],
            'nomor_antrian'    => (int) $antrian['nomor_antrian'],
            'topik'            => $antrian['topik'],
            'status'           => 'proses',
            'waktu_kehadiran'  => $waktu_kehadiran
        ]);
    } else {
        json_error('Gagal memperbarui database. Silakan coba lagi.', 500);
    }
    
} else {
    // Cari jadwal bimbingan di tanggal lain untuk pesan error yang spesifik
    $booking_lain = db_fetch(
        $conn,
        "SELECT tanggal, status FROM antrian 
         WHERE id_mahasiswa = ? 
           AND id_dosen = ? 
           AND status IN ('menunggu', 'dipanggil') 
         ORDER BY tanggal ASC LIMIT 1",
        'ii',
        [$mahasiswa_id, $id_dosen]
    );
    
    if ($booking_lain) {
        $tanggal_formatted = date('d/m/Y', strtotime($booking_lain['tanggal']));
        json_error(
            "Booking Anda dengan dosen ini dijadwalkan tanggal {$tanggal_formatted}, " .
            "bukan hari ini. QR Code hanya bisa di-scan pada tanggal booking yang sesuai.",
            404
        );
    } else {
        json_error(
            'Tidak ada jadwal bimbingan hari ini dengan dosen tersebut. ' .
            'Pastikan Anda sudah booking dan statusnya menunggu/dipanggil.',
            404
        );
    }
}
