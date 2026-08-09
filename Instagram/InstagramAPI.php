<?php

namespace InstagramPHP;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

class InstagramAPI
{
    private mixed $cookie;
    private ?string $proxy = null;
    private ClientInterface $client;

    public function __construct(mixed $cookie = null, ?ClientInterface $client = null)
    {
        $this->cookie = $cookie;

        // Fallback: If no cookie jar passed or empty, check for SESSION_ID in environment
        $sessionId = $_ENV['SESSION_ID'] ?? getenv('SESSION_ID') ?? null;
        $cookieString = $_ENV['COOKIE_STRING'] ?? getenv('COOKIE_STRING') ?? null;

        if ((!$this->cookie || (is_object($this->cookie) && method_exists($this->cookie, 'count') && count($this->cookie) === 0)) && ($sessionId || $cookieString)) {
            $jar = new \GuzzleHttp\Cookie\CookieJar();
            if ($sessionId) {
                $jar->setCookie(new \GuzzleHttp\Cookie\SetCookie([
                    'Name' => 'sessionid',
                    'Value' => trim($sessionId),
                    'Domain' => '.instagram.com',
                    'Path' => '/',
                    'Secure' => true,
                    'HttpOnly' => true,
                ]));
            }
            if ($cookieString) {
                foreach (explode(';', $cookieString) as $pair) {
                    $parts = explode('=', trim($pair), 2);
                    if (count($parts) === 2) {
                        $jar->setCookie(new \GuzzleHttp\Cookie\SetCookie([
                            'Name' => trim($parts[0]),
                            'Value' => trim($parts[1]),
                            'Domain' => '.instagram.com',
                            'Path' => '/',
                            'Secure' => true,
                        ]));
                    }
                }
            }
            $this->cookie = $jar;
        }

        $host = $_ENV['PROXY_HOST'] ?? getenv('PROXY_HOST') ?? '';
        $port = $_ENV['PROXY_PORT'] ?? getenv('PROXY_PORT') ?? '';
        if (!empty($host) && !empty($port)) {
            $this->proxy = "http://{$host}:{$port}";
        }
        $this->client = $client ?? new Client([
            'timeout' => 15.0,
            'http_errors' => false,
        ]);
    }

