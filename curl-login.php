<?php

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://www.instagram.com/',
    CURLOPT_RETURNTRANSFER => true,
    /*CURLOPT_PROXY => '105.112.191.250',
    CURLOPT_PROXYPORT => '3128',
    CURLOPT_PROXYTYPE => CURLPROXY_HTTP,
    CURLOPT_HTTPPROXYTUNNEL => true,*/
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Safari/537.36'
    ),
));

$response = curl_exec($curl);
curl_close($curl);
preg_match('/<script type="text\/javascript">window\._sharedData\s?=(.+);<\/script>/', $response, $matches);
if (!isset($matches[1])) {
    throw new Exception('Unable to extract JSON data');
}

$data = json_decode($matches[1]);

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://www.instagram.com/accounts/login/ajax/',
    CURLOPT_RETURNTRANSFER => true,
    /*CURLOPT_PROXY => '105.112.191.250',
    CURLOPT_PROXYPORT => '3128',
    CURLOPT_PROXYTYPE => CURLPROXY_HTTP,
    CURLOPT_HTTPPROXYTUNNEL => true,*/
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_COOKIEJAR => 'cookies.txt',
    CURLOPT_COOKIEFILE => 'cookies.txt',
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => array('enc_password' => '#PWD_INSTAGRAM_BROWSER:0:' . time() . ':alireza@1399', 'username' => 'matinkamran__'),
    CURLOPT_HTTPHEADER => array(
        'referer: https://www.instagram.com/',
        'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36',
        'x-csrftoken: ' . $data->config->csrf_token,
        'cookie: ig_cb=1; csrftoken=' . $data->config->csrf_token
    ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
