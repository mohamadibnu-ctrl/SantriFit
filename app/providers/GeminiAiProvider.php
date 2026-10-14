<?php
require_once 'AiProviderInterface.php';

class GeminiAiProvider implements AiProviderInterface {
    private $apiKey;
    
    public function __construct() {
        if (!defined('AI_API_KEY') || empty(AI_API_KEY) || AI_API_KEY === 'MASUKKAN_API_KEY_ANDA_DI_SINI') {
            throw new Exception("API Key Gemini belum diatur di config.php");
        }
        $this->apiKey = AI_API_KEY;
    }

    public function analyzeSymptoms(string $prompt): string {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $this->apiKey;
        
        $systemPrompt = "Anda adalah AI Assistant kesehatan untuk aplikasi SantriFit di sebuah pondok pesantren. "
                      . "Tugas Anda adalah merangkum keluhan pasien, memberikan informasi edukasi, "
                      . "dan memberikan saran tindak lanjut secara hati-hati.\n\n"
                      . "PENTING: Jangan berikan diagnosis pasti. Format jawaban dalam Markdown dengan subjudul: "
                      . "1. 📋 Ringkasan Gejala\n2. 💡 Informasi Edukasi\n3. 🚨 RED FLAG (jika ada kondisi gawat darurat)\n4. 👨‍⚕️ Saran Tindak Lanjut\n";
                      
        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $systemPrompt . "\n\nKeluhan pasien: " . $prompt]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.2
            ]
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
                'ignore_errors' => true
            ]
        ];

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            throw new Exception("Gagal terhubung ke API Gemini.");
        }
        
        $response = json_decode($result, true);
        
        if (isset($response['error'])) {
            throw new Exception("API Error: " . $response['error']['message']);
        }
        
        if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            return $response['candidates'][0]['content']['parts'][0]['text'];
        }
        
        return "Gagal mendapatkan respon dari AI.";
    }
}
