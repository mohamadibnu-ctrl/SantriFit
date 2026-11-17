<?php
/**
 * REST API to check & auto-start SantriFit Node.js Chatbot Server
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$port = 3001;
$host = '127.0.0.1';

// Function to check if chatbot port is open & responding
function isChatbotRunning($host, $port) {
    $fp = @fsockopen($host, $port, $errno, $errstr, 0.3);
    if ($fp) {
        fclose($fp);
        return true;
    }
    return false;
}

// 1. If already running, return immediately
if (isChatbotRunning($host, $port)) {
    echo json_encode([
        'success' => true,
        'status' => 'online',
        'message' => 'Server chatbot sudah aktif dan siap digunakan.',
        'port' => $port,
        'already_running' => true
    ]);
    exit;
}

// 2. If not running, start the server
$chatbotDir = realpath(__DIR__ . '/../chatbot-server');
$serverJs = $chatbotDir . DIRECTORY_SEPARATOR . 'server.js';
$vbsScript = $chatbotDir . DIRECTORY_SEPARATOR . 'run-server-hidden.vbs';

// Deteksi executable node.exe
$nodePaths = glob('C:/laragon/bin/nodejs/*/node.exe');
$nodeBin = (!empty($nodePaths) && file_exists($nodePaths[0])) ? $nodePaths[0] : null;

if (!$nodeBin) {
    if (file_exists('C:\\Program Files\\nodejs\\node.exe')) {
        $nodeBin = 'C:\\Program Files\\nodejs\\node.exe';
    } elseif (file_exists('C:\\Program Files (x86)\\nodejs\\node.exe')) {
        $nodeBin = 'C:\\Program Files (x86)\\nodejs\\node.exe';
    } else {
        $nodeBin = 'node';
    }
}

// Update run-server-hidden.vbs with active paths if needed
$vbsContent = 'Set WshShell = CreateObject("WScript.Shell")' . "\r\n";
$vbsContent .= 'WshShell.CurrentDirectory = "' . str_replace('/', '\\', $chatbotDir) . '"' . "\r\n";
$vbsContent .= 'nodeBin = "' . str_replace('/', '\\', $nodeBin) . '"' . "\r\n";
$vbsContent .= 'serverJs = "' . str_replace('/', '\\', $serverJs) . '"' . "\r\n";
$vbsContent .= 'cmd = Chr(34) & nodeBin & Chr(34) & " " & Chr(34) & serverJs & Chr(34)' . "\r\n";
$vbsContent .= 'WshShell.Run cmd, 0, False' . "\r\n";
@file_put_contents($vbsScript, $vbsContent);

// Trigger wscript to run the detached hidden process
$cmd = 'wscript.exe "' . str_replace('/', '\\', $vbsScript) . '"';
@pclose(@popen($cmd, 'r'));

// Tunggu hingga 1.5 detik agar server sempat listen
$started = false;
for ($i = 0; $i < 6; $i++) {
    usleep(250000); // 250ms
    if (isChatbotRunning($host, $port)) {
        $started = true;
        break;
    }
}

echo json_encode([
    'success' => true,
    'status' => $started ? 'online' : 'starting',
    'message' => $started ? 'Server chatbot berhasil dijalankan secara otomatis.' : 'Server chatbot sedang proses startup.',
    'port' => $port,
    'already_running' => false
]);
