<?php
// config/supabase.php
// Konfigurasi dan Helper Koneksi Supabase REST API

// Fungsi untuk membaca file .env secara manual (untuk pengembangan lokal)
function loadSupabaseEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Abaikan komentar
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        // Pisahkan nama dan nilai variabel
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Set ke env jika belum ada
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// Load env dari root project
loadSupabaseEnv(dirname(__DIR__) . '/.env');

/**
 * Mengirimkan HTTP Request ke Supabase REST API
 * 
 * @param string $method GET, POST, PATCH, DELETE
 * @param string $endpoint Nama tabel atau path endpoint (misalnya 'penjualan')
 * @param array|null $data Payload data untuk POST/PATCH
 * @return array Response code dan array data dari Supabase
 */
function supabaseRequest($method, $endpoint, $data = null) {
    $url = ($_ENV['SUPABASE_URL'] ?? getenv('SUPABASE_URL') ?? '');
    $key = ($_ENV['SUPABASE_KEY'] ?? getenv('SUPABASE_KEY') ?? '');
    
    if (empty($url) || empty($key)) {
        return [
            'code' => 500,
            'data' => ['error' => 'Supabase URL atau API Key tidak ditemukan. Pastikan sudah dikonfigurasi di Environment Variables.']
        ];
    }
    
    // Pastikan URL diakhiri dengan rest/v1/
    $fullUrl = rtrim($url, '/') . '/rest/v1/' . $endpoint;
    
    $ch = curl_init($fullUrl);
    
    $headers = [
        'apikey: ' . $key,
        'Authorization: Bearer ' . $key,
        'Content-Type: application/json',
        'Prefer: return=representation' // Memaksa Supabase mengembalikan data hasil modifikasi
    ];
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return [
            'code' => 500,
            'data' => ['error' => 'cURL Error: ' . $error_msg]
        ];
    }
    
    curl_close($ch);
    return [
        'code' => $httpCode,
        'data' => json_decode($response, true)
    ];
}
?>
