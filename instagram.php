<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use GuzzleHttp\Cookie\FileCookieJar;
use InstagramPHP\InstagramAPI;

// Load environment variables if .env exists
if (file_exists(__DIR__ . '/.env')) {
    Dotenv::createImmutable(__DIR__)->safeLoad();
}

class InstagramService
{
    private InstagramAPI $api;
    public ?string $highlightUrl = null;

    public function __construct()
    {
        $cookieJar = new FileCookieJar(__DIR__ . '/cookies.txt');
        $this->api = new InstagramAPI($cookieJar);
    }

    private function jsonResponse(array $data, int $statusCode = 200): string
    {
        http_response_code($statusCode);
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function getProfileInfo(string $username): string
    {
        try {
            $raw = $this->api->fetchProfile($username);
            $data = json_decode($raw);

            if (!isset($data->data->user)) {
                return $this->jsonResponse(['status' => false, 'message' => 'User profile not found or private.'], 404);
            }

            $user = $data->data->user;
            $id = $user->id ?? $user->pk ?? '';
            $followers = $user->edge_followed_by->count ?? $user->follower_count ?? $user->followers ?? 0;
            $following = $user->edge_follow->count ?? $user->following_count ?? $user->following ?? 0;
            $mediaCount = $user->edge_owner_to_timeline_media->count ?? $user->media_count ?? $user->posts ?? 0;

            return $this->jsonResponse([
                'status' => true,
                'type' => 'profile',
                'name' => $user->full_name ?? '',
                'username' => $user->username ?? $username,
                'id' => (string) $id,
                'bio' => $user->biography ?? '',
                'website' => $user->external_url ?? null,
                'account' => [
                    'business' => (bool)($user->is_business_account ?? false),
                    'professional' => (bool)($user->is_professional_account ?? false),
                    'category' => $user->category_name ?? null,
                ],
                'profile_hd' => $user->profile_pic_url_hd ?? $user->profile_pic_url ?? '',
                'followers' => $followers,
                'following' => $following,
                'media_count' => $mediaCount,
                'is_verified' => (bool)($user->is_verified ?? false),
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getMedia(string $url): string
    {
        try {
            $mediaId = $this->api->getMediaID($url);
            if (!$mediaId) {
                return $this->jsonResponse(['status' => false, 'message' => 'Could not extract Media ID from URL.'], 400);
            }

            $response = $this->api->fetchData($mediaId, $url);
            $jayson = json_decode($response);

            if (!isset($jayson->items[0])) {
                return $this->jsonResponse(['status' => false, 'message' => 'Media item unavailable or private.'], 404);
            }

            $item = $jayson->items[0];
            $captionText = $item->caption->text ?? null;
            $likes = $item->like_count ?? 0;
            $comments = $item->comment_count ?? 0;
            $views = $item->play_count ?? $item->view_count ?? null;

            // Media types: 1 = Image, 2 = Video, 8 = Carousel / Sidecar
            if ($item->media_type == 2) {
                return $this->jsonResponse([
                    'status' => true,
                    'type' => 'video',
                    'caption' => $captionText,
                    'like' => $likes,
                    'comment' => $comments,
                    'view' => $views,
                    'file' => $item->video_versions[0]->url ?? null,
                ]);
            } elseif ($item->media_type == 1) {
                return $this->jsonResponse([
                    'status' => true,
                    'type' => 'image',
                    'caption' => $captionText,
                    'like' => $likes,
                    'comment' => $comments,
                    'view' => $views,
                    'file' => $item->image_versions2->candidates[0]->url ?? null,
                ]);
            } elseif ($item->media_type == 8) {
                $children = [];
                if (!empty($item->carousel_media)) {
                    foreach ($item->carousel_media as $child) {
                        if ($child->media_type == 2) {
                            $children[] = [
                                'type' => 'video',
                                'file' => $child->video_versions[0]->url ?? null,
                            ];
                        } else {
                            $children[] = [
                                'type' => 'image',
                                'file' => $child->image_versions2->candidates[0]->url ?? null,
                            ];
                        }
                    }
                }
                return $this->jsonResponse([
                    'status' => true,
                    'type' => 'side',
                    'caption' => $captionText,
                    'like' => $likes,
                    'comment' => $comments,
                    'view' => $views,
                    'data' => $children,
                ]);
            }

            return $this->jsonResponse(['status' => false, 'message' => 'Unsupported media type.'], 400);
        } catch (Exception $e) {
            return $this->jsonResponse(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function isHighlight(string $url): bool
    {
        if (str_contains($url, 'highlight')) {
            $this->highlightUrl = $url;
            return true;
        }

        if (preg_match('/www\.instagram\.com\/s\/(.+)[?\/]?/', $url, $matches)) {
            $decoded = base64_decode($matches[1]);
            if (str_contains($decoded, 'highlight')) {
                $this->highlightUrl = $url;
                return true;
            }
        }

        return false;
    }

    public function getHighlight(string $url): string
    {
        try {
            $response = $this->api->fetchHighlight($url);
            $response2 = $this->api->fetchHighlight($url, 'second_response');

            if (!$response2 || !isset($response2->reels)) {
                return $this->jsonResponse(['status' => false, 'message' => 'Highlight content not found.'], 404);
            }

            $children = [];
            $hid = 'highlight:' . $response;
            $items = $response2->reels->$hid->items ?? [];

            foreach ($items as $child) {
                if ($child->media_type == 2) {
                    $children[] = ['type' => 'video', 'file' => $child->video_versions[0]->url ?? null];
                } else {
                    $children[] = ['type' => 'image', 'file' => $child->image_versions2->candidates[0]->url ?? null];
                }
            }

            return $this->jsonResponse([
                'status' => true,
                'type' => 'side',
                'caption' => 'Highlight Collection',
                'like' => null,
                'comment' => null,
                'data' => $children,
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getStory(string $url): string
    {
        try {
            $cleanUrl = preg_replace('/\?.*$/', '/', $url);
            if (!str_ends_with($cleanUrl, '/')) {
                $cleanUrl .= '/';
            }

            preg_match('/\/stories\/([^\/]+)\/([^\/]+)\//', $cleanUrl, $matches);
            $storyPk = $matches[2] ?? null;

            $userId = $this->api->getReelsID($cleanUrl);
            if (!$userId) {
                return $this->jsonResponse(['status' => false, 'message' => 'Could not detect Instagram User ID for stories.'], 400);
            }

            $raw = $this->api->fetchStory($userId);
            $json = json_decode($raw);

            if (empty($json->items)) {
                return $this->jsonResponse(['status' => false, 'message' => 'No active stories found for this user.'], 404);
            }

            foreach ($json->items as $item) {
                if (!$storyPk || $item->pk == $storyPk) {
                    if ($item->media_type == 2) {
                        return $this->jsonResponse([
                            'status' => true,
                            'type' => 'video',
                            'caption' => null,
                            'file' => $item->video_versions[0]->url ?? null,
                        ]);
                    } elseif ($item->media_type == 1) {
                        return $this->jsonResponse([
                            'status' => true,
                            'type' => 'image',
                            'caption' => null,
                            'file' => $item->image_versions2->candidates[0]->url ?? null,
                        ]);
                    }
                }
            }

            // Fallback to first story item if specific PK was expired
            $first = $json->items[0];
            $file = ($first->media_type == 2)
                ? ($first->video_versions[0]->url ?? null)
                : ($first->image_versions2->candidates[0]->url ?? null);

            return $this->jsonResponse([
                'status' => true,
                'type' => ($first->media_type == 2) ? 'video' : 'image',
                'caption' => null,
                'file' => $file,
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

$service = new InstagramService();

if (!empty($_GET['user'])) {
    echo $service->getProfileInfo(trim($_GET['user']));
} elseif (!empty($_GET['link'])) {
    $link = trim($_GET['link']);

    if (str_contains($link, 'stories') && !str_contains($link, 'highlight')) {
        echo $service->getStory($link);
    } elseif ($service->isHighlight($link)) {
        echo $service->getHighlight($service->highlightUrl ?? $link);
    } else {
        echo $service->getMedia($link);
    }
} else {
    echo json_encode([
        'status' => false,
        'message' => 'Missing parameter: "link" (URL) or "user" (username) is required.'
    ], JSON_PRETTY_PRINT);
}

