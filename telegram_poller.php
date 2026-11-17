<?php
/**
 * Telegram Long-Polling Daemon untuk Localhost SantriFit
 * Mengambil update dari server Telegram secara real-time dan meneruskannya ke Webhook lokal.
 */
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/telegram.php';

$token = TelegramConfig::getBotToken();
if (empty($token)) {
    die("Bot Token tidak ditemukan di .env\n");
}

echo "Telegram Polling Daemon SantriFit aktif...\n";
$offsetFile = __DIR__ . '/app/logs/telegram_offset.txt';
$offset = file_exists($offsetFile) ? (int)file_get_contents($offsetFile) : 0;

while (true) {
    $url = "https://api.telegram.org/bot{$token}/getUpdates?offset={$offset}&timeout=20";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        sleep(2);
        continue;
    }

    $data = json_decode($res, true);
    if (!empty($data['result'])) {
        foreach ($data['result'] as $update) {
            $offset = $update['update_id'] + 1;
            file_put_contents($offsetFile, (string)$offset);

            // Teruskan update ke Webhook lokal
            $webhookUrl = "http://127.0.0.1/SantriFit/public/telegram/webhook";
            $chHook = curl_init($webhookUrl);
            curl_setopt($chHook, CURLOPT_POST, true);
            curl_setopt($chHook, CURLOPT_POSTFIELDS, json_encode($update));
            curl_setopt($chHook, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($chHook, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($chHook, CURLOPT_TIMEOUT, 10);
            curl_exec($chHook);
            curl_close($chHook);
        }
    }

    usleep(500000); // 0.5 detik
}
