<?php
set_time_limit(0);
ignore_user_abort(true);
error_reporting(E_ALL);
ini_set("display_errors",1);

$path = '/var/www/html/bot/';
require_once $path . 'inc/torrent.php';
require_once $path . 'inc/func.php';
require_once $path . '/inc/TransmissionRPC.class.php';
require_once $path . 'config.php';
require_once $path . 'manual_config.php';


$videoFile =  $argv[1];
$putlockerCookie =  $argv[2];
$postData =  $argv[3];

print_r($argv);