<?php

namespace InstagramPHP;

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Exception\ClientException;

class Login
{
    private $client;
    private $username;
    private $password;
    function __construct(ClientInterface $client, string $username, string $password)
    {
        $this->client = $client;
        $this->password = $password;
        $this->username = $username;
    }
    public function SessionLogin()
    {
        $Request = $this->client->request('GET', Uri::BASE_URL, [
            'headers' => [
                'user-agent' => Uri::UA_IG_ANDROID,
            ],
        ]);
        preg_match('/\\\"csrf_token\\\":\\\"(.*?)\\\"/', $Request->getBody(), $matches);
        if (!isset($matches[1])) {
            throw new Exception('Unable to extract JSON data');
        }

        $data = $matches[1];
        $cookieJar = new FileCookieJar('cookies.txt', true);
        try {
            $query = $this->client->request('POST', Uri::AUTH_URL, [
                'form_params' => [
                    'username'     => $this->username,
                    'enc_password' => '#PWD_INSTAGRAM_BROWSER:0:' . time() . ':' . $this->password,
                ],
                'headers'     => [
                    'cookie'      => 'ig_cb=1; csrftoken=' . $data,
                    'referer'     => Uri::BASE_URL,
                    'x-csrftoken' => $data,
                    'user-agent'  => Uri::UA_IG_ANDROID,
                ],
                'cookies'     => $cookieJar,
            ]);
        } catch (ClientException $exception) {
            $data = json_decode((string) $exception->getResponse()->getBody());
            if ($data && $data->message === 'checkpoint_required') {
                throw new Exception('Disable 2FA' . $exception->getMessage());
            }
        }
        $response = json_decode((string) $query->getBody());

        if (property_exists($response, 'authenticated') && $response->authenticated == true) {
            return true;
        } else if (property_exists($response, 'error_type') && $response->error_type === 'generic_request_error') {
            throw new Exception('Generic error / Your IP may be block from Instagram. You should consider using a proxy.');
        } else {
            throw new Exception('Wrong login / password');
        }
    }
}
