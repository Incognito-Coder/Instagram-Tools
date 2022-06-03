<?php
header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');
$_COOKIE = 'mid=YpBUDwALAAFTg5TqZTL7LwgJbcHG; ig_did=D7A6DA5A-CD8E-4833-AB90-017C771A7040; ig_nrcb=1; csrftoken=tcL2KnLkTcZsut7DQ5wJ7eB0Q8FMdGuq; ds_user_id=31384858829; sessionid=31384858829%3AnT7bRJSjP7RgY6%3A21; rur="CLN\05431384858829\0541685162202:01f70da22413f9af74762e290946ffe51f97ab12fd3b955bf33929849f2dba09f1eb1f3a"';
error_reporting(0);
require 'v2/instagram.php';

use InstagramPHP\InstagramReloaded;

class Instagram
{
    function igInfo($user)
    {
        $ig = new InstagramReloaded($_COOKIE);
        $response = $ig->fetchProfile($user);
        if ($response != '{}') {
            $jayson = json_decode($response);
            $user = $jayson->data->user;
            $json = [
                "status" => true,
                "name" => $user->full_name,
                "id" => $user->id,
                "bio" => $user->biography,
                "website" => $user->external_url, "account" => ["business" => $user->is_business_account, "professional" => $user->is_professional_account, "category" => $user->category_name],
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
        $ig = new InstagramReloaded($_COOKIE);
        $response = $ig->fetchData($ig->getMediaID($url));
        if ($response != '{}') {
            $jayson = json_decode($response);
            $data = $jayson->items[0];
            if ($data->media_type == 2) {
                if (empty($data->caption->text))
                    return json_encode(['status' => true, 'type' => 'video', 'caption' => null, 'like' => $data->like_count, 'comment' => $data->comment_count, 'file' => $data->video_versions[0]->url], 128);
                else
                    return json_encode(['status' => true, 'type' => 'video', 'caption' => $data->caption->text, 'like' => $data->like_count, 'comment' => $data->comment_count, 'file' => $data->video_versions[0]->url], 128);
            } elseif ($data->media_type == 1) {
                if (empty($data->caption->text))
                    return json_encode(['status' => true, 'type' => 'image', 'caption' => null, 'like' => $data->like_count, 'comment' => $data->comment_count, 'file' => $data->image_versions2->candidates[0]->url], 128);
                else
                    return json_encode(['status' => true, 'type' => 'image', 'caption' => $data->caption->text, 'like' => $data->like_count, 'comment' => $data->comment_count, 'file' => $data->image_versions2->candidates[0]->url], 128);
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
                    return json_encode(['status' => true, 'type' => 'side', 'caption' => null, 'like' => $data->like_count, 'comment' => $data->comment_count, 'data' => $childrens], 128);
                else
                    return json_encode(['status' => true, 'type' => 'side', 'caption' => $data->caption->text, 'like' => $data->like_count, 'comment' => $data->comment_count, 'data' => $childrens], 128);
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
            $curl_id = curl_init();
            curl_setopt_array($curl_id, array(
                CURLOPT_URL =>  $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_COOKIEFILE => 'cookies.txt',
                CURLOPT_COOKIEJAR => 'cookies.txt'
            ));
            $id = curl_exec($curl_id);
            curl_close($curl_id);
            preg_match('/property="og:url" content="(.*)"/', $id, $matches);
            preg_match('/.*([?].*)/', $matches[1], $match);
            $urlnew =  str_replace($match[1], '/', $matches[1]);
            if (str_contains($urlnew, 'highlight')) {
                $this->highlight_url = $urlnew;
                return true;
            } else {
                return false;
            }
        }
    }
    function igHighlight($url)
    {

        $request_id = curl_init();
        curl_setopt_array($request_id, array(
            CURLOPT_URL => sprintf('%s?__a=1', $url),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_COOKIEFILE => 'cookies.txt',
            CURLOPT_COOKIEJAR => 'cookies.txt'
        ));
        $exec = curl_exec($request_id);
        $response = json_decode($exec);
        $request = curl_init();
        curl_setopt_array($request, array(
            CURLOPT_URL => 'https://i.instagram.com/api/v1/feed/reels_media/?reel_ids=highlight%3A' . $response->highlight->id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
            CURLOPT_COOKIEFILE => 'cookies.txt',
            CURLOPT_COOKIEJAR => 'cookies.txt',
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'User-Agent: Mozilla/5.0 (Linux; Android 8.1.0; Pixel C Build/OPM8.190605.005; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/89.0.4389.90 Safari/537.36 Instagram 179.0.0.31.132 Android (27/8.1.0; 320dpi; 1800x2448; Google/google; Pixel C; dragon; drago',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'Cache-Control: max-age=0',
                'Cookie: ' . $_COOKIE
            )
        ));
        $exec2 = curl_exec($request);
        $response2 = json_decode($exec2);
        curl_close($request);
        $childrens = [];
        $hid = 'highlight:' . $response->highlight->id;
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
        $ig = new InstagramReloaded($_COOKIE);
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
