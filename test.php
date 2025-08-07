<?php
require 'vendor/autoload.php';

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use InstagramPHP\Login as InstagramPHPLogin;

$dotenv = Dotenv::createImmutable(__DIR__)->safeLoad();

if (file_exists('cookies.txt')) {
    unlink('cookies.txt');
}
//$session = new InstagramPHPLogin(new Client(), 'user', 'pass');
$session = new InstagramPHPLogin(new Client(), $_ENV['username'], $_ENV['password']);
$session->SessionLogin();
