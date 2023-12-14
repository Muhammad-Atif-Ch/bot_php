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
echo "Uploading to clipwatching.com ... \n";
if(!$_POST['link'] = $func->clipwatchingcom($_POST['clipwatchingcomUser'], $_POST['clipwatchingcomPass'], $_POST['cookie'], $_POST['videoFile']))
	if(!$_POST['link'] = $func->clipwatchingcom($_POST['clipwatchingcomUser'], $_POST['clipwatchingcomPass'], $_POST['cookie'], $_POST['videoFile']))
		if(!$_POST['link'] = $func->clipwatchingcom($_POST['clipwatchingcomUser'], $_POST['clipwatchingcomPass'], $_POST['cookie'], $_POST['videoFile']))
				echo "Uploading to clipwatching Failed... \n";
		
if($_POST['link'])
{
	echo "Posting clipwatching.com  link on IWO... \n";
	$func->curl('https://www.iwatchonline.cr/tools/postbot',$_POST);
}