<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use GuzzleHttp\Client;

if (file_exists(__DIR__ . '/.env')) {
    Dotenv::createImmutable(__DIR__)->safeLoad();
}

$mediaUrl = $_POST['file'] ?? $_GET['file'] ?? null;

if (!$mediaUrl || !filter_var($mediaUrl, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Invalid or missing media URL parameter.'], JSON_PRETTY_PRINT);
    exit;
}

// Validate host domain for security (only allow Instagram & Facebook CDN URLs)
$parsed = parse_url($mediaUrl);
$host = strtolower($parsed['host'] ?? '');

$allowedHostSuffixes = [
    'cdninstagram.com',
    'fbcdn.net',
    'instagram.com',
    'igcdn.com',
];

$isAllowed = false;
foreach ($allowedHostSuffixes as $suffix) {
    if ($host === $suffix || str_ends_with($host, '.' . $suffix)) {
        $isAllowed = true;
        break;
    }
}

if (!$isAllowed) {
    http_response_code(403);
    echo json_encode(['status' => false, 'message' => 'Access denied: URL domain is not a valid Instagram media provider.'], JSON_PRETTY_PRINT);
    exit;
}

function cleanupTempDir(string $dir, int $maxAgeSeconds = 3600): void
{
    if (!is_dir($dir)) {
        return;
    }
    $now = time();
    $files = glob($dir . '/*');
    if ($files) {
        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file) > $maxAgeSeconds)) {
                @unlink($file);
            }
        }
    }
}

function mediaProxy(string $url): void
{
    $tempDir = __DIR__ . '/temp';
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0755, true);
    }

    cleanupTempDir($tempDir);

    // Generate safe, deterministic extension and filename hash
    $extension = 'jpg';
    $pathWithoutQuery = strtok($url, '?');
    $ext = pathinfo($pathWithoutQuery, PATHINFO_EXTENSION);
    if (in_array(strtolower($ext), ['mp4', 'webm', 'jpg', 'jpeg', 'png', 'webp'])) {
        $extension = strtolower($ext);
    }

    $fileName = md5($url) . '.' . $extension;
    $filePath = $tempDir . '/' . $fileName;
    $relativePath = 'temp/' . $fileName;

    if (!file_exists($filePath)) {
        $proxyHost = $_ENV['PROXY_HOST'] ?? getenv('PROXY_HOST') ?? '';
        $proxyPort = $_ENV['PROXY_PORT'] ?? getenv('PROXY_PORT') ?? '';
        $proxy = (!empty($proxyHost) && !empty($proxyPort)) ? "http://{$proxyHost}:{$proxyPort}" : null;

        $clientOptions = [
            'timeout' => 20.0,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/127.0.0.0 Safari/537.36',
            ],
            'sink' => $filePath,
        ];

        if ($proxy) {
            $clientOptions['proxy'] = $proxy;
        }

        try {
            $client = new Client();
            $client->request('GET', $url, $clientOptions);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => false, 'message' => 'Failed to proxy media file: ' . $e->getMessage()], JSON_PRETTY_PRINT);
            exit;
        }
    }

    echo json_encode(['status' => true, 'path' => $relativePath], JSON_PRETTY_PRINT);
}

mediaProxy($mediaUrl);

