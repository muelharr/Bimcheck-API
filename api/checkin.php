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
 * Logika ini diekstrak dari: actions/validasi_qr.php
 * Perbedaan utama:
 *   - Menggunakan token (bukan session) untuk autentikasi
 *   - Input berupa mahasiswa_id (bukan dari session)
 *   - Output murni JSON (tanpa HTML/alert)
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

// Load konfigurasi API
require_once __DIR__ . '/config.php';

// ─── VALIDASI METHOD ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method tidak diizinkan. Gunakan POST.', 405);
}

// ─── AUTENTIKASI TOKEN ──────────────────────────────────────────────────────
// Middleware: cek token di header Authorization
// Jika token invalid/expired, fungsi ini langsung mengirim response 401
$auth = require_auth();

// ─── AMBIL INPUT JSON ───────────────────────────────────────────────────────
$input = get_json_input();

$mahasiswa_id = (int) ($input['mahasiswa_id'] ?? 0);
$qr_code      = trim($input['qr_code'] ?? '');

// ─── VALIDASI INPUT ─────────────────────────────────────────────────────────
if ($mahasiswa_id <= 0) {
    json_error('Field mahasiswa_id wajib diisi dan harus berupa angka positif.', 400);
}

if (empty($qr_code)) {
    json_error('Field qr_code wajib diisi.', 400);
}

// ─── VALIDASI MAHASISWA ─────────────────────────────────────────────────────
// Pastikan mahasiswa_id benar-benar ada di database
$mahasiswa = db_fetch(
    $conn,
    "SELECT id_mahasiswa, npm, nama FROM mahasiswa WHERE id_mahasiswa = ?",
    'i',
    [$mahasiswa_id]
);

if (!$mahasiswa) {
    json_error('Data mahasiswa tidak ditemukan.', 404);
}

// ─── PARSE QR CODE ──────────────────────────────────────────────────────────
// Format QR Code dari dosen: "id_dosen|timestamp"
// Contoh: "5|83920" artinya dosen ID 5, timestamp 83920
//
// Timestamp dihitung dengan: floor(time() / (5 * 60))
// Artinya QR Code berubah setiap 5 menit untuk keamanan

if (strpos($qr_code, '|') !== false) {
    // QR Code dengan time-based token (format baru, lebih aman)
    list($id_dosen, $qr_timestamp) = explode('|', $qr_code, 2);
    $id_dosen = (int) $id_dosen;
    
    // Hitung timestamp saat ini dengan interval 5 menit
    $current_timestamp = floor(time() / (5 * 60));
    
    // Hitung selisih waktu antara QR dan waktu sekarang
    $time_diff = abs($current_timestamp - intval($qr_timestamp));
    
    // Toleransi: QR Code berlaku ±10 menit (2 interval × 5 menit)
    // Jika selisih > 2 interval, berarti QR sudah kedaluwarsa
    if ($time_diff > 2) {
        json_error('QR Code sudah kedaluwarsa. Minta dosen untuk refresh QR Code.', 400);
    }
} else {
    // QR Code sederhana tanpa timestamp (format lama, backward compatible)
    $id_dosen = (int) $qr_code;
}

// Validasi: id_dosen harus berupa angka positif
if ($id_dosen <= 0) {
    json_error('Format QR Code tidak valid.', 400);
}

// ─── CEK JADWAL ANTRIAN DI DATABASE ─────────────────────────────────────────
// Cari antrian yang cocok dengan kriteria:
//   1. Mahasiswa yang bersangkutan (mahasiswa_id)
//   2. Dosen yang QR Code-nya di-scan (id_dosen dari QR)
//   3. Tanggal hari ini (CURDATE())
//   4. Status masih 'menunggu' atau 'dipanggil' (belum diproses)
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

// ─── PROSES CHECK-IN ────────────────────────────────────────────────────────
if ($antrian) {
    $id_antrian = $antrian['id_antrian'];
    
    // Update status antrian menjadi 'proses' dan catat waktu kehadiran
    // Ini menandakan bahwa mahasiswa telah hadir dan bimbingan dimulai
    $waktu_kehadiran = date('Y-m-d H:i:s');
    
    $updated = db_update($conn, 'antrian', [
        'status'          => 'proses',
        'waktu_kehadiran' => $waktu_kehadiran
    ], 'id_antrian', $id_antrian);
    
    if ($updated !== false) {
        // ─── CATAT RIWAYAT PERUBAHAN STATUS ─────────────────────────────
        // Insert ke tabel riwayat_status untuk audit trail
        db_insert($conn, 'riwayat_status', [
            'id_antrian'       => $id_antrian,
            'status_lama'      => $antrian['status'],
            'status_baru'      => 'proses',
            'waktu_perubahan'  => $waktu_kehadiran,
            'keterangan'       => 'Check-in via API QR Code'
        ]);
        
        // ─── KIRIM RESPONSE SUKSES ──────────────────────────────────────
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
    // ─── HANDLE KASUS ANTRIAN TIDAK DITEMUKAN ───────────────────────────
    // Cek apakah mahasiswa punya booking dengan dosen ini di tanggal lain
    // Ini membantu memberikan pesan error yang lebih informatif
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
        // Ada booking tapi bukan hari ini
        $tanggal_formatted = date('d/m/Y', strtotime($booking_lain['tanggal']));
        json_error(
            "Booking Anda dengan dosen ini dijadwalkan tanggal {$tanggal_formatted}, " .
            "bukan hari ini. QR Code hanya bisa di-scan pada tanggal booking yang sesuai.",
            404
        );
    } else {
        // Tidak ada booking sama sekali
        json_error(
            'Tidak ada jadwal bimbingan hari ini dengan dosen tersebut. ' .
            'Pastikan Anda sudah booking dan statusnya menunggu/dipanggil.',
            404
        );
    }
}
