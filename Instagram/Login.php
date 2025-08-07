<?php

namespace InstagramPHP;

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Exception\ClientException;

$dotenv = Dotenv::createImmutable(__DIR__)->safeLoad();

class Login
{
    private $client;
    private $username;
    private $password;
    private $host;
    private $port;
    function __construct(ClientInterface $client, string $username, string $password)
    {
        $this->client = $client;
        $this->password = $password;
        $this->username = $username;
        $this->host = $_ENV['PROXY_HOST'] ?? getenv('PROXY_HOST') ?? '';
        $this->port = $_ENV['PROXY_PORT'] ?? getenv('PROXY_PORT') ?? '';
    }
    public function SessionLogin()
    {
        $Request = $this->client->request('GET', Uri::BASE_URL, [
            'headers' => [
                'user-agent' => Uri::UA_LINUX,
            ],
            'proxy' => (!empty($this->host) && !empty($this->port)) ? "http://{$this->host}:{$this->port}" : null
        ]);
        preg_match('/"csrf_token":"(.*?)"/', $Request->getBody(), $matches);
        if (!isset($matches[1])) {
            throw new Exception('Unable to extract CSRF token from Instagram response.');
        }

        $csrfToken = $matches[1];
        $cookieJar = new FileCookieJar('cookies.txt', true);
        $query = null;
        try {
            $query = $this->client->request('POST', Uri::AUTH_URL, [
                'form_params' => [
                    'username'     => $this->username,
                    'enc_password' => '#PWD_INSTAGRAM_BROWSER:0:' . time() . ':' . $this->password,
                ],
                'headers'     => [
                    'cookie'      => 'ig_cb=1; csrftoken=' . $csrfToken,
                    'referer'     => Uri::BASE_URL,
                    'x-csrftoken' => $csrfToken,
                    'user-agent'  => Uri::UA_LINUX,
                ],
                'cookies'     => $cookieJar,
                'proxy' => (!empty($this->host) && !empty($this->port)) ? "http://{$this->host}:{$this->port}" : null
            ]);
        } catch (ClientException $exception) {
            $data = json_decode((string) $exception->getResponse()->getBody());
            if ($data && isset($data->message) && $data->message === 'checkpoint_required') {
                throw new Exception('Instagram requires checkpoint verification (possible 2FA or suspicious login). ' . $exception->getMessage());
            }
            throw new Exception('Login request failed: ' . $exception->getMessage());
        }

        if (!$query) {
            throw new Exception('No response from Instagram login request.');
        }

        $response = json_decode((string) $query->getBody());

        if (property_exists($response, 'authenticated') && $response->authenticated == true) {
            return true;
        } else if (property_exists($response, 'error_type') && $response->error_type === 'generic_request_error') {
            throw new Exception('Generic error / Your IP may be blocked by Instagram. You should consider using a proxy.');
        } else {
            throw new Exception('Wrong login / password');
        }
    }
}
