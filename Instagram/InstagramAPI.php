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
                'sec-ch-ua' => '"Google Chrome";v="119", "Chromium";v="119", "Not?A_Brand";v="24"',
                'sec-ch-ua-mobile' => '?0',
                'sec-ch-ua-platform' => '"Windows"',
                'sec-fetch-dest' => 'document',
                'sec-fetch-mode' => 'navigate',
                'sec-fetch-site' => 'same-origin',
                'sec-fetch-user' => '?1',
                'upgrade-insecure-requests' => '1',
                'user-agent' => Uri::UA_LINUX,
                'viewport-width' => '1920'
            ],
            'cookies' => $this->cookie
        ];
        preg_match('/{"media_id":"(.*?)"/', $this->SendRequest('GET', $url, $options), $matches);
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
                'sec-ch-ua' => '"Google Chrome";v="119", "Chromium";v="119", "Not?A_Brand";v="24"',
                'sec-ch-ua-mobile' => '?0',
                'sec-ch-ua-platform' => '"Windows"',
                'sec-fetch-dest' => 'document',
                'sec-fetch-mode' => 'navigate',
                'sec-fetch-site' => 'same-origin',
                'sec-fetch-user' => '?1',
                'upgrade-insecure-requests' => '1',
                'user-agent' => Uri::UA_LINUX,
                'viewport-width' => '1920'
            ],
            'cookies' => $this->cookie
        ];
        preg_match('/"user_id":"(.*?)"}/', $this->SendRequest('GET', $url, $options), $matches);
        return $matches[1];
    }
    public function fetchStory($user_id)
    {
        $options = [
            'headers' => [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'User-Agent' => Uri::UA_IG_ANDROID
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
                'user-agent' => Uri::UA_LINUX,
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
                'user-agent' => Uri::UA_LINUX,
                'x-ig-app-id' => '936619743392459'
            ],
            'cookies' => $this->cookie
        ];
        return $this->SendRequest('GET', Uri::USER_PROFILE_URL . $username, $options);
    }
    public function fetchHighlight($url, $opt = 'first_response')
    {
        $fetch = $this->SendRequest('GET', $url, ['cookies' => $this->cookie]);
        preg_match('/"highlight:(.*?)","page_logging"/', $fetch, $matches);
        switch ($opt) {
            case 'second_response';
                $options = [
                    'headers' => [
                        'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                        'accept-language' => 'en-US,en;q=0.9,fa-IR;q=0.8,fa;q=0.7',
                        'user-agent' => Uri::UA_IG_ANDROID,
                        'Cache-Control' => 'max-age=0'
                    ],
                    'cookies' => $this->cookie
                ];
                try {
                    $result = json_decode($this->SendRequest('GET', Uri::REELS_URL . 'highlight%3A' . $matches[1], $options));
                } catch (Exception $e) {
                    preg_match('/www.instagram.com\/s\/(.+)[?]/', $url, $matches);
                    $result = json_decode($this->SendRequest('GET', Uri::REELS_URL . base64_decode($matches[1]), $options));
                }
                break;
            default;
                $result = $matches[1];
                break;
        }
        return $result;
    }
}
