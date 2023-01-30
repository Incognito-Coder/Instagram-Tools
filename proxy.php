<?php
header('Access-Control-Allow-Origin: *');
$media = $_POST['file'];

function ExtractFileName($url): string
{
    preg_match('/[^\/]+.(?=[\?])/', $url, $matched);
    return $matched[0];
}
function MediaProxy($file)
{
    if (!is_dir('temp')) {
        mkdir('temp');
    }
    file_put_contents('temp/' . ExtractFileName($file), file_get_contents($file));
    echo json_encode(['path' => 'temp/' . ExtractFileName($file)]);
}

MediaProxy($media);