    /**
     * Send HTTP request using Guzzle Client with proxy and cookies applied
     */
    public function SendRequest(string $method, string $url, array $opts = []): string
    {
        if ($this->proxy && !isset($opts['proxy'])) {
            $opts['proxy'] = $this->proxy;
        }

        if (!isset($opts['cookies']) && $this->cookie) {
            $opts['cookies'] = $this->cookie;
        }

        try {
            $response = $this->client->request($method, $url, $opts);
            return (string) $response->getBody();
        } catch (GuzzleException $e) {
            throw new Exception("Instagram API Request failed [{$url}]: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Common web headers for IG html requests
     */
    private function getWebHeaders(string $userAgent = Uri::UA_LINUX): array
    {
        return [
            'authority' => 'www.instagram.com',
            'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'accept-language' => 'en-US,en;q=0.9',
            'cache-control' => 'max-age=0',
            'user-agent' => $userAgent,
            'sec-fetch-dest' => 'document',
            'sec-fetch-mode' => 'navigate',
            'sec-fetch-site' => 'same-origin',
        ];
    }

    /**
     * Common API headers for mobile/web app requests
     */
    private function getApiHeaders(string $userAgent = Uri::UA_LINUX): array
    {
        return [
            'accept' => '*/*',
            'accept-language' => 'en-US,en;q=0.9',
            'user-agent' => $userAgent,
            'x-ig-app-id' => Uri::APP_ID,
        ];
    }

    public function shortcodeToMediaId(string $code): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';
        $id = '0';
        for ($i = 0; $i < strlen($code); $i++) {
            $char = $code[$i];
            $pos = strpos($alphabet, $char);
            if ($pos === false) continue;
            if (function_exists('bcmul')) {
                $id = bcadd(bcmul($id, '64'), (string)$pos);
            } else {
                $id = sprintf('%.0f', ((float)$id * 64) + $pos);
            }
        }
        return (string)$id;
    }

    public function getMediaID(string $url): ?string
    {
        // Try extracting shortcode from /p/CODE/, /reel/CODE/, /tv/CODE/
        if (preg_match('/instagram\.com\/(?:p|reel|tv)\/([A-Za-z0-9_-]+)/i', $url, $matches)) {
            return $this->shortcodeToMediaId($matches[1]);
        }

        $options = [
            'headers' => $this->getWebHeaders(Uri::UA_LINUX),
        ];

        try {
            $html = $this->SendRequest('GET', $url, $options);

            if (preg_match('/{"media_id":"(.*?)"/', $html, $matches)) {
                return $matches[1];
            }

            if (preg_match('/"post_id":"(.*?)"/', $html, $matches)) {
                return $matches[1];
            }
        } catch (Exception $e) {
            // Ignore
        }

        return null;
    }

    public function getReelsID(string $url): ?string
    {
        $options = [
            'headers' => $this->getWebHeaders(Uri::UA_LINUX),
        ];

        try {
            $html = $this->SendRequest('GET', $url, $options);

            if (preg_match('/"user_id":"(.*?)"/', $html, $matches)) {
                return $matches[1];
            }

            if (preg_match('/"owner":\s*{"id":"(.*?)"/', $html, $matches)) {
                return $matches[1];
            }
        } catch (Exception $e) {
            // Ignore
        }

        return null;
    }

    public function fetchStory(string $userId): string
    {
        $options = [
            'headers' => [
                'Accept' => '*/*',
                'User-Agent' => Uri::UA_IG_ANDROID,
                'x-ig-app-id' => Uri::APP_ID,
            ],
        ];
        return $this->SendRequest('GET', Uri::USER_REEL_MEDIA_URL . "{$userId}/reel_media/", $options);
    }

    public function fetchData(string $mediaId, ?string $originalUrl = null): string
    {
        $options = [
            'headers' => $this->getApiHeaders(Uri::UA_LINUX),
        ];

        try {
            $res = $this->SendRequest('GET', Uri::MEDIA_INFO_URL . "{$mediaId}/info/", $options);
            $json = json_decode($res);
            if ($json && isset($json->items[0])) {
                return $res;
            }
        } catch (Exception $e) {
            // Fallthrough to embed scraper
        }

        // Fallback: Embed HTML Scraper
        if ($originalUrl && preg_match('/instagram\.com\/(?:p|reel|tv)\/([A-Za-z0-9_-]+)/i', $originalUrl, $matches)) {
            $shortcode = $matches[1];
            try {
                $embedHtml = $this->SendRequest('GET', "https://www.instagram.com/p/{$shortcode}/embed/captioned/", [
                    'headers' => $this->getWebHeaders(Uri::UA_LINUX),
                ]);

                $synthetic = $this->parseMediaFromEmbedHtml($shortcode, $embedHtml);
                $decoded = json_decode($synthetic);
                if ($decoded && isset($decoded->items[0])) {
                    return $synthetic;
                }
            } catch (Exception $e) {
                // Ignore
            }
        }

        return json_encode(['items' => []]);
    }

    private function parseMediaFromEmbedHtml(string $shortcode, string $html): string
    {
        $mediaType = 1; // 1 = Image, 2 = Video
        $mediaUrl = '';
        $caption = '';
        $likes = 0;

        if (preg_match('/<video[^>]+src="([^"]+)"/i', $html, $matches)) {
            $mediaType = 2;
            $mediaUrl = htmlspecialchars_decode($matches[1]);
        } elseif (preg_match('/<img class="EmbeddedMediaImage"[^>]+src="([^"]+)"/i', $html, $matches)) {
            $mediaType = 1;
            $mediaUrl = htmlspecialchars_decode($matches[1]);
        } elseif (preg_match('/<meta property="og:image" content="(.*?)"/i', $html, $matches)) {
            $mediaType = 1;
            $mediaUrl = htmlspecialchars_decode($matches[1]);
        }

        if (preg_match('/<div class="Caption"[^>]*>(.*?)<\/div>/s', $html, $matches)) {
            $caption = trim(strip_tags($matches[1]));
        }

        if (preg_match('/"like_count":(\d+)/', $html, $matches)) {
            $likes = (int)$matches[1];
        }

        $synthetic = [
            'items' => [
                [
                    'media_type' => $mediaType,
                    'caption' => ['text' => $caption],
                    'like_count' => $likes,
                    'comment_count' => 0,
                    'play_count' => null,
                    'video_versions' => ($mediaType == 2) ? [['url' => $mediaUrl]] : [],
                    'image_versions2' => ['candidates' => [['url' => $mediaUrl]]],
                ]
            ]
        ];

        return json_encode($synthetic);
    }

    public function fetchProfile(string $username): string
    {
        $cleanUsername = trim(ltrim($username, '@'));

        // Strategy 1: Standard web_profile_info endpoint on www.instagram.com with full browser headers
        $options1 = [
            'headers' => [
                'authority' => 'www.instagram.com',
                'accept' => '*/*',
                'accept-language' => 'en-US,en;q=0.9',
                'origin' => Uri::BASE_URL,
                'referer' => "https://www.instagram.com/{$cleanUsername}/",
                'sec-ch-ua' => '"Google Chrome";v="127", "Chromium";v="127", "Not?A_Brand";v="24"',
                'sec-ch-ua-mobile' => '?0',
                'sec-ch-ua-platform' => '"Windows"',
                'sec-fetch-dest' => 'empty',
                'sec-fetch-mode' => 'cors',
                'sec-fetch-site' => 'same-origin',
                'user-agent' => Uri::UA_LINUX,
                'x-asbd-id' => '198387',
                'x-csrftoken' => 'val' . md5($cleanUsername),
                'x-ig-app-id' => Uri::APP_ID,
                'x-ig-www-claim' => '0',
                'x-requested-with' => 'XMLHttpRequest',
            ],
        ];

        try {
            $raw = $this->SendRequest('GET', "https://www.instagram.com/api/v1/users/web_profile_info/?username={$cleanUsername}", $options1);
            $decoded = json_decode($raw);
            if ($decoded && isset($decoded->data->user)) {
                return $raw;
            }
        } catch (Exception $e) {
            // Proceed to fallbacks
        }

        // Strategy 2: Mobile i.instagram.com API
        $options2 = [
            'headers' => [
                'accept' => '*/*',
                'accept-language' => 'en-US,en;q=0.9',
                'user-agent' => Uri::UA_IG_ANDROID,
                'x-ig-app-id' => Uri::APP_ID,
            ],
        ];

        try {
            $raw = $this->SendRequest('GET', Uri::USER_PROFILE_URL . urlencode($cleanUsername), $options2);
            $decoded = json_decode($raw);
            if ($decoded && isset($decoded->data->user)) {
                return $raw;
            }
        } catch (Exception $e) {
            // Proceed to fallbacks
        }

        // Strategy 3: HTML metadata scraping fallback
        try {
            $html = $this->SendRequest('GET', "https://www.instagram.com/{$cleanUsername}/", [
                'headers' => $this->getWebHeaders(Uri::UA_LINUX),
            ]);

            return $this->parseProfileFromHtml($cleanUsername, $html);
        } catch (Exception $e) {
            return json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    private function parseProfileFromHtml(string $username, string $html): string
    {
        $fullName = '';
        $bio = '';
        $profilePic = '';
        $userId = '';
        $followers = '0';
        $following = '0';
        $posts = '0';

        // Extract User ID
        if (preg_match('/"(user_id|pk|profile_id)":"?(\d{5,18})"?/i', $html, $matches)) {
            $userId = $matches[2];
        } elseif (preg_match('/"owner":\{"id":"(\d+)"/i', $html, $matches)) {
            $userId = $matches[1];
        } elseif (preg_match('/instapp:owner_user_id" content="(\d+)"/i', $html, $matches)) {
            $userId = $matches[1];
        }

        // Extract Full Name
        if (preg_match('/"full_name":"(.*?)"/', $html, $matches)) {
            $fullName = json_decode('"' . $matches[1] . '"') ?? $matches[1];
        } elseif (preg_match('/<meta property="og:title" content="(.*?)"/i', $html, $matches)) {
            $fullName = explode('(', htmlspecialchars_decode($matches[1]))[0] ?? $username;
        }

        // Extract Bio
        if (preg_match('/"biography":"(.*?)"/', $html, $matches)) {
            $bio = json_decode('"' . $matches[1] . '"') ?? $matches[1];
        }

        // Extract Profile Picture
        if (preg_match('/"profile_pic_url_hd":"(.*?)"/', $html, $matches)) {
            $profilePic = stripslashes($matches[1]);
        } elseif (preg_match('/<meta property="og:image" content="(.*?)"/i', $html, $matches)) {
            $profilePic = htmlspecialchars_decode($matches[1]);
        }

        // Extract Followers, Following, and Posts Count from script JSON or meta description
        if (preg_match('/"edge_followed_by":\{"count":(\d+)\}/', $html, $matches)) {
            $followers = (int) $matches[1];
        } elseif (preg_match('/"follower_count":(\d+)/', $html, $matches)) {
            $followers = (int) $matches[1];
        }

        if (preg_match('/"edge_follow":\{"count":(\d+)\}/', $html, $matches)) {
            $following = (int) $matches[1];
        } elseif (preg_match('/"following_count":(\d+)/', $html, $matches)) {
            $following = (int) $matches[1];
        }

        if (preg_match('/"edge_owner_to_timeline_media":\{"count":(\d+)\}/', $html, $matches)) {
            $posts = (int) $matches[1];
        } elseif (preg_match('/"media_count":(\d+)/', $html, $matches)) {
            $posts = (int) $matches[1];
        }

        // Meta description fallback e.g. "678M Followers, 52 Following, 7,890 Posts"
        if (($followers === 0 || $followers === '0') && preg_match('/content="([^"]*Followers[^"]*)"/i', $html, $matches)) {
            $desc = $matches[1];
            if (preg_match('/([\d\.,KMBkmb]+)\s*Followers/i', $desc, $fMatch)) {
                $followers = $fMatch[1];
            }
            if (preg_match('/([\d\.,KMBkmb]+)\s*Following/i', $desc, $flMatch)) {
                $following = $flMatch[1];
            }
            if (preg_match('/([\d\.,KMBkmb]+)\s*Posts/i', $desc, $pMatch)) {
                $posts = $pMatch[1];
            }
        }

        $synthetic = [
            'data' => [
                'user' => [
                    'full_name' => trim($fullName) ?: $username,
                    'username' => $username,
                    'id' => $userId,
                    'biography' => trim($bio),
                    'external_url' => null,
                    'is_business_account' => false,
                    'is_professional_account' => false,
                    'category_name' => null,
                    'profile_pic_url_hd' => $profilePic,
                    'profile_pic_url' => $profilePic,
                    'edge_followed_by' => ['count' => $followers],
                    'edge_follow' => ['count' => $following],
                    'edge_owner_to_timeline_media' => ['count' => $posts],
                    'is_verified' => false,
                ]
            ]
        ];

        return json_encode($synthetic);
    }

    public function fetchHighlight(string $url, string $opt = 'first_response'): mixed
    {
        $fetch = $this->SendRequest('GET', $url, [
            'headers' => $this->getWebHeaders(Uri::UA_LINUX),
        ]);

        preg_match('/"highlight:(.*?)","page_logging"/', $fetch, $matches);
        $highlightId = $matches[1] ?? null;

        if ($opt === 'second_response') {
            $options = [
                'headers' => [
                    'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'accept-language' => 'en-US,en;q=0.9',
                    'user-agent' => Uri::UA_IG_ANDROID,
                    'Cache-Control' => 'max-age=0',
                ],
            ];

            try {
                if ($highlightId) {
                    $raw = $this->SendRequest('GET', Uri::REELS_URL . 'highlight%3A' . $highlightId, $options);
                    return json_decode($raw);
                }
            } catch (Exception $e) {
                // Fallback to URL code decoding
                if (preg_match('/www\.instagram\.com\/s\/(.+)[?\/]?/', $url, $matches2)) {
                    $decoded = base64_decode($matches2[1]);
                    $raw = $this->SendRequest('GET', Uri::REELS_URL . urlencode($decoded), $options);
                    return json_decode($raw);
                }
            }
        }

        return $highlightId;
    }
}

