<?php

namespace InstagramPHP;

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Exception\ClientException;

class Login
{
    private ClientInterface $client;
    private string $username;
    private string $password;
    private ?string $proxy = null;

    public function __construct(ClientInterface $client, string $username, string $password)
    {
        $this->client = $client;
        $this->username = $username;
        $this->password = $password;

        $host = $_ENV['PROXY_HOST'] ?? getenv('PROXY_HOST') ?? '';
        $port = $_ENV['PROXY_PORT'] ?? getenv('PROXY_PORT') ?? '';
        if (!empty($host) && !empty($port)) {
            $this->proxy = "http://{$host}:{$port}";
        }
    }

    public function SessionLogin(): bool
    {
        $cookieJar = new FileCookieJar('cookies.txt', true);

        $getOptions = [
            'headers' => [
                'user-agent' => Uri::UA_LINUX,
                'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                'accept-language' => 'en-US,en;q=0.9',
                'sec-ch-ua' => '"Google Chrome";v="127", "Chromium";v="127", "Not?A_Brand";v="24"',
                'sec-ch-ua-mobile' => '?0',
                'sec-ch-ua-platform' => '"Windows"',
                'sec-fetch-dest' => 'document',
                'sec-fetch-mode' => 'navigate',
                'sec-fetch-site' => 'none',
                'sec-fetch-user' => '?1',
                'upgrade-insecure-requests' => '1',
            ],
            'cookies' => $cookieJar,
        ];

        if ($this->proxy) {
            $getOptions['proxy'] = $this->proxy;
        }

        $request = $this->client->request('GET', Uri::BASE_URL, $getOptions);
        $body = (string) $request->getBody();

        preg_match('/"csrf_token":"(.*?)"/', $body, $matches);
        $csrfToken = $matches[1] ?? null;

        if (!$csrfToken) {
            // Fallback attempt: check cookies for csrf token
            foreach ($cookieJar as $cookie) {
                if ($cookie->getName() === 'csrftoken') {
                    $csrfToken = $cookie->getValue();
                    break;
                }
            }
        }

        if (!$csrfToken) {
            throw new Exception('Unable to extract CSRF token from Instagram login response.');
        }

        try {
            $postOptions = [
                'form_params' => [
                    'username' => $this->username,
                    'enc_password' => '#PWD_INSTAGRAM_BROWSER:0:' . time() . ':' . $this->password,
                    'queryParams' => '{}',
                    'optIntoOneTap' => 'false',
                    'stopDeletionNonce' => '',
                    'trustedDeviceRecords' => '{}',
                ],
                'headers' => [
                    'authority' => 'www.instagram.com',
                    'accept' => '*/*',
                    'accept-language' => 'en-US,en;q=0.9',
                    'content-type' => 'application/x-www-form-urlencoded',
                    'origin' => Uri::BASE_URL,
                    'referer' => Uri::BASE_URL . 'accounts/login/',
                    'sec-ch-ua' => '"Google Chrome";v="127", "Chromium";v="127", "Not?A_Brand";v="24"',
                    'sec-ch-ua-mobile' => '?0',
                    'sec-ch-ua-platform' => '"Windows"',
                    'sec-fetch-dest' => 'empty',
                    'sec-fetch-mode' => 'cors',
                    'sec-fetch-site' => 'same-origin',
                    'user-agent' => Uri::UA_LINUX,
                    'x-asbd-id' => '198387',
                    'x-csrftoken' => $csrfToken,
                    'x-ig-app-id' => Uri::APP_ID,
                    'x-ig-www-claim' => '0',
                    'x-requested-with' => 'XMLHttpRequest',
                ],
                'cookies' => $cookieJar,
            ];

            if ($this->proxy) {
                $postOptions['proxy'] = $this->proxy;
            }

            $query = $this->client->request('POST', Uri::AUTH_URL, $postOptions);
        } catch (ClientException $exception) {
            $responseObj = $exception->getResponse();
            $respBody = $responseObj ? (string) $responseObj->getBody() : '';
            $data = json_decode($respBody);

            if ($data && isset($data->message) && $data->message === 'checkpoint_required') {
                $checkpointUrl = isset($data->checkpoint_url) ? 'https://www.instagram.com' . $data->checkpoint_url : '';
                throw new Exception("Instagram requires checkpoint verification / 2FA. Open the link in browser to approve login or paste session cookies into cookies.txt.\nCheckpoint URL: {$checkpointUrl}");
            }

            throw new Exception('Login request failed: ' . $exception->getMessage());
        }

        if (!$query) {
            throw new Exception('No response from Instagram login request.');
        }

        $response = json_decode((string) $query->getBody());

        if ($response && property_exists($response, 'authenticated') && $response->authenticated === true) {
            return true;
        }

        if ($response && property_exists($response, 'error_type') && $response->error_type === 'generic_request_error') {
            throw new Exception('Generic error: Your IP may be blocked by Instagram. Consider using a PROXY in .env.');
        }

        $msg = $response->message ?? 'Invalid Instagram username or password.';
        throw new Exception($msg);
    }
}

