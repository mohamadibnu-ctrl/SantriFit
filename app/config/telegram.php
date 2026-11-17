<?php
/**
 * Konfigurasi Telegram Bot untuk SantriFit
 * 
 * Nilai konfigurasi dibaca secara dinamis dari file environment (.env).
 * Sesuai ketentuan keamanan:
 * - Token Telegram tidak ditulis langsung di source code.
 * - Token Telegram tidak disimpan di database.
 * - Token Telegram tidak diekspos ke frontend atau response API publik.
 */

// Helper function untuk mengambil nilai env dengan fallback bertingkat
if (!function_exists('telegram_env')) {
    function telegram_env($key, $default = null) {
        $value = getenv($key);
        if ($value === false || $value === null || $value === '') {
            $value = $_ENV[$key] ?? ($_SERVER[$key] ?? $default);
        }
        return $value;
    }
}

// 1. Definisi Konstanta Konfigurasi Telegram
define('TELEGRAM_ENABLED', filter_var(telegram_env('TELEGRAM_ENABLED', false), FILTER_VALIDATE_BOOLEAN));
define('TELEGRAM_BOT_TOKEN', (string) telegram_env('TELEGRAM_BOT_TOKEN', ''));
define('TELEGRAM_BOT_USERNAME', (string) telegram_env('TELEGRAM_BOT_USERNAME', ''));
define('TELEGRAM_NOTIFICATION_ENABLED', filter_var(telegram_env('TELEGRAM_NOTIFICATION_ENABLED', false), FILTER_VALIDATE_BOOLEAN));
define('TELEGRAM_CHATBOT_ENABLED', filter_var(telegram_env('TELEGRAM_CHATBOT_ENABLED', false), FILTER_VALIDATE_BOOLEAN));
define('TELEGRAM_WEBHOOK_URL', (string) telegram_env('TELEGRAM_WEBHOOK_URL', ''));
define('TELEGRAM_WEBHOOK_SECRET', (string) telegram_env('TELEGRAM_WEBHOOK_SECRET', ''));

// 2. Class Helper TelegramConfig untuk kemudahan akses di Controller / Service
class TelegramConfig {
    /**
     * Memeriksa apakah integrasi Telegram diaktifkan
     */
    public static function isEnabled(): bool {
        return defined('TELEGRAM_ENABLED') ? TELEGRAM_ENABLED : false;
    }

    /**
     * Mengambil Bot Token secara internal (hanya untuk request backend ke Telegram API)
     */
    public static function getBotToken(): string {
        return defined('TELEGRAM_BOT_TOKEN') ? TELEGRAM_BOT_TOKEN : '';
    }

    /**
     * Mengambil username bot Telegram (misal: SantriFitHealthBot)
     */
    public static function getBotUsername(): string {
        return defined('TELEGRAM_BOT_USERNAME') ? TELEGRAM_BOT_USERNAME : '';
    }

    /**
     * Memeriksa apakah fitur notifikasi via Telegram diaktifkan
     */
    public static function isNotificationEnabled(): bool {
        return defined('TELEGRAM_NOTIFICATION_ENABLED') ? TELEGRAM_NOTIFICATION_ENABLED : false;
    }

    /**
     * Memeriksa apakah fitur chatbot via Telegram diaktifkan
     */
    public static function isChatbotEnabled(): bool {
        return defined('TELEGRAM_CHATBOT_ENABLED') ? TELEGRAM_CHATBOT_ENABLED : false;
    }

    /**
     * Mengambil URL webhook Telegram
     */
    public static function getWebhookUrl(): string {
        return defined('TELEGRAM_WEBHOOK_URL') ? TELEGRAM_WEBHOOK_URL : '';
    }

    /**
     * Mengambil Secret Token webhook Telegram
     */
    public static function getWebhookSecret(): string {
        return defined('TELEGRAM_WEBHOOK_SECRET') ? TELEGRAM_WEBHOOK_SECRET : '';
    }

    /**
     * Mengembalikan status konfigurasi aman (TANPA mengekspos Bot Token)
     * Aman digunakan untuk logging status atau indikator internal.
     */
    public static function getPublicStatus(): array {
        return [
            'enabled' => self::isEnabled(),
            'bot_username' => self::getBotUsername(),
            'notification_enabled' => self::isNotificationEnabled(),
            'chatbot_enabled' => self::isChatbotEnabled(),
            'has_token' => !empty(self::getBotToken()),
            'has_webhook' => !empty(self::getWebhookUrl()),
            'has_secret' => !empty(self::getWebhookSecret())
        ];
    }
}
