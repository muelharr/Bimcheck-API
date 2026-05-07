<?php
// Database Connection dengan Error Handling

// Database credentials
$host = "localhost";
$user = "root";
$pass = "";
$db = "bimcheck";

// Set mysqli error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Buat koneksi dengan timeout
    $conn = new mysqli($host, $user, $pass, $db);
    
    // Set connection timeout (5 detik)
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    
    // Set charset ke UTF-8
    $conn->set_charset("utf8mb4");
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
} catch (Exception $e) {
    // Log error (jangan tampilkan ke user)
    error_log("Database Connection Error: " . $e->getMessage());
    
    // Tampilkan pesan user-friendly
    die("Maaf, sistem sedang mengalami gangguan. Silakan coba lagi nanti.");
}

// Helper function untuk escape string (untuk backward compatibility)
// NOTE: Sebaiknya gunakan prepared statements!
function db_escape($conn, $string) {
    return $conn->real_escape_string($string);
}
?>