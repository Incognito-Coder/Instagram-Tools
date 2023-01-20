<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use InstagramPHP\Login as InstagramPHPLogin;

if (file_exists('cookies.txt')) {
    unlink('cookies.txt');
}
$session = new InstagramPHPLogin(new Client(), 'a.k.47.official', 'cumron');
$session->SessionLogin();
