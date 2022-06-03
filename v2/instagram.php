<?php

namespace InstagramPHP;

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class InstagramReloaded
{
    private $cookie;
    public function __construct(
        mixed $cookie
    ) {
        $this->cookie = $cookie;
    }
    public function getMediaID($url)
    {

        $client = new Client();
        $headers = [
            'authority' => 'www.instagram.com',
            'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'accept-language' => 'en-US,en;q=0.9,fa-IR;q=0.8,fa;q=0.7',
            'cache-control' => 'max-age=0',
            'cookie' => $this->cookie,
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
        ];
        $request = new Request('GET', $url, $headers);
        $res = $client->sendAsync($request)->wait();
        preg_match('/content="instagram:\/\/media[?]id=(.*?)" \/>/', $res->getBody(), $matches);
        return $matches[1];
    }
    public function getReelsID($url)
    {
        $client = new Client();
        $headers = [
            'authority' => 'www.instagram.com',
            'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'accept-language' => 'en-US,en;q=0.9,fa-IR;q=0.8,fa;q=0.7',
            'cache-control' => 'max-age=0',
            'cookie' => $this->cookie,
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
        ];
        $request = new Request('GET', $url, $headers);
        $res = $client->sendAsync($request)->wait();
        preg_match('/"props":{"user":{"id":"(.*?)"/', $res->getBody(), $matches);
        return $matches[1];
    }
    public function fetchStory($user_id)
    {

        $client = new Client();
        $headers = [
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'User-Agent' => 'Mozilla/5.0 (Linux; Android 8.1.0; Pixel C Build/OPM8.190605.005; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/89.0.4389.90 Safari/537.36 Instagram 179.0.0.31.132 Android (27/8.1.0; 320dpi; 1800x2448; Google/google; Pixel C; dragon; drago',
            'Cookie' => $this->cookie
        ];
        $request = new Request('GET', "https://i.instagram.com/api/v1/feed/user/$user_id/reel_media/", $headers);
        $res = $client->sendAsync($request)->wait();
        return $res->getBody();
    }
    public function fetchData($media_id)
    {
        $client = new Client();
        $headers = [
            'accept' => '*/*',
            'accept-language' => 'en-US,en;q=0.9,fa-IR;q=0.8,fa;q=0.7',
            'cookie' => $this->cookie,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.5005.63 Safari/537.36',
            'x-ig-app-id' => '936619743392459'
        ];
        $request = new Request('GET', "https://i.instagram.com/api/v1/media/$media_id/info/", $headers);
        $res = $client->sendAsync($request)->wait();
        return $res->getBody();
    }
    public function fetchProfile($username)
    {

        $client = new Client();
        $headers = [
            'accept' => '*/*',
            'accept-language' => 'en-US,en;q=0.9,fa-IR;q=0.8,fa;q=0.7',
            'cookie' => $this->cookie,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.5005.63 Safari/537.36',
            'x-ig-app-id' => '936619743392459'
        ];
        $request = new Request('GET', 'https://i.instagram.com/api/v1/users/web_profile_info/?username=' . $username, $headers);
        $res = $client->sendAsync($request)->wait();
        return $res->getBody();
    }
}
