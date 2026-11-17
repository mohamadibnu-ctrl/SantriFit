<?php
/**
 * Controller Telegram Webhook, Commands, & Chatbot SantriFit (STEP 3, 4, 5, & 6)
 * 
 * Bertanggung jawab menerima dan memvalidasi update webhook dari server Telegram,
 * memproses kode linking akun pengguna (STEP 4), mengeksekusi command Telegram (STEP 5),
 * serta menangani pesan percakapan chatbot SantriFit (STEP 6).
 */
class Telegram extends Controller {
    public function index() {
        $this->webhook();
    }

    /**
     * Endpoint Webhook Telegram
     * Menerima GET untuk pengecekan status kesiapan webhook,
     * dan POST untuk menerima update dari Telegram.
     */
    public function webhook() {
        header('Content-Type: application/json; charset=utf-8');

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // 1. Endpoint Pemeriksaan Status (GET)
        if ($method === 'GET') {
            http_response_code(200);
            echo json_encode([
                'status' => 'ready',
                'service' => 'SantriFit Telegram Webhook',
                'telegram_enabled' => TelegramConfig::isEnabled(),
                'bot_username' => TelegramConfig::getBotUsername(),
                'chatbot_enabled' => TelegramConfig::isChatbotEnabled(),
                'webhook_url_configured' => !empty(TelegramConfig::getWebhookUrl()),
                'webhook_secret_configured' => !empty(TelegramConfig::getWebhookSecret()),
                'timestamp' => date('c')
            ]);
            exit;
        }

        // 2. Endpoint Penerima Update Telegram (POST)
        if ($method === 'POST') {
            // Pastikan Telegram diaktifkan di konfigurasi
            if (!TelegramConfig::isEnabled()) {
                http_response_code(403);
                echo json_encode([
                    'status' => 'disabled',
                    'message' => 'Integrasi Telegram dinonaktifkan di konfigurasi SantriFit.'
                ]);
                exit;
            }

            // Validasi Secret Token Webhook (jika dikonfigurasi di .env)
            $expectedSecret = TelegramConfig::getWebhookSecret();
            if (!empty($expectedSecret)) {
                $incomingSecret = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';
                if (!hash_equals($expectedSecret, $incomingSecret)) {
                    $this->logSafe('Webhook ditolak: Secret token tidak valid', [
                        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                    ]);
                    http_response_code(403);
                    echo json_encode([
                        'status' => 'unauthorized',
                        'message' => 'Akses ditolak: Secret token tidak cocok.'
                    ]);
                    exit;
                }
            }

            // Membaca Raw Body Request dari Telegram
            $rawInput = file_get_contents('php://input');
            if (empty($rawInput)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'bad_request',
                    'message' => 'Body request kosong.'
                ]);
                exit;
            }

            // Parsing dan Validasi Format JSON
            $update = json_decode($rawInput, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($update)) {
                $this->logSafe('Webhook error: Format JSON tidak valid', [
                    'json_error' => json_last_error_msg()
                ]);
                http_response_code(400);
                echo json_encode([
                    'status' => 'bad_request',
                    'message' => 'Format payload JSON tidak valid.'
                ]);
                exit;
            }

            // Deteksi Tipe Update Telegram secara aman
            $updateId = $update['update_id'] ?? null;
            $updateType = 'unknown';
            $chatId = null;

            if (isset($update['message'])) {
                $updateType = 'message';
                $chatId = $update['message']['chat']['id'] ?? null;
            } elseif (isset($update['edited_message'])) {
                $updateType = 'edited_message';
                $chatId = $update['edited_message']['chat']['id'] ?? null;
            } elseif (isset($update['callback_query'])) {
                $updateType = 'callback_query';
                $chatId = $update['callback_query']['message']['chat']['id'] ?? null;
            } elseif (isset($update['my_chat_member'])) {
                $updateType = 'my_chat_member';
                $chatId = $update['my_chat_member']['chat']['id'] ?? null;
            }

