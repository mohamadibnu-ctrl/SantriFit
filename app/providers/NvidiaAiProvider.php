<?php
require_once 'AiProviderInterface.php';

class NvidiaAiProvider implements AiProviderInterface {
    private $apiKey;
    
    public function __construct() {
        if (!defined('AI_API_KEY') || empty(AI_API_KEY) || AI_API_KEY === 'MASUKKAN_API_KEY_ANDA_DI_SINI') {
            throw new Exception("API Key Nvidia belum diatur di config.php");
        }
        $this->apiKey = AI_API_KEY;
    }

    public function analyzeSymptoms(string $prompt): string {
        $url = "https://integrate.api.nvidia.com/v1/chat/completions";
        
        $systemPrompt = "Anda adalah AI Assistant kesehatan untuk aplikasi SantriFit di sebuah pondok pesantren. "
                      . "Tugas Anda adalah merangkum keluhan pasien, memberikan informasi edukasi, "
                      . "dan memberikan saran tindak lanjut secara hati-hati.\n\n"
                      . "PENTING: Jangan berikan diagnosis pasti. Format jawaban dalam Markdown dengan subjudul: "
                      . "1. 📋 Ringkasan Gejala\n2. 💡 Informasi Edukasi\n3. 🚨 RED FLAG (jika ada kondisi gawat darurat)\n4. 👨‍⚕️ Saran Tindak Lanjut\n";
                      
        $data = [
            "model" => "openai/gpt-oss-20b", 
            "messages" => [
                [
                    "role" => "system",
                    "content" => $systemPrompt
                ],
                [
                    "role" => "user",
                    "content" => "Keluhan pasien: " . $prompt
                ]
            ],
            "temperature" => 0.2,
            "max_tokens" => 1024
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n" .
                             "Authorization: Bearer " . $this->apiKey . "\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
                'ignore_errors' => true
            ]
        ];

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            throw new Exception("Gagal terhubung ke API Nvidia.");
        }
        
        $response = json_decode($result, true);
        
        if (isset($response['error'])) {
            throw new Exception("API Error: " . ($response['error']['message'] ?? json_encode($response['error'])));
        }
        
        if (isset($response['choices'][0]['message']['content'])) {
            return $response['choices'][0]['message']['content'];
        }
        
        return "Gagal mendapatkan respon dari AI Nvidia.";
    }
}
