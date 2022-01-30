<?php
header('Access-Control-Allow-Origin: *');
$media = $_POST['file'];

function MediaProxy($file)
{
    $tmp = 'tmp.jpg';
    file_put_contents($tmp, file_get_contents($file));
    echo json_encode(['path' => FullPath($tmp)]);
}
function FullPath($name)
{
    $break = Explode('/', $_SERVER["SCRIPT_NAME"]);
    $pfile = $break[count($break) - 1];
    $spliter = explode($pfile, $_SERVER["SERVER_NAME"] . $_SERVER["SCRIPT_NAME"]);
    return 'https://' . $spliter[0] . $name;
}
MediaProxy($media);
