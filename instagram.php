<?php
header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');
$_COOKIE = '';
error_reporting(0);
class Instagram
{
    function igInfo($user)
    {
        $request = curl_init();
        curl_setopt_array($request, array(
            CURLOPT_URL => sprintf('https://www.instagram.com/%s/?__a=1', $user),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.71 Safari/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'Cache-Control: max-age=0',
                'Cookie: ' . $_COOKIE
            )
        ));
        $exec = curl_exec($request);
        $response = json_decode($exec);
        curl_close($request);
        if ($exec != '{}') {
            $user = $response->graphql->user;
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
        $request = curl_init();
        curl_setopt_array($request, array(
            CURLOPT_URL => sprintf('%s&__a=1', $url),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
            CURLOPT_COOKIEFILE => 'cookies.txt',
            CURLOPT_COOKIEJAR => 'cookies.txt',
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.71 Safari/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'Cache-Control: max-age=0',
                'Cookie: ' . $_COOKIE
            )
        ));
        $exec = curl_exec($request);
        $response = json_decode($exec);
        curl_close($request);
        if ($exec != '{}') {
            $data = $response->items[0];
            if ($data->media_type == 2) {
                if (empty($data->caption->text))
                    return json_encode(['status' => true, 'type' => 'video', 'caption' => null, 'file' => $data->video_versions[0]->url], 128);
                else
                    return json_encode(['status' => true, 'type' => 'video', 'caption' => $data->caption->text, 'file' => $data->video_versions[0]->url], 128);
            } elseif ($data->media_type == 1) {
                if (empty($data->caption->text))
                    return json_encode(['status' => true, 'type' => 'image', 'caption' => null, 'file' => $data->image_versions2->candidates[0]->url], 128);
                else
                    return json_encode(['status' => true, 'type' => 'image', 'caption' => $data->caption->text, 'file' => $data->image_versions2->candidates[0]->url], 128);
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
                    return json_encode(['status' => true, 'type' => 'side', 'caption' => null, 'data' => $childrens], 128);
                else
                    return json_encode(['status' => true, 'type' => 'side', 'caption' => $data->caption->text, 'data' => $childrens], 128);
            }
        } else {
            return json_encode(['status' => false], 128);
        }
    }
    function igStory($url)
    {
        preg_match('/.*([?].*)/', $url, $match);
        $urlnew =  str_replace($match[1], '/', $url);
        $curl_id = curl_init();
        curl_setopt_array($curl_id, array(
            CURLOPT_URL => sprintf('%s?__a=1', $urlnew),
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
        $userid = json_decode($id)->user->id;
        preg_match('/\/stories\/(.*?)\/(.*?)\//', $urlnew, $match);
        $request = curl_init();
        curl_setopt_array($request, array(
            CURLOPT_URL => sprintf('https://i.instagram.com/api/v1/feed/user/%s/reel_media/', $userid),
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
        $exec = curl_exec($request);
        $response = json_decode($exec);
        curl_close($request);
        foreach ($response->items as $items) {
            if ($items->pk == $match[2]) {
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
    if (str_contains($_GET['link'], 'stories')) {
        echo $instagram->igStory($_GET['link']);
    } else {
        echo $instagram->igSave($_GET['link']);
    }
}
