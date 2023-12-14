<?php
set_time_limit(0);
error_reporting(E_ALL);
ini_set("display_errors",1);

$path = dirname( __FILE__ ) . '/';
require_once $path . '/inc/torrent.php';
require_once $path . '/inc/func.php';
require_once $path . '/inc/TransmissionRPC.class.php';
require_once $path . 'config.php';
$cookie = $path."temp/cookie.txt";

$func = new Func();
$file = '/var/www/html/bot/upload/Big.Brother.UK.S18E06.XviD-AFG/46122-HDTVS-S18E06-iwatchonline.cr.avi';
//$file = 'http://drive.google.com/open?id=0B54HVGzL6p00VWdueU53cy0tOEU';
//echo $func->firedrive($username,$pass,$file);

echo $func->imovieto($imovietoUser, $imovietoPass, $file);