            // Pemrosesan Pesan Masuk
            if ($updateType === 'message' && !empty($update['message']['text'])) {
                $rawText = trim($update['message']['text']);
                $fromUser = $update['message']['from'] ?? [];
                $telegramUsername = $fromUser['username'] ?? null;

                // A. STEP 4: Deteksi dan Proses Token Linking (Format: SF-XXXXXX atau /start SF-XXXXXX)
                $extractedToken = null;
                if (preg_match('/(?:^|\s)(SF-[A-Z0-9]{6})(?:\s|$)/i', $rawText, $matches)) {
                    $extractedToken = strtoupper($matches[1]);
                }

                if ($extractedToken !== null) {
                    $telegramModel = $this->model('Telegram_model');
                    $validToken = $telegramModel->findValidToken($extractedToken);

                    if ($validToken) {
                        $targetUserId = (int)$validToken['user_id'];
                        $targetUsername = $validToken['username'];
                        $linkResult = $telegramModel->linkAccount($targetUserId, $chatId, $telegramUsername, $validToken['id']);

                        if ($linkResult['success']) {
                            $this->logSafe('Akun Telegram berhasil dihubungkan ke SantriFit', [
                                'user_id' => $targetUserId,
                                'chat_id' => $chatId
                            ]);

                            $confirmText = "✅ *Akun SantriFit Berhasil Terhubung!*\n\n"
                                         . "Halo *" . htmlspecialchars($targetUsername) . "*,\n"
                                         . "Akun Telegram Anda telah resmi terhubung dengan akun SantriFit Anda.\n\n"
                                         . "Gunakan command /help untuk melihat fitur yang tersedia, atau Anda dapat langsung bertanya kepada saya seputar SantriFit.";
                            $telegramModel->sendTelegramMessage($chatId, $confirmText);

                            http_response_code(200);
                            echo json_encode([
                                'status' => 'ok',
                                'action' => 'account_linked',
                                'user_id' => $targetUserId
                            ]);
                            exit;
                        } else {
                            $this->logSafe('Gagal linking akun: ' . $linkResult['message'], [
                                'chat_id' => $chatId
                            ]);

                            $errorMsg = "⚠️ *Gagal Menghubungkan Akun*\n\n" . $linkResult['message'];
                            $telegramModel->sendTelegramMessage($chatId, $errorMsg);

                            http_response_code(200);
                            echo json_encode([
                                'status' => 'rejected',
                                'message' => $linkResult['message']
                            ]);
                            exit;
                        }
                    } else {
                        $this->logSafe('Percobaan linking dengan token tidak valid / kedaluwarsa', [
                            'chat_id' => $chatId
                        ]);

                        $invalidMsg = "⚠️ *Kode Linking Tidak Valid atau Kedaluwarsa*\n\n"
                                    . "Kode linking yang Anda masukkan salah atau masa berlakunya telah habis.\n\n"
                                    . "Silakan buat kode linking baru melalui menu *Hubungkan Telegram* di website SantriFit.";
                        $telegramModel->sendTelegramMessage($chatId, $invalidMsg);

                        http_response_code(200);
                        echo json_encode([
                            'status' => 'invalid_token'
                        ]);
                        exit;
                    }
                }

                // B. STEP 5: Routing Command Telegram (/start, /help, /status, /profile, /unlink)
                if (str_starts_with($rawText, '/')) {
                    $telegramModel = $this->model('Telegram_model');
                    $this->dispatchCommand($rawText, $chatId, $telegramModel);
                    exit;
                }

                // C. STEP 6: Chatbot Telegram SantriFit (Pesan Teks Biasa tanpa '/')
                $telegramModel = $this->model('Telegram_model');
                $this->handleChatbotMessage($rawText, $chatId, $telegramModel);
                exit;
            }

            // Logging aman untuk debugging (HANYA metadata teknis aman, tanpa data sensitif)
            $this->logSafe('Update Telegram berhasil diterima', [
                'update_id' => $updateId,
                'update_type' => $updateType,
                'chat_id' => $chatId
            ]);

