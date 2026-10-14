<?php
require_once '../app/services/AiService.php';

class AiAssistant extends Controller {
    public function __construct() {
        AuthMiddleware::check(); 
    }

    public function index() {
        $data['judul'] = 'Asisten AI SantriFit';
        
        $this->view('templates/header', $data);
        $this->view('ai/index', $data);
        $this->view('templates/footer');
    }

    public function analyze() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $input = $_POST['prompt'] ?? '';
            
            $rawInput = file_get_contents('php://input');
            $json = json_decode($rawInput, true);
            if ($json && isset($json['prompt'])) {
                $input = $json['prompt'];
            }

            $aiService = new AiService();
            $result = $aiService->analyze($input);
            
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'result' => $result
            ]);
            exit;
        }
    }
}
