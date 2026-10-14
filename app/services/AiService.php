<?php
require_once '../app/providers/MockAiProvider.php';
require_once '../app/providers/NvidiaAiProvider.php';

class AiService {
    private $provider;

    public function __construct() {
        if (!defined('AI_PROVIDER')) {
            $this->provider = new MockAiProvider();
        } else if (AI_PROVIDER === 'nvidia') {
            $this->provider = new NvidiaAiProvider();
        } else {
            $this->provider = new MockAiProvider();
        }
    }

    public function analyze(string $input): string {
        if (empty(trim($input))) {
            return "Error: Keluhan tidak boleh kosong.";
        }
        
        $sanitizedInput = strip_tags($input);
        
        // System Prompt logic would go here if using a real API
        // e.g. "You are SantriFit AI. Do NOT diagnose. Do NOT prescribe medications..."

        $response = $this->provider->analyzeSymptoms($sanitizedInput);
        
        $response .= "\n---\n";
        $response .= "> **⚠️ DISCLAIMER KESEHATAN SISTEM:**\n";
        $response .= "> Informasi di atas dihasilkan oleh kecerdasan buatan (AI) sebatas untuk tujuan edukasi dan administrasi triase. **AI ini BUKAN tenaga medis** dan dilarang digunakan untuk menentukan diagnosis pasti atau peresepan obat. Selalu patuhi standar operasional pelayanan kesehatan di klinik.";

        return $response;
    }
}
