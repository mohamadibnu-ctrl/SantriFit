<?php
/**
 * Model Telegram untuk SantriFit (STEP 4)
 * Menangani query database untuk akun telegram dan token linking sementara.
 */
class Telegram_model {
    private $tableAccounts = 'telegram_accounts';
    private $tableTokens = 'telegram_link_tokens';
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Mengambil akun Telegram berdasarkan user_id SantriFit
     */
    public function getAccountByUserId($userId) {
        $this->db->query("SELECT * FROM {$this->tableAccounts} WHERE user_id = :user_id LIMIT 1");
        $this->db->bind('user_id', $userId);
        return $this->db->single();
    }

    /**
     * Mengambil akun Telegram berdasarkan telegram_chat_id
     */
    public function getAccountByChatId($chatId) {
        $this->db->query("SELECT * FROM {$this->tableAccounts} WHERE telegram_chat_id = :chat_id LIMIT 1");
        $this->db->bind('chat_id', (string)$chatId);
        return $this->db->single();
    }

    /**
     * Mengambil token linking aktif untuk user (belum kedaluwarsa & belum digunakan)
     */
    public function getActiveTokenByUserId($userId) {
        $this->db->query("SELECT * FROM {$this->tableTokens} 
                          WHERE user_id = :user_id 
                            AND used_at IS NULL 
                            AND expires_at > NOW() 
                          ORDER BY created_at DESC LIMIT 1");
        $this->db->bind('user_id', $userId);
        return $this->db->single();
    }

    /**
     * Membuat token linking baru (kedaluwarsa dalam 15 menit)
     * Format: 'SF-' + 6 karakter alfanumerik acak (tanpa huruf/angka yang membingungkan)
     */
    public function createLinkToken($userId, $expiresMinutes = 15) {
        // Hanguskan token lama yang masih pending untuk user ini
        $this->db->query("UPDATE {$this->tableTokens} SET used_at = NOW() WHERE user_id = :user_id AND used_at IS NULL");
        $this->db->bind('user_id', $userId);
        $this->db->execute();

        // Karakter aman tanpa 0, O, 1, I agar mudah dibaca manusia
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $randomPart = '';
        for ($i = 0; $i < 6; $i++) {
            $randomPart .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $token = 'SF-' . $randomPart;

        $this->db->query("INSERT INTO {$this->tableTokens} (user_id, token, expires_at) 
                          VALUES (:user_id, :token, DATE_ADD(NOW(), INTERVAL :expires_minutes MINUTE))");
        $this->db->bind('user_id', $userId);
        $this->db->bind('token', $token);
        $this->db->bind('expires_minutes', (int)$expiresMinutes);
        $this->db->execute();

        return $this->getActiveTokenByUserId($userId);
    }

    /**
     * Mencari token linking yang valid dan belum kedaluwarsa
     */
    public function findValidToken($tokenString) {
        $token = strtoupper(trim($tokenString));
        $this->db->query("SELECT t.*, u.username 
                          FROM {$this->tableTokens} t
                          JOIN users u ON t.user_id = u.id
                          WHERE t.token = :token 
                            AND t.used_at IS NULL 
                            AND t.expires_at > NOW() 
                          LIMIT 1");
        $this->db->bind('token', $token);
        return $this->db->single();
    }

    /**
     * Menghubungkan akun SantriFit dengan akun Telegram
     */
    public function linkAccount($userId, $chatId, $username, $tokenId) {
        // 1. Validasi: Cek apakah chat_id sudah digunakan oleh user SantriFit lain
        $existing = $this->getAccountByChatId($chatId);
        if ($existing && (int)$existing['user_id'] !== (int)$userId) {
            return [
                'success' => false,
                'message' => 'Akun Telegram ini sudah terhubung dengan akun SantriFit lain.'
            ];
        }

        // 2. Simpan atau perbarui akun di telegram_accounts
        $userAccount = $this->getAccountByUserId($userId);
        if ($userAccount) {
            $this->db->query("UPDATE {$this->tableAccounts} 
                              SET telegram_chat_id = :chat_id, 
                                  telegram_username = :username, 
                                  is_verified = 1, 
                                  updated_at = NOW() 
                              WHERE user_id = :user_id");
            $this->db->bind('chat_id', (string)$chatId);
            $this->db->bind('username', $username);
            $this->db->bind('user_id', $userId);
            $this->db->execute();
        } else {
            $this->db->query("INSERT INTO {$this->tableAccounts} 
                              (user_id, telegram_chat_id, telegram_username, is_verified, created_at, updated_at) 
                              VALUES (:user_id, :chat_id, :username, 1, NOW(), NOW())");
            $this->db->bind('user_id', $userId);
            $this->db->bind('chat_id', (string)$chatId);
            $this->db->bind('username', $username);
            $this->db->execute();
        }

        // 3. Tandai token ini sebagai used
        $this->db->query("UPDATE {$this->tableTokens} SET used_at = NOW() WHERE id = :id");
        $this->db->bind('id', $tokenId);
        $this->db->execute();

        // 4. Hanguskan token pending lainnya milik user ini
        $this->db->query("UPDATE {$this->tableTokens} SET used_at = NOW() WHERE user_id = :user_id AND used_at IS NULL");
        $this->db->bind('user_id', $userId);
        $this->db->execute();

        return [
            'success' => true,
            'message' => 'Akun Telegram berhasil dihubungkan.'
        ];
    }

    /**
     * Memutuskan hubungan (Unlink) akun Telegram dari SantriFit
     */
    public function unlinkAccount($userId) {
        $this->db->query("DELETE FROM {$this->tableAccounts} WHERE user_id = :user_id");
        $this->db->bind('user_id', $userId);
        $this->db->execute();

        // Hanguskan juga token yang masih aktif
        $this->db->query("UPDATE {$this->tableTokens} SET used_at = NOW() WHERE user_id = :user_id AND used_at IS NULL");
        $this->db->bind('user_id', $userId);
        $this->db->execute();

        return true;
    }

    /**
     * Memutuskan hubungan (Unlink) akun Telegram berdasarkan telegram_chat_id
     */
    public function unlinkByChatId($chatId) {
        $account = $this->getAccountByChatId($chatId);
        if (!$account) {
            return false;
        }
        return $this->unlinkAccount($account['user_id']);
    }

    /**
     * Mengambil data profil aman pengguna SantriFit berdasarkan telegram_chat_id (STEP 5)
     */
    public function getUserProfileByChatId($chatId) {
        $this->db->query("SELECT u.id, u.username, u.created_at as user_created_at, 
                                 ta.telegram_chat_id, ta.telegram_username, ta.created_at as linked_at
                          FROM {$this->tableAccounts} ta
                          JOIN users u ON ta.user_id = u.id
                          WHERE ta.telegram_chat_id = :chat_id AND ta.is_verified = 1
                          LIMIT 1");
        $this->db->bind('chat_id', (string)$chatId);
        $user = $this->db->single();
        if (!$user) {
            return null;
        }

        // Ambil Roles
        $this->db->query("SELECT r.nama_role 
                          FROM user_roles ur 
                          JOIN roles r ON ur.role_id = r.id 
                          WHERE ur.user_id = :user_id");
        $this->db->bind('user_id', $user['id']);
        $roles = $this->db->resultSet();
        $user['roles'] = array_column($roles, 'nama_role');

        // Jika user memiliki role Santri, ambil detail santri
        if (in_array('Santri', $user['roles'])) {
            $this->db->query("SELECT nis, nama, kamar, kelas FROM santri WHERE user_id = :user_id LIMIT 1");
            $this->db->bind('user_id', $user['id']);
            $santriDetail = $this->db->single();
            if ($santriDetail) {
                $user['santri'] = $santriDetail;
            }
        }

        return $user;
    }

    /**
     * Mengirim pesan konfirmasi ke Telegram (hanya digunakan untuk proses linking)
     */
    public function sendTelegramMessage($chatId, $text) {
        $botToken = TelegramConfig::getBotToken();
        if (empty($botToken)) {
            return false;
        }

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
