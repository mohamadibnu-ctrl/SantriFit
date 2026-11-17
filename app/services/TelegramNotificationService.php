<?php
/**
 * Service Notifikasi Telegram SantriFit (STEP 7)
 * 
 * Bertanggung jawab mengirimkan notifikasi terarah ke akun Telegram pengguna SantriFit
 * yang telah terverifikasi dan terhubung di database.
 * 
 * Sesuai ketentuan keselamatan STEP 7:
 * - Hanya mengirim ke akun dengan is_verified = 1 dan telegram_chat_id valid.
 * - Tidak mengirim broadcast atau spam.
 * - Bot Token dibaca dinamis dari .env (tidak disimpan di database / log).
 * - Menangani kegagalan Telegram API secara graceful tanpa menyebabkan aplikasi crash.
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/telegram.php';
require_once dirname(__DIR__) . '/models/Telegram_model.php';

class TelegramNotificationService {
    private $telegramModel;

    public function __construct(?Telegram_model $model = null) {
        $this->telegramModel = $model ?? new Telegram_model();
    }

    /**
     * Mengirim notifikasi ke user tertentu berdasarkan user_id SantriFit
     * 
     * @param int $userId ID pengguna SantriFit
     * @param string $message Isi pesan notifikasi
     * @param string $category Kategori: 'info', 'activity', 'reminder', 'medical'
     * @param string|null $title Judul notifikasi opsional
     * @return array Status hasil pengiriman ['success' => bool, 'message' => string, ...]
     */
    public function sendToUser($userId, $message, $category = 'info', $title = null) {
        // 1. Cek apakah notifikasi Telegram diaktifkan di konfigurasi
        if (!TelegramConfig::isNotificationEnabled()) {
            return [
                'success' => false,
                'status' => 'disabled',
                'message' => 'Notifikasi Telegram dinonaktifkan di konfigurasi SantriFit.'
            ];
        }

        // 2. Cek ketersediaan Bot Token
        $botToken = TelegramConfig::getBotToken();
        if (empty($botToken)) {
            $this->logSafe('Telegram notification skipped: Bot token tidak tersedia', [
                'user_id' => $userId
            ]);
            return [
                'success' => false,
                'status' => 'no_token',
                'message' => 'Bot Token Telegram tidak tersedia.'
            ];
        }

        // 3. Validasi Akun Telegram di database
        $account = $this->telegramModel->getAccountByUserId($userId);

        // Syarat mutlak: akun ada, terverifikasi (is_verified = 1), dan chat_id tidak kosong
        if (!$account || (int)($account['is_verified'] ?? 0) !== 1 || empty($account['telegram_chat_id'])) {
            return [
                'success' => false,
                'status' => 'not_connected',
                'message' => 'Pengguna belum menghubungkan akun Telegram atau belum terverifikasi.'
            ];
        }

        $chatId = $account['telegram_chat_id'];

        // 4. Format Pesan Sesuai Kategori
        $formattedText = $this->formatMessage($message, $category, $title);

        // 5. Kirim via Telegram API dengan penanganan error aman
        try {
            $response = $this->telegramModel->sendTelegramMessage($chatId, $formattedText);

            if ($response === false) {
                $this->logSafe("Telegram notification failed for user ID: {$userId}", [
                    'chat_id' => $chatId,
                    'error' => 'cURL error or network unreachable'
                ]);
                return [
                    'success' => false,
                    'status' => 'network_error',
                    'message' => 'Gagal terhubung ke Telegram API.'
                ];
            }

            $json = json_decode($response, true);
            if (isset($json['ok']) && $json['ok'] === true) {
                $this->logSafe("Notifikasi Telegram berhasil dikirim", [
                    'user_id' => $userId,
                    'category' => $category,
                    'chat_id' => $chatId
                ]);
                return [
                    'success' => true,
                    'status' => 'sent',
                    'message' => 'Notifikasi Telegram berhasil dikirim.',
                    'chat_id' => $chatId,
                    'category' => $category
                ];
            } else {
                $errMsg = $json['description'] ?? 'Telegram API error';
                $this->logSafe("Telegram notification failed for user ID: {$userId}", [
                    'chat_id' => $chatId,
                    'error' => $errMsg
                ]);
                return [
                    'success' => false,
                    'status' => 'api_error',
                    'message' => "Telegram API: {$errMsg}"
                ];
            }
        } catch (Exception $e) {
            $this->logSafe("Telegram notification exception for user ID: {$userId}", [
                'chat_id' => $chatId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'status' => 'exception',
                'message' => 'Terjadi kesalahan sistem saat mengirim notifikasi.'
            ];
        }
    }

    /**
     * Helper Shortcut: Notifikasi Informasi
     */
    public function sendInfo($userId, $message, $title = null) {
        return $this->sendToUser($userId, $message, 'info', $title);
    }

    /**
     * Helper Shortcut: Notifikasi Aktivitas Akun
     */
    public function sendActivity($userId, $message, $title = null) {
        return $this->sendToUser($userId, $message, 'activity', $title);
    }

    /**
     * Helper Shortcut: Notifikasi Pengingat
     */
    public function sendReminder($userId, $message, $title = null) {
        return $this->sendToUser($userId, $message, 'reminder', $title);
    }

    /**
     * Helper Shortcut: Notifikasi Medis / Kesehatan
     */
    public function sendMedical($userId, $message, $title = null) {
        return $this->sendToUser($userId, $message, 'medical', $title);
    }

    /**
     * Memformat teks pesan Telegram dengan header dan emoji sesuai kategori
     */
    public function formatMessage($message, $category = 'info', $title = null) {
        switch (strtolower($category)) {
            case 'activity':
            case 'perhatian':
                $header = "🔔 *Aktivitas Akun SantriFit*";
                break;
            case 'reminder':
            case 'pengingat':
                $header = "⏰ *Pengingat SantriFit*";
                break;
            case 'medical':
            case 'pemeriksaan':
            case 'rekomendasi':
            case 'obat':
                $header = "🩺 *Layanan Kesehatan SantriFit*";
                break;
            case 'info':
            case 'sistem':
            default:
                $header = "ℹ️ *Informasi SantriFit*";
                break;
        }

        $formatted = "{$header}\n\n";
        if (!empty($title)) {
            $formatted .= "*" . htmlspecialchars(strip_tags($title)) . "*\n";
        }
        $formatted .= strip_tags($message) . "\n\n";
        $formatted .= "—\n_Sistem Manajemen Kesehatan SantriFit_";

        return $formatted;
    }

    /**
     * Helper logging aman: hanya mencatat ID dan pesan error teknis, tidak pernah mencatat Bot Token
     */
    private function logSafe($message, array $context = []) {
        $logDir = dirname(__DIR__) . '/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/telegram_webhook.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $line = "[{$timestamp}] {$message}{$contextStr}" . PHP_EOL;
        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