            // Mengembalikan HTTP 200 OK ke server Telegram
            http_response_code(200);
            echo json_encode([
                'status' => 'ok',
                'message' => 'Update Telegram berhasil diterima oleh SantriFit Webhook.',
                'update_id' => $updateId,
                'type' => $updateType
            ]);
            exit;
        }

        // Method selain GET dan POST tidak diizinkan
        http_response_code(405);
        echo json_encode([
            'status' => 'method_not_allowed',
            'message' => 'Metode request tidak diizinkan. Gunakan GET atau POST.'
        ]);
        exit;
    }

    /**
     * Dispatcher untuk routing command Telegram (STEP 5)
     */
    private function dispatchCommand($rawText, $chatId, $telegramModel) {
        $parts = explode(' ', trim($rawText));
        $commandRaw = $parts[0];
        // Normalisasi: hilangkan sufiks username bot seperti /help@SantriFitHealthBot
        $command = strtolower(explode('@', $commandRaw)[0]);

        try {
            switch ($command) {
                case '/start':
                    $this->handleStartCommand($chatId, $telegramModel);
                    break;
                case '/help':
                    $this->handleHelpCommand($chatId, $telegramModel);
                    break;
                case '/status':
                    $this->handleStatusCommand($chatId, $telegramModel);
                    break;
                case '/profile':
                    $this->handleProfileCommand($chatId, $telegramModel);
                    break;
                case '/unlink':
                    $this->handleUnlinkCommand($chatId, $telegramModel);
                    break;
                default:
                    $this->handleUnknownCommand($chatId, $telegramModel);
                    break;
            }
        } catch (Exception $e) {
            $this->logSafe('Error saat memproses command', [
                'command' => $command,
                'chat_id' => $chatId,
                'error' => $e->getMessage()
            ]);
            $fallbackMsg = "Maaf, terjadi kesalahan saat memproses permintaan.\nSilakan coba lagi.";
            $telegramModel->sendTelegramMessage($chatId, $fallbackMsg);

            http_response_code(200);
            echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan sistem.']);
        }
    }

    /**
     * Handler untuk command /start
     */
    private function handleStartCommand($chatId, $telegramModel) {
        $account = $telegramModel->getAccountByChatId($chatId);
        $isLinked = $account && (int)$account['is_verified'] === 1;

        if ($isLinked) {
            $msg = "Halo! 👋\n"
                 . "Akun Telegram Anda sudah terhubung dengan SantriFit.\n\n"
                 . "Gunakan /help untuk melihat command yang tersedia, atau Anda dapat langsung mengirim pesan pertanyaan kepada saya.";
        } else {
            $msg = "Halo! 👋\n"
                 . "Selamat datang di SantriFit.\n\n"
                 . "Akun Telegram Anda belum terhubung.\n"
                 . "Silakan hubungkan akun SantriFit terlebih dahulu.";
        }

        $telegramModel->sendTelegramMessage($chatId, $msg);
        $this->logSafe('Command /start diproses', ['chat_id' => $chatId, 'is_linked' => $isLinked]);

        http_response_code(200);
        echo json_encode(['status' => 'ok', 'command' => '/start', 'is_linked' => $isLinked]);
    }

    /**
     * Handler untuk command /help
     */
    private function handleHelpCommand($chatId, $telegramModel) {
        $msg = "📋 *Command SantriFit*\n\n"
             . "/start - Memulai bot\n"
             . "/help - Melihat daftar command\n"
             . "/status - Melihat status akun\n"
             . "/profile - Melihat informasi akun\n"
             . "/unlink - Memutuskan koneksi Telegram\n\n"
             . "💡 *Chatbot*: Anda juga dapat langsung mengetikkan pertanyaan tentang SantriFit (contoh: *'Apa itu SantriFit?'*, *'Siapa nama saya?'*, *'Bagaimana status akun saya?'*).";

        $telegramModel->sendTelegramMessage($chatId, $msg);
        $this->logSafe('Command /help diproses', ['chat_id' => $chatId]);

        http_response_code(200);
        echo json_encode(['status' => 'ok', 'command' => '/help']);
    }

    /**
     * Handler untuk command /status
     */
    private function handleStatusCommand($chatId, $telegramModel) {
        $account = $telegramModel->getAccountByChatId($chatId);
        $isLinked = $account && (int)$account['is_verified'] === 1;

        if ($isLinked) {
            $msg = "Status Telegram:\n"
                 . "✅ *Terhubung*\n\n"
                 . "Akun SantriFit Anda sudah terhubung dengan Telegram.";
        } else {
            $msg = "Status Telegram:\n"
                 . "❌ *Belum terhubung*\n\n"
                 . "Silakan hubungkan akun SantriFit terlebih dahulu.";
        }

        $telegramModel->sendTelegramMessage($chatId, $msg);
        $this->logSafe('Command /status diproses', ['chat_id' => $chatId, 'is_linked' => $isLinked]);

        http_response_code(200);
        echo json_encode(['status' => 'ok', 'command' => '/status', 'is_linked' => $isLinked]);
    }

    /**
     * Handler untuk command /profile
     */
    private function handleProfileCommand($chatId, $telegramModel) {
        $profile = $telegramModel->getUserProfileByChatId($chatId);

        if ($profile) {
            $username = $profile['username'];
            $nama = $profile['santri']['nama'] ?? null;

            $msg = "👤 *Profil SantriFit*\n\n";
            if ($nama) {
                $msg .= "Nama: " . htmlspecialchars($nama) . "\n";
            }
            $msg .= "Username: " . htmlspecialchars($username) . "\n"
                 . "Status Telegram: Terhubung";
        } else {
            $msg = "Akun Telegram Anda belum terhubung dengan SantriFit.";
        }

        $telegramModel->sendTelegramMessage($chatId, $msg);
        $this->logSafe('Command /profile diproses', ['chat_id' => $chatId, 'is_linked' => !empty($profile)]);

        http_response_code(200);
        echo json_encode(['status' => 'ok', 'command' => '/profile', 'is_linked' => !empty($profile)]);
    }

    /**
     * Handler untuk command /unlink
     */
    private function handleUnlinkCommand($chatId, $telegramModel) {
        $account = $telegramModel->getAccountByChatId($chatId);
        $isLinked = $account && (int)$account['is_verified'] === 1;

        if ($isLinked) {
            $telegramModel->unlinkByChatId($chatId);
            $msg = "✅ Akun Telegram berhasil diputuskan dari SantriFit.";
            $actionResult = 'unlinked';
        } else {
            $msg = "Akun Telegram Anda belum terhubung.";
            $actionResult = 'not_linked';
        }

        $telegramModel->sendTelegramMessage($chatId, $msg);
        $this->logSafe('Command /unlink diproses', ['chat_id' => $chatId, 'result' => $actionResult]);

        http_response_code(200);
        echo json_encode(['status' => 'ok', 'command' => '/unlink', 'result' => $actionResult]);
    }

    /**
     * Handler untuk command yang tidak dikenali
     */
    private function handleUnknownCommand($chatId, $telegramModel) {
        $msg = "Command tidak dikenali.\n\n"
             . "Gunakan /help untuk melihat command yang tersedia.";

        $telegramModel->sendTelegramMessage($chatId, $msg);
        $this->logSafe('Command tidak dikenal diterima', ['chat_id' => $chatId]);

        http_response_code(200);
        echo json_encode(['status' => 'ok', 'command' => 'unknown']);
    }

    /**
     * Handler untuk Pesan Chatbot Telegram (STEP 6)
     * Memproses pesan biasa tanpa awalan '/'
     */
    private function handleChatbotMessage($rawText, $chatId, $telegramModel) {
        // 1. Cek apakah chatbot Telegram diaktifkan di konfigurasi
        if (!TelegramConfig::isChatbotEnabled()) {
            $disabledMsg = "Layanan chatbot Telegram saat ini sedang dinonaktifkan.";
            $telegramModel->sendTelegramMessage($chatId, $disabledMsg);
            http_response_code(200);
            echo json_encode(['status' => 'chatbot_disabled']);
            return;
        }

        // 2. Validasi Akun Telegram di database
        $account = $telegramModel->getAccountByChatId($chatId);
        $isLinked = $account && (int)$account['is_verified'] === 1;

        if (!$isLinked) {
            $notLinkedMsg = "Akun Telegram Anda belum terhubung dengan SantriFit.\n\n"
                          . "Silakan hubungkan akun terlebih dahulu.";
            $telegramModel->sendTelegramMessage($chatId, $notLinkedMsg);
            $this->logSafe('Chatbot ditolak: Akun belum terhubung', ['chat_id' => $chatId]);

            http_response_code(200);
            echo json_encode(['status' => 'not_linked', 'chat_id' => $chatId]);
            return;
        }

        // 3. Ambil data profil aman pengguna yang sedang terhubung
        $userProfile = $telegramModel->getUserProfileByChatId($chatId);
        if (!$userProfile) {
            $errorMsg = "Data akun SantriFit Anda tidak ditemukan.\nSilakan hubungi administrator.";
            $telegramModel->sendTelegramMessage($chatId, $errorMsg);
            http_response_code(200);
            echo json_encode(['status' => 'user_not_found']);
            return;
        }

        // 4. Generate jawaban chatbot yang relevan dan aman
        $reply = $this->generateChatbotResponse($rawText, $userProfile);

        // 5. Kirim respon ke Telegram
        $telegramModel->sendTelegramMessage($chatId, $reply);
        $this->logSafe('Chatbot merespons pesan user', [
            'chat_id' => $chatId,
            'user_id' => $userProfile['id']
        ]);

        http_response_code(200);
        echo json_encode([
            'status' => 'ok',
            'action' => 'chatbot_replied',
            'user_id' => $userProfile['id']
        ]);
    }

    /**
     * Logika respon chatbot terarah & aman (STEP 6)
     */
    private function generateChatbotResponse($rawText, array $user) {
        $lower = mb_strtolower(trim($rawText));
        $clean = preg_replace('/[?!.,]/u', '', $lower);
        $clean = trim(preg_replace('/\s+/', ' ', $clean));

        // 1. Perlindungan Privasi: Menolak pertanyaan tentang data akun / pengguna lain
        if (preg_match('/(user|pengguna|santri|orang) lain/i', $clean) ||
            preg_match('/(data|akun|siapa).*lain/i', $clean) ||
            preg_match('/semua (user|pengguna|santri|akun)/i', $clean) ||
            preg_match('/daftar (user|pengguna)/i', $clean)) {
            return "⚠️ *Pemberitahuan Privasi*: Demi menjaga keamanan dan kerahasiaan data pengguna, saya tidak dapat membagikan informasi atau data pribadi pengguna lain.";
        }

        // 2. Pertanyaan Identitas Diri (Nama / Siapa Saya)
        if (preg_match('/^(siapa|apakah) (nama )?(saya|aku|diriku)/i', $clean) ||
            preg_match('/^(nama )?(saya|aku) siapa/i', $clean) ||
            preg_match('/^siapakah (saya|aku)/i', $clean)) {
            $nama = $user['santri']['nama'] ?? null;
            $username = htmlspecialchars($user['username']);
            $roles = !empty($user['roles']) ? implode(', ', $user['roles']) : 'Pengguna';
            
            $res = "Nama Anda di sistem SantriFit adalah *" . ($nama ? htmlspecialchars($nama) . " (@{$username})" : $username) . "*";
            $res .= " dengan hak akses sebagai *" . htmlspecialchars($roles) . "*.";
            return $res;
        }

        // 3. Status Akun Sendiri
        if (preg_match('/status akun( saya)?/i', $clean) || 
            preg_match('/(bagaimana|gimana) status akun/i', $clean) ||
            preg_match('/apakah akun saya aktif/i', $clean)) {
            return "Status akun SantriFit Anda (*" . htmlspecialchars($user['username']) . "*) saat ini **Aktif** dan telah terhubung resmi dengan akun Telegram ini (Terverifikasi).";
        }

        // 4. Status Koneksi Telegram
        if (preg_match('/(apakah|status) telegram( saya)? (sudah )?terhubung/i', $clean) ||
            preg_match('/telegram( saya)? terhubung/i', $clean) ||
            preg_match('/koneksi telegram/i', $clean)) {
            $tgUser = !empty($user['telegram_username']) ? '@' . htmlspecialchars($user['telegram_username']) : 'Akun ini';
            return "Ya, akun Telegram Anda ({$tgUser}) sudah **Terhubung dan Terverifikasi** dengan akun SantriFit (*" . htmlspecialchars($user['username']) . "*).";
        }

        // 5. Cara Melihat Profil
        if (preg_match('/(cara|bagaimana|gimana|mau) (melihat|lihat|cek|buka) profil/i', $clean) ||
            preg_match('/(lihat|cek|buka) profil saya/i', $clean)) {
            return "Anda dapat melihat rincian profil akun Anda dengan mengirimkan command /profile pada bot ini, atau dengan membuka menu **Profil Saya** di website SantriFit.";
        }

        // 6. Apa itu SantriFit / Fitur-fitur SantriFit
        if (preg_match('/apa itu santrifit/i', $clean) ||
            preg_match('/tentang santrifit/i', $clean) ||
            preg_match('/santrifit itu apa/i', $clean) ||
            preg_match('/(apa saja |daftar )?fitur( santrifit)?/i', $clean) ||
            preg_match('/fitur apa saja/i', $clean)) {
            return "🏥 *SantriFit* adalah Sistem Manajemen Kesehatan Terpadu untuk Pondok Pesantren.\n\n"
                 . "*Fitur-fitur utama SantriFit:*\n"
                 . "1. 📋 *Data Santri*: Pendataan profil dan rekam data santri.\n"
                 . "2. 🩺 *Triase & Pemeriksaan*: Pencatatan keluhan dan tanda vital kesehatan santri.\n"
                 . "3. 💊 *Rekomendasi & Resep*: Manajemen diagnosis dokter dan peresepan obat.\n"
                 . "4. 📦 *Data Obat*: Monitoring stok dan inventaris obat di klinik poskestren.\n"
                 . "5. 🤖 *Chatbot & Telegram*: Asisten interaktif dan notifikasi terpadu.\n"
                 . "6. 📊 *Laporan Kesehatan*: Rekapitulasi grafik dan data riwayat medis santri.";
        }

        // 7. Sapaan / Greeting
        if (preg_match('/^(halo|hai|hey|hei|assalamu[\'\w]*|selamat (pagi|siang|sore|malam))/i', $clean)) {
            return "Halo *" . htmlspecialchars($user['username']) . "*! 👋 Ada yang bisa saya bantu seputar informasi akun atau layanan kesehatan SantriFit?";
        }

        // 8. Identitas Bot
        if (preg_match('/^(kamu|anda|bot ini) siapa/i', $clean) ||
            preg_match('/^siapa (kamu|anda|bot ini)/i', $clean) ||
            preg_match('/^(kamu|bot) ini apa/i', $clean)) {
            return "Saya adalah **Chatbot Resmi SantriFit**, asisten virtual untuk membantu Anda mendapatkan informasi layanan kesehatan dan akun Anda di SantriFit.";
        }

        // 9. Bantuan / Help
        if (preg_match('/^(help|bantuan|tolong|perintah|command)/i', $clean)) {
            return "Ketik /help untuk melihat daftar command resmi yang dapat Anda gunakan di bot SantriFit.";
        }

        // 10. Fallback: Hubungi AI Lokal jika tersedia atau berikan panduan relevan
        $aiReply = $this->callLocalAiChatbot($rawText, $user);
        if (!empty($aiReply)) {
            return $aiReply;
        }

        // 11. Respon Default Terarah
        return "Terima kasih atas pesan Anda. Anda dapat menanyakan tentang akun Anda (contoh: *'Siapa nama saya?'*, *'Bagaimana status akun saya?'*), informasi sistem (contoh: *'Apa itu SantriFit?'*, *'Apa saja fitur SantriFit?'*), atau ketik /help untuk melihat daftar command yang tersedia.";
    }

    /**
     * Menghubungi service AI lokal terintegrasi (jika server AI aktif)
     */
    private function callLocalAiChatbot($message, array $user) {
        $url = 'http://127.0.0.1:3001/api/chat';
        $payload = json_encode([
            'message' => $message,
            'user' => [
                'id' => $user['id'] ?? 1,
                'username' => $user['username'] ?? 'User',
                'roles' => $user['roles'] ?? ['Pengguna']
            ]
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200 && $res) {
            $json = json_decode($res, true);
            if (!empty($json['reply'])) {
                return $json['reply'];
            }
        }
        return null;
    }

    /**
     * Helper logging aman: hanya mencatat metadata teknis tanpa membocorkan token / password
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
