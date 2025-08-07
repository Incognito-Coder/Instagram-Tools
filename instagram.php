<?php
header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');
error_reporting(0);
require 'vendor/autoload.php';

use GuzzleHttp\Cookie\FileCookieJar;
use InstagramPHP\InstagramAPI;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__)->safeLoad();

$_COOKIE = new FileCookieJar('cookies.txt');
class Instagram
{

    function igInfo($user)
    {
        $ig = new InstagramAPI($_COOKIE);
        $response = $ig->fetchProfile($user);
        if ($response != '{}') {
            $jayson = json_decode($response);
            $user = $jayson->data->user;
            $json = [
                "status" => true,
                "name" => $user->full_name,
                "id" => $user->id,
                "bio" => $user->biography,
                "website" => $user->external_url,
                "account" => ["business" => $user->is_business_account, "professional" => $user->is_professional_account, "category" => $user->category_name],
                "profile_hd" => $user->profile_pic_url_hd,
                "followers" => $user->edge_followed_by->count,
                "following" => $user->edge_follow->count
            ];
            return json_encode($json, 128);
        } else {
            return json_encode(['status' => false], 128);
        }
    }

    function igSave($url)
    {
        $ig = new InstagramAPI($_COOKIE);
        $response = $ig->fetchData($ig->getMediaID($url));
        if ($response != '{}') {
            $jayson = json_decode($response);
            $data = $jayson->items[0];
            if ($data->media_type == 2) {
                if (empty($data->caption->text))
                    return json_encode(['status' => true, 'type' => 'video', 'caption' => null, 'like' => $data->like_count, 'comment' => $data->comment_count, 'view' => $data->play_count, 'file' => $data->video_versions[0]->url], 128);
                else
                    return json_encode(['status' => true, 'type' => 'video', 'caption' => $data->caption->text, 'like' => $data->like_count, 'comment' => $data->comment_count, 'view' => $data->play_count, 'file' => $data->video_versions[0]->url], 128);
            } elseif ($data->media_type == 1) {
                if (empty($data->caption->text))
                    return json_encode(['status' => true, 'type' => 'image', 'caption' => null, 'like' => $data->like_count, 'comment' => $data->comment_count, 'view' => $data->play_count, 'file' => $data->image_versions2->candidates[0]->url], 128);
                else
                    return json_encode(['status' => true, 'type' => 'image', 'caption' => $data->caption->text, 'like' => $data->like_count, 'comment' => $data->comment_count, 'view' => $data->play_count, 'file' => $data->image_versions2->candidates[0]->url], 128);
            } elseif ($data->media_type == 8) {
                $childrens = [];
                foreach ($data->carousel_media as $child) {
                    if ($child->media_type == 2) {
                        array_push($childrens, ['type' => 'video', 'file' => $child->video_versions[0]->url]);
                    } else {
                        array_push($childrens, ['type' => 'image', 'file' => $child->image_versions2->candidates[0]->url]);
                    }
                }
                if (empty($data->caption->text))
                    return json_encode(['status' => true, 'type' => 'side', 'caption' => null, 'like' => $data->like_count, 'comment' => $data->comment_count, 'view' => $data->play_count, 'data' => $childrens], 128);
                else
                    return json_encode(['status' => true, 'type' => 'side', 'caption' => $data->caption->text, 'like' => $data->like_count, 'comment' => $data->comment_count, 'view' => $data->play_count, 'data' => $childrens], 128);
            }
        } else {
            return json_encode(['status' => false], 128);
        }
    }
    public $highlight_url;
    function isHighlight($url)
    {
        if (str_contains($url, 'highlight')) {
            $this->highlight_url = $url;
            return true;
        } else {
            preg_match('/www.instagram.com\/s\/(.+)[?]/', $url, $matches);
            if (str_contains(base64_decode($matches[1]), 'highlight')) {
                $this->highlight_url = $url;
                return true;
            } else {
                return false;
            }
        }
    }
    function igHighlight($url)
    {
        $ig = new InstagramAPI($_COOKIE);
        $response = $ig->fetchHighlight($url);
        $response2 = $ig->fetchHighlight($url, 'second_response');
        $childrens = [];
        $hid = 'highlight:' . $response;
        foreach ($response2->reels->$hid->items as $child) {
            if ($child->media_type == 2) {
                array_push($childrens, ['type' => 'video', 'file' => $child->video_versions[0]->url]);
            } else {
                array_push($childrens, ['type' => 'image', 'file' => $child->image_versions2->candidates[0]->url]);
            }
        }
        return json_encode(['status' => true, 'type' => 'side', 'caption' => null, 'like' => null, 'comment' => null, 'data' => $childrens], 128);
    }
    function igStory($url)
    {
        preg_match('/.*([?].*)/', $url, $match);
        $urlnew =  str_replace($match[1], '/', $url);
        preg_match('/\/stories\/(.*?)\/(.*?)\//', $urlnew, $matches);
        $ig = new InstagramAPI($_COOKIE);
        $response = $ig->fetchStory($ig->getReelsID($urlnew));
        $jayson = json_decode($response);
        foreach ($jayson->items as $items) {
            if ($items->pk == $matches[2]) {
                if ($items->media_type == 2) {
                    return json_encode(['status' => true, 'type' => 'video', 'caption' => null, 'file' => $items->video_versions[0]->url], 128);
                } elseif ($items->media_type == 1) {
                    return json_encode(['status' => true, 'type' => 'image', 'caption' => null, 'file' => $items->image_versions2->candidates[0]->url], 128);
                }
            }
        }
    }
}
$instagram = new Instagram();
if (isset($_GET['user']) && !empty($_GET['user'])) {
    echo $instagram->igInfo($_GET['user']);
} elseif (isset($_GET['link']) && !empty($_GET['link'])) {
    if (str_contains($_GET['link'], 'stories') && !str_contains($_GET['link'], 'highlight')) {
        echo $instagram->igStory($_GET['link']);
    } elseif ($instagram->isHighlight($_GET['link'])) {
        echo $instagram->igHighlight($instagram->highlight_url);
    } else {
        echo $instagram->igSave($_GET['link']);
    }
}
