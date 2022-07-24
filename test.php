<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use InstagramPHP\Login as InstagramPHPLogin;

$session = new InstagramPHPLogin(new Client(), 'user', 'pass');
$session->SessionLogin();
