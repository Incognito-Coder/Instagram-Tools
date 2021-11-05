<?php
header('Content-type: application/json');
$_COOKIE = 'mid=YP0P1QAEAAFzzlzQyQ0kF9n-bkMZ; ig_did=DA8C7B0D-A53D-4E9B-BC8A-C5CF025AF31D; ig_nrcb=1; ds_user_id=7731688175; fbm_124024574287414=base_domain=.instagram.com; shbid="5794\0547731688175\0541667491773:01f713074c987edaf26bd68cc113ee2ac2c3aebab239e1a46b91274c69f6e1ffa40674cd"; shbts="1635955773\0547731688175\0541667491773:01f78fa75710a6a0382f629951aea1e0d0e6aab0424f409d6c4a4a88c185fb8893b59286"; csrftoken=7Awx41etO0RoIlVyrrMBIV6HvPljqU4t; sessionid=7731688175:hVBseSJdPpo6Qr:23; fbsr_124024574287414=jkwFNPTOmJPN58hVN2ZpWwVpNHTM5c79taF1hmRrg88.eyJ1c2VyX2lkIjoiMTAwMDExMDQwMTU2NDE0IiwiY29kZSI6IkFRQWRSa1hCWW0xaHV5OFIzMC1VUDN4X3k2dHVYeUdkblY3SG1NNHpfaGJSU2VWbmVOQVQ4R1RuTTlwRWVyMG0wdzR2M1pfcW1MWExYcTZzY0ZnYnNPZ05WNHJka3JPZGJtR3YyeG5Sdzd5VVBuQ25YNkFXcUNydjlrdnBFc2hKTjZ1aXZuN2dFaHdjMUdIVkYwOHh6MmlVbjFmZl9SRHE5V3NLWnQ0bWl6VVl3LWk0ekNtQ25BcDFmWnZjdjA1OXpWRl83RVZBcWtJeG9DVGlIYUx5ZDdQR245bmxjc0RCQ2JSUDlDSXB6TzV5aUF2cVpXQV9DOXh4clJiRzJKNFVoMmJDY3VHaFhySC1XUmFUTWhYMUxQNE9uU1hyVG00RWFDRmQ2YmRTUmJGV2R0NE9RdVdJeVVkVjh1WHQ4eTRqSGoyOFZWaHNGeEduUzI5Nk91ZFo5WUFWIiwib2F1dGhfdG9rZW4iOiJFQUFCd3pMaXhuallCQUxjdU9FTzFYZTdCalBaQVBsVTVDN3ZINTJVY1JpbGJYaGVDdVpCTDg5V1FXUDcyYm1mTm1yVktMZ21DREc2OEFaQ3VqWEEwVjJ2M3hudllvaHlrdlpCQUxBdGlIWElOUGdkTXhYMVVEZkI4NmM1ajNuVURaQURIYkFlNmpyYVNvT1pBNGE5TnRaQWFtWkFaQUdzSjVPaGY1WkNaQmtkd0tFeGd1cHh2cHZrZVZwRHh3dm1BMk5RZXhQbmJMWDh2NE51N1FaRFpEIiwiYWxnb3JpdGhtIjoiSE1BQy1TSEEyNTYiLCJpc3N1ZWRfYXQiOjE2MzYxMTI5OTh9; rur="RVA\0547731688175\0541667649023:01f70b2d8fa9612e38eef48a8486876f00a3e2e3bd0a677f0d835c2848e2ebb6bc0f15d8"';
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
            $data = $response->graphql->shortcode_media;
            if ($data->__typename == 'GraphVideo') {
                if (empty($data->edge_media_to_caption->edges[0]->node->text))
                    return json_encode(['status' => true, 'type' => 'video', 'caption' => null, 'file' => $data->video_url], 128);
                else
                    return json_encode(['status' => true, 'type' => 'video', 'caption' => $data->edge_media_to_caption->edges[0]->node->text, 'file' => $data->video_url], 128);
            } elseif ($data->__typename == 'GraphImage') {
                if (empty($data->edge_media_to_caption->edges[0]->node->text))
                    return json_encode(['status' => true, 'type' => 'image', 'caption' => null, 'file' => $data->display_url], 128);
                else
                    return json_encode(['status' => true, 'type' => 'image', 'caption' => $data->edge_media_to_caption->edges[0]->node->text, 'file' => $data->display_url], 128);
            } elseif ($data->__typename == 'GraphSidecar') {
                $childrens = [];
                foreach ($data->edge_sidecar_to_children->edges as $child) {
                    if ($child->node->__typename == 'GraphVideo') {
                        array_push($childrens, ['type' => $child->node->__typename, 'file' => $child->node->video_url]);
                    } else {
                        array_push($childrens, ['type' => $child->node->__typename, 'file' => $child->node->display_url]);
                    }
                }
                if (empty($data->edge_media_to_caption->edges[0]->node->text))
                    return json_encode(['status' => true, 'type' => 'side', 'caption' => null, 'data' => $childrens], 128);
                else
                    return json_encode(['status' => true, 'type' => 'side', 'caption' => $data->edge_media_to_caption->edges[0]->node->text, 'data' => $childrens], 128);
            }
        } else {
            return json_encode(['status' => false], 128);
        }
    }
}
$instagram = new Instagram();
if (isset($_GET['user']) && !empty($_GET['user'])) {
    echo $instagram->igInfo($_GET['user']);
} elseif (isset($_GET['link']) && !empty($_GET['link'])) {
    echo $instagram->igSave($_GET['link']);
}
