<?php
require_once 'AiProviderInterface.php';

class MockAiProvider implements AiProviderInterface {
    public function analyzeSymptoms(string $prompt): string {
        sleep(2);
        
        $lowercasePrompt = strtolower($prompt);
        $redFlag = false;
        
        if (strpos($lowercasePrompt, 'darah') !== false || 
            strpos($lowercasePrompt, 'pingsan') !== false || 
            strpos($lowercasePrompt, 'sesak') !== false ||
            strpos($lowercasePrompt, 'kejang') !== false ||
            strpos($lowercasePrompt, 'tidak sadar') !== false) {
            $redFlag = true;
        }

        $result = "### 📋 Ringkasan Gejala\n";
        $result .= "Berdasarkan informasi yang diberikan, gejala utama yang dikeluhkan adalah terkait: _" . htmlspecialchars($prompt) . "_\n\n";
        
        $result .= "### 💡 Informasi Edukasi\n";
        $result .= "Secara umum, keluhan semacam ini memerlukan istirahat yang cukup dan hidrasi yang baik. Perhatikan apakah ada gejala penyerta lain seperti demam tinggi atau nyeri hebat.\n\n";
        
        if ($redFlag) {
            $result .= "### 🚨 RED FLAG (Peringatan Darurat)\n";
            $result .= "**Terdapat tanda bahaya (Red Flag) pada keluhan ini.** Segera lakukan observasi ketat atau rujuk pasien untuk mendapatkan penanganan gawat darurat dari tenaga medis profesional.\n\n";
        } else {
            $result .= "### 👨‍⚕️ Saran Tindak Lanjut\n";
            $result .= "Disarankan untuk melakukan pemeriksaan triase (suhu, tensi, berat badan) di klinik dan didokumentasikan di sistem untuk direview oleh dokter.\n\n";
        }

        return $result;
    }
}
