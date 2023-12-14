<?php
set_time_limit(0);
ignore_user_abort(true);
error_reporting(E_ALL);
ini_set("display_errors",1);
$path = '/var/www/html/bot/';
require_once $path . 'inc/func.php';
$func = new Func();
	
//$_POST = explode("|||",base64_decode($_POST));
$_POST['link'] = false;
echo "Uploading to thevideo.me ... \n";
if(!$_POST['link'] = $func->thevideome($_POST['vmeUser'], $_POST['vmePass'], $_POST['cookie'], $_POST['videoFile']))
	if(!$_POST['link'] = $func->thevideome($_POST['vmeUser'], $_POST['vmePass'], $_POST['cookie'], $_POST['videoFile']))
		if(!$_POST['link'] = $func->thevideome($_POST['vmeUser'], $_POST['vmePass'], $_POST['cookie'], $_POST['videoFile']))
				echo "Uploading to Thevideo.me Failed... \n";
		
if($_POST['link'])
{
	echo "Posting thevideo.me  link on IWO... \n";
	$func->curl('http://www.iwatchonline.ph/tools/postbot',$_POST);
}