<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Cookie\SetCookie;
use InstagramPHP\InstagramAPI;

if (file_exists(__DIR__ . '/.env')) {
    Dotenv::createImmutable(__DIR__)->safeLoad();
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'status';

function jsonOut(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

$cookieFile = __DIR__ . '/cookies.txt';

if ($action === 'status') {
    $exists = file_exists($cookieFile) && filesize($cookieFile) > 10;
    jsonOut([
        'status' => true,
        'has_session' => $exists,
        'message' => $exists ? 'Session cookies file is configured.' : 'No active session cookies found.',
    ]);
}

if ($action === 'save') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true) ?? $_POST;

    $sessionId = trim($data['sessionid'] ?? '');
    $cookieString = trim($data['cookie_string'] ?? '');

    if (empty($sessionId) && empty($cookieString)) {
        jsonOut(['status' => false, 'message' => 'Please provide a valid sessionid or cookie string.'], 400);
    }

    $jar = new FileCookieJar($cookieFile, true);

    if (!empty($sessionId)) {
        // Strip out 'sessionid=' if user accidentally copied the key name
        $sessionId = preg_replace('/^sessionid=/i', '', $sessionId);
        
        $cookie = new SetCookie([
            'Name' => 'sessionid',
            'Value' => $sessionId,
            'Domain' => '.instagram.com',
            'Path' => '/',
            'Secure' => true,
            'HttpOnly' => true,
        ]);
        $jar->setCookie($cookie);
    }

    if (!empty($cookieString)) {
        $pairs = explode(';', $cookieString);
        foreach ($pairs as $pair) {
            $parts = explode('=', trim($pair), 2);
            if (count($parts) === 2) {
                $cookie = new SetCookie([
                    'Name' => trim($parts[0]),
                    'Value' => trim($parts[1]),
                    'Domain' => '.instagram.com',
                    'Path' => '/',
                    'Secure' => true,
                ]);
                $jar->setCookie($cookie);
            }
        }
    }

    $jar->save($cookieFile);

    // Verify session against Instagram API
    try {
        $api = new InstagramAPI($jar);
        $res = $api->fetchProfile('instagram');
        $decoded = json_decode($res);

        if (isset($decoded->data->user)) {
            jsonOut([
                'status' => true,
                'message' => 'Instagram Session verified and saved successfully! 🎉',
            ]);
        } else {
            jsonOut([
                'status' => true,
                'warning' => true,
                'message' => 'Session cookies saved, but Instagram verification returned limited response. Test downloading a post to confirm.',
            ]);
        }
    } catch (Exception $e) {
        jsonOut([
            'status' => true,
            'warning' => true,
            'message' => 'Cookies saved to cookies.txt. (' . $e->getMessage() . ')',
        ]);
    }
}

jsonOut(['status' => false, 'message' => 'Invalid action.'], 400);
