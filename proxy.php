<?php
header('Access-Control-Allow-Origin: *');
$media = $_POST['file'];

function MediaProxy($file)
{
    file_put_contents('tmp.jpg', file_get_contents($file));
    echo json_encode(['path' => 'tmp.jpg']);
}

MediaProxy($media);
