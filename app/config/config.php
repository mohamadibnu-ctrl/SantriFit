<?php
// Timezone Indonesia (WIB)
date_default_timezone_set('Asia/Jakarta');

// ========================================================
// 1. Pemuatan File Environment (.env)
// ========================================================
$envFile = dirname(__DIR__, 2) . '/.env';
if (!file_exists($envFile)) {
    // Fallback ke direktori chatbot-server jika .env di root belum dibuat
    $envFile = dirname(__DIR__, 2) . '/chatbot-server/.env';
}

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            // Bersihkan kutip jika ada
            if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                $val = substr($val, 1, -1);
            }
            if (!getenv($key) && !isset($_ENV[$key])) {
                putenv("$key=$val");
                $_ENV[$key] = $val;
                $_SERVER[$key] = $val;
            }
        }
    }
}

// ========================================================
// 2. Base URL & Database Configuration
// ========================================================
define('BASEURL', 'http://localhost/SantriFit/public');

// DB Constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'santrifit_db');

// AI Configuration
define('AI_PROVIDER', 'nvidia'); // Options: 'mock', 'gemini', 'openai', 'nvidia'
define('AI_API_KEY', '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');

// ========================================================
// 3. Telegram Bot Configuration
// ========================================================
require_once __DIR__ . '/telegram.php';
