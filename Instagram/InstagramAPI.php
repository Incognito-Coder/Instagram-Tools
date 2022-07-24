<?php

namespace InstagramPHP;

use Exception;
use GuzzleHttp\Client;

class InstagramAPI
{
    private $cookie;
    public function __construct(
        mixed $cookie
    ) {
        $this->cookie = $cookie;
    }
    function SendRequest(string $method, string $url, array $opts)
    {
        $client = new Client();
        $request = $client->request($method, $url, $opts);
        return $request->getBody();
    }
    public function getMediaID($url)
    {
        $options = [
            'headers' => [
                'authority' => 'www.instagram.com',
                'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'accept-language' => 'en-US,en;q=0.9,fa-IR;q=0.8,fa;q=0.7',
                'cache-control' => 'max-age=0',
                'sec-ch-prefers-color-scheme' => 'light',
                'sec-ch-ua' => '" Not A;Brand";v="99", "Chromium";v="102", "Google Chrome";v="102"',
                'sec-ch-ua-mobile' => '?0',
                'sec-ch-ua-platform' => '"Windows"',
                'sec-fetch-dest' => 'document',
                'sec-fetch-mode' => 'navigate',
                'sec-fetch-site' => 'same-origin',
                'sec-fetch-user' => '?1',
                'upgrade-insecure-requests' => '1',
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.5005.63 Safari/537.36',
                'viewport-width' => '1920'
            ],
            'cookies' => $this->cookie
        ];
        preg_match('/{"media_id":"(.*?)",/', $this->SendRequest('GET', $url, $options), $matches);
        return $matches[1];
    }
    public function getReelsID($url)
    {
        $options = [
            'headers' => [
                'authority' => 'www.instagram.com',
                'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'accept-language' => 'en-US,en;q=0.9,fa-IR;q=0.8,fa;q=0.7',
                'cache-control' => 'max-age=0',
                'sec-ch-prefers-color-scheme' => 'light',
                'sec-ch-ua' => '" Not A;Brand";v="99", "Chromium";v="102", "Google Chrome";v="102"',
                'sec-ch-ua-mobile' => '?0',
                'sec-ch-ua-platform' => '"Windows"',
                'sec-fetch-dest' => 'document',
                'sec-fetch-mode' => 'navigate',
                'sec-fetch-site' => 'same-origin',
                'sec-fetch-user' => '?1',
                'upgrade-insecure-requests' => '1',
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.5005.63 Safari/537.36',
                'viewport-width' => '1920'
            ],
            'cookies' => $this->cookie
        ];
        preg_match('/"props":{"user":{"id":"(.*?)"/', $this->SendRequest('GET', $url, $options), $matches);
        return $matches[1];
    }
    public function fetchStory($user_id)
    {
        $options = [
            'headers' => [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'User-Agent' => 'Mozilla/5.0 (Linux; Android 8.1.0; Pixel C Build/OPM8.190605.005; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/89.0.4389.90 Safari/537.36 Instagram 179.0.0.31.132 Android (27/8.1.0; 320dpi; 1800x2448; Google/google; Pixel C; dragon; drago'
            ],
            'cookies' => $this->cookie
        ];
        return $this->SendRequest('GET', "https://i.instagram.com/api/v1/feed/user/$user_id/reel_media/", $options);
    }
    public function fetchData($media_id)
    {
        $options = [
            'headers' => [
                'accept' => '*/*',
                'accept-language' => 'en-US,en;q=0.9,fa-IR;q=0.8,fa;q=0.7',
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.5005.63 Safari/537.36',
                'x-ig-app-id' => '936619743392459'
            ],
            'cookies' => $this->cookie
        ];
        return $this->SendRequest('GET', "https://i.instagram.com/api/v1/media/$media_id/info/", $options);
    }
    public function fetchProfile($username)
    {
        $options = [
            'headers' => [
                'accept' => '*/*',
                'accept-language' => 'en-US,en;q=0.9,fa-IR;q=0.8,fa;q=0.7',
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.5005.63 Safari/537.36',
                'x-ig-app-id' => '936619743392459'
            ],
            'cookies' => $this->cookie
        ];
        return $this->SendRequest('GET', Uri::USER_PROFILE_URL . $username, $options);
    }
    public function fetchHighlight($url, $opt = 'first_response')
    {
        $fetch = $this->SendRequest('GET', $url, ['cookies' => $this->cookie]);
        preg_match('/"highlight":(.*?),"page_logging"/', $fetch, $matches);
        $data = json_decode($matches[1]);
        switch ($opt) {
            case 'second_response';
                $options = [
                    'headers' => [
                        'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                        'accept-language' => 'en-US,en;q=0.9,fa-IR;q=0.8,fa;q=0.7',
                        'user-agent' => 'Mozilla/5.0 (Linux; Android 8.1.0; Pixel C Build/OPM8.190605.005; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/89.0.4389.90 Safari/537.36 Instagram 179.0.0.31.132 Android (27/8.1.0; 320dpi; 1800x2448; Google/google; Pixel C; dragon; drago',
                        'Cache-Control' => 'max-age=0'
                    ],
                    'cookies' => $this->cookie
                ];
                try {
                    $result = json_decode($this->SendRequest('GET', Uri::REELS_URL . 'highlight%3A' . $data->id, $options));
                } catch (Exception $e) {
                    preg_match('/www.instagram.com\/s\/(.+)[?]/', $url, $matches);
                    $result = json_decode($this->SendRequest('GET', Uri::REELS_URL . base64_decode($matches[1]), $options));
                }
                break;
            default;
                $result = $data->id;
                break;
        }
        return $result;
    }
}
